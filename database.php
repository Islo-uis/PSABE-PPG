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

// 7. Users table
$users = "
  CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255)      NOT NULL UNIQUE,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255)  NOT NULL,
    sex VARCHAR(10) NOT NULL,
    university VARCHAR(255) DEFAULT NULL,
    photo VARCHAR(255)      DEFAULT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
  );
";
if (! mysqli_query($conn, $users)) {
    die("Error creating users table: " . mysqli_error($conn));
}

$announcements = "CREATE table if not exists announcements (
    announcementID INT PRIMARY KEY AUTO_INCREMENT,
    header TEXT NOT NULL,
    description TEXT NOT NULL,
    orderr INT NOT NULL,
    photo TEXT NOT NULL,
    status INT NOT NULL,
    timeUploaded DATETIME)
";
if (! mysqli_query($conn, $announcements)) {
    die("Error creating users table: " . mysqli_error($conn));
}

$schedule = "CREATE table if not exists schedule (
    photo TEXT NOT NULL)
";
if (! mysqli_query($conn, $schedule)) {
    die("Error creating users table: " . mysqli_error($conn));
}


$merch = "CREATE table if not exists merch (
    merchID INT PRIMARY KEY AUTO_INCREMENT,
    merchName TEXT NOT NULL,
    hasSize INT NOT NULL,
    price FLOAT NOT NULL,
    photo TEXT NOT NULL,
    qty INT NOT NULL,
    qtyS INT NOT NULL,
    qtyM INT NOT NULL,
    qtyL INT NOT NULL,
    status INT NOT NULL)
";
if (! mysqli_query($conn, $merch)) {
    die("Error creating users table: " . mysqli_error($conn));
}


// ('ORD-2002005', 'Emma Svensson',     
// 'emma.svensson@example.co',  'REF-M4N5O6', 
//  23.75,   'Widget A, Widget C',               1,         'Not Confirmed',  '2025-07-14 16:22:59');
// // 8. Transactions table
// $transactions = "
//   CREATE TABLE IF NOT EXISTS transactions (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     order_number VARCHAR(100)    NOT NULL,
//     name VARCHAR(255)            NOT NULL,
//     email VARCHAR(255)           NOT NULL,
//     reference_no VARCHAR(100)    NOT NULL,
//     amount DECIMAL(10,2)         NOT NULL DEFAULT 0.00,
//     products TEXT                NOT NULL,
//     confirmed TINYINT(1)         NOT NULL DEFAULT 0,
//     status ENUM('Not Confirmed','Processing','Claimed')
//              NOT NULL DEFAULT 'Not Confirmed',
//     created_at TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP);
// ";
// if (! mysqli_query($conn, $transactions)) {
//     die("Error creating transactions table: " . mysqli_error($conn));
// }

function adminAccount()
{
    global $conn;

    // Check how many admin rows exist
    $sql = "SELECT COUNT(*) AS cnt FROM admin";
    $result = $conn->query($sql);

    if ($result && ($row = $result->fetch_assoc()) && $row['cnt'] == 0) {
        $user = "adminn";
        $pass = password_hash("password", PASSWORD_DEFAULT);

        // Prepare the INSERT
        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        if (! $stmt) {
            echo "Prepare failed: " . $conn->error;
            return;
        }

        // Bind parameters and execute
        $stmt->bind_param("ss", $user, $pass);
        if ($stmt->execute()) {
            // Admin created successfully
        } else {
            echo "Error creating admin: " . $stmt->error;
        }

        $stmt->close();
    } else if (!$result) {
        echo "Error checking admin table: " . $conn->error;
    }
}



mysqli_close($conn);
