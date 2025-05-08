<?php
include '../database/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === 0) {
    $targetDir = "../assets/fee_screenshots/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["screenshot"]["name"]);
    $fileName = preg_replace("/[^a-zA-Z0-9._-]/", "", $fileName); // sanitize filename
    $targetFile = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($fileType, $allowedTypes)) {
        echo "<script>alert('Invalid file type. Only JPG, JPEG, PNG, and WEBP are allowed.'); window.history.back();</script>";
        exit();
    }

    if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $targetFile)) {
        $query = $conn->prepare("SELECT joining_date FROM users WHERE id = ?");
        $query->bind_param("i", $user_id);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows === 0) {
            echo "<script>alert('User not found.'); window.history.back();</script>";
            exit();
        }

        $user = $result->fetch_assoc();
        $joinDate = new DateTime($user['joining_date']);
        $today = new DateTime();

        $interval = $joinDate->diff($today);
        $monthsPassed = $interval->m + ($interval->y * 12);

        // Fee due date is every full month from join date
        $dueDate = clone $joinDate;
        $dueDate->modify("+{$monthsPassed} months");

        if ($dueDate > $today) {
            $dueDate->modify("-1 month");
        }

        // Fine if overdue
        $fine = 0;
        if ($today > $dueDate) {
            $lateDays = $today->diff($dueDate)->days;
            $fine = $lateDays * 10;
        }

        $paymentDate = $today->format('Y-m-d');
        $screenshotPath = "assets/fee_screenshots/" . $fileName;

        // Save to DB
        $stmt = $conn->prepare("INSERT INTO fee_payments (user_id, screenshot, payment_date, fine, status) VALUES (?, ?, ?, ?, 'Pending')");
        if (!$stmt) {
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("issi", $user_id, $screenshotPath, $paymentDate, $fine);

        if ($stmt->execute()) {
            echo "<script>alert('Fee submitted successfully and is pending confirmation.'); window.location.href = 'user_fee.php';</script>";
        } else {
            echo "<script>alert('Database error. Please try again later.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Failed to upload screenshot.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('No screenshot selected or invalid request.'); window.history.back();</script>";
}
?>
