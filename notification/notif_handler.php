<?php
require_once '../session/db.php';

function getNotifications($user_id, $limit = 10, $offset = 0) {
    global $conn;
    $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTotalNotifications($user_id) {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

function markAsRead($notification_id) {
    global $conn;
    $query = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $notification_id);
    return $stmt->execute();
}

function getUnreadCount($user_id) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

function createNotification($user_id, $message, $type = 'kyc') {
    global $conn;
    $query = "INSERT INTO notifications (user_id, message, notification_type, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $user_id, $message, $type);
    return $stmt->execute();
}

function getNotificationIcon($type) {
    switch($type) {
        case 'payment':
            return 'credit-card';
        case 'admin_payment':
            return 'dollar-sign';
        case 'contract':
            return 'file-text';
        case 'admin_contract':
            return 'file';
        case 'maintenance':
            return 'tool';
        case 'admin_maintenance':
            return 'wrench';
        case 'reservation':
            return 'calendar';
        case 'admin_reservation':
            return 'book';
        case 'security':
            return 'shield';
        case 'kyc':
            return 'user-check';
        default:
            return 'bell';
    }
}
?>
