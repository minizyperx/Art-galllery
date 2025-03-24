<?php
// Database connection (if needed for other parts of the page)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "art_gallery";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all painting data (if needed for other parts of the page)
$sql = "SELECT id, painting_title, painting_image, painting_year, painting_cost FROM paintings";
$result = $conn->query($sql);

$paintings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paintings[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CUSAT Art Gallery Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        .swiper-slide {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <!-- Navbar -->
    <nav class="bg-blue-900 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-white text-lg font-bold">CUSAT Art Gallery</a>
            <div class="space-x-4">
                <a href="index.php" class="text-white hover:text-gray-300">Home</a>
                <a href="user/userlogin.php" class="text-white hover:text-gray-300">Login as User</a>
                <a href="seller/seller_login.php" class="text-white hover:text-gray-300">Login as Seller</a>
                <a href="admin/adminlogin.php" class="text-white hover:text-gray-300">Login as Admin</a>
                <a href="user/contact.php" class="text-white hover:text-gray-300">Contact Us</a>
                <a href="about.html" class="text-white hover:text-gray-300">About Us</a>
            </div>
        </div>
    </nav>

    <!-- Modern Slider with Autoplay -->
    <div class="swiper mySwiper container mx-auto my-4">
        <div class="swiper-wrapper">
            <div class="swiper-slide"><img src="carousel/cool9.jpeg" alt="Painting 1"></div>
            <div class="swiper-slide"><img src="carousel/cool6.jpg" alt="Painting 2"></div>
            <div class="swiper-slide"><img src="carousel/cool7.jpg" alt="Painting 3"></div>
            <div class="swiper-slide"><img src="carousel/cool8.jpg" alt="Painting 4"></div>
            <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        </div>
       
    </div>

    <!-- Main Content -->
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center my-8">Welcome to Art Gallery Dashboard</h1>
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold mb-4">Paintings and Sculptures</h2>
            <?php if (count($paintings) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($paintings as $painting): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="<?php echo $painting['painting_image']; ?>" alt="<?php echo $painting['painting_title']; ?>" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <p class="text-lg font-semibold"><?php echo $painting['painting_title']; ?></p>
                                <p class="text-sm text-gray-600">Year: <?php echo $painting['painting_year']; ?></p>
                                <p class="text-sm text-gray-600">Price: $<?php echo number_format($painting['painting_cost'], 2); ?></p>
                              
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">No paintings or sculptures available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Initialize Swiper with Autoplay -->
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 2000, // Change slide every 2 seconds
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
    </script>
</body>
</html>