<?php
// Turn off all error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Set proper headers for JSON response
header('Content-Type: application/json');

// Start output buffering
ob_start();

try {
    require '../vendor/tecnickcom/tcpdf/tcpdf.php';
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
    $maintenanceCost = $_POST['maintenanceCost'];
    $completionDate = $_POST['completionDate'];

    // Create PDF directory if it doesn't exist
    $pdfDir = '../reports/maintenance_reports/';
    if (!file_exists($pdfDir)) {
        mkdir($pdfDir, 0777, true);
    }

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

    // Add logo if exists
    $logoPath = '../images/logo.png';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 15, 10, 30);
    }

    // Set font
    $pdf->SetFont('helvetica', 'B', 16);

    // Title
    $pdf->Cell(0, 10, 'Maintenance Report', 0, 1, 'C');
    $pdf->Ln(10);

    // Report details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 7, 'Report Details:', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    
    // Create details array
    $details = array(
        'Date Generated' => date('F j, Y'),
        'Staff Name' => $staffName,
        'Status' => $status,
        'Completion Date' => date('F j, Y', strtotime($completionDate)),
        'Maintenance Cost' => '₱ ' . number_format($maintenanceCost, 2)
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
    $pdf->MultiCell(0, 7, $issueDescription, 0);
    $pdf->Ln(5);

    // Action Taken
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, 'Action Taken:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(0, 7, $actionTaken, 0);
    $pdf->Ln(5);

    // Handle image uploads with proper error checking
    if (!empty($_FILES['uploadImages']['name'][0])) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'Maintenance Images:', 0, 1);
        $pdf->Ln(5);

        foreach($_FILES['uploadImages']['tmp_name'] as $key => $tmp_name) {
            if (is_uploaded_file($tmp_name)) {
                $img_info = getimagesize($tmp_name);
                if ($img_info !== false) {
                    // Calculate image dimensions to fit page
                    $max_width = 180; // Maximum width in mm
                    $width = $img_info[0] * 0.264583; // Convert pixels to mm
                    $height = $img_info[1] * 0.264583;
                    
                    if ($width > $max_width) {
                        $ratio = $max_width / $width;
                        $width = $max_width;
                        $height = $height * $ratio;
                    }
                    
                    $pdf->Image($tmp_name, null, null, $width, $height);
                    $pdf->Ln(5);
                }
            }
        }
    }

    // Generate unique filename
    $pdfFileName = 'maintenance_report_' . $requestId . '_' . date('Ymd_His') . '.pdf';
    $pdfPath = $pdfDir . $pdfFileName;

    // Save PDF
    $pdf->Output($pdfPath, 'F');

    // Update database with additional error checking
    $query = "UPDATE maintenance_requests 
             SET status = ?, 
                 report_pdf = ?,
                 completion_date = ?,
                 maintenance_cost = ?,
                 action_taken = ?
             WHERE id = ? AND assigned_to = ?";

    if (!$stmt = $conn->prepare($query)) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }

    if (!$stmt->bind_param('sssdsis', 
        $status,
        $pdfFileName,
        $completionDate,
        $maintenanceCost,
        $actionTaken,
        $requestId,
        $staffId
    )) {
        throw new Exception('Parameter binding failed: ' . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Database update failed: ' . $stmt->error);
    }

    // Clear any output buffers
    ob_clean();

    echo json_encode([
        'success' => true,
        'message' => 'Report submitted successfully',
        'pdfPath' => $pdfFileName
    ]);

} catch (Exception $e) {
    // Clear any output buffers
    ob_clean();
    
    error_log('Maintenance Report Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'staff_id' => $_SESSION['staff_id'] ?? 'not set',
            'error' => $e->getMessage()
        ]
    ]);
} finally {
    // End and flush output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}
?>