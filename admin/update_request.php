<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

require '../session/db.php';

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
    $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
    $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_STRING);

    $errors = [];
    if (!$request_id) $errors[] = "Invalid request ID";
    if (!$staff_id) $errors[] = "Invalid staff ID";
    if (!in_array($priority, ['high', 'medium', 'low'])) $errors[] = "Invalid priority level";

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors]);
        exit;
    }

    mysqli_begin_transaction($conn);

    try {
        // Update maintenance request
        $query = "UPDATE maintenance_requests SET assigned_to = ?, priority = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param('ssi', $staff_id, $priority, $request_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Update staff status
        $staffQuery = "UPDATE staff SET status = 'Busy' WHERE staff_id = ?";
        $staffStmt = $conn->prepare($staffQuery);
        if (!$staffStmt) {
            throw new Exception("Staff status update prepare failed: " . $conn->error);
        }
        $staffStmt->bind_param('i', $staff_id);
        if (!$staffStmt->execute()) {
            throw new Exception("Staff status update failed: " . $staffStmt->error);
        }

        mysqli_commit($conn);

        echo json_encode(['status' => 'success', 'message' => 'Request updated successfully']);
        $stmt->close();
        $staffStmt->close();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
