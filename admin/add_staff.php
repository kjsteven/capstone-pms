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

session_start();

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
            $mail->isHTML(true); // Changed to HTML format
            $mail->Subject = 'Welcome to PropertyWise - Your Account Details';
            
            // Modern HTML email template
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1'>
                <style>
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        line-height: 1.6;
                        color: #333333;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        padding: 20px;
                    }
                    .header {
                        background-color: #3498db;
                        color: white;
                        padding: 20px;
                        text-align: center;
                        border-radius: 5px 5px 0 0;
                    }
                    .content {
                        background-color: #ffffff;
                        padding: 30px;
                        border-left: 1px solid #e0e0e0;
                        border-right: 1px solid #e0e0e0;
                    }
                    .footer {
                        background-color: #f5f5f5;
                        padding: 15px;
                        text-align: center;
                        font-size: 12px;
                        color: #666666;
                        border-radius: 0 0 5px 5px;
                        border: 1px solid #e0e0e0;
                    }
                    .credentials {
                        background-color: #f9f9f9;
                        padding: 15px;
                        margin: 20px 0;
                        border-left: 4px solid #3498db;
                    }
                    .button {
                        display: inline-block;
                        padding: 10px 20px;
                        background-color: #3498db;
                        color: white;
                        text-decoration: none;
                        border-radius: 4px;
                        margin: 20px 0;
                    }
                    .warning {
                        color: #e74c3c;
                        font-size: 13px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Welcome to PropertyWise!</h1>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>$staffName</strong>,</p>
                        
                        <p>We're delighted to welcome you to the PropertyWise team! Your staff account has been successfully created.</p>
                        
                        <div class='credentials'>
                            <p><strong>Account Details:</strong></p>
                            <p>Email: <strong>$staffEmail</strong></p>
                            <p>Password: <strong>$password</strong></p>
                        </div>
                        
                        <p>To access your account, please click the button below:</p>
                        
                        <a href='https://propertywise.site/authentication/stafflogin.php' class='button'>Log In Now</a>
                        
                        <p class='warning'><strong>Important:</strong> For security reasons, we recommend changing your password immediately after your first login.</p>
                        
                        <p>If you have any questions or need assistance, please don't hesitate to contact your administrator.</p>
                        
                        <p>Best regards,<br>The PropertyWise Team</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " PropertyWise. All rights reserved.</p>
                        <p>This is an automated email, please do not reply.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            // Plain text alternative
            $mail->AltBody = "Hello $staffName,\n\n"
                       . "Welcome to PropertyWise! Your account has been created.\n\n"
                       . "Email: $staffEmail\n"
                       . "Password: $password\n\n"
                       . "To access your account, please visit: https://propertywise.site/authentication/stafflogin.php\n\n"
                       . "For security reasons, we recommend changing your password after your first login.\n\n"
                       . "Best regards,\nThe PropertyWise Team";

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