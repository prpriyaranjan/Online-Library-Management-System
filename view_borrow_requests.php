<?php
session_start();
include '../database/db_connect.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /TheShivLibrary/auth/admin_login.php");
    exit();
}

// Fetch pending borrow requests
$query = "SELECT br.id, br.user_id, b.title AS book_title, br.status, br.request_date, b.id AS book_id
          FROM borrow_requests br
          JOIN books b ON br.book_id = b.id
          WHERE br.status = 'pending'";

$result = $conn->query($query);
$requests = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

// Approve request via GET
if (isset($_GET['approve_id'])) {
    $requestId = $_GET['approve_id'];

    // Prepare statement for retrieving user_id and book_id
    $stmt = $conn->prepare("SELECT user_id, book_id FROM borrow_requests WHERE id = ?");
    if (!$stmt) {
        // Print error message if the statement fails to prepare
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $request = $result->fetch_assoc();

        // Step 1: Get the book's available copies
        $stmt2 = $conn->prepare("SELECT available_copies FROM books WHERE id = ?");
        if (!$stmt2) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt2->bind_param("i", $request['book_id']);
        $stmt2->execute();
        $stmt2->bind_result($available_copies);
        $stmt2->fetch();
        $stmt2->close();

        // Step 2: Check if there's available stock to approve the request
        if ($available_copies > 0) {
            // Step 3: Insert into borrowings table
            $stmt3 = $conn->prepare("INSERT INTO borrowings (user_id, book_id, borrow_date, due_date, return_date)
                                     VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), NULL)");
            if (!$stmt3) {
                die("Error preparing statement: " . $conn->error);
            }
            $stmt3->bind_param("ii", $request['user_id'], $request['book_id']);
            $stmt3->execute();
            $stmt3->close();

            // Step 4: Update borrow request status to 'approved'
            $stmt4 = $conn->prepare("UPDATE borrow_requests SET status = 'approved' WHERE id = ?");
            if (!$stmt4) {
                die("Error preparing statement: " . $conn->error);
            }
            $stmt4->bind_param("i", $requestId);
            $stmt4->execute();
            $stmt4->close();

            // Step 5: Decrease the book's available copies by 1
            $stmt5 = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
            if (!$stmt5) {
                die("Error preparing statement: " . $conn->error);
            }
            $stmt5->bind_param("i", $request['book_id']);
            $stmt5->execute();
            $stmt5->close();

            $_SESSION['message'] = "Borrow request approved and book availability updated.";
        } else {
            $_SESSION['message'] = "Cannot approve request: Book is out of stock.";
        }
    } else {
        $_SESSION['message'] = "Request not found.";
    }

    // Redirect to the requests page
    header("Location: view_borrow_requests.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Borrow Requests | Admin Panel | The Shiv Library</title>
    <style>
        /* Table Styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-top: 40px;
            font-size: 36px;
        }

        h2 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        table th, table td {
            padding: 12px 20px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #ffcc00;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        table td {
            font-size: 16px;
            color: #555;
        }

        table td a {
            color: #28a745;
            text-decoration: none;
        }

        table td a:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px 0;
            background-color: #333;
            border-top: 2px solid #ffcc00;
        }

        footer a {
            text-decoration: none;
            color: #ffcc00;
            font-weight: bold;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        footer a:hover {
            background-color: #ffcc00;
            color: #333;
        }

    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>View Borrow Requests</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <p style="color: green; text-align: center;"><?php echo $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <h2>Pending Borrow Requests</h2>
        <table>
            <tr>
                <th>Book Title</th>
                <th>User ID</th>
                <th>Request Date</th>
                <th>Action</th>
            </tr>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['book_title']); ?></td>
                    <td><?php echo htmlspecialchars($request['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($request['request_date']); ?></td>
                    <td>
                        <a href="view_borrow_requests.php?approve_id=<?php echo $request['id']; ?>" onclick="return confirm('Are you sure you want to approve this request?')">Approve</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <footer>
        <a href="admin_dashboard.php">Back to Admin Dashboard</a>
    </footer>
</body>
</html>
