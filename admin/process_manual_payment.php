<?php
// Turn off error reporting for production but log errors to a file
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

// Start output buffering immediately
ob_start();

// Function to safely return JSON response
function return_json_response($success, $message, $additional_data = []) {
    // Clear any previous output
    if (ob_get_length()) ob_clean();
    
    // Set headers
    header('Content-Type: application/json');
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
    // Prepare response
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $additional_data);
    
    // Output JSON and exit
    echo json_encode($response);
    ob_end_flush();
    exit;
}

// Wrap the entire script in a try-catch to catch any unexpected errors
try {
    // Make sure the session directory exists and is writable
    if (!is_dir(session_save_path()) || !is_writable(session_save_path())) {
        error_log("Session directory is not writable: " . session_save_path());
    }
    
    require_once '../session/session_manager.php';
    require '../session/db.php';
    require_once '../session/audit_trail.php';

    // Use start_secure_session instead of session_start
    start_secure_session();

    // Verify database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection object is null"));
    }

    // Check if the user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        return_json_response(false, 'Unauthorized access');
    }

    // Log the request for debugging
    error_log("Processing manual payment. User: {$_SESSION['user_id']}, Method: {$_SERVER['REQUEST_METHOD']}");

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return_json_response(false, 'Invalid request method');
    }

    // Validate required fields
    $required_fields = ['tenant_id', 'amount', 'payment_date', 'payment_method', 'payment_type'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            return_json_response(false, "Missing required field: $field");
        }
    }
    
    // Get form data
    $tenant_id = (int)$_POST['tenant_id'];
    $amount = (float)$_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_method = $_POST['payment_method'];
    $payment_type = $_POST['payment_type']; // 'rent' or 'other'
    $notes = isset($_POST['notes']) ? $_POST['notes'] : null;
    
    // Get bill item details if payment type is 'other'
    $bill_item = '';  // Default empty string instead of null
    $bill_description = '';  // Default empty string instead of null
    if ($payment_type === 'other') {
        if (!isset($_POST['bill_item']) || empty($_POST['bill_item'])) {
            return_json_response(false, 'Bill item is required for other payment types');
        }
        $bill_item = $_POST['bill_item'];
        $bill_description = isset($_POST['bill_description']) ? $_POST['bill_description'] : '';
    }
    
    // Additional validation for GCash payments
    $gcash_number = '';
    $reference_number = '';
    if ($payment_method === 'gcash') {
        if (!isset($_POST['gcash_number']) || empty($_POST['gcash_number'])) {
            return_json_response(false, 'GCash number is required for GCash payments');
        }
        
        $gcash_number = $_POST['gcash_number'];
        
        // Validate GCash number format (Philippine mobile number)
        if (!preg_match('/^09\d{9}$/', $gcash_number)) {
            return_json_response(false, 'Invalid GCash number format. Must be a Philippine mobile number (e.g., 09123456789)');
        }
        
        $reference_number = isset($_POST['reference_number']) ? $_POST['reference_number'] : '';
    }
    
    // Handle file upload if provided
    $receipt_image = null;
    if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
        try {
            $upload_dir = '../uploads/receipts/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    throw new Exception("Failed to create directory: $upload_dir");
                }
            }
            
            // Check if directory is writable
            if (!is_writable($upload_dir)) {
                throw new Exception("Upload directory is not writable: $upload_dir");
            }
            
            // Get file extension and generate unique filename
            $file_ext = pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'receipt_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            // Check if it's a valid image using a simpler approach
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($file_ext), $allowed_extensions)) {
                throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed');
            }
            
            // Upload the file
            if (!move_uploaded_file($_FILES['receipt_image']['tmp_name'], $upload_path)) {
                throw new Exception("Failed to upload file. Error code: " . $_FILES['receipt_image']['error']);
            }
            
            $receipt_image = 'uploads/receipts/' . $new_filename;
        } catch (Exception $e) {
            // Log the error but continue processing the payment
            error_log("File upload error: " . $e->getMessage());
            $receipt_image = null;
        }
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    // Get the current admin's user ID for processed_by
    $processed_by = $_SESSION['user_id'];
    
    // Test the query structure first
    error_log("Preparing to insert payment for tenant ID: $tenant_id, amount: $amount, processed by: $processed_by");
    
    // Updated INSERT query to include processed_by column, with explicit column types for debugging
    $sql = "INSERT INTO payments 
        (tenant_id, amount, payment_date, status, gcash_number, reference_number, receipt_image, notes, payment_type, bill_item, bill_description, processed_by) 
        VALUES (?, ?, ?, 'Received', ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('SQL Prepare Error: ' . $conn->error . ' for query: ' . $sql);
    }
    
    // Make sure no values are null
    if ($notes === null) $notes = '';
    if ($receipt_image === null) $receipt_image = '';
    
    // Updated bind_param with processed_by field - fix the type definition string
    if (!$stmt->bind_param("idssssssssi", $tenant_id, $amount, $payment_date, $gcash_number, 
                          $reference_number, $receipt_image, $notes, $payment_type, 
                          $bill_item, $bill_description, $processed_by)) {
        throw new Exception('Binding parameters failed: ' . $stmt->error);
    }
    
    // Execute the statement with detailed error handling
    if (!$stmt->execute()) {
        throw new Exception('Database error on INSERT: ' . $stmt->error);
    }
    
    // Get the payment ID
    $payment_id = $conn->insert_id;
    
    // Update tenant's outstanding balance only if payment type is rent
    if ($payment_type === 'rent') {
        // First check if tenant exists
        $checkTenantStmt = $conn->prepare("SELECT tenant_id, monthly_rate FROM tenants WHERE tenant_id = ?");
        $checkTenantStmt->bind_param("i", $tenant_id);
        
        if (!$checkTenantStmt->execute()) {
            throw new Exception('Error checking tenant: ' . $checkTenantStmt->error);
        }
        
        $tenantResult = $checkTenantStmt->get_result();
        if ($tenantResult->num_rows === 0) {
            throw new Exception('Tenant ID not found: ' . $tenant_id);
        }
        
        $tenantData = $tenantResult->fetch_assoc();
        $monthly_rate = $tenantData['monthly_rate'];

        // Update outstanding balance
        $balanceStmt = $conn->prepare(
            "UPDATE tenants 
            SET outstanding_balance = GREATEST(0, outstanding_balance - ?) 
            WHERE tenant_id = ?"
        );
        
        if (!$balanceStmt) {
            throw new Exception('SQL Prepare Error for balance update: ' . $conn->error);
        }
        
        $balanceStmt->bind_param("di", $amount, $tenant_id);
        
        if (!$balanceStmt->execute()) {
            throw new Exception('Error updating tenant balance: ' . $balanceStmt->error);
        }
        
        // Get updated balance to recalculate payable months
        $getBalanceStmt = $conn->prepare("SELECT outstanding_balance FROM tenants WHERE tenant_id = ?");
        $getBalanceStmt->bind_param("i", $tenant_id);
        $getBalanceStmt->execute();
        $balanceResult = $getBalanceStmt->get_result();
        $balanceData = $balanceResult->fetch_assoc();
        $new_balance = $balanceData['outstanding_balance'];
        
        // Recalculate payable months
        $payable_months = ceil($new_balance / $monthly_rate);
        
        // Update payable months and last payment date
        $updateTenantStmt = $conn->prepare(
            "UPDATE tenants 
            SET payable_months = ?, 
                last_payment_date = ? 
            WHERE tenant_id = ?"
        );
        $updateTenantStmt->bind_param("isi", $payable_months, $payment_date, $tenant_id);
        
        if (!$updateTenantStmt->execute()) {
            throw new Exception('Error updating tenant payable months: ' . $updateTenantStmt->error);
        }
    }
    
    // Get tenant info for logging
    $tenantInfoStmt = $conn->prepare(
        "SELECT t.tenant_id, u.name AS tenant_name, p.unit_no
         FROM tenants t
         JOIN users u ON t.user_id = u.user_id
         JOIN property p ON t.unit_rented = p.unit_id
         WHERE t.tenant_id = ?"
    );
    $tenantInfoStmt->bind_param("i", $tenant_id);
    if (!$tenantInfoStmt->execute()) {
        throw new Exception('Error retrieving tenant info: ' . $tenantInfoStmt->error);
    }
    
    $tenantResult = $tenantInfoStmt->get_result();
    $tenant = $tenantResult->fetch_assoc();
    
    if (!$tenant) {
        throw new Exception('Tenant information not found');
    }
    
    // Log activity
    $adminName = $_SESSION['name'] ?? 'Admin';
    $paymentMethodText = ($payment_method === 'gcash') ? 'GCash' : 'Cash';
    
    // Different activity details for rent vs other payments
    if ($payment_type === 'rent') {
        $activityDetails = "Recorded $paymentMethodText rent payment of ₱" . number_format($amount, 2) . 
                          " for " . $tenant['tenant_name'] . " (Unit " . $tenant['unit_no'] . ")";
    } else {
        $activityDetails = "Recorded $paymentMethodText payment of ₱" . number_format($amount, 2) . 
                          " for " . $bill_item . " - " . $tenant['tenant_name'] . " (Unit " . $tenant['unit_no'] . ")";
    }
    
    logActivity(
        $_SESSION['user_id'],
        'Recorded Manual Payment',
        $activityDetails
    );
    
    // Commit transaction
    $conn->commit();
    
    // Success! Return success response with payment details
    return_json_response(true, 'Payment recorded successfully', [
        'payment_id' => $payment_id,
        'tenant_name' => $tenant['tenant_name'],
        'unit_no' => $tenant['unit_no']
    ]);
    
} catch (Exception $e) {
    // Log detailed error
    error_log("Manual payment error: " . $e->getMessage() . " - " . $e->getTraceAsString());
    
    // Rollback transaction on error if one is active
    if (isset($conn) && $conn instanceof mysqli && $conn->ping() && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    // Return error response
    return_json_response(false, 'Error: ' . $e->getMessage());
} catch (Error $e) {
    // Log detailed error
    error_log("PHP error in manual payment: " . $e->getMessage() . " - " . $e->getTraceAsString());
    
    // Catch PHP errors too
    return_json_response(false, 'System error: ' . $e->getMessage());
}

// This should never execute, but just in case
return_json_response(false, 'Unexpected error occurred');
?>