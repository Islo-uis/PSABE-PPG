<?php

include "../../../database.php";
session_start();

if ($_GET["action"] == 'getUsers') {
    header('Content-Type: application/json');
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

    if (!$conn) {
        die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
    }

    $univ = $_POST['university'];
    $sql = "";
    if ($univ == "all") {
        $sql = "SELECT * from user";
    } else {
        $sql = "SELECT * from user where university = '$univ'";
    }

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $users = [];

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = [
                "id" => $row['userID'],
                "first" => $row['firstName'],
                "last" => $row['lastName'],
                "email" => $row['email'],
                "univ" => $row['university'],
                "nickname" => $row['nickname'],
                "pic" => $row['idPic'],
                "status" => $row['status']
            ];
        }
    }


    mysqli_close($conn);
    echo json_encode([
        "users" => $users
    ]);
}


if ($_GET["action"] == 'getUserData') {
    header('Content-Type: application/json');
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

    if (!$conn) {
        die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
    }

    $id = $_POST['id'];
    $sql = "SELECT user.*, transactionNum, transactionType, firstWave, secondWave, thirdWave, picture from user inner join preregtransaction on user.userID = preregtransaction.userID where user.userID = '$id'";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $first = "";
    $last = "";
    $nickname = "";
    $email = "";
    $univ = "";
    $pic = "";
    $paymentType = "";
    $fWave = "";
    $sWave = "";
    $tWave = "";
    $transactionNum = "";
    $receipt = "";

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $first = $row['firstName'];
            $last = $row['lastName'];
            $nickname =  $row['nickname'];
            $email =  $row['email'];
            $univ =  $row['university'];
            $pic =  $row['idPic'];
            $paymentType = $row['transactionType'];
            $fWave = $row['firstWave'];
            $sWave = $row['secondWave'];
            $tWave = $row['thirdWave'];
            $transactionNum =  $row['transactionNum'];
            $receipt =  $row['picture'];
        }
    }


    mysqli_close($conn);
    echo json_encode([
        "first" => $first,
        "last" => $last,
        "nickname" => $nickname,
        "email" => $email,
        "university" => $univ,
        "transactionNum" => $transactionNum,
        "receipt" => $receipt,
        "paymentType" => $paymentType,
        "fWave" => $fWave,
        "sWave" => $sWave,
        "tWave" => $tWave,
        "photo" => $pic
    ]);
}

if ($_GET["action"] == 'changePayment') {
    header('Content-Type: application/json');
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
    $id = $_POST['id'];
    $first = $_POST['first'];
    $second = $_POST['second'];
    $third = $_POST['third'];
    if (!$conn) {
        die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
    }


    $stmt = $conn->prepare("UPDATE preregtransaction SET firstWave = ?, secondWave = ?, thirdWave = ? WHERE userID = ?");
    $stmt->bind_param("iiii", $first, $second, $third, $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => true]);

    mysqli_close($conn);
}
