<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../session/audit_trail.php';  // Add this line
require_once '../notification/notif_handler.php';

session_start();


try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); // Unauthorized
        echo "Error: User is not logged in.";
        exit();
    }

    // Ensure it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        echo "Error: Invalid request method.";
        exit();
    }

    // Validate required fields
    $requiredFields = ['unit', 'issue', 'description', 'service_date'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400); // Bad Request
            echo "Error: Field '$field' is required.";
            exit();
        }
    }

    // Validate service date is in the future
    $service_date = strtotime($_POST['service_date']);
    $today = strtotime(date('Y-m-d'));
    
    if ($service_date <= $today) {
        http_response_code(400);
        echo "Error: Service date must be a future date.";
        exit();
    }

    // Handle file upload (if applicable)
    $image = null;
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/maintenance_requests/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid('maintenance_', true) . '.' . pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['file_upload']['tmp_name'], $filePath)) {
            http_response_code(500); // Internal Server Error
            echo "Error: File upload failed.";
            exit();
        }
        $image = $filePath;
    }

    // Save to database
    $query = "INSERT INTO maintenance_requests (user_id, unit, issue, description, service_date, image) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        http_response_code(500);
        echo "Error: Database query preparation failed.";
        exit();
    }

    $stmt->bind_param("isssss", $_SESSION['user_id'], $_POST['unit'], $_POST['issue'], $_POST['description'], $_POST['service_date'], $image);
    if ($stmt->execute()) {
        // Log the activity
        $request_id = $conn->insert_id;
        logActivity(
            $_SESSION['user_id'],
            'Submit Maintenance Request',
            "Submitted maintenance request for unit {$_POST['unit']}, Issue: {$_POST['issue']}"
        );

        // Create notification for the tenant
        $userMessage = "Your maintenance request for Unit {$_POST['unit']} has been submitted successfully. Our team will review it shortly.";
        createNotification($_SESSION['user_id'], $userMessage, 'maintenance');

        // Create notification for admin (assuming admin user_id is 1)
        $adminMessage = "New maintenance request from Unit {$_POST['unit']} - Issue: {$_POST['issue']}";
        createNotification(1, $adminMessage, 'admin_maintenance');

        http_response_code(200); // OK
        echo "Success: Maintenance request submitted successfully.";
    } else {
        http_response_code(500);
        echo "Error: Failed to save the request.";
    }
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    http_response_code(500);
    echo "Error: An unexpected error occurred.";
}


?>
