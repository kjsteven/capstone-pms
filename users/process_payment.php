<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../session/audit_trail.php'; // Added audit_trail.php inclusion

session_start();

// Prevent any output before headers
ob_start();

// Set proper headers for JSON response
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Flag to track if transaction was started
$transaction_started = false;

try {
    // Validate required fields
    $required_fields = ['unit_id', 'amount', 'reference_number', 'gcash_number'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception('Missing required field: ' . $field);
        }
    }

    // Check if file is uploaded
    if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Receipt image is required');
    }

    // Get form data
    $unit_id = $_POST['unit_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $gcash_number = $_POST['gcash_number'] ?? null;
    $reference_number = $_POST['reference_number'] ?? null;

    // Get the new fields
    $payment_type = $_POST['payment_type'] ?? 'rent';
    $bill_item = ($payment_type === 'rent') ? "Monthly Rent" : ($_POST['bill_item'] ?? "");

    // Validate required fields
    if (!$unit_id || !$amount || !$gcash_number || !$reference_number) {
        $response = array(
            'status' => 'error',
            'message' => 'Missing required fields.'
        );
        echo json_encode($response);
        exit;
    }

    // For non-rent payments, bill item is required
    if ($payment_type !== 'rent' && empty($bill_item)) {
        $response = array(
            'status' => 'error',
            'message' => 'Bill item is required for non-rent payments.'
        );
        echo json_encode($response);
        exit;
    }

    // Validate GCash number format (Philippine mobile number starting with 09)
    if (!preg_match('/^09\d{9}$/', $gcash_number)) {
        throw new Exception('Invalid GCash number format');
    }

    // Get tenant_id using unit_id and user_id
    $query = "SELECT tenant_id FROM tenants WHERE user_id = ? AND unit_rented = ? AND status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $_SESSION['user_id'], $unit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('You are not registered as a tenant for this unit');
    }

    $tenant = $result->fetch_assoc();
    $tenant_id = $tenant['tenant_id'];
    $stmt->close();

    // Handle file upload
    $upload_dir = '../uploads/receipts/';

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }

    // Get file info
    $file_name = $_FILES['receipt']['name'];
    $file_tmp = $_FILES['receipt']['tmp_name'];
    $file_size = $_FILES['receipt']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Allowed file extensions
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    // Validate file extension
    if (!in_array($file_ext, $allowed_extensions)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
    }

    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($file_size > $max_size) {
        throw new Exception('File is too large. Maximum size is 5MB.');
    }

    // Generate unique filename
    $new_filename = uniqid('receipt_') . '_' . time() . '.' . $file_ext;
    $file_path = $upload_dir . $new_filename;

    // Move uploaded file
    if (!move_uploaded_file($file_tmp, $file_path)) {
        throw new Exception('Failed to upload file');
    }

    // Store relative path in database
    $db_file_path = 'uploads/receipts/' . $new_filename;

    // Begin transaction
    $conn->autocommit(false);
    $transaction_started = true;

    // Insert payment record in database
    $query = "INSERT INTO payments (tenant_id, amount, payment_date, reference_number, receipt_image, gcash_number, status, payment_type, bill_item) 
              VALUES (?, ?, NOW(), ?, ?, ?, 'Pending', ?, ?)";
    $stmt = $conn->prepare($query);
    
    // Fix: add an extra 's' to the type definition string to match the 7 parameters
    $stmt->bind_param("idsssss", $tenant_id, $amount, $reference_number, $db_file_path, $gcash_number, $payment_type, $bill_item);

    if (!$stmt->execute()) {
        // Roll back transaction on error
        $conn->rollback();
        throw new Exception("Database error: " . $stmt->error);
    }

    // Get the payment ID of the inserted record
    $payment_id = $stmt->insert_id;

    // Log the payment using the audit_trail function
    $actionDetails = "Payment of PHP " . number_format($amount, 2) . " submitted for review (Reference: $reference_number)";
    logActivity($_SESSION['user_id'], 'Payment Submission', $actionDetails);

    // Commit transaction
    $conn->commit();
    $transaction_started = false;

    // Clear output buffer before sending response
    ob_clean();

    // Return success response
    echo json_encode([
        'status' => 'success', 
        'message' => 'Payment submitted successfully! Your payment is being reviewed by management.',
        'payment_id' => $payment_id
    ]);

} catch (Exception $e) {
    // Roll back transaction if started
    if ($transaction_started && isset($conn)) {
        $conn->rollback();
    }
    
    // Log the error
    error_log("Payment processing error: " . $e->getMessage());
    
    // Clear output buffer before sending response
    ob_clean();
    
    // Return error response
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    // Reset autocommit mode
    if ($transaction_started && isset($conn)) {
        $conn->autocommit(true);
    }
    
    // Close any open statements and connection
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn) {
        $conn->close();
    }
    
    // End output buffering
    if (ob_get_length()) ob_end_flush();
}
?>
