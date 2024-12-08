<?php
require '../session/db.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Enable error reporting for detailed debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception("Invalid request method");
    }

    $report_id = $_POST['report_id'] ?? null;
    if (!$report_id) {
        http_response_code(400);
        throw new Exception("Missing or invalid report ID");
    }

    // Fetch the report to get the file path from the database
    $query = "SELECT file_path FROM generated_reports WHERE report_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement for fetching file path");
    }

    mysqli_stmt_bind_param($stmt, 'i', $report_id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to execute query to fetch file path");
    }

    $result = mysqli_stmt_get_result($stmt);
    $report = mysqli_fetch_assoc($result);

    if (!$report) {
        throw new Exception("Report not found in database");
    }

    // Get the relative file path from the database
    $file_path = $report['file_path'];

    // Debugging: Check the value of the file path
    error_log("Report File Path from DB: " . $file_path);

    // Define the base directory where the reports are stored
    $base_dir = realpath('../reports');  // Use your actual base directory path

    // Combine the base directory and file path to get the full absolute file path
    $full_file_path = $base_dir . DIRECTORY_SEPARATOR . $file_path;

    // Debugging: Check the full resolved file path
    error_log("Full File Path: " . $full_file_path);

    // Validate if the file exists
    if (file_exists($full_file_path)) {
        // Attempt to delete the file
        if (!unlink($full_file_path)) {
            throw new Exception("Failed to delete file: " . $full_file_path);
        }
        error_log("File deleted successfully: " . $full_file_path); // Confirm file deletion
    } else {
        throw new Exception("File does not exist at the path: " . $full_file_path);
    }

    // Delete the report from the database
    $delete_query = "DELETE FROM generated_reports WHERE report_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    if (!$delete_stmt) {
        throw new Exception("Failed to prepare statement for deleting the report");
    }

    mysqli_stmt_bind_param($delete_stmt, 'i', $report_id);
    if (!mysqli_stmt_execute($delete_stmt)) {
        throw new Exception("Failed to delete report from database");
    }

    echo json_encode(['status' => 'success', 'message' => 'Report deleted successfully']);

} catch (Exception $e) {
    // Log the exception message
    error_log("Error: " . $e->getMessage());

    // Return a more detailed error response
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
