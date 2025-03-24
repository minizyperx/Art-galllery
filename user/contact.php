<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ccc";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get logged-in user details
$user_name = $_SESSION['username'];
$user_email = $_SESSION['email'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    
    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$user_name', '$user_email', '$subject', '$message')";
    
    if ($conn->query($sql) === TRUE) {
        $success_message = "Message sent successfully!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - CUSAT Art Gallery</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .navbar {
            background-color: #0d0767;
            padding: 10px 0;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            display: inline-block;
        }
        .navbar a:hover {
            background-color: #575fcf;
            border-radius: 5px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #0d0767;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #575fcf;
        }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="../index.php">Home</a>
        <a href="../about.html">About</a>
        
        
    </div>
    
    <div class="container">
        <h1>Contact Us</h1>
        <?php if (isset($success_message)) echo "<p class='success'>$success_message</p>"; ?>
        <?php if (isset($error_message)) echo "<p class='error'>$error_message</p>"; ?>
        
        <form method="POST" action="">
            <input type="text" name="name" value="<?php echo htmlspecialchars($user_name); ?>" readonly>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
            <input type="text" name="subject" placeholder="Subject" required>
            <textarea name="message" placeholder="Your message" required></textarea>
            <button type="submit">Send Message</button>
        </form>
    </div>
</body>
</html>
