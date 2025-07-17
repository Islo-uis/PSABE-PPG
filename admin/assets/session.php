<?php

if ($_GET['action'] == "checkIfLoggedIn") {
    session_start();
    $loggedIn = false;
    if (isset($_SESSION['Username'])) {
        $loggedIn = true;
    }
    header('Content-Type: application/json');
    echo json_encode($loggedIn);
}


if ($_GET['action'] == "checkIfLoggedOut") {
    session_start();
    $user = "";
    $loggedOut = false;
    if (!isset($_SESSION['Username'])) {
        $loggedOut = true;
    } else {
        $user = $_SESSION['Username'];
    }
    header('Content-Type: application/json');
    echo json_encode($loggedOut);
}


if ($_GET['action'] == "logOut") {
    session_start();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
