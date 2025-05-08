<?php
include '../database/db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$books = $pdo->query("SELECT id, title FROM books")->fetchAll();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_id']) && isset($_POST['rating']) && isset($_POST['review'])) {
    $book_id = $_POST['book_id'];
    $rating = $_POST['rating'];
    $review = $_POST['review'];
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, book_id, rating, review) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $book_id, $rating, $review]);
    echo "Review Submitted Successfully!";
}
$reviews = $pdo->query("SELECT books.title, reviews.rating, reviews.review FROM reviews JOIN books ON reviews.book_id = books.id")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rate & Review Books</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <h1>Rate and Review Books</h1>
    <form method="POST">
        <label for="book">Select Book:</label>
        <select name="book_id" required>
            <?php foreach ($books as $book) { ?>
                <option value="<?php echo $book['id']; ?>"><?php echo $book['title']; ?></option>
            <?php } ?>
        </select>
        <label for="rating">Rating (1-5):</label>
        <input type="number" name="rating" min="1" max="5" required>
        <label for="review">Review:</label>
        <textarea name="review" required></textarea>
        <button type="submit">Submit Review</button>
    </form>
    <h2>Recent Reviews</h2>
    <table>
        <tr>
            <th>Book</th>
            <th>Rating</th>
            <th>Review</th>
        </tr>
        <?php foreach ($reviews as $review) { ?>
            <tr>
                <td><?php echo $review['title']; ?></td>
                <td><?php echo $review['rating']; ?>/5</td>
                <td><?php echo $review['review']; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
