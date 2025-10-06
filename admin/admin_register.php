<?php
session_start();

/* -------------------------------
   DATABASE CONNECTION CLASS
--------------------------------*/
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $dbname = "art_gallery";
    private $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

/* -------------------------------
   ADMIN MODEL CLASS
--------------------------------*/
class Admin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db->getConnection();
    }

    // Validate admin inputs
    public function validateInput($data) {
        $errors = [];

        if (empty($data['name']) || empty($data['email']) || empty($data['phone']) ||
            empty($data['password']) || empty($data['confirm_password'])) {
            $errors[] = "All fields are required!";
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format!";
        }

        if (!preg_match("/^\d{10}$/", $data['phone'])) {
            $errors[] = "Phone number must be 10 digits!";
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = "Passwords do not match!";
        }

        return $errors;
    }

    // Check if email already exists
    public function emailExists($email) {
        $query = "SELECT id FROM admin WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    // Register new admin
    public function register($data) {
        $name = trim($data['name']);
        $email = trim($data['email']);
        $phone = trim($data['phone']);
        $password = password_hash(trim($data['password']), PASSWORD_BCRYPT);

        $query = "INSERT INTO admin (name, email, phone, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $name, $email, $phone, $password);

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}

/* -------------------------------
   CONTROLLER CLASS
--------------------------------*/
class RegisterController {
    private $admin;

    public function __construct($admin) {
        $this->admin = $admin;
    }

    public function handleRequest() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $data = $_POST;
            $errors = $this->admin->validateInput($data);

            if (!empty($errors)) {
                $this->alertAndRedirect(implode('\n', $errors), 'admin_register.php');
                return;
            }

            if ($this->admin->emailExists($data['email'])) {
                $this->alertAndRedirect('Email already exists!', 'adminlogin.php');
                return;
            }

            if ($this->admin->register($data)) {
                $this->alertAndRedirect('Admin registration successful!', 'adminlogin.php');
            } else {
                $this->alertAndRedirect('Something went wrong. Please try again later.', 'admin_register.php');
            }
        }
    }

    private function alertAndRedirect($message, $location) {
        echo "<script>alert('$message'); window.location.href='$location';</script>";
        exit();
    }
}

/* -------------------------------
   MAIN EXECUTION
--------------------------------*/
$db = new Database();
$admin = new Admin($db);
$controller = new RegisterController($admin);
$controller->handleRequest();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .home-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 50;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .home-btn:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-cover bg-center" style="background-image: url('../admin.jpeg');">

    <!-- Home Button -->
    <a href="../index.php" class="home-btn">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
        </svg>
        Home
    </a>

    <!-- Registration Form -->
    <div class="bg-lime-500 p-10 shadow-xl rounded-lg w-full max-w-md text-white relative mt-10">
        <h1 class="text-3xl font-bold text-center mb-6">Admin Register</h1>

        <form method="POST" class="space-y-4">
            <div>
                <label for="name" class="block font-medium">Full Name:</label>
                <input type="text" id="name" name="name" required
                       class="w-full px-4 py-2 text-black border rounded focus:outline-none focus:ring-2 focus:ring-lime-300">
            </div>

            <div>
                <label for="email" class="block font-medium">Email:</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-2 text-black border rounded focus:outline-none focus:ring-2 focus:ring-lime-300">
            </div>

            <div>
                <label for="phone" class="block font-medium">Phone Number:</label>
                <input type="number" id="phone" name="phone" required
                       class="w-full px-4 py-2 text-black border rounded focus:outline-none focus:ring-2 focus:ring-lime-300">
            </div>

            <div>
                <label for="password" class="block font-medium">Password:</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 text-black border rounded focus:outline-none focus:ring-2 focus:ring-lime-300">
            </div>

            <div>
                <label for="confirm_password" class="block font-medium">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="w-full px-4 py-2 text-black border rounded focus:outline-none focus:ring-2 focus:ring-lime-300">
            </div>

            <button type="submit"
                    class="w-full bg-lime-700 hover:bg-lime-800 text-white font-bold py-2 px-4 rounded transition duration-300">
                Register
            </button>
        </form>

        <p class="text-center text-sm mt-4">
            Already an admin? <a href="adminlogin.php" class="text-white font-bold hover:underline">Login here</a>
        </p>
    </div>

    <script>
        // Enforce 10-digit phone numbers
        document.getElementById('phone').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    </script>
</body>
</html>
