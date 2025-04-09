<?php

session_start();

require_once '../session/db.php';
require_once '../session/audit_trail.php';
require_once '../notification/notif_handler.php';



header('Content-Type: application/json');

// Get the action from request (check both GET and POST)
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    switch($action) {
        case 'view':
            // Get KYC details for viewing
            $kycId = isset($_GET['kyc_id']) ? (int)$_GET['kyc_id'] : 0;
            
            $query = "SELECT k.*, u.email as user_email, u.name as user_name,
                     (SELECT name FROM users WHERE user_id = ? AND role = 'Admin') as admin_name
                     FROM kyc_verification k 
                     JOIN users u ON k.user_id = u.user_id 
                     WHERE k.kyc_id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $_SESSION['user_id'], $kycId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($kyc = $result->fetch_assoc()) {
                // Format dates properly
                $kyc['submission_date'] = date('Y-m-d H:i:s', strtotime($kyc['submission_date']));
                $kyc['verification_date'] = $kyc['verification_date'] ? date('Y-m-d H:i:s', strtotime($kyc['verification_date'])) : null;
                
                // Add verification info
                $kyc['verified_by'] = $kyc['admin_name'];
                
                echo json_encode(['success' => true, 'kyc' => $kyc]);
            } else {
                throw new Exception("KYC record not found");
            }
            break;

        case 'approve':
            $kycId = isset($_POST['kyc_id']) ? (int)$_POST['kyc_id'] : 0;
            
            if (!$kycId) {
                throw new Exception("Invalid KYC ID");
            }
            
            $conn->begin_transaction();
            
            try {
                // Get user_id first
                $userQuery = "SELECT user_id FROM kyc_verification WHERE kyc_id = ?";
                $userStmt = $conn->prepare($userQuery);
                $userStmt->bind_param("i", $kycId);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                $userData = $userResult->fetch_assoc();
                $userStmt->close();

                if (!$userData) {
                    throw new Exception("KYC record not found");
                }

                // Get admin name first
                $adminQuery = "SELECT name FROM users WHERE user_id = ? AND role = 'Admin'";
                $adminStmt = $conn->prepare($adminQuery);
                $adminStmt->bind_param("i", $_SESSION['user_id']);
                $adminStmt->execute();
                $adminResult = $adminStmt->get_result();
                
                if ($adminResult->num_rows === 0) {
                    throw new Exception("Unauthorized access");
                }
                
                $adminName = $adminResult->fetch_assoc()['name'];
                $adminStmt->close();

                // Update KYC status with admin name
                $updateKyc = $conn->prepare("
                    UPDATE kyc_verification SET 
                    verification_status = 'approved',
                    admin_remarks = ?,
                    verification_date = NOW()
                    WHERE kyc_id = ? AND verification_status = 'pending'
                ");
                
                $remarks = "Approved by " . $adminName;
                $updateKyc->bind_param("si", $remarks, $kycId);
                $updateKyc->execute();
                
                if ($updateKyc->affected_rows === 0) {
                    throw new Exception("KYC verification already processed or not found");
                }
                
                // Log the action
                logActivity($_SESSION['user_id'], 'KYC Approval', "Approved KYC verification #$kycId");

                // Create notification for user
                $userMessage = "Your KYC verification has been approved. You can now access all features.";
                createNotification($userData['user_id'], $userMessage, 'kyc_approved');

                // Create notification for admin
                $adminMessage = "KYC verification #" . $kycId . " has been approved successfully.";
                createNotification($_SESSION['user_id'], $adminMessage, 'admin_kyc');
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'KYC verification approved successfully']);
            } catch (Exception $e) {
                $conn->rollback();
                throw new Exception("Failed to approve KYC: " . $e->getMessage());
            }
            break;

        case 'reject':
            $kycId = isset($_POST['kyc_id']) ? (int)$_POST['kyc_id'] : 0;
            $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
            
            if (empty($reason)) {
                throw new Exception("Rejection reason is required");
            }
            
            $conn->begin_transaction();
            
            try {
                // Get user_id first
                $userQuery = "SELECT user_id FROM kyc_verification WHERE kyc_id = ?";
                $userStmt = $conn->prepare($userQuery);
                $userStmt->bind_param("i", $kycId);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                $userData = $userResult->fetch_assoc();
                $userStmt->close();

                if (!$userData) {
                    throw new Exception("KYC record not found");
                }

                // Get admin name first
                $adminQuery = "SELECT name FROM users WHERE user_id = ? AND role = 'Admin'";
                $adminStmt = $conn->prepare($adminQuery);
                $adminStmt->bind_param("i", $_SESSION['user_id']);
                $adminStmt->execute();
                $adminResult = $adminStmt->get_result();
                $adminName = $adminResult->fetch_assoc()['name'];
                $adminStmt->close();

                // Update remarks to include admin name
                $fullRemarks = "Rejected by " . $adminName . ": " . $reason;
                
                // Update KYC status
                $updateKyc = $conn->prepare("UPDATE kyc_verification SET 
                    verification_status = 'rejected',
                    admin_remarks = ?,
                    verification_date = NOW()
                    WHERE kyc_id = ? AND verification_status = 'pending'");
                
                $updateKyc->bind_param("si", $fullRemarks, $kycId);
                $updateKyc->execute();
                
                if ($updateKyc->affected_rows === 0) {
                    throw new Exception("KYC verification already processed or not found");
                }
                
                // Log the action
                logActivity($_SESSION['user_id'], 'KYC Rejection', "Rejected KYC verification #$kycId");

                // Create notification for user
                $userMessage = "Your KYC verification was rejected. Reason: " . $reason;
                createNotification($userData['user_id'], $userMessage, 'kyc_rejected');

                // Create notification for admin
                $adminMessage = "KYC verification #" . $kycId . " has been rejected.";
                createNotification($_SESSION['user_id'], $adminMessage, 'admin_kyc');
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'KYC verification rejected successfully']);
            } catch (Exception $e) {
                $conn->rollback();
                throw new Exception("Failed to reject KYC: " . $e->getMessage());
            }
            break;

        case 'archive':
            $kycId = isset($_POST['kyc_id']) ? (int)$_POST['kyc_id'] : 0;
            
            if (!$kycId) {
                throw new Exception("Invalid KYC ID");
            }
            
            $conn->begin_transaction();
            
            try {
                $updateKyc = $conn->prepare("
                    UPDATE kyc_verification 
                    SET archived = 1 
                    WHERE kyc_id = ?
                ");
                
                $updateKyc->bind_param("i", $kycId);
                if (!$updateKyc->execute()) {
                    throw new Exception("Failed to archive KYC record");
                }
                
                // Log the action
                logActivity(
                    $_SESSION['user_id'], 
                    'Archive KYC', 
                    "Archived KYC verification #$kycId"
                );
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'KYC record archived successfully']);
            } catch (Exception $e) {
                $conn->rollback();
                throw new Exception("Failed to archive KYC: " . $e->getMessage());
            }
            break;
            
        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
