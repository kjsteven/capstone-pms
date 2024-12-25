<?php
require_once '../session/session_manager.php';
require '../session/db.php';

// Start session before any output
start_secure_session();

// Clear any previous output
ob_end_clean();

// Set JSON headers
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['reservation_ids']) || empty($data['reservation_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No reservation IDs provided']);
    exit();
}

try {
    $conn->begin_transaction();
    
    $query = "UPDATE reservations SET archived = 1 WHERE reservation_id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    foreach ($data['reservation_ids'] as $reservationId) {
        $stmt->bind_param("i", $reservationId);
        if (!$stmt->execute()) {
            throw new Exception("Failed to archive reservation ID {$reservationId}: " . $stmt->error);
        }
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Reservations archived successfully']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>