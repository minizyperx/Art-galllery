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
        s.name AS seller_name
    FROM bids b
    JOIN paintings p ON b.p_id = p.id
    JOIN sellers s ON p.seller_id = s.id
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
        sc.title AS artwork_name, sc.image_url AS artwork_image,
        s.name AS seller_name
    FROM bids b
    JOIN sculptures sc ON b.s_id = sc.id
    JOIN sellers s ON sc.seller_id = s.id
    WHERE b.u_id = ? AND b.s_id IS NOT NULL
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
        <?php if ($paintingsResult->num_rows > 0): ?>
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">Your Painting Bids</h2>
            <div class="overflow-x-auto">
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
                        <?php while ($row = $paintingsResult->fetch_assoc()): ?>
                        <tr class="bg-white hover:bg-gray-100">
                            <td class="border p-2"><?php echo htmlspecialchars($row['artwork_name']); ?></td>
                            <td class="border p-2">
                                <img src="../<?php echo htmlspecialchars($row['artwork_image']); ?>" alt="Painting" class="w-16 h-16 object-cover rounded-md">
                            </td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['seller_name']); ?></td>
                            <td class="border p-2 font-semibold text-blue-600"><?php echo $row['bid_amount']; ?></td>
                            <td class="border p-2 font-semibold text-green-600"><?php echo $row['highest_bid'] ?? "N/A"; ?></td>
                            <td class="border p-2"><?php echo $row['currency']; ?></td>
                            <td class="border p-2"><?php echo date('M j, Y', strtotime($row['start_date'])); ?></td>
                            <td class="border p-2"><?php echo date('M j, Y', strtotime($row['end_date'])); ?></td>
                            <td class="border p-2">
                                <?php if ($row['winner_status']): ?>
                                    <span class='px-2 py-1 bg-green-500 text-white rounded'>Won</span>
                                <?php else: ?>
                                    <span class='px-2 py-1 bg-gray-500 text-white rounded'><?php echo ucfirst($row['bid_status']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sculptures Bids Section -->
        <?php if ($sculpturesResult->num_rows > 0): ?>
        <div class="mt-10">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">Your Sculpture Bids</h2>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border p-2">Sculpture</th>
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
                        <?php while ($row = $sculpturesResult->fetch_assoc()): ?>
                        <tr class="bg-white hover:bg-gray-100">
                            <td class="border p-2"><?php echo htmlspecialchars($row['artwork_name']); ?></td>
                            <td class="border p-2">
                                <img src="../<?php echo htmlspecialchars($row['artwork_image']); ?>" alt="Sculpture" class="w-16 h-16 object-cover rounded-md">
                            </td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['seller_name']); ?></td>
                            <td class="border p-2 font-semibold text-blue-600"><?php echo $row['bid_amount']; ?></td>
                            <td class="border p-2 font-semibold text-green-600"><?php echo $row['highest_bid'] ?? "N/A"; ?></td>
                            <td class="border p-2"><?php echo $row['currency']; ?></td>
                            <td class="border p-2"><?php echo date('M j, Y', strtotime($row['start_date'])); ?></td>
                            <td class="border p-2"><?php echo date('M j, Y', strtotime($row['end_date'])); ?></td>
                            <td class="border p-2">
                                <?php if ($row['winner_status']): ?>
                                    <span class='px-2 py-1 bg-green-500 text-white rounded'>Won</span>
                                <?php else: ?>
                                    <span class='px-2 py-1 bg-gray-500 text-white rounded'><?php echo ucfirst($row['bid_status']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($paintingsResult->num_rows == 0 && $sculpturesResult->num_rows == 0): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
            You haven't placed any bids yet.
        </div>
        <?php endif; ?>
    </div>

</body>
</html>