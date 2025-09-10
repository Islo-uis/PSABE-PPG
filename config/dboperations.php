<?php
// Include the database class
require_once __DIR__ . '/database.php';

class dboperations
{
    private $conn;

    public function __construct()
    {
        // Correctly get the single database instance using the Singleton pattern
        $this->conn = Database::getInstance()->getConnection();
    }
    
    public function getConnection()
    {
        return $this->conn;
    }

public function addProductWithImages($prod_name, $prod_description, $prod_qty, $prod_price, $doer_name = null)
{
    $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/images/product-images/';
    $imageURLs = [];

    try {
        // 1️⃣ Validate that at least one image was uploaded
        if (!isset($_FILES['file']) || !is_array($_FILES['file']['tmp_name']) || empty($_FILES['file']['tmp_name'])) {
            throw new Exception('At least one image is required.');
        }

        // 2️⃣ Determine product status
        $prod_status = ($prod_qty == 0) ? 'sold_out' : 'in_stock';

        // 3️⃣ Insert the product first
        $stmt = $this->conn->prepare("
            INSERT INTO products (prod_name, prod_description, prod_qty, prod_price, prod_status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssids", $prod_name, $prod_description, $prod_qty, $prod_price, $prod_status);

        if (!$stmt->execute()) {
            throw new Exception('Failed to insert product: ' . $stmt->error);
        }

        // 4️⃣ Get the generated product ID
        $prod_id = $this->conn->insert_id;

        // Ensure the target directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // 5️⃣ Process uploaded files
        $files = $_FILES['file'];
        foreach ($files['tmp_name'] as $index => $tmp_name) {
            if ($files['error'][$index] !== UPLOAD_ERR_OK) continue;

            $file_info = [
                'name' => $files['name'][$index],
                'type' => $files['type'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'error' => $files['error'][$index],
                'size' => $files['size'][$index],
            ];

            $uploaded_filename = $this->uploadImage($file_info, $targetDir, $prod_id, $index + 1);
            if ($uploaded_filename) $imageURLs[$index + 1] = $uploaded_filename;
        }

        // 6️⃣ Insert image URLs into product_images
        $stmt = $this->conn->prepare("
            INSERT INTO product_images (prod_id, img_url1, img_url2, img_url3)
            VALUES (?, ?, ?, ?)
        ");

        $img1 = $imageURLs[1] ?? null;
        $img2 = $imageURLs[2] ?? null;
        $img3 = $imageURLs[3] ?? null;

        $stmt->bind_param("isss", $prod_id, $img1, $img2, $img3);
        $stmt->execute();

        // 7️⃣ Success
        return [
            'status' => 'success',
            'message' => 'Product and images added successfully!',
            'prod_id' => $prod_id,
            'images' => $imageURLs
        ];

    } catch (Exception $e) {
        error_log('Error adding product with images: ' . $e->getMessage());
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Upload a single image
 */
public function uploadImage($image, $targetDir, $prod_id, $imageIndex)
{
    if (!$image || $image['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));

    // 1️⃣ Validate image
    if (!getimagesize($image["tmp_name"])) {
        error_log('File is not a valid image: ' . $image['name']);
        return false;
    }

    if ($image["size"] > 2000000) {
        error_log('File too large: ' . $image['name']);
        return false;
    }

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        error_log('Invalid file type: ' . $image['name']);
        return false;
    }

    // 2️⃣ Generate filename
    $filename = $prod_id . "_" . $imageIndex . "." . $imageFileType;
    $targetFile = $targetDir . $filename;

    // 3️⃣ Move the file
    if (move_uploaded_file($image["tmp_name"], $targetFile)) {
        return $filename;
    } else {
        error_log('Failed to move uploaded file: ' . $image['name']);
        return false;
    }
}

}
