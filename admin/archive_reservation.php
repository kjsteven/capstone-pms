<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../session/audit_trail.php';

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

if (!isset($data['reservation_ids']) || !is_array($data['reservation_ids'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("UPDATE reservations SET archived = 1 WHERE reservation_id = ?");
    $successCount = 0;

    foreach ($data['reservation_ids'] as $id) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $successCount++;
            // Log each archive action
            logActivity(
                $_SESSION['user_id'],
                'Archive Reservation',
                "Archived reservation ID: $id"
            );
        }
    }

    $conn->commit();

    if ($successCount === count($data['reservation_ids'])) {
        echo json_encode([
            'success' => true,
            'message' => $successCount > 1 
                ? "$successCount reservations archived successfully" 
                : "Reservation archived successfully"
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => "Archived $successCount out of " . count($data['reservation_ids']) . " reservations"
        ]);
    }
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error archiving reservations: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>