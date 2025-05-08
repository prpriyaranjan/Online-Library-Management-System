<?php
include '../database/db_connect.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reservation_id'], $_POST['action'])) {
    $reservation_id = $_POST['reservation_id'];
    $status = ($_POST['action'] == 'approve') ? 'Approved' : 'Rejected';
    $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->execute([$status, $reservation_id]);
    echo "Reservation status updated!";
}
$reservations = $pdo->query("SELECT reservations.id, users.name, books.title, reservations.status FROM reservations JOIN users ON reservations.user_id = users.id JOIN books ON reservations.book_id = books.id ORDER BY reservations.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Reservations</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <h1>Manage Book Reservations</h1>
    <table>
        <tr>
            <th>User</th>
            <th>Book</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($reservations as $res) { ?>
            <tr>
                <td><?php echo $res['name']; ?></td>
                <td><?php echo $res['title']; ?></td>
                <td><?php echo $res['status']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
                        <button type="submit" name="action" value="approve">Approve</button>
                        <button type="submit" name="action" value="reject">Reject</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
