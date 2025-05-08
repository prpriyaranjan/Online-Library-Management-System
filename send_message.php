<?php
session_start();
require_once('../database/db_connect.php');

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    exit("Unauthorized.");
}

$admin_id = $_SESSION['admin_id'];

// Sanitize and validate input
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($receiver_id <= 0 || empty($message)) {
    exit("Invalid input.");
}

// Insert message into database
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sender_role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
$stmt->bind_param("iis", $admin_id, $receiver_id, $message);
$stmt->execute();

// Optional: you can add error checks like $stmt->error

// Redirect back to chat view
header("Location: admin_chat.php?user_id=" . $receiver_id);
exit();
?>
