<?php
session_start();
include "includes/db.php";

$houseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$houseQuery = mysqli_query($conn, "SELECT * FROM houses WHERE id = $houseId");
$house = mysqli_fetch_assoc($houseQuery);

$images = [];
if ($house) {
    $imageResult = mysqli_query($conn, "SELECT * FROM house_images WHERE house_id = $houseId ORDER BY id ASC");
    while ($row = mysqli_fetch_assoc($imageResult)) {
        $images[] = $row;
    }
}

$defaultImage = 'image/house1.jpg';
$mainImagePath = $defaultImage;
if ($house) {
    if (!empty($house['image']) && file_exists('uploads/' . $house['image'])) {
        $mainImagePath = 'uploads/' . $house['image'];
    } elseif (!empty($images[0]['image']) && file_exists('uploads/' . $images[0]['image'])) {
        $mainImagePath = 'uploads/' . $images[0]['image'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House Details - House Rental</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">House Rental</div>
    <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox" hidden>
    <label for="nav-toggle" class="nav-toggle" aria-label="Toggle navigation menu">☰</label>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="houses.php" class="active">Houses</a>
        <a href="index.php#contact">Contact</a>
        <?php if (isset($_SESSION['user'])) { ?>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php } else { ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php } ?>
    </div>
</nav>

<section class="details-section">
    <a href="houses.php" class="back-link">&larr; Back to houses</a>

    <?php if ($house) { ?>
        <div class="details-card">
            <div class="details-image">
                <img src="<?php echo htmlspecialchars($mainImagePath); ?>" alt="<?php echo htmlspecialchars($house['title']); ?>">
            </div>
            <div class="details-info">
                <h1><?php echo htmlspecialchars($house['title']); ?></h1>
                <p class="house-location">Location: <?php echo htmlspecialchars($house['location']); ?></p>
                <p class="house-meta">
                    <?php echo (int)$house['bedrooms']; ?> Beds &middot; <?php echo (int)$house['bathrooms']; ?> Baths
                </p>
                <p class="house-price large">Tsh <?php echo number_format($house['price']); ?>/month</p>
                <p><?php echo nl2br(htmlspecialchars($house['description'])); ?></p>
                <div class="details-actions">
                    <a href="booking.php?id=<?php echo (int)$house['id']; ?>" class="btn">Book This House</a>
                    <a href="calendar.php?house_id=<?php echo (int)$house['id']; ?>" class="btn btn-secondary">View Availability</a>
                </div>
            </div>
        </div>

        <?php if (!empty($images)) { ?>
            <div class="details-gallery">
                <?php foreach ($images as $img) { ?>
                    <?php $imgPath = 'uploads/' . htmlspecialchars($img['image']); ?>
                    <div class="gallery-item">
                        <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($house['title']); ?>">
                        <?php if (!empty($img['caption'])) { ?>
                            <p class="gallery-caption"><?php echo htmlspecialchars($img['caption']); ?></p>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="empty-state">House not found.</div>
    <?php } ?>
</section>

<footer class="site-footer">
    &copy; <?php echo date("Y"); ?> HouseRental. All Rights Reserved.
</footer>
</body>
</html>