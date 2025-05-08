<?php
include '../database/db_connect.php'; // this sets up the $conn for MySQLi
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle book reservation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_SESSION['user_id'];

    // Check for duplicate reservation
    $check_sql = "SELECT * FROM reservations WHERE user_id = ? AND book_id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "You have already reserved this book.";
    } else {
        // Insert new reservation
        $insert_sql = "INSERT INTO reservations (user_id, book_id, status) VALUES (?, ?, 'Pending')";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ii", $user_id, $book_id);
        if ($stmt->execute()) {
            $message = "Book reserved successfully!";
        } else {
            $message = "Error reserving the book. Please try again.";
        }
    }
}

// Fetch available books
$books_sql = "SELECT * FROM books WHERE available = 1";
$books_result = $conn->query($books_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reserve Books</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <h1>Reserve a Book</h1>

    <?php if (isset($message)) echo "<p>$message</p>"; ?>

    <form method="POST">
        <label for="book_id">Select a Book:</label>
        <select name="book_id" required>
            <?php while ($book = $books_result->fetch_assoc()) { ?>
                <option value="<?php echo $book['id']; ?>"><?php echo $book['title']; ?></option>
            <?php } ?>
        </select>
        <button type="submit">Reserve</button>
    </form>
</body>
</html>
