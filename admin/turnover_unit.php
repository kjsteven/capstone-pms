<?php
// Turn off PHP error display that could interfere with JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../session/audit_trail.php';
require_once '../utils/email_sender.php'; 

session_start();

// Always ensure content type is set for JSON responses
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Define turnover statuses
define("TURNOVER_NOTIFIED", "notified");
define("TURNOVER_SCHEDULED", "scheduled");
define("TURNOVER_INSPECTED", "inspected");
define("TURNOVER_COMPLETED", "completed");

// Get tenant details for the turnover process
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_details') {
    try {
        $tenant_id = isset($_GET['tenant_id']) ? (int)$_GET['tenant_id'] : 0;
        
        if (!$tenant_id) {
            throw new Exception('Invalid tenant ID');
        }
        
        // Get tenant and unit details with turnover status
        $stmt = $conn->prepare("
            SELECT t.*, u.name as tenant_name, u.email, p.unit_no, p.unit_id,
                   IFNULL(tt.status, 'pending') as turnover_status
            FROM tenants t
            JOIN users u ON t.user_id = u.user_id
            JOIN property p ON t.unit_rented = p.unit_id
            LEFT JOIN tenant_turnovers tt ON t.tenant_id = tt.tenant_id
            WHERE t.tenant_id = ? AND t.status = 'active'
        ");
        
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tenant = $result->fetch_assoc();
        
        if (!$tenant) {
            throw new Exception('Tenant not found or not active');
        }
        
        echo json_encode([
            'success' => true,
            'tenant_name' => $tenant['tenant_name'],
            'unit_no' => $tenant['unit_no'],
            'email' => $tenant['email'],
            'turnover_status' => $tenant['turnover_status'] // Include turnover status
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Handle POST requests for turnover process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Extract and validate tenant_id
        $tenant_id = isset($_POST['tenant_id']) ? (int)$_POST['tenant_id'] : 0;
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        if (!$tenant_id) {
            throw new Exception('Invalid tenant ID');
        }
        
        // Get tenant details (needed for all actions)
        $stmt = $conn->prepare("
            SELECT t.*, u.name as tenant_name, u.email, p.unit_no, p.unit_id 
            FROM tenants t
            JOIN users u ON t.user_id = u.user_id
            JOIN property p ON t.unit_rented = p.unit_id
            WHERE t.tenant_id = ?
        ");
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tenant = $result->fetch_assoc();
        
        if (!$tenant) {
            throw new Exception('Tenant not found');
        }
        
        // Start a transaction
        $conn->begin_transaction();
        
        // Check if there's an existing turnover record
        $checkStmt = $conn->prepare("SELECT * FROM tenant_turnovers WHERE tenant_id = ?");
        $checkStmt->bind_param("i", $tenant_id);
        $checkStmt->execute();
        $existingTurnover = $checkStmt->get_result()->fetch_assoc();
        
        switch ($action) {
            case 'notify':
                $message = isset($_POST['message']) ? $_POST['message'] : '';
                
                // Create or update turnover record
                if ($existingTurnover) {
                    $updateStmt = $conn->prepare("
                        UPDATE tenant_turnovers 
                        SET status = ?, 
                            notification_date = CURRENT_TIMESTAMP, 
                            notification_message = ?,
                            updated_at = CURRENT_TIMESTAMP 
                        WHERE tenant_id = ?
                    ");
                    $status = TURNOVER_NOTIFIED;
                    $updateStmt->bind_param("ssi", $status, $message, $tenant_id);
                    $updateStmt->execute();
                } else {
                    $insertStmt = $conn->prepare("
                        INSERT INTO tenant_turnovers 
                        (tenant_id, status, notification_date, notification_message, created_at, updated_at) 
                        VALUES (?, ?, CURRENT_TIMESTAMP, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                    ");
                    $status = TURNOVER_NOTIFIED;
                    $insertStmt->bind_param("iss", $tenant_id, $status, $message);
                    $insertStmt->execute();
                }
                
                // Send email notification
                $result = sendTurnoverNotificationEmail(
                    $tenant['email'],
                    $tenant['tenant_name'],
                    $tenant['unit_no'],
                    $message
                );
                
                if (!$result) {
                    error_log('Failed to send turnover notification email to ' . $tenant['email']);
                }
                
                // Log activity
                logActivity(
                    $_SESSION['user_id'],
                    'Turnover Notification',
                    "Sent turnover notification to {$tenant['tenant_name']} for unit {$tenant['unit_no']}"
                );
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Notification sent successfully']);
                break;
                
            case 'schedule':
                $inspection_date = $_POST['inspection_date'] ?? null;
                $staff_assigned = isset($_POST['staff_assigned']) ? (int)$_POST['staff_assigned'] : null;
                $notes = $_POST['notes'] ?? '';
                
                if (!$inspection_date || !$staff_assigned) {
                    throw new Exception('Inspection date and staff assignment are required');
                }
                
                // Get staff name for activity log and email
                $staffQuery = $conn->prepare("SELECT name FROM staff WHERE staff_id = ?");
                $staffQuery->bind_param("i", $staff_assigned);
                $staffQuery->execute();
                $staffResult = $staffQuery->get_result();
                $staffName = $staffResult->fetch_assoc()['name'] ?? 'Unknown staff';
                
                // Update turnover record
                if ($existingTurnover) {
                    $updateStmt = $conn->prepare("
                        UPDATE tenant_turnovers 
                        SET status = ?, 
                            inspection_date = ?, 
                            staff_assigned = ?, 
                            inspection_notes = ?, 
                            updated_at = CURRENT_TIMESTAMP 
                        WHERE tenant_id = ?
                    ");
                    $status = TURNOVER_SCHEDULED;
                    $updateStmt->bind_param("ssisi", $status, $inspection_date, $staff_assigned, $notes, $tenant_id);
                    $updateStmt->execute();
                } else {
                    $insertStmt = $conn->prepare("
                        INSERT INTO tenant_turnovers 
                        (tenant_id, status, inspection_date, staff_assigned, inspection_notes, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                    ");
                    $status = TURNOVER_SCHEDULED;
                    $insertStmt->bind_param("issis", $tenant_id, $status, $inspection_date, $staff_assigned, $notes);
                    $insertStmt->execute();
                }
                
                // Send inspection schedule email
                $result = sendInspectionScheduleEmail(
                    $tenant['email'],
                    $tenant['tenant_name'],
                    $tenant['unit_no'],
                    $inspection_date,
                    $staffName,
                    $notes
                );
                
                if (!$result) {
                    error_log('Failed to send inspection schedule email to ' . $tenant['email']);
                }
                
                // Log activity
                logActivity(
                    $_SESSION['user_id'],
                    'Turnover Inspection Scheduled',
                    "Scheduled inspection for {$tenant['unit_no']} on " . date('Y-m-d H:i', strtotime($inspection_date)) . " with {$staffName}"
                );
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Inspection scheduled successfully']);
                break;
                
            case 'inspect':
                $cleanliness = $_POST['cleanliness'] ?? null;
                $damages = $_POST['damages'] ?? null;
                $equipment = $_POST['equipment'] ?? null;
                $inspection_report = $_POST['inspection_report'] ?? '';
                
                if (!$cleanliness || !$damages || !$equipment || !$inspection_report) {
                    throw new Exception('All inspection fields are required');
                }
                
                // Process and save inspection photos
                $photo_paths = [];
                if (isset($_FILES['inspection_photos'])) {
                    $photos = $_FILES['inspection_photos'];
                    $upload_dir = '../uploads/inspections/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Handle multiple file uploads
                    for ($i = 0; $i < count($photos['name']); $i++) {
                        if ($photos['error'][$i] === 0) {
                            $file_extension = pathinfo($photos['name'][$i], PATHINFO_EXTENSION);
                            $new_filename = 'inspection_' . $tenant_id . '_' . time() . '_' . $i . '.' . $file_extension;
                            $upload_path = $upload_dir . $new_filename;
                            
                            if (move_uploaded_file($photos['tmp_name'][$i], $upload_path)) {
                                $photo_paths[] = $upload_path;
                            }
                        }
                    }
                }
                
                $photos_json = json_encode($photo_paths);
                
                // Update turnover record with inspection details
                if ($existingTurnover) {
                    $updateStmt = $conn->prepare("
                        UPDATE tenant_turnovers 
                        SET status = ?, 
                            cleanliness_rating = ?, 
                            damage_rating = ?, 
                            equipment_rating = ?, 
                            inspection_report = ?, 
                            inspection_photos = ?, 
                            inspection_completed_date = CURRENT_TIMESTAMP,
                            updated_at = CURRENT_TIMESTAMP 
                        WHERE tenant_id = ?
                    ");
                    $status = TURNOVER_INSPECTED;
                    $updateStmt->bind_param("ssssssi", $status, $cleanliness, $damages, $equipment, $inspection_report, $photos_json, $tenant_id);
                    $updateStmt->execute();
                } else {
                    $insertStmt = $conn->prepare("
                        INSERT INTO tenant_turnovers 
                        (tenant_id, status, cleanliness_rating, damage_rating, equipment_rating, inspection_report, inspection_photos, inspection_completed_date, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                    ");
                    $status = TURNOVER_INSPECTED;
                    $insertStmt->bind_param("issssss", $tenant_id, $status, $cleanliness, $damages, $equipment, $inspection_report, $photos_json);
                    $insertStmt->execute();
                }
                
                // Log activity
                logActivity(
                    $_SESSION['user_id'],
                    'Turnover Inspection Completed',
                    "Completed inspection for {$tenant['unit_no']}, Cleanliness: {$cleanliness}, Damages: {$damages}"
                );
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Inspection completed successfully']);
                break;
                
            case 'complete':
                $notes = $_POST['notes'] ?? '';
                
                // Update turnover record
                if ($existingTurnover) {
                    $updateStmt = $conn->prepare("
                        UPDATE tenant_turnovers 
                        SET status = ?, 
                            completion_notes = ?, 
                            completion_date = CURRENT_TIMESTAMP,
                            updated_at = CURRENT_TIMESTAMP 
                        WHERE tenant_id = ?
                    ");
                    $status = TURNOVER_COMPLETED;
                    $updateStmt->bind_param("ssi", $status, $notes, $tenant_id);
                    $updateStmt->execute();
                } else {
                    throw new Exception('Cannot complete turnover without prior steps');
                }
                
                // Get inspection results for the email
                $inspectionQuery = $conn->prepare("
                    SELECT cleanliness_rating, damage_rating, equipment_rating 
                    FROM tenant_turnovers
                    WHERE tenant_id = ?
                ");
                $inspectionQuery->bind_param("i", $tenant_id);
                $inspectionQuery->execute();
                $inspectionResult = $inspectionQuery->get_result();
                $inspection = $inspectionResult->fetch_assoc();
                
                $inspectionResults = [];
                if ($inspection) {
                    $inspectionResults = [
                        'cleanliness' => $inspection['cleanliness_rating'],
                        'damages' => $inspection['damage_rating'],
                        'equipment' => $inspection['equipment_rating']
                    ];
                }
                
                // Send turnover completion email
                $result = sendTurnoverCompletionEmail(
                    $tenant['email'],
                    $tenant['tenant_name'],
                    $tenant['unit_no'],
                    $inspectionResults
                );
                
                if (!$result) {
                    error_log('Failed to send turnover completion email to ' . $tenant['email']);
                }
                
                // Update tenant status to turnover
                $updateTenantStmt = $conn->prepare("UPDATE tenants SET status = 'turnover' WHERE tenant_id = ?");
                $updateTenantStmt->bind_param("i", $tenant_id);
                $updateTenantStmt->execute();
                
                // Update property status
                $updatePropertyStmt = $conn->prepare("UPDATE property SET status = 'Available' WHERE unit_id = ?");
                $updatePropertyStmt->bind_param("i", $tenant['unit_id']);
                $updatePropertyStmt->execute();
                
                // Log activity
                logActivity(
                    $_SESSION['user_id'],
                    'Turnover Completed',
                    "Completed turnover for {$tenant['tenant_name']} from unit {$tenant['unit_no']}"
                );
                
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Turnover completed successfully']);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->connect_error === null) {
            $conn->rollback();
        }
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error processing turnover: ' . $e->getMessage()
        ]);
    }
    exit();
}

// Invalid request method
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit();
?>
