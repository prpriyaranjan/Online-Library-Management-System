<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: /TheShivLibrary/auth/admin_login.php");
    exit();
}

// Fetch admin details from `admins` table using MySQLi
$adminQuery = $conn->prepare("SELECT name, profile_photo FROM admins WHERE id = ?");
$adminQuery->bind_param("i", $_SESSION['admin_id']);
$adminQuery->execute();
$adminResult = $adminQuery->get_result();
$admin = $adminResult->fetch_assoc();

$adminName = $admin && isset($admin['name']) ? $admin['name'] : 'Admin';
$profilePhoto = $admin && isset($admin['profile_photo']) ? $admin['profile_photo'] : '/TheShivLibrary/assets/images/default_user.png';

// If profile photo path doesn't already start from root, add base path
$profileImagePath = (strpos($profilePhoto, '/TheShivLibrary/') === 0) ? $profilePhoto : '/TheShivLibrary' . $profilePhoto;

// Fetch dashboard stats
function fetchCount($conn, $query) {
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_row()) {
        return $row[0];
    }
    return 0;
}

$totalUsers = fetchCount($conn, "SELECT COUNT(*) FROM users WHERE role = 'user'");
$totalBooks = fetchCount($conn, "SELECT COUNT(*) FROM books");
$borrowedBooks = fetchCount($conn, "SELECT COUNT(*) FROM borrowings WHERE return_date IS NULL");
$pendingRequests = fetchCount($conn, "SELECT COUNT(*) FROM borrow_requests WHERE status = 'pending'");
$overdueBooks = fetchCount($conn, "SELECT COUNT(*) FROM borrowings WHERE due_date < NOW() AND return_date IS NULL");

// Unread message count
$messageQuery = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND sender_role = 'user' AND status = 'unread'");
$messageQuery->bind_param("i", $_SESSION['admin_id']);
$messageQuery->execute();
$messageQuery->bind_result($unreadMessages);
$messageQuery->fetch();
$messageQuery->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | The Shiv Library</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: black;
            color: white;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.1);
            height: 100vh;
            padding-top: 20px;
            position: fixed;
            left: 0;
            backdrop-filter: blur(10px);
            box-shadow: 2px 0px 10px rgba(255, 255, 255, 0.2);
        }

        .sidebar h2 {
            color: #ffcc00;
            text-align: center;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px;
            margin: 5px 10px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }

        .sidebar a.logout {
            color: red;
        }

        .dashboard-container {
            margin-left: 260px;
            padding: 40px 20px;
            width: calc(100% - 260px);
            text-align: center;
        }

        .admin-info {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        .admin-info img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
            border: 2px solid #ffcc00;
            object-fit: cover;
        }

        .admin-info h1 {
            font-size: 28px;
        }

        .stat-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.2);
            text-align: center;
            transition: transform 0.3s;
            width: 200px;
            height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-box:hover {
            transform: scale(1.05);
        }

        .stat-box h3 {
            font-size: 16px;
            color: #ffcc00;
        }

        .stat-box p {
            font-size: 20px;
            font-weight: bold;
        }

        .message-alert {
            background: #ffcc00;
            color: black;
            padding: 12px;
            margin-top: 30px;
            border-radius: 8px;
            font-weight: bold;
        }

        .message-alert a {
            color: black;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_books.php">Manage Books</a>
    <a href="generate_reports.php">Generate Reports</a>
    <a href="view_borrow_requests.php">Pending Borrow Requests</a>
    <a href="admin_chat.php">💬 Messages <?php if ($unreadMessages > 0) echo "($unreadMessages)"; ?></a>
    <a href="activity_logs.php">User Activity Logs</a>
    <a class="logout" href="../logout.php">Logout</a>

    <!-- Delete Admin Account Button -->
    <form action="delete_admin.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');" style="margin-top: 20px;">
        <input type="hidden" name="admin_id" value="<?php echo $_SESSION['admin_id']; ?>">
        <button type="submit" style="
            background-color: red;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0px 4px 10px rgba(255, 0, 0, 0.3);
            transition: 0.3s;
        ">🗑️ Delete My Account</button>
    </form>
</div>
    

    <div class="dashboard-container">
        <div class="admin-info">
            <img src="<?php echo htmlspecialchars($profileImagePath); ?>" alt="Admin Profile">
            <h1>Welcome, <?php echo htmlspecialchars($adminName); ?>!</h1>
        </div>

        <div class="stat-container">
            <div class="stat-box">
                <h3>Total Users</h3>
                <p><?php echo $totalUsers; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Books</h3>
                <p><?php echo $totalBooks; ?></p>
            </div>
            <div class="stat-box">
                <h3>Borrowed Books</h3>
                <p><?php echo $borrowedBooks; ?></p>
            </div>
            <div class="stat-box">
                <h3>Pending Requests</h3>
                <p><?php echo $pendingRequests; ?></p>
            </div>
            <div class="stat-box">
                <h3>Overdue Books</h3>
                <p><?php echo $overdueBooks; ?></p>
            </div>
        </div>

        <?php if ($unreadMessages > 0): ?>
            <div class="message-alert">
                📬 You have <?php echo $unreadMessages; ?> unread message<?php echo $unreadMessages > 1 ? 's' : ''; ?> from users.
                <a href="admin_chat.php">Open Messages</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
