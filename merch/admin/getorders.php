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


$sql = "SELECT 
    o.order_id,
    CONCAT(u.firstName, ' ', u.lastName) AS full_name,
    u.email,
    u.nickname AS mobile_number,  -- replace if you have an actual mobile field
    o.placed_at AS order_datetime,
    o.total_amount,
    o.payment_refno,
    o.payment_photo,
    o.order_status,
    SUM(od.item_qty) AS total_items,  -- total quantity of all items
    GROUP_CONCAT(CONCAT(p.prod_name, ' (x', od.item_qty, ')') SEPARATOR ', ') AS item_summary
FROM orders o
JOIN user u 
    ON o.buyer_id = u.userID
LEFT JOIN orderdetails od 
    ON o.order_id = od.order_id
LEFT JOIN products p
    ON od.prod_id = p.prod_id
GROUP BY 
    o.order_id";

$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            // make order_id clickable for frontend
            "<a href='ordersummary.php?orderid=" . $row['order_id'] . "'>#" . $row['order_id'] . "</a>",
            "<div>
                <p class='font-medium'>" . htmlspecialchars($row['full_name']) . "</p>
                <p class='text-gray-500 text-xs'>" . htmlspecialchars($row['email']) . "</p>
                <p class='text-gray-500 text-xs'>" . htmlspecialchars($row['mobile_number']) . "</p>
            </div>",

            $row['order_datetime'],
            '₱' . number_format($row['total_amount'], 2),

            (isset($row['payment_refno']) && $row['payment_refno'] !== '')
                ? 'Ref. No. ' . htmlspecialchars($row['payment_refno'])
                : '',

            $row['payment_photo']
                ? "<img src='/uploads/receipts/" . htmlspecialchars($row['payment_photo']) . "' class='h-10'>"
                : '',
            $row['order_status'],
            "<div>
                <p class='font-medium'>" . htmlspecialchars($row['total_items'] ) . "</p>
                <p class='text-gray-500 text-xs'>" . htmlspecialchars($row['item_summary']) . "</p>
            </div>",
            "<button class='px-2 py-1 bg-blue-500 text-white rounded'>Edit</button>"
        ];
    }
}


echo json_encode(["data" => $data]);
