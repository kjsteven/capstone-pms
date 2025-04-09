<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../notification/notif_handler.php';

start_secure_session();

if (!isset($_SESSION['user_id']) || !isset($_POST['unit_no'])) {
    echo json_encode(['success' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];
$unit_no = $_POST['unit_no'];

// Create notification for tenant
$userMessage = "You have downloaded the contract for Unit {$unit_no}. Please keep it in a safe location.";
$success = createNotification($user_id, $userMessage, 'contract');

// Create notification for admin
$adminMessage = "Tenant has downloaded the contract for Unit {$unit_no}.";
createNotification(1, $adminMessage, 'admin_contract');

// Get updated unread count
$newUnreadCount = getUnreadCount($user_id);

echo json_encode([
    'success' => $success,
    'unreadCount' => $newUnreadCount
]);
