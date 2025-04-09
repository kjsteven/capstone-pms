<?php
require_once '../session/session_manager.php';
require_once '../session/db.php';
require_once 'notif_handler.php';

start_secure_session();

if (!isset($_SESSION['user_id']) || !isset($_POST['offset'])) {
    echo json_encode(['success' => false]);
    exit;
}

$offset = intval($_POST['offset']);
$limit = 10;
$notifications = getNotifications($_SESSION['user_id'], $limit, $offset);

$html = '';
foreach ($notifications as $notif) {
    $html .= '<div class="notification-item ' . ($notif['is_read'] ? '' : 'unread') . ' p-4 border-b border-gray-200 dark:border-blue-700">
        <div class="flex flex-col">
            <div class="flex items-start justify-between">
                <p class="text-sm notification-message flex-1 mr-4">
                    ' . htmlspecialchars($notif['message']) . '
                </p>';
    if (!$notif['is_read']) {
        $html .= '<button onclick="markNotificationAsRead(' . $notif['notification_id'] . ', this)" 
                    class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium whitespace-nowrap">
                    Mark as read
                </button>';
    }
    $html .= '</div>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                ' . date('M j, H:i', strtotime($notif['created_at'])) . '
            </p>
        </div>
    </div>';
}

echo json_encode([
    'success' => true,
    'html' => $html,
    'hasMore' => count($notifications) == $limit
]);
