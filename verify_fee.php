<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

$user_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Confirm or Decline or Delete payment
    if ((isset($_POST['confirm_fee']) || isset($_POST['decline_fee'])) && isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
    } elseif (isset($_POST['delete_payment']) && isset($_POST['payment_id']) && is_numeric($_POST['payment_id'])) {
        $payment_id = $_POST['payment_id'];
        $stmt = $conn->prepare("SELECT user_id FROM fee_payments WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $user_id = $row['user_id'];
            $del_stmt = $conn->prepare("DELETE FROM fee_payments WHERE id = ?");
            $del_stmt->bind_param("i", $payment_id);
            $del_stmt->execute();
            header("Location: verify_fee.php?user_id=$user_id&success=1");
            exit();
        } else {
            echo "Invalid payment ID.";
            exit();
        }
    } else {
        echo "Invalid POST data.";
        exit();
    }
} elseif (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} else {
    echo "Invalid or missing user ID.";
    exit();
}

$success = isset($_GET['success']) && $_GET['success'] == 1;

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit();
}

// Get latest fee payment
$fee_stmt = $conn->prepare("SELECT * FROM fee_payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$fee_stmt->bind_param("i", $user_id);
$fee_stmt->execute();
$fee_result = $fee_stmt->get_result();
$fee = $fee_result->fetch_assoc();

// Fee Calculation
$join_date = $user['created_at'];
$today = date('Y-m-d');
$due_date = date('Y-m-d', strtotime($join_date . ' +1 month'));
$payment_date = $fee['payment_date'] ?? null;

$fine = 0;
$status = 'Pending';

if ($payment_date) {
    if ($payment_date > $due_date) {
        $days_late = ceil((strtotime($payment_date) - strtotime($due_date)) / (60 * 60 * 24));
        $fine = $days_late * 10;
    } else {
        $status = 'Paid';
    }
} else {
    if ($today > $due_date) {
        $days_late = ceil((strtotime($today) - strtotime($due_date)) / (60 * 60 * 24));
        $fine = $days_late * 10;
    }
}

// Handle Confirm or Decline action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['confirm_fee']) || isset($_POST['decline_fee']))) {
    $admin_id = $_SESSION['admin_id'];
    $fee_id = $fee['id'] ?? null;

    if ($fee_id) {
        if (isset($_POST['confirm_fee'])) {
            $status = "Confirmed";
            $msg = "Dear {$user['name']}, your monthly library fee has been successfully confirmed on " . date('d M Y') . ". Thank you!";
        } else {
            $status = "Declined";
            $msg = "Dear {$user['name']}, your monthly fee payment was declined due to issues with the screenshot. Please re-upload the proof.";
        }

        // Update fee status
        $stmt = $conn->prepare("UPDATE fee_payments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $fee_id);
        $stmt->execute();

        // Send message
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, sender_role, message, created_at, status) VALUES (?, ?, 'admin', ?, NOW(), 'unread')");
        $stmt->bind_param("iis", $admin_id, $user_id, $msg);
        $stmt->execute();

        // Send email
        mail($user['email'], "Fee Payment Status - The Shiv Library", $msg, "From: no-reply@shivlibrary.com\r\nContent-Type: text/plain; charset=UTF-8\r\n");

        header("Location: verify_fee.php?user_id=$user_id&success=1");
        exit();
    } else {
        echo "Fee record not found.";
        exit();
    }
}

// Fetch payment history
$history_stmt = $conn->prepare("SELECT * FROM fee_payments WHERE user_id = ? ORDER BY created_at DESC");
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['name']) ?> - Fee Status | Admin Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
            color: white;
            padding: 20px;
        }
        .container {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(8px);
            max-width: 900px;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: rgba(255, 255, 255, 0.07);
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #444;
        }
        .user-photo {
            width: 100px;
            border-radius: 50%;
            border: 2px solid white;
        }
        .screenshot {
            width: 100px;
            border-radius: 5px;
        }
        .confirm-box button {
            padding: 10px 20px;
            margin: 10px 10px 0 0;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: white;
        }
        .confirm { background-color: #28a745; }
        .decline { background-color: #dc3545; }
        .delete-button {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .success {
            background: #4caf50;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            color: #fff;
        }
        .back a {
            color: #fff;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .back a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">

    <?php if ($success): ?>
        <div class="success">✅ Payment status updated, message/email sent to user.</div>
    <?php endif; ?>

    <h2>Fee Status: <?= htmlspecialchars($user['name']) ?></h2>

    <div class="user-info">
        <p><img class="user-photo" src="/TheShivLibrary/assets/images/<?= htmlspecialchars(basename($user['profile_photo'])) ?>" alt="Profile"></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
    </div>

    <div class="fee-info">
        <table>
            <tr>
                <th>Join Date</th>
                <th>Due Date</th>
                <th>Payment Date</th>
                <th>Fine (₹)</th>
                <th>Status</th>
                <th>Screenshot</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars($join_date) ?></td>
                <td><?= htmlspecialchars($due_date) ?></td>
                <td><?= $payment_date ? htmlspecialchars($payment_date) : "Not Paid" ?></td>
                <td><?= $fine ?></td>
                <td><?= isset($fee['status']) ? htmlspecialchars($fee['status']) : $status ?></td>
                <td>
                    <?php if (!empty($fee['payment_screenshot'])): ?>
                        <a href="/TheShivLibrary/assets/images/<?= htmlspecialchars($fee['payment_screenshot']) ?>" target="_blank">
                            <img class="screenshot" src="/TheShivLibrary/assets/images/<?= htmlspecialchars($fee['payment_screenshot']) ?>" alt="Screenshot">
                        </a>
                    <?php else: ?>
                        Not Uploaded
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="confirm-box">
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">
            <?php if (!$fee['status'] || $fee['status'] === 'Pending'): ?>
                <button class="confirm" name="confirm_fee" type="submit">✅ Confirm</button>
                <button class="decline" name="decline_fee" type="submit">❌ Decline</button>
            <?php endif; ?>
        </form>
    </div>

    <h3>Payment History</h3>
    <table>
        <tr>
            <th>Payment Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($history = $history_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($history['payment_date'] ?? '—') ?></td>
                <td>₹<?= htmlspecialchars($history['amount']) ?></td>
                <td><?= htmlspecialchars($history['status']) ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this payment?');">
                        <input type="hidden" name="payment_id" value="<?= $history['id'] ?>">
                        <button class="delete-button" name="delete_payment" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <div class="back">
        <a href="manage_users.php">⬅ Back to Manage Users</a>
    </div>
</div>
</body>
</html>
