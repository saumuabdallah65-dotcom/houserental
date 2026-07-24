<?php
session_start();
include "includes/db.php";

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$booking = null;
$notificationMsg = '';
if (!empty($_SESSION['booking_notification_status'])) {
    $notificationMsg = $_SESSION['booking_notification_status'];
    unset($_SESSION['booking_notification_status']);
}

if ($booking_id > 0) {
    $q = "SELECT b.*, h.title AS house_title, h.location AS house_location, h.id AS house_id FROM bookings b LEFT JOIN houses h ON b.house_id = h.id WHERE b.id=$booking_id LIMIT 1";
    $res = mysqli_query($conn, $q);
    if ($res && mysqli_num_rows($res) > 0) {
        $booking = mysqli_fetch_assoc($res);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .confirmation-card { max-width:720px;margin:24px auto;padding:20px;background:#fff;border-radius:6px;box-shadow:0 6px 18px rgba(0,0,0,.06); }
        .confirmation-actions { margin-top:16px; display:flex; gap:10px; }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">House Rental</div>
    <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox" hidden>
    <label for="nav-toggle" class="nav-toggle" aria-label="Toggle navigation menu">☰</label>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="houses.php">Houses</a>
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
<div class="confirmation-card">
    <h2>Booking Submitted</h2>
    <?php if (!empty($notificationMsg)) { ?>
        <div class="success-message"><?php echo htmlspecialchars($notificationMsg); ?></div>
    <?php } ?>
    <?php if (!$booking) { ?>
        <p>Booking not found.</p>
    <?php } else { ?>
        <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['id']); ?></p>
        <p><strong>House:</strong> <?php echo htmlspecialchars($booking['house_title']); ?> — <?php echo htmlspecialchars($booking['house_location']); ?></p>
        <p><strong>Check-in:</strong> <?php echo htmlspecialchars($booking['check_in']); ?> &nbsp; <strong>Check-out:</strong> <?php echo htmlspecialchars($booking['check_out']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($booking['status'])); ?></p>
        <p><strong>Message:</strong> <?php echo nl2br(htmlspecialchars($booking['message'])); ?></p>

        <div class="confirmation-actions">
            <a class="btn" href="user_dashboard.php">View My Bookings</a>
            <?php if (!empty($booking['house_id'])) { ?>
                <a class="btn" href="details.php?id=<?php echo (int)$booking['house_id']; ?>">Back to House</a>
            <?php } ?>
        </div>
    <?php } ?>
</div>
</body>
</html>
