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

// Get painting ID from URL parameter
if (isset($_GET['id'])) {
    $painting_id = intval($_GET['id']);
    
    $sql = "SELECT painting_title, painting_image, painting_year, painting_cost, start_date, end_date, status 
            FROM paintings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $painting_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $painting = $result->fetch_assoc();
    } else {
        echo "Painting not found.";
        exit();
    }
} else {
    echo "Invalid request.";
    exit();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Painting Details</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        .details-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
        }
        img {
            max-width: 100%;
            border-radius: 8px;
        }
        .details-container p {
            font-size: 16px;
            color: #555;
        }
        .back-link, .bid-button {
            display: block;
            margin-top: 15px;
            text-decoration: none;
            color: white;
            background: #0d0767;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .bid-button {
            background: #28a745;
        }
        .bid-button:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="details-container">
        <img src="<?php echo $painting['painting_image']; ?>" alt="<?php echo $painting['painting_title']; ?>">
        <h2><?php echo $painting['painting_title']; ?></h2>
        <p><strong>Year:</strong> <?php echo $painting['painting_year']; ?></p>
        <p><strong>Price:</strong> $<?php echo number_format($painting['painting_cost'], 2); ?></p>
        <p><strong>Start Date:</strong> <?php echo $painting['start_date']; ?></p>
        <p><strong>End Date:</strong> <?php echo $painting['end_date']; ?></p>
        <p><strong>Status:</strong> <?php echo $painting['status']; ?></p>
        
        <a class="back-link" href="index.php">Back to Gallery</a>
        <a class="bid-button" href="bid.php?id=<?php echo $painting_id; ?>">Place a Bid</a>
    </div>
</body>
</html>
