<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "art_gallery";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session and get user ID (replace with your actual session handling)
session_start();
$user_id = $_SESSION['user_id'] ?? 1; // Fallback to 1 for testing

// Handle bid submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bid_amount'])) {
    $artwork_id = $_POST['artwork_id'];
    $artwork_type = $_POST['artwork_type']; // 'painting' or 'sculpture'
    $bid_amount = $_POST['bid_amount'];
    $currency = $_POST['currency'] ?? 'INR';
    
    // Validate bid amount is higher than current highest bid
    $check_sql = "SELECT MAX(bid_amount) as max_bid FROM bids WHERE ";
    $check_sql .= ($artwork_type == 'painting') ? "p_id = ?" : "s_id = ?";
    
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_max = $result->fetch_assoc()['max_bid'] ?? 0;
    
    if ($bid_amount > $current_max) {
        // Insert new bid
        $insert_sql = "INSERT INTO bids (bid_amount, start_date, end_date, ";
        $insert_sql .= ($artwork_type == 'painting') ? "p_id" : "s_id";
        $insert_sql .= ", u_id, currency, bid_status, winner_status) VALUES (?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), ?, ?, ?, 'pending', 0)";
        
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("diis", $bid_amount, $artwork_id, $user_id, $currency);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Bid placed successfully!";
        } else {
            $_SESSION['error'] = "Error placing bid: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Your bid must be higher than the current highest bid of " . $current_max;
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch user's bidding activity
$bidding_activity = [
    'paintings_bid_on' => [],
    'sculptures_bid_on' => [],
    'paintings_won' => [],
    'sculptures_won' => []
];

// Paintings user has bid on
$sql = "SELECT p.*, b.bid_amount, b.start_date, b.end_date, b.bid_status, b.currency 
        FROM paintings p
        JOIN bids b ON p.p_id = b.p_id
        WHERE b.u_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bidding_activity['paintings_bid_on'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Sculptures user has bid on
$sql = "SELECT s.*, b.bid_amount, b.start_date, b.end_date, b.bid_status, b.currency 
        FROM sculptures s
        JOIN bids b ON s.s_id = b.s_id
        WHERE b.u_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bidding_activity['sculptures_bid_on'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Paintings user has won
$sql = "SELECT p.*, b.bid_amount, b.currency 
        FROM paintings p
        JOIN bids b ON p.p_id = b.p_id
        WHERE b.u_id = ? AND b.winner_status = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bidding_activity['paintings_won'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Sculptures user has won
$sql = "SELECT s.*, b.bid_amount, b.currency 
        FROM sculptures s
        JOIN bids b ON s.s_id = b.s_id
        WHERE b.u_id = ? AND b.winner_status = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bidding_activity['sculptures_won'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch some featured artworks for bidding
$featured_paintings = [];
$featured_sculptures = [];

$sql = "SELECT * FROM paintings WHERE end_date > NOW() LIMIT 3";
$result = $conn->query($sql);
$featured_paintings = $result->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT * FROM sculptures WHERE end_date > NOW() LIMIT 3";
$result = $conn->query($sql);
$featured_sculptures = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Gallery Bidding</title>
    <style>
        :root {
            --primary-color: #0d0767;
            --secondary-color: #ff9800;
            --light-bg: rgba(255, 255, 255, 0.9);
            --dark-text: #333;
            --light-text: #fff;
            --success-color: #4CAF50;
            --warning-color: #ff9800;
            --error-color: #f44336;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: var(--dark-text);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: var(--light-text);
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1, h2, h3 {
            color: var(--primary-color);
        }
        
        .section {
            background-color: var(--light-bg);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .artwork-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .artwork-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .artwork-card:hover {
            transform: translateY(-5px);
        }
        
        .artwork-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .artwork-info {
            padding: 15px;
        }
        
        .artwork-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 1.1em;
        }
        
        .artwork-artist {
            color: #666;
            margin-bottom: 10px;
        }
        
        .artwork-price {
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .bid-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .bid-form input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .bid-form button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .bid-form button:hover {
            background-color: #05043c;
        }
        
        .status-pending {
            color: var(--warning-color);
        }
        
        .status-accepted {
            color: var(--success-color);
        }
        
        .status-rejected {
            color: var(--error-color);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .success {
            background-color: #dff0d8;
            color: var(--success-color);
        }
        
        .error {
            background-color: #f2dede;
            color: var(--error-color);
        }
        
        @media (max-width: 768px) {
            .artwork-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Art Gallery Bidding Platform</h1>
        </div>
    </header>
    
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Featured Artworks Section -->
        <section class="section">
            <h2>Featured Paintings</h2>
            <?php if (empty($featured_paintings)): ?>
                <div class="empty-state">No featured paintings available for bidding at this time.</div>
            <?php else: ?>
                <div class="artwork-grid">
                    <?php foreach ($featured_paintings as $painting): ?>
                        <div class="artwork-card">
                            <img src="<?= htmlspecialchars($painting['image_url']) ?>" alt="<?= htmlspecialchars($painting['title']) ?>" class="artwork-image">
                            <div class="artwork-info">
                                <div class="artwork-title"><?= htmlspecialchars($painting['title']) ?></div>
                                <div class="artwork-artist">By <?= htmlspecialchars($painting['artist']) ?></div>
                                <div class="artwork-price">Current Bid: <?= $painting['currency'] ?? 'INR' ?> <?= number_format($painting['current_bid'] ?? 0, 2) ?></div>
                                <form class="bid-form" method="post">
                                    <input type="hidden" name="artwork_id" value="<?= $painting['p_id'] ?>">
                                    <input type="hidden" name="artwork_type" value="painting">
                                    <input type="number" name="bid_amount" placeholder="Enter your bid" min="<?= ($painting['current_bid'] ?? 0) + 1 ?>" step="0.01" required>
                                    <input type="hidden" name="currency" value="<?= $painting['currency'] ?? 'INR' ?>">
                                    <button type="submit">Place Bid</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <section class="section">
            <h2>Featured Sculptures</h2>
            <?php if (empty($featured_sculptures)): ?>
                <div class="empty-state">No featured sculptures available for bidding at this time.</div>
            <?php else: ?>
                <div class="artwork-grid">
                    <?php foreach ($featured_sculptures as $sculpture): ?>
                        <div class="artwork-card">
                            <img src="<?= htmlspecialchars($sculpture['image_url']) ?>" alt="<?= htmlspecialchars($sculpture['title']) ?>" class="artwork-image">
                            <div class="artwork-info">
                                <div class="artwork-title"><?= htmlspecialchars($sculpture['title']) ?></div>
                                <div class="artwork-artist">By <?= htmlspecialchars($sculpture['artist']) ?></div>
                                <div class="artwork-price">Current Bid: <?= $sculpture['currency'] ?? 'INR' ?> <?= number_format($sculpture['current_bid'] ?? 0, 2) ?></div>
                                <form class="bid-form" method="post">
                                    <input type="hidden" name="artwork_id" value="<?= $sculpture['s_id'] ?>">
                                    <input type="hidden" name="artwork_type" value="sculpture">
                                    <input type="number" name="bid_amount" placeholder="Enter your bid" min="<?= ($sculpture['current_bid'] ?? 0) + 1 ?>" step="0.01" required>
                                    <input type="hidden" name="currency" value="<?= $sculpture['currency'] ?? 'INR' ?>">
                                    <button type="submit">Place Bid</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- My Bidding Activity Section -->
        <section class="section">
            <h2>My Bidding Activity</h2>
            
            <h3>Paintings I've Bid On</h3>
            <?php if (empty($bidding_activity['paintings_bid_on'])): ?>
                <div class="empty-state">You haven't bid on any paintings yet.</div>
            <?php else: ?>
                <div class="artwork-grid">
                    <?php foreach ($bidding_activity['paintings_bid_on'] as $bid): ?>
                        <div class="artwork-card">
                            <img src="<?= htmlspecialchars($bid['image_url']) ?>" alt="<?= htmlspecialchars($bid['title']) ?>" class="artwork-image">
                            <div class="artwork-info">
                                <div class="artwork-title"><?= htmlspecialchars($bid['title']) ?></div>
                                <div class="artwork-artist">By <?= htmlspecialchars($bid['artist']) ?></div>
                                <div class="artwork-price">Your Bid: <?= $bid['currency'] ?> <?= number_format($bid['bid_amount'], 2) ?></div>
                                <div class="bid-status status-<?= $bid['bid_status'] ?>">Status: <?= ucfirst($bid['bid_status']) ?></div>
                                <div>Bid Period: <?= date('M j, Y', strtotime($bid['start_date'])) ?> - <?= date('M j, Y', strtotime($bid['end_date'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <h3>Sculptures I've Bid On</h3>
            <?php if (empty($bidding_activity['sculptures_bid_on'])): ?>
                <div class="empty-state">You haven't bid on any sculptures yet.</div>
            <?php else: ?>
                <div class="artwork-grid">
                    <?php foreach ($bidding_activity['sculptures_bid_on'] as $bid): ?>
                        <div class="artwork-card">
                            <img src="<?= htmlspecialchars($bid['image_url']) ?>" alt="<?= htmlspecialchars($bid['title']) ?>" class="artwork-image">
                            <div class="artwork-info">
                                <div class="artwork-title"><?= htmlspecialchars($bid['title']) ?></div>
                                <div class="artwork-artist">By <?= htmlspecialchars($bid['artist']) ?></div>
                                <div class="artwork-price">Your Bid: <?= $bid['currency'] ?> <?= number_format($bid['bid_amount'], 2) ?></div>
                                <div class="bid-status status-<?= $bid['bid_status'] ?>">Status: <?= ucfirst($bid['bid_status']) ?></div>
                                <div>Bid Period: <?= date('M j, Y', strtotime($bid['start_date'])) ?> - <?= date('M j, Y', strtotime($bid['end_date'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- My Won Bids Section -->
        <section class="section">
            <h2>My Won Bids</h2>
            
            <h3>Paintings I've Won</h3>
            <?php if (empty($bidding_activity['paintings_won'])): ?>
                <div class="empty-state">You haven't won any paintings yet.</div>
            <?php else: ?>
                <div class="artwork-grid">
                    <?php foreach ($bidding_activity['paintings_won'] as $bid): ?>
                        <div class="artwork-card">
                            <img src="<?= htmlspecialchars($bid['image_url']) ?>" alt="<?= htmlspecialchars($bid['title']) ?>" class="artwork-image">
                            <div class="artwork-info">
                                <div class="artwork-title"><?= htmlspecialchars($bid['title']) ?></div>
                                <div class="artwork-artist">By <?= htmlspecialchars($bid['artist']) ?></div>
                                <div class="artwork-price">Winning Bid: <?= $bid['currency'] ?> <?= number_format($bid['bid_amount'], 2) ?></div>
                                <div class="status-accepted">Congratulations! You won this auction</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <h3>Sculptures I've Won</h3>
            <?php if (empty($bidding_activity['sculptures_won'])): ?>
                <div class="empty-state">You haven't won any sculptures yet.</div>
            <?php else: ?>
                <div class="artwork-grid">
                    <?php foreach ($bidding_activity['sculptures_won'] as $bid): ?>
                        <div class="artwork-card">
                            <img src="<?= htmlspecialchars($bid['image_url']) ?>" alt="<?= htmlspecialchars($bid['title']) ?>" class="artwork-image">
                            <div class="artwork-info">
                                <div class="artwork-title"><?= htmlspecialchars($bid['title']) ?></div>
                                <div class="artwork-artist">By <?= htmlspecialchars($bid['artist']) ?></div>
                                <div class="artwork-price">Winning Bid: <?= $bid['currency'] ?> <?= number_format($bid['bid_amount'], 2) ?></div>
                                <div class="status-accepted">Congratulations! You won this auction</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>