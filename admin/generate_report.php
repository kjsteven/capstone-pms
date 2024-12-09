<?php
require '../session/db.php';
require 'UnitOccupancyReport.php'; // Include your class file

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_report') {
    $reportType = $_POST['report_type'];
    $reportMonth = $_POST['report_month'];
    $reportYear = $_POST['report_year'];
    $reportDate = date('Y-m-d H:i:s'); // Date when the report is generated

    try {
        // Check the report type and generate accordingly
        if ($reportType === 'Unit Occupancy Report') {
            $report = new UnitOccupancyReport($conn);
            $generatedReport = $report->generateReport($reportDate, $reportYear, $reportMonth);

            // Save the report to the database
            $reportSaved = $report->saveReportToDatabase($generatedReport);

            if ($reportSaved) {
                // Export the report to a CSV file
                $filename = $report->exportReportToCSV($generatedReport);

                // Return success response with the filename and report ID
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Report generated successfully',
                    'filename' => $filename,
                    'report_id' => $conn->insert_id // Assuming you have an auto-incrementing ID
                ]);
            } else {
                throw new Exception('Failed to save report to the database');
            }
        } else {
            // Handle other report types (e.g., Property Availability Report, etc.)
            throw new Exception('Unknown report type');
        }
    } catch (Exception $e) {
        // Catch errors and return failure
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
?>
