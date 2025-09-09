<?php
class Database {
    private $conn;
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "abecon";

    // Private constructor to implement Singleton pattern
    private function __construct() {
        $this->connect();
    }

    // Create and return the database connection
    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Get the database connection instance
    public function getConnection() {
        return $this->conn;
    }

    // Prevent cloning of this class (Singleton Pattern)
    private function __clone() {}



    
}


