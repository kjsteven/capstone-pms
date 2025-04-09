<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require '../session/audit_trail.php';
require_once '../notification/notif_handler.php';

session_start();

// Initialize response array
$response = array('success' => false);

try {
    // Validate user authentication
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    if (!isset($_POST['user_id'], $_POST['unit_id'], $_POST['viewing_date'], $_POST['viewing_time'])) {
        throw new Exception('Missing required fields');
    }

    // Sanitize and validate input data
    $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
    $unit_id = filter_var($_POST['unit_id'], FILTER_VALIDATE_INT);
    $viewing_date = filter_var($_POST['viewing_date'], FILTER_SANITIZE_STRING);
    $viewing_time = filter_var($_POST['viewing_time'], FILTER_SANITIZE_STRING);
    $status = 'Pending';

    if (!$user_id || !$unit_id) {
        throw new Exception('Invalid user_id or unit_id');
    }

    // Begin transaction
    $conn->begin_transaction();

    // Check if the unit exists and get unit details
    $check_stmt = $conn->prepare("SELECT unit_no FROM property WHERE unit_id = ?");
    if (!$check_stmt) {
        throw new Exception('Failed to prepare unit check statement');
    }

    $check_stmt->bind_param("i", $unit_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Invalid unit selected');
    }

    $unit_data = $result->fetch_assoc();

    // Insert reservation
    $insert_stmt = $conn->prepare("INSERT INTO reservations (user_id, unit_id, viewing_date, viewing_time, status) VALUES (?, ?, ?, ?, ?)");
    if (!$insert_stmt) {
        throw new Exception('Failed to prepare insert statement');
    }

    $insert_stmt->bind_param("iisss", $user_id, $unit_id, $viewing_date, $viewing_time, $status);

    if (!$insert_stmt->execute()) {
        throw new Exception('Failed to submit reservation: ' . $insert_stmt->error);
    }

    // Get the new reservation ID
    $reservation_id = $insert_stmt->insert_id;

    // Log the activity
    $action_details = sprintf(
        "Reserved Unit #%s - Viewing scheduled for %s at %s (Reservation ID: %d)",
        $unit_data['unit_no'],
        $viewing_date,
        $viewing_time,
        $reservation_id
    );
    
    if (!logActivity($user_id, "Unit Reservation", $action_details)) {
        throw new Exception('Failed to log activity');
    }

    // Get user's name for the admin notification
    $user_query = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
    $user_query->bind_param("i", $user_id);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user_data = $user_result->fetch_assoc();
    $user_name = $user_data['name'];

    // Create notification for the user
    $userMessage = "You have successfully reserved Unit {$unit_data['unit_no']} for viewing on " . 
                  date('F j, Y', strtotime($viewing_date)) . " at " . 
                  date('g:i A', strtotime($viewing_time)) . ". Please wait for confirmation.";
    createNotification($user_id, $userMessage, 'reservation');

    // Create notification for admin using the fetched user name
    $adminMessage = "New reservation request for Unit {$unit_data['unit_no']} from " . $user_name . 
                   " scheduled for " . date('F j, Y', strtotime($viewing_date)) . 
                   " at " . date('g:i A', strtotime($viewing_time));
    createNotification(1, $adminMessage, 'admin_reservation');

    // Update unit status to Reserved
    $updateQuery = "UPDATE property SET status = 'Reserved' WHERE unit_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $unit_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Success response with more details
    $response['success'] = true;
    $response['message'] = 'Reservation submitted successfully';
    $response['reservation_id'] = $reservation_id;
    $response['viewing_date'] = date('F j, Y', strtotime($viewing_date));
    $response['viewing_time'] = date('g:i A', strtotime($viewing_time));

} catch (Exception $e) {
    // Log the error for debugging
    error_log('Reservation Error: ' . $e->getMessage());
    
    // Rollback transaction if it was started
    if (isset($conn) && !$conn->connect_error) {
        $conn->rollback();
    }

    // More user-friendly error message
    $response['success'] = false;
    $response['message'] = 'An error occurred while processing your reservation. Please try again.';
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
