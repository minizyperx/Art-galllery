<?php
session_start();
include 'config.php'; // Database connection

// Check if seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['seller_id'];

// Handle new painting submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_painting'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $starting_bid = $_POST['starting_bid'];
    
    $image = $_FILES['image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO paintings (seller_id, title, description, starting_bid, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issds", $seller_id, $title, $description, $starting_bid, $image);
        $stmt->execute();
    }
}

// Fetch seller's paintings
$stmt = $conn->prepare("SELECT * FROM paintings WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Welcome, Seller</h2>
    <h3>Add New Painting</h3>
    <form method="POST" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" required><br>
        <label>Description:</label>
        <textarea name="description" required></textarea><br>
        <label>Starting Bid:</label>
        <input type="number" name="starting_bid" required><br>
        <label>Image:</label>
        <input type="file" name="image" required><br>
        <button type="submit" name="add_painting">Add Painting</button>
    </form>

    <h3>Your Listed Paintings</h3>
    <table border="1">
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Starting Bid</th>
            <th>Image</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['title']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td><?php echo $row['starting_bid']; ?></td>
                <td><img src="uploads/<?php echo $row['image']; ?>" width="100"></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>