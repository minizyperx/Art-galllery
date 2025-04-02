<?php
session_start(); // Start the session
include '../db/connect.php'; // Include database connection

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php");
    exit();
}

// Handle Bid Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_bid'])) {
    // Get form data
    $bidAmount = $_POST['bid_amount'];
    $paintingId = $_POST['painting_id'];
    $userId = $_SESSION['user_id']; // Get user ID from session

    // Validate bid amount
    if ($bidAmount <= 0) {
        echo "<script>alert('Bid amount must be greater than 0.');</script>";
    } else {
        // Insert bid into the database
        $sql = "INSERT INTO bids (bid_amount, p_id, u_id, start_date, end_date) VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('dii', $bidAmount, $paintingId, $userId);

        if ($stmt->execute()) {
            echo "<script>alert('Bid placed successfully!');</script>";
        } else {
            echo "<script>alert('Failed to place bid. Please try again.');</script>";
        }

        $stmt->close();
    }
}

// Fetch all paintings data
$sql = "SELECT * FROM paintings";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $paintings = $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as an associative array
} else {
    $paintings = []; // No data found
}

// Fetch the highest bid for each painting
$highestBids = [];
foreach ($paintings as $painting) {
    $paintingId = $painting['id'];
    $sql = "SELECT u.username, b.bid_amount 
            FROM bids b 
            JOIN users u ON b.u_id = u.id 
            WHERE b.p_id = ? 
            ORDER BY b.bid_amount DESC 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $paintingId);
    $stmt->execute();
    $stmt->bind_result($username, $bidAmount);
    $stmt->fetch();
    $stmt->close();

    $highestBids[$paintingId] = [
        'username' => $username,
        'bid_amount' => $bidAmount,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Paintings Gallery</title>
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
                    <li><a href="../index.php" class="hover:text-gray-400">Home</a></li>
                    <li><a href="#" class="hover:text-gray-400">Gallery</a></li>
                    <li><a href="#" class="hover:text-gray-400">About</a></li>
                    <li><a href="#" class="hover:text-gray-400">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <h1 class="text-4xl font-bold text-center mb-8">Modern Paintings Gallery</h1>

        <!-- Paintings Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (empty($paintings)): ?>
                <p class="text-center text-gray-600 col-span-full">No paintings found.</p>
            <?php else: ?>
                <?php foreach ($paintings as $painting): ?>
                    <?php
                    $paintingId = $painting['id'];
                    $highestBid = $highestBids[$paintingId] ?? null;
                    $endDate = $painting['end_date']; // Use end_date from paintings table
                    $isSoldOut = $endDate && strtotime($endDate) < time();
                    ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition-transform duration-300">
                        <img src="../<?php echo htmlspecialchars($painting['painting_image']); ?>" alt="<?php echo htmlspecialchars($painting['painting_title']); ?>" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <!-- Painting Title -->
                            <h3 class="text-xl font-semibold mb-2 text-gray-800"><?php echo htmlspecialchars($painting['painting_title']); ?></h3>

                            <!-- Tags -->
                            <div class="flex flex-wrap gap-2 mb-2">
                                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2 py-1 rounded">Modern</span>
                                <span class="bg-green-100 text-green-800 text-sm font-medium px-2 py-1 rounded">Abstract</span>
                                <span class="bg-purple-100 text-purple-800 text-sm font-medium px-2 py-1 rounded">Classic</span>
                            </div>

                            <!-- Painting Details -->
                            <p class="text-gray-600"><span class="font-medium">Year:</span> <?php echo htmlspecialchars($painting['painting_year']); ?></p>
                            <p class="text-gray-600"><span class="font-medium">Cost:</span> $<?php echo htmlspecialchars($painting['painting_cost']); ?></p>
                            <p class="text-gray-600"><span class="font-medium">Status:</span> <span class="font-semibold"><?php echo htmlspecialchars($painting['status']); ?></span></p>

                            <!-- Display Highest Bid -->
                            <?php if ($highestBid): ?>
                                <p class="text-gray-600"><span class="font-medium">Highest Bid:</span> <span class="text-green-600 font-semibold">$<?php echo htmlspecialchars($highestBid['bid_amount']); ?></span> by <span class="text-blue-600 font-semibold"><?php echo htmlspecialchars($highestBid['username']); ?></span></p>
                            <?php endif; ?>

                            <!-- Countdown Timer or Sold Out -->
                            <?php if ($isSoldOut): ?>
                                <p class="text-red-600 font-semibold">Sold Out</p>
                            <?php elseif ($endDate): ?>
                                <p class="text-gray-600"><span class="font-medium">Time Left:</span> <span id="countdown-<?php echo $paintingId; ?>" class="font-semibold text-purple-600"></span></p>
                                <script>
                                    // Append time to the end_date (23:59:59)
                                    const endDate<?php echo $paintingId; ?> = new Date("<?php echo $endDate; ?>T23:59:59").getTime();
                                    const countdown<?php echo $paintingId; ?> = setInterval(() => {
                                        const now = new Date().getTime();
                                        const timeLeft = endDate<?php echo $paintingId; ?> - now;

                                        if (timeLeft <= 0) {
                                            clearInterval(countdown<?php echo $paintingId; ?>);
                                            document.getElementById('countdown-<?php echo $paintingId; ?>').innerText = "Sold Out";
                                            location.reload(); // Refresh the page to update the status
                                        } else {
                                            const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                                            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                                            document.getElementById('countdown-<?php echo $paintingId; ?>').innerText = `${hours}h ${minutes}m ${seconds}s`;
                                        }
                                    }, 1000);
                                </script>
                            <?php endif; ?>

                            <!-- Bid Button -->
                            <button
                                onclick="openBidModal(<?php echo $painting['id']; ?>)"
                                class="mt-4 w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none"
                            >
                                Place Bid
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Bid Modal -->
    <div id="bidModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 class="text-xl font-bold mb-4">Place a Bid</h2>
            <form method="POST" action="">
                <input type="hidden" id="paintingId" name="painting_id">
                <div class="mb-4">
                    <label for="bidAmount" class="block text-gray-700">Bid Amount ($)</label>
                    <input
                        type="number"
                        id="bidAmount"
                        name="bid_amount"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>
                <div class="flex justify-end">
                    <button
                        type="button"
                        onclick="closeBidModal()"
                        class="mr-2 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 focus:outline-none"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        name="place_bid"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none"
                    >
                        Submit Bid
                    </button>
                </div>
            </form>
        </div>
    </div>

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

    <!-- JavaScript -->
    <script>
        // Open Bid Modal
        function openBidModal(paintingId) {
            document.getElementById('paintingId').value = paintingId;
            document.getElementById('bidModal').classList.remove('hidden');
        }

        // Close Bid Modal
        function closeBidModal() {
            document.getElementById('bidModal').classList.add('hidden');
        }
    </script>
</body>
</html>