<?php
session_start();
include "includes/db.php";

$message = "";

if (isset($_POST['register'])) {


$name = mysqli_real_escape_string($conn, trim($_POST['name']));
$email = mysqli_real_escape_string($conn, trim($_POST['email']));
$phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
$password = $_POST['password'];

$check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1");
if (mysqli_num_rows($check) > 0) {
    $message = "Email already exists. Please choose another.";
} else {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, phone, password, role) VALUES ('$name', '$email', '$phone', '$hashedPassword', 'user')";


if(mysqli_query($conn,$sql)){

$message = "Registration successful. Please login.";

}else{

$message = "Registration failed.";

}


}
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <h2>Create Account</h2>
        <?php if (!empty($message)) { echo '<div class="' . (strpos($message, 'successful') !== false ? 'success-message' : 'error-message') . '">' . htmlspecialchars($message) . '</div>'; } ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <button class="btn btn-full" name="register">Register</button>
        </form>
        <p class="auth-link"><a href="login.php">Already have an account? Login</a></p>
    </div>
</div>
</body>
</html>