<?php
// Database configuration details
define('DB_HOST', 'localhost'); // Your database host
define('DB_USER', 'root'); // Your database username
define('DB_PASS', ''); // Your database password (leave empty if no password)
define('DB_NAME', 'TheShivLibrary'); // Your database name

// PDO connection (optional for using with PDO)
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
