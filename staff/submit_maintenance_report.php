<?php
// Start output buffering right at the beginning
ob_start();

// Set proper headers
header('Content-Type: application/json');

// Disable error display but log them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../error.log');

try {
    // Check if TCPDF exists before requiring it
    if (!file_exists('../vendor/tecnickcom/tcpdf/tcpdf.php')) {
        throw new Exception('TCPDF library not found');
    }
    
    require '../vendor/tecnickcom/tcpdf/tcpdf.php';
    
    // Check if database connection file exists
    if (!file_exists('../session/db.php')) {
        throw new Exception('Database configuration file not found');
    }
    
    require '../session/db.php';

    session_start();

    if (!isset($_SESSION['staff_id'])) {
        throw new Exception('Not authenticated');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get staff details
    $staffId = $_SESSION['staff_id'];
    $query = "SELECT Name FROM staff WHERE staff_id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $staffId);
    $stmt->execute();
    $result = $stmt->get_result();
    $staffData = $result->fetch_assoc();
    
    if (!$staffData || !isset($staffData['Name'])) {
        throw new Exception('Staff not found');
    }
    
    $staffName = $staffData['Name'];

    // Validate form data
    if (!isset($_POST['requestId']) || !isset($_POST['status']) || !isset($_POST['actionTaken'])) {
        throw new Exception('Missing required fields');
    }

    // Get form data
    $requestId = $_POST['requestId'];
    $status = $_POST['status'];
    $issueDescription = $_POST['modalDescription']; // Using the modal field
    $actionTaken = $_POST['actionTaken'];
    $materialsUsed = $_POST['materialsUsed'];
    $completionDate = $_POST['completionDate'];

    // Fix path handling - use clean absolute paths
    $projectRoot = realpath(__DIR__ . '/..');  // Get clean path without ../
    
    // Create reports directory with clean absolute path
    $reportsBaseDir = $projectRoot . DIRECTORY_SEPARATOR . 'reports';
    if (!file_exists($reportsBaseDir)) {
        if (!mkdir($reportsBaseDir, 0777, true)) {
            throw new Exception('Failed to create reports directory: ' . $reportsBaseDir);
        }
        chmod($reportsBaseDir, 0777);
    }

    // Create maintenance_reports directory with clean absolute path
    $pdfDir = $reportsBaseDir . DIRECTORY_SEPARATOR . 'maintenance_reports';
    if (!file_exists($pdfDir)) {
        if (!mkdir($pdfDir, 0777, true)) {
            throw new Exception('Failed to create maintenance_reports directory: ' . $pdfDir);
        }
        chmod($pdfDir, 0777);
    }

    // Debug logging
    error_log('Project root: ' . $projectRoot);
    error_log('PDF directory: ' . $pdfDir);

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Maintenance System');
    $pdf->SetAuthor($staffName);
    $pdf->SetTitle('Maintenance Report');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    // Fix logo path and update size
    $logoPath = $projectRoot . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo.png';
    if (file_exists($logoPath)) {
        // Reduced logo size from 20mm to 15mm width, and positioned slightly higher
        $pdf->Image($logoPath, 15, 8, 15);
    }

    // Set font
    $pdf->SetFont('helvetica', 'B', 16);

    // Title - adjusted spacing for smaller logo
    $pdf->Cell(0, 6, 'Maintenance Report', 0, 1, 'C');
    $pdf->Ln(3); // Reduced spacing after title due to smaller logo

    // Report details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 7, 'Report Details:', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    // Create details array with materials used instead of maintenance cost
    $details = array(
        'Date Generated' => date('F j, Y'),
        'Staff Name' => $staffName,
        'Unit No.' => $_POST['modalUnit'],
        'Service Date' => $_POST['modalServiceDate'],
        'Issue Type' => $_POST['modalIssue'],
        'Status' => $status,
        'Completion Date' => date('F j, Y', strtotime($completionDate))
    );

    // Add details to PDF
    foreach($details as $key => $value) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(50, 7, $key . ':', 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 7, $value, 0);
        $pdf->Ln();
    }

    $pdf->Ln(5);

    // Issue Description
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, 'Issue Description:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(0, 7, $issueDescription, 0, 'L', false, 1, '', '', true, 0, false, false, 0, 'T', false);
    $pdf->Ln(5);

    // Add Materials Used section before Action Taken
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, 'Materials Used:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(180, 7, $materialsUsed, 0, 'L', false, 1, '', '', true, 0, false, false, 0, 'T', false);
    $pdf->Ln(5);

    // Action Taken
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, 'Action Taken:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(180, 7, $actionTaken, 0, 'L', false, 1, '', '', true, 0, false, false, 0, 'T', false);
    $pdf->Ln(5);

    // Add Materials Official Receipt with fixed dimensions
    if (!empty($_FILES['receiptImage']['tmp_name'])) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'Materials Official Receipt:', 0, 1);
        $pdf->Ln(5);

        $tmp_name = $_FILES['receiptImage']['tmp_name'];
        if (is_uploaded_file($tmp_name)) {
            $img_info = getimagesize($tmp_name);
            if ($img_info !== false) {
                // Fixed dimensions for receipt
                $fixed_width = 160; // mm
                $fixed_height = 100; // mm
                
                // Center the receipt image
                $x = (210 - $fixed_width) / 2; // 210 is A4 width in mm
                $y = $pdf->GetY(); // Get current Y position
                
                // Add image with fixed dimensions
                $pdf->Image($tmp_name, $x, $y, $fixed_width, $fixed_height, '', '', '', false, 300, '', false, false, 0, 'CM');
                $pdf->Ln($fixed_height + 10); // Add space after image
            }
        }
    }

    // Handle maintenance images with fixed dimensions
    if (!empty($_FILES['uploadImages']['name'][0])) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'Maintenance Images:', 0, 1);
        $pdf->Ln(5);

        // Fixed dimensions for maintenance images
        $fixed_width = 160; // mm
        $fixed_height = 120; // mm
        
        foreach($_FILES['uploadImages']['tmp_name'] as $key => $tmp_name) {
            if (is_uploaded_file($tmp_name)) {
                $img_info = getimagesize($tmp_name);
                if ($img_info !== false) {
                    // Check if adding another image would exceed page height
                    if ($pdf->GetY() + $fixed_height > 270) { // 270mm is approximate safe height for A4
                        $pdf->AddPage(); // Add new page if needed
                        $pdf->Ln(5);
                    }
                    
                    // Center the image
                    $x = (210 - $fixed_width) / 2; // 210 is A4 width in mm
                    $y = $pdf->GetY(); // Get current Y position
                    
                    // Add image with fixed dimensions
                    $pdf->Image($tmp_name, $x, $y, $fixed_width, $fixed_height, '', '', '', false, 300, '', false, false, 0, 'CM');
                    $pdf->Ln($fixed_height + 10); // Add space after image
                }
            }
        }
    }

    // Generate clean PDF path
    $pdfFileName = 'maintenance_report_' . intval($requestId) . '_' . date('Ymd_His') . '.pdf';
    $pdfPath = $pdfDir . DIRECTORY_SEPARATOR . $pdfFileName;

    // Debug logging
    error_log('Attempting to save PDF at: ' . $pdfPath);

    // Verify directory is writable
    if (!is_writable(dirname($pdfPath))) {
        throw new Exception('Directory is not writable: ' . dirname($pdfPath));
    }

    // Save PDF with error checking
    $result = $pdf->Output($pdfPath, 'F');
    if ($result === false) {
        throw new Exception('TCPDF Output failed');
    }

    // Verify file was created with additional checks
    clearstatcache(true, $pdfPath);
    if (!file_exists($pdfPath)) {
        throw new Exception('PDF file was not created at: ' . $pdfPath);
    }
    if (!is_readable($pdfPath)) {
        throw new Exception('PDF file was created but is not readable: ' . $pdfPath);
    }

    // Set proper permissions for the file
    chmod($pdfPath, 0644);

    // Update database with additional error checking
    $query = "UPDATE maintenance_requests 
             SET status = ?, 
                 report_pdf = ?,
                 completion_date = ?,
                 materials_used = ?,
                 action_taken = ?
             WHERE id = ? AND assigned_to = ?";

    if (!$stmt = $conn->prepare($query)) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }

    if (!$stmt->bind_param('sssssii', 
        $status,
        $pdfFileName,
        $completionDate,
        $materialsUsed,
        $actionTaken,
        $requestId,
        $staffId
    )) {
        throw new Exception('Parameter binding failed: ' . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Database update failed: ' . $stmt->error);
    }

    // Add audit logging
    require_once '../session/audit_trail.php';
    
    // Log the maintenance report submission
    $auditDetails = "Maintenance Report submitted for Request #$requestId. Status: $status";
    logActivity($staffId, "Submit Maintenance Report", $auditDetails);
    
    // Log PDF generation
    $pdfAuditDetails = "Generated maintenance report PDF: $pdfFileName";
    logActivity($staffId, "Generate PDF", $pdfAuditDetails);

    // Clear any existing output
    ob_clean();

    // Send JSON response
    echo json_encode([
        'success' => true,
        'message' => 'Report submitted successfully',
        'pdfPath' => $pdfFileName
    ]);
    exit;

} catch (Exception $e) {
    // Clear any output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Log error
    error_log('Maintenance Report Error: ' . $e->getMessage());
    error_log('Detailed error: ' . $e->getMessage() . ' | Stack trace: ' . $e->getTraceAsString());
    
    // Send JSON error response
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'staff_id' => $_SESSION['staff_id'] ?? 'not set',
            'error' => $e->getMessage()
        ]
    ]);
    exit;
} finally {
    // End and flush output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}
?>