<?php
class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "art_gallery";
    public $conn;

    // Constructor to initialize the connection
    public function __construct() {
        $this->connect();
    }

    // Method to create the connection
    private function connect() {
        $this->conn = new mysqli(
            $this->servername, 
            $this->username, 
            $this->password, 
            $this->database
        );

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Optional: method to close connection
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Usage
$db = new Database();
$conn = $db->conn;  // Access the connection for queries
 if ($db) {
            echo("Connection success: " );
        }
?>
