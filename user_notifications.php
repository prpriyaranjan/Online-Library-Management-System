<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch unread messages from admin
$notificationQuery = $pdo->prepare("
    SELECT id, message, reply, created_at 
    FROM messages 
    WHERE user_id = ? AND reply IS NOT NULL AND is_read = 0
");
$notificationQuery->execute([$user_id]);
$notifications = $notificationQuery->fetchAll();

// Mark notifications as read
$markAsRead = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND reply IS NOT NULL");
$markAsRead->execute([$user_id]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <div class="parallax">
        <h1>Admin Replies</h1>
    </div>

    <div class="container">
        <?php if ($notifications) { ?>
            <?php foreach ($notifications as $note) { ?>
                <div class="card">
                    <p><strong>Your Message:</strong> <?php echo $note['message']; ?></p>
                    <p><strong>Admin's Reply:</strong> <?php echo $note['reply']; ?></p>
                    <p><em>Received on: <?php echo $note['created_at']; ?></em></p>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No new replies from the admin.</p>
        <?php } ?>
    </div>
</body>
</html>
