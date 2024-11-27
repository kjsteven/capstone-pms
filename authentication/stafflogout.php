<?php
session_start();

// Check if the user is already logged in, if not, redirect to the login page
if (empty($_SESSION["staff_id"])) {
    header("Location: stafflogin.php");
    exit();
}
// Destroy the session
session_destroy();

// Redirect the user to the login page
header("Location: stafflogin.php");
exit();
?>
