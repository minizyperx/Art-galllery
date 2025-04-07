<?php
session_start();
include '../db/connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php");
    exit();
}

// Handle Bid Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_bid'])) {
    $bidAmount = floatval($_POST['bid_amount']);
    $paintingId = intval($_POST['painting_id']);
    $userId = $_SESSION['user_id'];
    $currency = 'INR'; // Default currency

    // Get painting details
    $costSql = "SELECT painting_cost, start_date, end_date FROM paintings WHERE id = ?";
    $costStmt = $conn->prepare($costSql);
    $costStmt->bind_param('i', $paintingId);
    $costStmt->execute();
    $costResult = $costStmt->get_result();
    $paintingData = $costResult->fetch_assoc();
    $paintingCost = $paintingData['painting_cost'];
    $startDate = $paintingData['start_date'];
    $endDate = $paintingData['end_date'];
    $isSoldOut = $endDate && strtotime($endDate) < time();
    $costStmt->close();

    // Validate bid timing
    if ($startDate && strtotime($startDate) > time()) {
        $_SESSION['error'] = 'Bidding has not started yet for this painting.';
    } elseif ($isSoldOut) {
        $_SESSION['error'] = 'This painting is already sold out.';
    } elseif ($bidAmount <= 0) {
        $_SESSION['error'] = 'Bid amount must be greater than 0.';
    } elseif ($bidAmount <= $paintingCost) {
        $_SESSION['error'] = 'Bid amount must be greater than the painting cost (Rs '.number_format($paintingCost, 2).').';
    } else {
        // Check if bid is higher than current highest bid
        $checkSql = "SELECT MAX(bid_amount) as max_bid FROM bids WHERE p_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('i', $paintingId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();
        $maxBid = $row['max_bid'] ?? 0;
        
        if ($maxBid > 0 && $bidAmount <= $maxBid) {
            $_SESSION['error'] = 'Your bid must be higher than the current highest bid (Rs '.number_format($maxBid, 2).')';
        } else {
            // Insert bid into the database with all required fields
            $sql = "INSERT INTO bids (bid_amount, p_id, u_id, start_date, end_date, bid_status, winner_status, currency, highest_bid) 
                    VALUES (?, ?, ?, NOW(), ?, 'pending', 0, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            // Use the painting's end date for the bid end date
            $stmt->bind_param('diisss', $bidAmount, $paintingId, $userId, $endDate, $currency, $bidAmount);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Bid placed successfully!';
                // Update the highest bid in the bids table
                $updateSql = "UPDATE bids SET highest_bid = ? WHERE p_id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param('di', $bidAmount, $paintingId);
                $updateStmt->execute();
                $updateStmt->close();
                
                // Redirect to prevent form resubmission
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['error'] = 'Failed to place bid. Please try again.';
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}

// Fetch all paintings data with their highest bids and bidder info
$paintings = [];
$sql = "SELECT p.*, 
        (SELECT MAX(bid_amount) FROM bids WHERE p_id = p.id) AS highest_bid,
        (SELECT u.username FROM bids b JOIN users u ON b.u_id = u.id WHERE b.p_id = p.id ORDER BY b.bid_amount DESC LIMIT 1) AS highest_bidder,
        (SELECT b.currency FROM bids b WHERE b.p_id = p.id ORDER BY b.bid_amount DESC LIMIT 1) AS bid_currency
        FROM paintings p";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $paintings = $result->fetch_all(MYSQLI_ASSOC);
}

// Update status to 'Sold' for paintings where end_date has passed
if (!empty($paintings)) {
    foreach ($paintings as $painting) {
        if ($painting['end_date'] && strtotime($painting['end_date']) < time() && $painting['status'] !== 'Sold') {
            // Update painting status
            $updateSql = "UPDATE paintings SET status = 'Sold' WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('i', $painting['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Update the winning bid
            $winnerSql = "UPDATE bids 
                         SET bid_status = 'accepted', winner_status = 1 
                         WHERE p_id = ? AND bid_amount = (SELECT MAX(bid_amount) FROM bids WHERE p_id = ?)";
            $winnerStmt = $conn->prepare($winnerSql);
            $winnerStmt->bind_param('ii', $painting['id'], $painting['id']);
            $winnerStmt->execute();
            $winnerStmt->close();
            
            // Reject all other bids for this painting
            $rejectSql = "UPDATE bids 
                         SET bid_status = 'rejected' 
                         WHERE p_id = ? AND bid_amount < (SELECT MAX(bid_amount) FROM bids WHERE p_id = ?)";
            $rejectStmt = $conn->prepare($rejectSql);
            $rejectStmt->bind_param('ii', $painting['id'], $painting['id']);
            $rejectStmt->execute();
            $rejectStmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Paintings Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .painting-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .painting-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .bid-btn {
            transition: all 0.2s ease;
        }
        .bid-btn:hover {
            transform: scale(1.05);
        }
        .modal {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .not-started {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        .countdown {
            font-family: monospace;
        }
        .sold-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ef4444;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Notification Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Header with Back Button -->
    <header class="bg-indigo-700 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-4">
            <a href="user_dashboard.php" class="inline-flex items-center px-4 py-2 mb-4 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
    ⬅ Back
</a>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-palette text-2xl"></i>
                    <h1 class="text-xl font-bold">Paintings Gallery</h1>
                </div>
            </div>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="user_dashboard.php" class="hover:text-indigo-200 flex items-center">
                        <i class="fas fa-home mr-1"></i> Home</a></li>
                    <li><a href="#" class="hover:text-indigo-200 flex items-center">
                    
                    <li><a href="logout.php" class="hover:text-indigo-200 flex items-center">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Modern Painting Collection</h2>
            <p class="text-gray-600">Bid on your favorite paintings and take home a masterpiece</p>
        </div>

        <!-- Paintings Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php if (empty($paintings)): ?>
                <div class="col-span-full text-center py-10">
                    <i class="fas fa-sad-tear text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-600 text-lg">No paintings available for bidding at this time.</p>
                </div>
            <?php else: ?>
                <?php foreach ($paintings as $painting): ?>
                    <?php
                    $endDate = $painting['end_date'];
                    $startDate = $painting['start_date'];
                    $isSoldOut = $endDate && strtotime($endDate) < time();
                    $isNotStarted = $startDate && strtotime($startDate) > time();
                    $isActive = !$isSoldOut && !$isNotStarted;
                    $currencySymbol = ($painting['bid_currency'] ?? 'INR') === 'INR' ? 'Rs.' : '$';
                    ?>
                    <div class="painting-card bg-white rounded-lg overflow-hidden <?php echo $isNotStarted ? 'not-started' : ''; ?>">
                        <div class="relative">
                            <img src="../<?php echo htmlspecialchars($painting['painting_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($painting['painting_title']); ?>" 
                                 class="w-full h-56 object-cover">
                            <?php if ($isSoldOut): ?>
                                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                    <span class="text-white text-xl font-bold bg-red-500 px-3 py-1 rounded">SOLD</span>
                                </div>
                                <span class="sold-badge">Sold for <?php echo $currencySymbol . number_format($painting['highest_bid'] ?? $painting['painting_cost'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-5">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">
                                <?php echo htmlspecialchars($painting['painting_title']); ?>
                            </h3>
                            
                            <div class="flex justify-between text-sm text-gray-600 mb-3">
                                <span><i class="fas fa-calendar-alt mr-1"></i> <?php echo htmlspecialchars($painting['painting_year']); ?></span>
                                <span><i class="fas fa-tag mr-1"></i> <?php echo $currencySymbol . htmlspecialchars($painting['painting_cost']); ?></span>
                            </div>
                            
                            <!-- Show highest bid for all paintings -->
                            <?php if ($painting['highest_bid']): ?>
                                <div class="bg-blue-50 p-3 rounded-lg mb-3">
                                    <p class="text-blue-800 font-medium">
                                        <i class="fas fa-trophy mr-1"></i> Highest Bid: 
                                        <span class="font-bold"><?php echo $currencySymbol . number_format($painting['highest_bid'], 2); ?></span>
                                    </p>
                                    <?php if ($painting['highest_bidder']): ?>
                                        <p class="text-sm text-blue-700 mt-1">
                                            By: <?php echo htmlspecialchars($painting['highest_bidder']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-yellow-50 p-3 rounded-lg mb-3">
                                    <p class="text-yellow-800 font-medium">
                                        <i class="fas fa-info-circle mr-1"></i> No bids placed
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($isActive): ?>
                                <div class="flex justify-between items-center mb-4">
                                    <div class="text-sm text-purple-700">
                                        <i class="fas fa-clock mr-1"></i>
                                        <span id="countdown-<?php echo $painting['id']; ?>" class="font-medium countdown"></span>
                                    </div>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                        Active
                                    </span>
                                </div>
                                
                                <button onclick="openBidModal(<?php echo $painting['id']; ?>, <?php echo max($painting['highest_bid'] ?? 0, $painting['painting_cost']); ?>, <?php echo $painting['painting_cost']; ?>)" 
                                        class="bid-btn w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-medium flex items-center justify-center">
                                    <i class="fas fa-gavel mr-2"></i> Place Bid
                                </button>
                                
                                <script>
                                    // Countdown timer
                                    const endDate<?php echo $painting['id']; ?> = new Date("<?php echo $painting['end_date']; ?>T23:59:59").getTime();
                                    const timer<?php echo $painting['id']; ?> = setInterval(() => {
                                        const now = new Date().getTime();
                                        const distance = endDate<?php echo $painting['id']; ?> - now;
                                        
                                        if (distance < 0) {
                                            clearInterval(timer<?php echo $painting['id']; ?>);
                                            document.getElementById('countdown-<?php echo $painting['id']; ?>').innerText = "Auction ended";
                                            location.reload();
                                        } else {
                                            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                            
                                            let countdownText = '';
                                            if (days > 0) countdownText += `${days}d `;
                                            countdownText += `${hours}h ${minutes}m ${seconds}s`;
                                            document.getElementById('countdown-<?php echo $painting['id']; ?>').innerText = countdownText;
                                        }
                                    }, 1000);
                                </script>
                            <?php elseif ($isNotStarted): ?>
                                <div class="text-center py-2 text-yellow-600">
                                    <i class="fas fa-clock mr-1"></i> Bidding starts: <?php echo date('M j, Y H:i', strtotime($startDate)); ?>
                                </div>
                                <button class="w-full bg-gray-300 text-gray-600 py-2 rounded-lg font-medium cursor-not-allowed">
                                    <i class="fas fa-ban mr-2"></i> Not Started
                                </button>
                            <?php else: ?>
                                <div class="text-center py-2 text-red-600">
                                    <i class="fas fa-times-circle mr-1"></i> Bidding closed
                                </div>
                                <button class="w-full bg-gray-300 text-gray-600 py-2 rounded-lg font-medium cursor-not-allowed">
                                    <i class="fas fa-ban mr-2"></i> Closed
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Bid Modal -->
    <div id="bidModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-md mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Place Your Bid</h3>
                    <button onclick="closeBidModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" id="modalPaintingId" name="painting_id">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Current Highest Bid</label>
                        <div class="bg-gray-100 p-3 rounded-lg">
                            <p id="currentBidDisplay" class="text-2xl font-bold text-indigo-600">Rs.0.00</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Painting Cost</label>
                        <div class="bg-gray-100 p-3 rounded-lg">
                            <p id="paintingCostDisplay" class="text-xl font-bold text-indigo-600">Rs.0.00</p>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="bidAmount" class="block text-gray-700 mb-2">Your Bid Amount (Rs.)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rs.</span>
                            <input type="number" id="bidAmount" name="bid_amount" 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                                   step="0.01" min="0" required>
                        </div>
                        <p id="bidError" class="text-red-500 text-sm mt-1 hidden">Your bid must be higher than both the painting cost and current highest bid.</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeBidModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" name="place_bid" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Submit Bid
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <h3 class="text-xl font-bold mb-2">Paintings Gallery</h3>
                    <p class="text-gray-400">Discover and bid on unique paintings</p>
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-6 pt-6 text-center text-gray-400 text-sm">
                <p>&copy; <?php echo date('Y'); ?> Paintings Gallery. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Modal functions
        function openBidModal(paintingId, currentHighestBid, paintingCost) {
            document.getElementById('modalPaintingId').value = paintingId;
            document.getElementById('currentBidDisplay').textContent = 
                'Rs.' + (currentHighestBid ? currentHighestBid.toFixed(2) : '0.00');
            document.getElementById('paintingCostDisplay').textContent = 
                'Rs.' + paintingCost.toFixed(2);
            document.getElementById('bidModal').classList.remove('hidden');
            
            // Set minimum bid amount (must be higher than both painting cost and current highest bid)
            const minBid = Math.max(currentHighestBid, paintingCost) + 0.01;
            const bidAmountInput = document.getElementById('bidAmount');
            bidAmountInput.min = minBid.toFixed(2);
            bidAmountInput.value = minBid.toFixed(2);
            
            // Validate bid amount in real-time
            bidAmountInput.addEventListener('input', function() {
                const bidError = document.getElementById('bidError');
                if (this.value && parseFloat(this.value) <= Math.max(currentHighestBid, paintingCost)) {
                    bidError.classList.remove('hidden');
                } else {
                    bidError.classList.add('hidden');
                }
            });
        }

        function closeBidModal() {
            document.getElementById('bidModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('bidModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeBidModal();
            }
        });

        // Auto-hide notifications after 5 seconds
        setTimeout(() => {
            const notifications = document.querySelectorAll('[role="alert"]');
            notifications.forEach(notification => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>