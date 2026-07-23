<?php

session_start();
include "../includes/db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

if ($id <= 0 || empty($status)) {
	$_SESSION['admin_msg'] = 'Invalid request.';
	header('Location: booking.php');
	exit;
}

if ($status === 'Approved') {
	// ensure dates do not overlap an existing approved booking for the same house
	$bStmt = mysqli_prepare($conn, "SELECT house_id, check_in, check_out FROM bookings WHERE id=? LIMIT 1");
	mysqli_stmt_bind_param($bStmt, 'i', $id);
	mysqli_stmt_execute($bStmt);
	mysqli_stmt_bind_result($bStmt, $house_id, $check_in, $check_out);
	if (mysqli_stmt_fetch($bStmt)) {
		mysqli_stmt_close($bStmt);
		$ovStmt = mysqli_prepare($conn, "SELECT id FROM bookings WHERE house_id=? AND status='Approved' AND NOT (check_out < ? OR check_in > ?) AND id != ? LIMIT 1");
		mysqli_stmt_bind_param($ovStmt, 'issi', $house_id, $check_in, $check_out, $id);
		mysqli_stmt_execute($ovStmt);
		mysqli_stmt_store_result($ovStmt);
		if (mysqli_stmt_num_rows($ovStmt) > 0) {
			mysqli_stmt_close($ovStmt);
			$_SESSION['admin_msg'] = 'Cannot approve booking: dates overlap with an existing approved booking.';
			header('Location: booking.php');
			exit;
		}
		mysqli_stmt_close($ovStmt);
	} else {
		mysqli_stmt_close($bStmt);
	}
}

$upStmt = mysqli_prepare($conn, "UPDATE bookings SET status=? WHERE id=?");
mysqli_stmt_bind_param($upStmt, 'si', $status, $id);
mysqli_stmt_execute($upStmt);
mysqli_stmt_close($upStmt);

header('Location: booking.php');
exit;

?>