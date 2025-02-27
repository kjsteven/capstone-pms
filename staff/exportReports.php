<?php
require '../session/db.php';
require_once '../session/session_manager.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['staff_id'])) {
    header('Location: ../authentication/stafflogin.php');
    exit();
}

$staff_id = $_SESSION['staff_id'];

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="maintenance_reports_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Get all completed maintenance reports for the staff member
$reports_query = "SELECT 
    mr.id,
    mr.unit,
    p.unit_type,
    mr.issue,
    mr.description,
    mr.service_date,
    mr.completion_date,
    mr.maintenance_cost,
    mr.status
FROM maintenance_requests mr
JOIN property p ON mr.unit = p.unit_no
WHERE mr.assigned_to = ? 
AND mr.status = 'Completed'
ORDER BY mr.completion_date DESC";

$stmt = $conn->prepare($reports_query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$reports = $stmt->get_result();

// Get staff name for the report header
$staff_query = "SELECT Name AS staff_name FROM staff WHERE staff_id = ?";
$staff_stmt = $conn->prepare($staff_query);
$staff_stmt->bind_param("i", $staff_id);
$staff_stmt->execute();
$staff_result = $staff_stmt->get_result();
$staff_name = ($staff_result->num_rows > 0) ? $staff_result->fetch_assoc()['staff_name'] : 'Staff Member';

// Create file handle for output
$output = fopen('php://output', 'w');

// Add title rows
fputcsv($output, ['Maintenance Reports']);
fputcsv($output, ['Generated on:', date('F d, Y'), 'Staff:', $staff_name]);
fputcsv($output, []); // Empty line for spacing

// Add column headers
fputcsv($output, [
    'Request ID',
    'Unit',
    'Unit Type',
    'Issue',
    'Description',
    'Service Date',
    'Completion Date',
    'Cost (PHP)',
    'Status'
]);

// Add data rows
$total_cost = 0;

while ($row = $reports->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['unit'],
        $row['unit_type'],
        $row['issue'],
        $row['description'],
        date('Y-m-d', strtotime($row['service_date'])),
        date('Y-m-d', strtotime($row['completion_date'])),
        'Php ' . number_format($row['maintenance_cost'], 2),
        $row['status']
    ]);
    
    $total_cost += $row['maintenance_cost'];
}

// Add empty line and total
fputcsv($output, []); // Empty line for spacing
fputcsv($output, ['', '', '', '', '', '', 'Total Maintenance Cost:', 'Php ' . number_format($total_cost, 2)]);

fclose($output);
exit;
?>
