<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once '../config/config.php';

/**
 * Sends KYC submission confirmation email to the user
 * 
 * @param string $userEmail The recipient's email address (must be valid email format)
 * @param string $firstName The recipient's first name (must be sanitized before passing)
 * @return bool Returns true if email sent successfully, false otherwise
 * @throws Exception When email configuration is invalid or sending fails
 * @security This function handles sensitive KYC information and should only be called after:
 *          - Input validation and sanitization
 *          - User authentication verification
 *          - Rate limiting checks (if implemented)
 *          - XSS prevention (all inputs must be escaped)
 *          - CSRF protection validation
 */
function sendKYCSubmissionEmail($userEmail, $firstName) {
    // Validate email format
    if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email format attempted: " . $userEmail);
        return false;
    }

    // Basic sanitization of firstName
    $firstName = htmlspecialchars(trim($firstName), ENT_QUOTES, 'UTF-8');
    if (empty($firstName)) {
        error_log("Empty or invalid first name provided for KYC email");
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;  // Using constant from config
        $mail->Password = SMTP_PASSWORD;  // Using constant from config
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom(SMTP_USERNAME, 'PropertyWise');  // Using constant from config
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'PropertyWise - KYC Verification Submission Confirmation';

        // Calculate expected completion date range (3-5 business days)
        $startDate = new DateTime();
        $startDate->modify('+3 weekdays');
        $endDate = new DateTime();
        $endDate->modify('+5 weekdays');

        $emailBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #1e40af; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0;'>KYC Verification Submission</h1>
            </div>
            
            <div style='padding: 20px; background-color: #f8fafc;'>
                <p>Dear {$firstName},</p>
                
                <p>Thank you for submitting your KYC verification documents to PropertyWise. This email confirms that we have received your application.</p>
                
                <div style='background-color: #e2e8f0; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h2 style='color: #1e40af; margin-top: 0;'>Timeline for Verification</h2>
                    <p>Your KYC verification is expected to be completed between:</p>
                    <p style='font-weight: bold;'>{$startDate->format('F d, Y')} - {$endDate->format('F d, Y')}</p>
                </div>
                
                <h3 style='color: #1e40af;'>What happens next?</h3>
                <ol style='color: #475569;'>
                    <li>Our compliance team will review your submitted documents</li>
                    <li>We will verify the information provided</li>
                    <li>You will receive a notification about the status of your verification</li>
                </ol>
                
                <p style='background-color: #fef3c7; padding: 10px; border-left: 4px solid #f59e0b;'>
                    Note: The verification process typically takes 3-5 business days. We will notify you once the review is complete.
                </p>
                
                <p>If you have any questions about your KYC verification, please don't hesitate to contact our support team.</p>
                
                <p style='margin-top: 20px;'>Best regards,<br>PropertyWise Team</p>
            </div>
        </div>";

        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $emailBody));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}
