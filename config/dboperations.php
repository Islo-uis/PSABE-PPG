<?php
// Include the database class
require_once 'database.php';

class dboperations
{
    private $conn;

    // Constructor to initialize the connection
    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Function to handle image upload and renaming
    public function uploadImage($image, $targetDir, $prod_code, $imageIndex)
    {
        $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
        $check = getimagesize($image["tmp_name"]);

        if ($check === false) {
            echo "<script>alert('File is not an image.');</script>";
            return false;
        }

        if ($image["size"] > 2000000) {
            echo "<script>alert('Sorry, your file is too large.');</script>";
            return false;
        }

        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
            return false;
        }

        // Generate file name based on prod_id and image index (1, 2, 3)
        $filename = $prod_code . $imageIndex . ".jpeg";
        $targetfile = $targetDir . $filename;

        // Attempt to move the uploaded file
        if (move_uploaded_file($image["tmp_name"], $targetfile)) {
            return $filename;  // Return the final image URL
        } else {
            echo "<script>alert('Failed to move uploaded file.');</script>";
            return false;
        }
    }

  
    public function addProductWithImages($prod_name, $prod_description, $prod_qty, $prod_price, $doer_name = null)
    {
        $target = $_SERVER['DOCUMENT_ROOT'] . '/images/product-images/';
        $imageURLs = [];

        try {
            // Check at least the first image is uploaded
            if (!isset($_FILES['img_url1']) || $_FILES['img_url1']['error'] !== UPLOAD_ERR_OK) {
                echo "<script>alert('img_url1 is required.');</script>";
                return;
            }

            // 1. Insert product without image data
            $stmt = $this->conn->prepare("
                INSERT INTO products (prod_name, prod_description, prod_qty, prod_price, prod_status)
                VALUES (:prod_name, :prod_description, :prod_qty, :prod_price, 
                    CASE WHEN :prod_qty = 0 THEN 'sold_out' ELSE 'in_stock' END)");

            $stmt->execute([
                ':prod_name' => $prod_name,
                ':prod_description' => $prod_description,
                ':prod_qty' => $prod_qty,
                ':prod_price' => $prod_price
            ]);



            // 2. Get the generated product ID
            $prod_id = $this->conn->lastInsertId();

            $imageURLs = [];

            for ($i = 1; $i <= 3; $i++) {
                $key = 'img_url' . $i;
                if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                    $filename = $prod_id . $i . ".jpeg";
                    $uploaded = $this->uploadImage($_FILES[$key], $target, $prod_id, $i);
                    if ($uploaded) {
                        $imageURLs[$i] = $filename;
                    }
                }
            }


            // 4. Insert image URLs into product_images table
            
            $stmt = $this->conn->prepare("
            INSERT INTO product_images (prod_id, p_img_url1, p_img_url2, p_img_url3)
            VALUES (:prod_id, :img_url1, :img_url2, :img_url3)
        ");

            $stmt->execute([
                ':prod_id' => $prod_id,
                ':img_url1' => $imageURLs[1] ?? null,
                ':img_url2' => $imageURLs[2] ?? null,
                ':img_url3' => $imageURLs[3] ?? null
            ]);


            // // 5. Log the action
            // $this->conn->query("INSERT INTO logs (action, doer_name) VALUES ('Added a new product', '$doer_name')");

            // 6. Success message
            echo "<script>alert('New product created successfully');</script>";
        } catch (PDOException $e) {
            error_log('Error adding product: ' . $e->getMessage());
            throw new Exception('Failed to add product: ' . $e->getMessage());
        }
    }


    // Update a record
    public function update($id, $name, $description, $price, $quantity)
    {
        $query = "UPDATE products SET prod_name = ?, prod_description = ?, prod_price = ?, prod_qty = ? 
                  WHERE prod_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssdi", $name, $description, $price, $quantity);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Delete a record
    public function delete($id)
    {
        $query = "DELETE FROM products WHERE prod_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Select all records
    public function selectAll()
    {
        $query = "SELECT * FROM products";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Select a single record by ID
    public function selectById($id)
    {
        $query = "SELECT * FROM products WHERE prod_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
  // public function addProductWithImages($prod_name, $prod_description, $prod_qty, $prod_price, $img_url1, $img_url2 = null, $img_url3 = null, $doer_name)
    // {
    //     $target = $_SERVER['DOCUMENT_ROOT'] . '/images/product-images/';
    //     $upload_done = 1;


    //     try {
    //         // Ensure at least the first image is provided
    //         if ($img_url1 === null) {
    //             echo "<script>alert('img_url1 is required.');</script>";
    //             return;
    //         }

    //         // Set current user
    //         $this->conn->query("SET @current_user = '$doer_name'");

    //         //insert to logs table
    //         $this->conn->query("INSERT INTO logs (action, doer_name) VALUES ('Added a new product', @current_user)");


    //         // Prepare the statement to call the stored procedure
    //         $stmt = $this->conn->prepare("CALL sp_addProductWithImages(:prod_name, :prod_description, :prod_qty, :prod_price, :img_url1, :img_url2, :img_url3, @prod_id)");

    //         // Bind parameters
    //         $stmt->bindParam(':prod_name', $prod_name, PDO::PARAM_STR);
    //         $stmt->bindParam(':prod_description', $prod_description, PDO::PARAM_STR);
    //         $stmt->bindParam(':prod_qty', $prod_qty, PDO::PARAM_INT);
    //         $stmt->bindParam(':prod_price', $prod_price, PDO::PARAM_STR);
    //         $stmt->bindParam(':img_url1', $img_url1, PDO::PARAM_STR); // img_url1 is required
    //         $stmt->bindParam(':img_url2', $img_url2, PDO::PARAM_STR); // img_url2 is optional
    //         $stmt->bindParam(':img_url3', $img_url3, PDO::PARAM_STR); // img_url3 is optional

    //         // Execute the procedure
    //         $stmt->execute();

    //         // Fetch the newly created product ID
    //         $result = $this->conn->query("SELECT @prod_id AS prod_id");
    //         $prod_id = $result->fetch(PDO::FETCH_ASSOC)['prod_id'];

    //         // Upload images and get their URLs
    //         $imageURLs = [];
    //         if ($img_url1) {
    //             $img_url1 = $this->uploadImage($_FILES['img_url1'], $target, $prod_id, 1);
    //             if ($img_url1) {
    //                 $imageURLs[] = $img_url1;
    //             }
    //         }
    //         if ($img_url2) {
    //             $img_url2 = $this->uploadImage($_FILES['img_url2'], $target, $prod_id, 2);
    //             if ($img_url2) {
    //                 $imageURLs[] = $img_url2;
    //             }
    //         }
    //         if ($img_url3) {
    //             $img_url3 = $this->uploadImage($_FILES['img_url3'], $target, $prod_id, 3);
    //             if ($img_url3) {
    //                 $imageURLs[] = $img_url3;
    //             }
    //         }

    //         // Log success
    //         error_log('Product added successfully by staff: ' . $doer_name);
    //         echo "<script>alert('New record created successfully');</script>";
    //     } catch (PDOException $e) {
    //         // Handle any errors
    //         error_log('Error adding product: ' . $e->getMessage());
    //         throw new Exception('Failed to add product: ' . $e->getMessage());
    //     }
    // }
