<?php
header("Content-Type: application/json");
session_start(); //for trial purposes only, replace with actual session management


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


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['products'], $_POST['payment_refno'])) {
        echo json_encode([
            "success" => false,
            "message" => "Missing required fields"
        ]);
        exit;
    }
    // Sanitize buyer/payment info
    $buyer_id = $_SESSION['user_id'] ?? 1; // null; //replace with actual session user id
    $payment_refno = htmlspecialchars($_POST['payment_refno']);
    $payment_photo = null; // will be updated if file upload is used
    // $products = []; // will hold decoded product list

    $payment_photo = null;

    if (isset($_FILES['receipt-file']) && $_FILES['receipt-file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/uploads/receipts/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // Get extension
        $ext = pathinfo($_FILES['receipt-file']['name'], PATHINFO_EXTENSION);

        // TEMP name before we know order_id
        $tempName = time() . "_" . basename($_FILES['receipt-file']['name']);
        $tempTarget = $uploadDir . $tempName;
    } elseif (!empty($_POST['payment_photo'])) {
        $payment_photo = htmlspecialchars($_POST['payment_photo']);
    }

    // Decode product list
    $products = json_decode($_POST['products'], true);
    if (!$products || !is_array($products)) {
        die("Invalid products data.");
    }



    // --- Insert into orders table ---
    $stmt = $conn->prepare("
        INSERT INTO orders (buyer_id, payment_refno, payment_photo)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $buyer_id, $payment_refno, $payment_photo);

    if (!$stmt->execute()) {
        die("Error inserting order: " . $stmt->error);
    }
    $order_id = $stmt->insert_id;
    $stmt->close();

    //rename file to orderid-receipt.ext
    $newFileName = $order_id . "-receipt." . strtolower($ext);
    $finalPath = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES['receipt-file']['tmp_name'], $finalPath)) {
        $payment_photo = "uploads/receipts/" . $newFileName;

        $stmt = $conn->prepare("UPDATE orders SET payment_photo=? WHERE order_id=?");
        $stmt->bind_param("si", $newFileName, $order_id);


        $stmt->execute();
    }

    // --- Insert order details ---
    $stmtDetail = $conn->prepare(" INSERT INTO orderdetails (order_id, prod_id, item_qty) VALUES (?, ?, ?) ");
    foreach ($products as $prod) {
        $prod_id = intval($prod['id']);
        $qty = intval($prod['qty']);

        $stmtDetail->bind_param("iii", $order_id, $prod_id, $qty);

        if (!$stmtDetail->execute()) {
            die("Error inserting order: " . $stmtDetail->error);
            echo json_encode(["success" => false, "message" => "Error inserting order detail: " . $stmtDetail->error]);
            exit;
        }
    }


    echo json_encode([
        "success" => true,
        "order_id" => $order_id,
        "message" => "Order submitted successfully."
    ]);
}
