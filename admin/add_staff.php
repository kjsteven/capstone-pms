<?php
// Buffer all output
ob_start();

require_once '../session/session_manager.php';
require '../session/db.php';
require '../vendor/autoload.php'; 
require '../config/config.php';

// Enable error reporting for debugging
ini_set('display_errors', 0); // Disable display_errors to prevent it from breaking JSON
error_reporting(E_ALL);

// Set proper JSON header
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

start_secure_session();

function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, '../logs/error.log');
}

// Clear any previous output
if (ob_get_length()) ob_clean();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log incoming data
        logError('Received POST request: ' . print_r($_POST, true));

        // Get and validate form data
        $staffName = trim($_POST['name'] ?? '');
        $staffEmail = trim($_POST['email'] ?? '');
        $staffSpecialty = trim($_POST['specialty'] ?? '');
        $staffPhone = trim($_POST['phone'] ?? '');

        if (empty($staffName) || empty($staffEmail) || empty($staffSpecialty) || empty($staffPhone)) {
            throw new Exception('Please fill out all fields.');
        }

        if (!filter_var($staffEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }

        if (!preg_match('/^\d{11}$/', $staffPhone)) {
            throw new Exception('Phone number must be exactly 11 digits.');
        }

        // Check for existing email - using prepared statement
        $stmt = $conn->prepare("SELECT email FROM staff WHERE email = ?");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }

        $stmt->bind_param("s", $staffEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Email already exists.');
        }

        // Generate password and hash it
        $password = bin2hex(random_bytes(8));
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new staff member
        $insertStmt = $conn->prepare("INSERT INTO staff (Name, Email, Specialty, Phone_Number, Password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$insertStmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }

        $insertStmt->bind_param("sssss", $staffName, $staffEmail, $staffSpecialty, $staffPhone, $hashedPassword);
        
        if (!$insertStmt->execute()) {
            throw new Exception('Failed to insert staff record: ' . $insertStmt->error);
        }

        // Initialize PHPMailer with debug mode
        $mail = new PHPMailer(true);
        
        try {
            $mail->SMTPDebug = 0; // Disable debug output that might break JSON
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom(SMTP_USERNAME, 'PropertyWise');
            $mail->addAddress($staffEmail, $staffName);

            // Content
            $mail->isHTML(false);
            $mail->Subject = 'Your Staff Account Information';
            $mail->Body = "Hello $staffName,\n\n"
                       . "Your account has been created.\n\n"
                       . "Email: $staffEmail\n"
                       . "Password: $password\n\n"
                       . "Regards,\nPropertyWise Team";

            $mail->send();
            logError('Email sent successfully to ' . $staffEmail);
            
            $response = [
                'success' => true,
                'message' => 'Staff account created successfully and email sent.'
            ];
        } catch (Exception $e) {
            logError('Email sending failed: ' . $e->getMessage());
            
            $response = [
                'success' => true,
                'message' => 'Staff account created successfully, but email delivery failed. Please contact support.'
            ];
        }
    } catch (Exception $e) {
        logError('Error in add_staff.php: ' . $e->getMessage());
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request method.'
    ];
}

// Clear any output buffers
while (ob_get_length()) ob_end_clean();

// Send the JSON response
echo json_encode($response);
exit;

?>