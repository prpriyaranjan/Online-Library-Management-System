<?php
include '../database/db_connect.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
$overdue_books = $pdo->query("SELECT borrowings.id, users.name, books.title, borrowings.due_date, borrowings.returned FROM borrowings JOIN users ON borrowings.user_id = users.id JOIN books ON borrowings.book_id = books.id WHERE borrowings.returned = 0 AND borrowings.due_date < NOW() ORDER BY borrowings.due_date ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Overdue Books & Fines</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <h1>Overdue Books & Fines</h1>
    <table>
        <tr>
            <th>User</th>
            <th>Book</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Fine</th>
        </tr>
        <?php foreach ($overdue_books as $book) { ?>
            <tr>
                <td><?php echo $book['name']; ?></td>
                <td><?php echo $book['title']; ?></td>
                <td><?php echo $book['due_date']; ?></td>
                <td>Overdue</td>
                <td><?php echo "₹" . (ceil((time() - strtotime($book['due_date'])) / 86400) * 5); ?> (₹5 per day)</td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
