<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // First fetch the book cover path to remove the image if not default
    $stmt = $conn->prepare("SELECT cover_image FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    if ($book) {
        if (!empty($book['cover_image']) && strpos($book['cover_image'], 'default_book.png') === false) {
            $image_path = $_SERVER['DOCUMENT_ROOT'] . $book['cover_image'];
            if (file_exists($image_path)) {
                unlink($image_path); // delete book cover from server
            }
        }

        // Now delete book from database
        $delete = $conn->prepare("DELETE FROM books WHERE id = ?");
        $delete->bind_param("i", $book_id);
        $delete->execute();
    }
}

header("Location: manage_books.php");
exit();
