<?php

require_once __DIR__ . '/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$conn = Database::getInstance()->getConnection();



$admin = "CREATE TABLE IF NOT EXISTS admin (
        username VARCHAR(171) NOT NULL PRIMARY KEY,
        password VARBINARY(500) NOT NULL
        )";

if (mysqli_query($conn, $admin)) {
    adminAccount();
} else {
    echo "Error creating table: " . mysqli_error($conn);
}


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



$orders = "CREATE table if not exists orders (
    order_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT NOT NULL,
    order_status ENUM('pending','paid','fulfilled','cancelled','refunded') NOT NULL DEFAULT 'pending',
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    placed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    payment_refno VARCHAR(100) NULL,
    payment_photo VARCHAR(255) NULL,
    FOREIGN KEY (buyer_id) REFERENCES user(userID)
)";
if (! mysqli_query($conn, $orders)) {
    die("Error creating orders table: " . mysqli_error($conn));
}

$orderdetails = "CREATE table if not exists orderdetails (
    order_id INT UNSIGNED NOT NULL,
    prod_id  SMALLINT UNSIGNED NOT NULL,
    item_qty SMALLINT UNSIGNED NOT NULL CHECK (item_qty > 0),
    unit_price DECIMAL(10,2) NOT NULL AS (SELECT prod_price FROM products WHERE prod_id = prod_id),
    line_total DECIMAL(10,2) AS (item_qty * unit_price) STORED,
    PRIMARY KEY (order_id, prod_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (prod_id) REFERENCES products(prod_id)
)";
if (! mysqli_query($conn, $orderdetails)) {
    die("Error creating orderdetails table: " . mysqli_error($conn));
}   

$merch = "CREATE table if not exists products (
    prod_id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    prod_name VARCHAR(160) NOT NULL,
    prod_description TEXT NULL,
    prod_qty SMALLINT UNSIGNED NOT NULL,
    prod_price DECIMAL(10,2) NOT NULL,
    prod_status ENUM('in_stock','sold_out') 
    GENERATED ALWAYS AS (
      CASE 
        WHEN prod_qty > 0 THEN 'in_stock'
        ELSE 'sold_out'
      END
    ) STORED
)
";
if (! mysqli_query($conn, $merch)) {
    die("Error creating merch table: " . mysqli_error($conn));
}


$prodimg = "CREATE TABLE IF NOT EXISTS product_images (
    prod_id SMALLINT UNSIGNED,
    img_url1 VARCHAR(255),
    img_url2 VARCHAR(255),
    img_url3 VARCHAR(255),
    PRIMARY KEY (prod_id),
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
    wave TEXT NOT NULL,
    transactionNum TEXT NOT NULL,
    picture TEXT NOT NULL,
    FOREIGN KEY (userID) REFERENCES user(userID))
";
if (! mysqli_query($conn, $transactions)) {
    die("Error creating users table: " . mysqli_error($conn));
}

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

// //Create stored procedures
// $sp_addproducts = "

// CREATE PROCEDURE sp_addProductWithImages(
//     IN p_prod_name VARCHAR(160),
//     IN p_prod_description TEXT,
//     IN p_prod_qty SMALLINT UNSIGNED,
//     IN p_prod_price DECIMAL(10,2),
//     IN p_img_url1 VARCHAR(255),
//     IN p_img_url2 VARCHAR(255),
//     IN p_img_url3 VARCHAR(255),
//     OUT new_prod_id INT
// )
// BEGIN
//     DECLARE new_prod_id INT;
//     DECLARE p_prod_status ENUM('in_stock', 'sold_out');
    
//     -- Set product status based on quantity
//     IF p_prod_qty = 0 THEN
//         SET p_prod_status = 'sold_out';
//     ELSE
//         SET p_prod_status = 'in_stock';
//     END IF;

//     -- Insert the product into the products table
//     INSERT INTO products (prod_name, prod_description, prod_qty, prod_price, prod_status)
//     VALUES (p_prod_name, p_prod_description, p_prod_qty, p_prod_price, p_prod_status);
    
//     -- Get the last inserted prod_id
//     SET new_prod_id = LAST_INSERT_ID();
    
//     INSERT INTO product_images (prod_id, p_img_url1, p_img_url2, p_img_url3)
//     VALUES (
//         new_prod_id, 
//         p_img_url1,  -- img_url1 is required
//         IFNULL(p_img_url2, NULL),  -- img_url2 is optional
//         IFNULL(p_img_url3, NULL)   -- img_url3 is optional
//     );

// END;
// ";
// if (! mysqli_query($conn, $sp_addproducts)) {
//     die("Error creating stored procedure: " . mysqli_error($conn));
// }



?>
