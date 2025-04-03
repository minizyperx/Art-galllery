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

// Fetch painting data with limit
$paintings_sql = "SELECT id, painting_title, painting_image, painting_year, painting_cost FROM paintings LIMIT 3";
$paintings_result = $conn->query($paintings_sql);

$paintings = [];
if ($paintings_result->num_rows > 0) {
    while ($row = $paintings_result->fetch_assoc()) {
        $paintings[] = $row;
    }
}

// Fetch sculpture data with limit
$sculptures_sql = "SELECT id, title, image_url, sculpture_year, sculpture_cost FROM sculptures LIMIT 3";
$sculptures_result = $conn->query($sculptures_sql);

$sculptures = [];
if ($sculptures_result->num_rows > 0) {
    while ($row = $sculptures_result->fetch_assoc()) {
        $sculptures[] = $row;
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
        .art-card {
            transition: transform 0.3s ease;
        }
        .art-card:hover {
            transform: translateY(-5px);
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
                <a href="user/userlogin.php" class="text-white hover:text-gray-300">User Login</a>
                <a href="seller/seller_login.php" class="text-white hover:text-gray-300">Seller Login</a>
                <a href="admin/adminlogin.php" class="text-white hover:text-gray-300">Admin</a>
                <a href="user/contact.php" class="text-white hover:text-gray-300">Contact Us</a>
                <a href="about.html" class="text-white hover:text-gray-300">About Us</a>
            </div>
        </div>
    </nav>

    <!-- Modern Slider with Autoplay -->
    <div class="swiper mySwiper container mx-auto my-4">
        <div class="swiper-wrapper">
            <div class="swiper-slide"><img src="carousel/cool9.jpeg" alt="Artwork 1"></div>
            <div class="swiper-slide"><img src="carousel/cool6.jpg" alt="Artwork 2"></div>
            <div class="swiper-slide"><img src="carousel/cool7.jpg" alt="Artwork 3"></div>
            <div class="swiper-slide"><img src="carousel/cool8.jpg" alt="Artwork 4"></div>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center my-8">Welcome to Art Gallery Dashboard</h1>
        
        <!-- Paintings Section -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <h2 class="text-2xl font-semibold mb-4">Paintings Collection</h2>
            <?php if (count($paintings) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($paintings as $painting): ?>
                        <div class="art-card bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="<?php echo htmlspecialchars($painting['painting_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($painting['painting_title']); ?>" 
                                 class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($painting['painting_title']); ?></h3>
                                <p class="text-sm text-gray-600">Year: <?php echo htmlspecialchars($painting['painting_year']); ?></p>
                                <p class="text-sm text-gray-600">Price: $<?php echo number_format($painting['painting_cost'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">No paintings available at the moment.</p>
            <?php endif; ?>
        </div>
        
        <!-- Sculptures Section -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold mb-4">Sculptures Collection</h2>
            <?php if (count($sculptures) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($sculptures as $sculpture): ?>
                        <div class="art-card bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="uploads/sculptures/<?php echo htmlspecialchars(basename($sculpture['image_url'])); ?>" 
                                 alt="<?php echo htmlspecialchars($sculpture['title']); ?>" 
                                 class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($sculpture['title']); ?></h3>
                                <p class="text-sm text-gray-600">Year: <?php echo htmlspecialchars($sculpture['sculpture_year']); ?></p>
                                <p class="text-sm text-gray-600">Price: $<?php echo number_format($sculpture['sculpture_cost'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600">No sculptures available at the moment.</p>
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
                delay: 2000,
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