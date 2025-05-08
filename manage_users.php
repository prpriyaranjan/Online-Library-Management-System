<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: manage_users.php");
    exit();
}

// Fetch all users
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'user'");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | The Shiv Library</title>
    <style>
        body {
            background-color: #000;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            color: #fff;
        }

        .container {
            backdrop-filter: blur(14px);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 30px;
            margin: 50px auto;
            max-width: 1150px;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
        }

        h2 {
            color: #ffcc00;
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: rgba(255, 255, 255, 0.02);
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #444;
        }

        th {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffcc00;
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            object-fit: cover;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.3s ease;
            text-decoration: none;
        }

        .btn-fee {
            background-color: #0066cc;
            color: #fff;
        }

        .btn-fee:hover {
            background-color: #0052a3;
        }

        .btn-delete {
            background-color: #cc0000;
            color: #fff;
        }

        .btn-delete:hover {
            background-color: #990000;
        }

        .back {
            margin-top: 20px;
            display: block;
            text-align: center;
        }

        .back a {
            color: #ffcc00;
            text-decoration: none;
            font-size: 16px;
        }

        .back a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Users</h2>
    <table>
        <tr>
            <th>Profile</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <?php
                $userPhoto = "/TheShivLibrary/assets/images/" . basename($user['profile_photo']);
                if (empty($user['profile_photo']) || !file_exists($_SERVER['DOCUMENT_ROOT'] . $userPhoto)) {
                    $userPhoto = "/TheShivLibrary/assets/images/default_user.png";
                }
            ?>
            <tr>
                <td><img src="<?= htmlspecialchars($userPhoto) ?>" alt="Profile"></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['mobile']) ?></td>
                <td><?= htmlspecialchars($user['address']) ?></td>
                <td class="actions">
                    <a class="btn btn-fee" href="manage_user_fees.php?user_id=<?= $user['id'] ?>">💰 Fee</a>
                    <a class="btn btn-delete" href="?delete=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">🗑 Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div class="back">
        <a href="admin_dashboard.php">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
