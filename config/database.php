<?php
class Database
{

    private $conn;
    private static $instance = null;


    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "abecon";

    // Private constructor to implement Singleton pattern
    private function __construct()
    {
        $this->connect();
    }
    // Public static method to get the instance
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Create and return the database connection
    private function connect()
    {
        // Connect to MySQL server without specifying database
        $this->conn = new mysqli($this->host, $this->username, $this->password);

        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }

        // Check if the database exists
        if (!$this->databaseExists($this->dbname)) {
            // Create the database if it doesn't exist
            $createDbQuery = "CREATE DATABASE IF NOT EXISTS $this->dbname";
            if (!$this->conn->query($createDbQuery)) {
                throw new Exception("Error creating database: " . $this->conn->error);
            }
        }

        // Select the database
        $this->conn->select_db($this->dbname);
    }

    // Check if the database exists
    private function databaseExists($dbname)
    {
        $result = $this->conn->query("SHOW DATABASES LIKE '$dbname'");
        return $result->num_rows > 0;
    }

    // Get the database connection instance
    public function getConnection()
    {
        return $this->conn;
    }

    // Prevent cloning of this class (Singleton Pattern)
    private function __clone() {}

    // Prevent from being unserialized (Singleton Pattern)
    private function __wakeup() {}

    // Destructor to close the connection
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
