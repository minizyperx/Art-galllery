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
$paintings_sql = "SELECT id, painting_title, painting_image, painting_year, painting_cost FROM paintings LIMIT 6";
$paintings_result = $conn->query($paintings_sql);

$paintings = [];
if ($paintings_result->num_rows > 0) {
    while ($row = $paintings_result->fetch_assoc()) {
        $paintings[] = $row;
    }
}

// Fetch sculpture data with limit
$sculptures_sql = "SELECT id, title, image_url, sculpture_year, sculpture_cost FROM sculptures LIMIT 6";
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
    <title>CUSAT Art Gallery - Discover Artistic Masterpieces</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3a0ca3;
            --secondary: #7209b7;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .swiper {
            width: 100%;
            height: 500px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .swiper-slide {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .swiper-slide::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50%;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        }
        
        .swiper-caption {
            position: absolute;
            bottom: 40px;
            left: 40px;
            color: white;
            z-index: 10;
            max-width: 600px;
        }
        
        .art-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        
        .art-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .art-card-img {
            height: 250px;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .art-card:hover .art-card-img {
            transform: scale(1.05);
        }
        
        .price-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--accent);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .section-title {
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Navbar -->
    <nav class="gradient-bg text-white p-4 sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <a href="index.php" class="text-2xl font-bold flex items-center mb-4 md:mb-0">
                <i class="fas fa-palette mr-2 text-pink-400"></i> CUSAT Art Gallery
            </a>
            <div class="flex flex-wrap justify-center gap-4 md:gap-6">
                <a href="index.php" class="nav-link px-2 py-1 font-medium hover:text-pink-200">Home</a>
                <a href="user/userlogin.php" class="nav-link px-2 py-1 font-medium hover:text-pink-200">User Login</a>
                <a href="seller/seller_login.php" class="nav-link px-2 py-1 font-medium hover:text-pink-200">Seller Login</a>
                <a href="admin/adminlogin.php" class="nav-link px-2 py-1 font-medium hover:text-pink-200">Admin</a>
                <a href="user/contact.php" class="nav-link px-2 py-1 font-medium hover:text-pink-200">Contact Us</a>
                <a href="about.html" class="nav-link px-2 py-1 font-medium hover:text-pink-200">About Us</a>
            </div>
        </div>
    </nav>

    <!-- Hero Slider -->
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <img src="carousel/cool9.jpeg" alt="Artwork 1">
                <div class="swiper-caption">
                    <h2 class="text-4xl font-bold mb-3">Discover Timeless Masterpieces</h2>
                    <p class="text-lg">Explore our curated collection of paintings from emerging and established artists</p>
                </div>
            </div>
            <div class="swiper-slide">
                <img src="carousel/cool6.jpg" alt="Artwork 2">
                <div class="swiper-caption">
                    <h2 class="text-4xl font-bold mb-3">Sculptures That Tell Stories</h2>
                    <p class="text-lg">Three-dimensional art that transforms spaces and sparks conversations</p>
                </div>
            </div>
            <div class="swiper-slide">
                <img src="carousel/cool7.jpg" alt="Artwork 3">
                <div class="swiper-caption">
                    <h2 class="text-4xl font-bold mb-3">Art for Every Space</h2>
                    <p class="text-lg">Find the perfect piece for your home or office from our diverse collection</p>
                </div>
            </div>
            <div class="swiper-slide">
                <img src="carousel/cool8.jpg" alt="Artwork 4">
                <div class="swiper-caption">
                    <h2 class="text-4xl font-bold mb-3">Support Emerging Artists</h2>
                    <p class="text-lg">Discover and collect works from the next generation of talented creators</p>
                </div>
            </div>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next text-white"></div>
        <div class="swiper-button-prev text-white"></div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-12">
        <!-- Featured Section -->
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Welcome to CUSAT Art Gallery</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                A premier destination for art lovers and collectors. Discover unique artworks from talented artists around the world.
            </p>
        </div>
        
        <!-- Paintings Section -->
        <section class="mb-20">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800 section-title">Featured Paintings</h2>
                <a href="paintings.php" class="text-indigo-700 font-medium hover:text-indigo-900 flex items-center">
                
                </a>
            </div>
            
            <?php if (count($paintings) > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($paintings as $painting): ?>
                        <div class="art-card bg-white">
                            <div class="relative overflow-hidden">
                                <img src="<?php echo htmlspecialchars($painting['painting_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($painting['painting_title']); ?>" 
                                     class="art-card-img">
                                <span class="price-tag">Rs: <?php echo number_format($painting['painting_cost'], 2); ?></span>
                            </div>
                            <div class="p-5">
                                <h3 class="text-xl font-semibold mb-1"><?php echo htmlspecialchars($painting['painting_title']); ?></h3>
                                <p class="text-gray-500"><?php echo htmlspecialchars($painting['painting_year']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-lg shadow">
                    <i class="fas fa-paint-brush text-5xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-500">No paintings available at the moment</h3>
                    <p class="text-gray-400">Check back later for new additions</p>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Sculptures Section -->
        <section class="mb-20">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800 section-title">Featured Sculptures</h2>
                <a href="sculptures.php" class="text-indigo-700 font-medium hover:text-indigo-900 flex items-center">
                   
                </a>
            </div>
            
            <?php if (count($sculptures) > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($sculptures as $sculpture): ?>
                        <div class="art-card bg-white">
                            <div class="relative overflow-hidden">
                                <img src="uploads/sculptures/<?php echo htmlspecialchars(basename($sculpture['image_url'])); ?>" 
                                     alt="<?php echo htmlspecialchars($sculpture['title']); ?>" 
                                     class="art-card-img">
                                <span class="price-tag">Rs: <?php echo number_format($sculpture['sculpture_cost'], 2); ?></span>
                            </div>
                            <div class="p-5">
                                <h3 class="text-xl font-semibold mb-1"><?php echo htmlspecialchars($sculpture['title']); ?></h3>
                                <p class="text-gray-500"><?php echo htmlspecialchars($sculpture['sculpture_year']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-lg shadow">
                    <i class="fas fa-monument text-5xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-500">No sculptures available at the moment</h3>
                    <p class="text-gray-400">Check back later for new additions</p>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Call to Action -->
        <section class="gradient-bg text-white rounded-2xl p-12 text-center mb-12">
            <h2 class="text-3xl font-bold mb-4">Ready to Start Your Art Collection?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Join our community of art lovers and discover exceptional artworks from talented artists.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="user/user_register.php" class="bg-white text-indigo-700 hover:bg-gray-100 font-bold py-3 px-8 rounded-full transition">
                    Register Now
                </a>
                <a href="user/userlogin.php" class="border-2 border-white hover:bg-white hover:text-indigo-700 font-bold py-3 px-8 rounded-full transition">
                    Sign In
                </a>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-palette mr-2 text-pink-400"></i> CUSAT Art Gallery
                    </h3>
                    <p class="text-gray-400">Celebrating creativity and connecting artists with art enthusiasts worldwide.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="about.html" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Account</h4>
                    <ul class="space-y-2">
                        <li><a href="user/userlogin.php" class="text-gray-400 hover:text-white transition">User Login</a></li>
                        <li><a href="seller/seller_login.php" class="text-gray-400 hover:text-white transition">Seller Login</a></li>
                        <li><a href="user/user_register.php" class="text-gray-400 hover:text-white transition">Register</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center text-gray-400"><i class="fas fa-map-marker-alt mr-2"></i> CUSAT, Kochi, Kerala</li>
                        <li class="flex items-center text-gray-400"><i class="fas fa-phone-alt mr-2"></i> +91 98765 43210</li>
                        <li class="flex items-center text-gray-400"><i class="fas fa-envelope mr-2"></i> info@cusatartgallery.com</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 mb-4 md:mb-0">© 2025 CUSAT Art Gallery. All rights reserved.</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Initialize Swiper -->
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            autoplay: {
                delay: 5000,
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
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
        });
    </script>
</body>
</html>