<?php
require '../session/db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to get floor prefix from unit number
function getFloorPrefix($unit_no) {
    if (substr($unit_no, 0, 2) === '10') {
        return '10';
    }
    return substr($unit_no, 0, 1);
}

// Initialize counters for each floor
$floorCounts = [
    'G' => 0,  // Ground Floor
    '1' => 0,  // First Floor
    '2' => 0,  // Second Floor
    '3' => 0,  // Third Floor
    '4' => 0,  // Fourth Floor
    '5' => 0,  // Fifth Floor
    '6' => 0,  // Sixth Floor
    '7' => 0,  // Seventh Floor
    '8' => 0,  // Eighth Floor
    '9' => 0,  // Ninth Floor
    '10' => 0  // Tenth Floor
];

try {
    // Query to get available units
    $query = "SELECT unit_no FROM property WHERE status = 'Available' AND position = 'active'";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    // Count available units per floor
    while ($row = $result->fetch_assoc()) {
        $floor = getFloorPrefix($row['unit_no']);
        if (isset($floorCounts[$floor])) {
            $floorCounts[$floor]++;
        }
    }

    // Format data for the chart
    $data = [
        'categories' => [
            'Ground Floor', 'First Floor', 'Second Floor', 'Third Floor', 
            'Fourth Floor', 'Fifth Floor', 'Sixth Floor', 'Seventh Floor',
            'Eighth Floor', 'Ninth Floor', 'Tenth Floor'
        ],
        'series' => [
            [
                'name' => 'Available Units',
                'data' => array_values($floorCounts)
            ]
        ]
    ];

    // Debug log
    error_log('Available units data: ' . json_encode($data));

    header('Content-Type: application/json');
    echo json_encode($data);

} catch (Exception $e) {
    error_log('Error in get_availability_data.php: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
