<nav class="navbar">
    <div class="nav-brand">House Rental</div>
    <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox">
    <label for="nav-toggle" class="nav-toggle" aria-label="Toggle navigation menu">☰</label>
    <div class="nav-links">
        <a href="index.php" class="active">Home</a>
        <a href="houses.php">Houses</a>
        <a href="#contact">Contact</a>
        <?php if (isset($_SESSION['user'])) { ?>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php } else { ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php } ?>
    </div>
</nav>