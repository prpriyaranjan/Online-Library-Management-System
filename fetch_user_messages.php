<?php
require_once('../database/db_connect.php');
session_start();

if (!isset($_POST['admin_id']) || !isset($_POST['user_id'])) {
    exit("Invalid request.");
}

$admin_id = intval($_POST['admin_id']);
$user_id = intval($_POST['user_id']);

// Fetch admin profile photo (from admins table)
$admin_stmt = $conn->prepare("SELECT profile_photo FROM admins WHERE id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin_data = $admin_result->fetch_assoc();

$admin_photo = (!empty($admin_data['profile_photo']))
    ? htmlspecialchars($admin_data['profile_photo'])
    : 'default_admin.png';

if (strpos($admin_photo, '/TheShivLibrary') === false) {
    $admin_photo = '/TheShivLibrary/assets/images/' . $admin_photo;
}

// Fetch user profile photo (from users table)
$user_stmt = $conn->prepare("SELECT profile_photo FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

$user_photo = (!empty($user_data['profile_photo']))
    ? htmlspecialchars($user_data['profile_photo'])
    : 'default_user.png';

if (strpos($user_photo, '/TheShivLibrary') === false) {
    $user_photo = '/TheShivLibrary/assets/images/' . $user_photo;
}

// Fetch chat messages
$stmt = $conn->prepare("SELECT * FROM messages WHERE 
    (sender_id = ? AND receiver_id = ?) 
    OR 
    (sender_id = ? AND receiver_id = ?) 
    ORDER BY created_at ASC");
$stmt->bind_param("iiii", $admin_id, $user_id, $user_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $isAdmin = $row['sender_role'] === 'admin';
    $profile = $isAdmin ? $admin_photo : $user_photo;
    $class = $isAdmin ? 'admin' : 'user';

    echo '
    <div class="message-container ' . $class . '">
        ' . (!$isAdmin ? '<img src="' . $profile . '" class="profile-pic" alt="User">' : '') . '
        <div class="bubble">' . htmlspecialchars($row['message']) . '</div>
        ' . ($isAdmin ? '<img src="' . $profile . '" class="profile-pic" alt="Admin">' : '') . '
    </div>';
}
?>
