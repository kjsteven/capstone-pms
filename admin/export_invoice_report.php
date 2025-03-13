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
    // Get data from POST request
    $dateRange = isset($_POST['date_range']) ? $_POST['date_range'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $tenantId = isset($_POST['tenant_id']) ? $_POST['tenant_id'] : '';
    
    // Parse date range (format: YYYY-MM-DD to YYYY-MM-DD)
    $dates = explode(' to ', $dateRange);
    $fromDate = isset($dates[0]) && !empty($dates[0]) ? trim($dates[0]) : date('Y-m-d', strtotime('-30 days'));
    $toDate = isset($dates[1]) && !empty($dates[1]) ? trim($dates[1]) : date('Y-m-d');
    
    // Get admin name for the report
    $adminQuery = "SELECT name FROM users WHERE user_id = ?";
    $adminStmt = $conn->prepare($adminQuery);
    $adminStmt->bind_param("i", $_SESSION['user_id']);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    $adminName = ($adminResult->num_rows > 0) ? $adminResult->fetch_assoc()['name'] : 'Admin';
    
    // Build query based on filters
    $query = "SELECT i.id, i.tenant_id, i.amount, i.issue_date, i.due_date, i.status, 
                     i.description, i.created_at, i.updated_at, i.email_sent,
                     u.name AS tenant_name, p.unit_no
              FROM invoices i
              JOIN tenants t ON i.tenant_id = t.tenant_id
              JOIN users u ON t.user_id = u.user_id
              JOIN property p ON t.unit_rented = p.unit_id
              WHERE i.issue_date BETWEEN ? AND ?";
    
    $params = [$fromDate, $toDate];
    $types = "ss";
    
    // Add status filter if provided
    if (!empty($status)) {
        if ($status === 'paid') {
            $query .= " AND i.status = 'paid'";
        } elseif ($status === 'unpaid') {
            $query .= " AND i.status = 'unpaid' AND i.due_date >= CURDATE()";
        } elseif ($status === 'overdue') {
            $query .= " AND i.status = 'unpaid' AND i.due_date < CURDATE()";
        }
    }
    
    // Add tenant filter if provided
    if (!empty($tenantId)) {
        $query .= " AND i.tenant_id = ?";
        $params[] = $tenantId;
        $types .= "i";
    }
    
    $query .= " ORDER BY i.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $invoices = [];
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
    
    // Get total amount for paid invoices
    $totalPaidQuery = "SELECT SUM(amount) AS total 
                       FROM invoices 
                       WHERE status = 'paid' 
                       AND issue_date BETWEEN ? AND ?";
    
    $totalStmt = $conn->prepare($totalPaidQuery);
    $totalStmt->bind_param("ss", $fromDate, $toDate);
    $totalStmt->execute();
    $totalResult = $totalStmt->get_result();
    $totalRow = $totalResult->fetch_assoc();
    $totalPaid = $totalRow['total'] ?? 0;

    // Get total amount for unpaid invoices
    $totalUnpaidQuery = "SELECT SUM(amount) AS total 
                         FROM invoices 
                         WHERE status = 'unpaid' 
                         AND issue_date BETWEEN ? AND ?";
    
    $totalUnpaidStmt = $conn->prepare($totalUnpaidQuery);
    $totalUnpaidStmt->bind_param("ss", $fromDate, $toDate);
    $totalUnpaidStmt->execute();
    $totalUnpaidResult = $totalUnpaidStmt->get_result();
    $totalUnpaidRow = $totalUnpaidResult->fetch_assoc();
    $totalUnpaid = $totalUnpaidRow['total'] ?? 0;
    
    // Make sure to clear any output before generating exports
    ob_clean();
    
    // Export as CSV
    exportCSV($invoices, $fromDate, $toDate, $status, $totalPaid, $totalUnpaid, $adminName);
    
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

// Function to export data as CSV
function exportCSV($invoices, $fromDate, $toDate, $reportType, $totalPaid, $totalUnpaid, $exportedBy) {
    // Create a temporary file to hold the CSV data
    $tempFile = fopen('php://temp', 'w');
    
    // Add UTF-8 BOM at the beginning of the file to help with encoding
    fputs($tempFile, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Add report metadata
    $reportTitleMap = [
        'all' => 'All Invoices',
        'paid' => 'Paid Invoices',
        'unpaid' => 'Unpaid Invoices',
        'overdue' => 'Overdue Invoices'
    ];
    
    $reportTitle = $reportTitleMap[$reportType] ?? 'Invoice Report';
    
    fputcsv($tempFile, ['Report Title:', $reportTitle]);
    fputcsv($tempFile, ['Date Range:', date('M d, Y', strtotime($fromDate)) . ' to ' . date('M d, Y', strtotime($toDate))]);
    fputcsv($tempFile, ['Total Paid Amount:', 'PHP ' . number_format($totalPaid, 2)]);
    fputcsv($tempFile, ['Total Unpaid Amount:', 'PHP ' . number_format($totalUnpaid, 2)]);
    fputcsv($tempFile, ['Exported By:', $exportedBy]);
    fputcsv($tempFile, ['Export Date:', date('M d, Y h:i A')]);
    
    // Add a blank row
    fputcsv($tempFile, []);
    
    // Add column headers
    fputcsv($tempFile, [
        'Invoice #', 
        'Tenant', 
        'Unit', 
        'Amount (PHP)',
        'Issue Date',
        'Due Date', 
        'Status', 
        'Description', 
        'Email Sent',
        'Created At', 
        'Last Updated'
    ]);
    
    // Add data rows
    foreach ($invoices as $invoice) {
        $invoiceId = 'INV-' . str_pad($invoice['id'], 5, '0', STR_PAD_LEFT);
        
        // Determine status text
        $status = $invoice['status'];
        if ($status === 'unpaid' && strtotime($invoice['due_date']) < time()) {
            $status = 'Overdue';
        } elseif ($status === 'unpaid') {
            $status = 'Unpaid';
        } else {
            $status = 'Paid';
        }
        
        // Format email sent status
        $emailSent = $invoice['email_sent'] ? 'Yes' : 'No';
        
        fputcsv($tempFile, [
            $invoiceId,
            $invoice['tenant_name'],
            $invoice['unit_no'],
            number_format($invoice['amount'], 2),
            date('M d, Y', strtotime($invoice['issue_date'])),
            date('M d, Y', strtotime($invoice['due_date'])),
            $status,
            $invoice['description'],
            $emailSent,
            date('M d, Y h:i A', strtotime($invoice['created_at'])),
            date('M d, Y h:i A', strtotime($invoice['updated_at']))
        ]);
    }
    
    // Add summary rows at the bottom
    fputcsv($tempFile, []);
    fputcsv($tempFile, ['', '', 'Total Paid Amount:', 'PHP ' . number_format($totalPaid, 2)]);
    fputcsv($tempFile, ['', '', 'Total Unpaid Amount:', 'PHP ' . number_format($totalUnpaid, 2)]);
    fputcsv($tempFile, ['', '', 'Total Amount:', 'PHP ' . number_format($totalPaid + $totalUnpaid, 2)]);
    
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
    $reportTypeText = str_replace(' ', '_', strtolower($reportTitle));
    $filename = 'invoice_report_' . $reportTypeText . '_' . date('Y-m-d') . '.csv';
    
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
