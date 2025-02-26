<?php
require '../session/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $reportId = $_POST['report_id'];
    $response = ['status' => 'error', 'message' => ''];

    try {
        // First, get the file path from the database
        $query = "SELECT file_path, report_data FROM generated_reports WHERE report_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $reportId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $report = mysqli_fetch_assoc($result);

        if (!$report) {
            throw new Exception('Report not found in database');
        }

        // Debug log the report data
        error_log("Report data from database: " . print_r($report, true));

        // Try both file_path and constructed path from report_data
        $filePaths = [];
        
        // Try the file_path from database
        if (!empty($report['file_path'])) {
            $filePaths[] = '../reports/' . ltrim($report['file_path'], '/');
        }
        
        // Try to get filename from report_data
        $reportData = json_decode($report['report_data'], true);
        if (isset($reportData['filename'])) {
            $filePaths[] = '../reports/' . $reportData['filename'];
        }
        
        // Try with report period if available
        if (isset($reportData['overview']['report_period'])) {
            $filePaths[] = '../reports/unit_occupancy_report_' . $reportData['overview']['report_period'] . '.csv';
        }

        $fileDeleted = false;
        foreach ($filePaths as $filePath) {
            error_log("Attempting to delete file: " . $filePath);
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    error_log("Successfully deleted file: " . $filePath);
                    $fileDeleted = true;
                    break;
                } else {
                    error_log("Failed to delete file: " . $filePath);
                }
            } else {
                error_log("File does not exist: " . $filePath);
            }
        }

        if (!$fileDeleted) {
            error_log("Warning: No physical file was deleted");
        }

        // Delete the database record regardless of file deletion
        $deleteQuery = "DELETE FROM generated_reports WHERE report_id = ?";
        $stmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($stmt, 'i', $reportId);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to delete database record');
        }

        $response['status'] = 'success';
        $response['message'] = $fileDeleted ? 
            'Report and file deleted successfully' : 
            'Report deleted from database (file not found)';

    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
        error_log("Error deleting report: " . $e->getMessage());
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>