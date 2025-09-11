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


$products = [];

$sql = "SELECT p.prod_id, p.prod_name, p.prod_description, 
               p.prod_qty, p.prod_price, p.prod_status,
               pi.img_url1, pi.img_url2, pi.img_url3
        FROM products p
        LEFT JOIN product_images pi ON p.prod_id = pi.prod_id";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        // Pick first available image or fallback
        $img = !empty($row['img_url1']) ? $row['img_url1'] :
               (!empty($row['img_url2']) ? $row['img_url2'] :
               (!empty($row['img_url3']) ? $row['img_url3'] : "default.png"));

        $products[] = [
            "prod_id"      => (int)$row["prod_id"],
            "prod_name"    => $row["prod_name"],
            "prod_description" => $row["prod_description"],
            "prod_qty"     => (int)$row["prod_qty"],
            "prod_price"   => (float)$row["prod_price"],
            "prod_status"  => $row["prod_status"],
            "img1"          => $row['img_url1'],
            "img2"          => $row['img_url2'],
            "img3"          => $row['img_url3']
        ];
    }
    $result->free();
} else {
    echo json_encode(["error" => $conn->error]);
    exit;
}

echo json_encode($products);
?>