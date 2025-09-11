<?php
header("Content-Type: application/json");



error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/dboperations.php';
$dbo = new dboperations();

// Create an instance of the dboperations class
try {
    $conn = $dbo->getConnection();
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}



// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve form data and sanitize it
    $buyer_id = htmlspecialchars($_POST['buyer_id']);
    $payment_refno = htmlspecialchars($_POST['payment_refno']);
    $payment_photo = htmlspecialchars($_POST['payment_photo']);
//array of products
    $products = json_decode($_POST['products'], true);
    
    // Insert into orders table
//    CREATE table if not exists orders (
//     order_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
//     buyer_id INT NOT NULL,
//     order_status ENUM('pending','paid','fulfilled','cancelled','refunded') NOT NULL DEFAULT 'pending',
//     total_amount DECIMAL(10,2) DEFAULT 0.00,
//     placed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//     payment_refno VARCHAR(100) NULL,
//     payment_photo VARCHAR(255) NULL,
//     FOREIGN KEY (buyer_id) REFERENCES user(userID)
    
    //Insert into orderdetails table for each product

    //CREATE table if not exists orderdetails (
    // order_id INT UNSIGNED NOT NULL,
    // prod_id  SMALLINT UNSIGNED NOT NULL,
    // item_qty SMALLINT UNSIGNED NOT NULL CHECK (item_qty > 0),
    // unit_price DECIMAL(10,2) NOT NULL AS (SELECT prod_price FROM products WHERE prod_id = prod_id),
    // line_total DECIMAL(10,2) AS (item_qty * unit_price) STORED,
    // PRIMARY KEY (order_id, prod_id),
    // FOREIGN KEY (order_id) REFERENCES orders(order_id),
    // FOREIGN KEY (prod_id) REFERENCES products(prod_id)