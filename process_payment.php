<?php
include '../database/db_connect.php';
session_start();

if (isset($_POST['payment_id']) && isset($_POST['action'])) {
    $payment_id = intval($_POST['payment_id']);
    $action = $_POST['action'];

    // Process confirmation
    if ($action == 'confirm') {
        // Update payment status to confirmed
        $query = "UPDATE fee_payments SET status = 'Confirmed' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();

        // Optional: Notify user about payment confirmation (via message/email)
        // Assuming you have a 'messages' table to send admin messages to users
        $user_query = mysqli_query($conn, "SELECT user_id FROM fee_payments WHERE id = $payment_id");
        $user_data = mysqli_fetch_assoc($user_query);
        $user_id = $user_data['user_id'];
        $admin_id = $_SESSION['admin_id']; // Assume admin ID is stored in session
        $message = "Your payment has been successfully confirmed.";
        
        // Insert the message into the database
        $insert_msg = "INSERT INTO messages (sender_id, receiver_id, message, sender_role, status) 
                       VALUES (?, ?, ?, 'admin', 'unread')";
        $msg_stmt = $conn->prepare($insert_msg);
        $msg_stmt->bind_param("iis", $admin_id, $user_id, $message);
        $msg_stmt->execute();
        
        // Optionally, you can send an email too
        // mail(...);

        echo "Payment confirmed!";
    } 
    // Process decline
    elseif ($action == 'decline') {
        // Update payment status to declined
        $query = "UPDATE fee_payments SET status = 'Declined' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();

        // Optional: Notify user about payment decline
        $user_query = mysqli_query($conn, "SELECT user_id FROM fee_payments WHERE id = $payment_id");
        $user_data = mysqli_fetch_assoc($user_query);
        $user_id = $user_data['user_id'];
        $admin_id = $_SESSION['admin_id']; // Assume admin ID is stored in session
        $message = "Your payment has been declined.";
        
        // Insert the message into the database
        $insert_msg = "INSERT INTO messages (sender_id, receiver_id, message, sender_role, status) 
                       VALUES (?, ?, ?, 'admin', 'unread')";
        $msg_stmt = $conn->prepare($insert_msg);
        $msg_stmt->bind_param("iis", $admin_id, $user_id, $message);
        $msg_stmt->execute();
        
        // Optionally, you can send an email too
        // mail(...);

        echo "Payment declined!";
    }
} else {
    echo "Invalid action.";
}
?>
