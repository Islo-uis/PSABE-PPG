<?php
include "../../database.php";
session_start();
if (isset($_GET["action"])) {
    if ($_GET["action"] == 'getAccount') {
        header('Content-Type: application/json');
        $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

        if (!$conn) {
            die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
        }
        $user    = $_POST['user'];
        $pass    = $_POST['pass'];
        $real  = false;

        // 1. Prepare your statement (I’m assuming you have an `id` column you can grab here)
        if ($stmt = $conn->prepare("SELECT username, password FROM admin WHERE username = ?")) {
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($dbUser, $dbPassHash);
                $stmt->fetch();
                if (password_verify($pass, $dbPassHash)) {
                    $_SESSION['Username'] = $user;
                    $real = true;
                }
            }
            $stmt->close();
        } else {
            echo "Database error: " . $conn->error;
        }
        mysqli_close($conn);
        echo json_encode([
            "real" => $real
        ]);
    }

    if ($_GET["action"] == 'saveRecord') {
        header('Content-Type: application/json');
        $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

        if (!$conn) {
            die(json_encode(["success" => false, "error" => "Connection failed: " . mysqli_connect_error()]));
        }

        $type = $_POST['type'];
        $record = json_decode($_POST['record'], true);
        $success = false;

        try {
            if ($type === 'announcement') {
                // Prepare statement for announcements
                if ($stmt = $conn->prepare("INSERT INTO announcements (id, header, description, photo, enabled) 
                                      VALUES (?, ?, ?, ?, ?)
                                      ON DUPLICATE KEY UPDATE 
                                      header = VALUES(header), 
                                      description = VALUES(description), 
                                      photo = VALUES(photo), 
                                      enabled = VALUES(enabled)")) {
                    $stmt->bind_param(
                        "ssssi",
                        $record['id'],
                        $record['header'],
                        $record['desc'],
                        $record['photo'],
                        $record['enabled']
                    );
                    $success = $stmt->execute();
                    $stmt->close();
                }
            } elseif ($type === 'merch') {
                // Prepare statement for merch
                if ($stmt = $conn->prepare("INSERT INTO merch (id, name, price, photo, enabled) 
                                      VALUES (?, ?, ?, ?, ?)
                                      ON DUPLICATE KEY UPDATE 
                                      name = VALUES(name), 
                                      price = VALUES(price), 
                                      photo = VALUES(photo), 
                                      enabled = VALUES(enabled)")) {
                    $stmt->bind_param(
                        "ssdsi",
                        $record['id'],
                        $record['name'],
                        $record['price'],
                        $record['photo'],
                        $record['enabled']
                    );
                    $success = $stmt->execute();
                    $stmt->close();

                    // Handle stock levels if merch was successfully saved
                    if ($success) {
                        foreach (['S', 'M', 'L', 'XL'] as $size) {
                            $qty = $record['stock'][$size] ?? 0;
                            if ($stockStmt = $conn->prepare("INSERT INTO merch_stock (merch_id, size, quantity) 
                                                       VALUES (?, ?, ?)
                                                       ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)")) {
                                $stockStmt->bind_param("ssi", $record['id'], $size, $qty);
                                $stockStmt->execute();
                                $stockStmt->close();
                            }
                        }
                    }
                }
            }

            echo json_encode([
                "success" => $success,
                "message" => $success ? "Record saved successfully" : "Failed to save record"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Database error: " . $e->getMessage()
            ]);
        }

        mysqli_close($conn);
    }
}
