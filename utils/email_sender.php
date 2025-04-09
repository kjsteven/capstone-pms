<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends payment confirmation email to tenant
 * 
 * @param string $email Recipient's email address
 * @param array $payment Payment details (amount, date, status, reference, etc.)
 * @param string $tenantName Tenant's name
 * @param string $unitNo Unit number
 * @return bool True if email sent successfully, false otherwise
 */
function sendPaymentConfirmationEmail($email, $payment, $tenantName, $unitNo) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->setFrom(SMTP_USERNAME, 'PropertyWise | Payment Confirmation');
        $mail->addAddress($email, $tenantName);
        $mail->isHTML(true);
        
        // Format payment date
        $paymentDate = date('F d, Y', strtotime($payment['payment_date']));
        
        // Format payment amount
        $amount = number_format($payment['amount'], 2);
        
        // Determine payment method
        $paymentMethod = !empty($payment['gcash_number']) ? 'GCash' : 'Cash';
        
        // Format reference number display
        $referenceDisplay = !empty($payment['reference_number']) ? 
            "<p style='margin: 5px 0;'><strong>Reference #:</strong> {$payment['reference_number']}</p>" : "";
        
        // Format payment type and bill item
        $paymentTypeDisplay = '';
        if (isset($payment['payment_type']) && $payment['payment_type'] === 'rent') {
            $paymentTypeDisplay = "<p style='margin: 5px 0;'><strong>Payment Type:</strong> Rent Payment</p>";
        } else {
            $billItem = !empty($payment['bill_item']) ? $payment['bill_item'] : 'Other Payment';
            $paymentTypeDisplay = "<p style='margin: 5px 0;'><strong>Payment Type:</strong> {$billItem}</p>";
        }

        // Email content with HTML and inline CSS (Tailwind-like styles)
        $mail->Subject = 'Payment Confirmation - PropertyWise';
        $mail->Body = '
        <div style="font-family: \'Arial\', sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
            <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="color: #1f2937; font-size: 24px; font-weight: bold; margin-bottom: 10px;">Payment Confirmation</h1>
                    <p style="color: #6b7280; font-size: 16px; margin-bottom: 20px;">Thank you for your payment</p>
                </div>
                
                <div style="background-color: #f8fafc; border: 1px dashed #e2e8f0; padding: 20px; margin: 20px 0; border-radius: 8px;">
                    <p style="margin: 5px 0;"><strong>Tenant:</strong> ' . $tenantName . '</p>
                    <p style="margin: 5px 0;"><strong>Unit #:</strong> ' . $unitNo . '</p>
                    <p style="margin: 5px 0;"><strong>Amount:</strong> ₱' . $amount . '</p>
                    <p style="margin: 5px 0;"><strong>Date:</strong> ' . $paymentDate . '</p>
                    <p style="margin: 5px 0;"><strong>Payment Method:</strong> ' . $paymentMethod . '</p>
                    ' . $referenceDisplay . '
                    ' . $paymentTypeDisplay . '
                    <p style="margin: 5px 0;"><strong>Status:</strong> <span style="color: #047857; font-weight: bold;">Confirmed</span></p>
                </div>
                
                <div style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 20px;">
                    <p>This is an automatic confirmation of your payment. Please keep this for your records.</p>
                    <p style="margin-top: 10px;">If you have any questions, please contact our property management office.</p>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px;">
                    <p>This is an automated message, please do not reply.</p>
                    <p style="margin-top: 5px;">&copy; ' . date("Y") . ' PropertyWise. All rights reserved.</p>
                </div>
            </div>
        </div>';

        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Payment Confirmation\n\nTenant: {$tenantName}\nUnit #: {$unitNo}\nAmount: ₱{$amount}\nDate: {$paymentDate}\nPayment Method: {$paymentMethod}\nStatus: Confirmed";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error sending payment confirmation email: ' . $e->getMessage());
        return false;
    }
}

/**
 * Sends payment rejection email to tenant
 * 
 * @param string $email Recipient's email address
 * @param array $payment Payment details (amount, date, status, reference, etc.)
 * @param string $tenantName Tenant's name
 * @param string $unitNo Unit number
 * @param string $rejectionReason Optional reason for rejection
 * @return bool True if email sent successfully, false otherwise
 */
function sendPaymentRejectionEmail($email, $payment, $tenantName, $unitNo, $rejectionReason = '') {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->setFrom(SMTP_USERNAME, 'PropertyWise | Payment Notification');
        $mail->addAddress($email, $tenantName);
        $mail->isHTML(true);
        
        // Format payment date
        $paymentDate = date('F d, Y', strtotime($payment['payment_date']));
        
        // Format payment amount
        $amount = number_format($payment['amount'], 2);
        
        // Determine payment method
        $paymentMethod = !empty($payment['gcash_number']) ? 'GCash' : 'Cash';
        
        // Format reference number display
        $referenceDisplay = !empty($payment['reference_number']) ? 
            "<p style='margin: 5px 0;'><strong>Reference #:</strong> {$payment['reference_number']}</p>" : "";
        
        // Format rejection reason
        $reasonDisplay = !empty($rejectionReason) ? 
            "<p style='margin: 10px 0;'><strong>Reason:</strong> {$rejectionReason}</p>" : 
            "<p style='margin: 10px 0;'>Please contact property management for more information.</p>";
        
        // Format payment type and bill item
        $paymentTypeDisplay = '';
        if (isset($payment['payment_type']) && $payment['payment_type'] === 'rent') {
            $paymentTypeDisplay = "<p style='margin: 5px 0;'><strong>Payment Type:</strong> Rent Payment</p>";
        } else {
            $billItem = !empty($payment['bill_item']) ? $payment['bill_item'] : 'Other Payment';
            $paymentTypeDisplay = "<p style='margin: 5px 0;'><strong>Payment Type:</strong> {$billItem}</p>";
        }

        // Email content with HTML and inline CSS
        $mail->Subject = 'Payment Rejection Notice - PropertyWise';
        $mail->Body = '
        <div style="font-family: \'Arial\', sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
            <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="color: #1f2937; font-size: 24px; font-weight: bold; margin-bottom: 10px;">Payment Rejection Notice</h1>
                    <p style="color: #6b7280; font-size: 16px; margin-bottom: 20px;">Your recent payment could not be processed</p>
                </div>
                
                <div style="background-color: #fee2e2; border: 1px dashed #ef4444; padding: 20px; margin: 20px 0; border-radius: 8px;">
                    <p style="margin: 5px 0;"><strong>Tenant:</strong> ' . $tenantName . '</p>
                    <p style="margin: 5px 0;"><strong>Unit #:</strong> ' . $unitNo . '</p>
                    <p style="margin: 5px 0;"><strong>Amount:</strong> ₱' . $amount . '</p>
                    <p style="margin: 5px 0;"><strong>Date:</strong> ' . $paymentDate . '</p>
                    <p style="margin: 5px 0;"><strong>Payment Method:</strong> ' . $paymentMethod . '</p>
                    ' . $referenceDisplay . '
                    ' . $paymentTypeDisplay . '
                    <p style="margin: 5px 0;"><strong>Status:</strong> <span style="color: #b91c1c; font-weight: bold;">Rejected</span></p>
                    ' . $reasonDisplay . '
                </div>
                
                <div style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 20px;">
                    <p>Please submit a new payment or contact our property management office for assistance.</p>
                    <p style="margin-top: 10px;">If you believe this is an error, please contact our property management office.</p>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px;">
                    <p>This is an automated message, please do not reply.</p>
                    <p style="margin-top: 5px;">&copy; ' . date("Y") . ' PropertyWise. All rights reserved.</p>
                </div>
            </div>
        </div>';

        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Payment Rejection Notice\n\nTenant: {$tenantName}\nUnit #: {$unitNo}\nAmount: ₱{$amount}\nDate: {$paymentDate}\nPayment Method: {$paymentMethod}\nStatus: Rejected";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error sending payment rejection email: ' . $e->getMessage());
        return false;
    }
}

/**
 * Sends turnover notification email to tenant
 * 
 * @param string $email Recipient's email address
 * @param string $tenantName Tenant's name
 * @param string $unitNo Unit number
 * @param string $message Custom message for the tenant
 * @param array $additionalDetails Optional additional details
 * @return bool True if email sent successfully, false otherwise
 */
function sendTurnoverNotificationEmail($email, $tenantName, $unitNo, $message, $additionalDetails = []) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->setFrom(SMTP_USERNAME, 'PropertyWise | Turnover Notification');
        $mail->addAddress($email, $tenantName);
        $mail->isHTML(true);
        
        // Format message with line breaks for HTML
        $formattedMessage = nl2br($message);
        
        // Email content with HTML and inline CSS
        $mail->Subject = 'Unit Turnover Notification - PropertyWise';
        $mail->Body = '
        <div style="font-family: \'Arial\', sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
            <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="color: #1f2937; font-size: 24px; font-weight: bold; margin-bottom: 10px;">Unit Turnover Notification</h1>
                    <p style="color: #6b7280; font-size: 16px; margin-bottom: 20px;">Important information about your move-out process</p>
                </div>
                
                <div style="margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong>Dear ' . $tenantName . ',</strong></p>
                    <div style="margin: 15px 0; line-height: 1.6; color: #4b5563;">
                        ' . $formattedMessage . '
                    </div>
                </div>
                
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; margin: 20px 0; border-radius: 8px;">
                    <p style="margin: 5px 0;"><strong>Tenant:</strong> ' . $tenantName . '</p>
                    <p style="margin: 5px 0;"><strong>Unit #:</strong> ' . $unitNo . '</p>';
        
        // Add any additional details if provided
        if (!empty($additionalDetails)) {
            foreach($additionalDetails as $label => $value) {
                $mail->Body .= '<p style="margin: 5px 0;"><strong>' . $label . ':</strong> ' . $value . '</p>';
            }
        }
                
        $mail->Body .= '
                </div>
                
                <div style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 20px;">
                    <p>Please prepare your unit for inspection according to our turnover guidelines.</p>
                    <p style="margin-top: 10px;">If you have any questions, please contact our property management office.</p>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px;">
                    <p>This is an automated message from our property management system.</p>
                    <p style="margin-top: 5px;">&copy; ' . date("Y") . ' PropertyWise. All rights reserved.</p>
                </div>
            </div>
        </div>';

        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Unit Turnover Notification\n\nDear {$tenantName},\n\n{$message}\n\nUnit #: {$unitNo}\n\nPlease prepare your unit for inspection according to our turnover guidelines.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error sending turnover notification email: ' . $e->getMessage());
        return false;
    }
}

/**
 * Sends turnover inspection notification email to tenant
 * 
 * @param string $email Recipient's email address
 * @param string $tenantName Tenant's name
 * @param string $unitNo Unit number
 * @param string $inspectionDate Date and time of the scheduled inspection
 * @param string $staffName Name of staff assigned to the inspection
 * @param string $notes Additional notes about the inspection
 * @return bool True if email sent successfully, false otherwise
 */
function sendInspectionScheduleEmail($email, $tenantName, $unitNo, $inspectionDate, $staffName, $notes = '') {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->setFrom(SMTP_USERNAME, 'PropertyWise | Inspection Schedule');
        $mail->addAddress($email, $tenantName);
        $mail->isHTML(true);
        
        // Format inspection date
        $formattedDate = date('F d, Y \a\t h:i A', strtotime($inspectionDate));
        
        // Notes section
        $notesSection = !empty($notes) ? 
            "<p style='margin: 5px 0;'><strong>Additional Notes:</strong> " . nl2br($notes) . "</p>" : "";
        
        // Email content with HTML and inline CSS
        $mail->Subject = 'Unit Inspection Scheduled - PropertyWise';
        $mail->Body = '
        <div style="font-family: \'Arial\', sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
            <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="color: #1f2937; font-size: 24px; font-weight: bold; margin-bottom: 10px;">Unit Inspection Scheduled</h1>
                    <p style="color: #6b7280; font-size: 16px; margin-bottom: 20px;">Your unit inspection has been scheduled</p>
                </div>
                
                <div style="margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong>Dear ' . $tenantName . ',</strong></p>
                    <div style="margin: 15px 0; line-height: 1.6; color: #4b5563;">
                        <p>We have scheduled an inspection of your unit as part of the turnover process. Please ensure you or your representative is present during the inspection time.</p>
                    </div>
                </div>
                
                <div style="background-color: #f0f7ff; border: 1px solid #bcdcff; padding: 20px; margin: 20px 0; border-radius: 8px;">
                    <p style="margin: 5px 0;"><strong>Unit #:</strong> ' . $unitNo . '</p>
                    <p style="margin: 5px 0;"><strong>Inspection Date:</strong> ' . $formattedDate . '</p>
                    <p style="margin: 5px 0;"><strong>Inspector:</strong> ' . $staffName . '</p>
                    ' . $notesSection . '
                </div>
                
                <div style="color: #4b5563; font-size: 14px; margin-top: 20px;">
                    <h3 style="font-size: 16px; color: #1f2937;">Preparing for Inspection:</h3>
                    <ul style="margin-top: 5px; padding-left: 20px;">
                        <li>Clean the unit thoroughly</li>
                        <li>Remove all personal belongings</li>
                        <li>Check for any damages that need to be reported</li>
                        <li>Ensure all fixtures and appliances are in working order</li>
                        <li>Have all keys ready for return</li>
                    </ul>
                </div>
                
                <div style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 20px;">
                    <p>If you need to reschedule the inspection, please contact our property management office at least 24 hours before the scheduled time.</p>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px;">
                    <p>This is an automated message from our property management system.</p>
                    <p style="margin-top: 5px;">&copy; ' . date("Y") . ' PropertyWise. All rights reserved.</p>
                </div>
            </div>
        </div>';

        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Unit Inspection Scheduled\n\nDear {$tenantName},\n\nWe have scheduled an inspection of your unit as part of the turnover process.\n\nUnit #: {$unitNo}\nInspection Date: {$formattedDate}\nInspector: {$staffName}\n\nPlease ensure you or your representative is present during the inspection time.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error sending inspection schedule email: ' . $e->getMessage());
        return false;
    }
}

/**
 * Sends turnover completion email to tenant
 * 
 * @param string $email Recipient's email address
 * @param string $tenantName Tenant's name
 * @param string $unitNo Unit number
 * @param array $inspectionResults Results from the inspection
 * @param string $completionDate Date of turnover completion
 * @return bool True if email sent successfully, false otherwise
 */
function sendTurnoverCompletionEmail($email, $tenantName, $unitNo, $inspectionResults = [], $completionDate = null) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->setFrom(SMTP_USERNAME, 'PropertyWise | Turnover Complete');
        $mail->addAddress($email, $tenantName);
        $mail->isHTML(true);
        
        // Format completion date
        $formattedDate = $completionDate ? date('F d, Y', strtotime($completionDate)) : date('F d, Y');
        
        // Inspection results section
        $inspectionSection = '';
        if (!empty($inspectionResults)) {
            $inspectionSection = '<div style="margin: 15px 0;">
                <h3 style="font-size: 16px; color: #1f2937;">Inspection Results:</h3>
                <ul style="margin-top: 5px; padding-left: 20px;">';
            
            foreach($inspectionResults as $key => $value) {
                $inspectionSection .= '<li><strong>' . ucfirst($key) . ':</strong> ' . ucfirst($value) . '</li>';
            }
            
            $inspectionSection .= '</ul></div>';
        }
        
        // Email content with HTML and inline CSS
        $mail->Subject = 'Unit Turnover Complete - PropertyWise';
        $mail->Body = '
        <div style="font-family: \'Arial\', sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
            <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="color: #1f2937; font-size: 24px; font-weight: bold; margin-bottom: 10px;">Unit Turnover Complete</h1>
                    <p style="color: #6b7280; font-size: 16px; margin-bottom: 20px;">Your unit has been successfully turned over</p>
                </div>
                
                <div style="margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong>Dear ' . $tenantName . ',</strong></p>
                    <div style="margin: 15px 0; line-height: 1.6; color: #4b5563;">
                        <p>We are writing to confirm that the turnover process for your unit has been completed successfully. Thank you for your cooperation throughout this process.</p>
                    </div>
                </div>
                
                <div style="background-color: #f0fff4; border: 1px solid #c6f6d5; padding: 20px; margin: 20px 0; border-radius: 8px;">
                    <p style="margin: 5px 0;"><strong>Unit #:</strong> ' . $unitNo . '</p>
                    <p style="margin: 5px 0;"><strong>Completion Date:</strong> ' . $formattedDate . '</p>
                    <p style="margin: 5px 0;"><strong>Status:</strong> <span style="color: #047857; font-weight: bold;">Completed</span></p>
                </div>
                
                ' . $inspectionSection . '
                
                <div style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 20px;">
                    <p>We hope that your stay with us has been pleasant. If you have any questions or require any further assistance, please do not hesitate to contact our property management office.</p>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px;">
                    <p>This is an automated message from our property management system.</p>
                    <p style="margin-top: 5px;">&copy; ' . date("Y") . ' PropertyWise. All rights reserved.</p>
                </div>
            </div>
        </div>';

        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Unit Turnover Complete\n\nDear {$tenantName},\n\nWe are writing to confirm that the turnover process for your unit has been completed successfully.\n\nUnit #: {$unitNo}\nCompletion Date: {$formattedDate}\nStatus: Completed\n\nThank you for your cooperation throughout this process.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error sending turnover completion email: ' . $e->getMessage());
        return false;
    }
}

/**
 * Sends inspection assignment notification email to staff
 * 
 * @param string $email Staff member's email address
 * @param string $staffName Staff member's name
 * @param string $tenantName Tenant's name
 * @param string $unitNo Unit number being inspected
 * @param string $inspectionDate Date and time of the scheduled inspection
 * @param string $notes Additional notes about the inspection
 * @return bool True if email sent successfully, false otherwise
 */
function sendStaffInspectionAssignmentEmail($email, $staffName, $tenantName, $unitNo, $inspectionDate, $notes = '') {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->setFrom(SMTP_USERNAME, 'PropertyWise | Inspection Assignment');
        $mail->addAddress($email, $staffName);
        $mail->isHTML(true);
        
        // Format inspection date
        $formattedDate = date('F d, Y \a\t h:i A', strtotime($inspectionDate));
        
        // Notes section
        $notesSection = !empty($notes) ? 
            "<p style='margin: 5px 0;'><strong>Additional Notes:</strong> " . nl2br($notes) . "</p>" : "";
        
        // Email content with HTML and inline CSS
        $mail->Subject = 'Unit Inspection Assignment - PropertyWise';
        $mail->Body = '
        <div style="font-family: \'Arial\', sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
            <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="color: #1f2937; font-size: 24px; font-weight: bold; margin-bottom: 10px;">New Inspection Assignment</h1>
                    <p style="color: #6b7280; font-size: 16px; margin-bottom: 20px;">You have been assigned to conduct a unit inspection</p>
                </div>
                
                <div style="margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong>Dear ' . $staffName . ',</strong></p>
                    <div style="margin: 15px 0; line-height: 1.6; color: #4b5563;">
                        <p>You have been assigned to conduct an inspection for the following unit. Please ensure you are available at the scheduled time.</p>
                    </div>
                </div>
                
                <div style="background-color: #f0f7ff; border: 1px solid #bcdcff; padding: 20px; margin: 20px 0; border-radius: 8px;">
                    <p style="margin: 5px 0;"><strong>Tenant:</strong> ' . $tenantName . '</p>
                    <p style="margin: 5px 0;"><strong>Unit #:</strong> ' . $unitNo . '</p>
                    <p style="margin: 5px 0;"><strong>Inspection Date:</strong> ' . $formattedDate . '</p>
                    ' . $notesSection . '
                </div>
                
                <div style="color: #4b5563; font-size: 14px; margin-top: 20px;">
                    <h3 style="font-size: 16px; color: #1f2937;">Inspection Checklist:</h3>
                    <ul style="margin-top: 5px; padding-left: 20px;">
                        <li>Check cleanliness of all rooms</li>
                        <li>Document any damages to walls, floors, and ceilings</li>
                        <li>Verify all fixtures and equipment are working properly</li>
                        <li>Collect and check all keys</li>
                        <li>Take photos of any issues for documentation</li>
                    </ul>
                </div>
                
                <div style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 20px;">
                    <p>If you are unable to complete this assignment, please contact management immediately.</p>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px;">
                    <p>This is an automated message from our property management system.</p>
                    <p style="margin-top: 5px;">&copy; ' . date("Y") . ' PropertyWise. All rights reserved.</p>
                </div>
            </div>
        </div>';

        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Unit Inspection Assignment\n\nDear {$staffName},\n\nYou have been assigned to conduct an inspection for unit {$unitNo}.\n\nTenant: {$tenantName}\nInspection Date: {$formattedDate}\n\nPlease ensure you are available at the scheduled time.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error sending staff inspection assignment email: ' . $e->getMessage());
        return false;
    }
}
?>
