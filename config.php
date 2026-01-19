<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change if needed
define('DB_PASS', '');      // Change if needed
define('DB_NAME', 'library_management_db');  // ✅ CORRECT database name

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to sanitize input
function sanitize_input($data, $conn) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Check if user is logged in (for future authentication extension)
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
?>