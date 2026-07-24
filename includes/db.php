<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration — replace the placeholders below with your InfinityFree/MySQL credentials
$host = 'sql304.infinityfree.com';       // e.g. sql123.epizy.com
$user = 'if0_42486320';    // e.g. epiz_12345678
$password = 'Wadox123';
$database = 'if0_42486320_house_rental';    // e.g. epiz_12345678_db

// Connect to the existing database.
// InfinityFree does not allow creating databases from PHP scripts.
$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die('<pre>Database connection failed: ' . mysqli_connect_error() . '\nHost: ' . htmlspecialchars($host) . '\nUser: ' . htmlspecialchars($user) . '\nDB: ' . htmlspecialchars($database) . '</pre>');
}

if (!$conn) {
    // If the database doesn't exist or connection failed, try connecting without selecting DB
    $connNoDb = @mysqli_connect($host, $user, $password);
    if (!$connNoDb) {
        die('Database connection failed: ' . mysqli_connect_error());
    }

    // Attempt to create the database if permitted (some hosts disallow this)
    if (!mysqli_query($connNoDb, "CREATE DATABASE IF NOT EXISTS `$database`")) {
        die('Database not found and could not be created. Configure DB credentials.');
    }

    mysqli_close($connNoDb);
    $conn = @mysqli_connect($host, $user, $password, $database);
    if (!$conn) {
        die('Failed to connect to the database after creation: ' . mysqli_connect_error());
    }
}

// Ensure uploads directory exists and is writable
$uploadsDir = __DIR__ . '/../uploads';
if (!is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0755, true);
}

// Helper to run queries and log (do not die on hosts without privileges)
function safe_query($conn, $sql) {
    $res = @mysqli_query($conn, $sql);
    if ($res === false) {
        error_log('DB query failed: ' . mysqli_error($conn) . ' -- SQL: ' . $sql);
    }
    return $res;
}

safe_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

safe_query($conn, "CREATE TABLE IF NOT EXISTS houses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    bedrooms INT NOT NULL,
    bathrooms INT NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

safe_query($conn, "CREATE TABLE IF NOT EXISTS house_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    house_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    caption VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE
)");

safe_query($conn, "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    house_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT,
    check_in DATE DEFAULT NULL,
    check_out DATE DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Ensure columns exist (safe)
if ($res = safe_query($conn, "SHOW COLUMNS FROM bookings LIKE 'check_in'")) {
    if (mysqli_num_rows($res) === 0) {
        safe_query($conn, "ALTER TABLE bookings ADD COLUMN check_in DATE DEFAULT NULL");
    }
}

if ($res = safe_query($conn, "SHOW COLUMNS FROM bookings LIKE 'check_out'")) {
    if (mysqli_num_rows($res) === 0) {
        safe_query($conn, "ALTER TABLE bookings ADD COLUMN check_out DATE DEFAULT NULL");
    }
}

// Insert a sample house if none exists
if ($res = safe_query($conn, "SELECT id FROM houses LIMIT 1")) {
    if (mysqli_num_rows($res) === 0) {
        safe_query($conn, "INSERT INTO houses (title, location, price, bedrooms, bathrooms, description, image) VALUES
            ('Modern Family House', 'Dar es Salaam', 450000, 3, 2, 'Bright and spacious house with a garden and parking.', 'house1.jpg')");
    }
}

// Create an admin account only if none exists (default password 'admin123')
if ($res = safe_query($conn, "SELECT id FROM users WHERE role='admin' LIMIT 1")) {
    if (mysqli_num_rows($res) === 0) {
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
        safe_query($conn, "INSERT INTO users (name, email, phone, password, role) VALUES ('Admin', 'admin@house.com', '0755123456', '$adminHash', 'admin')");
    }
}

?>