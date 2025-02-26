<?php
require '../session/db.php';

header('Content-Type: application/json');

try {
    // Fetch saved reports
    $query = "SELECT report_id, report_type, report_date, report_period, file_path FROM generated_reports ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }

    $reports = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reports[] = [
            'id' => $row['report_id'],
            'type' => $row['report_type'],
            'period' => $row['report_period'],
            'date' => $row['report_date'],
            'filename' => basename($row['file_path']) // Extract only the file name
        ];
    }

    echo json_encode([
        'status' => 'success',
        'reports' => $reports
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Handle errors
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>