<?php
// Ensure no output before headers
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output

// Set constant to allow access to included files
define('DirectAccess', true);

// Force JSON content type
header('Content-Type: application/json');

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Pass the year parameter to get_monthly_payments_data.php
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

try {
    // Include the monthly payments data file
    require_once 'get_monthly_payments_data.php';
    
    // The included file should set $response
    if (!isset($response)) {
        throw new Exception("Response data not generated");
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error processing request: ' . $e->getMessage()]);
}
?>
