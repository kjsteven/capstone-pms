<?php
// Prevent any output before headers
ob_start();

// Set JSON header immediately
header('Content-Type: application/json');

require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../session/audit_trail.php';


session_start();

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

    // Calculate rental details - UPDATED CALCULATION
    $date1 = new DateTime($rent_from);
    $date2 = new DateTime($rent_until);
    
    // Calculate exact days between dates
    $interval = $date1->diff($date2);
    $totalDays = $interval->days;
    
    // Calculate months more precisely (average month = 365.25/12 days)
    $exactMonths = $totalDays / (365.25/12);
    
    // Calculate total rent based on exact months
    $total_rent = $exactMonths * $monthly_rate;
    
    // Calculate outstanding balance
    $outstanding_balance = $total_rent - $downpayment_amount;
    
    // Calculate payable months - ensure this is consistent with outstanding balance
    $payable_months = ceil($outstanding_balance / $monthly_rate);
    
    // Recalculate outstanding balance to ensure consistency with payable months
    $outstanding_balance = $payable_months * $monthly_rate;

    // Handle receipt file upload
    $downpayment_receipt = null;
    if (isset($_FILES['downpayment_receipt']) && $_FILES['downpayment_receipt']['error'] == 0) {
        // Change this directory path to match the one in tenantAdmin.php
        $upload_dir = '../uploads/downpayment/'; // Previously '../uploads/receipts/'
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['downpayment_receipt']['name'], PATHINFO_EXTENSION);
        $new_filename = 'receipt_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (!move_uploaded_file($_FILES['downpayment_receipt']['tmp_name'], $upload_path)) {
            throw new Exception("Failed to upload receipt file");
        }
        $downpayment_receipt = $upload_path;
    }

    $conn->begin_transaction();

    // Adjust the SQL query to include downpayment_receipt
    $sql = "INSERT INTO tenants (user_id, unit_rented, rent_from, rent_until, monthly_rate, 
            outstanding_balance, downpayment_amount, payable_months, created_at, updated_at";
    
    if ($downpayment_receipt) {
        $sql .= ", downpayment_receipt";
    }
    
    $sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()";
    
    if ($downpayment_receipt) {
        $sql .= ", ?";
    }
    
    $sql .= ")";
    
    $stmt = $conn->prepare($sql);
    
    if ($downpayment_receipt) {
        $stmt->bind_param(
            "isssddids",
            $user_id,
            $unit_rented,
            $rent_from,
            $rent_until,
            $monthly_rate,
            $outstanding_balance,
            $downpayment_amount,
            $payable_months,
            $downpayment_receipt
        );
    } else {
        $stmt->bind_param(
            "isssddi",
            $user_id,
            $unit_rented,
            $rent_from,
            $rent_until,
            $monthly_rate,
            $outstanding_balance,
            $downpayment_amount,
            $payable_months
        );
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert rental record: " . $stmt->error);
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
        "Added unit {$unitData['unit_no']} for tenant {$userData['name']}" . 
        ($downpayment_receipt ? " with receipt" : " without receipt")
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