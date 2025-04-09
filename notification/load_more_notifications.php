<?php
// Ensure clean output
ob_start();

require_once '../session/session_manager.php';
require_once '../session/db.php';
require_once 'notif_handler.php';

// Prevent any session related errors from outputting
start_secure_session();

// Set proper headers
header('Content-Type: application/json');

// Clear any existing output
if (ob_get_length()) ob_clean();

if (!isset($_SESSION['user_id']) || !isset($_POST['offset'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $offset = intval($_POST['offset']);
    $limit = 10;
    $user_id = $_SESSION['user_id'];

    // Get notifications
    $notifications = getNotifications($user_id, $limit, $offset);
    
    // Get total count
    $total = getTotalNotifications($user_id);
    
    // Build HTML for notifications
    $html = '';
    foreach ($notifications as $notif) {
        $unreadClass = $notif['is_read'] ? '' : 'unread';
        $html .= '<div class="notification-item ' . $unreadClass . ' p-4 border-b border-gray-200 dark:border-blue-700">';
        $html .= '<div class="flex flex-col">';
        $html .= '<div class="flex items-start justify-between">';
        $html .= '<p class="text-sm notification-message flex-1 mr-4">' . htmlspecialchars($notif['message']) . '</p>';
        
        if (!$notif['is_read']) {
            $html .= '<button onclick="markNotificationAsRead(' . $notif['notification_id'] . ', this)" 
                        class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium whitespace-nowrap"
                        data-notification-id="' . $notif['notification_id'] . '">
                        Mark as read
                    </button>';
        }
        
        $html .= '</div>';
        $html .= '<p class="text-xs text-gray-600 dark:text-gray-400 mt-2">' . 
                 date('M j, H:i', strtotime($notif['created_at'])) . '</p>';
        $html .= '</div></div>';
    }

    // Clear output buffer before sending JSON response
    if (ob_get_length()) ob_clean();

    // Send JSON response
    echo json_encode([
        'success' => true,
        'html' => $html,
        'hasMore' => ($offset + $limit) < $total,
        'nextOffset' => $offset + $limit,
        'currentCount' => count($notifications),
        'total' => $total
    ]);

} catch (Exception $e) {
    // Clear output buffer before sending error response
    if (ob_get_length()) ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error loading notifications',
        'error' => $e->getMessage()
    ]);
}

// End and flush output buffer
ob_end_flush();
