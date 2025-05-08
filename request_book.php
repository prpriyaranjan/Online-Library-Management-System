<?php
include '../database/db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT INTO book_requests (user_id, title, author, status) VALUES (?, ?, ?, 'Pending')");
    $stmt->execute([$user_id, $title, $author]);
    echo "<script>alert('Book request submitted!'); window.location.href='user_dashboard.php';</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request a Book</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <h1>Request a New Book</h1>
    <form method="post">
        <label for="title">Book Title:</label>
        <input type="text" name="title" required><br>
        <label for="author">Author:</label>
        <input type="text" name="author" required><br>
        <button type="submit">Submit Request</button>
    </form>
</body>
</html>
