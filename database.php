<?php
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "psabe";
try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass);
} catch (mysqli_sql_exception) {
    echo "Could not Connect!";
}

//db creeattioonnn    
$dbName = "CREATE DATABASE IF NOT EXISTS psabe";
if (mysqli_query($conn, $dbName)) {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
} else {
    echo "Error creating database: " . mysqli_error($conn);
}

$admin = "CREATE TABLE IF NOT EXISTS admin (
        username VARCHAR(750) NOT NULL PRIMARY KEY,
        password VARBINARY(750) NOT NULL
        )";

if (mysqli_query($conn, $admin)) {
    adminAccount();
} else {
    echo "Error creating table: " . mysqli_error($conn);
}


// $admin = "CREATE TABLE IF NOT EXISTS admin (
//         username VARCHAR(750) NOT NULL PRIMARY KEY,
//         password VARBINARY(750) NOT NULL
//         )";

// if (mysqli_query($conn, $admin)) {
//     adminAccount();
// } else {
//     echo "Error creating table: " . mysqli_error($conn);
// }



function adminAccount()
{
    global $conn;

    // Query to check if admin account exists
    $sql = "SELECT * FROM admin";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        $createAdmin = "INSERT INTO admin values ('admin', 'password') ";
        if (mysqli_query($conn, $createAdmin)) {
        } else {
            echo "Error creating table: " . mysqli_error($conn);
        }
    }
}


mysqli_close($conn);
