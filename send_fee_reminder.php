<?php
include '../database/db_connect.php';
require '../mail/send_mail.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_email = $_POST['user_email'];
    $user_name = $_POST['user_name'];

    $message = "Dear $user_name,\n\nThis is a gentle reminder to pay your monthly library fee. Please upload the payment screenshot from your dashboard.\n\nThank you!";
    sendMail($user_email, "Library Fee Reminder", $message);

    echo "<script>alert('Reminder sent to $user_name.'); window.location.href='manage_fees.php';</script>";
}
?>
