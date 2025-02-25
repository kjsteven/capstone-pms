<?php
// Prevent any output before headers
ob_start();

// Set JSON header immediately
header('Content-Type: application/json');

require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../session/audit_trail.php';

start_secure_session();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $required_fields = ['user_id', 'unit_rented', 'rent_from', 'rent_until', 'monthly_rate', 'downpayment_amount'];
    $input = array_map('trim', $_POST);
    
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $user_id = (int)$input['user_id'];
    $unit_rented = $input['unit_rented'];
    $rent_from = $input['rent_from'];
    $rent_until = $input['rent_until'];
    $monthly_rate = (float)$input['monthly_rate'];
    $downpayment_amount = (float)$input['downpayment_amount'];

    // Calculate rental details
    $date1 = new DateTime($rent_from);
    $date2 = new DateTime($rent_until);
    $interval = $date1->diff($date2);
    $months = $interval->m + ($interval->y * 12);
    
    $total_rent = $months * $monthly_rate;
    $outstanding_balance = $total_rent - $downpayment_amount;
    $payable_months = ceil($outstanding_balance / $monthly_rate);

    $conn->begin_transaction();

    // Insert rental record
    $stmt = $conn->prepare(
        "INSERT INTO tenants (user_id, unit_rented, rent_from, rent_until, monthly_rate, 
        outstanding_balance, downpayment_amount, payable_months, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
    );
    
    $stmt->bind_param(
        "isssdddi",
        $user_id,
        $unit_rented,
        $rent_from,
        $rent_until,
        $monthly_rate,
        $outstanding_balance,
        $downpayment_amount,
        $payable_months
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert rental record");
    }

    // Update unit status
    $stmt = $conn->prepare("UPDATE property SET status = 'Occupied' WHERE unit_id = ?");    
    $stmt->bind_param("s", $unit_rented);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update unit status");
    }

    // Update reservation
    $stmt = $conn->prepare(
        "UPDATE reservations SET status = 'completed' 
        WHERE user_id = ? AND unit_id = ? AND status = 'confirmed'"
    );
    $stmt->bind_param("is", $user_id, $unit_rented);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update reservation status");
    }

    // Get unit details for audit log
    $unitQuery = $conn->prepare("SELECT unit_no FROM property WHERE unit_id = ?");
    $unitQuery->bind_param("i", $unit_rented);
    $unitQuery->execute();
    $unitResult = $unitQuery->get_result();
    $unitData = $unitResult->fetch_assoc();

    // Get user details for audit log
    $userQuery = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    $userData = $userResult->fetch_assoc();

    // Log the activity
    logActivity(
        $_SESSION['user_id'],
        "Added New Unit to Tenant",
        "Added unit {$unitData['unit_no']} for tenant {$userData['name']}"
    );

    $conn->commit();
    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Unit added successfully']);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Clean output buffer before exit
ob_end_flush();

?>