<?php
require_once '../session/session_manager.php';
require '../session/db.php';
start_secure_session();

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); // Unauthorized
        echo "Error: User is not logged in.";
        exit();
    }

    // Fetch maintenance requests from the database
    $query = "SELECT mr.unit, mr.issue, mr.description, mr.service_date, mr.status, mr.image 
              FROM maintenance_requests mr
              WHERE mr.user_id = ? 
              ORDER BY mr.service_date DESC";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        http_response_code(500);
        echo "Error: Database query preparation failed.";
        exit();
    }

    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the data and encode it as JSON
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    // Return the result as JSON
    echo json_encode($requests);
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    http_response_code(500);
    echo "Error: An unexpected error occurred.";
}
?>
