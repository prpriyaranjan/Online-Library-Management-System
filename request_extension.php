<?php
include '../database/db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$borrowings = $pdo->prepare("SELECT borrowings.id, books.title, borrowings.due_date FROM borrowings JOIN books ON borrowings.book_id = books.id WHERE borrowings.user_id = ? AND borrowings.returned = 0");
$borrowings->execute([$user_id]);
$borrowed_books = $borrowings->fetchAll();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['extension_id'])) {
    $extension_id = $_POST['extension_id'];
    $pdo->prepare("UPDATE borrowings SET due_date = DATE_ADD(due_date, INTERVAL 7 DAY) WHERE id = ? AND returned = 0")->execute([$extension_id]);
    echo "Extension Requested Successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Extension</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <h1>Request Book Due Date Extension</h1>
    <table>
        <tr>
            <th>Book</th>
            <th>Current Due Date</th>
            <th>Action</th>
        </tr>
        <?php foreach ($borrowed_books as $book) { ?>
            <tr>
                <td><?php echo $book['title']; ?></td>
                <td><?php echo $book['due_date']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="extension_id" value="<?php echo $book['id']; ?>">
                        <button type="submit">Request Extension</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
