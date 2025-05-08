<?php
require '../db.php';

$token = $_GET['token'] ?? '';
$error = "";

if ($token) {
    // Verify token
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE reset_token = :token AND reset_expires > NOW()");
    $stmt->execute(['token' => $token]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        die("Invalid or expired token.");
    }
} else {
    die("No token provided.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Your Password</h2>
    
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="update_password.php" method="POST">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <input type="password" name="new_password" placeholder="Enter new password" required>
        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
