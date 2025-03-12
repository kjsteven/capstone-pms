<?php
// Prevent direct access
if(!defined('DirectAccess')) {
    header("HTTP/1.0 403 Forbidden");
    exit("Direct access not permitted.");
}

// Don't start session again if it's already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../session/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    $response = ['error' => 'Unauthorized access'];
    return;
}

// Get year parameter, default to current year
$year = isset($year) ? intval($year) : date('Y');

try {
    // Query to get payment amounts by month for the specified year
    $query = "
        SELECT 
            MONTH(payment_date) as month,
            SUM(amount) as total_amount
        FROM 
            payments
        WHERE 
            YEAR(payment_date) = ? 
            AND status = 'Received'
        GROUP BY 
            MONTH(payment_date)
        ORDER BY 
            month
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize array with zeros for all months
    $monthlyPayments = array_fill(1, 12, 0);

    // Fill in actual payment amounts
    while ($row = $result->fetch_assoc()) {
        $month = (int)$row['month'];
        $monthlyPayments[$month] = (float)$row['total_amount'];
    }

    // Convert associative array to indexed array for ApexCharts
    $data = array_values($monthlyPayments);

    // Get available years for dropdown
    $yearsQuery = "
        SELECT DISTINCT YEAR(payment_date) as year 
        FROM payments 
        WHERE status = 'Received'
        ORDER BY year DESC
    ";

    $yearsResult = $conn->query($yearsQuery);
    $years = [];

    while ($row = $yearsResult->fetch_assoc()) {
        $years[] = $row['year'];
    }

    // If no payment years are found, add current year
    if (empty($years)) {
        $years[] = date('Y');
    }

    // Set the response variable to be used by the including file
    $response = [
        'data' => $data,
        'years' => $years
    ];
    
} catch (Exception $e) {
    $response = ['error' => 'Database error: ' . $e->getMessage()];
}
?>
