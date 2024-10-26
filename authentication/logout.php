<?php
session_start();

// Check if the user is already logged in, if not, redirect to the login page
if (empty($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
// Destroy the session
session_destroy();

// Redirect the user to the login page
header("Location: login.php");
exit();
?>
