<?php
// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Clean any existing output
while (ob_get_level()) ob_end_clean();
// Start fresh output buffer
ob_start();

header('Content-Type: application/json');

session_start();

require '../session/db.php';
require '../vendor/autoload.php';
require '../config/config.php';
require_once '../session/session_manager.php';
require_once '../session/audit_trail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = ['success' => false, 'message' => ''];

try {
    // Verify user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input received');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }

    if (!$data || !isset($data['reservation_id']) || !isset($data['status'])) {
        throw new Exception('Invalid input data');
    }

    $allowedStatuses = ['confirmed', 'cancelled', 'completed'];
    if (!in_array(strtolower($data['status']), $allowedStatuses)) {
        throw new Exception('Invalid status');
    }

    $conn->begin_transaction();

    // Update status
    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
    $stmt->bind_param("si", $data['status'], $data['reservation_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update status');
    }

    // Get admin/staff name for the audit log
    $userQuery = $conn->prepare("SELECT name, role FROM users WHERE user_id = ?");
    if (!$userQuery) {
        throw new Exception('Failed to prepare user query');
    }
    
    $userQuery->bind_param("i", $_SESSION['user_id']);
    $userQuery->execute();
    $userResult = $userQuery->get_result()->fetch_assoc();
    
    if (!$userResult) {
        throw new Exception('User details not found');
    }
    
    // Log the status update activity with user details
    $auditDetails = sprintf(
        "Reservation ID: %d updated to %s by %s (%s)",
        $data['reservation_id'],
        $data['status'],
        $userResult['name'],
        $userResult['role']
    );
    
    logActivity(
        $_SESSION['user_id'],
        'Update Reservation',
        $auditDetails
    );

    // If status is confirmed, update property status to Reserved
    if (strtolower($data['status']) === 'confirmed') {
        $stmt = $conn->prepare("
            UPDATE property p 
            JOIN reservations r ON p.unit_id = r.unit_id 
            SET p.status = 'Reserved' 
            WHERE r.reservation_id = ?
        ");
        $stmt->bind_param("i", $data['reservation_id']);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update property status');
        }
    }

    // Get user details for email
    $stmt = $conn->prepare("
        SELECT u.email, u.name, p.unit_no, r.viewing_date, r.viewing_time 
        FROM reservations r
        JOIN users u ON r.user_id = u.user_id
        JOIN property p ON r.unit_id = p.unit_id
        WHERE r.reservation_id = ?
    ");
    $stmt->bind_param("i", $data['reservation_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();

    // Send email notification using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->setFrom(SMTP_USERNAME, 'PropertyWise');
        $mail->addAddress($reservation['email'], $reservation['name']);
        $mail->isHTML(true);
        $mail->Subject = 'Reservation Status Update';

        // Format date and time
        $formattedDate = date('F j, Y', strtotime($reservation['viewing_date']));
        $formattedTime = date('g:i A', strtotime($reservation['viewing_time']));


        $statusMessage = match(strtolower($data['status'])) {
            'confirmed' => 'has been confirmed',
            'cancelled' => 'has been cancelled',
            'completed' => 'has been marked as completed'
        };

        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .details { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { text-align: center; padding-top: 20px; font-size: 14px; color: #666; }
                .highlight { color: #2563eb; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='color: #2563eb; margin: 0;'>Reservation Status Update</h2>
                </div>
                
                <div class='content'>
                    <p>Dear <strong>{$reservation['name']}</strong>,</p>
                    
                    <p>We hope this email finds you well. This is to inform you that your property viewing reservation {$statusMessage}.</p>
                    
                    <div class='details'>
                        <h3 style='margin-top: 0;'>Reservation Details:</h3>
                        <p><strong>Unit Number:</strong> {$reservation['unit_no']}</p>
                        <p><strong>Viewing Schedule:</strong> {$formattedDate}</p>
                        <p><strong>Time:</strong> {$formattedTime}</p>
                        <p><strong>Status:</strong> <span class='highlight'>" . ucfirst($data['status']) . "</span></p>
                    </div>
                    
                    <p>If you have any questions or need further assistance, please don't hesitate to contact our support team.</p>
                    
                    <p>Thank you for choosing our services.</p>
                    
                    <p>Best regards,<br>PropertyWise Team</p>
                </div>
                
                <div class='footer'>
                    <p>Contact Us:<br>
                    Email: support@propertywise.com<br>
                    Phone: (123) 456-7890</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
    }

    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Status updated successfully';

} catch (Exception $e) {
    if (isset($conn) && !$conn->connect_error) {
        $conn->rollback();
    }
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Update reservation error: " . $e->getMessage());
} finally {
    // Close all statements and connection
    if (isset($stmt)) $stmt->close();
    if (isset($userQuery)) $userQuery->close();
    if (isset($conn)) $conn->close();

    // Clear any output buffer
    if (ob_get_length()) ob_clean();
    
    // Send JSON response
    echo json_encode($response);
    exit();
}
?>