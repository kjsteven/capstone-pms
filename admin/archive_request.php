<?php
ob_start();
require_once '../session/session_manager.php';
require '../session/db.php';
require '../session/audit_trail.php';
start_secure_session();
// Clear any previous output
while (ob_get_level()) ob_end_clean();

// Set JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Request ID is required');
    }

    $request_id = intval($_GET['id']);
    $conn->begin_transaction();

    // Check if request exists
    $check_stmt = $conn->prepare("SELECT archived FROM maintenance_requests WHERE id = ?");
    $check_stmt->bind_param("i", $request_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $request = $result->fetch_assoc();
    $check_stmt->close();

    if (!$request) {
        throw new Exception('Request not found');
    }

    if ($request['archived'] == 1) {
        throw new Exception('Request is already archived');
    }

    // Archive the request
    $archive_stmt = $conn->prepare("UPDATE maintenance_requests SET archived = 1 WHERE id = ?");
    $archive_stmt->bind_param("i", $request_id);
    
    if ($archive_stmt->execute()) {
        // Log the archive action
        $user_id = $_SESSION['user_id'];
        $action_details = "Archived maintenance request #$request_id";
        logActivity($user_id, "Archive Maintenance Request", $action_details);

        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Request archived successfully'
        ]);
    } else {
        throw new Exception('Failed to archive request');
    }

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($archive_stmt)) $archive_stmt->close();
    if (isset($conn)) $conn->close();
    exit();
}
?>