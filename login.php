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
<div class="login-page">
    <div class="login-card">
        <h2>Login</h2>
        <p>Welcome back! Sign in to continue.</p>
        <?php if (!empty($message)) { echo '<div class="error-message">' . htmlspecialchars($message) . '</div>'; } ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button class="btn btn-full" name="login">Login</button>
        </form>
        <p class="auth-link"><a href="register.php">Create an account</a></p>
    </div>
</div>
</body>
</html>