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

    if ($_GET["action"] == 'addAnnouncement') {
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

            $header = $_POST['aa-header'];
            $description = $_POST['aa-desc'];
            $imageName = $_FILES['aa-photo']['name'];
            $imageTmpName = $_FILES['aa-photo']['tmp_name'];
            $imageSize = $_FILES['aa-photo']['size'];
            $imageError = $_FILES['aa-photo']['error'];
            $imageType = $_FILES['aa-photo']['type'];

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
            $count = 0;
            if ($stmt = $conn->prepare("SELECT COUNT(*) FROM announcements")) {
                $stmt->execute();
                $stmt->bind_result($count);
                if ($stmt->fetch()) {
                    $count++;
                }
                $stmt->close();
            }

            $fileNameNew = uniqid('', true) . "." . $imgActualExt;
            $fileDestination = '../../photos/announcement/' . $fileNameNew;

            if (!move_uploaded_file($imageTmpName, $fileDestination)) {
                throw new Exception('Failed to move uploaded file.');
            }
            // Prepare statement for announcements
            if ($stmt = $conn->prepare("INSERT INTO announcements (announcementID, header, description, orderr, photo, status, timeUploaded) 
                                      VALUES (NULL, ?, ?, $count, ?, 1, NOW())")) {
                $stmt->bind_param(
                    "sss",
                    $header,
                    $description,
                    $fileNameNew
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

    if ($_GET["action"] == 'getMerch') {
        header('Content-Type: application/json');
        $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

        if (!$conn) {
            die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
        }
        $sql = "SELECT * from merch";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $merch = [];

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $merch[] = [
                    "id" => $row['merchID'],
                    "name" => $row['merchName'],
                    "hasSize" => $row['hasSize'],
                    "price" => $row['price'],
                    "photo" => $row['photo'],
                    "qty" => $row['qty'],
                    "qtyS" => $row['qtyS'],
                    "qtyM" => $row['qtyM'],
                    "qtyL" => $row['qtyL'],
                    "status" => $row['status']
                ];
            }
        }


        mysqli_close($conn);
        echo json_encode([
            "merch" => $merch
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

    if ($_GET["action"] == 'getMerchData') {
        header('Content-Type: application/json');
        $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
        $id = $_POST['id'];
        if (!$conn) {
            die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
        }
        $sql = "SELECT * from merch where merchID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $name = "";
        $hasSize = "";
        $price = "";
        $photo = "";
        $qty = "";
        $qtyS = "";
        $qtyM = "";
        $qtyL = "";
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $name = $row['merchName'];
                $hasSize = $row['hasSize'];
                $price = $row['price'];
                $photo = $row['photo'];
                $qty = $row['qty'];
                $qtyS = $row['qtyS'];
                $qtyM = $row['qtyM'];
                $qtyL = $row['qtyL'];
            }
        }


        mysqli_close($conn);
        echo json_encode([
            "name" => $name,
            "hasSize" => $hasSize,
            "photo" => $photo,
            "price" => $price,
            "qty" => $qty,
            "qtyS" => $qtyS,
            "qtyM" => $qtyM,
            "qtyL" => $qtyL
        ]);
    }

    if ($_GET["action"] == 'getSched') {
        header('Content-Type: application/json');
        $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
        if (!$conn) {
            die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
        }
        $sql = "SELECT photo from schedule";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $photo = "";
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $photo = $row['photo'];
            }
        }


        mysqli_close($conn);
        echo json_encode([
            "photo" => $photo
        ]);
    }

    if ($_GET["action"] == 'changeSched') {
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
            $imageName = $_FILES['sched-file']['name'];
            $imageTmpName = $_FILES['sched-file']['tmp_name'];
            $imageSize = $_FILES['sched-file']['size'];
            $imageError = $_FILES['sched-file']['error'];
            $imageType = $_FILES['sched-file']['type'];

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


            if ($stmt = $conn->prepare("SELECT photo FROM schedule")) {
                $stmt->execute();
                $stmt->bind_result($oldPhoto);
                if ($stmt->fetch()) {
                    $oldFilePath = '../../photos/schedule/' . $oldPhoto;
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath); // Delete the old file
                    }
                }
                $stmt->close();
            }


            $fileNameNew = uniqid('', true) . "." . $imgActualExt;
            $fileDestination = '../../photos/schedule/' . $fileNameNew;

            if (!move_uploaded_file($imageTmpName, $fileDestination)) {
                throw new Exception('Failed to move uploaded file.');
            }
            // Prepare statement for announcements
            if ($stmt = $conn->prepare("UPDATE schedule set photo = ?")) {
                $stmt->bind_param(
                    "s",
                    $fileNameNew
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

    if ($_GET["action"] == 'changeAnnouncementOrder') {
        header('Content-Type: application/json');
        $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
        $id = $_POST['id'];
        $status = $_POST['status'];
        if (!$conn) {
            die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
        }
        $stmt = $conn->prepare("SELECT orderr FROM announcements WHERE announcementID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($currentOrder);
        $stmt->fetch();
        $stmt->close();

        if (!$currentOrder) {
            echo json_encode(["error" => "Announcement not found"]);
            exit;
        }

        if ($status == "up") {
            // 2. Find announcement above
            $stmt = $conn->prepare("SELECT announcementID, orderr FROM announcements WHERE orderr < ? ORDER BY orderr DESC LIMIT 1");
            $stmt->bind_param("i", $currentOrder);
        } else {
            // down
            $stmt = $conn->prepare("SELECT announcementID, orderr FROM announcements WHERE orderr > ? ORDER BY orderr ASC LIMIT 1");
            $stmt->bind_param("i", $currentOrder);
        }
        $stmt->execute();
        $stmt->bind_result($swapId, $swapOrder);
        $stmt->fetch();
        $stmt->close();

        if (!$swapId) {
            echo json_encode(["success" => false, "message" => "No swap target found"]);
            exit;
        }

        // 3. Swap orders
        $stmt = $conn->prepare("UPDATE announcements SET orderr = ? WHERE announcementID = ?");
        $stmt->bind_param("ii", $swapOrder, $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE announcements SET orderr = ? WHERE announcementID = ?");
        $stmt->bind_param("ii", $currentOrder, $swapId);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true]);

        mysqli_close($conn);
    }

    if ($_GET["action"] == 'changeAnnouncementStatus') {
        header('Content-Type: application/json');
        $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
        $id = $_POST['id'];
        $status = $_POST['status'];
        if (!$conn) {
            die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
        }


        $stmt = $conn->prepare("UPDATE announcements SET status = ? WHERE announcementID = ?");
        $stmt->bind_param("ii", $status, $id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true]);

        mysqli_close($conn);
    }

    if ($_GET["action"] == 'addMerch') {
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
            $size = 0;
            if (isset($_POST['sizeornot'])) {
                if ($_POST['sizeornot'] === 'with') {
                    $size = 1;
                }
            }
            $name = $_POST['am-name'];
            $price = $_POST['am-price'];
            $qty = $_POST['am-qty'];
            $sqty = $_POST['am-sqty'];
            $mqty = $_POST['am-mqty'];
            $lqty = $_POST['am-lqty'];
            $imageName = $_FILES['am-photo']['name'];
            $imageTmpName = $_FILES['am-photo']['tmp_name'];
            $imageSize = $_FILES['am-photo']['size'];
            $imageError = $_FILES['am-photo']['error'];
            $imageType = $_FILES['am-photo']['type'];

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
            $fileDestination = '../../photos/merch/' . $fileNameNew;

            if (!move_uploaded_file($imageTmpName, $fileDestination)) {
                throw new Exception('Failed to move uploaded file.');
            }
            // Prepare statement for announcements
            if ($stmt = $conn->prepare("INSERT INTO merch values (NULL, ?, ?, ?, ?, ?, ?, ?, ?, 1)")) {
                $stmt->bind_param(
                    "ssssssss",
                    $name,
                    $size,
                    $price,
                    $fileNameNew,
                    $qty,
                    $sqty,
                    $mqty,
                    $lqty
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

    if ($_GET["action"] == 'editMerch') {
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
            $size = 0;
            $qty = 0;
            $sqty = 0;
            $mqty = 0;
            $lqty = 0;

            if (isset($_POST['em-sizeornot'])) {
                if ($_POST['em-sizeornot'] === 'with') {
                    $size = 1;
                    $sqty = $_POST['em-sqty'];
                    $mqty = $_POST['em-mqty'];
                    $lqty = $_POST['em-lqty'];
                } else {
                    $qty = $_POST['em-qty'];
                }
            }
            $name = $_POST['em-name'];
            $price = $_POST['em-price'];
            $id = $_POST['em-id'];
            $imageName = $_FILES['em-photo']['name'];
            $imageTmpName = $_FILES['em-photo']['tmp_name'];
            $imageSize = $_FILES['em-photo']['size'];
            $imageError = $_FILES['em-photo']['error'];
            $imageType = $_FILES['em-photo']['type'];

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

            if ($stmt = $conn->prepare("SELECT photo FROM merch WHERE merchID = ?")) {
                $stmt->bind_param("s", $id);
                $stmt->execute();
                $stmt->bind_result($oldPhoto);
                if ($stmt->fetch()) {
                    $oldFilePath = '../../photos/merch/' . $oldPhoto;
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath); // Delete the old file
                    }
                }
                $stmt->close();
            }


            $fileNameNew = uniqid('', true) . "." . $imgActualExt;
            $fileDestination = '../../photos/merch/' . $fileNameNew;

            if (!move_uploaded_file($imageTmpName, $fileDestination)) {
                throw new Exception('Failed to move uploaded file.');
            }
            // Prepare statement for announcements
            if ($stmt = $conn->prepare("UPDATE merch set merchName = ?, hasSize = ?, price = ?, photo = ?, qty = ?, qtyS = ?, qtyM = ?, qtyL = ? where merchID = ?")) {
                $stmt->bind_param(
                    "sssssssss",
                    $name,
                    $size,
                    $price,
                    $fileNameNew,
                    $qty,
                    $sqty,
                    $mqty,
                    $lqty,
                    $id
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

    if ($_GET["action"] == 'changeMerchStatus') {
        header('Content-Type: application/json');
        $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
        $id = $_POST['id'];
        $status = $_POST['status'];
        if (!$conn) {
            die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
        }


        $stmt = $conn->prepare("UPDATE merch SET status = ? WHERE merchID = ?");
        $stmt->bind_param("ii", $status, $id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true]);

        mysqli_close($conn);
    }
}
