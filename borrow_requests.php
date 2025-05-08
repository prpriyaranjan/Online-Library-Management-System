<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle approve/reject
if (isset($_GET['action']) && isset($_GET['id'])) {
    $request_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'approved' WHERE id = ?");
        $stmt->execute([$request_id]);
    } elseif ($action == 'reject') {
        $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$request_id]);
    }
}

// Fetch borrow requests
$stmt = $pdo->query("SELECT br.*, u.name as user_name, b.title as book_title 
                     FROM borrow_requests br
                     JOIN users u ON br.user_id = u.id
                     JOIN books b ON br.book_id = b.id
                     ORDER BY br.request_date DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Borrow Requests | The Shiv Library</title>
    <style>
        body {
            background-color: #000;
            color: white;
            font-family: Arial, sans-serif;
            padding: 30px;
        }
        .container {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 25px;
            max-width: 1100px;
            margin: auto;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
        }
        h2 {
            color: #ffcc00;
            text-align: center;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color: rgba(255, 255, 255, 0.03);
        }
        th, td {
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        th {
            color: #ffcc00;
            background-color: rgba(255, 255, 255, 0.05);
        }
        .button {
            padding: 6px 12px;
            background-color: #ffcc00;
            color: #000;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #e6b800;
        }
        .back-link {
            display: block;
            text-align: right;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-link">
            <a href="admin_dashboard.php" class="button">← Back to Dashboard</a>
        </div>
        <h2>Borrow Requests</h2>
        <table>
            <tr>
                <th>User</th>
                <th>Book</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?= htmlspecialchars($req['user_name']) ?></td>
                    <td><?= htmlspecialchars($req['book_title']) ?></td>
                    <td><?= htmlspecialchars($req['request_date']) ?></td>
                    <td><?= htmlspecialchars($req['status']) ?></td>
                    <td>
                        <?php if ($req['status'] === 'pending'): ?>
                            <a href="?action=approve&id=<?= $req['id'] ?>" class="button">Approve</a>
                            <a href="?action=reject&id=<?= $req['id'] ?>" class="button">Reject</a>
                        <?php else: ?>
                            <?= ucfirst($req['status']) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle approve/reject using MySQLi
if (isset($_GET['action']) && isset($_GET['id'])) {
    $request_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        // Approve the request
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();

        // Get the book_id from the request
        $bookStmt = $conn->prepare("SELECT book_id FROM borrow_requests WHERE id = ?");
        $bookStmt->bind_param("i", $request_id);
        $bookStmt->execute();
        $bookResult = $bookStmt->get_result();

        if ($book = $bookResult->fetch_assoc()) {
            $book_id = $book['book_id'];

            // Decrease available copies
            $updateBook = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ? AND available_copies > 0");
            $updateBook->bind_param("i", $book_id);
            $updateBook->execute();
        }

    } elseif ($action == 'reject') {
        // Reject the request
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
    }
}

// Fetch all borrow requests
$stmt = $conn->prepare("SELECT br.*, u.name as user_name, b.title as book_title 
                        FROM borrow_requests br
                        JOIN users u ON br.user_id = u.id
                        JOIN books b ON br.book_id = b.id
                        ORDER BY br.request_date DESC");
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Borrow Requests | The Shiv Library</title>
    <style>
        body {
            background-color: #000;
            color: white;
            font-family: Arial, sans-serif;
            padding: 30px;
        }
        .container {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 25px;
            max-width: 1100px;
            margin: auto;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
        }
        h2 {
            color: #ffcc00;
            text-align: center;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color: rgba(255, 255, 255, 0.03);
        }
        th, td {
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        th {
            color: #ffcc00;
            background-color: rgba(255, 255, 255, 0.05);
        }
        .button {
            padding: 6px 12px;
            background-color: #ffcc00;
            color: #000;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #e6b800;
        }
        .back-link {
            display: block;
            text-align: right;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-link">
            <a href="admin_dashboard.php" class="button">← Back to Dashboard</a>
        </div>
        <h2>Borrow Requests</h2>
        <table>
            <tr>
                <th>User</th>
                <th>Book</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?= htmlspecialchars($req['user_name']) ?></td>
                    <td><?= htmlspecialchars($req['book_title']) ?></td>
                    <td><?= htmlspecialchars($req['request_date']) ?></td>
                    <td><?= htmlspecialchars($req['status']) ?></td>
                    <td>
                        <?php if ($req['status'] === 'pending'): ?>
                            <a href="?action=approve&id=<?= $req['id'] ?>" class="button">Approve</a>
                            <a href="?action=reject&id=<?= $req['id'] ?>" class="button">Reject</a>
                        <?php else: ?>
                            <?= ucfirst($req['status']) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
