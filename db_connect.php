<?php
$host = 'localhost';
$dbname = 'TheShivLibrary';
$username = 'root'; // Or whatever your MySQL user is
$password = '';     // Leave empty if using default in XAMPP

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
