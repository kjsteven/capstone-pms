<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Navbar</title>
</head>
<body>

<nav class="fixed top-0 z-50 w-full bg-white border-b border-blue-200 dark:bg-blue-800 dark:border-blue-700">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start rtl:justify-end">
                <button id="sidebar-toggle" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-white rounded-lg sm:hidden hover:bg-white focus:outline-none focus:ring-2 focus:ring-white dark:text-white dark:hover:bg-white dark:focus:ring-white-600">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                    </svg>
                </button>
                <a href="#" class="flex items-center ms-2 md:me-24">
                    <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white flex items-center">
                        <!-- Key Icon -->
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
                        <i class="fas fa-bell fa-lg text-blue-800 dark:text-white"></i>
                    </button>

                    <button type="button" class="flex text-sm bg-blue-600 rounded-full focus:ring-4 ms-6 focus:ring-blue-300 dark:focus:ring-blue-600" aria-expanded="false" id="user-menu-button">
                        <span class="sr-only">Open user menu</span>
                        <img class="w-8 h-8 rounded-full" src="https://flowbite.com/docs/images/people/profile-picture-5.jpg" alt="user photo">
                    </button>

                    <!-- Dropdown menu -->
                    <div class="absolute right-0 z-50 hidden w-48 top-[45px] origin-top-right shadow-lg bg-white dark:bg-blue-800 ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" id="dropdown-user">
                        <div class="px-4 py-3">
                            <p class="text-sm text-blue-900 dark:text-white">User Name</p>
                            <p class="text-sm font-medium text-blue-900 truncate dark:text-blue-300">user.email@example.com</p>
                        </div>
                        <ul class="py-1" role="none">
                            <li>
                                <a href="profile.php" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-100 dark:text-white dark:hover:bg-blue-600 dark:hover:text-white" role="menuitem"> <i class="fas fa-user mr-4"></i>Profile</a>
                            </li>
                            <li>
                                <a href="../authentication/logout.php" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-100 dark:text-white dark:hover:bg-blue-600 dark:hover:text-white" role="menuitem"> <i class="fas fa-sign-out-alt mr-4"></i>Logout</a>
                            </li>
                        </ul>
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
