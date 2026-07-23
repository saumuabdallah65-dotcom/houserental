<?php

session_start();

include "includes/db.php";


if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_email = mysqli_real_escape_string($conn, $_SESSION['user']);
$userQuery = mysqli_query($conn, "SELECT id FROM users WHERE email='$user_email' LIMIT 1");
$user_id = 0;
if ($userQuery && mysqli_num_rows($userQuery) > 0) {
    $user = mysqli_fetch_assoc($userQuery);
    $user_id = (int) $user['id'];
}

$query = "SELECT bookings.*, houses.title AS house_title, houses.location AS house_location FROM bookings LEFT JOIN houses ON bookings.house_id = houses.id WHERE bookings.user_id=$user_id ORDER BY bookings.booking_date DESC";
$result = mysqli_query($conn, $query);


?>


<!DOCTYPE html>
<html>

<head>

<title>User Dashboard</title>

<link rel="stylesheet" href="css/style.css">

</head>


<body>


<nav class="navbar">

<div class="nav-brand">House Rental</div>
<div class="nav-links"><a href="logout.php">Logout</a></div>

</nav>



<h2 style="text-align:center;">
My Bookings
</h2>

<table border="1" cellpadding="10" align="center">
<tr>
<th>House</th>
<th>Location</th>
<th>Check-in</th>
<th>Check-out</th>
<th>Status</th>
<th>Message</th>
<th>Booking Date</th>
</tr>



<?php

while ($booking = mysqli_fetch_assoc($result)) {
?>
<tr>
<td><?php echo htmlspecialchars($booking['house_title'] ?: 'House removed'); ?></td>
<td><?php echo htmlspecialchars($booking['house_location'] ?: '-'); ?></td>
<td><?php echo htmlspecialchars($booking['check_in'] ?: '-'); ?></td>
<td><?php echo htmlspecialchars($booking['check_out'] ?: '-'); ?></td>
<td><?php echo htmlspecialchars($booking['status']); ?></td>
<td><?php echo nl2br(htmlspecialchars($booking['message'])); ?></td>
<td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
</tr>


<?php } ?>


</table>



</body>

</html>