<?php
ob_start(); 

require_once '../session/session_manager.php';
require '../session/db.php';
include('../session/auth.php');

// Start secure session
start_secure_session();

// Set HTTP headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("X-Content-Type-Options: nosniff");
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
    <title>Admin Sidebar</title>
    <style>
        .transition-transform {
            transition: transform 0.3s ease;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }

        .icon-size {
            width: 20px; /* Adjust the size as needed */
            height: 20px;
        }
    </style>
</head>
<body>

<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-blue-200 sm:translate-x-0 dark:bg-blue-800 dark:border-blue-700" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-blue-800">
        <ul class="space-y-2">
            <!-- Overview Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold text-blue-950 dark:text-white uppercase tracking-wide">Overview</h3>
            </li>
            <li>
                <a href="dashboardAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="activity" class="text-white icon-size"></svg>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Property Management Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold text-blue-950 dark:text-white uppercase tracking-wide">Administration</h3>
            </li>
            <li>
                <a href="propertyAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="home" class="text-white icon-size"></svg>
                    <span>Properties</span>
                </a>
            </li>
            <li>
                <a href="tenantAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="user-plus" class="text-white icon-size"></svg>
                    <span>Tenants</span>
                </a>
            </li>
            <li>
                <a href="tenant_information.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="info" class="text-white icon-size"></svg>
                    <span>Tenant Information</span>
                </a>
            </li>
            <li>
                <a href="manageUsers.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="users" class="text-white icon-size"></svg>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="kyc_verification.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="user-check" class="text-white icon-size"></svg>
                    <span>KYC Verification</span>
                </a>
            </li>

            <!-- Operations Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold text-blue-950 dark:text-white uppercase tracking-wide">Operations</h3>
            </li>
            <li>
                <a href="reservationAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="calendar" class="text-white icon-size"></svg>
                    <span>Reservation</span>
                </a>
            </li>
            <li>
                <a href="maintenanceAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="tool" class="text-white icon-size"></svg>
                    <span>Maintenance</span>
                </a>
            </li>
            <li>
                <a href="contractAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="file-plus" class="text-white icon-size"></svg>
                    <span>Contract</span>
                </a>
            </li>

            <!-- Financial Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold  text-blue-950 dark:text-white uppercase tracking-wide">Financial</h3>
            </li>
            <li>
                <a href="invoiceAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="file-text" class="text-white icon-size"></svg>
                    <span>Invoice</span>
                </a>
            </li>
            <li>
                <a href="paymentAdmin.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="dollar-sign" class="text-white icon-size"></svg>
                    <span>Payments</span>
                </a>
            </li>

            <!-- Admin Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold text-blue-950 dark:text-white uppercase tracking-wide">Reports</h3>
            </li>
            <li>
                <a href="reports_analytics.php" class="grid grid-cols-[30px_auto] items-center p-2 text-sm text-blue-900 rounded-lg dark:text-blue-100 hover:bg-blue-100 dark:hover:bg-blue-700">
                    <svg data-feather="bar-chart" class="text-white icon-size"></svg>
                    <span>Reports & Analytics</span>
                </a>
            </li>
        </ul>
    </div>
</aside>


<script src="../node_modules/feather-icons/dist/feather.min.js"></script>

<script>
    // Initialize Feather Icons
    feather.replace();
</script>


<script>
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('logo-sidebar');

    toggleBtn?.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
    });
</script>

</body>
</html>
