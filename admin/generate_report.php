<?php
// Enable strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering and ensure clean JSON output
ob_start();
ob_clean();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Include necessary files
require '../session/db.php';
require_once 'UnitOccupancyReport.php';

// Logging function
function logError($message, $context = []) {
    $log_file = '../logs/report_generation_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}\n";
    if (!empty($context)) {
        $log_entry .= "Context: " . json_encode($context) . "\n";
    }
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Error handling
try {
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception("Invalid request method");
    }

    // Validate input
    $valid_report_types = ['Unit Occupancy Report', 'Property Availability Report', 'Property Maintenance Report'];
    $report_type = $_POST['report_type'] ?? '';
    if (!in_array($report_type, $valid_report_types)) {
        http_response_code(400);
        throw new Exception("Invalid or missing report type");
    }

    // Initialize report generator
    if (!class_exists('UnitOccupancyReport')) {
        throw new Exception("UnitOccupancyReport class not found");
    }
    $report_generator = new UnitOccupancyReport($conn);

    // Generate the report
    $report_date = date('Y-m-d');
    $report_period = date('F Y');
    $report = $report_generator->generateReport($report_date, $report_period);

    // Save the report and export to CSV
    if (!$report_generator->saveReportToDatabase($report)) {
        throw new Exception("Failed to save the report to the database");
    }
    $filename = $report_generator->exportReportToCSV($report);
    $filepath = realpath('../reports/' . $filename);

    // Return response
    echo json_encode([
        'status' => 'success',
        'message' => 'Report generated successfully',
        'filename' => $filename,
        'filepath' => $filepath,
        'report_id' => mysqli_insert_id($conn)
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    logError($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_PRETTY_PRINT);
}

ob_end_flush();
exit;

?>
