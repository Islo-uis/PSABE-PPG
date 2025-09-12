<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config/dboperations.php';
$dbo = new dboperations();

try {
    $conn = $dbo->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

if (!isset($_GET['orderid'])) {
    echo json_encode(["success" => false, "message" => "Missing order ID"]);
    exit;
}

$orderId = intval($_GET['orderid']);

$sql = "SELECT order_id, buyer_id, order_status, total_amount, DATE_FORMAT(placed_at, '%b %e, %Y %l:%i %p') AS placed_at, payment_refno, payment_photo FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(["success" => true, "order" => $row]);
} else {
    echo json_encode(["success" => false, "message" => "Order not found"]);
}
?>