<?php
require '../session/db.php';
start_secure_session();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['staff_id'])) {
        throw new Exception('Not authenticated');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $requestId = isset($_POST['requestId']) ? intval($_POST['requestId']) : 0;
    $staffId = $_SESSION['staff_id'];

    // Verify the request belongs to the staff member
    $query = "UPDATE maintenance_requests 
              SET archived = 1 
              WHERE id = ? AND assigned_to = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement');
    }

    $stmt->bind_param('ii', $requestId, $staffId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to archive request');
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Request not found or not authorized');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Request archived successfully'
    ]);

} catch (Exception $e) {
    error_log('Archive Request Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
