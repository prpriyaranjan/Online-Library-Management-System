<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all users for the dropdown
$usersQuery = "SELECT id, name FROM users";
$usersResult = $conn->query($usersQuery);
$users = $usersResult->fetch_all(MYSQLI_ASSOC);

// Default query
$query = "SELECT logs.id, users.name, logs.action, logs.timestamp 
          FROM logs 
          JOIN users ON logs.user_id = users.id 
          ORDER BY logs.timestamp DESC";

// Filter if user is selected
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $query = "SELECT logs.id, users.name, logs.action, logs.timestamp 
              FROM logs 
              JOIN users ON logs.user_id = users.id 
              WHERE logs.user_id = $user_id 
              ORDER BY logs.timestamp DESC";
}

$result = $conn->query($query);
if (!$result) {
    die("Error executing query: " . $conn->error);
}
$logs = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Activity Logs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #121212;
            color: white;
        }

        .parallax {
            background-image: url('../assets/images/parallax-bg.jpg');
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .header-text {
            font-size: 48px;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.5);
            padding: 20px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .user-select {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .user-select label {
            font-size: 18px;
            color: #ddd;
        }

        .user-select select {
            padding: 10px;
            font-size: 16px;
            background-color: #1f1f1f;
            color: white;
            border: 1px solid #333;
            border-radius: 8px;
        }

        .user-select input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #00c853;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .user-select input[type="submit"]:hover {
            background-color: #00b342;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            color: white;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        th {
            background-color: #222;
            font-size: 18px;
        }

        tr:nth-child(even) {
            background-color: #1e1e1e;
        }

        tr:hover {
            background-color: #2a2a2a;
        }

        .no-logs {
            text-align: center;
            font-size: 18px;
            padding: 30px;
            color: #aaa;
        }

        @media (max-width: 768px) {
            .header-text {
                font-size: 32px;
                padding: 15px 25px;
            }
            .user-select {
                flex-direction: column;
                align-items: flex-start;
            }
            table, th, td {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="parallax">
        <div class="header-text">User Activity Logs</div>
    </div>

    <!-- Content -->
    <div class="container">
        <form method="GET" action="activity_logs.php" class="user-select">
            <label for="user_id">Select User:</label>
            <select name="user_id" id="user_id">
                <option value="">All Users</option>
                <?php foreach ($users as $user) { ?>
                    <option value="<?= $user['id'] ?>" <?= (isset($_GET['user_id']) && $_GET['user_id'] == $user['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($user['name']) ?>
                    </option>
                <?php } ?>
            </select>
            <input type="submit" value="Filter">
        </form>

        <?php if (count($logs) > 0): ?>
            <table>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                </tr>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['name']) ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= $log['timestamp'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <div class="no-logs">No activity logs found.</div>
        <?php endif; ?>
    </div>

</body>
</html>
