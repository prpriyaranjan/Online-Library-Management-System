<?php
function logActivity($conn, $user_id, $action) {
    if (!$user_id || !$action) return;

    $stmt = $conn->prepare("INSERT INTO logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
    $stmt->close();
}
?>
