<?php
session_start();
require_once('database/db_connect.php');

header('Content-Type: text/plain'); // Good practice for AJAX responses

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']); // Admin ID from hidden input
$message = trim($_POST['message']);
$sender_role = 'user';

if (!empty($message)) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sender_role, created_at, status) VALUES (?, ?, ?, ?, NOW(), 'unread')");
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $sender_role);

    if ($stmt->execute()) {
        echo "Message sent";
    } else {
        echo "Error sending message";
    }
}
?>
