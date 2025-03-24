<?php
session_start(); // Start the session
include '../db/connect.php'; // Include database connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validate form data
    $errors = [];

    if (empty($name)) {
        $errors[] = "Name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    $sql = "SELECT id FROM sellers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Email already exists.";
    }

    $stmt->close();

    // If no errors, insert seller into the database
    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert seller into the database
        $sql = "INSERT INTO sellers (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! You can now login.'); window.location.href='seller_login.php';</script>";
        } else {
            $errors[] = "Registration failed. Please try again.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-gray-800 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="logo">
                <img src="logo.png" alt="Art Gallery Logo" class="h-12">
            </div>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="#" class="hover:text-gray-400">Home</a></li>
                    <li><a href="#" class="hover:text-gray-400">Gallery</a></li>
                    <li><a href="#" class="hover:text-gray-400">About</a></li>
                    <li><a href="#" class="hover:text-gray-400">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <h1 class="text-4xl font-bold text-center mb-8">Seller Registration</h1>

        <!-- Display Errors -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="" class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="name" class="block text-gray-700">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="block text-gray-700">Confirm Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Register
                </button>
            </div>
            <!-- Login Link -->
            <div class="mt-4 text-center">
                <p class="text-gray-600">Already have an account? <a href="seller_login.php" class="text-blue-500 hover:underline">Login here</a></p>
            </div>
        </form>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white p-6 mt-8">
        <div class="container mx-auto text-center">
            <p>Email: info@artgallery.com</p>
            <p>Phone: +123 456 7890</p>
            <div class="mt-4">
                <a href="#" class="mx-2 hover:text-gray-400">About Us</a>
                <a href="#" class="mx-2 hover:text-gray-400">FAQs</a>
                <a href="#" class="mx-2 hover:text-gray-400">Privacy Policy</a>
            </div>
        </div>
    </footer>
</body>
</html>