<?php
session_start();
include '../db/connect.php';

// Check if the seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: seller_login.php");
    exit();
}

$seller_id = $_SESSION['seller_id'];

// Fetch paintings for the logged-in seller
$paintings = [];
$paintingsQuery = "SELECT * FROM paintings WHERE seller_id = ?";
$paintingsStmt = $conn->prepare($paintingsQuery);
$paintingsStmt->bind_param("i", $seller_id);
$paintingsStmt->execute();
$paintingsResult = $paintingsStmt->get_result();
if ($paintingsResult->num_rows > 0) {
    while ($row = $paintingsResult->fetch_assoc()) {
        $paintings[] = $row;
    }
}
$paintingsStmt->close();

// Fetch sculptures for the logged-in seller
$sculptures = [];
$sculpturesQuery = "SELECT * FROM sculptures WHERE seller_id = ?";
$sculpturesStmt = $conn->prepare($sculpturesQuery);
$sculpturesStmt->bind_param("i", $seller_id);
$sculpturesStmt->execute();
$sculpturesResult = $sculpturesStmt->get_result();
if ($sculpturesResult->num_rows > 0) {
    while ($row = $sculpturesResult->fetch_assoc()) {
        $sculptures[] = $row;
    }
}
$sculpturesStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-pending {
            color: #f39c12;
            font-weight: bold;
        }
        .status-active {
            color: #2ecc71;
            font-weight: bold;
        }
        .status-sold {
            color: #e74c3c;
            font-weight: bold;
        }
        .artwork-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Seller Dashboard</h1>

            <!-- Welcome Message -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h2 class="text-xl font-semibold text-blue-800">Welcome, <?php echo htmlspecialchars($_SESSION['seller_name'] ?? 'Seller'); ?>!</h2>
                <p class="text-blue-600">Here you can manage your artworks and track their status.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap justify-center gap-4 mb-8">
                <a href="../painting.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition duration-300 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Painting
                </a>
                <a href="../sculpture.php" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition duration-300 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Sculpture
                </a>
                <a href="seller_logout.php" class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg transition duration-300 flex items-center">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>

            <!-- Paintings Section -->
            <div class="mb-12">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4 border-b pb-2">Your Paintings</h2>
                <?php if (empty($paintings)): ?>
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="fas fa-paint-brush text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600">You haven't uploaded any paintings yet.</p>
                        <a href="../painting.php" class="text-blue-500 hover:underline mt-2 inline-block">Add your first painting</a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white rounded-lg overflow-hidden">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left">ID</th>
                                    <th class="py-3 px-4 text-left">Title</th>
                                    <th class="py-3 px-4 text-left">Image</th>
                                    <th class="py-3 px-4 text-left">Year</th>
                                    <th class="py-3 px-4 text-left">Cost (Rs)</th>
                                    <th class="py-3 px-4 text-left">Start Date</th>
                                    <th class="py-3 px-4 text-left">End Date</th>
                                    <th class="py-3 px-4 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($paintings as $painting): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 px-4"><?php echo htmlspecialchars($painting['id']); ?></td>
                                    <td class="py-4 px-4 font-medium"><?php echo htmlspecialchars($painting['painting_title']); ?></td>
                                    <td class="py-4 px-4">
                                        <img src="../<?php echo htmlspecialchars($painting['painting_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($painting['painting_title']); ?>" 
                                             class="artwork-image">
                                    </td>
                                    <td class="py-4 px-4"><?php echo htmlspecialchars($painting['painting_year']); ?></td>
                                    <td class="py-4 px-4"><?php echo number_format($painting['painting_cost'], 2); ?></td>
                                    <td class="py-4 px-4"><?php echo date('M d, Y', strtotime($painting['start_date'])); ?></td>
                                    <td class="py-4 px-4"><?php echo date('M d, Y', strtotime($painting['end_date'])); ?></td>
                                    <td class="py-4 px-4 status-<?php echo strtolower(htmlspecialchars($painting['status'])); ?>">
                                        <?php echo htmlspecialchars($painting['status']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sculptures Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4 border-b pb-2">Your Sculptures</h2>
                <?php if (empty($sculptures)): ?>
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="fas fa-monument text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600">You haven't uploaded any sculptures yet.</p>
                        <a href="../sculpture.php" class="text-blue-500 hover:underline mt-2 inline-block">Add your first sculpture</a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white rounded-lg overflow-hidden">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="py-3 px-4 text-left">ID</th>
                                    <th class="py-3 px-4 text-left">Title</th>
                                    <th class="py-3 px-4 text-left">Image</th>
                                    <th class="py-3 px-4 text-left">Year</th>
                                    <th class="py-3 px-4 text-left">Cost (Rs)</th>
                                    <th class="py-3 px-4 text-left">Start Date</th>
                                    <th class="py-3 px-4 text-left">End Date</th>
                                    <th class="py-3 px-4 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($sculptures as $sculpture): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 px-4"><?php echo htmlspecialchars($sculpture['id']); ?></td>
                                    <td class="py-4 px-4 font-medium"><?php echo htmlspecialchars($sculpture['title']); ?></td>
                                    <td class="py-4 px-4">
                                        <img src="../<?php echo htmlspecialchars($sculpture['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($sculpture['title']); ?>" 
                                             class="artwork-image">
                                    </td>
                                    <td class="py-4 px-4"><?php echo htmlspecialchars($sculpture['sculpture_year']); ?></td>
                                    <td class="py-4 px-4"><?php echo number_format($sculpture['sculpture_cost'], 2); ?></td>
                                    <td class="py-4 px-4"><?php echo date('M d, Y', strtotime($sculpture['start_date'])); ?></td>
                                    <td class="py-4 px-4"><?php echo date('M d, Y', strtotime($sculpture['end_date'])); ?></td>
                                    <td class="py-4 px-4 status-<?php echo strtolower(htmlspecialchars($sculpture['status'])); ?>">
                                        <?php echo htmlspecialchars($sculpture['status']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>