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
    </style>
</head>
<body>

<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-blue-200 sm:translate-x-0 dark:bg-blue-800 dark:border-blue-700" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-blue-800">
        <ul class="space-y-2 font-medium">

            <!-- Dashboard -->
            <li>
                <a href="staffDashboard.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <!-- Feather Icon for Dashboard -->
                    <svg data-feather="home" class="text-white w-5 h-5"></svg>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Profile Management -->
            <li>
                <a href="staffProfile.php" class="flex items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700 group">
                    <!-- Feather Icon for Profile -->
                    <svg data-feather="user" class="w-5 h-5 text-white w-5 h-5"></svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Profile</span>
                </a>
            </li>


             <!-- Schedule -->
             <li>
                <a href="staffSchedule.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <!-- Feather Icon for Work Orders -->
                    <svg data-feather="calendar" class="text-white w-5 h-5"></svg>
                    <span>Maintenance Schedule</span>
                </a>
            </li>

            <!-- Work Orders -->
                <li>
                <a href="staffWork.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <!-- Feather Icon for Work Orders -->
                    <svg data-feather="clipboard" class="text-white w-5 h-5"></svg>
                    <span>Work Orders</span>
                </a>
            </li>

            <!-- Reports -->
            <li>
                <a href="staffReports.php" class="grid grid-cols-[30px_auto] items-center p-2 text-blue-900 rounded-lg dark:text-white hover:bg-blue-100 dark:hover:bg-blue-700">
                    <!-- Feather Icon for Reports -->
                    <svg data-feather="bar-chart" class="text-white w-5 h-5"></svg>
                    <span>Reports and Analytics</span>
                </a>
            </li>

        </ul>
    </div>
</aside>

<!-- Include your JavaScript for Feather Icons -->
<script src="../node_modules/feather-icons/dist/feather.min.js"></script>
<script>
    // Initialize Feather Icons
    feather.replace();
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('logo-sidebar');
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('transform-none');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const isMobile = window.innerWidth < 768;
            const clickedOutsideSidebar = !sidebar.contains(event.target);
            const clickedOutsideToggle = !sidebarToggle.contains(event.target);

            if (isMobile && clickedOutsideSidebar && clickedOutsideToggle) {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('transform-none');
            }
        });
    });

    // Initialize Feather Icons
    feather.replace();
</script>

</body>
</html>
