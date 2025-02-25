<?php
require '../session/db.php';
require_once '../session/session_manager.php';
require '../session/audit_trail.php';

// Make sure user is logged in and get user_id
start_secure_session();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    if (!isset($_FILES['contract']) || !isset($_POST['tenant_id'])) {
        throw new Exception('Missing required fields');
    }

    $tenant_id = $_POST['tenant_id'];
    $file = $_FILES['contract'];
    
    // Validate file
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Invalid file type. Only PDF and DOC files are allowed.');
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/contracts/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $filename = uniqid() . '_' . $tenant_id . '_' . basename($file['name']);
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to upload file');
    }

    // Update database
    $stmt = $conn->prepare("UPDATE tenants SET contract_file = ?, contract_upload_date = CURRENT_TIMESTAMP WHERE tenant_id = ?");
    $relative_path = 'uploads/contracts/' . $filename;
    $stmt->bind_param("si", $relative_path, $tenant_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update database');
    }

    // Add audit log using user_id from session
    $user_id = $_SESSION['user_id'];
    $tenant_details = "Contract uploaded for tenant ID: $tenant_id - File: $filename";
    logActivity($user_id, "Upload Contract", $tenant_details);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>