<?php
session_start(); // Start a session to track user login state

// Check if the user is already logged in (modify based on your login system)
if (isset($_SESSION['user_id'])) {
    header("Location: users/user_dashboard.php"); // Redirect logged-in users to their dashboard
    exit;
} elseif (isset($_SESSION['admin_id'])) {
    header("Location: admin/admin_dashboard.php"); // Redirect admin to admin panel
    exit;
}

// Load the HTML content
include "index.html";
?>
