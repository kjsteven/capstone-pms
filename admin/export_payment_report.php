<?php
// Turn off error reporting to prevent output before headers
error_reporting(0);

// Start output buffer
ob_start();

require_once '../session/session_manager.php';
require '../session/db.php';

// Start secure session
start_secure_session();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    ob_clean(); // Clear any output
    
    // Redirect to login or show error message
    header('Content-Type: text/html');
    echo '<div style="padding: 20px; text-align: center; font-family: Arial, sans-serif;">
            <h2>Unauthorized Access</h2>
            <p>You need to be logged in as an administrator to access this feature.</p>
            <p><a href="../authentication/login.php">Go to Login</a></p>
          </div>';
    exit();
}

try {
    // Get report type filter
    $reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'all';
    
    // Get date range
    $fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d', strtotime('-30 days'));
    $toDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
    
    // Get export format (simplified to just CSV or Excel)
    $format = isset($_GET['format']) ? strtolower($_GET['format']) : 'csv';
    
    // Get admin name for the report
    $adminQuery = "SELECT name FROM users WHERE user_id = ?";
    $adminStmt = $conn->prepare($adminQuery);
    $adminStmt->bind_param("i", $_SESSION['user_id']);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    $adminName = ($adminResult->num_rows > 0) ? $adminResult->fetch_assoc()['name'] : 'Admin';
    
    // Build query based on filters
    $query = "SELECT p.payment_id, p.amount, p.payment_date, p.status, p.gcash_number, 
                     p.reference_number, p.created_at, p.updated_at,
                     u.name AS tenant_name, pr.unit_no
              FROM payments p
              JOIN tenants t ON p.tenant_id = t.tenant_id
              JOIN users u ON t.user_id = u.user_id
              JOIN property pr ON t.unit_rented = pr.unit_id
              WHERE p.payment_date BETWEEN ? AND ?";
    
    $params = [$fromDate, $toDate];
    $types = "ss";
    
    // Add status filter if not "all"
    if ($reportType !== 'all') {
        $statusMap = [
            'pending' => 'Pending',
            'received' => 'Received',
            'rejected' => 'Rejected'
        ];
        
        if (isset($statusMap[$reportType])) {
            $query .= " AND p.status = ?";
            $params[] = $statusMap[$reportType];
            $types .= "s";
        }
    }
    
    $query .= " ORDER BY p.payment_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    
    // Get total amount received
    $totalReceivedQuery = "SELECT SUM(amount) AS total 
                          FROM payments 
                          WHERE status = 'Received' 
                          AND payment_date BETWEEN ? AND ?";
    
    $totalStmt = $conn->prepare($totalReceivedQuery);
    $totalStmt->bind_param("ss", $fromDate, $toDate);
    $totalStmt->execute();
    $totalResult = $totalStmt->get_result();
    $totalRow = $totalResult->fetch_assoc();
    $totalReceived = $totalRow['total'] ?? 0;
    
    // Make sure to clear any output before generating exports
    ob_clean();
    
    // Generate the report based on format
    if ($format === 'excel') {
        // For Excel, we'll use CSV format which is simpler
        exportCSV($payments, $fromDate, $toDate, $reportType, $totalReceived, $adminName);
    } else {
        // Default is CSV
        exportCSV($payments, $fromDate, $toDate, $reportType, $totalReceived, $adminName);
    }
    
} catch (Exception $e) {
    ob_clean(); // Clear any output
    
    header('Content-Type: text/html');
    echo '<div style="padding: 20px; text-align: center; font-family: Arial, sans-serif;">
            <h2>Error Generating Report</h2>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
            <p><a href="javascript:history.back()">Go Back</a></p>
          </div>';
    exit();
}

// Function to export data as CSV (simpler than Excel)
function exportCSV($payments, $fromDate, $toDate, $reportType, $totalReceived, $exportedBy) {
    // Create a temporary file to hold the CSV data
    $tempFile = fopen('php://temp', 'w');
    
    // Add UTF-8 BOM at the beginning of the file to help with encoding
    fputs($tempFile, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Add report metadata
    $reportTitle = ucfirst($reportType === 'all' ? 'All Payments' : $reportType . ' Payments') . ' Report';
    fputcsv($tempFile, ['Report Title:', $reportTitle]);
    fputcsv($tempFile, ['Date Range:', date('M d, Y', strtotime($fromDate)) . ' to ' . date('M d, Y', strtotime($toDate))]);
    fputcsv($tempFile, ['Total Received:', 'PHP ' . number_format($totalReceived, 2)]); // Changed ₱ to PHP
    fputcsv($tempFile, ['Exported By:', $exportedBy]);
    fputcsv($tempFile, ['Export Date:', date('M d, Y h:i A')]);
    
    // Add a blank row
    fputcsv($tempFile, []);
    
    // Add column headers
    fputcsv($tempFile, [
        'Payment ID', 
        'Tenant', 
        'Unit', 
        'Amount (PHP)', 
        'Payment Method', 
        'Reference Number', 
        'Payment Date', 
        'Status', 
        'Created At', 
        'Last Updated'
    ]);
    
    // Add data rows
    foreach ($payments as $payment) {
        $paymentId = 'PAY-' . str_pad($payment['payment_id'], 5, '0', STR_PAD_LEFT);
        $method = !empty($payment['gcash_number']) ? 'GCash' : 'Cash';
        
        // Fix reference number by adding a single quote prefix to force Excel to treat it as text
        $reference = !empty($payment['reference_number']) ? "'".$payment['reference_number'] : 'N/A';
        
        fputcsv($tempFile, [
            $paymentId,
            $payment['tenant_name'],
            $payment['unit_no'],
            number_format($payment['amount'], 2), // Remove PHP symbol, just use the number
            $method,
            $reference,  // Modified to prevent scientific notation
            date('M d, Y', strtotime($payment['payment_date'])),
            $payment['status'],
            date('M d, Y h:i A', strtotime($payment['created_at'])),
            date('M d, Y h:i A', strtotime($payment['updated_at']))
        ]);
    }
    
    // Add total row at the bottom
    fputcsv($tempFile, []);
    fputcsv($tempFile, ['', '', 'Total Received:', 'PHP ' . number_format($totalReceived, 2)]); // Changed ₱ to PHP
    
    // Reset the file pointer to the beginning
    rewind($tempFile);
    
    // Get the file contents
    $csvData = '';
    while (!feof($tempFile)) {
        $csvData .= fread($tempFile, 8192);
    }
    
    // Close the file
    fclose($tempFile);
    
    // Generate a filename based on the report type and date
    $reportTypeText = $reportType === 'all' ? 'all_payments' : $reportType . '_payments';
    $filename = 'payment_report_' . $reportTypeText . '_' . date('Y-m-d') . '.csv';
    
    // Set headers for file download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output the CSV data
    echo $csvData;
    exit;
}
?>
