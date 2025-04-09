<?php
// Start output buffering to prevent any early output
ob_start();

// Disable error reporting to prevent headers already sent errors
error_reporting(0);

require_once '../session/session_manager.php';
require '../session/db.php';
require '../vendor/tecnickcom/tcpdf/tcpdf.php';

// Clear any existing output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Check if tenant_id is provided
if (!isset($_GET['tenant_id'])) {
    die('Tenant ID is required');
}

$tenant_id = (int)$_GET['tenant_id'];

// Fetch turnover details
$stmt = $conn->prepare("
    SELECT tt.*, t.user_id, u.name as tenant_name, u.email,
           p.unit_no, p.unit_id,
           s.name as staff_name
    FROM tenant_turnovers tt
    JOIN tenants t ON tt.tenant_id = t.tenant_id
    JOIN users u ON t.user_id = u.user_id
    JOIN property p ON t.unit_rented = p.unit_id
    LEFT JOIN staff s ON tt.staff_assigned = s.staff_id
    WHERE tt.tenant_id = ?
");
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$turnover = $result->fetch_assoc();

if (!$turnover) {
    die('Turnover record not found');
}

// Create new PDF document
class MYPDF extends TCPDF {
    public function Header() {
        // Add logo if you have one
        // $this->Image('path_to_logo.png', 15, 10, 30);
        
        $this->SetY(15); // Set Y position for header
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, 'Unit Turnover Report', 0, true, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, true, 'R');
        
        // Add a line separator
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(15); // Add space after line
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Property Management System');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Unit Turnover Report - Unit ' . $turnover['unit_no']);

// Set margins and headers
$pdf->SetMargins(15, 60, 15); // Increased top margin to prevent overlap
$pdf->SetHeaderMargin(20);
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Tenant and Property Information section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Tenant and Property Information', 0, 1);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// Create a reusable function for info rows
function addInfoRow($pdf, $label, $value) {
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(50, 7, $label, 0);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 7, $value, 0, 1);
}

// Add tenant information
addInfoRow($pdf, 'Tenant Name:', $turnover['tenant_name']);
addInfoRow($pdf, 'Unit Number:', $turnover['unit_no']);
addInfoRow($pdf, 'Email:', $turnover['email']);
$pdf->Ln(10);

// Turnover Details section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Turnover Details', 0, 1);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// Add turnover information
addInfoRow($pdf, 'Notification Date:', date('F d, Y', strtotime($turnover['notification_date'])));
addInfoRow($pdf, 'Inspection Date:', date('F d, Y', strtotime($turnover['inspection_date'])));
addInfoRow($pdf, 'Inspector:', $turnover['staff_name'] ?? 'N/A');
$pdf->Ln(5);

// Inspection Results section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Inspection Results', 0, 1);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// Add inspection results
addInfoRow($pdf, 'Cleanliness:', ucfirst($turnover['cleanliness_rating'] ?? 'N/A'));
addInfoRow($pdf, 'Damages:', ucfirst($turnover['damage_rating'] ?? 'N/A'));
addInfoRow($pdf, 'Equipment:', ucfirst($turnover['equipment_rating'] ?? 'N/A'));

// Add inspection report
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 10, 'Detailed Report:', 0, 1);
$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 7, $turnover['inspection_report'] ?? 'N/A', 0, 'L');
$pdf->Ln(10);

// Inspection Photos section
if (!empty($turnover['inspection_photos'])) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Inspection Photos', 0, 1);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(10);
    
    $photos = json_decode($turnover['inspection_photos'], true);
    if (is_array($photos)) {
        foreach ($photos as $index => $photo) {
            if (file_exists($photo)) {
                // Calculate image dimensions while maintaining aspect ratio
                list($width, $height) = getimagesize($photo);
                $ratio = $width / $height;
                $newWidth = min(160, $width); // Max width of 160
                $newHeight = $newWidth / $ratio;
                
                // Add photo number
                $pdf->SetFont('helvetica', '', 10);
                $pdf->Cell(0, 7, 'Photo ' . ($index + 1), 0, 1);
                
                // Add the image
                $pdf->Image($photo, 15, null, $newWidth, $newHeight, '', '', '', false, 300);
                $pdf->Ln($newHeight + 10); // Add space after image
                
                // Add new page if needed
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();
                }
            }
        }
    }
}

// Completion Details section (only if completed)
if ($turnover['status'] === 'completed') {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Completion Details', 0, 1);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(5);
    
    addInfoRow($pdf, 'Status:', 'Completed');
    addInfoRow($pdf, 'Completion Date:', date('F d, Y', strtotime($turnover['completion_date'])));
    
    if (!empty($turnover['completion_notes'])) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 10, 'Final Notes:', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 7, $turnover['completion_notes'], 0, 'L');
    }
}

// Before outputting the PDF, make sure all buffers are cleaned
if (ob_get_length()) ob_end_clean();

// Set headers for PDF download
header('Content-Type: application/pdf');
header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

// Output the PDF with forced download
$pdf->Output('Unit_Turnover_Report_' . $turnover['unit_no'] . '.pdf', 'D');
exit;
