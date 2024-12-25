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

            <!-- Property Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold  text-blue-950 dark:text-white uppercase tracking-wide">Property</h3>
            </li>
            
            <li class="relative">
            <!-- Main Sidebar Item -->
            <div class="flex items-center justify-between p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group">
                <a href="bookunit.php" class="flex items-center">
                    <svg data-feather="home" class="w-5 h-5 text-blue-500 transition duration-75 dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="ms-3 text-white text-sm dark:text-white">Reserve a Unit</span>
                </a>
                <!-- Arrow Icon for Dropdown -->
                <button id="toggle-submenu" class="focus:outline-none">
                    <svg data-feather="chevron-down" class="w-4 h-4 text-blue-500 transition duration-75 group-hover:text-blue-900 dark:text-white dark:group-hover:text-white"></svg>
                </button>
            </div>

            <!-- Sub-sidebar for Reservation History -->
            <ul class="hidden pl-8 space-y-2" id="reservation-submenu">
                <li>
                    <a href="reservation_history.php" class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group">
                        <svg data-feather="file-text" class="w-4 h-4 text-blue-500 transition duration-75 dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                        <span class="ms-3 text-white text-sm dark:text-white">Reservation History</span>
                    </a>
                </li>
            </ul>
        </li>


            <li>
                <a href="unitinfo.php" class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group">
                    <svg data-feather="info" class="w-5 h-5 text-blue-500 transition duration-75 dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="flex-1 ms-3 text-sm text-white dark:text-white">View Unit</span>
                </a>
            </li>

            <!-- Services Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold text-blue-950 dark:text-white uppercase tracking-wide">Services</h3>
            </li>
            <li>
                <a href="maintenance.php" class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group">
                    <svg data-feather="tool" class="w-5 h-5 text-blue-500 transition duration-75 dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="flex-1 ms-3 text-sm text-white dark:text-white">Maintenance Requests</span>
                </a>
            </li>

            <!-- Documents & Payments Section -->
            <li>
                <h3 class="px-2 pt-4 pb-2 text-sm font-semibold  text-blue-950 dark:text-white uppercase tracking-wide">Documents & Payments</h3>
            </li>
            <li>
                <a href="contract.php" class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group">
                    <svg data-feather="file" class="w-5 h-5 text-blue-500 transition duration-75 text-sm dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="ms-3 text-white text-sm dark:text-white">Rent Agreement</span>
                </a>
            </li>
            <li>
                <a href="payment.php" class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group">
                    <svg data-feather="credit-card" class="w-5 h-5 text-blue-500 transition duration-75 text-sm dark:text-white group-hover:text-blue-900 dark:group-hover:text-white"></svg>
                    <span class="ms-3 text-white text-sm  dark:text-white">Pay Online</span>
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
