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


// Sanitize inputs
$prod_id = intval($_POST['prod_id']);
$prod_name = $conn->real_escape_string($_POST['prod_name']);
$prod_qty = intval($_POST['prod_qty']);
$prod_desc = $conn->real_escape_string($_POST['prod_description']);



// Start base update query (name, qty, description only)
$sql = "UPDATE products 
        SET prod_name = '$prod_name',
            prod_qty = $prod_qty,
            prod_description = '$prod_desc'
        WHERE prod_id = $prod_id";

if (!$conn->query($sql)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Product update failed: " . $conn->error]);
    exit;
}


$uploadDir = __DIR__ . "/product-images/";
$response = ["status" => "success", "message" => "Product updated", "updated_images" => []];

// Allowed extensions
$allowedExt = ['jpg', 'jpeg', 'png'];
$imageSlots = [
    "img1" => "img_url1",
    "img2" => "img_url2",
    "img3" => "img_url3"
];
foreach ($imageSlots as $inputKey => $dbCol) {
    if (isset($_FILES[$inputKey]) && $_FILES[$inputKey]['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES[$inputKey]["tmp_name"];
        $ext = strtolower(pathinfo($_FILES[$inputKey]["name"], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $response["status"] = "warning";
            $response["message"] = "Invalid file type for $inputKey. Only JPG, JPEG, PNG allowed.";
            continue;
        }

        // File name format: prodid_slot.ext → e.g., 6_1.png
        $slotNum = substr($inputKey, -1); // 1, 2, or 3
        $newFileName = $prod_id . "_" . $slotNum . "." . $ext;
        $targetFile = $uploadDir . $newFileName;

        // Delete any old versions (with different extensions)
        foreach ($allowedExt as $oldExt) {
            $oldFile = $uploadDir . $prod_id . "_" . $slotNum . "." . $oldExt;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Move new file
        if (move_uploaded_file($tmpName, $targetFile)) {
            // Update DB with new filename
            $updateImgSql = "UPDATE product_images SET $dbCol = '$newFileName' WHERE prod_id = $prod_id";
            $conn->query($updateImgSql);

            $response["updated_images"][] = $dbCol;
        } else {
            $response["status"] = "warning";
            $response["message"] = "Failed to upload $inputKey.";
        }
    }
}

echo json_encode($response);