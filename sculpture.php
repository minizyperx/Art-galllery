<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "art_gallery";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the seller is logged in
if (!isset($_SESSION['seller_id'])) {
    die("You must be logged in to view this page.");
}

$seller_id = $_SESSION['seller_id'];

// Check if editing an existing sculpture
$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sculpture = null;

if ($edit_id) {
    $stmt = $conn->prepare("SELECT * FROM sculptures WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $edit_id, $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sculpture = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sculpture_title = trim($_POST["sculpture_title"]);
    $start_date = $_POST["start_date"];
    $sculpture_year = $_POST["sculpture_year"];
    $sculpture_cost = $_POST["sculpture_cost"];
    $end_date = $_POST["end_date"];
    $status = $_POST["status"];
    $new_image = false;

    // File upload handling
    $target_dir = "uploads/sculptures/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!empty($_FILES["sculpture_image"]["name"])) {
        $target_file = $target_dir . basename($_FILES["sculpture_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            $_SESSION["error"] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif (move_uploaded_file($_FILES["sculpture_image"]["tmp_name"], $target_file)) {
            $sculpture_image = $target_file;
            $new_image = true;
        } else {
            $_SESSION["error"] = "Failed to upload the image.";
        }
    } else {
        $sculpture_image = $sculpture ? $sculpture['image_url'] : "";
    }

    // Insert or Update based on edit mode
    if ($edit_id) {
        $query = "UPDATE sculptures SET title=?, image_url=?, start_date=?, sculpture_year=?, sculpture_cost=?, end_date=?, status=? WHERE id=? AND seller_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssii", $sculpture_title, $sculpture_image, $start_date, $sculpture_year, $sculpture_cost, $end_date, $status, $edit_id, $seller_id);
        if ($stmt->execute()) {
            $_SESSION["success"] = "Sculpture updated successfully!";
        } else {
            $_SESSION["error"] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $query = "INSERT INTO sculptures (title, image_url, start_date, sculpture_year, sculpture_cost, end_date, status, created_at, seller_id) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssi", $sculpture_title, $sculpture_image, $start_date, $sculpture_year, $sculpture_cost, $end_date, $status, $seller_id);
        if ($stmt->execute()) {
            $_SESSION["success"] = "Sculpture uploaded successfully!";
        } else {
            $_SESSION["error"] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }

    header("Location: sculpture.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $edit_id ? "Edit" : "Upload" ?> Sculpture</title>
    <style>
        body {
            font-family: sans-serif;
            background: linear-gradient(to bottom right, #f0e4d7, #d7ccc8);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('sculpture.jpg'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0;
            position: relative;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
            position: relative;
        }

        h1 {
            text-align: center;
            color: #6d4c41;
            margin-top: 0;
        }

        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #6d4c41;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        button[type="submit"]:hover {
            background-color: #5d4037;
        }

        /* Right-most back button */
        .right-back-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #6d4c41;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            text-decoration: none;
        }

        .right-back-button:hover {
            background-color: #5d4037;
        }

        .right-back-button i {
            font-size: 18px;
        }

        .current-image {
            margin: 10px 0;
        }

        .current-image img {
            max-width: 100px;
            max-height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Right-most back button -->
    <a href="javascript:history.back()" class="right-back-button">
        <i class="fas fa-arrow-left"></i>
        Back
    </a>

    <div class="container">
        <h1><?= $edit_id ? "Edit" : "Upload" ?> Sculpture</h1>

        <?php
        if (isset($_SESSION["success"])) {
            echo '<div class="message success">' . $_SESSION["success"] . '</div>';
            unset($_SESSION["success"]);
        }
        if (isset($_SESSION["error"])) {
            echo '<div class="message error">' . $_SESSION["error"] . '</div>';
            unset($_SESSION["error"]);
        }
        ?>

        <form action="" method="post" enctype="multipart/form-data">
            <label for="sculpture_title">Sculpture Title:</label>
            <input type="text" id="sculpture_title" name="sculpture_title" required value="<?= htmlspecialchars($sculpture['title'] ?? '') ?>">

            <label for="sculpture_image">Sculpture Image:</label>
            <input type="file" id="sculpture_image" name="sculpture_image">
            <?php if ($sculpture && $sculpture['image_url']) { ?>
                <div class="current-image">
                    <p>Current Image:</p>
                    <img src="<?= htmlspecialchars($sculpture['image_url']) ?>" alt="Current sculpture image">
                </div>
            <?php } ?>

            <label for="start_date">Bidding Start Date:</label>
            <input type="date" id="start_date" name="start_date" required value="<?= htmlspecialchars($sculpture['start_date'] ?? '') ?>">

            <label for="sculpture_year">Year of Creation:</label>
            <input type="number" id="sculpture_year" name="sculpture_year" required value="<?= htmlspecialchars($sculpture['sculpture_year'] ?? '') ?>">

            <label for="sculpture_cost">Cost:</label>
            <input type="number" id="sculpture_cost" name="sculpture_cost" required value="<?= htmlspecialchars($sculpture['sculpture_cost'] ?? '') ?>">

            <label for="end_date">Bidding End Date:</label>
            <input type="date" id="end_date" name="end_date" required value="<?= htmlspecialchars($sculpture['end_date'] ?? '') ?>">

            <label for="status">Sculpture Status:</label>
            <select id="status" name="status">
                <option value="Pending" <?= ($sculpture['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Active" <?= ($sculpture['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Sold" <?= ($sculpture['status'] ?? '') === 'Sold' ? 'selected' : '' ?>>Sold</option>
            </select>

            <button type="submit"><?= $edit_id ? "Update" : "Upload" ?> Sculpture</button>
        </form>
    </div>
</body>
</html>