<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details and last payment date
$feeQuery = $pdo->prepare("
    SELECT name, email, last_payment_date 
    FROM users 
    WHERE id = ?
");
$feeQuery->execute([$user_id]);
$user = $feeQuery->fetch();

// Calculate the next due date
$lastPaymentDate = new DateTime($user['last_payment_date']);
$nextDueDate = $lastPaymentDate->modify('+1 month');
$today = new DateTime();

$reminderMessage = "";
if ($today >= $nextDueDate) {
    $reminderMessage = "Your monthly fee is due. Please make the payment to continue accessing library services.";
} else {
    $daysRemaining = $today->diff($nextDueDate)->days;
    $reminderMessage = "Your next fee payment is due in $daysRemaining days.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fee Reminder</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <div class="parallax">
        <h1>Monthly Fee Reminder</h1>
    </div>

    <div class="container">
        <p><strong>Hello, <?php echo $user['name']; ?>!</strong></p>
        <p><?php echo $reminderMessage; ?></p>
        <a href="../users/pay_fines.php"><button>Pay Now</button></a>
    </div>
</body>
</html>
