<?php
include '../database/db_connect.php';

if (!isset($_GET['id'])) {
    die("No payment ID specified.");
}

$id = intval($_GET['id']);

$check = mysqli_query($conn, "SELECT screenshot FROM fee_payments WHERE id = $id");
$data = mysqli_fetch_assoc($check);
if ($data && file_exists("../" . $data['screenshot'])) {
    unlink("../" . $data['screenshot']);
}

$query = "DELETE FROM fee_payments WHERE id = $id";
if (mysqli_query($conn, $query)) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    echo "Failed to delete record: " . mysqli_error($conn);
}
?>
