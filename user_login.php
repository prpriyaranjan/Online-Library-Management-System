<?php
include '../database/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header("Location: ../users/user_dashboard.php");
        exit();
    } else {
        $error = "User not found or invalid email/password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login | The Shiv Library</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0,0,0,0.9), rgba(0,0,0,0.9)), url('../assets/images/library_bg.jpg') no-repeat center center/cover;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px;
            max-width: 400px;
            width: 100%;
            color: #ffffff;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffcc00;
        }

        label {
            display: block;
            margin-top: 15px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 10px;
            margin-top: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 14px;
        }

        input::placeholder {
            color: #ddd;
        }

        button {
            background-color: #ffcc00;
            color: #000;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #e0b800;
        }

        .error {
            text-align: center;
            color: #ff4d4d;
            font-size: 13px;
        }

        .success {
            text-align: center;
            color: #00ff88;
            font-size: 13px;
        }

        p {
            text-align: center;
            margin-top: 15px;
            color: #ddd;
        }

        a {
            color: #ffcc00;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>User Login</h2>

        <?php if (isset($_SESSION['success'])) { ?>
            <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php } ?>

        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <form action="user_login.php" method="POST">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="user_register.php">Register here</a></p>
    </div>
</body>
</html>
