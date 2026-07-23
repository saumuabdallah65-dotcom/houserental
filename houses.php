<?php
session_start();
include "includes/db.php";

$locationFilter = isset($_GET['location']) ? trim($_GET['location']) : '';
$maxPrice = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';

$query = "SELECT * FROM houses WHERE 1=1";

if ($locationFilter !== '') {
    $locationFilterSafe = mysqli_real_escape_string($conn, $locationFilter);
    $query .= " AND location LIKE '%$locationFilterSafe%'";
}

if ($maxPrice !== '') {
    $maxPriceSafe = (float)$maxPrice;
    $query .= " AND price <= $maxPriceSafe";
}

$query .= " ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Houses - House Rental</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">HouseRental</div>
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

<section class="page-header">
    <h1>All Houses</h1>
    <p>Browse available homes for rent</p>
</section>

<section class="featured">
    <form class="search-panel" method="GET" action="houses.php">
        <input type="text" name="location" placeholder="Search by location" value="<?php echo htmlspecialchars($locationFilter); ?>">
        <input type="number" name="max_price" placeholder="Max price" min="0" step="1000" value="<?php echo htmlspecialchars($maxPrice); ?>">
        <button type="submit" class="btn">Search</button>
        <a href="houses.php" class="btn btn-secondary">Reset</a>
    </form>

    <?php if (mysqli_num_rows($result) > 0) { ?>
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
    <?php } else { ?>
        <div class="empty-state">No houses match your search right now.</div>
    <?php } ?>
</section>

<footer class="site-footer">
    &copy; <?php echo date("Y"); ?> HouseRental. All Rights Reserved.
</footer>
</body>
</html>