<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_books.php");
    exit();
}

$book_id = $_GET['id'];
$message = "";

// Fetch current book data
$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    $message = "Book not found!";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];

    $book_cover = $book['cover_image'];

    if (!empty($_FILES["book_cover"]["name"])) {
        $target_dir = "../assets/images/books/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES["book_cover"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["book_cover"]["tmp_name"], $target_file)) {
            $book_cover = "/TheShivLibrary/assets/images/books/" . $file_name;
        }
    }

    $update = $conn->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, category = ?, quantity = ?, cover_image = ? WHERE id = ?");
    $update->bind_param("ssssisi", $title, $author, $isbn, $category, $quantity, $book_cover, $book_id);

    if ($update->execute()) {
        $message = "Book updated successfully!";

        // Refresh data
        $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
    } else {
        $message = "Failed to update book.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book | The Shiv Library</title>
    <style>
        body {
            background-color: #000;
            font-family: Arial, sans-serif;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .glass-box {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            width: 450px;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
        }

        .glass-box h2 {
            text-align: center;
            color: #ffcc00;
        }

        form input, form textarea, form select {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            border-radius: 8px;
        }

        form input::placeholder {
            color: #ccc;
        }

        form button {
            width: 100%;
            margin-top: 20px;
            padding: 10px;
            background-color: #ffcc00;
            color: black;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .message {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            color: #00ffcc;
        }

        .book-cover {
            display: block;
            width: 100px;
            margin: 10px auto;
            border-radius: 8px;
        }

        .back-link {
            text-align: center;
            margin-top: 10px;
        }

        .back-link a {
            color: #ffcc00;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="glass-box">
    <h2>Edit Book</h2>

    <?php if (!empty($message)) { ?>
        <p class="message"><?= $message; ?></p>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" placeholder="Book Title" required>
        <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>" placeholder="Author" required>
        <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>" placeholder="ISBN" required>
        <input type="text" name="category" value="<?= htmlspecialchars($book['category']) ?>" placeholder="Category" required>
        <input type="number" name="quantity" value="<?= htmlspecialchars($book['quantity']) ?>" placeholder="Quantity" required>

        <img src="<?= $book['cover_image'] ?>" alt="Current Cover" class="book-cover">

        <input type="file" name="book_cover" accept="image/*">
        <button type="submit">Update Book</button>
    </form>

    <div class="back-link">
        <a href="manage_books.php">← Back to Manage Books</a>
    </div>
</div>
</body>
</html>
