<?php

session_start();
require '../session/db.php';

if (empty($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION["user_id"];

// Update the user's status to 'inactive'
$update_status_query = "UPDATE users SET status = 'inactive' WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $update_status_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Destroy session and redirect to login
session_unset();
session_destroy();

header("Location: login.php");
exit();


?>
