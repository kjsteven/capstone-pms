<?php
// Force no output before our JSON
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering immediately
ob_start();

// Check for any existing output
$existing_output = ob_get_contents();
if (!empty($existing_output)) {
    // Log the unexpected output for debugging
    file_put_contents('debug_output.txt', $existing_output);
    // Clean the buffer
    ob_clean();
}

// Set the content type header before any other output
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Postpone loading includes until after headers are sent
try {
    require_once '../session/session_manager.php';
    require '../session/db.php';

    // Initialize session
    start_secure_session();

    // Check if user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        throw new Exception('Unauthorized access');
    }
    
    // Validate payment ID
    if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Missing or invalid payment ID');
    }
    
    $payment_id = (int)$_GET['id'];
    
    // Get admin name from the database using session user_id
    $adminQuery = "SELECT name FROM users WHERE user_id = ?";
    $adminStmt = $conn->prepare($adminQuery);
    $adminStmt->bind_param("i", $_SESSION['user_id']);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    $adminName = ($adminResult->num_rows > 0) ? $adminResult->fetch_assoc()['name'] : 'Admin';
    
    // Simplified query without complex JOIN
    $query = "SELECT p.*, 
                    t.user_id, 
                    u.name AS tenant_name, 
                    pr.unit_no
            FROM payments p
            JOIN tenants t ON p.tenant_id = t.tenant_id
            JOIN users u ON t.user_id = u.user_id
            JOIN property pr ON t.unit_rented = pr.unit_id
            WHERE p.payment_id = ?";
    
    // Prepare and execute query
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Database query preparation failed: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $payment_id);
    if (!$stmt->execute()) {
        throw new Exception('Database query execution failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Payment not found');
    }
    
    $payment = $result->fetch_assoc();
    
    // Add admin info for display
    $payment['processed_by_name'] = $adminName;
    $payment['processed_by_id'] = $_SESSION['user_id'];
    
    // Make sure no other output has been generated
    ob_clean();
    
    // Use simple array to minimize potential encoding issues
    echo json_encode([
        'success' => true,
        'payment' => $payment
    ], JSON_NUMERIC_CHECK);
    
} catch (Exception $e) {
    // Make sure no other output has been generated
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_NUMERIC_CHECK);
}

// End output buffer and flush
ob_end_flush();
exit; // Ensure no more output is generated
?>
