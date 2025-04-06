<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require '../session/audit_trail.php';

start_secure_session();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authorized']);
    exit;
}

$request_id = $_POST['request_id'] ?? null;
$cost = $_POST['cost'] ?? null;

if (!$request_id || !$cost) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    // Update the maintenance cost
    $stmt = $conn->prepare("UPDATE maintenance_requests SET maintenance_cost = ? WHERE id = ?");
    $stmt->bind_param("di", $cost, $request_id);
    
    if ($stmt->execute()) {
        // Log the activity
        $action_details = "Updated maintenance cost for request #$request_id to ₱$cost";
        logActivity($_SESSION['user_id'], "Update Maintenance Cost", $action_details);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Maintenance cost updated successfully',
            'cost' => $cost
        ]);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update maintenance cost: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
