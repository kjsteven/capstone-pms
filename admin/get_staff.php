<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../session/db.php';
start_secure_session();

// Check database connection
if (!$conn) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

// Query to fetch staff without status condition
$query = "SELECT staff_id, name, specialty FROM staff";
$result = mysqli_query($conn, $query);

if ($result === false) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Query execution failed: ' . mysqli_error($conn)
    ]);
    exit;
}

// Check if any rows were returned
$staff = [];
while ($row = mysqli_fetch_assoc($result)) {
    $staff[] = $row;
}

if (empty($staff)) {
    echo json_encode([
        'status' => 'success', 
        'data' => [],
        'message' => 'No staff found'
    ]);
} else {
    echo json_encode([
        'status' => 'success', 
        'data' => $staff
    ]);
}

// Close the connection
mysqli_close($conn);
?>
