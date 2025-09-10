<?php

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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {


// Collect form data
$prod_name        = $_POST['prod_name'] ?? '';
$prod_price       = $_POST['prod_price'] ?? 0;
$prod_qty         = $_POST['prod_qty'] ?? 0;
$prod_description = $_POST['prod_description'] ?? '';

$status = ($prod_qty > 0) ? "in_stock" : "sold_out";

// Check if at least 1 image uploaded
if (empty($_FILES['files']['name'][0])) {
    die("Error: You must upload at least one product image.");
}

// Insert product first (without photo columns yet)
$stmt = $conn->prepare("INSERT INTO products 
    (prod_name, prod_description, prod_price, prod_qty, prod_status) 
    VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssdis", $prod_name, $prod_description, $prod_price, $prod_qty, $status);

if (!$stmt->execute()) {
    die("DB Error: " . $stmt->error);
}
$product_id = $conn->insert_id; // last inserted product id
$stmt->close();

// Upload directory
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/public/uploads/productimages/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle uploaded images
$photo1 = $photo2 = $photo3 = null;

foreach ($_FILES['files']['name'] as $index => $name) {
    if ($index > 2) break; // only 3 max

    $tmpName = $_FILES['files']['tmp_name'][$index];
    $error   = $_FILES['files']['error'][$index];

    if ($error === UPLOAD_ERR_OK) {
        // Force .jpeg extension
        $newName = $product_id . "_" . ($index + 1) . ".jpeg";
        $targetPath = $uploadDir . $newName;

        // Convert to jpeg (safer than just renaming)
        $imageInfo = getimagesize($tmpName);
        if ($imageInfo !== false) {
            switch ($imageInfo['mime']) {
                case 'image/png':
                    $image = imagecreatefrompng($tmpName);
                    imagejpeg($image, $targetPath, 90);
                    imagedestroy($image);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($tmpName);
                    imagejpeg($image, $targetPath, 90);
                    imagedestroy($image);
                    break;
                case 'image/jpeg':
                default:
                    move_uploaded_file($tmpName, $targetPath);
                    break;
            }
        }

        // Assign to variables
        if ($index == 0) $photo1 = $newName;
        if ($index == 1) $photo2 = $newName;
        if ($index == 2) $photo3 = $newName;
    }
}

// Update row with photo filenames
$update = $conn->prepare("UPDATE products 
    SET prod_photo1=?, prod_photo2=?, prod_photo3=? 
    WHERE prod_id=?");
$update->bind_param("sssi", $photo1, $photo2, $photo3, $product_id);
$update->execute();
$update->close();

$conn->close();

echo "success";

}
