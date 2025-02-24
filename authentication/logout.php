<?php

session_start();
require '../session/db.php';
require_once '../session/audit_trail.php'; // Add this line

if (empty($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
if (!empty($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    
    // Log the logout activity
    logActivity(
        $user_id,
        'Logout',
        'User logged out'
    );

    // Update the user's status to 'inactive'
    $update_status_query = "UPDATE users SET status = 'inactive' WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $update_status_query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Destroy session and redirect to login
session_unset();
session_destroy();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: login.php");
exit();

?>
