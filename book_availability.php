<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all books with their availability status
$bookQuery = $pdo->query("
    SELECT books.id, books.title, books.author, books.genre, 
           (CASE 
                WHEN borrowings.book_id IS NOT NULL THEN 'Checked Out'
                ELSE 'Available'
            END) AS availability 
    FROM books 
    LEFT JOIN borrowings ON books.id = borrowings.book_id AND borrowings.return_date IS NULL
");
$books = $bookQuery->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Availability</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <div class="parallax">
        <h1>Book Availability</h1>
    </div>

    <table>
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Genre</th>
            <th>Availability</th>
        </tr>
        <?php foreach ($books as $book) { ?>
            <tr>
                <td><?php echo $book['title']; ?></td>
                <td><?php echo $book['author']; ?></td>
                <td><?php echo $book['genre']; ?></td>
                <td><?php echo $book['availability']; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
