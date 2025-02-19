<?php
require '../session/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['staff_id'])) {
    // First get current status
    $query = "SELECT status FROM staff WHERE staff_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $data['staff_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();

    // Determine new status based on current status
    $new_status = '';
    switch($current['status']) {
        case 'Available':
        case 'Busy':
        case 'Active':
            $new_status = 'Suspended';
            $message = 'Staff account has been suspended';
            break;
        case 'Suspended':
            $new_status = 'Active';
            $message = 'Staff account has been activated';
            break;
        default:
            $new_status = 'Suspended';
            $message = 'Staff account has been suspended';
    }
    
    $query = "UPDATE staff SET status = ? WHERE staff_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $new_status, $data['staff_id']);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'newStatus' => $new_status
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error updating staff status: ' . $stmt->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request data: staff_id is required'
    ]);
}
