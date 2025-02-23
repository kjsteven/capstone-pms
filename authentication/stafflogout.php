<?php
session_start();
require_once '../session/db.php';
require_once '../session/audit_trail.php';

// Debug log
error_log("Stafflogout.php started - Staff ID: " . (isset($_SESSION["staff_id"]) ? $_SESSION["staff_id"] : 'not set'));

if (empty($_SESSION["staff_id"])) {
    header("Location: stafflogin.php");
    exit();
}

// Store staff_id before session destruction
$staff_id = $_SESSION["staff_id"];

// Check database connection
if (!$conn) {
    error_log("Database connection failed in stafflogout.php");
    die("Connection failed: " . mysqli_connect_error());
}

// First update the staff status
$update_status_query = "UPDATE staff SET status = 'inactive' WHERE staff_id = ?";
$stmt = mysqli_prepare($conn, $update_status_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $staff_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    error_log("Failed to prepare status update statement");
}

// Then log the activity
$logged = logActivity(
    $staff_id,
    'Staff Logout',
    'Staff member logged out successfully',
    $_SERVER['REMOTE_ADDR']
);

if (!$logged) {
    error_log("Failed to log staff logout activity for staff_id: " . $staff_id);
}

// Verify the log was created
$check_query = "SELECT * FROM activity_logs WHERE staff_id = ? ORDER BY timestamp DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $check_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $staff_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        error_log("Logout activity logged successfully for staff_id: " . $staff_id);
    } else {
        error_log("No logout activity found for staff_id: " . $staff_id);
    }
    mysqli_stmt_close($stmt);
}

// Only destroy session after everything else is done
session_unset();
session_destroy();

// Close database connection
mysqli_close($conn);

header("Location: stafflogin.php");
exit();
?>
