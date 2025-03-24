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

// Hardcoded user ID (Replace this with session authentication in production)
$user_id = 1; 

// Handle bid submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $painting_id = $_POST['painting_id'];
    $bid_amount = $_POST['bid_amount'];

    $sql = "INSERT INTO bids (bid_amount, start_date, end_date, p_id, u_id)
            VALUES (?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dii", $bid_amount, $painting_id, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Bid placed successfully!');</script>";
    } else {
        echo "<script>alert('Error placing bid: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Get highest bid and user for a painting (replace 1 with dynamic painting ID)
$painting_id = 1;
$sql = "SELECT bids.bid_amount, users.username, users.id 
        FROM bids 
        JOIN users ON bids.u_id = users.id 
        WHERE p_id = ? 
        ORDER BY bid_amount DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $painting_id);
$stmt->execute();
$result = $stmt->get_result();
$highest_bid = $result->fetch_assoc();

// Assign colors based on user ID
$user_colors = [
    1 => "#ff0000",  // Red
    2 => "#0000ff",  // Blue
    3 => "#008000",  // Green
    4 => "#ff00ff"   // Purple
];

$bidder_color = isset($highest_bid['id']) ? ($user_colors[$highest_bid['id']] ?? "#ffffff") : "#ffffff";

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bid on Paintings</title>
    <style>
        body {
            font-family: sans-serif;
            background: linear-gradient(to bottom, #ff9800, #f44336);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            color: white;
            background-image: url('bid.jpeg'); /* Ensure bid.jpeg is in the correct folder */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        h1, h2 {
            text-align: center;
            margin-top: 20px;
            color: #0d0767;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        #painting_list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            max-width: 900px;
        }

        .painting-item {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 300px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .painting-item:hover {
            transform: translateY(-5px);
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
        }

        .painting-item img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .painting-item p {
            margin-bottom: 10px;
        }

        .painting-item form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .painting-item input[type="number"] {
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(100% - 18px);
            box-sizing: border-box;
        }

        .painting-item button {
            padding: 10px 15px;
            background-color: #0d0767;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .painting-item button:hover {
            background-color: #05043c;
        }

        @media (max-width: 768px) {
            #painting_list {
                flex-direction: column;
                align-items: center;
            }
            .painting-item {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <h1>Bid on Art</h1>
    <h2>Paintings</h2>

    <div id="painting_list">
        <div class="painting-item">
            <img src="painting1.jpg" alt="Painting 1">
            <p>Painting Name</p>
            <p style="color: <?php echo $bidder_color; ?>;">
                Highest Bid: $<?php echo $highest_bid['bid_amount'] ?? '0'; ?> 
                by <?php echo $highest_bid['username'] ?? 'No bids yet'; ?>
            </p>
            <form action="" method="post">
                <input type="hidden" name="painting_id" value="1">
                <label for="bid_amount">Your Bid:</label>
                <input type="number" id="bid_amount" name="bid_amount" required>
                <button type="submit">Place Bid</button>
            </form>
        </div>
    </div>
</body>
</html>
