<?php
ob_start(); 

require_once '../session/session_manager.php';
require '../session/db.php';
include('../session/auth.php');

// Start secure session
start_secure_session();

// Set HTTP headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

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

// Prevent caching with JavaScript (placed after header calls)
echo '<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Admin Sidebar</title>
    <style>
        .transition-transform {
            transition: transform 0.3s ease;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>

<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-blue-200 sm:translate-x-0 dark:bg-blue-800 dark:border-blue-700" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-blue-800">
        <ul class="space-y-2 font-medium">

            <!-- Dashboard -->
            <li>
                <a href="dashboardAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <i class="fas fa-tachometer-alt text-blue-500"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Property Management -->
            <li>
                <a href="propertyAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <i class="fas fa-building text-blue-500"></i>
                    <span>Property Management</span>
                </a>
            </li>

            <!-- Tenant Management -->
            <li>
                <a href="tenantAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <i class="fas fa-user-friends text-blue-500"></i>
                    <span>Tenant Management</span>
                </a>
            </li>

            <!-- Booking -->
            <li>
                <a href="bookingAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <i class="fas fa-calendar-alt text-blue-500"></i>
                    <span>Booking</span>
                </a>
            </li>

            <!-- Maintenance Requests -->
            <li>
                <a href="maintenanceAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <i class="fas fa-tools text-blue-500"></i>
                    <span>Maintenance Requests</span>
                </a>
            </li>

            <!-- Rental Payments -->
            <li>
                <a href="paymentAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <i class="fas fa-file-invoice-dollar text-blue-500"></i>
                    <span>Rental Payments</span>
                </a>
            </li>

            <!-- Reports and Analytics -->
            <li>
                <a href="reports_analytics.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <i class="fas fa-chart-line text-blue-500"></i>
                    <span>Reports and Analytics</span>
                </a>
            </li>

        </ul>
    </div>
</aside>

<script>
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('logo-sidebar');

    toggleBtn?.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
    });
</script>

</body>
</html>
