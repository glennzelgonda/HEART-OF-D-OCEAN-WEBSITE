<?php
class Database {
    private $host = "localhost";
    private $db_name = "beach_resort";
    private $username = "root";  
    private $password = "";    
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            // Set additional PDO attributes for better security and error handling
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->conn->exec("set names utf8");
            
        } catch(PDOException $exception) {
            // Better error handling for production
            error_log("Database connection error: " . $exception->getMessage());
            // Don't display detailed errors to users in production
            if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
                // Show detailed error only on localhost
                die("Connection error: " . $exception->getMessage());
            } else {
                // Generic error message for production
                die("Database connection failed. Please try again later.");
            }
        }
        return $this->conn;
    }
}

// Create database instance
$database = new Database();
$db = $database->getConnection();
?>