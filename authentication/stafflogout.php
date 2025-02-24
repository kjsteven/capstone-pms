<?php
session_start();
require '../session/db.php';
require_once '../session/audit_trail.php';

// Get user ID from session before destroying it
$staff_id = $_SESSION["staff_id"] ?? null;

if ($staff_id) {
    // Log the logout activity
    logActivity(
        $staff_id,
        'Staff  Logout',
        'Staff member logged out successfully'
    );

    // Update the user's status to 'inactive'
    $update_status_query = "UPDATE staff SET status = 'Inactive' WHERE staff_id = ?";
    $stmt = $conn->prepare($update_status_query);
    $stmt->bind_param('i', $staff_id);
    $stmt->execute();
    $stmt->close();
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy session
session_destroy();

// Redirect to login with cache prevention
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: ../authentication/login.php");
exit();
?>
