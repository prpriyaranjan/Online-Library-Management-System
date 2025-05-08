<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's overdue fines
$fineQuery = $pdo->prepare("SELECT borrowings.id, books.title, borrowings.due_date, 
                                   DATEDIFF(NOW(), borrowings.due_date) AS overdue_days
                            FROM borrowings
                            JOIN books ON borrowings.book_id = books.id
                            WHERE borrowings.user_id = ? 
                            AND borrowings.return_date IS NULL 
                            AND borrowings.due_date < NOW()");
$fineQuery->execute([$user_id]);
$fines = $fineQuery->fetchAll();

// Process payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['borrow_id'])) {
    $borrow_id = $_POST['borrow_id'];
    
    // Mark fine as paid (for simplicity, we assume direct payment)
    $updateFine = $pdo->prepare("UPDATE borrowings SET fine_paid = 1 WHERE id = ? AND user_id = ?");
    $updateFine->execute([$borrow_id, $user_id]);
    
    header("Location: pay_fines.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pay Fines</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <h1>Your Overdue Fines</h1>
    <table>
        <tr>
            <th>Book Title</th>
            <th>Due Date</th>
            <th>Overdue Days</th>
            <th>Fine (₹10/day)</th>
            <th>Action</th>
        </tr>
        <?php foreach ($fines as $fine) { ?>
            <tr>
                <td><?php echo $fine['title']; ?></td>
                <td><?php echo $fine['due_date']; ?></td>
                <td><?php echo $fine['overdue_days']; ?></td>
                <td>₹<?php echo $fine['overdue_days'] * 10; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="borrow_id" value="<?php echo $fine['id']; ?>">
                        <button type="submit">Pay Fine</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>