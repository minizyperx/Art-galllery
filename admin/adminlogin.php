<?php
session_start();

class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "art_gallery";
    public $conn;

    public function __construct() {
        $this->connectDB();
    }

    private function connectDB() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Database Connection failed: " . $this->conn->connect_error);
        }
    }
}

class Admin {
    private $conn;
    private $table = "admin";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $email = strtolower(trim($email));
        $password = trim($password);

        if (empty($email) || empty($password)) {
            $this->redirectWithAlert("Both fields are required!", "adminlogin.php");
        }

        $query = "SELECT id, name, password FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id, $name, $hashed_password);
            $stmt->fetch();

            // Debugging (you can remove later)
            // var_dump("Email: ", $email, " Password: ", $password, " Hashed: ", $hashed_password);

            if (password_verify($password, $hashed_password)) {
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_name'] = $name;
                $this->redirectWithAlert("Login successful!", "admin_dashboard.php");
            } else {
                $this->redirectWithAlert("Invalid email or password!", "adminlogin.php");
            }
        } else {
            $this->redirectWithAlert("Admin not found!", "adminlogin.php");
        }

        $stmt->close();
    }

    private function redirectWithAlert($message, $location) {
        echo "<script>alert('$message'); window.location.href='$location';</script>";
        exit();
    }
}

// Main Execution
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $database = new Database();
    $admin = new Admin($database->conn);
    $admin->login($_POST['email'], $_POST['password']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .home-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #4f46e5;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .home-button:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
        }
        .login-container {
            position: relative;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-cover bg-center" style="background-image: url('../adminlogin.jpeg');">

    <div class="login-container bg-white p-10 shadow-xl rounded-lg w-full max-w-md relative">
        <a href="../index.php" class="home-button">Home</a>
        <h1 class="text-3xl font-bold text-center text-pink-600 mb-6">Admin Login</h1>

        <form action="" method="post" class="space-y-4">
            <div>
                <label for="email" class="block font-medium text-gray-700">Email:</label>
                <input type="email" id="email" name="email" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-pink-300">
            </div>

            <div>
                <label for="password" class="block font-medium text-gray-700">Password:</label>
                <input type="password" id="password" name="password" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-pink-300">
            </div>

            <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded transition duration-300">Login</button>
        </form>

        <p class="text-center text-sm mt-4 text-gray-600">
            Not registered? <a href="admin_register.php" class="text-pink-600 font-bold hover:underline">Register here</a>
        </p>
    </div>

</body>
</html>
