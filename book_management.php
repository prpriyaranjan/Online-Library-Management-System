<?php
// book_management.php
include '../database/db_connect.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Books</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f7f7f7;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, select, button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background: #333;
            color: white;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
        img {
            max-width: 60px;
            height: auto;
        }
        .action a {
            color: red;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Manage Books</h1>
    <form action="add_book.php" method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Book Title" required>
        <input type="text" name="author" placeholder="Author" required>
        <input type="text" name="isbn" placeholder="ISBN">
        <input type="text" name="genre" placeholder="Genre" required>
        <input type="text" name="category" placeholder="Category">
        <input type="number" name="published_year" placeholder="Published Year" required>
        <input type="number" name="total_copies" placeholder="Total Copies" required>
        <input type="file" name="cover_image" required>
        <button type="submit">Add Book</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Cover</th>
            <th>Title</th>
            <th>Author</th>
            <th>Genre</th>
            <th>Copies</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = mysqli_query($conn, "SELECT * FROM books");
        while ($book = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$book['id']}</td>";
            echo "<td><img src='../assets/images/books/{$book['cover_image']}' alt='Book Cover'></td>";
            echo "<td>{$book['title']}</td>";
            echo "<td>{$book['author']}</td>";
            echo "<td>{$book['genre']}</td>";
            echo "<td>{$book['available_copies']} / {$book['total_copies']}</td>";
            echo "<td class='action'><a href='delete_book.php?id={$book['id']}'>Delete</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>