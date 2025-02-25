<?php


date_default_timezone_set('Asia/Manila'); 

require_once '../session/session_manager.php';
require '../session/db.php';
require 'UnitOccupancyReport.php';


session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_report') {
    $reportType = $_POST['report_type'];
    $reportMonth = $_POST['report_month'];
    $reportYear = $_POST['report_year'];
    $reportDate = date('Y-m-d H:i:s');

    // Fetch the user_id from the session
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User is not logged in.'
        ]);
        exit;
    }

    // Query to get the user's name
    $userQuery = "SELECT name FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();

    if ($userResult->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found.'
        ]);
        exit;
    }

    $userRow = $userResult->fetch_assoc();
    $generatedBy = $userRow['name'];

    try {
        if ($reportType === 'Unit Occupancy Report') {
            $report = new UnitOccupancyReport($conn);
            $generatedReport = $report->generateReport($reportDate, $reportYear, $reportMonth, $generatedBy);

            $filename = $report->exportReportToCSV($generatedReport);
            $filePath = '../reports/' . $filename;

            $reportSaved = $report->saveReportToDatabase($generatedReport, $filePath);

            if ($reportSaved) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Report generated successfully',
                    'filename' => $filename,
                    'report_id' => $conn->insert_id
                ]);
            } else {
                throw new Exception('Failed to save report to the database');
            }
        } else {
            throw new Exception('Unknown report type');
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
?>