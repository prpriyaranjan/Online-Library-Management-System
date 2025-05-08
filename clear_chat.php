<?php
session_start();
require_once('../database/db_connect.php'); // Make sure this path is correct

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_POST['sender_id'] ?? null;
    $receiver_id = $_POST['receiver_id'] ?? null;

    if ($sender_id && $receiver_id) {
        $sql = "DELETE FROM messages 
                WHERE (sender_id = ? AND receiver_id = ?) 
                   OR (sender_id = ? AND receiver_id = ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);

        if ($stmt->execute()) {
            echo json_encode("Chats cleared successfully.");
        } else {
            echo json_encode("Failed to clear chats.");
        }
    } else {
        echo json_encode("Invalid user IDs.");
    }
} else {
    echo json_encode("Invalid request.");
}
