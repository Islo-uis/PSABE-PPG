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
    DATE_FORMAT(o.placed_at, '%b %e, %Y %l:%i %p') AS order_datetime,
    o.total_amount,
    o.payment_refno,
    o.payment_photo,
    o.order_status,
    SUM(od.item_qty) AS total_items,  -- total quantity of all items
    GROUP_CONCAT(CONCAT('(x', od.item_qty, ') ', p.prod_name) SEPARATOR ' <br> ') AS item_summary
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

$statuses = [
    'pending',
    'payment under review',
    'payment verified',
    'fraudulent',
    'claimable',
    'completed',
    'cancelled',
    'refunded'
];

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        // Find current status index
        $currentIndex = array_search($row['order_status'], $statuses);

        $statusOptions = "";
        foreach ($statuses as $i => $s) {
            $selected = ($row['order_status'] === $s) ? "selected" : "";
            // disable if status index is less than current
            $disabled = ($i < $currentIndex) ? "disabled" : "";
            $statusOptions .= "<option value='" . htmlspecialchars($s) . "' $selected $disabled>" . ucfirst($s) . "</option>";
        }

        $orderStatusSelect = "
    <select name='status' data-order-id='" . (int)$row['order_id'] . "'
        class='bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block  p-2.5'>
        $statusOptions
    </select>
";




        $data[] = [
            "<a href='ordersummary.php?orderid=" . $row['order_id'] . "'>#" . $row['order_id'] . "</a>",

            "<div>
                <p class='font-medium'>" . htmlspecialchars($row['full_name']) . "</p>
                <p class='text-gray-500 text-xs'>" . htmlspecialchars($row['email']) . "</p>
                <p class='text-gray-500 text-xs'>" . htmlspecialchars($row['mobile_number']) . "</p>
            </div>",

            htmlspecialchars($row['order_datetime']),
            '₱' . number_format($row['total_amount'], 2),
            $row['payment_refno'] ? 'Ref. No. ' . htmlspecialchars($row['payment_refno']) : '',
            $row['payment_photo']
                ? "<img data-modal-target='receipt-modal'
                    data-modal-toggle='receipt-modal'
                    data-img-src='../uploads/receipts/" . htmlspecialchars($row['payment_photo']) . "' 
                    data-refno='" . htmlspecialchars($row['payment_refno']). "'
                    src='../uploads/receipts/" . htmlspecialchars($row['payment_photo']) . "' class='h-10 cursor-pointer' >"
                                    : '',
                
            $orderStatusSelect,
            "<div>
                <p class='font-medium'>" . htmlspecialchars($row['total_items']) . "</p>
                <p class='text-gray-500 text-xs'>" . $row['item_summary'] . "</p>
            </div>"
        ];
    }
}

echo json_encode(["data" => $data]);
