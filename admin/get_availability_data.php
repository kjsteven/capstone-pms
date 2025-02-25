<?php
require '../session/db.php';
require_once '../session/session_manager.php';


session_start();
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Updated function to correctly handle floor numbers
function getFloorPrefix($unit_no) {
    // First, ensure we're working with a string
    $unit_no = (string)$unit_no;
    
    // For tenth floor (units starting with 10 and length of 4)
    if (substr($unit_no, 0, 2) === '10' && strlen($unit_no) === 4) {
        return '10';
    }
    
    // For first floor (units starting with 1 and length of 3)
    if ($unit_no[0] === '1' && strlen($unit_no) === 3) {
        return '1';
    }
    
    // For all other floors
    return substr($unit_no, 0, 1);
}

// Initialize counters for each floor and status
$floorData = [
    'G' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0],
    '1' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0],
    '2' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0],
    '3' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0],
    '4' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0],
    '5' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0],
    '6' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0],
    '7' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0],
    '8' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0],
    '9' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0],
    '10' => ['Available' => 0, 'Occupied' => 0, 'Maintenance' => 0]
];

try {
    // Add debug logging
    $query = "SELECT unit_no, status FROM property WHERE position = 'active'";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    // Count units per floor and status with debug logging
    while ($row = $result->fetch_assoc()) {
        $floor = getFloorPrefix($row['unit_no']);
        $status = $row['status'];
        
        // Debug log
        error_log("Unit: {$row['unit_no']}, Floor: {$floor}, Status: {$status}");
        
        if (isset($floorData[$floor][$status])) {
            $floorData[$floor][$status]++;
        }
    }

    // Format data for the stacked column chart
    $data = [
        'categories' => [
            'Ground Floor', 'First Floor', 'Second Floor', 'Third Floor', 
            'Fourth Floor', 'Fifth Floor', 'Sixth Floor', 'Seventh Floor',
            'Eighth Floor', 'Ninth Floor', 'Tenth Floor'
        ],
        'series' => [
            [
                'name' => 'Available',
                'data' => array_column($floorData, 'Available')
            ],
            [
                'name' => 'Occupied',
                'data' => array_column($floorData, 'Occupied')
            ],
            [
                'name' => 'Maintenance',
                'data' => array_column($floorData, 'Maintenance')
            ]
        ]
    ];

    header('Content-Type: application/json');
    echo json_encode($data);

} catch (Exception $e) {
    error_log('Error in get_availability_data.php: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
   