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
    $sculptureId = $_POST['sculpture_id'];
    $userId = $_SESSION['user_id']; // Get user ID from session

    // Validate bid amount
    if ($bidAmount <= 0) {
        echo "<script>alert('Bid amount must be greater than 0.');</script>";
    } else {
        // Insert bid into the database
        $sql = "INSERT INTO bids (bid_amount, s_id, u_id, start_date, end_date) VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('dii', $bidAmount, $sculptureId, $userId);

        if ($stmt->execute()) {
            echo "<script>alert('Bid placed successfully!');</script>";
        } else {
            echo "<script>alert('Failed to place bid. Please try again.');</script>";
        }

        $stmt->close();
    }
}

// Fetch all sculptures data
$sql = "SELECT * FROM sculptures";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $sculptures = $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as an associative array
} else {
    $sculptures = []; // No data found
}

// Fetch the highest bid for each sculpture
$highestBids = [];
foreach ($sculptures as $sculpture) {
    $sculptureId = $sculpture['id'];
    $sql = "SELECT u.username, b.bid_amount 
            FROM bids b 
            JOIN users u ON b.u_id = u.id 
            WHERE b.s_id = ? 
            ORDER BY b.bid_amount DESC 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $sculptureId);
    $stmt->execute();
    $stmt->bind_result($username, $bidAmount);
    $stmt->fetch();
    $stmt->close();

    $highestBids[$sculptureId] = [
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
    <title>Modern Sculptures Gallery</title>
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
                    <li><a href="logout.php" class="hover:text-gray-400">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <h1 class="text-4xl font-bold text-center mb-8">Modern Sculptures Gallery</h1>

        <!-- Sculptures Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (empty($sculptures)): ?>
                <p class="text-center text-gray-600 col-span-full">No sculptures found.</p>
            <?php else: ?>
                <?php foreach ($sculptures as $sculpture): ?>
                    <?php
                    $sculptureId = $sculpture['id'];
                    $highestBid = $highestBids[$sculptureId] ?? null;
                    $endDate = $sculpture['end_date']; // Use end_date from sculptures table
                    $isSoldOut = $endDate && strtotime($endDate) < time();
                    ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition-transform duration-300">
                        <img src="../<?php echo htmlspecialchars($sculpture['image_url']); ?>" alt="<?php echo htmlspecialchars($sculpture['title']); ?>" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <!-- Sculpture Title -->
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($sculpture['title']); ?></h3>

                            <!-- Sculpture Details -->
                            
                            <p class="text-gray-600">Year: <?php echo htmlspecialchars($sculpture['sculpture_year']); ?></p>
                            <p class="text-gray-600">Cost: <?php echo htmlspecialchars($sculpture['sculpture_cost']); ?></p>

                            <!-- Display Highest Bid -->
                            <?php if ($highestBid): ?>
                                <p class="text-gray-600"><span class="font-medium">Highest Bid:</span> <span class="text-green-600 font-semibold">Rs:<?php echo htmlspecialchars($highestBid['bid_amount']); ?></span> by <span class="text-blue-600 font-semibold"><?php echo htmlspecialchars($highestBid['username']); ?></span></p>
                            <?php endif; ?>
                            <p class="text-gray-600"><b>Status:</b> <?php echo htmlspecialchars($sculpture['status']); ?></p>

                            <!-- Countdown Timer or Sold Out -->
                            <?php if ($isSoldOut): ?>
                                <p class="text-red-600 font-semibold">Sold Out</p>
                            <?php elseif ($endDate): ?>
                                <p class="text-gray-600"><span class="font-medium">Time Left:</span> <span id="countdown-<?php echo $sculptureId; ?>" class="font-semibold text-purple-600"></span></p>
                                <script>
                                    // Append time to the end_date (23:59:59)
                                    const endDate<?php echo $sculptureId; ?> = new Date("<?php echo $endDate; ?>T23:59:59").getTime();
                                    const countdown<?php echo $sculptureId; ?> = setInterval(() => {
                                        const now = new Date().getTime();
                                        const timeLeft = endDate<?php echo $sculptureId; ?> - now;

                                        if (timeLeft <= 0) {
                                            clearInterval(countdown<?php echo $sculptureId; ?>);
                                            document.getElementById('countdown-<?php echo $sculptureId; ?>').innerText = "Sold Out";
                                            location.reload(); // Refresh the page to update the status
                                        } else {
                                            const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                                            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                                            document.getElementById('countdown-<?php echo $sculptureId; ?>').innerText = `${hours}h ${minutes}m ${seconds}s`;
                                        }
                                    }, 1000);
                                </script>
                            <?php endif; ?>

                            <!-- Bid Button -->
                            <button
                                onclick="openBidModal(<?php echo $sculpture['id']; ?>)"
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
                <input type="hidden" id="sculptureId" name="sculpture_id">
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
        function openBidModal(sculptureId) {
            document.getElementById('sculptureId').value = sculptureId;
            document.getElementById('bidModal').classList.remove('hidden');
        }

        // Close Bid Modal
        function closeBidModal() {
            document.getElementById('bidModal').classList.add('hidden');
        }
    </script>
</body>
</html>