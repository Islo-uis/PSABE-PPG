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


}
