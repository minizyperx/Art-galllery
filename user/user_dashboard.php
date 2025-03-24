<?php
// Include the database connection
include '../db/connect.php';

// Initialize search term
$searchTerm = '';

// Check if a search term is submitted
if (isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']); // Get the search term
}

// Fetch paintings data with search filter
$sql = "SELECT * FROM paintings WHERE painting_title LIKE ? OR painting_year LIKE ? OR status LIKE ?";
$stmt = $conn->prepare($sql);

// Fetch sculptures data with search filter
$sql2 = "SELECT * FROM sculptures WHERE title LIKE ? OR sculpture_year LIKE ? OR status LIKE ?";
$stmt2 = $conn->prepare($sql2);

// Bind the search term to the queries
$searchTermLike = "%$searchTerm%";
$stmt->bind_param('sss', $searchTermLike, $searchTermLike, $searchTermLike);
$stmt->execute();
$result = $stmt->get_result();

$stmt2->bind_param('sss', $searchTermLike, $searchTermLike, $searchTermLike);
$stmt2->execute();
$result2 = $stmt2->get_result();

// Fetch results
$paintings = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];
$sculptures = $result2->num_rows > 0 ? $result2->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Gallery Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-gray-800 text-white p-4 flex justify-between items-center">
        <div class="logo">
            <img src="logo.png" alt="Art Gallery Logo" class="h-12">
        </div>
        <div class="welcome-message">
            <h1 class="text-xl font-bold">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</h1>
        </div>
        <nav>
            <ul class="flex space-x-4">
                <li><a href="#" class="hover:text-gray-400">Home</a></li>
                <li><a href="#" class="hover:text-gray-400">Explore</a></li>
                <li><a href="#" class="hover:text-gray-400">Profile</a></li>
                <li><a href="logout.php" class="hover:text-gray-400">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="p-6">
        <!-- Search Bar -->
        <section class="mb-8">
            <form method="GET" action="" class="flex justify-center">
                <input
                    type="text"
                    name="search"
                    placeholder="Search paintings or sculptures by title, year, or status..."
                    value="<?php echo htmlspecialchars($searchTerm); ?>"
                    class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <button
                    type="submit"
                    class="ml-2 px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Search
                </button>
            </form>
        </section>

        <!-- Buttons -->
        <section class="text-center mb-8">
            <a href='paintingstore.php' id="paintingsBtn" class="bg-blue-500 text-white px-6 py-2 rounded-lg mr-4 hover:bg-blue-600">Paintings</a>
            <a href='sculpturestore.php' id="sculpturesBtn" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">Sculptures</a>
        </section>

        <!-- Featured Paintings -->
        <section class="mb-8">
            <h2 class="text-2xl font-bold text-center mb-6">Featured Paintings</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php if (empty($paintings)): ?>
                    <p class="text-center text-gray-600">No paintings found.</p>
                <?php else: ?>
                    <?php foreach ($paintings as $painting): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="<?php echo htmlspecialchars($painting['painting_image']); ?>" alt="<?php echo htmlspecialchars($painting['painting_title']); ?>" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($painting['painting_title']); ?></h3>
                                <p class="text-gray-600">Year: <?php echo htmlspecialchars($painting['painting_year']); ?></p>
                                <p class="text-gray-600">Cost: $<?php echo htmlspecialchars($painting['painting_cost']); ?></p>
                                <p class="text-gray-600">Status: <span class="font-semibold"><?php echo htmlspecialchars($painting['status']); ?></span></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Featured Sculptures -->
        <section class="mb-8">
            <h2 class="text-2xl font-bold text-center mb-6">Featured Sculptures</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php if (empty($sculptures)): ?>
                    <p class="text-center text-gray-600">No sculptures found.</p>
                <?php else: ?>
                    <?php foreach ($sculptures as $sculpture): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="../<?php echo htmlspecialchars($sculpture['image_url']); ?>" alt="<?php echo htmlspecialchars($sculpture['title']); ?>" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($sculpture['title']); ?></h3>
                                <p class="text-gray-600">Year: <?php echo htmlspecialchars($sculpture['sculpture_year']); ?></p>
                                <p class="text-gray-600">Cost: $<?php echo htmlspecialchars($sculpture['sculpture_cost']); ?></p>
                                <p class="text-gray-600">Status: <span class="font-semibold"><?php echo htmlspecialchars($sculpture['status']); ?></span></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white p-6 text-center">
        <div class="contact-info mb-4">
            <p>Email: info@artgallery.com</p>
            <p>Phone: +123 456 7890</p>
        </div>
        <div class="quick-links">
            <a href="#" class="mx-2 hover:text-gray-400">About Us</a>
            <a href="#" class="mx-2 hover:text-gray-400">FAQs</a>
            <a href="#" class="mx-2 hover:text-gray-400">Privacy Policy</a>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="script.js"></script>
</body>
</html>