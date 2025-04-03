<?php
session_start();
include '../db/connect.php';

$loggedInUserId = $_SESSION['user_id']; // Get user ID from session

// Fetch bids for paintings
$paintingsQuery = "
    SELECT 
        b.bid_id, b.bid_amount, b.start_date, b.end_date, b.bid_status, 
        b.winner_status, b.highest_bid, b.currency, b.bid_reason,
        p.painting_title AS artwork_name, p.painting_image AS artwork_image,
        up.username AS seller_name
    FROM bids b
    LEFT JOIN paintings p ON b.p_id = p.id
    LEFT JOIN users up ON p.seller_id = up.id
    WHERE b.u_id = ? AND b.p_id IS NOT NULL
    ORDER BY b.end_date DESC";

$paintingsStmt = $conn->prepare($paintingsQuery);
$paintingsStmt->bind_param("i", $loggedInUserId);
$paintingsStmt->execute();
$paintingsResult = $paintingsStmt->get_result();

// Fetch bids for sculptures
$sculpturesQuery = "
    SELECT 
        b.bid_id, b.bid_amount, b.start_date, b.end_date, b.bid_status, 
        b.winner_status, b.highest_bid, b.currency, b.bid_reason,
        s.title AS artwork_name, s.image_url AS artwork_image,
        us.username AS seller_name
    FROM bids b
    LEFT JOIN sculptures s ON b.p_id = s.id
    LEFT JOIN users us ON s.seller_id = us.id
    WHERE b.u_id = ? AND b.p_id IS NULL
    ORDER BY b.end_date DESC";

$sculpturesStmt = $conn->prepare($sculpturesQuery);
$sculpturesStmt->bind_param("i", $loggedInUserId);
$sculpturesStmt->execute();
$sculpturesResult = $sculpturesStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bids</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-5xl mx-auto bg-white p-6 shadow-lg rounded-lg">
        <a href="user_dashboard.php" class="inline-flex items-center px-4 py-2 mb-4 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
            ⬅ Back
        </a>

        <!-- Paintings Bids Section -->
        <h2 class="text-2xl font-bold text-gray-700 mb-4">Your All Bids</h2>
        <div class="overflow-x-auto mb-6">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">Painting</th>
                        <th class="border p-2">Image</th>
                        <th class="border p-2">Seller</th>
                        <th class="border p-2">Bid Amount</th>
                        <th class="border p-2">Highest Bid</th>
                        <th class="border p-2">Currency</th>
                        <th class="border p-2">Start Date</th>
                        <th class="border p-2">End Date</th>
                        <th class="border p-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $paintingsResult->fetch_assoc()) { ?>
                    <tr class="bg-white hover:bg-gray-100">
                        <td class="border p-2"><?php echo $row['artwork_name']; ?></td>
                        <td class="border p-2">
                            <img src="../<?php echo $row['artwork_image']; ?>" alt="Painting" class="w-16 h-16 object-cover rounded-md">
                        </td>
                        <td class="border p-2"><?php echo $row['seller_name']; ?></td>
                        <td class="border p-2 font-semibold text-blue-600"><?php echo $row['bid_amount']; ?></td>
                        <td class="border p-2 font-semibold text-green-600"><?php echo $row['highest_bid'] ?? "N/A"; ?></td>
                        <td class="border p-2"><?php echo $row['currency']; ?></td>
                        <td class="border p-2"><?php echo $row['start_date']; ?></td>
                        <td class="border p-2"><?php echo $row['end_date']; ?></td>
                        <td class="border p-2">
                            <?php 
                                if ($row['winner_status']) {
                                    echo "<span class='px-2 py-1 bg-green-500 text-white rounded'>Won</span>";
                                } else {
                                    echo "<span class='px-2 py-1 bg-gray-500 text-white rounded'>" . ucfirst($row['bid_status']) . "</span>";
                                }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Sculptures Bids Section -->
        
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                  
                </thead>
                <tbody>
                    <?php while ($row = $sculpturesResult->fetch_assoc()) { ?>
                    <tr class="bg-white hover:bg-gray-100">
                        <td class="border p-2"><?php echo $row['artwork_name']; ?></td>
                        <td class="border p-2">
                            <img src="../uploads/<?php echo $row['artwork_image']; ?>" alt="Sculpture" class="w-16 h-16 object-cover rounded-md">
                        </td>
                        <td class="border p-2"><?php echo $row['seller_name']; ?></td>
                        <td class="border p-2 font-semibold text-blue-600"><?php echo $row['bid_amount']; ?></td>
                        <td class="border p-2 font-semibold text-green-600"><?php echo $row['highest_bid'] ?? "N/A"; ?></td>
                        <td class="border p-2"><?php echo $row['currency']; ?></td>
                        <td class="border p-2"><?php echo $row['start_date']; ?></td>
                        <td class="border p-2"><?php echo $row['end_date']; ?></td>
                        <td class="border p-2">
                            <?php 
                                if ($row['winner_status']) {
                                    echo "<span class='px-2 py-1 bg-green-500 text-white rounded'>Won</span>";
                                } else {
                                    echo "<span class='px-2 py-1 bg-gray-500 text-white rounded'>" . ucfirst($row['bid_status']) . "</span>";
                                }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
