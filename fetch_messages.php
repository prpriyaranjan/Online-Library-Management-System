<?php
require_once('database/db_connect.php');

if (!isset($_POST['sender_id']) || !isset($_POST['receiver_id'])) {
    echo "Invalid request.";
    exit;
}

$sender_id = intval($_POST['sender_id']);
$receiver_id = intval($_POST['receiver_id']);

$query = "SELECT * FROM messages WHERE 
    (sender_id = ? AND receiver_id = ?) 
    OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

$profile_photos = [];

while ($row = $result->fetch_assoc()) {
    $message_sender_id = $row['sender_id'];
    $sender_role = $row['sender_role'];

    // Check and store profile photo if not already fetched
    if (!isset($profile_photos[$message_sender_id])) {
        if ($sender_role === 'admin') {
            $photo_query = $conn->prepare("SELECT profile_photo FROM admins WHERE id = ?");
        } else {
            $photo_query = $conn->prepare("SELECT profile_photo FROM users WHERE id = ?");
        }

        $photo_query->bind_param("i", $message_sender_id);
        $photo_query->execute();
        $photo_result = $photo_query->get_result();
        $photo_data = $photo_result->fetch_assoc();

        if ($photo_data && !empty($photo_data['profile_photo'])) {
            // If photo path already includes 'assets/images', use it directly
            if (strpos($photo_data['profile_photo'], 'assets/images') !== false) {
                $profile_photos[$message_sender_id] = $photo_data['profile_photo'];
            } else {
                $profile_photos[$message_sender_id] = '/TheShivLibrary/assets/images/' . $photo_data['profile_photo'];
            }
        } else {
            // Use default if no photo
            $profile_photos[$message_sender_id] = '/TheShivLibrary/assets/images/' . ($sender_role === 'admin' ? 'default_admin.png' : 'default_user.png');
        }
    }

    $photo_path = $profile_photos[$message_sender_id];
    $message_class = ($sender_id == $message_sender_id) ? 'my-message' : 'their-message';

    echo '<div class="message ' . $message_class . '">';
    echo '<img class="profile-img" src="' . htmlspecialchars($photo_path) . '" alt="Profile Photo">';
    echo '<p>' . htmlspecialchars($row['message']) . '</p>';
    echo '</div>';
}
?>
