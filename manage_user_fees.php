<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

if (!isset($_GET['user_id'])) {
    echo "No user selected.";
    exit();
}

$user_id = $_GET['user_id'];

$query = "SELECT u.id AS user_id, u.name, u.profile_photo, f.screenshot, f.payment_date, f.status, f.id AS fee_id 
          FROM users u 
          LEFT JOIN (
              SELECT user_id, MAX(payment_date) AS latest_payment
              FROM fee_payments
              GROUP BY user_id
          ) latest ON u.id = latest.user_id
          LEFT JOIN fee_payments f ON u.id = f.user_id AND f.payment_date = latest.latest_payment
          WHERE u.id = $user_id
          ORDER BY u.name ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage User Fees | The Shiv Library</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #121212;
            color: white;
        }
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #1e1e1e;
            color: #0f0;
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            background-color: rgba(255, 255, 255, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #444;
        }
        th {
            background-color: #333;
        }
        tr:nth-child(even) {
            background-color: #222;
        }
        .status {
            font-size: 14px;
        }
        .approved { color: #00cc88; }
        .pending { color: #ffcc00; }
        .rejected { color: #ff6666; }
        .view-screenshot {
            color: #00cc88;
            text-decoration: none;
        }
        .view-screenshot:hover { text-decoration: underline; }
        .btn-approve, .btn-reject, .btn-history, .btn-view {
            padding: 6px 12px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            margin: 5px;
            display: inline-block;
        }
        .btn-approve { background-color: #4CAF50; }
        .btn-approve:hover { background-color: #45a049; }
        .btn-reject { background-color: #f44336; }
        .btn-reject:hover { background-color: #da190b; }
        .btn-history { background-color: #3498db; }
        .btn-history:hover { background-color: #2980b9; }
        .btn-view { background-color: #8e44ad; }
        .btn-view:hover { background-color: #732d91; }
        .payment-history { display: none; }
    </style>
</head>
<body>

<h2>Manage User Fees for <?= htmlspecialchars($user['name']) ?></h2>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert">
        <?php
        if ($_GET['msg'] == 'success') {
            echo "Payment has been successfully " . htmlspecialchars($_GET['action']) . ".";
        } elseif ($_GET['msg'] == 'notfound') {
            echo "No pending payment found for this user.";
        } else {
            echo "Invalid request.";
        }
        ?>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>User Name</th>
            <th>Profile Photo</th>
            <th>Payment Screenshot</th>
            <th>Payment Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><img src="../assets/images/<?= htmlspecialchars(basename($user['profile_photo'])) ?>" width="50" height="50" style="border-radius: 50%;"></td>
            <td>
                <?php if ($user['screenshot']): ?>
                    <a href="../<?= htmlspecialchars($user['screenshot']) ?>" target="_blank" class="view-screenshot">View Screenshot</a>
                <?php else: ?>
                    No Screenshot
                <?php endif; ?>
            </td>
            <td><?= $user['payment_date'] ? date('d M Y', strtotime($user['payment_date'])) : 'N/A' ?></td>
            <td class="status <?= $user['status'] ?>"><?= $user['status'] ? ucfirst($user['status']) : 'N/A' ?></td>
            <td>
                <?php if ($user['status'] == 'pending'): ?>
                    <a href="verify_fee.php?user_id=<?= $user['user_id'] ?>&action=approve" class="btn-approve">Approve</a>
                    <a href="verify_fee.php?user_id=<?= $user['user_id'] ?>&action=reject" class="btn-reject">Reject</a>
                <?php else: ?>
                    <span>Action Taken</span>
                <?php endif; ?>
                <br>
                <a href="verify_fee.php?user_id=<?= $user['user_id'] ?>&action=view_history" class="btn-view">Verify Fee and See Payment History</a>
            </td>
        </tr>
        <tr id="payment-history-<?= $user['user_id'] ?>" class="payment-history">
            <td colspan="6" id="history-content-<?= $user['user_id'] ?>">Loading...</td>
        </tr>
    </tbody>
</table>

<script>
function togglePaymentHistory(userId) {
    const row = document.getElementById('payment-history-' + userId);
    const content = document.getElementById('history-content-' + userId);

    document.querySelectorAll('.payment-history').forEach(r => r.style.display = 'none');

    if (row.style.display === 'table-row') {
        row.style.display = 'none';
    } else {
        row.style.display = 'table-row';
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_fee_history.php?user_id=' + userId, true);
        xhr.onload = function () {
            content.innerHTML = xhr.status === 200 ? xhr.responseText : "Failed to load history.";
        };
        xhr.send();
    }
}
</script>

</body>
</html>
