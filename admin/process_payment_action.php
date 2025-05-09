<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../session/audit_trail.php';
require_once '../utils/email_sender.php';
require_once '../notification/notif_handler.php'; // Add this line

session_start();

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Get admin name from the database using session user_id
$adminQuery = "SELECT name FROM users WHERE user_id = ?";
$adminStmt = $conn->prepare($adminQuery);
$adminStmt->bind_param("i", $_SESSION['user_id']);
$adminStmt->execute();
$adminResult = $adminStmt->get_result();
$adminName = ($adminResult->num_rows > 0) ? $adminResult->fetch_assoc()['name'] : 'Admin';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    // Validate required fields
    if (!isset($_POST['action']) || empty($_POST['action'])) {
        throw new Exception("Missing required field: action");
    }
    
    if (!isset($_POST['payment_id']) || empty($_POST['payment_id'])) {
        throw new Exception("Missing required field: payment_id");
    }
    
    $action = $_POST['action'];
    $payment_id = (int)$_POST['payment_id'];
    
    // Begin transaction
    $conn->begin_transaction();
    
    // Get payment details
    $paymentStmt = $conn->prepare(
        "SELECT p.*, t.user_id, u.name as tenant_name, pr.unit_no
         FROM payments p
         JOIN tenants t ON p.tenant_id = t.tenant_id
         JOIN users u ON t.user_id = u.user_id
         JOIN property pr ON t.unit_rented = pr.unit_id
         WHERE p.payment_id = ?"
    );
    $paymentStmt->bind_param("i", $payment_id);
    $paymentStmt->execute();
    $paymentResult = $paymentStmt->get_result();
    
    if ($paymentResult->num_rows === 0) {
        throw new Exception("Payment not found");
    }
    
    $payment = $paymentResult->fetch_assoc();
    
    // Check payment status - only pending payments can be approved or rejected
    if ($payment['status'] !== 'Pending') {
        throw new Exception("Only pending payments can be processed");
    }
    
    // Process action
    if ($action === 'approve') {
        // Additional validation for approving payment
        if (!isset($_POST['tenant_id']) || !isset($_POST['amount'])) {
            throw new Exception("Missing required fields for approval");
        }
        
        $tenant_id = (int)$_POST['tenant_id'];
        $amount = (float)$_POST['amount'];
        
        // Update payment status to Received
        $updateStmt = $conn->prepare(
            "UPDATE payments
             SET status = 'Received',
                 updated_at = CURRENT_TIMESTAMP,
                 processed_by = ?
             WHERE payment_id = ?"
        );
        $updateStmt->bind_param("si", $adminName, $payment_id);
        
        try {
            // After updating payment status
            if ($updateStmt->execute()) {
                try {
                    // Create notification for tenant with error logging
                    $tenantMessage = "Your payment of PHP " . number_format($payment['amount'], 2) . " has been approved.";
                    $tenantNotifResult = createNotification($payment['user_id'], $tenantMessage, 'payment_approved');
                    if (!$tenantNotifResult) {
                        error_log("Failed to create tenant notification for payment approval");
                    }

                    // Create notification for admin with error logging
                    $adminMessage = "Payment of PHP " . number_format($payment['amount'], 2) . " from " . 
                                  $payment['tenant_name'] . " (Unit " . $payment['unit_no'] . ") has been approved";
                    $adminNotifResult = createNotification($_SESSION['user_id'], $adminMessage, 'admin_payment');
                    if (!$adminNotifResult) {
                        error_log("Failed to create admin notification for payment approval");
                    }
                } catch (Exception $notifError) {
                    error_log("Notification error during payment approval: " . $notifError->getMessage());
                }
            }
        } catch (Exception $e) {
            error_log("Error in payment approval: " . $e->getMessage());
            throw $e;
        }
        
        // Check if this is a rent payment (only update balances for rent payments)
        $paymentTypeStmt = $conn->prepare("SELECT payment_type FROM payments WHERE payment_id = ?");
        $paymentTypeStmt->bind_param("i", $payment_id);
        $paymentTypeStmt->execute();
        $paymentTypeResult = $paymentTypeStmt->get_result();
        $paymentType = $paymentTypeResult->fetch_assoc()['payment_type'];
        
        if ($paymentType === 'rent') {
            // Get tenant's monthly rate
            $getTenantStmt = $conn->prepare("SELECT monthly_rate FROM tenants WHERE tenant_id = ?");
            $getTenantStmt->bind_param("i", $tenant_id);
            $getTenantStmt->execute();
            $tenantResult = $getTenantStmt->get_result();
            $monthly_rate = $tenantResult->fetch_assoc()['monthly_rate'];
            
            // Update tenant's outstanding balance
            $balanceStmt = $conn->prepare(
                "UPDATE tenants 
                SET outstanding_balance = GREATEST(0, outstanding_balance - ?) 
                WHERE tenant_id = ?"
            );
            $balanceStmt->bind_param("di", $amount, $tenant_id);
            $balanceStmt->execute();
            
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
                    last_payment_date = (SELECT payment_date FROM payments WHERE payment_id = ?) 
                WHERE tenant_id = ?"
            );
            $updateTenantStmt->bind_param("iii", $payable_months, $payment_id, $tenant_id);
            $updateTenantStmt->execute();
        }
        
        // Get tenant email for sending confirmation
        $emailStmt = $conn->prepare(
            "SELECT u.email 
             FROM users u 
             JOIN tenants t ON u.user_id = t.user_id 
             WHERE t.tenant_id = ?"
        );
        $emailStmt->bind_param("i", $tenant_id);
        $emailStmt->execute();
        $emailResult = $emailStmt->get_result();
        $tenantEmail = ($emailResult->num_rows > 0) ? $emailResult->fetch_assoc()['email'] : '';
        
        if (!empty($tenantEmail)) {
            // Send payment confirmation email
            $emailSent = sendPaymentConfirmationEmail(
                $tenantEmail,
                $payment,
                $payment['tenant_name'],
                $payment['unit_no']
            );
            
            if (!$emailSent) {
                // Log the failure but don't interrupt the process
                error_log("Failed to send payment confirmation email to: $tenantEmail");
            } else {
                // Log success
                error_log("Payment confirmation email sent successfully to: $tenantEmail");
            }
        }
        
        // Log activity
        $activityDetails = "Approved payment of ₱" . number_format($payment['amount'], 2) . 
                          " for " . $payment['tenant_name'] . " (Unit " . $payment['unit_no'] . ")";
        
        logActivity(
            $_SESSION['user_id'],
            'Approved Payment',
            $activityDetails
        );
        
        $message = "Payment approved successfully";
    } 
    elseif ($action === 'reject') {
        // Get rejection reason if provided
        $rejectionReason = isset($_POST['rejection_reason']) ? trim($_POST['rejection_reason']) : '';
        
        // Update payment status to Rejected
        $updateStmt = $conn->prepare(
            "UPDATE payments
             SET status = 'Rejected',
                 updated_at = CURRENT_TIMESTAMP,
                 processed_by = ?,
                 notes = CASE 
                          WHEN LENGTH(?) > 0 THEN CONCAT(COALESCE(notes, ''), ' [Rejection Reason: ', ?, ']')
                          ELSE CONCAT(COALESCE(notes, ''), ' [Payment rejected by admin]')
                        END
             WHERE payment_id = ?"
        );
        $updateStmt->bind_param("sssi", $adminName, $rejectionReason, $rejectionReason, $payment_id);
        
        try {
            // After updating payment status
            if ($updateStmt->execute()) {
                try {
                    // Create notification for tenant with error logging
                    $tenantMessage = "Your payment of PHP " . number_format($payment['amount'], 2) . " has been rejected.";
                    if (!empty($rejectionReason)) {
                        $tenantMessage .= " Reason: " . $rejectionReason;
                    }
                    $tenantNotifResult = createNotification($payment['user_id'], $tenantMessage, 'payment_rejected');
                    if (!$tenantNotifResult) {
                        error_log("Failed to create tenant notification for payment rejection");
                    }

                    // Create notification for admin with error logging
                    $adminMessage = "Payment of PHP " . number_format($payment['amount'], 2) . " from " . 
                                  $payment['tenant_name'] . " (Unit " . $payment['unit_no'] . ") has been rejected";
                    $adminNotifResult = createNotification($_SESSION['user_id'], $adminMessage, 'admin_payment');
                    if (!$adminNotifResult) {
                        error_log("Failed to create admin notification for payment rejection");
                    }
                } catch (Exception $notifError) {
                    error_log("Notification error during payment rejection: " . $notifError->getMessage());
                }
            }
        } catch (Exception $e) {
            error_log("Error in payment rejection: " . $e->getMessage());
            throw $e;
        }
        
        // Log activity
        $activityDetails = "Rejected payment of ₱" . number_format($payment['amount'], 2) . 
                           " for " . $payment['tenant_name'] . " (Unit " . $payment['unit_no'] . ")";
        if (!empty($rejectionReason)) {
            $activityDetails .= " - Reason: " . $rejectionReason;
        }
        
        logActivity(
            $_SESSION['user_id'],
            'Rejected Payment',
            $activityDetails
        );

        // Get tenant email for sending rejection notice
        $emailStmt = $conn->prepare(
            "SELECT u.email 
             FROM users u 
             JOIN tenants t ON u.user_id = t.user_id 
             WHERE t.tenant_id = ?"
        );
        $emailStmt->bind_param("i", $payment['tenant_id']);
        $emailStmt->execute();
        $emailResult = $emailStmt->get_result();
        $tenantEmail = ($emailResult->num_rows > 0) ? $emailResult->fetch_assoc()['email'] : '';
        
        if (!empty($tenantEmail)) {
            // Send payment rejection email
            $emailSent = sendPaymentRejectionEmail(
                $tenantEmail,
                $payment,
                $payment['tenant_name'],
                $payment['unit_no'],
                $rejectionReason
            );
            
            if (!$emailSent) {
                // Log the failure but don't interrupt the process
                error_log("Failed to send payment rejection email to: $tenantEmail");
            } else {
                // Log success
                error_log("Payment rejection email sent successfully to: $tenantEmail");
            }
        }
        
        $message = "Payment rejected successfully";
    }
    else {
        throw new Exception("Invalid action");
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
