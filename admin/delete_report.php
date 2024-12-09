<?php
require '../session/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $reportId = $_POST['report_id'];

    // Get the report details from the database
    $query = "SELECT report_data FROM generated_reports WHERE report_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $reportId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $report = mysqli_fetch_assoc($result);

    if ($report) {
        $reportData = json_decode($report['report_data'], true);
        $filename = 'unit_occupancy_report_' . $reportData['overview']['report_period'] . '.csv';
        $filePath = '../reports/' . $filename;

        // Delete the file from the file system if it exists
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                // Successfully deleted the file
                $fileDeleted = true;
            } else {
                // Failed to delete the file
                $fileDeleted = false;
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete the report file']);
                exit;
            }
        } else {
            $fileDeleted = false;  // File not found, no need to delete
        }

        // Delete the report from the database
        $query = "DELETE FROM generated_reports WHERE report_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $reportId);

        if (mysqli_stmt_execute($stmt)) {
            $status = 'success';
            $message = 'Report deleted successfully';

            // Check if the directory is empty and delete it
            $directoryPath = '../reports';
            if (is_dir($directoryPath)) {
                $files = array_diff(scandir($directoryPath), array('.', '..'));

                if (empty($files)) {
                    // If the directory is empty, remove it
                    if (rmdir($directoryPath)) {
                        $message .= ' and directory removed';
                    } else {
                        $message .= ' but failed to remove directory';
                    }
                }
            }

            if (!$fileDeleted) {
                // Add a message if the file was not found or failed to delete
                $message .= ' (File not found or failed to delete)';
            }
        } else {
            $status = 'error';
            $message = 'Failed to delete report from the database';
        }

        // Return the response as JSON
        echo json_encode(['status' => $status, 'message' => $message]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Report not found']);
    }
}
?>
