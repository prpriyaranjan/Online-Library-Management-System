<?php
session_start();
include '../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_photo'] = $admin['profile_photo'];
            header("Location: ../admin/admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Invalid email or password!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | The Shiv Library</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #000;
            background-image: url('../assets/images/library_bg.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: white;
        }

        .glass-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        h2 {
            color: #ffcc00;
            margin-bottom: 30px;
        }

        form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 10px;
            border: none;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        form input::placeholder {
            color: #eee;
        }

        form button {
            width: 100%;
            padding: 12px;
            background-color: #ffcc00;
            color: black;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #e6b800;
        }

        .links {
            margin-top: 20px;
        }

        .links a {
            display: block;
            color: #ffcc00;
            text-decoration: none;
            margin-top: 10px;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="glass-container">
        <h2>Admin Login</h2>
        <?php if (isset($error)) echo "<p class='error'>{$error}</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="links">
            <a href="admin_register.php">Don't have an account? Register here!</a>
            <a href="../index.html">Back to Home</a>
        </div>
    </div>
</body>
</html>
