<?php

session_start();
include "includes/db.php";
include_once "includes/notification.php";

if (!isset($_SESSION['user'])) {
    // save the current requested URI so we can return after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

$house_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$houseResult = mysqli_query($conn, "SELECT * FROM houses WHERE id = $house_id");
$house = mysqli_fetch_assoc($houseResult);
$message = "";

if (!$house) {
    $message = "House not found.";
} elseif (isset($_POST['submit'])) {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $email = mysqli_real_escape_string($conn, $_SESSION['user']);
    $messageText = mysqli_real_escape_string($conn, trim($_POST['message']));
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    if (empty($check_in) || empty($check_out) || $check_in > $check_out) {
        $message = "Please provide valid check-in and check-out dates.";
    } else {
        // Check for overlapping bookings for this house (exclude rejected) using prepared statement
        $stmt = mysqli_prepare($conn, "SELECT id FROM bookings WHERE house_id=? AND status!='Rejected' AND NOT (check_out < ? OR check_in > ?) LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'iss', $house_id, $check_in, $check_out);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = "Selected dates are not available. Please choose different dates.";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            $user_email = mysqli_real_escape_string($conn, $_SESSION['user']);
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email=? LIMIT 1");
            mysqli_stmt_bind_param($stmt, 's', $user_email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $found_user_id);
            if (mysqli_stmt_fetch($stmt)) {
                $user_id = (int)$found_user_id;
                mysqli_stmt_close($stmt);

                $ins = mysqli_prepare($conn, "INSERT INTO bookings (user_id, house_id, full_name, phone, email, message, check_in, check_out) VALUES (?,?,?,?,?,?,?,?)");
                mysqli_stmt_bind_param($ins, 'iissssss', $user_id, $house_id, $fullname, $phone, $email, $messageText, $check_in, $check_out);
                if (mysqli_stmt_execute($ins)) {
                    $booking_id = mysqli_insert_id($conn);
                    $emailMessage = "Your house booking request for {$house['title']} from {$check_in} to {$check_out} has been submitted. Booking ID #{$booking_id}.";
                    $emailSubject = 'House Booking Confirmation';
                    $emailResult = sendEmailNotification($email, $emailSubject, $emailMessage);
                    $_SESSION['booking_notification_status'] = $emailResult['success'] ? "Email sent to {$email}." : "Email notification failed: {$emailResult['message']}";
                    mysqli_stmt_close($ins);
                    header("Location: booking_confirmation.php?id=" . $booking_id);
                    exit;
                } else {
                    $message = "Error: " . mysqli_stmt_error($ins);
                    mysqli_stmt_close($ins);
                }
            } else {
                mysqli_stmt_close($stmt);
                $message = "User not found. Please login again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book House</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <h2>Book This House</h2>
        <?php if (!empty($message)) { echo '<div class="success-message">' . htmlspecialchars($message) . '</div>'; } ?>

        <?php if ($house) { ?>
            <p class="house-location">Booking: <?php echo htmlspecialchars($house['title']); ?> in <?php echo htmlspecialchars($house['location']); ?></p>
            <form class="form-container" method="POST">
                <input type="text" name="fullname" placeholder="Full Name" required>
                <input type="text" name="phone" placeholder="Phone Number" required>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']); ?>" readonly>
                <div class="form-grid form-grid-2">
                    <div>
                        <label for="check_in">Arrival</label>
                        <input id="check_in" type="date" name="check_in" placeholder="Arrival date" required title="Select your arrival date">
                    </div>
                    <div>
                        <label for="check_out">Departure</label>
                        <input id="check_out" type="date" name="check_out" placeholder="Departure date" required title="Select your departure date">
                    </div>
                </div>
                <textarea name="message" placeholder="Your Message"></textarea>
                <button type="submit" class="btn btn-full" name="submit">Send Booking</button>
            </form>
        <?php } ?>
    </div>
</div>
</body>
</html>
