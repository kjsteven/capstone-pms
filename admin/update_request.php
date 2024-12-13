<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure JSON header is sent
header('Content-Type: application/json');

require '../session/db.php';

// Check database connection
if (!$conn) {
    die(json_encode([
        'status' => 'error', 
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log all received POST data for debugging
    error_log("Received POST data: " . print_r($_POST, true));

    // Validate input with more detailed checking
    $request_id = isset($_POST['request_id']) ? filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT) : null;
    $staff_id = isset($_POST['staff_id']) ? filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT) : null;

    // Detailed input validation
    $errors = [];
    if ($request_id === null || $request_id === false) {
        $errors[] = "Invalid or missing request_id";
    }
    if ($staff_id === null || $staff_id === false) {
        $errors[] = "Invalid or missing staff_id";
    }

    // If there are validation errors, return detailed error
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Invalid input parameters',
            'details' => $errors,
            'received_data' => $_POST
        ]);
        exit;
    }

    // Begin transaction to ensure atomicity
    mysqli_begin_transaction($conn);

    try {
        // Prepare the update query for the maintenance request
        $query = "UPDATE maintenance_requests SET assigned_to = ? WHERE id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Failed to prepare query: " . $conn->error);
        }

        // Bind parameters and execute the maintenance request update
        $stmt->bind_param('ii', $staff_id, $request_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute maintenance request update: " . $stmt->error);
        }

        // Update the staff status from 'Available' to 'Busy'
        $updateStaffQuery = "UPDATE staff SET status = 'Busy' WHERE staff_id = ?";
        $staffStmt = $conn->prepare($updateStaffQuery);

        if (!$staffStmt) {
            throw new Exception("Failed to prepare staff status update query: " . $conn->error);
        }

        $staffStmt->bind_param('i', $staff_id);
        if (!$staffStmt->execute()) {
            throw new Exception("Failed to update staff status: " . $staffStmt->error);
        }

        // Commit the transaction
        mysqli_commit($conn);

        echo json_encode([
            'status' => 'success', 
            'message' => 'Request updated and staff status changed successfully.'
        ]);

        // Close the statements
        $stmt->close();
        $staffStmt->close();
    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($conn);

        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error occurred: ' . $e->getMessage()
        ]);
    }

    // Close the connection
    $conn->close();
} else {
    // Method not POST
    http_response_code(405);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid request method'
    ]);
}
?>
