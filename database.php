<?php
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "abecon";
try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass);
} catch (mysqli_sql_exception) {
    echo "Could not Connect!";
}

//db creeattioonnn    
$dbName = "CREATE DATABASE IF NOT EXISTS abecon CHARACTER SET utf8 COLLATE utf8_general_ci";
if (mysqli_query($conn, $dbName)) {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
} else {
    echo "Error creating database: " . mysqli_error($conn);
}

$admin = "CREATE TABLE IF NOT EXISTS admin (
        username VARCHAR(171) NOT NULL PRIMARY KEY,
        password VARBINARY(500) NOT NULL
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
else{
    sched();
}


$merch = "CREATE table if not exists products (
    prod_id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    prod_name VARCHAR(160) NOT NULL,
    prod_description TEXT NULL,
    prod_qty SMALLINT UNSIGNED NOT NULL,
    prod_price DECIMAL(10,2) NOT NULL,
    prod_status ENUM('in_stock','sold_out’))
";
if (! mysqli_query($conn, $merch)) {
    die("Error creating merch table: " . mysqli_error($conn));
}



$prodimg = "CREATE TABLE product_images (
  img_id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  prod_id SMALLINT UNSIGNED ,
  img_url VARCHAR(255) NOT NULL,
  FOREIGN KEY (prod_id) REFERENCES products(prod_id) ON DELETE CASCADE
)";
if (! mysqli_query($conn, $prodimg)) {
    die("Error creating prodimg table: " . mysqli_error($conn));
}

$event = "CREATE table if not exists events (
    eventID INT PRIMARY KEY AUTO_INCREMENT,
    eventName TEXT NOT NULL,
    description TEXT NOT NULL,
    status INT NOT NULL,
    photo TEXT NOT NULL)
";
if (! mysqli_query($conn, $event)) {
    die("Error creating users table: " . mysqli_error($conn));
}

$eventSched = "CREATE table if not exists eventSched (
    schedID INT PRIMARY KEY AUTO_INCREMENT,
    eventID INT NOT NULL,
    description TEXT NOT NULL,
    venue TEXT NOT NULL,
    datee TEXT NOT NULL,
    timeStart TEXT NOT NULL,
    timeEnd TEXT NOT NULL,
    status TEXT NULL,
    FOREIGN KEY (eventID) REFERENCES events (eventID))
";
if (! mysqli_query($conn, $eventSched)) {
    die("Error creating users table: " . mysqli_error($conn));
}

$user = "CREATE table if not exists user (
    userID INT PRIMARY KEY AUTO_INCREMENT,
    firstName TEXT NOT NULL,
    lastName TEXT NOT NULL,
    email TEXT NOT NULL,
    university TEXT NOT NULL,
    nickname TEXT NOT NULL,
    password TEXT NOT NULL,
    idPic TEXT NOT NULL,
    status INT NOT NULL)
";
if (! mysqli_query($conn, $user)) {
    die("Error creating users table: " . mysqli_error($conn));
}

$transactions = "CREATE table if not exists preregtransaction (
    transactionID INT PRIMARY KEY AUTO_INCREMENT,
    userID INT NOT NULL,
    transactionType TEXT NOT NULL,
    firstWave INT NOT NULL,
    secondWave INT NOT NULL,
    thirdWave INT NOT NULL,
    transactionNum TEXT NOT NULL,
    picture TEXT NOT NULL,
    FOREIGN KEY (userID) REFERENCES user(userID))
";
if (! mysqli_query($conn, $transactions)) {
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
function sched()
{
    global $conn;

    // Check how many admin rows exist
    $sql = "SELECT COUNT(*) AS cnt FROM schedule";
    $result = $conn->query($sql);

    if ($result && ($row = $result->fetch_assoc()) && $row['cnt'] == 0) {
        $user = "miaw.jpg";

        // Prepare the INSERT
        $stmt = $conn->prepare("INSERT INTO schedule VALUES (?)");
        if (! $stmt) {
            echo "Prepare failed: " . $conn->error;
            return;
        }
        // Bind parameters and execute
        $stmt->bind_param("s", $user);
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
