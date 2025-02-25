<?php
require '../session/db.php';
start_secure_session();
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Log the request
error_log('Maintenance data requested for year: ' . $_GET['year']);

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Initialize arrays with zeros for all months
$completed = array_fill(0, 12, 0);
$pending = array_fill(0, 12, 0);
$inProgress = array_fill(0, 12, 0);

$query = "
    SELECT 
        MONTH(service_date) as month,
        status,
        COUNT(*) as count
    FROM maintenance_requests 
    WHERE YEAR(service_date) = ? 
    AND status IN ('Completed', 'Pending', 'In Progress')
    GROUP BY MONTH(service_date), status
    ORDER BY month ASC";

try {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('i', $year);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    // Log the query results
    error_log('Query executed successfully. Processing results...');

    while ($row = $result->fetch_assoc()) {
        $month_index = $row['month'] - 1;
        switch ($row['status']) {
            case 'Completed':
                $completed[$month_index] = (int)$row['count'];
                break;
            case 'Pending':
                $pending[$month_index] = (int)$row['count'];
                break;
            case 'In Progress':
                $inProgress[$month_index] = (int)$row['count'];
                break;
        }
    }

    $response_data = [
        'completed' => array_values($completed),
        'pending' => array_values($pending),
        'inProgress' => array_values($inProgress)
    ];

    // Log the response data
    error_log('Sending response: ' . json_encode($response_data));
    
    echo json_encode($response_data);

} catch (Exception $e) {
    error_log('Error in get_maintenance_data.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'debug_info' => [
            'year' => $year,
            'query' => $query
        ]
    ]);
}

$conn->close();
