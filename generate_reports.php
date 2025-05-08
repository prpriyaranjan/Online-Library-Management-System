<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Generate report data using MySQLi
$usersCount = $conn->query("SELECT COUNT(*) AS total_users FROM users")->fetch_assoc();
$booksCount = $conn->query("SELECT COUNT(*) AS total_books FROM books")->fetch_assoc();
$borrowedBooksCount = $conn->query("SELECT COUNT(*) AS total_borrowed FROM borrowings WHERE return_date IS NULL")->fetch_assoc();
$overdueBooksCount = $conn->query("SELECT COUNT(*) AS total_overdue FROM borrowings WHERE due_date < NOW() AND return_date IS NULL")->fetch_assoc();
$unpaidFinesCount = $conn->query("SELECT COUNT(*) AS total_fines FROM fines WHERE status = 'Unpaid'")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Reports</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
    <style>
        body {
            background-color: #121212;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            padding: 30px 0;
            font-size: 36px;
            background: linear-gradient(145deg, #222, #1a1a1a);
            box-shadow: 0 4px 8px rgba(0,0,0,0.4);
            margin-bottom: 30px;
        }

        table {
            width: 80%;
            margin: 0 auto 40px auto;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
        }

        th, td {
            padding: 20px;
            text-align: left;
            border-bottom: 1px solid #333;
            font-size: 18px;
        }

        th {
            background-color: #1e1e1e;
        }

        tr:nth-child(even) {
            background-color: #1c1c1c;
        }

        tr:hover {
            background-color: #2c2c2c;
        }
    </style>
</head>
<body>

    <h1>Library Reports</h1>

    <table>
        <tr>
            <th>Report Type</th>
            <th>Count</th>
        </tr>
        <tr>
            <td>Total Users</td>
            <td><?= $usersCount['total_users'] ?></td>
        </tr>
        <tr>
            <td>Total Books</td>
            <td><?= $booksCount['total_books'] ?></td>
        </tr>
        <tr>
            <td>Total Borrowed Books</td>
            <td><?= $borrowedBooksCount['total_borrowed'] ?></td>
        </tr>
        <tr>
            <td>Total Overdue Books</td>
            <td><?= $overdueBooksCount['total_overdue'] ?></td>
        </tr>
        <tr>
            <td>Total Unpaid Fines</td>
            <td><?= $unpaidFinesCount['total_fines'] ?></td>
        </tr>
    </table>

</body>
</html>
