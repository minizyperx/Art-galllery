<?php
$servername = "localhost";  // XAMPP default
$username = "root";  // Default username for XAMPP
$password = "";  // No password by default
$database = "art_gallery";  // Ensure this is your correct database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {  
    die("Connection failed: " . $conn->connect_error);
}
?>
