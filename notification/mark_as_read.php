<?php
require_once '../session/db.php';
require_once 'notif_handler.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notification_id = intval($_POST['notification_id']);
    $success = markAsRead($notification_id);
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false]);
}
?>
