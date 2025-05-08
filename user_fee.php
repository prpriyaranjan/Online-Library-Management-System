<?php
session_start();
include '../database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch user data
$query = "SELECT name, profile_photo, joining_date FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) {
    die("User not found.");
}

// Calculate fee due info
$joining_date = new DateTime($user['joining_date']);
$today = new DateTime();
$interval = $joining_date->diff($today);
$months_passed = $interval->m + ($interval->y * 12);

$next_due_date = clone $joining_date;
$next_due_date->modify('+' . $months_passed . ' months');
if ($today > $next_due_date) {
    $next_due_date->modify('+1 month');
}
$days_remaining = $today->diff($next_due_date)->format('%r%a');

$fine_per_day = 10;
$fine = $days_remaining < 0 ? abs($days_remaining) * $fine_per_day : 0;

// Get current cycle month (e.g., April 2025)
$current_fee_month = $next_due_date->format("Y-m");

// Check latest payment
$payment_status = "Not paid yet";
$screenshot_path = "";

$check_payment_query = "
    SELECT screenshot_path, uploaded_at 
    FROM fee_payments 
    WHERE user_id = ? 
    ORDER BY uploaded_at DESC 
    LIMIT 1
";
$stmt_screenshot = $conn->prepare($check_payment_query);
if ($stmt_screenshot) {
    $stmt_screenshot->bind_param("i", $user_id);
    $stmt_screenshot->execute();
    $result_screenshot = $stmt_screenshot->get_result();
    if ($result_screenshot && $result_screenshot->num_rows > 0) {
        $row_screenshot = $result_screenshot->fetch_assoc();
        $screenshot_path = $row_screenshot['screenshot_path'];

        $uploaded_month = (new DateTime($row_screenshot['uploaded_at']))->format("Y-m");
        if ($uploaded_month === $current_fee_month) {
            $payment_status = "Paid";
        }
    }
    $stmt_screenshot->close();
}

// Remove folder prefix for profile photo
$profile_photo = basename($user['profile_photo']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Payment | The Shiv Library</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #0d0d0d, #1a1a1a);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            animation: fadeIn 1s ease-in-out;
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            animation: slideIn 1s ease-out;
        }
        .user-info img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #ffcc00;
            margin-right: 20px;
            box-shadow: 0 0 20px #ffcc00;
        }
        .user-info h1 {
            font-size: 30px;
            color: #ffcc00;
        }
        .fee-box {
            max-width: 700px;
            margin: auto;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
            animation: glowIn 1.5s ease-in-out;
        }
        .fee-box h2 { color: #00ffcc; font-size: 26px; }
        .fee-box p { font-size: 18px; margin: 15px 0; }
        .upload-box { margin-top: 30px; }
        input[type="file"] {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #333;
            background: #222;
            color: white;
        }
        button {
            padding: 12px 25px;
            margin-top: 20px;
            background-color: #00cc88;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }
        button:hover {
            background-color: #00aa77;
            transform: scale(1.05);
        }
        .status {
            margin: 15px 0;
            font-size: 16px;
            color: #00ffcc;
        }
        .error { color: #ff6666; }
        .screenshot-preview {
            margin-top: 20px;
        }
        .screenshot-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 12px;
            border: 2px solid #00cc88;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.3);
        }
        .notice {
            font-size: 18px;
            font-weight: bold;
            margin: 25px 0;
            color: <?= $payment_status === 'Paid' ? '#00ffcc' : '#ff6666'; ?>;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { transform: translateX(-100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes glowIn {
            from { box-shadow: 0 0 0 rgba(255, 255, 255, 0); }
            to { box-shadow: 0 0 30px rgba(255, 255, 255, 0.1); }
        }
    </style>
</head>
<body>
    <div class="user-info">
        <img src="../assets/images/<?php echo htmlspecialchars($profile_photo); ?>" alt="User Photo">
        <h1><?php echo htmlspecialchars($user['name']); ?></h1>
    </div>

    <div class="fee-box">
        <h2>Monthly Fee Details</h2>
        <p><strong>Next Due Date:</strong> <?php echo $next_due_date->format('d M Y'); ?></p>
        <p><strong>Days Remaining:</strong>
            <?php echo $days_remaining >= 0 ? $days_remaining . ' days' : 'Overdue by ' . abs($days_remaining) . ' days'; ?>
        </p>
        <p><strong>Fine (₹10/day after due):</strong> ₹<?php echo $fine; ?></p>

        <div class="notice">
            <?php 
                echo $payment_status === 'Paid' 
                    ? "✅ Payment already uploaded for this cycle." 
                    : "⚠️ Payment pending! Please upload your screenshot.";
            ?>
        </div>

        <?php if ($payment_status !== 'Paid'): ?>
        <div class="upload-box">
            <form action="upload_fee_screenshot.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="screenshot" required>
                <br>
                <button type="submit">Upload Payment Screenshot</button>
            </form>
        </div>
        <?php endif; ?>

        <?php if (!empty($screenshot_path)): ?>
            <div class="screenshot-preview">
                <p><strong>Last Uploaded Screenshot:</strong></p>
                <img src="<?php echo htmlspecialchars($screenshot_path); ?>" alt="Payment Screenshot">
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
