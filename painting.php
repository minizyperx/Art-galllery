<?php
session_start();

// Database configuration
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$dbname = "art_gallery"; 

// Create database connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error and success messages
$_SESSION['error'] = '';
$_SESSION['success'] = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $painting_title = trim(filter_input(INPUT_POST, "painting_title", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $start_date = filter_input(INPUT_POST, "start_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $painting_year = filter_input(INPUT_POST, "painting_year", FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 1800,
            'max_range' => 2100
        ]
    ]);
    $painting_cost = filter_input(INPUT_POST, "painting_cost", FILTER_VALIDATE_FLOAT);
    $end_date = filter_input(INPUT_POST, "end_date", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, "status", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $seller_id = $_SESSION['seller_id'] ?? null;

    // Validate required fields
    if (empty($painting_title)) {
        $_SESSION["error"] = "Painting title is required.";
    } elseif (!$painting_year) {
        $_SESSION["error"] = "Please enter a valid year between 1800 and 2100.";
    } elseif (!$painting_cost || $painting_cost <= 0) {
        $_SESSION["error"] = "Please enter a valid cost greater than 0.";
    } elseif (empty($start_date)) {
        $_SESSION["error"] = "Start date is required.";
    } elseif (empty($end_date)) {
        $_SESSION["error"] = "End date is required.";
    } elseif (strtotime($end_date) <= strtotime($start_date)) {
        $_SESSION["error"] = "End date must be after start date.";
    } elseif (empty($seller_id)) {
        $_SESSION["error"] = "Seller ID not found. Please login again.";
    } elseif (!isset($_FILES["painting_image"]) || $_FILES["painting_image"]["error"] != UPLOAD_ERR_OK) {
        $_SESSION["error"] = "Please upload a valid image file.";
    } else {
        // Process image upload
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($_FILES["painting_image"]["name"], PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $unique_filename;

        // Validate image file
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION["error"] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif ($_FILES["painting_image"]["size"] > 5000000) { // 5MB limit
            $_SESSION["error"] = "File is too large. Maximum size is 5MB.";
        } elseif (move_uploaded_file($_FILES["painting_image"]["tmp_name"], $target_file)) {
            // Prepare SQL statement
            $query = "INSERT INTO paintings (painting_title, painting_image, start_date, painting_year, painting_cost, end_date, status, created_at, seller_id) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("sssssssi", $painting_title, $target_file, $start_date, $painting_year, $painting_cost, $end_date, $status, $seller_id);
                
                if ($stmt->execute()) {
                    $_SESSION["success"] = "Painting uploaded successfully!";
                    // Clear form values on success
                    $painting_title = $start_date = $painting_year = $painting_cost = $end_date = '';
                } else {
                    $_SESSION["error"] = "Database error: " . $stmt->error;
                    // Delete the uploaded file if database insert failed
                    if (file_exists($target_file)) {
                        unlink($target_file);
                    }
                }
                $stmt->close();
            } else {
                $_SESSION["error"] = "Database preparation error: " . $conn->error;
            }
        } else {
            $_SESSION["error"] = "Failed to upload the image.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Painting</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom right, #e0f2f7, #bbdefb);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            position: relative;
            background-image: url('painter.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .right-back-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #2196f3;
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
            transition: background-color 0.3s ease;
        }

        .right-back-button:hover {
            background-color: #1976d2;
            transform: translateY(-1px);
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            position: relative;
            backdrop-filter: blur(5px);
            margin: 20px;
        }

        h1 {
            text-align: center;
            color: #2196f3;
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 28px;
        }

        .message {
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #2196f3;
            outline: none;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.2);
        }

        input[type="file"] {
            padding: 8px;
            background-color: #f8f9fa;
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: #2196f3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background-color: #1976d2;
            transform: translateY(-2px);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        input[type="date"] {
            appearance: none;
            -webkit-appearance: none;
            padding: 12px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            input, select {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.history.back()" class="right-back-button">
        <i class="fas fa-arrow-left"></i>
        Back
    </button>

    <div class="container">
        <h1>Upload Painting</h1>

        <?php
        if (isset($_SESSION["success"]) && !empty($_SESSION["success"])) {
            echo '<div class="message success">' . htmlspecialchars($_SESSION["success"]) . '</div>';
            unset($_SESSION["success"]);
        }
        if (isset($_SESSION["error"]) && !empty($_SESSION["error"])) {
            echo '<div class="message error">' . htmlspecialchars($_SESSION["error"]) . '</div>';
            unset($_SESSION["error"]);
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <label for="painting_title">Painting Title:</label>
            <input type="text" id="painting_title" name="painting_title" value="<?php echo isset($painting_title) ? htmlspecialchars($painting_title) : ''; ?>" required>

            <label for="painting_image">Painting Image:</label>
            <input type="file" id="painting_image" name="painting_image" accept="image/*" required>

            <label for="start_date">Bidding Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo isset($start_date) ? htmlspecialchars($start_date) : ''; ?>" required>

            <label for="painting_year">Year of Painting:</label>
            <input type="number" id="painting_year" name="painting_year" min="1800" max="2100" value="<?php echo isset($painting_year) ? htmlspecialchars($painting_year) : ''; ?>" required>

            <label for="painting_cost">Cost of Painting ($):</label>
            <input type="number" id="painting_cost" name="painting_cost" step="0.01" min="0.01" value="<?php echo isset($painting_cost) ? htmlspecialchars($painting_cost) : ''; ?>" required>

            <label for="end_date">Bidding End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo isset($end_date) ? htmlspecialchars($end_date) : ''; ?>" required>

            <label for="status">Painting Status:</label>
            <select id="status" name="status" required>
                <option value="Pending" <?php echo (isset($status) && $status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Active" <?php echo (isset($status) && $status == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Sold" <?php echo (isset($status) && $status == 'Sold') ? 'selected' : ''; ?>>Sold</option>
            </select>

            <button type="submit">Upload and Start Bidding</button>
        </form>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (endDate <= startDate) {
                alert('End date must be after start date');
                e.preventDefault();
            }
        });
        
        document.getElementById('start_date').min = new Date().toISOString().split('T')[0];
        
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>
</body>
</html>