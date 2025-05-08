<?php
session_start();
require_once('../database/db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Fetch admin details
$admin = [];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) $admin = $result->fetch_assoc();

// Get selected user ID
$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch all non-admin users
$users_result = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY name ASC");

// Fetch selected user
$selected_user = null;
if ($selected_user_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $selected_user_id);
    $stmt->execute();
    $selected_user_result = $stmt->get_result();
    $selected_user = $selected_user_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Chat | The Shiv Library</title>
    <link rel="stylesheet" href="/TheShivLibrary/assets/css/chat.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #1e1e2f;
            color: #fff;
        }
        .chat-wrapper {
            display: flex;
            height: 100vh;
        }
        .user-list {
            width: 260px;
            background: #111;
            overflow-y: auto;
            border-right: 1px solid #333;
        }
        .user-list h3 {
            text-align: center;
            padding: 15px;
            margin: 0;
            background: #222;
            font-size: 18px;
        }
        .user-list a {
            display: block;
            padding: 12px 16px;
            color: #ccc;
            text-decoration: none;
            border-bottom: 1px solid #333;
        }
        .user-list a.active,
        .user-list a:hover {
            background: #333;
            color: #fff;
        }
        .chat-box {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #222;
        }
        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #444;
            background: #2c2c3c;
            font-weight: bold;
        }
        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        .message {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .message img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .message.admin {
            flex-direction: row-reverse;
        }
        .message.admin img {
            margin-left: 10px;
            margin-right: 0;
        }
        .bubble {
            background: #444;
            padding: 10px 15px;
            border-radius: 12px;
            max-width: 70%;
            word-wrap: break-word;
        }
        .admin .bubble {
            background: #007bff;
            color: #fff;
        }
        .chat-form {
            display: flex;
            padding: 15px;
            border-top: 1px solid #444;
        }
        .chat-form input[type="text"] {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            border: none;
            outline: none;
            background: #333;
            color: white;
        }
        .chat-form button {
            padding: 10px 15px;
            margin-left: 10px;
            background: #00aaff;
            border: none;
            border-radius: 10px;
            color: white;
            cursor: pointer;
        }
        .back-link {
            text-align: center;
            margin: 10px 0;
        }
        .back-link a {
            color: #00aaff;
            text-decoration: none;
        }
        .clear-btn {
    float: right;
    background-color: rgba(255, 0, 0, 0.2);
    border: none;
    color: #ff4d4d;
    padding: 6px 12px;
    font-size: 14px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s ease;
}
.clear-btn:hover {
    background-color: rgba(255, 0, 0, 0.4);
    color: white;
}
.message-container {
    display: flex;
    align-items: flex-end;
    margin: 10px;
    max-width: 90%;
}

.message-container.admin {
    justify-content: flex-end;
}

.message-container.admin .profile-pic {
    margin-left: 10px;
}


.profile-pic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 10px;
    box-shadow: 0 0 5px rgba(0,0,0,0.3);
}

.bubble {
    background: rgba(255, 255, 255, 0.1);
    color: #f1f1f1;
    padding: 10px 14px;
    border-radius: 18px;
    max-width: 75%;
    word-wrap: break-word;
    backdrop-filter: blur(6px);
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

    </style>
</head>
<body>
<div class="chat-wrapper">
    <!-- User list panel -->
    <div class="user-list">
        <h3>Users</h3>
        <?php while ($user = $users_result->fetch_assoc()): ?>
            <a href="admin_chat.php?user_id=<?= $user['id'] ?>" class="<?= $selected_user_id == $user['id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($user['name']) ?>
            </a>
        <?php endwhile; ?>
        <div class="back-link">
            <a href="/TheShivLibrary/admin/admin_dashboard.php">← Back to Dashboard</a>
            
        </div>
    </div>

    <!-- Chat interface -->
    <div class="chat-box">
        <div class="chat-header">
            <?= $selected_user ? 'Chatting with ' . htmlspecialchars($selected_user['name']) : 'Select a user to start chat'; ?>&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="hidden" id="user_id" value="<?php echo $selected_user_id; ?>">


            <button id="clearChatBtn" class="clear-btn">🗑️ Clear All Chats</button>

        </div>

        <div class="messages" id="messages">
            <!-- Messages will be loaded here via AJAX -->
        </div>

        <?php if ($selected_user): ?>
        <form class="chat-form" method="POST" action="/TheShivLibrary/admin/send_message.php">
            <input type="hidden" name="receiver_id" value="<?= $selected_user_id ?>">
            <input type="text" name="message" placeholder="Type a message..." required>
            <button type="submit">Send</button>
        </form>
        <?php endif; ?>
    </div>
    

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const adminId = <?= $admin_id ?>;
    const selectedUserId = document.getElementById('user_id').value;

    function fetchMessages(admin_id, user_id) {
        $.ajax({
            url: '/TheShivLibrary/admin/fetch_user_messages.php',
            method: 'POST',
            data: { admin_id: admin_id, user_id: user_id },
            success: function(data) {
                $('#messages').html(data);
                $('#messages').scrollTop($('#messages')[0].scrollHeight);
            }
        });
    }

    document.getElementById('clearChatBtn').addEventListener('click', function () {
        if (confirm("Are you sure you want to delete all chats with this user?")) {
            const receiver_id = selectedUserId;

            $.ajax({
                url: '/TheShivLibrary/admin/clear_chat.php',
                method: 'POST',
                data: {
                    sender_id: adminId,
                    receiver_id: receiver_id
                },
                success: function (response) {
                    alert(response);
                    fetchMessages(adminId, receiver_id);
                },
                error: function () {
                    alert("Error clearing chat.");
                }
            });
        }
    });

    // Call fetch every 3 seconds
    setInterval(function () {
        if (selectedUserId > 0) {
            fetchMessages(adminId, selectedUserId);
        }
    }, 3000);

    // Initial load
    if (selectedUserId > 0) {
        fetchMessages(adminId, selectedUserId);
    }

    // Clear chat
    document.getElementById('clearChatBtn').addEventListener('click', function () {
        if (confirm("Are you sure you want to delete all chats with this user?")) {
            const receiver_id = selectedUserId;

            $.ajax({
                url: 'clear_chat.php',
                method: 'POST',
                data: {
                    sender_id: adminId,
                    receiver_id: receiver_id
                },
                success: function (response) {
                    alert(response);
                    fetchMessages(adminId, receiver_id); // Proper call
                },
                error: function () {
                    alert("Error clearing chat.");
                }
            });
        }
    });
</script>

</body>
</html>
