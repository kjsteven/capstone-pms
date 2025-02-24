<?php

require '../session/db.php';
require_once '../session/session_manager.php';

start_secure_session();


// Check if the staff member is logged in
if (!isset($_SESSION['staff_id'])) {
    die("You must be logged in to view this page.");
}


// Get the logged-in staff member's ID
$staffId = $_SESSION['staff_id'];

// Fetch staff details from the database
$query = "SELECT Name, Email FROM staff WHERE staff_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $staffId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Staff member not found.");
}

$staff = $result->fetch_assoc();
$staffName = $staff['Name'];
$staffEmail = $staff['Email'];

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <title>Navbar</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
</head>
<body>

<nav class="fixed top-0 z-50 w-full bg-white border-b border-blue-200 dark:bg-blue-800 dark:border-blue-700">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start rtl:justify-end">
                <button id="sidebar-toggle" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-white rounded-lg sm:hidden hover:bg-white focus:outline-none focus:ring-2 focus:ring-white dark:text-white dark:hover:bg-white dark:focus:ring-white-600">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" data-feather="menu"></svg> <!-- Feather Hamburger Icon -->
                </button>
                <a href="#" class="flex items-center ms-2 md:me-24">
                    <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white flex items-center">
                        <!-- FontAwesome Key Icon -->
                        <i class="fas fa-key text-inherit mr-2"></i>
                        <!-- PropertyWise Text -->
                        PropertyWise
                    </span>
                </a>

            </div>
          
            <div class="flex items-center">
                    <div class="flex items-center ms-3 relative">
                        <!-- Notification Icon -->
                        <button type="button" class="flex items-center justify-center w-10 h-10 bg-blue-700 rounded-full text-white" id="notification-button">
                            <span class="sr-only">Open notifications</span>
                            <svg data-feather="bell" class="w-6 h-6 text-blue-800 dark:text-white"></svg> <!-- Feather Bell Icon -->
                        </button>

                        <button type="button" class="flex text-sm bg-blue-600 rounded-full focus:ring-4 ms-6 focus:ring-blue-300 dark:focus:ring-blue-600" aria-expanded="false" id="user-menu-button">
                            <span class="sr-only">Open user menu</span>
                            <!-- Displaying profile image dynamically -->
                            <img class="w-8 h-8 rounded-full" src="https://flowbite.com/docs/images/people/profile-picture-5.jpg" alt="user photo">
                        </button>

                        <!-- Dropdown menu -->
                        <div class="absolute right-0 z-50 hidden w-48 top-[45px] origin-top-right shadow-lg bg-white dark:bg-blue-800 ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" id="dropdown-user">
                            <div class="px-4 py-3">
                                <p class="text-sm text-blue-900 dark:text-white"><?php echo htmlspecialchars($staffName); ?></p>
                                <p class="text-sm font-medium text-blue-900 truncate dark:text-blue-300"><?php echo htmlspecialchars($staffEmail); ?></p>
                            </div>
                            <ul class="py-1" role="none">
                                <li>
                                    <a href="StaffProfile.php" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-100 dark:text-white dark:hover:bg-blue-600 dark:hover:text-white flex items-center" role="menuitem"> 
                                        <svg data-feather="user" class="w-5 h-5 text-blue-500 mr-4"></svg>
                                        Profile
                                    </a> <!-- Feather User Icon -->
                                </li>
                                <li>
                                    <a href="../authentication/stafflogout.php" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-100 dark:text-white dark:hover:bg-blue-600 dark:hover:text-white flex items-center" role="menuitem"> 
                                        <svg data-feather="log-out" class="w-5 h-5 text-blue-500 mr-4"></svg>
                                        Logout
                                    </a> <!-- Feather Log-out Icon -->
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                    <!-- Notifications menu -->
                    <div class="absolute right-0 z-40 hidden w-48 top-[45px] origin-top-right shadow-lg bg-white dark:bg-blue-800 ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="notification-button" id="dropdown-notifications">
                        <div class="px-4 py-3">
                            <p class="text-sm text-blue-900 dark:text-white">Notifications</p>
                        </div>
                        <ul class="py-1" role="none">
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-100 dark:text-white dark:hover:bg-blue-600 dark:hover:text-white" role="menuitem">Notification 1</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-100 dark:text-white dark:hover:bg-blue-600 dark:hover:text-white" role="menuitem">Notification 2</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script src="../node_modules/feather-icons/dist/feather.min.js"></script>

<script>
    // Initialize Feather Icons
    feather.replace();
</script>



<script>
    // Toggle dropdown visibility
    const userMenuButton = document.getElementById('user-menu-button');
    const dropdownUser = document.getElementById('dropdown-user');

    userMenuButton.addEventListener('click', () => {
        dropdownUser.classList.toggle('hidden');
    });

    // Toggle notifications visibility
    const notificationButton = document.getElementById('notification-button');
    const dropdownNotifications = document.getElementById('dropdown-notifications');

    notificationButton.addEventListener('click', () => {
        dropdownNotifications.classList.toggle('hidden');
    });

    // Close the dropdowns if clicked outside
    document.addEventListener('click', (e) => {
        if (!userMenuButton.contains(e.target) && !dropdownUser.contains(e.target)) {
            dropdownUser.classList.add('hidden');
        }
        if (!notificationButton.contains(e.target) && !dropdownNotifications.contains(e.target)) {
            dropdownNotifications.classList.add('hidden');
        }
    });
</script>

</body>
</html>
