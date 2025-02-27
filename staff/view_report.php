<?php
require_once '../session/session_manager.php';
require '../session/db.php';

session_start();

try {
    if (!isset($_SESSION['staff_id'])) {
        die('Not authenticated');
    }

    $requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $staffId = $_SESSION['staff_id'];

    // Get the report file name
    $query = "SELECT report_pdf, unit FROM maintenance_requests 
              WHERE id = ? AND assigned_to = ? AND archived = 0";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement');
    }

    $stmt->bind_param('ii', $requestId, $staffId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row || !$row['report_pdf']) {
        die('Report not found');
    }

    $pdfPath = '../reports/maintenance_reports/' . $row['report_pdf'];
    
    if (!file_exists($pdfPath)) {
        die('PDF file not found');
    }

    // Get user agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);

    // For mobile devices, offer both view and download options
    if ($isMobile && isset($_GET['download'])) {
        // Force download for mobile devices when requested
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($pdfPath) . '"');
        header('Content-Length: ' . filesize($pdfPath));
        readfile($pdfPath);
        exit;
    } else if ($isMobile) {
        // Show mobile-friendly viewer with options
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>View Report - Unit <?php echo htmlspecialchars($row['unit']); ?></title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-100">
            <div class="container mx-auto px-4 py-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h1 class="text-xl font-bold mb-4">Maintenance Report - Unit <?php echo htmlspecialchars($row['unit']); ?></h1>
                    <div class="flex flex-col space-y-4">
                        <a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) . '&download=1'; ?>" 
                           class="bg-blue-500 text-white px-4 py-2 rounded text-center">
                            Download PDF
                        </a>
                        <a href="<?php echo htmlspecialchars($pdfPath); ?>" 
                           target="_blank"
                           class="bg-green-500 text-white px-4 py-2 rounded text-center">
                            View in Browser
                        </a>
                        <button onclick="window.close()" 
                                class="bg-gray-500 text-white px-4 py-2 rounded">
                            Close
                        </button>
                    </div>
                    <div class="mt-6">
                        <iframe src="<?php echo htmlspecialchars($pdfPath); ?>" 
                                class="w-full h-screen border-0 rounded"
                                type="application/pdf">
                            <p>Your browser does not support embedded PDF files. 
                               <a href="<?php echo htmlspecialchars($pdfPath); ?>">Click here to download the PDF</a>.</p>
                        </iframe>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    } else {
        // Desktop browsers: display inline
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . filesize($pdfPath));
        readfile($pdfPath);
    }
    exit;

} catch (Exception $e) {
    error_log('View Report Error: ' . $e->getMessage());
    die('Error: ' . $e->getMessage());
}
