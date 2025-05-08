<?php
require '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        die("Both password fields are required.");
    }

    if ($new_password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the database
    $stmt = $pdo->prepare("UPDATE admins SET password = :password, reset_token = NULL, reset_expires = NULL WHERE reset_token = :token");
    $stmt->execute(['password' => $hashed_password, 'token' => $token]);

    if ($stmt->rowCount()) {
        echo "Password reset successful! <a href='admin_login.php'>Login here</a>";
    } else {
        echo "Invalid or expired token.";
    }
}
?>
