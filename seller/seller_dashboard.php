<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "art_gallery";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the seller is logged in
if (!isset($_SESSION['seller_id'])) {
    die("You must be logged in to view this page.");
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #555;
        }
        .button-group {
            text-align: center;
            margin-bottom: 20px;
        }
        .button-group button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
        }
        .button-group button:hover {
            background-color: #0056b3;
        }
        .section {
            margin-bottom: 40px;
        }
        .section h2 {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        img {
            max-width: 100px;
            height: auto;
            border-radius: 5px;
        }
        .no-data {
            text-align: center;
            color: #888;
            font-style: italic;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-sold {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Seller Dashboard</h1>

        <!-- Button Group -->
        <div class="button-group">
            <button onclick="window.location.href='../painting.php'">Add Painting</button>
            <button onclick="window.location.href='../sculpture.php'">Add Sculpture</button>
            <button onclick="window.location.href='seller_logout.php'">Logout</button>
        </div>

        <!-- Paintings Section -->
        <div class="section">
            <h2>Paintings</h2>
            <?php if (empty($paintings)): ?>
                <p class="no-data">No paintings found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Image</th>
                            <th>Start Date</th>
                            <th>Year</th>
                            <th>Cost</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paintings as $painting): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($painting['id']); ?></td>
                            <td><?php echo htmlspecialchars($painting['painting_title']); ?></td>
                            <td><img src="../<?php echo htmlspecialchars($painting['painting_image']); ?>" alt="<?php echo htmlspecialchars($painting['painting_title']); ?>"></td>
                            <td><?php echo htmlspecialchars($painting['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($painting['painting_year']); ?></td>
                            <td>$<?php echo number_format($painting['painting_cost'], 2); ?></td>
                            <td><?php echo htmlspecialchars($painting['end_date']); ?></td>
                            <td class="status-<?php echo strtolower(htmlspecialchars($painting['status'])); ?>">
                                <?php echo htmlspecialchars($painting['status']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Sculptures Section -->
        <div class="section">
            <h2>Sculptures</h2>
            <?php if (empty($sculptures)): ?>
                <p class="no-data">No sculptures found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Image</th>
                            <th>Start Date</th>
                            <th>Year</th>
                            <th>Cost</th>
                            <th>End Date</th>
                            <th>Status</th>
                
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sculptures as $sculpture): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sculpture['id']); ?></td>
                            <td><?php echo htmlspecialchars($sculpture['title']); ?></td>
                            <td><img src="../<?php echo htmlspecialchars($sculpture['image_url']); ?>" alt="<?php echo htmlspecialchars($sculpture['title']); ?>"></td>
                            <td><?php echo htmlspecialchars($sculpture['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($sculpture['sculpture_year']); ?></td>
                            <td>$<?php echo number_format($sculpture['sculpture_cost'], 2); ?></td>
                            <td><?php echo htmlspecialchars($sculpture['end_date']); ?></td>
                            <td class="status-<?php echo strtolower(htmlspecialchars($sculpture['status'])); ?>">
                                <?php echo htmlspecialchars($sculpture['status']); ?>
                            </td>
                        
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>