<?php
ob_start(); 

require_once '../session/session_manager.php';
require '../session/db.php';
include('../session/auth.php');
require_once '../notification/notif_handler.php'; // Add this line

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


// Assuming you have a session started and the user is logged in, get the user ID
$user_id = $_SESSION['user_id']; // or fetch user ID from session if already set

// Query to get the user details (name and email) from the database
$query = "SELECT name, email, profile_image  FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);

// Check for query errors
if (!$stmt) {
    die('Query failed: ' . $conn->error);
}

$stmt->bind_param("i", $user_id); // Bind the user_id to the query
$stmt->execute();
$stmt->bind_result($user_name, $user_email, $profile_image); // Bind the results to variables
$stmt->fetch();
$stmt->close();

// if no image is found, set a default image
$profile_image_path = !empty($profile_image) ? $profile_image : "https://flowbite.com/docs/images/people/profile-picture-5.jpg";

// Get notifications for admin
$notifications = getNotifications($user_id);
$unread_count = getUnreadCount($user_id);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <title>Navbar</title>
    <style>
        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            padding: 2px 6px;
            border-radius: 50%;
            background: #EF4444;
            color: white;
            font-size: 12px;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .notification-dropdown {
            width: 400px;
            max-width: calc(100vw - 2rem);
            right: 1rem;
            margin-left: 1rem;
        }
        .notification-item {
            transition: all 0.3s ease;
            background-color: #ffffff;
        }
        .notification-item.unread {
            border-left: 4px solid #3b82f6;
            background-color: #f0f7ff;
        }
        .notification-message {
            color: #1e293b;
            font-weight: 500;
            line-height: 1.5;
        }
    </style>
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
                    <span class="self-center text-lg font-semibold sm:text-2xl whitespace-nowrap dark:text-white flex items-center">
                        <!-- PropertyWise Text -->
                        <i class="fas fa-key text-inherit mr-2"></i> 
                        PropertyWise
                    </span>
                </a>
            </div>


            <div class="flex items-center">
                <div class="flex items-center ms-3 relative">
                    <!-- Notification Icon -->
                    <button type="button" class="flex items-center justify-center w-10 h-10 bg-blue-700 rounded-full text-white relative" id="notification-button">
                        <span class="sr-only">Open notifications</span>
                        <svg data-feather="bell" class="w-6 h-6 text-white"></svg>
                        <?php if ($unread_count > 0): ?>
                            <span class="notification-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </button>

                    <button type="button" class="flex text-sm bg-blue-600 rounded-full focus:ring-4 ms-6 focus:ring-blue-300 dark:focus:ring-blue-600" aria-expanded="false" id="user-menu-button">
                        <span class="sr-only">Open user menu</span>
                        <img class="w-8 h-8 rounded-full" src="<?php echo htmlspecialchars($profile_image_path); ?>" alt="user photo">
                    </button>

                    <!-- Dropdown menu -->
                    <div class="absolute right-0 z-50 hidden w-48 top-[45px] origin-top-right shadow-lg bg-white dark:bg-blue-800 ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" id="dropdown-user">
                        <div class="px-4 py-3">
                            <p class="text-sm text-blue-900 dark:text-white">Admin: <?php echo htmlspecialchars($user_name); ?></p>
                            <p class="text-sm font-medium text-blue-900 truncate dark:text-blue-300"><?php echo htmlspecialchars($user_email); ?></p>
                        </div>
                        <ul class="py-1" role="none">
                            <li>
                                <a href="../authentication/logout.php" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-100 dark:text-white dark:hover:bg-blue-600 dark:hover:text-white flex items-center" role="menuitem"> 
                                    <svg data-feather="log-out" class="w-5 h-5 text-blue-500 mr-4"></svg>
                                    Logout
                                </a> <!-- Feather Log-out Icon -->
                            </li>
                          
                        </ul>
                    </div>

                    <!-- Notifications menu -->
                    <div class="absolute right-0 z-40 hidden notification-dropdown top-[45px] origin-top-right shadow-lg bg-white dark:bg-blue-900 ring-1 ring-black ring-opacity-5 focus:outline-none rounded-lg mx-4" role="menu" aria-orientation="vertical" aria-labelledby="notification-button" id="dropdown-notifications">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-blue-800">
                            <div class="flex justify-between items-center">
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</p>
                                <?php if ($unread_count > 0): ?>
                                    <span class="text-sm text-blue-600 dark:text-blue-400"><?php echo $unread_count; ?> unread</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="max-h-[400px] overflow-y-auto" id="notifications-container">
                            <?php if (empty($notifications)): ?>
                                <div class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 text-center">
                                    No notifications
                                </div>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <div class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?> p-4 border-b border-gray-200 dark:border-blue-700">
                                        <div class="flex flex-col">
                                            <div class="flex items-start justify-between">
                                                <p class="text-sm notification-message flex-1 mr-4">
                                                    <?php echo htmlspecialchars($notif['message']); ?>
                                                </p>
                                                <?php if (!$notif['is_read']): ?>
                                                    <button onclick="markNotificationAsRead(<?php echo $notif['notification_id']; ?>, this)" 
                                                            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium whitespace-nowrap">
                                                        Mark as read
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                                <?php echo date('M j, H:i', strtotime($notif['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php
                                $total_notifications = getTotalNotifications($user_id);
                                if ($total_notifications > 10):
                                ?>
                                    <div class="text-center py-3" id="load-more-container">
                                        <button onclick="loadMoreNotifications()" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm font-medium">
                                            Show More
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
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

    let currentOffset = 10;
    let isLoading = false;

    function markNotificationAsRead(notificationId, button) {
        fetch('../notification/mark_as_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'notification_id=' + notificationId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notifItem = button.closest('.notification-item');
                notifItem.classList.remove('unread');
                button.remove();
                
                // Update unread count
                const unreadCount = document.querySelector('.notification-badge');
                if (unreadCount) {
                    const currentCount = parseInt(unreadCount.textContent) - 1;
                    if (currentCount <= 0) {
                        unreadCount.remove();
                    } else {
                        unreadCount.textContent = currentCount;
                    }
                }
            }
        });
    }

    function loadMoreNotifications() {
        if (isLoading) return;
        isLoading = true;

        const loadMoreBtn = document.querySelector('#load-more-container button');
        loadMoreBtn.textContent = 'Loading...';

        fetch('../notification/load_more_notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'offset=' + currentOffset
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('notifications-container');
                const loadMoreContainer = document.getElementById('load-more-container');
                
                loadMoreContainer.insertAdjacentHTML('beforebegin', data.html);
                currentOffset += 10;
                
                if (!data.hasMore) {
                    loadMoreContainer.remove();
                }
            }
            isLoading = false;
            loadMoreBtn.textContent = 'Show More';
        })
        .catch(error => {
            console.error('Error:', error);
            isLoading = false;
            loadMoreBtn.textContent = 'Show More';
        });
    }
</script>

</body>
</html>
