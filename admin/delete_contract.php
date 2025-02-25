<?php
require '../session/db.php';
require '../session/audit_trail.php';

// Make sure user is logged in and get user_id
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['tenant_id'])) {
        throw new Exception('Tenant ID is required');
    }

    $tenant_id = $data['tenant_id'];

    // Get current contract file path
    $stmt = $conn->prepare("SELECT contract_file FROM tenants WHERE tenant_id = ?");
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tenant = $result->fetch_assoc();

    if ($tenant && $tenant['contract_file']) {
        // Delete file from filesystem
        $filepath = '../' . $tenant['contract_file'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // Update database
    $stmt = $conn->prepare("UPDATE tenants SET contract_file = NULL, contract_upload_date = NULL WHERE tenant_id = ?");
    $stmt->bind_param("i", $tenant_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update database');
    }

    // Add audit log using user_id from session
    $user_id = $_SESSION['user_id'];
    $tenant_details = "Contract deleted for tenant ID: $tenant_id";
    logActivity($user_id, "Delete Contract", $tenant_details);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>