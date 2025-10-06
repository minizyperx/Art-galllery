<?php
session_start();

class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "art_gallery";
    public $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }
}

class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username, $email, $phone, $address, $password, $confirm_password) {
        // Trim and sanitize input
        $username = trim($username);
        $email = trim($email);
        $phone = trim($phone);
        $address = trim($address);
        $password = trim($password);
        $confirm_password = trim($confirm_password);

        // Validation
        if (empty($username) || empty($email) || empty($phone) || empty($address) || empty($password) || empty($confirm_password)) {
            $this->redirectWithAlert("All fields are required!", "user_register.php");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithAlert("Invalid email format!", "user_register.php");
        }

        if (!preg_match("/^\d{10}$/", $phone)) {
            $this->redirectWithAlert("Phone number must be 10 digits!", "user_register.php");
        }

        if ($password !== $confirm_password) {
            $this->redirectWithAlert("Passwords do not match!", "user_register.php");
        }

        // Check if username or email already exists
        $check_query = "SELECT id FROM {$this->table} WHERE username = ? OR email = ?";
        $stmt = $this->conn->prepare($check_query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $this->redirectWithAlert("Username or Email already exists!", "user_register.php");
        }
        $stmt->close();

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $insert_query = "INSERT INTO {$this->table} (username, email, phone, address, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($insert_query);
        $stmt->bind_param("sssss", $username, $email, $phone, $address, $hashed_password);

        if ($stmt->execute()) {
            $this->redirectWithAlert("Registration successful! Please log in.", "userlogin.php");
        } else {
            $this->redirectWithAlert("Something went wrong. Please try again later.", "user_register.php");
        }

        $stmt->close();
    }

    private function redirectWithAlert($message, $location) {
        echo "<script>alert('$message'); window.location.href='$location';</script>";
        exit();
    }
}

// Main execution
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $user = new User($database->conn);

    $user->register(
        $_POST['username'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['password'],
        $_POST['confirm_password']
    );
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body {
            font-family: sans-serif;
            background-image: url('../userregister.jpeg');
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
            position: relative;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #e91e63;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"] {
            width: calc(100% - 18px);
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px 15px;
            background-color: #e91e63;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #d81b60;
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
        }

        .login-link a {
            color: #e91e63;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .home-button {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 14px;
        }

        .home-button:hover {
            background-color: #45a049;
        }

        @media (max-width: 450px) {
            .container {
                width: 90%;
            }
            .home-button {
                top: 10px;
                right: 10px;
                padding: 6px 10px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="home-button">Home</a>
        <h1>Register</h1>
        <form action="" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone number:</label>
            <input type="number" id="phone" name="phone" required>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>

        <div class="login-link">
            <p>Already have an account? <a href="userlogin.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
