<?php
session_start();
include '../database/db_connect.php';

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT name, email, profile_photo FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
if (!$user_stmt) {
    die('User query prepare failed: ' . $conn->error);
}
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if (!$user_result) {
    die('User query failed: ' . $conn->error);
}
$user = $user_result->fetch_assoc();
$profilePhoto = $user['profile_photo'] ?? 'default.png';

// Fetch borrowed books (adjust for status column if not present)
$query = "SELECT bo.book_id, bo.borrow_date, bo.due_date, b.title 
          FROM borrowings bo
          JOIN books b ON bo.book_id = b.id
          WHERE bo.user_id = ? AND bo.return_date IS NULL";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Borrowed books query prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die('Borrowed books query failed: ' . $conn->error);
}

$borrowedBooks = [];
while ($row = $result->fetch_assoc()) {
    $borrowedBooks[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Borrowed Books | The Shiv Library</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #000;
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        .dashboard-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 1000px;
            backdrop-filter: blur(12px);
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.2);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h2 {
            color: #ffcc00;
            margin: 0;
        }
        .profile-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .profile-info img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffcc00;
        }
        .borrowed-books {
            margin-top: 30px;
        }
        .borrowed-books table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: white;
        }
        .borrowed-books th, .borrowed-books td {
            padding: 12px;
            border: 1px solid #fff;
            text-align: left;
        }
        .borrowed-books th {
            background-color: #ffcc00;
            color: black;
        }
        .borrowed-books tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }
        .borrowed-books tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .back-to-dashboard-btn {
            display: inline-block;
            background-color: #ffcc00;
            color: black;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }
        .back-to-dashboard-btn:hover {
            background-color: #f1a500;
        }
        .return-btn {
            padding: 6px 12px;
            background-color: #f44336;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .message-box {
            background-color: #00c853;
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message-box">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']); 
                ?>
            </div>
        <?php endif; ?>

        <div class="header">
            <div class="profile-info">
                <img src="../assets/images/<?php echo htmlspecialchars($profilePhoto); ?>" alt="User Photo">
                <div>
                    <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
        </div>

        <div class="borrowed-books">
            <h2>My Borrowed Books</h2>
            <table>
                <tr>
                    <th>Book Title</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
                <?php if (!empty($borrowedBooks)): ?>
                    <?php foreach ($borrowedBooks as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['borrow_date']); ?></td>
                            <td><?php echo htmlspecialchars($book['due_date']); ?></td>
                            <td>
                                <form action="/TheShivLibrary/users/return_books.php" method="POST" onsubmit="return confirm('Are you sure you want to return this book?');">
                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                    <button type="submit" class="return-btn">Return</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">You have no borrowed books.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <a href="user_dashboard.php" class="back-to-dashboard-btn">Return to Dashboard</a>
    </div>
</body>
</html>
