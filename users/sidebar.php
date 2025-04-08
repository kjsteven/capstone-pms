<?php

require_once '../session/session_manager.php';
start_secure_session();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

// Get user's KYC status
$kyc_query = "SELECT COALESCE(verification_status, 'not_submitted') as kyc_status 
              FROM kyc_verification 
              WHERE user_id = ? 
              ORDER BY submission_date DESC LIMIT 1";
$stmt = $conn->prepare($kyc_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$kyc_result = $stmt->get_result();
$kyc_status = $kyc_result->num_rows > 0 ? $kyc_result->fetch_assoc()['kyc_status'] : 'not_submitted';

// Check if user has access
$has_access = ($kyc_status === 'approved');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <title>Sidebar</title>
    <style>
        /* Optional: Custom styles for smooth transitions */
        .transition-transform {
            transition: transform 0.3s ease;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }
        /* Add styles for disabled state */
        .sidebar-item-disabled {
            opacity: 0.75;
            position: relative;
            pointer-events: none;
        }
        .kyc-badge {
            font-size: 0.65rem;
            padding: 2px 6px;
            background-color: rgba(239, 68, 68, 0.2);
            color: rgb(239, 68, 68);
            border-radius: 4px;
            white-space: nowrap;
            margin-left: auto;
        }
    </style>
</head>
<body>

<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-blue-200 sm:translate-x-0 dark:bg-blue-800 dark:border-blue-700" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-blue-800">
        <ul class="space-y-2 font-medium">
            <!-- Overview Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold text-blue-950 dark:text-white uppercase tracking-wide">Overview</h3>
            </li>
            <li>
                <a href="dashboard.php" class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group">
                    <svg data-feather="activity" class="w-5 h-5 text-blue-500 transition duration-75 dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="ms-3 text-white text-sm dark:text-white">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="profile.php" class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group">
                    <svg data-feather="user" class="w-5 h-5 text-blue-500 transition duration-75 dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="flex-1 ms-3 text-sm text-white dark:text-white">Profile</span>
                </a>
            </li>

            <!-- KYC Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold  text-blue-950 dark:text-white uppercase tracking-wide">Verification</h3>
            </li>

            <li>
                <a href="kyc.php" class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group">
                    <svg data-feather="user-check" class="w-5 h-5 text-blue-500 transition duration-75 dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="flex-1 ms-3 text-sm text-white dark:text-white">Verify Your Identity</span>
                </a>
            </li>

            <!-- Property Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold text-blue-950 dark:text-white uppercase tracking-wide">Property</h3>
            </li>
            <li>
                <a href="<?= $has_access ? 'bookunit.php' : '#' ?>" 
                   class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group <?= !$has_access ? 'sidebar-item-disabled' : '' ?>">
                    <svg data-feather="home" class="w-5 h-5 text-blue-500 transition duration-75 dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="ms-3 text-white text-sm dark:text-white">Reserve a Unit</span>
                    <?php if (!$has_access): ?>
                        <span class="kyc-badge">Requires KYC</span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="<?= $has_access ? 'unitinfo.php' : '#' ?>" 
                   class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group <?= !$has_access ? 'sidebar-item-disabled' : '' ?>">
                    <svg data-feather="info" class="w-5 h-5 text-blue-500 transition duration-75 dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="flex-1 ms-3 text-sm text-white dark:text-white">View Unit</span>
                    <?php if (!$has_access): ?>
                        <span class="kyc-badge">Requires KYC</span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Services Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold text-blue-950 dark:text-white uppercase tracking-wide">Services</h3>
            </li>
            <li>
                <a href="<?= $has_access ? 'maintenance.php' : '#' ?>" 
                   class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group <?= !$has_access ? 'sidebar-item-disabled' : '' ?>">
                    <svg data-feather="tool" class="w-5 h-5 text-blue-500 transition duration-75 dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="flex-1 ms-3 text-sm text-white dark:text-white">Maintenance Requests</span>
                    <?php if (!$has_access): ?>
                        <span class="kyc-badge">Requires KYC</span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Documents & Payments Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold text-blue-950 dark:text-white uppercase tracking-wide">Documents & Payments</h3>
            </li>
            <li>
                <a href="<?= $has_access ? 'contract.php' : '#' ?>" 
                   class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group <?= !$has_access ? 'sidebar-item-disabled' : '' ?>">
                    <svg data-feather="file" class="w-5 h-5 text-blue-500 transition duration-75 text-sm dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="ms-3 text-white text-sm dark:text-white">Rent Agreement</span>
                    <?php if (!$has_access): ?>
                        <span class="kyc-badge">Requires KYC</span>
                    <?php endif; ?>
                </a>
            </li>

            <li>
                <a href="<?= $has_access ? 'invoice.php' : '#' ?>" 
                   class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group <?= !$has_access ? 'sidebar-item-disabled' : '' ?>">
                    <svg data-feather="file-text" class="w-5 h-5 text-blue-500 transition duration-75 text-sm dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="ms-3 text-white text-sm dark:text-white">Invoice</span>
                    <?php if (!$has_access): ?>
                        <span class="kyc-badge">Requires KYC</span>
                    <?php endif; ?>
                </a>
            </li>

            <li>
                <a href="<?= $has_access ? 'payment.php' : '#' ?>" 
                   class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group <?= !$has_access ? 'sidebar-item-disabled' : '' ?>">
                    <svg data-feather="credit-card" class="w-5 h-5 text-blue-500 transition duration-75 text-sm dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="ms-3 text-white text-sm dark:text-white">Pay Online</span>
                    <?php if (!$has_access): ?>
                        <span class="kyc-badge">Requires KYC</span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </div>
</aside>

<script src="../node_modules/feather-icons/dist/feather.min.js"></script> <!-- Local path to Feather Icons -->
<script>
    // Initialize Feather Icons
    feather.replace();

    // Sidebar toggle script
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('logo-sidebar');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleButton = document.getElementById('toggle-submenu');
        const subMenu = document.getElementById('reservation-submenu');

        toggleButton.addEventListener('click', (e) => {
            e.preventDefault();
            subMenu.classList.toggle('hidden');
        });
    });
</script>

</body>
</html>
