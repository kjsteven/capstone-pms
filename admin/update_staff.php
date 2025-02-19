<?php
require '../session/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_POST['staff_id'];
    $specialty = $_POST['specialty'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];

    // Validate status
    $valid_statuses = ['Available', 'Busy', 'Active', 'Suspended'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit;
    }

    $query = "UPDATE staff SET Specialty = ?, Phone_Number = ?, status = ? WHERE staff_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $specialty, $phone, $status, $staff_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Staff information updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating staff information: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
