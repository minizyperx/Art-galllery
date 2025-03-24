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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $painting_title = trim($_POST["painting_title"]);
    $start_date = $_POST["start_date"];
    $painting_year = $_POST["painting_year"];
    $painting_cost = $_POST["painting_cost"];
    $end_date = $_POST["end_date"];
    $status = $_POST["status"];

    $target_dir = "uploads/"; 
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["painting_image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION["error"] = "Only JPG, JPEG, PNG & GIF files are allowed.";
    } elseif (move_uploaded_file($_FILES["painting_image"]["tmp_name"], $target_file)) {
        $painting_image = $target_file;

        $query = "INSERT INTO paintings (painting_title, painting_image, start_date, painting_year, painting_cost, end_date, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", $painting_title, $painting_image, $start_date, $painting_year, $painting_cost, $end_date, $status);

        if ($stmt->execute()) {
            $_SESSION["success"] = "Painting uploaded successfully!";
        } else {
            $_SESSION["error"] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION["error"] = "Failed to upload the image.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Painting</title>
    <style>
        body {
            font-family: sans-serif;
            background: linear-gradient(to bottom right, #e0f2f7, #bbdefb);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('painter.jpeg'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        h1 {
            text-align: center;
            color: #2196f3;
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
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #2196f3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Painting</h1>

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
            <label for="painting_title">Painting Title:</label>
            <input type="text" id="painting_title" name="painting_title" required>

            <label for="painting_image">Painting Image:</label>
            <input type="file" id="painting_image" name="painting_image" required>

            <label for="start_date">Bidding Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label for="painting_year">Year of Painting:</label>
            <input type="number" id="painting_year" name="painting_year" min="1800" max="2100" required>

            <label for="painting_cost">Cost of Painting:</label>
            <input type="number" id="painting_cost" name="painting_cost" required>

            <label for="end_date">Bidding End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <label for="status">Painting Status:</label>
            <select id="status" name="status">
                <option value="Pending">Pending</option>
                <option value="Active">Active</option>
                <option value="Sold">Sold</option>
            </select>

            <button type="submit">Upload and Start Bidding</button>
        </form>
    </div>
</body>
</html>
