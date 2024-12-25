<?php
// Include the database connection file
require '../session/db.php';

// Initialize response array
$response = array('success' => false);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $user_id = $_POST['user_id'];  // User ID from session
    $unit_id = $_POST['unit_id'];  // Unit ID passed from modal
    $viewing_date = $_POST['viewing_date'];  // Date of viewing
    $viewing_time = $_POST['viewing_time'];  // Time of viewing
    $status = 'Pending'; // Default status for new reservation

    // Check if the unit_id exists in the property table
    $check_unit_sql = "SELECT unit_id FROM property WHERE unit_id = ?";
    $check_stmt = $conn->prepare($check_unit_sql);
    $check_stmt->bind_param("i", $unit_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // Unit exists, proceed with inserting the reservation
        $insert_sql = "INSERT INTO reservations (user_id, unit_id, viewing_date, viewing_time, status) 
                       VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iisss", $user_id, $unit_id, $viewing_date, $viewing_time, $status);

        if ($insert_stmt->execute()) {
            // Reservation successful
            $response['success'] = true;
        }
    }
}

// Return response as JSON
echo json_encode($response);
?>
