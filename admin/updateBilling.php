<?php
// Prevent any output buffering issues
if (ob_get_level()) ob_end_clean();

// Start new output buffering
ob_start();

session_start();

require_once '../session/session_manager.php';
require '../session/db.php';
require '../session/audit_trail.php';



// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');


// Ensure no whitespace or output before this point
try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authorized');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate inputs
    $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    $cost = isset($_POST['cost']) ? (float)$_POST['cost'] : 0;

    if (!$request_id || $cost <= 0) {
        throw new Exception('Invalid request ID or cost');
    }

    // Update the maintenance cost
    $stmt = $conn->prepare("UPDATE maintenance_requests SET maintenance_cost = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    
    $stmt->bind_param("di", $cost, $request_id);
    
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    // Log the activity
    $action_details = "Updated maintenance cost for request #$request_id to ₱$cost";
    logActivity($_SESSION['user_id'], "Update Maintenance Cost", $action_details);
    
    // Ensure proper encoding of the JSON response
    $response = [
        'status' => 'success',
        'message' => 'Maintenance cost updated successfully',
        'cost' => $cost
    ];
    
    // Clear any potential output before sending JSON
    if (ob_get_length()) ob_clean();
    
    echo json_encode($response);
    exit;

} catch (Exception $e) {
    // Clear any output before error response
    if (ob_get_length()) ob_clean();
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
