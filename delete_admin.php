<?php
session_start();
include '../database/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Prepare MySQLi DELETE statement
$stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);

    if ($stmt->execute()) {
        // Destroy session to log out the deleted admin
        session_unset();
        session_destroy();

        echo "<script>alert('Your account has been deleted successfully.'); window.location.href = '../auth/admin_login.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to delete your account. Please try again.'); window.location.href = 'admin_dashboard.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Database error. Please contact the developer.'); window.location.href = 'admin_dashboard.php';</script>";
}

$conn->close();
?>
