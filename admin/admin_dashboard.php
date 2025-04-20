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
    <title>Admin Dashboard | CUSAT Art Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3a0ca3;
            --secondary: #7209b7;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        .sidebar-item {
            transition: all 0.3s ease;
        }
        
        .sidebar-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-item.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        .status-active {
            background-color: #e6f7ee;
            color: #10b981;
        }
        
        .status-sold {
            background-color: #fee2e2;
            color: #ef4444;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #f59e0b;
        }
        
        .action-btn {
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar w-64 p-4 flex flex-col">
        <div class="flex items-center justify-center mb-8">
            <i class="fas fa-palette text-2xl mr-2 text-pink-400"></i>
            <h1 class="text-xl font-bold">CUSAT Art Gallery</h1>
        </div>
        
        <div class="flex-1">
            <a href="#" class="sidebar-item active flex items-center p-3 rounded-lg mb-2">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Dashboard
            </a>
          
         
       
        </div>
        
        <div class="mt-auto">
            <a href="adminlogout.php" class="flex items-center p-3 rounded-lg hover:bg-red-500 transition">
                <i class="fas fa-sign-out-alt mr-3"></i>
                Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Admin Dashboard</h2>
            <div class="flex items-center">
                <span class="mr-4 text-gray-600">Welcome, Admin</span>
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-user text-indigo-600"></i>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="card p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-600 mr-4">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Total Users</p>
                        <h3 class="text-2xl font-bold"><?= $users->num_rows ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="card p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-envelope text-lg"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Messages</p>
                        <h3 class="text-2xl font-bold"><?= $messages->num_rows ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="card p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <i class="fas fa-paint-brush text-lg"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Paintings</p>
                        <h3 class="text-2xl font-bold"><?= $paintings->num_rows ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="card p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-pink-100 text-pink-600 mr-4">
                        <i class="fas fa-monument text-lg"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Sculptures</p>
                        <h3 class="text-2xl font-bold"><?= $sculptures->num_rows ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Users Management</h3>
                
            </div>
            
            <div class="table-container">
                <table class="w-full">
                    <thead class="table-header">
                        <tr>
                            <th class="p-4 text-left">ID</th>
                            <th class="p-4 text-left">Name</th>
                            <th class="p-4 text-left">Email</th>
                            <th class="p-4 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="p-4 font-medium"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="p-4">
                                <a href="?delete_user=<?= $user['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this user?')"
                                   class="text-red-500 action-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Contact Messages -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Contact Messages</h3>
            
            <div class="table-container">
                <table class="w-full">
                    <thead class="table-header">
                        <tr>
                            <th class="p-4 text-left">Name</th>
                            <th class="p-4 text-left">Email</th>
                            <th class="p-4 text-left">Subject</th>
                            <th class="p-4 text-left">Message</th>
                            <th class="p-4 text-left">Received At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4 font-medium"><?= htmlspecialchars($msg['name']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($msg['email']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($msg['subject']) ?></td>
                            <td class="p-4"><?= htmlspecialchars(substr($msg['message'], 0, 50)) ?>...</td>
                            <td class="p-4"><?= htmlspecialchars($msg['created_at']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paintings Table -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Paintings Collection</h3>
                
            </div>
            
            <div class="table-container">
                <table class="w-full">
                    <thead class="table-header">
                        <tr>
                            <th class="p-4 text-left">ID</th>
                            <th class="p-4 text-left">Title</th>
                            <th class="p-4 text-left">Image</th>
                            <th class="p-4 text-left">Year</th>
                            <th class="p-4 text-left">Cost</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($painting = $paintings->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4"><?= htmlspecialchars($painting['id']) ?></td>
                            <td class="p-4 font-medium"><?= htmlspecialchars($painting['painting_title']) ?></td>
                            <td class="p-4">
                                <img src="../<?= htmlspecialchars($painting['painting_image']) ?>" 
                                     class="w-16 h-16 object-cover rounded-lg shadow">
                            </td>
                            <td class="p-4"><?= htmlspecialchars($painting['painting_year']) ?></td>
                            <td class="p-4">Rs<?= number_format(htmlspecialchars($painting['painting_cost']), 2) ?></td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs 
                                    <?= 
                                        $painting['status'] === 'Active' ? 'status-active' : 
                                        ($painting['status'] === 'Sold' ? 'status-sold' : 'status-pending') 
                                    ?>">
                                    <?= htmlspecialchars($painting['status']) ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <a href="?delete_painting=<?= $painting['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this painting?')"
                                   class="text-red-500 action-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sculptures Table -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Sculptures Collection</h3>
                
            </div>
            
            <div class="table-container">
                <table class="w-full">
                    <thead class="table-header">
                        <tr>
                            <th class="p-4 text-left">ID</th>
                            <th class="p-4 text-left">Title</th>
                            <th class="p-4 text-left">Image</th>
                            <th class="p-4 text-left">Year</th>
                            <th class="p-4 text-left">Cost</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sculpture = $sculptures->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4"><?= htmlspecialchars($sculpture['id']) ?></td>
                            <td class="p-4 font-medium"><?= htmlspecialchars($sculpture['title']) ?></td>
                            <td class="p-4">
                                <img src="../<?= htmlspecialchars($sculpture['image_url']) ?>" 
                                     class="w-16 h-16 object-cover rounded-lg shadow">
                            </td>
                            <td class="p-4"><?= htmlspecialchars($sculpture['sculpture_year']) ?></td>
                            <td class="p-4">Rs<?= number_format(htmlspecialchars($sculpture['sculpture_cost']), 2) ?></td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs 
                                    <?= 
                                        $sculpture['status'] === 'Active' ? 'status-active' : 
                                        ($sculpture['status'] === 'Sold' ? 'status-sold' : 'status-pending') 
                                    ?>">
                                    <?= htmlspecialchars($sculpture['status']) ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <a href="?delete_sculpture=<?= $sculpture['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this sculpture?')"
                                   class="text-red-500 action-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>