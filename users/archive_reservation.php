<?php
// Prevent any output before our JSON response
ob_start();

require_once '../session/session_manager.php';
require '../session/db.php';

start_secure_session();

// Clear any previous output
ob_clean();

// Set proper JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if (isset($_POST['archive_reservation'])) {
    try {
        if (!isset($_POST['reservation_id'])) {
            throw new Exception('reservation_id was not set');
        }
        
        $reservation_id = $_POST['reservation_id'];
        $user_id = $_SESSION['user_id'];
        
        // Log incoming request
        error_log("Archiving reservation: " . $reservation_id . " for user: " . $user_id);
        
        $update_query = "UPDATE reservations SET archived = 1 WHERE reservation_id = ? AND user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        
        if (!$update_stmt) {
            throw new Exception("Failed to prepare update statement: " . $conn->error);
        }

        $update_stmt->bind_param("ii", $reservation_id, $user_id);
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            $response = [
                'success' => true,
                'message' => 'Reservation archived successfully'
            ];
            error_log("Archive success response: " . json_encode($response));
            echo json_encode($response);
        } else {
            throw new Exception("No changes were made. The reservation might not exist.");
        }
    } catch (Exception $e) {
        $error_response = [
            'success' => false, 
            'error' => "Error archiving reservation: " . $e->getMessage()
        ];
        error_log("Archive error response: " . json_encode($error_response));
        echo json_encode($error_response);
    }
    
    exit();
}
?>