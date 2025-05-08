<?php
session_start();
require_once('database/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch admin details
$admin_query = "SELECT * FROM admins LIMIT 1";
$admin_result = $conn->query($admin_query);
$admin = $admin_result->fetch_assoc();

if (!$admin) {
    echo "<p style='color: red; text-align: center;'>No admin available to chat with right now. Please try again later.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Chat | The Shiv Library</title>
    <link rel="stylesheet" href="/TheShivLibrary/assets/css/chat.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>.clear-btn {
    float: right;
    background-color: rgba(255, 0, 0, 0.2);
    border: none;
    color:rgb(207, 2, 2);
    padding: 6px 12px;
    font-size: 14px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.clear-btn:hover {
    background-color: rgba(255, 0, 0, 0.4);
    color: white;
}
.message {
    display: flex;
    align-items: flex-start;
    margin-bottom: 10px;
    gap: 10px;
    padding: 10px;
    max-width: 70%;
    border-radius: 10px;
    background-color: rgba(255,255,255,0.05);
    color: white;
}

.admin-message {
    align-self: flex-start;
    background-color: rgba(0, 123, 255, 0.1);
}

.user-message {
    align-self: flex-end;
    background-color: rgba(0, 255, 100, 0.1);
    margin-left: auto;
    flex-direction: row-reverse;
}

.message .profile-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.2);
}

.message-text {
    background-color: rgba(0, 0, 0, 0.3);
    padding: 10px;
    border-radius: 10px;
    max-width: 100%;
    word-wrap: break-word;
}

</style>
</head>
<body>
<div class="chat-container">
    <div class="chat-header">
        <h2>Chat with Admin</h2>
        <div class="chat-header">
    Chat with Admin
    <button id="clearChatBtn" class="clear-btn">🗑️ Clear Chat</button>
</div>
        <div style="padding: 10px;">
            <a href="/TheShivLibrary/users/user_dashboard.php" style="color: #00aaff; text-decoration: none;">← Back to Dashboard</a>
        </div>
    </div>

    <div id="chatBox" class="chat-box"></div>

    <form id="chatForm" class="chat-form">
        <input type="text" id="messageInput" placeholder="Type your message..." required>
        <button type="submit">Send</button>
    </form>

</div>

<script>
    const chatBox = document.getElementById('chatBox');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('messageInput');

    function fetchMessages() {
        $.ajax({
            url: 'fetch_messages.php',
            method: 'POST',
            data: {
                sender_id: <?php echo $user_id; ?>,
                receiver_id: <?php echo $admin['id']; ?>
            },
            success: function (data) {
                $('#chatBox').html(data);
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
    }

    setInterval(fetchMessages, 1000);
    fetchMessages();

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const message = input.value.trim();
        if (message === '') return;

        $.ajax({
            url: 'sendmsg.php',
            method: 'POST',
            data: {
                sender_id: <?php echo $user_id; ?>,
                receiver_id: <?php echo $admin['id']; ?>,
                message: message,
                sender_role: 'user'
            },
            success: function () {
                input.value = '';
                fetchMessages();
            }
        });
    });
    document.getElementById('clearChatBtn').addEventListener('click', function () {
    if (confirm("Are you sure you want to delete all chats?")) {
        $.ajax({
            url: 'clear_chat.php',
            method: 'POST',
            data: {
                sender_id: <?php echo $user_id; ?>,
                receiver_id: <?php echo $admin['id']; ?>
            },
            success: function (res) {
                fetchMessages();
            }
        });
    }
});

</script>
</body>
</html>
