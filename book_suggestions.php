<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch genres from borrowed books
$genre_sql = "
    SELECT DISTINCT books.genre 
    FROM borrowings 
    JOIN books ON borrowings.book_id = books.id 
    WHERE borrowings.user_id = ? AND borrowings.status = 'borrowed'
";
$stmt = $conn->prepare($genre_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$genres = [];
while ($row = $result->fetch_assoc()) {
    $genres[] = $row['genre'];
}

// Fetch genres from borrow requests (approved and pending)
$request_sql = "
    SELECT DISTINCT books.genre 
    FROM borrow_requests 
    JOIN books ON borrow_requests.book_id = books.id 
    WHERE borrow_requests.user_id = ? AND (borrow_requests.status = 'approved' OR borrow_requests.status = 'pending')
";
$stmt = $conn->prepare($request_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$request_result = $stmt->get_result();

while ($row = $request_result->fetch_assoc()) {
    // Avoid duplicate genres
    if (!in_array($row['genre'], $genres)) {
        $genres[] = $row['genre'];
    }
}

// Suggest books from the same genres if available
if (!empty($genres)) {
    $placeholders = implode(',', array_fill(0, count($genres), '?'));
    $types = str_repeat('s', count($genres)); // 's' for string type
    $sql = "
        SELECT id, title, author, genre, cover_image 
        FROM books 
        WHERE genre IN ($placeholders)
        ORDER BY RAND() 
        LIMIT 5
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$genres);
    $stmt->execute();
    $suggestions = $stmt->get_result();
} else {
    // If no genres, suggest top-rated books
    $suggestions = $conn->query("
        SELECT id, title, author, genre, cover_image 
        FROM books 
        ORDER BY rating DESC 
        LIMIT 5
    ");
}

// Check if the query succeeded
if (!$suggestions || $suggestions->num_rows === 0) {
    $no_recommendations = true;
} else {
    $no_recommendations = false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Recommendations</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
        color: white;
        padding-top: 80px;
        overflow-x: hidden;
    }

    /* Fixed header */
    .fixed-header {
        position: fixed;
        top: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.85);
        padding: 20px 0;
        text-align: center;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    .fixed-header h1 {
        margin: 0;
        font-size: 34px;
        letter-spacing: 1px;
        background: linear-gradient(45deg, #00c6ff, #0072ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: bold;
    }

    .container {
        padding: 30px 20px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
    }

    .card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 15px;
        width: 300px;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        backdrop-filter: blur(12px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        color: #fff;
        transition: all 0.3s ease-in-out;
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
    }

    /* Sequential animation */
    .card:nth-child(1) { animation-delay: 0.1s; }
    .card:nth-child(2) { animation-delay: 0.2s; }
    .card:nth-child(3) { animation-delay: 0.3s; }
    .card:nth-child(4) { animation-delay: 0.4s; }
    .card:nth-child(5) { animation-delay: 0.5s; }

    .card:hover {
        transform: scale(1.05) rotateZ(0.5deg);
        box-shadow: 0 8px 32px rgba(0, 255, 255, 0.25);
    }

    .card img {
        width: 80px;
        height: 120px;
        border-radius: 8px;
        margin-left: 12px;
        object-fit: cover;
    }

    .description {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .card h2 {
        margin: 0;
        font-size: 16px;
        font-weight: bold;
        color: #fff;
    }

    .card p {
        margin: 0;
        font-size: 13px;
        color: #ddd;
    }

    .card button {
        margin-top: 8px;
        background: linear-gradient(135deg, #00c6ff, #0072ff);
        border: none;
        padding: 6px 10px;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        align-self: flex-start;
        transition: background 0.3s ease;
    }

    .card button:hover {
        background: linear-gradient(135deg, #0072ff, #00c6ff);
    }

    /* Entrance animation */
    @keyframes fadeInUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

</head>
<body>
    <div class="fixed-header">
        <h1>📚 Book Recommendations</h1>
    </div>
    <div class="container">
        <?php if (!$no_recommendations): ?>
            <?php while ($book = $suggestions->fetch_assoc()): ?>
                <div class="card">
                    <div class="description">
                        <h2><?php echo htmlspecialchars($book['title']); ?></h2>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                        <p><strong>Genre:</strong> <?php echo htmlspecialchars($book['genre']); ?></p>
                        <a href="book_details.php?id=<?php echo $book['id']; ?>">
                            <button>View Details</button>
                        </a>
                    </div>
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="<?= !empty($book['cover_image']) ? $book['cover_image'] : '/TheShivLibrary/assets/images/books/default_book.png' ?>" class="cover">
                    <?php else: ?>
                        <img src="../assets/images/default-cover.jpg" alt="Book Cover">
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center;">No recommendations available.</p>
        <?php endif; ?>
    </div>
</body>
</html>
