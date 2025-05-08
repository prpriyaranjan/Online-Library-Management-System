<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/user_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);
    $user_id = intval($_SESSION['user_id']);

    // Check for existing pending request
    $check = $conn->prepare("SELECT id FROM borrow_requests WHERE user_id = ? AND book_id = ? AND status = 'pending'");
    $check->bind_param("ii", $user_id, $book_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['message'] = "⚠️ You have already requested this book and it's pending.";
    } else {
        $insert = $conn->prepare("INSERT INTO borrow_requests (user_id, book_id, status) VALUES (?, ?, 'pending')");
        $insert->bind_param("ii", $user_id, $book_id);

        if ($insert->execute()) {
            $_SESSION['message'] = "✅ Borrow request submitted successfully!";
        } else {
            $_SESSION['message'] = "❌ Failed to submit borrow request. Please try again.";
        }
        $insert->close();
    }

    $check->close();
    $conn->close();
}

header("Location: search_books.php");
exit();
