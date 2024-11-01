<?php
ob_start(); 

require_once '../session/session_manager.php';
require '../session/db.php';
include('../session/auth.php');

start_secure_session();

// Set HTTP headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

echo '<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>';


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../authentication/login.php');
    exit();
}

// Check if the user is an admin
if (!check_admin_role()) {
    header('Location: error.php'); // Redirect to an error page if not admin
    exit();
}


?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Admin Dashboard</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        /* Optional: Custom styles for smooth transitions */
        .transition-transform {
            transition: transform 0.3s ease;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body> 

<!-- Include Navbar -->
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>

<div class="p-4 sm:ml-64">
    <div class="mt-14">
        <h1 class="text-2xl font-semibold text-gray-600 dark:text-gray-600">Hi, Welcome Admin</h1>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Here you can manage your property, tenants, and access other features.</p>
    </div>
</div>


</body>
</html>
