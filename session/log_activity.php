<?php
session_start();
require_once 'db.php';
require_once 'audit_trail.php';

// Check if request is POST and is JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset($_SERVER["CONTENT_TYPE"]) && 
    strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data['action']) && isset($data['details'])) {
        // Log the activity
        $result = logActivity(
            $_SESSION['user_id'],
            $data['action'],
            $data['details']
        );

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Activity logged successfully' : 'Failed to log activity'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data provided'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method or content type'
    ]);
}
?>
