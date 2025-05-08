<?php
session_start();
include '../database/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$books = []; // ✅ Initialize the books array

// Fetch all books
$query = "SELECT * FROM books";
$stmt = $conn->prepare($query);

// Error handling for prepare
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }

    $stmt->close();
} else {
    die("Prepare failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Books | The Shiv Library</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #000;
      color: white;
      padding: 20px;
    }

    .dashboard {
      background: rgba(255, 255, 255, 0.07);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      padding: 30px;
      max-width: 1200px;
      margin: 40px auto;
      box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
    }

    h2 {
      color: #ffcc00;
      text-align: center;
      margin-bottom: 30px;
    }

    .top-buttons {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    a.button {
      padding: 10px 20px;
      background-color: #ffcc00;
      color: black;
      text-decoration: none;
      border-radius: 8px;
      font-weight: bold;
      transition: background-color 0.3s;
    }

    a.button:hover {
      background-color: #e6b800;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 12px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      text-align: left;
    }

    th {
      background-color: rgba(255, 255, 255, 0.1);
      color: #ffcc00;
    }

    tr:hover {
      background-color: rgba(255, 255, 255, 0.05);
    }

    img.cover {
      width: 60px;
      height: auto;
      border-radius: 8px;
    }

    .actions a {
      margin-right: 10px;
    }
  </style>
</head>
<body>
  <div class="dashboard">
    <div class="top-buttons">
      <a class="button" href="add_book.php">➕ Add New Book</a>
      <a class="button" href="admin_dashboard.php">🏠 Back to Admin Dashboard</a>
    </div>

    <h2>📚 Manage Books</h2>

    <table>
      <tr>
        <th>Cover</th>
        <th>Title</th>
        <th>Author</th>
        <th>Genre</th>
        <th>Published Year</th>
        <th>Available</th>
        <th>Total</th>
        <th>Actions</th>
      </tr>
      <?php if (!empty($books)): ?>
        <?php foreach ($books as $book): ?>
          <tr>
            <td>
              <img src="<?= !empty($book['cover_image']) ? $book['cover_image'] : '/TheShivLibrary/assets/images/books/default_book.png' ?>" class="cover">
            </td>
            <td><?= htmlspecialchars($book['title']) ?></td>
            <td><?= htmlspecialchars($book['author']) ?></td>
            <td><?= htmlspecialchars($book['genre']) ?></td>
            <td><?= $book['published_year'] ?></td>
            <td><?= $book['available_copies'] ?></td>
            <td><?= $book['total_copies'] ?></td>
            <td class="actions">
              <a class="button" href="edit_book.php?id=<?= $book['id'] ?>">✏️ Edit</a>
              <a class="button" href="delete_book.php?id=<?= $book['id'] ?>" onclick="return confirm('Are you sure you want to delete this book?');">❌ Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8" style="text-align:center;">No books found in the library.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</body>
</html>
