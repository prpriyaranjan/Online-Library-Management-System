<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['book_id']) && isset($_POST['user_id'])) {
    $book_id = intval($_POST['book_id']);
    $user_id = intval($_POST['user_id']);

    // Check if book was borrowed and not yet returned
    $checkQuery = "SELECT id FROM borrowings WHERE user_id = ? AND book_id = ? AND return_date IS NULL";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // Update borrowings table to mark as returned
        $updateReturn = "UPDATE borrowings SET return_date = NOW() WHERE user_id = ? AND book_id = ? AND return_date IS NULL";
        $stmtReturn = $conn->prepare($updateReturn);
        $stmtReturn->bind_param("ii", $user_id, $book_id);
        $stmtReturn->execute();

        // Check if borrowings table was updated
        if ($stmtReturn->affected_rows > 0) {
            // Now update the book's available stock
            $updateBook = "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?";
            $stmtBook = $conn->prepare($updateBook);
            $stmtBook->bind_param("i", $book_id);

            if ($stmtBook->execute()) {
                if ($stmtBook->affected_rows > 0) {
                    $_SESSION['message'] = "Book returned and stock updated.";
                } else {
                    $_SESSION['message'] = "Book return recorded, but available stock was not updated.";
                }
            } else {
                $_SESSION['message'] = "Failed to update available stock: " . $stmtBook->error;
            }
        } else {
            $_SESSION['message'] = "Failed to update return date.";
        }
    } else {
        $_SESSION['message'] = "No active borrowing found for this book.";
    }

    header("Location: http://localhost/TheShivLibrary/users/borrowed_books.php");
    exit();
} else {
    $_SESSION['message'] = "Invalid request.";
    header("Location: http://localhost/TheShivLibrary/users/borrowed_books.php");
    exit();
}
?>
