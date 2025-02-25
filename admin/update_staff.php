<?php
require_once '../session/db.php';
require_once '../session/audit_trail.php';

start_secure_session();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$staff_id = $_POST['staff_id'] ?? null;
$specialty = $_POST['specialty'] ?? null;
$phone = $_POST['phone'] ?? null;
$status = $_POST['status'] ?? null;
$current_user_id = $_POST['current_user_id'] ?? null;

if (!$staff_id || !$current_user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate status
$valid_statuses = ['Available', 'Busy', 'Active', 'Suspended'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

// Get current staff info for audit log
$current_query = "SELECT Specialty, Phone_Number, status FROM staff WHERE staff_id = ?";
$stmt = $conn->prepare($current_query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$current_info = $stmt->get_result()->fetch_assoc();

$query = "UPDATE staff SET Specialty = ?, Phone_Number = ?, status = ? WHERE staff_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssi", $specialty, $phone, $status, $staff_id);

if ($stmt->execute()) {
    // Build audit log details
    $changes = [];
    if ($current_info['Specialty'] !== $specialty) {
        $changes[] = "Specialty: {$current_info['Specialty']} → {$specialty}";
    }
    if ($current_info['Phone_Number'] !== $phone) {
        $changes[] = "Phone: {$current_info['Phone_Number']} → {$phone}";
    }
    if ($current_info['status'] !== $status) {
        $changes[] = "Status: {$current_info['status']} → {$status}";
    }
    
    if (!empty($changes)) {
        $details = "Updated staff (ID: $staff_id) information: " . implode(", ", $changes);
        logActivity($current_user_id, "Update Staff Information", $details);
    }

    echo json_encode(['success' => true, 'message' => 'Staff information updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating staff information']);
}
