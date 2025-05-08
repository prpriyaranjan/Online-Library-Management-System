<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT name, email, profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch borrowed books (not yet returned)
$borrowedBooksQuery = $conn->prepare("
    SELECT b.title, br.borrow_date, br.due_date 
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ? AND br.return_date IS NULL
");
$borrowedBooksQuery->bind_param("i", $user_id);
$borrowedBooksQuery->execute();
$borrowedBooksResult = $borrowedBooksQuery->get_result();
$borrowedBooks = $borrowedBooksResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard | The Shiv Library</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: black;
            color: white;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.1);
            height: 100vh;
            padding-top: 20px;
            position: fixed;
            left: 0;
            backdrop-filter: blur(10px);
            box-shadow: 2px 0px 10px rgba(255, 255, 255, 0.2);
        }

        .sidebar h2 {
            color: #ffcc00;
            text-align: center;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px;
            margin: 5px 10px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }

        .sidebar a.logout {
            color: red;
        }

        .dashboard-container {
            margin-left: 260px;
            padding: 40px 20px;
            width: calc(100% - 260px);
            text-align: center;
        }

        .admin-info {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .admin-info img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 15px;
            border: 2px solid #ffcc00;
            object-fit: cover;
        }

        .stat-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.2);
            text-align: center;
            transition: transform 0.3s;
            width: 200px;
            height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-box:hover {
            transform: scale(1.05);
        }

        .stat-box h3 {
            font-size: 16px;
            color: #ffcc00;
        }

        .stat-box p {
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>User Panel</h2>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="profile.php">My Profile</a>
        <a href="search_books.php">Search Books</a>
        <a href="borrowed_books.php">My Borrowed Books</a>
        <a href="user_fee.php">💰 Fee Payment</a> <!-- ✅ New Fee Section Link -->
        <a href="../books/book_suggestions.php">📘 Book Recommendations</a>
        <a href="../chat.php">💬 Messages</a>
        <a class="logout" href="../logout.php">Logout</a>
    </div>

    <div class="dashboard-container">
        <!-- User Info -->
        <div class="admin-info">
            <img src="../assets/images/<?php echo $user['profile_photo']; ?>" alt="User Photo">
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        </div>

        <!-- Stats -->
        <div class="stat-container">
            <div class="stat-box">
                <h3>My Email</h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="stat-box">
                <h3>Borrowed Books</h3>
                <p><?php echo count($borrowedBooks); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
