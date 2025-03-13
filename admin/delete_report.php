<?php
require '../session/db.php';
require '../session/audit_trail.php'; // Added audit trail requirement

session_start(); // Added session start to get user_id

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['report_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}

$reportId = (int) $_POST['report_id'];
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User is not logged in.'
    ]);
    exit;
}

try {
    // First get the filename to delete the physical file
    $query = "SELECT file_path, report_type, report_period FROM generated_reports WHERE report_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $reportId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Report not found");
    }
    
    $row = $result->fetch_assoc();
    $filePath = $row['file_path'];
    $reportType = $row['report_type'];
    $reportPeriod = $row['report_period'];
    
    // Then delete the record from the database
    $deleteQuery = "DELETE FROM generated_reports WHERE report_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $reportId);
    
    if ($stmt->execute()) {
        // Try to delete the physical file if it exists
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Log the activity
        $actionDetails = "Deleted $reportType (ID: $reportId) for period $reportPeriod";
        logActivity($userId, "Delete Report", $actionDetails);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Report deleted successfully'
        ]);
    } else {
        throw new Exception("Failed to delete report: " . $conn->error);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>