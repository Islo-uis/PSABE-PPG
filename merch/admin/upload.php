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

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve form data and sanitize it
    $prod_name = htmlspecialchars($_POST['prod_name']);
    $prod_price = htmlspecialchars($_POST['prod_price']);
    $prod_qty = htmlspecialchars($_POST['prod_qty']);
    $prod_description = htmlspecialchars($_POST['prod_description']);

    if (empty($prod_name)) {
        die("Error: Product name is required.");
    }

    // First insert into products table (without images)
    $sql = "INSERT INTO products (prod_name, prod_price, prod_qty, prod_description) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sdis", $prod_name, $prod_price, $prod_qty, $prod_description);

        if ($stmt->execute()) {
            $prod_id = $stmt->insert_id; // Get last inserted ID
        } else {
            die("Error inserting product: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }

    // Process uploaded images
    $uploadDir = './product-images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageUrls = ["", "", ""]; // default empty slots
    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
        $allowedTypes = ['image/jpeg', 'image/png'];

        $imgCounter = 1;
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($imgCounter > 3) break; // max 3 images

            $fileName = $_FILES['images']['name'][$key];
            $fileSize = $_FILES['images']['size'][$key];
            $fileTmp  = $_FILES['images']['tmp_name'][$key];
            $fileType = $_FILES['images']['type'][$key];
            $fileError= $_FILES['images']['error'][$key];

            if ($fileError !== UPLOAD_ERR_OK) continue;
            if (!in_array($fileType, $allowedTypes)) continue;
            if ($fileSize > 2000000) continue;

            // Get file extension
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);

            // Rename file -> prodid_imageno.ext
            $newFileName = $prod_id . "_" . $imgCounter . "." . strtolower($ext);
            $uploadPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $imageUrls[$imgCounter - 1] = $newFileName;
                $imgCounter++;
            }
        }
    }

    // Insert image URLs into product_images table
    $stmt = $conn->prepare("
        INSERT INTO product_images (prod_id, img_url1, img_url2, img_url3)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("isss", $prod_id, $imageUrls[0], $imageUrls[1], $imageUrls[2]);

    if ($stmt->execute()) {
        echo "Product and images added successfully!";
        header("Location: managemerch.html");
        exit();
    } else {
        echo "Error inserting images: " . $stmt->error;
    }

    $stmt->close();

} else {
    echo "Invalid request method. NOT POST";
}

?>
