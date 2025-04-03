<?php
session_start();
include __DIR__ . '/../db/connect.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Server-side validation
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('All fields are required!'); window.location.href='admin_register.php';</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!'); window.location.href='admin_register.php';</script>";
        exit();
    }

    if (!preg_match("/^\d{10}$/", $phone)) {
        echo "<script>alert('Phone number must be 10 digits!'); window.location.href='admin_register.php';</script>";
        exit();
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href='admin_register.php';</script>";
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists
    $check_query = "SELECT id FROM admin WHERE email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.location.href='adminlogin.php';</script>";
        exit();
    }
    $stmt->close();

    // Insert admin into the database
    $query = "INSERT INTO admin (name, email, phone, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Admin registration successful!'); window.location.href='adminlogin.php';</script>";
    } else {
        echo "<script>alert('Something went wrong. Please try again later.'); window.location.href='adminregister.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
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

    <div class="bg-lime-500 p-10 shadow-xl rounded-lg w-full max-w-md text-white relative mt-10">
        <h1 class="text-3xl font-bold text-center mb-6">Admin Register</h1>

        <form action="" method="post" class="space-y-4">
            <div>
                <label for="name" class="block font-medium">Full Name:</label>
                <input type="text" id="name" name="name" required class="w-full px-4 py-2 text-black border rounded focus:outline-none focus:ring-2 focus:ring-lime-300">
            </div>

            <div>
                <label for="email" class="block font-medium">Email:</label>
                <input type="email" id="email" name="email" required class="w-full px-4 py-2 text-black border rounded focus:outline-none focus:ring-2 focus:ring-lime-300">
            </div>

            <div>
                <label for="phone" class="block font-medium">Phone Number:</label>
                <input type="number" id="phone" name="phone" required class="w-full px-4 py-2 text-black border rounded focus:outline-none focus:ring-2 focus:ring-lime-300">
            </div>

            <div>
                <label for="password" class="block font-medium">Password:</label>
                <input type="password" id="password" name="password" required class="w-full px-4 py-2 text-black border rounded focus:outline-none focus:ring-2 focus:ring-lime-300">
            </div>

            <div>
                <label for="confirm_password" class="block font-medium">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-4 py-2 text-black border rounded focus:outline-none focus:ring-2 focus:ring-lime-300">
            </div>

            <button type="submit" class="w-full bg-lime-700 hover:bg-lime-800 text-white font-bold py-2 px-4 rounded transition duration-300">Register</button>
        </form>

        <p class="text-center text-sm mt-4">
            Already an admin? <a href="adminlogin.php" class="text-white font-bold hover:underline">Login here</a>
        </p>
    </div>

    <script>
        // Phone number validation - enforce 10 digits only
        document.getElementById('phone').addEventListener('input', function(e) {
            // Remove any non-digit characters
            this.value = this.value.replace(/\D/g, '');
            
            // Limit to 10 characters
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    </script>
</body>
</html>