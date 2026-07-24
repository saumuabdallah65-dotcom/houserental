<?php

session_start();

include "../includes/db.php";

$message = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email' AND role='admin' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        if (password_verify($password, $admin['password']) || $password === $admin['password']) {
            $_SESSION['admin'] = $email;
            header("Location: dashboard.php");
            exit;
        }
    }

    $message = "Invalid Email or Password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <h2>Admin Login</h2>
        <p>Enter your admin credentials to access the dashboard.</p>
        <?php if (!empty($message)) {
            echo '<div class="error-message">' . htmlspecialchars($message) . '</div>';
        } ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn btn-full" name="login">Login</button>
        </form>
        <p class="auth-link"><a href="../login.php">Back to User Login</a></p>
    </div>
</div>
</body>
</html>
