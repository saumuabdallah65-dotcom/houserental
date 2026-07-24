<?php
session_start();
include "includes/db.php";

if (isset($_SESSION['user'])) {
    header("Location: user_dashboard.php");
    exit;
}

$message = "";


if(isset($_POST['login'])){


$email = mysqli_real_escape_string($conn, trim($_POST['email']));

$password = $_POST['password'];



// use prepared statement to fetch user
$stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role FROM users WHERE email=? AND role='user' LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = $res ? mysqli_fetch_assoc($res) : null;

if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
    $_SESSION['user'] = $user['email'];
    // redirect back to intended page after login if set
    if (!empty($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header("Location: " . $redirect);
        exit;
    }
    header("Location: user_dashboard.php");
    exit;
} else {
    $message = "Invalid email or password.";
}




}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="css/style.css">
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
        <a href="register.php">Register</a>
    </div>
</nav>
<div class="login-page">
    <div class="login-card">
        <h2>welcome back</h2>
        <p>Sign in below to manage your bookings and find the best rental homes.</p>
        <?php if (!empty($message)) { echo '<div class="error-message">' . htmlspecialchars($message) . '</div>'; } ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button class="btn btn-full" name="login">log in</button>
        </form>
        <p class="auth-link">Don&#8217;t have an account? <a href="register.php">Create one now</a></p>
    </div>
</div>
</body>
</html>