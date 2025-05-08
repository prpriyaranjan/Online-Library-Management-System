<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle password update
if (isset($_POST['update_password'])) {
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $user_id);
    $stmt->execute();
    $success = "Password updated successfully.";
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $email, $mobile, $address, $user_id);
    $stmt->execute();
    $success = "Profile updated successfully.";

    // Refresh user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | The Shiv Library</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #000;
            background-size: cover;
            background-position: center;
            color: white;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .profile-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 100%;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.2);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffcc00;
        }

        label {
            margin-top: 10px;
            display: block;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: none;
            margin-top: 5px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        button {
            padding: 10px;
            background-color: #ffcc00;
            color: black;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            width: 100%;
            margin-top: 10px;
            cursor: pointer;
        }

        button:hover {
            background-color: #e6b800;
        }

        .success {
            color: #00ff88;
            text-align: center;
            margin-bottom: 10px;
        }

        .profile-photo {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-photo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffcc00;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #ffcc00;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>User Profile</h2>

        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

        <div class="profile-photo">
        <img src="../assets/images/<?php echo $user['profile_photo']; ?>" alt="User Photo">



        </div>

        <form method="POST">
            <label for="name">Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="mobile">Mobile</label>
            <input type="text" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>

            <label for="address">Address</label>
            <textarea name="address" required><?php echo htmlspecialchars($user['address']); ?></textarea>

            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <form method="POST">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" required>
            <button type="submit" name="update_password">Update Password</button>
        </form>

        <div class="back-link">
            <a href="user_dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
