<?php
require_once '../session/db.php';
require_once '../session/session_manager.php';
require_once '../session/audit_trail.php';

session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$staff_id = $data['staff_id'] ?? null;
$current_user_id = $data['current_user_id'] ?? null;

if (!$staff_id || !$current_user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Get current status
$status_query = "SELECT status FROM staff WHERE staff_id = ?";
$stmt = $conn->prepare($status_query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$current_status = $stmt->get_result()->fetch_assoc()['status'];

// Toggle status
$new_status = $current_status === 'Suspended' ? 'Active' : 'Suspended';

$query = "UPDATE staff SET status = ? WHERE staff_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $new_status, $staff_id);

if ($stmt->execute()) {
    // Log the status change
    $details = "Changed staff (ID: $staff_id) status from $current_status to $new_status";
    logActivity($current_user_id, "Update Staff Status", $details);

    echo json_encode([
        'success' => true,
        'message' => "Staff status changed to $new_status",
        'newStatus' => $new_status
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating staff status']);
}
