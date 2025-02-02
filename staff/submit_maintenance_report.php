<?php
// submit_maintenance_report.php

require_once('../vendor/tecnickcom/TCPDF/tcpdf.php'); // Make sure to include TCPDF library
require '../session/db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get staff details
    $staffId = $_SESSION['staff_id'];
    $query = "SELECT name FROM staff WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $staffId);
    $stmt->execute();
    $result = $stmt->get_result();
    $staffName = $result->fetch_assoc()['name'];

    // Get form data
    $requestId = $_POST['requestId'];
    $status = $_POST['status'];
    $issueDescription = $_POST['issueDescription'];
    $actionTaken = $_POST['actionTaken'];
    $maintenanceCost = $_POST['maintenanceCost'];
    $completionDate = $_POST['completionDate'];

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Maintenance System');
    $pdf->SetAuthor($staffName);
    $pdf->SetTitle('Maintenance Report');

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    // Set Logo
    $pdf->Image('../images/logo.png', 15, 10, 30);

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

    // Handle image uploads
    if (!empty($_FILES['uploadImages']['name'][0])) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, 'Maintenance Images:', 0, 1);
        $pdf->Ln(5);

        foreach($_FILES['uploadImages']['tmp_name'] as $key => $tmp_name) {
            $img_info = getimagesize($tmp_name);
            if ($img_info !== false) {
                $pdf->Image($tmp_name, null, null, 100);
                $pdf->Ln(5);
            }
        }
    }

    // Generate PDF filename
    $pdfFileName = 'maintenance_report_' . $requestId . '_' . date('Ymd_His') . '.pdf';
    $pdfPath = '../reports/maintenance_reports/' . $pdfFileName;

    // Save PDF
    $pdf->Output($pdfPath, 'F');

    // Update database with report information and PDF path
    $query = "UPDATE maintenance_requests 
              SET status = ?, 
                  report_pdf = ?,
                  completion_date = ?,
                  maintenance_cost = ?,
                  action_taken = ?
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssdsi', 
        $status,
        $pdfFileName,
        $completionDate,
        $maintenanceCost,
        $actionTaken,
        $requestId
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>