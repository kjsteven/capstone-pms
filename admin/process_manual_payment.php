<?php
// Turn off error reporting to prevent output before headers
ini_set('display_errors', 0);
error_reporting(0);

// Start output buffering immediately
ob_start();

require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../session/audit_trail.php';

// Use start_secure_session instead of session_start for consistency
start_secure_session();

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean(); // Clear any output
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    // Validate required fields
    $required_fields = ['tenant_id', 'amount', 'payment_date', 'payment_method'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Get form data
    $tenant_id = (int)$_POST['tenant_id'];
    $amount = (float)$_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_method = $_POST['payment_method'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : null;
    
    // Additional validation for GCash payments
    $gcash_number = '';
    $reference_number = '';
    if ($payment_method === 'gcash') {
        if (!isset($_POST['gcash_number']) || empty($_POST['gcash_number'])) {
            throw new Exception('GCash number is required for GCash payments');
        }
        
        $gcash_number = $_POST['gcash_number'];
        
        // Validate GCash number format (Philippine mobile number)
        if (!preg_match('/^09\d{9}$/', $gcash_number)) {
            throw new Exception('Invalid GCash number format. Must be a Philippine mobile number (e.g., 09123456789)');
        }
        
        $reference_number = isset($_POST['reference_number']) ? $_POST['reference_number'] : '';
    }
    
    // Handle file upload if provided
    $receipt_image = null;
    if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/receipts/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Get file extension and generate unique filename
        $file_ext = pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'receipt_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;
        
        // Check if it's a valid image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['receipt_image']['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed');
        }
        
        // Upload the file
        if (move_uploaded_file($_FILES['receipt_image']['tmp_name'], $upload_path)) {
            $receipt_image = 'uploads/receipts/' . $new_filename;
        }
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    // Fixed INSERT query to match table structure - IMPORTANT CHANGE HERE
    // Adjusted parameter count and removed created_at (which has default value)
    $stmt = $conn->prepare(
        "INSERT INTO payments 
        (tenant_id, amount, payment_date, status, gcash_number, reference_number, receipt_image, notes) 
        VALUES (?, ?, ?, 'Received', ?, ?, ?, ?)"
    );
    
    // Make sure reference_number is never null (use empty string if null)
    if ($reference_number === null) $reference_number = '';
    
    // Fixed bind_param types to match the parameters (i=integer, d=double/float, s=string)
    $stmt->bind_param("idsssss", $tenant_id, $amount, $payment_date, $gcash_number, $reference_number, 
                      $receipt_image, $notes);
    
    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }
    
    // Get the payment ID
    $payment_id = $conn->insert_id;
    
    // Update tenant's outstanding balance
    $balanceStmt = $conn->prepare(
        "UPDATE tenants 
        SET outstanding_balance = GREATEST(0, outstanding_balance - ?) 
        WHERE tenant_id = ?"
    );
    $balanceStmt->bind_param("di", $amount, $tenant_id);
    $balanceStmt->execute();
    
    // Get tenant info for logging
    $tenantInfoStmt = $conn->prepare(
        "SELECT t.tenant_id, u.name AS tenant_name, p.unit_no
         FROM tenants t
         JOIN users u ON t.user_id = u.user_id
         JOIN property p ON t.unit_rented = p.unit_id
         WHERE t.tenant_id = ?"
    );
    $tenantInfoStmt->bind_param("i", $tenant_id);
    $tenantInfoStmt->execute();
    $tenantResult = $tenantInfoStmt->get_result();
    $tenant = $tenantResult->fetch_assoc();
    
    // Log activity
    $adminName = $_SESSION['name'] ?? 'Admin';
    $paymentMethodText = ($payment_method === 'gcash') ? 'GCash' : 'Cash';
    $activityDetails = "Recorded $paymentMethodText payment of ₱" . number_format($amount, 2) . 
                      " for " . $tenant['tenant_name'] . " (Unit " . $tenant['unit_no'] . ")";
    
    logActivity(
        $_SESSION['user_id'],
        'Recorded Manual Payment',
        $activityDetails
    );
    
    // Commit transaction
    $conn->commit();
    
    // Clear any output before returning JSON
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment recorded successfully',
        'payment_id' => $payment_id,
        'tenant_name' => $tenant['tenant_name'],
        'unit_no' => $tenant['unit_no']
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    // Clear any output before returning JSON
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// End output buffer and flush
ob_end_flush();
exit; // Make sure no additional output is generated
?>