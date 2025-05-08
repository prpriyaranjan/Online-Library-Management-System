<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all pending book requests
$requestsQuery = $pdo->query("
    SELECT book_requests.id, users.name, book_requests.book_title, book_requests.author, book_requests.status 
    FROM book_requests
    JOIN users ON book_requests.user_id = users.id
    WHERE book_requests.status = 'Pending'
");
$requests = $requestsQuery->fetchAll();

// Approve or Reject Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $status = ($_POST['action'] == 'approve') ? 'Approved' : 'Rejected';

    $updateRequest = $pdo->prepare("UPDATE book_requests SET status = ? WHERE id = ?");
    $updateRequest->execute([$status, $request_id]);

    header("Location: book_requests.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Requests</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <h1>Review Book Requests</h1>

    <table>
        <tr>
            <th>User</th>
            <th>Book Title</th>
            <th>Author</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($requests as $request) { ?>
            <tr>
                <td><?php echo $request['name']; ?></td>
                <td><?php echo $request['book_title']; ?></td>
                <td><?php echo $request['author']; ?></td>
                <td><?php echo $request['status']; ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        <button type="submit" name="action" value="approve">Approve</button>
                        <button type="submit" name="action" value="reject">Reject</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

</body>
</html>
