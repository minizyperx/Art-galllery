<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "art_gallery";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id=$user_id");
}

// Handle painting deletion
if (isset($_GET['delete_painting'])) {
    $painting_id = intval($_GET['delete_painting']);
    $conn->query("DELETE FROM paintings WHERE id=$painting_id");
}

// Handle sculpture deletion
if (isset($_GET['delete_sculpture'])) {
    $sculpture_id = intval($_GET['delete_sculpture']);
    $conn->query("DELETE FROM sculptures WHERE id=$sculpture_id");
}

// Handle painting upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['painting_file'])) {
    $title = $_POST['title'];
    $file_name = basename($_FILES['painting_file']['name']);
    $target_dir = "uploads/";
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES['painting_file']['tmp_name'], $target_file)) {
        $conn->query("INSERT INTO paintings (painting_title, painting_image) VALUES ('$title', '$target_file')");
    }
}

// Fetch users, messages, paintings, and sculptures
$users = $conn->query("SELECT * FROM users");
$messages = $conn->query("SELECT * FROM contact_messages");
$paintings = $conn->query("SELECT * FROM paintings");
$sculptures = $conn->query("SELECT * FROM sculptures");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    <div class="max-w-6xl mx-auto my-10 p-6 bg-white shadow-lg rounded-lg">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-blue-600">Admin Dashboard</h2>
            <div class="space-x-2">
                <a href="adminlogout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700 transition">Logout</a>
            </div>
        </div>

        <!-- Users Table -->
        <h3 class="mt-6 text-xl font-semibold">Users</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 shadow-md">
                <thead class="bg-blue-500 text-white">
                    <tr>
                        <th class="p-3 border">ID</th>
                        <th class="p-3 border">Name</th>
                        <th class="p-3 border">Email</th>
                        <th class="p-3 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="p-3 border"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="p-3 border">
                                <a class="text-red-600 hover:underline" href="?delete_user=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Contact Messages -->
        <h3 class="mt-6 text-xl font-semibold">Contact Messages</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 shadow-md">
                <thead class="bg-blue-500 text-white">
                    <tr>
                        <th class="p-3 border">Name</th>
                        <th class="p-3 border">Email</th>
                        <th class="p-3 border">Subject</th>
                        <th class="p-3 border">Message</th>
                        <th class="p-3 border">Received At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($msg = $messages->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="p-3 border"><?= htmlspecialchars($msg['name']) ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($msg['email']) ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($msg['subject']) ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($msg['message']) ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($msg['created_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Paintings Table -->
        <h3 class="mt-6 text-xl font-semibold">Paintings</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 shadow-md">
                <thead class="bg-blue-500 text-white">
                    <tr>
                        <th class="p-3 border">ID</th>
                        <th class="p-3 border">Title</th>
                        <th class="p-3 border">Image</th>
                        <th class="p-3 border">Year</th>
                        <th class="p-3 border">Cost</th>
                        <th class="p-3 border">Status</th>
                        <th class="p-3 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($painting = $paintings->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="p-3 border"><?= htmlspecialchars($painting['id']) ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($painting['painting_title']) ?></td>
                            <td class="p-3 border">
                                <img src="../<?= htmlspecialchars($painting['painting_image']) ?>" width="100" class="rounded-lg shadow">
                            </td>
                            <td class="p-3 border"><?= htmlspecialchars($painting['painting_year']) ?></td>
                            <td class="p-3 border">$<?= number_format(htmlspecialchars($painting['painting_cost']), 2) ?></td>
                            <td class="p-3 border">
                                <span class="<?= 
                                    $painting['status'] === 'Active' ? 'bg-green-100 text-green-800' : 
                                    ($painting['status'] === 'Sold' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') 
                                ?> px-2 py-1 rounded-full text-xs">
                                    <?= htmlspecialchars($painting['status']) ?>
                                </span>
                            </td>
                            <td class="p-3 border">
                                <a class="text-red-600 hover:underline" href="?delete_painting=<?= $painting['id'] ?>" onclick="return confirm('Are you sure you want to delete this painting?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Sculptures Table -->
        <h3 class="mt-6 text-xl font-semibold">Sculptures</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 shadow-md">
                <thead class="bg-blue-500 text-white">
                    <tr>
                        <th class="p-3 border">ID</th>
                        <th class="p-3 border">Title</th>
                        <th class="p-3 border">Image</th>
                        <th class="p-3 border">Year</th>
                        <th class="p-3 border">Cost</th>
                        <th class="p-3 border">Status</th>
                        <th class="p-3 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($sculpture = $sculptures->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="p-3 border"><?= htmlspecialchars($sculpture['id']) ?></td>
                            <td class="p-3 border"><?= htmlspecialchars($sculpture['title']) ?></td>
                            <td class="p-3 border">
                            <img src="../<?= htmlspecialchars($sculpture['image_url']) ?>" width="100" class="rounded-lg shadow">
                            </td>
                            <td class="p-3 border"><?= htmlspecialchars($sculpture['sculpture_year']) ?></td>
                            <td class="p-3 border">$<?= number_format(htmlspecialchars($sculpture['sculpture_cost']), 2) ?></td>
                            <td class="p-3 border">
                                <span class="<?= 
                                    $sculpture['status'] === 'Active' ? 'bg-green-100 text-green-800' : 
                                    ($sculpture['status'] === 'Sold' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') 
                                ?> px-2 py-1 rounded-full text-xs">
                                    <?= htmlspecialchars($sculpture['status']) ?>
                                </span>
                            </td>
                            <td class="p-3 border">
                                <a class="text-red-600 hover:underline" href="?delete_sculpture=<?= $sculpture['id'] ?>" onclick="return confirm('Are you sure you want to delete this sculpture?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // File name display for upload form
        document.getElementById("painting_file")?.addEventListener("change", function() {
            document.getElementById("file-name").innerText = this.files[0].name;
        });
    </script>
</body>
</html>