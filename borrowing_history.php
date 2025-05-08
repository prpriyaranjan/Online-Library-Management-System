<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch borrowing history for the logged-in user
$historyQuery = $pdo->prepare("
    SELECT books.title, borrowings.borrow_date, borrowings.due_date, borrowings.return_date, fines.fine_amount 
    FROM borrowings
    JOIN books ON borrowings.book_id = books.id
    LEFT JOIN fines ON borrowings.id = fines.borrowing_id
    WHERE borrowings.user_id = ?
    ORDER BY borrowings.borrow_date DESC
");
$historyQuery->execute([$user_id]);
$history = $historyQuery->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Borrowing History</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <div class="parallax">
        <h1>Your Borrowing History</h1>
    </div>

    <table>
        <tr>
            <th>Book Title</th>
            <th>Borrow Date</th>
            <th>Due Date</th>
            <th>Return Date</th>
            <th>Fine (if any)</th>
        </tr>
        <?php foreach ($history as $record) { ?>
            <tr>
                <td><?php echo $record['title']; ?></td>
                <td><?php echo $record['borrow_date']; ?></td>
                <td><?php echo $record['due_date']; ?></td>
                <td><?php echo $record['return_date'] ? $record['return_date'] : 'Not Returned'; ?></td>
                <td>₹<?php echo $record['
