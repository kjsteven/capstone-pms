<?php
require_once '../session/session_manager.php';
start_secure_session();

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

// Clean the filename
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
