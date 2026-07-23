<?php
session_start();
include "includes/db.php";

$query = "SELECT * FROM houses ORDER BY created_at DESC LIMIT 3";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House Rental</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">HouseRental</div>
    <div class="nav-links">
        <a href="index.php" class="active">Home</a>
        <a href="houses.php">Houses</a>
        <a href="#contact">Contact</a>
        <?php if (isset($_SESSION['user'])) { ?>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php } else { ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php } ?>
    </div>
</nav>

<section class="hero">
    <div class="hero-content">
        <h1>Find Your Dream House</h1>
        <p>Book a comfortable and affordable home in minutes.</p>
        <a href="houses.php" class="btn btn-hero">Browse Houses</a>
    </div>
</section>

<section class="featured">
    <h2 class="section-title center">Featured Houses</h2>
    <div class="house-grid">
        <?php while ($house = mysqli_fetch_assoc($result)) { 
            $imagePath = !empty($house['image']) && file_exists('uploads/' . $house['image']) ? 'uploads/' . $house['image'] : 'image/house1.jpg';
        ?>
            <div class="house-card">
                <div class="house-image">
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($house['title']); ?>">
                </div>
                <div class="house-info">
                    <h3><?php echo htmlspecialchars($house['title']); ?></h3>
                    <p class="house-location">Location: <?php echo htmlspecialchars($house['location']); ?></p>
                    <p class="house-meta">
                        <?php echo (int)$house['bedrooms']; ?> Beds &middot; <?php echo (int)$house['bathrooms']; ?> Baths
                    </p>
                    <p class="house-price">Tsh <?php echo number_format($house['price']); ?>/month</p>
                    <a href="details.php?id=<?php echo (int)$house['id']; ?>" class="btn">View Details</a>
                </div>
            </div>
        <?php } ?>
    </div>
</section>

<section class="contact-section" id="contact">
    <div class="contact-card">
        <h2>Contact Us</h2>
        <p>Need help finding the right home? Reach out to our team today.</p>
        <div class="contact-details">
            <p><strong>Phone:</strong> 0759187775</p>
            <p><strong>Email:</strong> info@houserental.com</p>
            <p><strong>Address:</strong> Dar es Salaam, Tanzania</p>
        </div>
        <a href="mailto:info@houserental.com" class="btn btn-hero">Send Email</a>
    </div>
</section>

<footer class="site-footer">
    &copy; <?php echo date("Y"); ?> HouseRental. All Rights Reserved.
</footer>
</body>
</html>