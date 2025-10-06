<?php
session_start();
include '../db/connect.php'; // Database connection

class SellerRegistration {
    private $conn;
    private $errors = [];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        }
    }

    private function processForm() {
        $username = trim($_POST['username'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        $this->validate($username, $address, $phone, $email, $password, $confirmPassword);

        if (empty($this->errors)) {
            if ($this->checkDuplicates($username, $email, $phone)) {
                $this->errors[] = "Username, email, or phone number already exists.";
            } else {
                $this->registerSeller($username, $address, $phone, $email, $password);
            }
        }

        $this->displayErrors();
    }

    private function validate($username, $address, $phone, $email, $password, $confirmPassword) {
        if (empty($username)) {
            $this->errors[] = "Username is required.";
        } elseif (strlen($username) < 4) {
            $this->errors[] = "Username must be at least 4 characters long.";
        }

        if (empty($address)) {
            $this->errors[] = "Address is required.";
        }

        if (empty($phone)) {
            $this->errors[] = "Phone number is required.";
        } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
            $this->errors[] = "Phone number must be exactly 10 digits.";
        }

        if (empty($email)) {
            $this->errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email format.";
        }

        if (empty($password)) {
            $this->errors[] = "Password is required.";
        } elseif (strlen($password) < 8) {
            $this->errors[] = "Password must be at least 8 characters long.";
        }

        if ($password !== $confirmPassword) {
            $this->errors[] = "Passwords do not match.";
        }
    }

    private function checkDuplicates($username, $email, $phone) {
        $sql = "SELECT id FROM sellers WHERE username = ? OR email = ? OR phone = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $username, $email, $phone);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private function registerSeller($username, $address, $phone, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO sellers (username, address, phone, email, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssss', $username, $address, $phone, $email, $hashedPassword);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! You can now login.'); window.location.href='seller_login.php';</script>";
            exit;
        } else {
            $this->errors[] = "Registration failed. Please try again.";
        }

        $stmt->close();
    }

    private function displayErrors() {
        if (!empty($this->errors)) {
            echo '<div class="error-message bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <ul class="list-disc pl-5 space-y-1">';
            foreach ($this->errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul></div>';
        }
    }
}

// Instantiate and handle registration
$register = new SellerRegistration($conn);
ob_start(); // To capture and render error messages at correct place
$register->handleRequest();
$errorOutput = ob_get_clean();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Registration | Art Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('sellerreg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    <!-- Home Button -->
    <a href="../index.php" class="fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">Home</a>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="bg-white/90 backdrop-blur-md rounded-lg shadow-xl p-8 w-full max-w-md">
            <h1 class="text-3xl font-bold text-center mb-2 text-gray-800">Seller Registration</h1>
            <p class="text-center text-gray-600 mb-6">Create your seller account</p>

            <?= $errorOutput ?>

            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Username*</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Address*</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Phone*</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" maxlength="10" pattern="[0-9]{10}" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Email*</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Password*</label>
                    <input type="password" name="password" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Confirm Password*</label>
                    <input type="password" name="confirm_password" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-medium py-2 rounded-lg hover:bg-blue-700 transition">Register</button>

                <p class="text-center text-gray-600 mt-3">
                    Already have an account?
                    <a href="seller_login.php" class="text-blue-600 font-semibold hover:underline">Login here</a>
                </p>
            </form>
        </div>
    </main>

    <script>
        document.querySelector('input[name="phone"]').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    </script>
</body>
</html>
