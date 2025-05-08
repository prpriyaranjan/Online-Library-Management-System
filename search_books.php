<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/user_login.php");
    exit();
}

$books = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search_term'])) {
    $search = '%' . $_POST['search_term'] . '%';
    $stmt = $conn->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR genre LIKE ?");
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Books | The Shiv Library</title>
    <style>
        body {
            background-color: #000;
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            margin: 0;
            padding: 20px;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: #ffcc00;
            border-radius: 10px;
        }

        .glass {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }

        h2 {
            text-align: center;
            color: #ffcc00;
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 10px;
            width: 60%;
            border: none;
            border-radius: 10px;
            margin-right: 10px;
            font-size: 16px;
        }

        button {
            padding: 10px 20px;
            background-color: #ffcc00;
            border: none;
            color: black;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
        }

        .book-card {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.08);
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 15px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
        }

        .book-card:hover {
            background-color: rgba(255, 255, 255, 0.12);
            transform: scale(1.02);
            transition: all 0.3s ease;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .book-card img {
            width: 70px;
            height: auto;
            border-radius: 10px;
            margin-right: 20px;
        }

        .book-info {
            flex-grow: 1;
        }

        .book-info h4 {
            margin: 0;
            color: #ffcc00;
        }

        .borrow-btn {
            background-color: #ffcc00;
            color: black;
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .borrow-btn:hover {
            background-color: #e6b800;
        }

        .back-button {
            display: inline-block;
            margin: 20px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #2980b9;
        }

        .no-results {
            text-align: center;
            color: #ccc;
            font-size: 18px;
            margin-top: 30px;
        }
        .status-message {
    background-color: #1abc9c;
    color: white;
    font-weight: bold;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 0 10px rgba(26, 188, 156, 0.5);
    animation: fadeSlide 0.5s ease;
}

@keyframes fadeSlide {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

    </style>
</head>
<body>
    <div class="glass">
    <?php if (isset($_SESSION['message'])): ?>
    <div class="status-message">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

        <a href="user_dashboard.php" class="back-button">← Back to Dashboard</a>
        <h2>Search Books</h2>
        <form method="POST" action="">
            <input type="text" name="search_term" placeholder="Search by title, author, or genre" required>
            <button type="submit">Search</button>
        </form>

        <?php if (!empty($books)): ?>
            <?php foreach ($books as $book): ?>
                <div class="book-card">
                    <img src="<?= $book['cover_image'] ?? '/TheShivLibrary/assets/images/books/default_book.png' ?>" alt="Book Cover">
                    <div class="book-info">
                        <h4><?= htmlspecialchars($book['title']) ?></h4>
                        <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?> | <strong>Genre:</strong> <?= htmlspecialchars($book['genre']) ?></p>
                        <p><strong>Available Copies:</strong> <?= $book['available_copies'] ?> / <?= $book['total_copies'] ?></p>
                    </div>
                    <form action="request_borrow.php" method="POST">
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                        <button type="submit" class="borrow-btn">Request Borrow</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="no-results">
                📚 Oops! Couldn't find anything. Try a different keyword.
            </div>
        <?php endif; ?>
    </div>
    <script>
    setTimeout(() => {
        const msg = document.querySelector('.status-message');
        if (msg) msg.style.display = 'none';
    }, 4000);
</script>

</body>
</html>
