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
?>
