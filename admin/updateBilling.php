<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require '../session/audit_trail.php';

// Prevent any output before headers
ob_clean();
start_secure_session();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Validate inputs
$request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$cost = isset($_POST['cost']) ? (float)$_POST['cost'] : 0;

if (!$request_id || $cost <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request ID or cost']);
    exit;
}

try {
    // Update the maintenance cost
    $stmt = $conn->prepare("UPDATE maintenance_requests SET maintenance_cost = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    
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
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update maintenance cost: ' . $e->getMessage()
    ]);
}

$conn->close();
