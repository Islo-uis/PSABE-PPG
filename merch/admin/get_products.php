<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/dboperations.php';
$dbo = new dboperations();

try {
    $conn = $dbo->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$products = [];

// 1. Base product query
$sql = "SELECT p.prod_id, p.prod_name, p.prod_description, 
               p.prod_qty, p.prod_price, p.prod_status,
               pi.img_url1, pi.img_url2, pi.img_url3
        FROM products p
        LEFT JOIN product_images pi ON p.prod_id = pi.prod_id";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $img = !empty($row['img_url1']) ? $row['img_url1'] :
              (!empty($row['img_url2']) ? $row['img_url2'] :
              (!empty($row['img_url3']) ? $row['img_url3'] : "default.png"));

        $products[$row["prod_id"]] = [
            "prod_id"        => (int)$row["prod_id"],
            "prod_name"      => $row["prod_name"],
            "prod_description"=> $row["prod_description"],
            "prod_qty"       => (int)$row["prod_qty"],
            "prod_price"     => (float)$row["prod_price"],
            "prod_status"    => $row["prod_status"],
            "img1"            => $row['img_url1'],
            "img2"            => $row['img_url2'],
            "img3"            => $row['img_url3'],

            "total_sold"     => 0,  // default
            "total_sales"    => 0   // default
        ];
    }
    $result->free();
}

// 2. Sales query (only products with successful orders)
$salesSql = "
    SELECT 
        p.prod_id,
        SUM(od.item_qty) AS total_units_sold,
        SUM(od.line_total) AS total_sales
    FROM orderdetails od
    JOIN orders o 
        ON od.order_id = o.order_id
       AND o.order_status IN ('payment verified','claimable','completed')
    JOIN products p 
        ON od.prod_id = p.prod_id
    GROUP BY p.prod_id
";

if ($salesResult = $conn->query($salesSql)) {
    while ($srow = $salesResult->fetch_assoc()) {
        $pid = $srow["prod_id"];
        if (isset($products[$pid])) {
            $products[$pid]["total_sold"]  = (int)$srow["total_units_sold"];
            $products[$pid]["total_sales"] = (float)$srow["total_sales"];
        }
    }
    $salesResult->free();
}

echo json_encode(array_values($products)); // reset keys
