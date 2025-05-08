<?php
session_start();
include '../database/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $mobile   = $_POST['mobile'];
    $address  = $_POST['address'];
    $user_id  = uniqid('user_');

    // Default profile photo filename
    $profile_photo = "default_user.png";
    $photo_filename = "";
    $photo_tmp = "";

    if (!empty($_FILES["profile_photo"]["name"])) {
        $photo_filename = time() . "_" . basename($_FILES["profile_photo"]["name"]);
        $profile_photo  = $photo_filename; // Just the filename
        $photo_tmp      = $_FILES["profile_photo"]["tmp_name"];
    }

    // Check for duplicate email
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "User already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, user_id, email, password, mobile, address, profile_photo, role, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'user', NOW())");
        $stmt->bind_param("sssssss", $name, $user_id, $email, $password, $mobile, $address, $profile_photo);

        if ($stmt->execute()) {
            // Save image file
            if (!empty($photo_tmp)) {
                $target_path = realpath(__DIR__ . '/../assets/images') . '/' . $photo_filename;
                move_uploaded_file($photo_tmp, $target_path);
            }

            $_SESSION['success'] = "You have registered successfully.";
            header("Location: user_login.php");
            exit();
        } else {
            $error = "Error: Could not register user.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration | The Shiv Library</title>
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

        .register-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px;
            max-width: 450px;
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

        input, textarea {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 10px;
            margin-top: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 14px;
        }

        input::placeholder, textarea::placeholder {
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
            background-color: rgba(255, 0, 0, 0.1);
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
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

        #preview {
            display: none;
            margin-top: 10px;
            border-radius: 10px;
            width: 80px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>User Registration</h2>

        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <form action="user_register.php" method="POST" enctype="multipart/form-data">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Enter full name" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>

            <label for="mobile">Mobile Number</label>
            <input type="tel" id="mobile" name="mobile" placeholder="Enter your mobile number" required>

            <label for="address">Address</label>
            <textarea id="address" name="address" placeholder="Enter your address" required></textarea>

            <label for="profile_photo">Profile Photo</label>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/*" onchange="previewImage(event)">
            <img id="preview" alt="Preview">

            <button type="submit">Register as User</button>
        </form>

        <p>Already have an account? <a href="user_login.php">Login here</a></p>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const img = document.getElementById('preview');
                img.src = reader.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
