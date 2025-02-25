<?php
start_secure_session();
require '../session/audit_trail.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Not authorized');
}

// Validate file parameter
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('No file specified');
}

$filename = basename($_GET['file']);
$filepath = "../reports/maintenance_reports/" . $filename;

// Security checks
if (!file_exists($filepath)) {
    header('HTTP/1.1 404 Not Found');
    exit('File not found');
}

// Check if file is actually in the maintenance_reports directory
$realPath = realpath($filepath);
$reportsDir = realpath("../reports/maintenance_reports");
if (strpos($realPath, $reportsDir) !== 0) {
    header('HTTP/1.1 403 Forbidden');
    exit('Invalid file path');
}

// Simple download tracking using session
if (!isset($_SESSION['downloaded_files']) || !in_array($filename, $_SESSION['downloaded_files'])) {
    // Log the download action
    $user_id = $_SESSION['user_id'];
    $action_details = "Downloaded maintenance report: $filename";
    logActivity($user_id, "Download Report", $action_details);
    
    // Track this file as downloaded
    if (!isset($_SESSION['downloaded_files'])) {
        $_SESSION['downloaded_files'] = array();
    }
    $_SESSION['downloaded_files'][] = $filename;
}

// Clear output buffer
if (ob_get_level()) ob_end_clean();

// Set headers for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($filepath);
exit;
?>
