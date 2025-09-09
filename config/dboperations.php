<?php
// Include the database class
require_once 'database.php';

class dboperations {
    private $conn;

    // Constructor to initialize the connection
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Insert a record
  public function addproduct($prod_name, $prod_description, $prod_price, $prod_qty, $photo_url, $doer_name) {

        $target =  $_SERVER['DOCUMENT_ROOT'] . '/images/product-images/';
        $upload_done = 1;
        $imageFileType = strtolower(pathinfo($photo_url["name"], PATHINFO_EXTENSION));

        $check = getimagesize($photo_url["tmp_name"]);
        if ($check !== false) {
            $upload_done = 1;
        } else {
            echo "<script>alert('File is not an image.');</script>";
            $upload_done = 0;
        }

        if ($photo_url["size"] > 2000000) {
            echo "<script>alert('Sorry, your file is too large.');</script>";
            $upload_done = 0;
        }
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
            $upload_done = 0;
        }

        

        if ($upload_done == 0) {
            echo "<script>alert('Sorry, your file was not uploaded.');</script>";

        } else {
            try {

                $this->conn->query("SET @current_user = '$doer_name'");

                // Prepare the statement to call the stored procedure
                $stmt = $this->conn->prepare("CALL sp_addproducts(:prod_name, :prod_price, :prod_qoh, :prod_origin, :prod_category, @prod_code)");

                // Bind the parameters to the stored procedure
                $stmt->bindParam(':prod_name', $prod_name, PDO::PARAM_STR);
                $stmt->bindParam(':prod_desc', $prod_description, PDO::PARAM_STR);
                $stmt->bindParam(':prod_price', $prod_price, PDO::PARAM_STR);
                $stmt->bindParam(':prod_qty', $prod_qty, PDO::PARAM_INT);

                // Execute the statement
                $stmt->execute();

                $result = $this->conn->query("SELECT @prod_code AS prod_code");
                $prod_code = $result->fetch(PDO::FETCH_ASSOC)['prod_code'];

                // Save the image with the correct filename
                $filename = $prod_code . ".jpeg";
                $targetfile = $target . $filename;

                if (move_uploaded_file($photo_url["tmp_name"], $targetfile)) {
                    // Log success
                    error_log('Product added successfully by staff: ' . $doer_name);
                    echo "<script>alert('New record created successfully');</script>";
                } else {
                    echo "<script>alert('Failed to move uploaded file.');</script>";
                }

            } catch (PDOException $e) {
                // Handle any errors
                error_log('Error adding product: ' . $e->getMessage());
                throw new Exception('Failed to add product: ' . $e->getMessage());
            }
        }
    }

    // Update a record
    public function update($id, $name, $description, $price, $quantity) {
        $query = "UPDATE products SET prod_name = ?, prod_description = ?, prod_price = ?, prod_qty = ? 
                  WHERE prod_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssdi", $name, $description, $price, $quantity);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Delete a record
    public function delete($id) {
        $query = "DELETE FROM products WHERE prod_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Select all records
    public function selectAll() {
        $query = "SELECT * FROM products";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Select a single record by ID
    public function selectById($id) {
        $query = "SELECT * FROM products WHERE prod_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
