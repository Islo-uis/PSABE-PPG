<?php
include "../../database.php";
session_start();
if (isset($_GET["action"])) {

    if ($_GET["action"] == 'submitRegistration') {
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

            $university = $_POST['university'];
            $wave = $_POST['wave'];
            $email = $_POST['email'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $nickname = $_POST['nickname'];
            $password = $_POST['password'];
            $transaction_num = $_POST['transaction-num'];

            $pimageName = $_FILES['profile-input']['name'];
            $pimageTmpName = $_FILES['profile-input']['tmp_name'];
            $pimageSize = $_FILES['profile-input']['size'];
            $pimageError = $_FILES['profile-input']['error'];
            $pimageType = $_FILES['profile-input']['type'];

            $pimgExt = explode('.', $pimageName);
            $pimgActualExt = strtolower(end($pimgExt));

            $allowed = array('jpg', 'jpeg', 'png');

            if (!in_array($pimgActualExt, $allowed)) {
                throw new Exception('Invalid file type. Only JPG, JPEG, PNG are allowed.');
            }

            if ($pimageError !== 0) {
                throw new Exception('File upload error.');
            }

            if ($pimageSize >= 5000000) {
                throw new Exception('File size too large. Maximum 500KB allowed.');
            }

            $pfileNameNew = uniqid('', true) . "." . $pimgActualExt;
            $pfileDestination = '../../photos/user/' . $pfileNameNew;

            if (!move_uploaded_file($pimageTmpName, $pfileDestination)) {
                throw new Exception('Failed to move uploaded file.');
            }

            $rimageName = $_FILES['receipt-input']['name'];
            $rimageTmpName = $_FILES['receipt-input']['tmp_name'];
            $rimageSize = $_FILES['receipt-input']['size'];
            $rimageError = $_FILES['receipt-input']['error'];
            $rimageType = $_FILES['receipt-input']['type'];

            $rimgExt = explode('.', $rimageName);
            $rimgActualExt = strtolower(end($rimgExt));

            $allowed = array('jpg', 'jpeg', 'png');

            if (!in_array($rimgActualExt, $allowed)) {
                throw new Exception('Invalid file type. Only JPG, JPEG, PNG are allowed.');
            }

            if ($rimageError !== 0) {
                throw new Exception('File upload error.');
            }

            if ($rimageSize >= 5000000) {
                throw new Exception('File size too large. Maximum 500KB allowed.');
            }

            $rfileNameNew = uniqid('', true) . "." . $rimgActualExt;
            $rfileDestination = '../../photos/pre_reg_transaction/' . $rfileNameNew;

            if (!move_uploaded_file($rimageTmpName, $rfileDestination)) {
                throw new Exception('Failed to move uploaded file.');
            }

            // Prepare statement for announcements
            if ($stmt = $conn->prepare("INSERT INTO user values (NULL, ?, ?, ?, ?, ?, ?, ?, 1)")) {
                $stmt->bind_param(
                    "sssssss",
                    $first_name,
                    $last_name,
                    $email,
                    $university,
                    $nickname,
                    $password,
                    $pfileNameNew
                );
                $success = $stmt->execute();
                $stmt->close();
            }

            $id = 0;

            $sql = "SELECT userID FROM user where email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $id = $row['userID'];
                }
            }

            // Prepare statement for announcements
            if ($stmt = $conn->prepare("INSERT INTO preregtransaction values (NULL, ?, ?, ?, ?)")) {
                $stmt->bind_param(
                    "ssss",
                    $id,
                    $wave,
                    $transaction_num,
                    $rfileNameNew
                );
                $success = $stmt->execute();
                $stmt->close();
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

    if ($_GET["action"] == 'editAnnouncement') {
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

            $id = $_POST['ea-id'];
            $header = $_POST['ea-header'];
            $description = $_POST['ea-desc'];

            $hasNewPhoto = isset($_FILES['ea-photo']) && $_FILES['ea-photo']['error'] === UPLOAD_ERR_OK;

            if ($hasNewPhoto) {
                $imageName = $_FILES['ea-photo']['name'];
                $imageTmpName = $_FILES['ea-photo']['tmp_name'];
                $imageSize = $_FILES['ea-photo']['size'];
                $imageError = $_FILES['ea-photo']['error'];

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
                    throw new Exception('File size too large. Maximum 5MB allowed.');
                }

                if ($stmt = $conn->prepare("SELECT photo FROM announcements where announcementID = ?")) {
                    $stmt->bind_param("s", $id);
                    $stmt->execute();
                    $stmt->bind_result($oldPhoto);
                    if ($stmt->fetch()) {
                        $oldFilePath = '../../photos/announcement/' . $oldPhoto;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath); // Delete the old file
                        }
                    }
                    $stmt->close();
                }

                $fileNameNew = uniqid('', true) . "." . $imgActualExt;
                $fileDestination = '../../photos/announcement/' . $fileNameNew;

                if (!move_uploaded_file($imageTmpName, $fileDestination)) {
                    throw new Exception('Failed to move uploaded file.');
                }

                // Update with photo
                $stmt = $conn->prepare("UPDATE announcements SET header = ?, description = ?, photo = ? WHERE announcementID = ?");
                $stmt->bind_param("ssss", $header, $description, $fileNameNew, $id);
            } else {
                // Update without touching photo
                $stmt = $conn->prepare("UPDATE announcements SET header = ?, description = ? WHERE announcementID = ?");
                $stmt->bind_param("sss", $header, $description, $id);
            }

            $success = $stmt->execute();
            $stmt->close();

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

    if ($_GET["action"] == 'getAnnouncements') {
        header('Content-Type: application/json');
        $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

        if (!$conn) {
            die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
        }
        $sql = "SELECT * from announcements";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $announcements = [];

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $announcements[] = [
                    "desc" => $row['description'],
                    "status" => $row['status'],
                    "header" => $row['header'],
                    "order" => $row['orderr'],
                    "uploadedTime" => $row['timeUploaded'],
                    "id" => $row['announcementID'],
                    "photo" => $row['photo']
                ];
            }
        }


        mysqli_close($conn);
        echo json_encode([
            "announcements" => $announcements
        ]);
    }

    if ($_GET["action"] == 'getAnnouncementData') {
        header('Content-Type: application/json');
        $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
        $id = $_POST['id'];
        if (!$conn) {
            die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
        }
        $sql = "SELECT * from announcements where announcementID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $header = "";
        $desc = "";
        $photo = "";
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $header = $row['header'];
                $desc = $row['description'];
                $photo = $row['photo'];
            }
        }


        mysqli_close($conn);
        echo json_encode([
            "header" => $header,
            "desc" => $desc,
            "photo" => $photo
        ]);
    }
}
