<?php
session_start();
include "../includes/db.php";

$query = "SELECT bookings.*, houses.title AS house_title, houses.location AS house_location FROM bookings LEFT JOIN houses ON bookings.house_id = houses.id ORDER BY bookings.booking_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Bookings</title>
    <link rel="stylesheet" href="../css/style.css">
</head>


<body>


<h1 style="text-align:center;">Customer Bookings</h1>

<?php if (!empty($_SESSION['admin_msg'])) { echo '<div class="error-message" style="max-width:900px;margin:10px auto;">' . htmlspecialchars($_SESSION['admin_msg']) . '</div>'; unset($_SESSION['admin_msg']); } ?>

<table border="1" cellpadding="10" align="center">
<tr>
<th>Name</th>
<th>Phone</th>
<th>Email</th>
<th>House</th>
<th>Location</th>
<th>Check-in</th>
<th>Check-out</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while ($booking = mysqli_fetch_assoc($result)) { ?>
<tr>
	<td><?php echo htmlspecialchars($booking['full_name']); ?></td>
	<td><?php echo htmlspecialchars($booking['phone']); ?></td>
	<td><?php echo htmlspecialchars($booking['email']); ?></td>
	<td><?php echo htmlspecialchars($booking['house_title'] ?: '—'); ?></td>
	<td><?php echo htmlspecialchars($booking['house_location'] ?: '—'); ?></td>
	<td><?php echo htmlspecialchars($booking['check_in'] ?: '-'); ?></td>
	<td><?php echo htmlspecialchars($booking['check_out'] ?: '-'); ?></td>
	<td><?php echo htmlspecialchars($booking['status']); ?></td>
	<td>
		<a href="update_status.php?id=<?php echo $booking['id']; ?>&status=Approved"><button>Approve</button></a>
		<a href="update_status.php?id=<?php echo $booking['id']; ?>&status=Rejected"><button>Reject</button></a>
	</td>
</tr>
<?php } ?>

</table>

</body>

</html>