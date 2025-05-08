<?php
session_start();
include '../database/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $profile_photo_filename = "default_user.png";

    if (isset($_FILES["profile_photo"]) && is_uploaded_file($_FILES["profile_photo"]["tmp_name"])) {
        $target_dir = "../assets/images/";
        $original_name = basename($_FILES["profile_photo"]["name"]);
        $safe_name = preg_replace("/[^A-Za-z0-9_\-\.]/", "_", $original_name); // sanitize
        $image_temp = $_FILES["profile_photo"]["tmp_name"];
        $check_name = "/TheShivLibrary/assets/images/" . $safe_name;

        // Check if already used by another admin
        $stmt_check = $conn->prepare("SELECT id FROM admins WHERE profile_photo = ?");
        $stmt_check->bind_param("s", $check_name);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "This profile photo is already used. Please upload a different one.";
        } else {
            $new_file_path = $target_dir . $safe_name;

            if (!file_exists($new_file_path)) {
                move_uploaded_file($image_temp, $new_file_path);
            }

            $profile_photo_filename = $safe_name;
        }
    }

    if (!isset($error)) {
        // Check for existing admin
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->fetch_assoc()) {
            $error = "Admin already registered!";
        } else {
            $profile_path = "/TheShivLibrary/assets/images/" . $profile_photo_filename;
            $stmt = $conn->prepare("INSERT INTO admins (name, email, password, profile_photo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password, $profile_path);

            if ($stmt->execute()) {
                $_SESSION['success'] = "You have registered successfully!";
                header("Location: admin_login.php");
                exit();
            } else {
                $error = "Error registering admin.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Register | The Shiv Library</title>
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
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        h2 {
            color: #ffcc00;
            margin-bottom: 25px;
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
            color: #ddd;
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

        .message {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .error {
            color: red;
        }

        .success {
            color: limegreen;
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
    </style>
</head>
<body>
    <div class="glass-container">
        <h2>Admin Registration</h2>

        <?php
        if (isset($error)) echo "<p class='message error'>{$error}</p>";
        if (isset($success)) echo "<p class='message success'>{$success}</p>";
        ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="file" name="profile_photo" accept="image/*">
            <button type="submit">Register</button>
        </form>

        <div class="links">
            <a href="admin_login.php">Already have an account? Login here!</a>
            <a href="../index.html">Back to Home</a>
        </div>
    </div>
</body>
</html>
