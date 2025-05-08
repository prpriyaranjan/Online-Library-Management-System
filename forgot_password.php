<?php
require '../db.php';

$error = "";
$success = "";

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Generate a secure reset token
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Store the token in the database
        $stmt = $pdo->prepare("UPDATE admins SET reset_token = :token, reset_expires = :expires WHERE email = :email");
        $stmt->execute(['token' => $token, 'expires' => $expires, 'email' => $email]);

        // Send reset email
        $reset_link = "http://localhost/TheShivLibrary/auth/reset_password.php?token=$token";
        $subject = "Password Reset - The Shiv Library";
        $message = "Click the following link to reset your password: $reset_link";
        $headers = "From: no-reply@shivlibrary.com\r\n";

        if (mail($email, $subject, $message, $headers)) {
            $success = "Reset link has been sent to your email.";
        } else {
            $error = "Failed to send reset email.";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - The Shiv Library</title>
</head>
<body>
    <h2>Forgot Password</h2>
    
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form action="forgot_password.php" method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send Reset Link</button>
    </form>

    <p><a href="admin_login.php">Back to Login</a></p>
</body>
</html>
