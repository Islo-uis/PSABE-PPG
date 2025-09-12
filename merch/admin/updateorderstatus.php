<?php
header("Content-Type: application/json");



error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/dboperations.php';
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
// Get and sanitize inputs
$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$newStatus = isset($_POST['status']) ? trim($_POST['status']) : '';

if (!$orderId || $newStatus === '') {
    echo json_encode(["success" => false, "error" => "Missing order_id or status"]);
    exit;
}

// Only allow valid statuses
$allowedStatuses = [
    'pending',
    'payment under review',
    'payment verified',
    'fraudulent',
    'claimable',
    'completed',
    'cancelled',
    'refunded'
];

if (!in_array($newStatus, $allowedStatuses, true)) {
    echo json_encode(["success" => false, "error" => "Invalid status"]);
    exit;
}

// Update query
$stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "error" => $conn->error]);
    exit;
}

$stmt->bind_param("si", $newStatus, $orderId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Order #$orderId updated to '$newStatus'"]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}
?>