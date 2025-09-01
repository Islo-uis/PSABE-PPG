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

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["success" => false, "message" => "Method not allowed"]);
                exit;
            }

            $formData = $_POST['formData'];

            if ($type === 'announcement') {
                $header = $_POST['announcement-header'];
                $description = $_POST['announcement-desc'];
                $imageName = $_FILES['card-photo']['name'];
                $imageTmpName = $_FILES['card-photo']['tmp_name'];
                $imageSize = $_FILES['card-photo']['size'];
                $imageError = $_FILES['card-photo']['error'];
                $imageType = $_FILES['card-photo']['type'];

                $imgExt = explode('.', $imageName);
                $imgActualExt = strtolower(end($imgExt));

                $allowed = array('jpg', 'jpeg', 'png');

                if (!in_array($imgActualExt, $allowed)) {
                    throw new Exception('Invalid file type. Only JPG, JPEG, PNG are allowed.');
                }

                if ($imageError !== 0) {
                    throw new Exception('File upload error.');
                }

                if ($imageSize >= 5000000) {
                    throw new Exception('File size too large. Maximum 500KB allowed.');
                }

                $fileNameNew = uniqid('', true) . "." . $imgActualExt;
                $fileDestination = '../images/announcements/' . $fileNameNew;

                if (!move_uploaded_file($imageTmpName, $fileDestination)) {
                    throw new Exception('Failed to move uploaded file.');
                }
                // Prepare statement for announcements
                if ($stmt = $conn->prepare("INSERT INTO announcements (announcementID, header, description, orderr, photo, status, timeUploaded) 
                                      VALUES (NULL, ?, ?, 1, ?, 1, NOW())")) {
                    $stmt->bind_param(
                        "sss",
                        $header,
                        $description,
                        $fileNameNew
                    );
                    $success = $stmt->execute();
                    $stmt->close();
                }
            } elseif ($type === 'merch') {
                // Prepare statement for merch

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
