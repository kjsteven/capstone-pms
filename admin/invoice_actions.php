<?php
// Ensure there's no whitespace, BOM or output before this point
// Turn off output buffering and clean any existing output
if (ob_get_level()) ob_end_clean();
ob_start();

// Turn off PHP error display - we'll handle errors ourselves
ini_set('display_errors', 0);
error_reporting(E_ALL);

// For debugging PHP errors - log them to a file
ini_set('log_errors', 1);
$logDir = dirname(__DIR__) . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
ini_set('error_log', $logDir . '/php_errors.log');

// Log the start of script execution for debugging
error_log("Starting invoice_actions.php with action: " . ($_GET['action'] ?? 'none'));

// Session management must happen before any potential output
session_start(); // Start session at the very beginning, before any potential output

require_once '../session/db.php';
require_once '../session/audit_trail.php';
require '../vendor/autoload.php';  // For PHPMailer
require '../config/config.php';     // For email credentials
require_once '../notification/notif_handler.php'; // Add at the top with other requires

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Debug array to capture information
$debugInfo = [];

// Custom error handler to prevent HTML error output
function jsonErrorHandler($errno, $errstr, $errfile, $errline) {
    global $debugInfo;
    
    // Log the error to error_log
    error_log("PHP Error: $errstr in $errfile on line $errline");
    
    $error = [
        'success' => false,
        'message' => 'PHP Error: ' . $errstr,
        'details' => "File: $errfile, Line: $errline"
    ];
    
    $debugInfo[] = [
        'type' => 'php_error',
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ];
    
    // Clean any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set JSON header
    header('Content-Type: application/json');
    echo json_encode($error);
    exit;
}

// Set custom error handler
set_error_handler('jsonErrorHandler');

try {
    // Check if the user is logged in - we've already started the session above
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }

    // Set content type to JSON - this is critical
    header('Content-Type: application/json');

    // Define allowed actions
    $allowedActions = ['create', 'view', 'delete', 'send_email', 'update_status'];

    // Get the requested action
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    // Add action to debug info
    $debugInfo[] = [
        'type' => 'action',
        'value' => $action
    ];

    // Check if action is valid
    if (!in_array($action, $allowedActions)) {
        throw new Exception('Invalid action');
    }

    // Handle different actions
    switch($action) {
        case 'create':
            createInvoice();
            break;
        case 'view':
            viewInvoice();
            break;
        case 'delete':
            deleteInvoice();
            break;
        case 'send_email':
            sendInvoiceEmail();
            break;
        case 'update_status':
            updateInvoiceStatus();
            break;
        default:
            throw new Exception('Action not implemented');
    }
} catch (Exception $e) {
    // Clean any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    error_log("Exception in invoice_actions.php: " . $e->getMessage());
    
    $debugInfo[] = [
        'type' => 'exception',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ];
    
    // Send JSON error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => $debugInfo
    ]);
    exit;
}

// Create a new invoice
function createInvoice() {
    global $conn, $debugInfo;
    
    try {
        // Log POST data to debug file
        error_log("Create invoice POST data: " . print_r($_POST, true));
        
        // Validate required fields
        if (!isset($_POST['tenant_id']) || !isset($_POST['amount']) || !isset($_POST['issue_date']) || !isset($_POST['due_date'])) {
            // Log which fields are missing
            $missing = [];
            if (!isset($_POST['tenant_id'])) $missing[] = 'tenant_id';
            if (!isset($_POST['amount'])) $missing[] = 'amount';
            if (!isset($_POST['issue_date'])) $missing[] = 'issue_date';
            if (!isset($_POST['due_date'])) $missing[] = 'due_date';
            
            $debugInfo[] = [
                'type' => 'missing_fields',
                'fields' => $missing,
                'post_data' => $_POST
            ];
            
            throw new Exception('Missing required fields: ' . implode(', ', $missing));
        }
        
        // Get form data
        $tenant_id = (int)$_POST['tenant_id'];
        $amount = (float)$_POST['amount'];
        $issue_date = $_POST['issue_date'];
        $due_date = $_POST['due_date'];
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $invoice_type = isset($_POST['invoice_type']) ? $_POST['invoice_type'] : 'rent';
        
        $debugInfo[] = [
            'type' => 'form_data',
            'tenant_id' => $tenant_id,
            'amount' => $amount,
            'issue_date' => $issue_date,
            'due_date' => $due_date,
            'description' => $description,
            'invoice_type' => $invoice_type
        ];
        
        // Generate invoice number
        $invoice_number = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // Insert invoice
        $stmt = $conn->prepare(
            "INSERT INTO invoices (tenant_id, invoice_number, amount, issue_date, due_date, description, invoice_type, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"
        );
        
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }
        
        // Fix this line by adding one more 's' to the type definition string to match the 7 parameters
        $stmt->bind_param("isdssss", $tenant_id, $invoice_number, $amount, $issue_date, $due_date, $description, $invoice_type);
        
        if (!$stmt->execute()) {
            throw new Exception('Database execute error: ' . $stmt->error);
        }
        
        $invoice_id = $conn->insert_id;
        
        // Process line items if any
        if (isset($_POST['line_items'])) {
            $lineItemsRaw = $_POST['line_items'];
            
            // Add to debug
            $debugInfo[] = [
                'type' => 'line_items_raw',
                'data' => $lineItemsRaw
            ];
            
            // Handle empty line items array more gracefully
            if ($lineItemsRaw === '[]' || empty(trim($lineItemsRaw))) {
                $lineItems = [];
                $debugInfo[] = [
                    'type' => 'line_items_empty',
                    'message' => 'Empty line items array detected'
                ];
            } else {
                // Try to decode non-empty line items
                $lineItems = json_decode($lineItemsRaw, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $debugInfo[] = [
                        'type' => 'line_items_json_error',
                        'error' => json_last_error_msg(),
                        'raw_data' => $lineItemsRaw
                    ];
                }
            }
            
            // Only process if we have valid line items
            if (!empty($lineItems) && is_array($lineItems)) {
                foreach ($lineItems as $item) {
                    if (!empty($item['name']) && isset($item['amount'])) {
                        $itemStmt = $conn->prepare(
                            "INSERT INTO invoice_items (invoice_id, item_name, amount) VALUES (?, ?, ?)"
                        );
                        
                        if (!$itemStmt) {
                            throw new Exception('Database prepare error: ' . $conn->error);
                        }
                        
                        $itemName = $item['name'];
                        $itemAmount = (float)$item['amount'];
                        $itemStmt->bind_param("isd", $invoice_id, $itemName, $itemAmount);
                        
                        $debugInfo[] = [
                            'type' => 'line_item_insert',
                            'name' => $itemName,
                            'amount' => $itemAmount
                        ];
                        
                        if (!$itemStmt->execute()) {
                            throw new Exception('Database execute error: ' . $itemStmt->error);
                        }
                    }
                }
            }
        }
        
        // Always create the main line item based on invoice type
        $defaultItemName = '';
        switch ($invoice_type) {
            case 'rent':
                $defaultItemName = 'Monthly Rent';
                break;
            case 'utility':
                $defaultItemName = 'Utilities Payment';
                break;
            case 'other':
            default:
                $defaultItemName = 'Other Charges';
                break;
        }
        
        $defaultItemStmt = $conn->prepare(
            "INSERT INTO invoice_items (invoice_id, item_name, amount) VALUES (?, ?, ?)"
        );
        $defaultItemStmt->bind_param("isd", $invoice_id, $defaultItemName, $amount);
        $defaultItemStmt->execute();
        
        $debugInfo[] = [
            'type' => 'default_line_item_created',
            'name' => $defaultItemName,
            'amount' => $amount
        ];
        
        // Update the total amount to include both main item and additional line items
        $totalAmount = $amount;
        if (!empty($lineItems) && is_array($lineItems)) {
            foreach ($lineItems as $item) {
                if (isset($item['amount'])) {
                    $totalAmount += (float)$item['amount'];
                }
            }
            
            // Update the invoice with the new total if line items were added
            if ($totalAmount != $amount) {
                $updateStmt = $conn->prepare("UPDATE invoices SET amount = ? WHERE id = ?");
                $updateStmt->bind_param("di", $totalAmount, $invoice_id);
                $updateStmt->execute();
            }
        }
        
        // Log activity
        logActivity(
            $_SESSION['user_id'], 
            'Created Invoice', 
            "Created invoice #{$invoice_number} for tenant #{$tenant_id}"
        );
        
        // Return clean JSON response
        http_response_code(200); // Explicitly set 200 OK status
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'message' => 'Invoice created successfully',
            'invoice_id' => $invoice_id
        ];
        
        $debugInfo[] = [
            'type' => 'response',
            'data' => $response
        ];
        
        // Log successful response
        error_log("Invoice created successfully with ID: $invoice_id and invoice number: $invoice_number");
        
        echo json_encode($response);
        exit;
    } 
    catch (Exception $e) {
        http_response_code(400); // Bad request
        header('Content-Type: application/json');
        
        // Log the caught exception
        error_log("Error creating invoice: " . $e->getMessage());
        
        $errorResponse = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'debug' => $debugInfo
        ];
        
        echo json_encode($errorResponse);
        exit;
    }
}

// View invoice details
function viewInvoice() {
    global $conn;
    
    try {
        if (!isset($_GET['id'])) {
            throw new Exception('Invoice ID is required');
        }
        
        $invoice_id = (int)$_GET['id'];
        
        // Get invoice details
        $stmt = $conn->prepare(
            "SELECT i.*, t.user_id 
             FROM invoices i
             JOIN tenants t ON i.tenant_id = t.tenant_id
             WHERE i.id = ?"
        );
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Invoice not found');
        }
        
        $invoice = $result->fetch_assoc();
        
        // Get tenant details
        $tenantStmt = $conn->prepare(
            "SELECT u.name, u.email, p.unit_no
             FROM users u
             JOIN tenants t ON u.user_id = t.user_id
             JOIN property p ON t.unit_rented = p.unit_id
             WHERE t.tenant_id = ?"
        );
        $tenantStmt->bind_param("i", $invoice['tenant_id']);
        $tenantStmt->execute();
        $tenantResult = $tenantStmt->get_result();
        $tenant = $tenantResult->fetch_assoc();
        
        // Get invoice items
        $itemsStmt = $conn->prepare(
            "SELECT * FROM invoice_items WHERE invoice_id = ?"
        );
        $itemsStmt->bind_param("i", $invoice_id);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        $items = [];
        
        // If there are no specific items, create a default one based on invoice type
        if ($itemsResult->num_rows === 0) {
            $itemName = 'Unknown';
            switch ($invoice['invoice_type']) {
                case 'rent':
                    $itemName = 'Monthly Rent';
                    break;
                case 'utility':
                    $itemName = 'Utilities Payment';
                    break;
                case 'other':
                    $itemName = 'Other Fees';
                    break;
            }
            
            $items[] = [
                'item_name' => $itemName,
                'amount' => $invoice['amount']
            ];
        } else {
            while ($item = $itemsResult->fetch_assoc()) {
                $items[] = $item;
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'invoice' => $invoice,
            'tenant' => $tenant,
            'items' => $items
        ]);
        exit;
    } 
    catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Delete an invoice
function deleteInvoice() {
    global $conn;
    
    try {
        if (!isset($_GET['id'])) {
            throw new Exception('Invoice ID is required');
        }
        
        $invoice_id = (int)$_GET['id'];
        
        // Get invoice details for logging
        $stmt = $conn->prepare("SELECT invoice_number, tenant_id FROM invoices WHERE id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Invoice not found');
        }
        
        $invoice = $result->fetch_assoc();
        
        // Delete invoice items first to maintain referential integrity
        $stmt = $conn->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        
        // Delete the invoice
        $stmt = $conn->prepare("DELETE FROM invoices WHERE id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        
        // Log activity
        logActivity(
            $_SESSION['user_id'], 
            'Deleted Invoice', 
            "Deleted invoice #{$invoice['invoice_number']} for tenant #{$invoice['tenant_id']}"
        );
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Invoice deleted successfully']);
        exit;
    } 
    catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}

// Send invoice email
function sendInvoiceEmail() {
    global $conn;
    
    try {
        if (!isset($_POST['invoice_id'])) {
            throw new Exception('Invoice ID is required');
        }
        
        $invoice_id = (int)$_POST['invoice_id'];
        $subject = isset($_POST['subject']) ? $_POST['subject'] : 'Your Invoice';
        $additionalMessage = isset($_POST['message']) ? $_POST['message'] : '';
        
        // Get invoice details
        $stmt = $conn->prepare(
            "SELECT i.*, t.user_id, u.name as tenant_name, u.email as tenant_email, p.unit_no
             FROM invoices i
             JOIN tenants t ON i.tenant_id = t.tenant_id
             JOIN users u ON t.user_id = u.user_id
             JOIN property p ON t.unit_rented = p.unit_id
             WHERE i.id = ?"
        );
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Invoice not found');
        }
        
        $invoice = $result->fetch_assoc();
        
        // Get invoice items
        $itemsStmt = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
        $itemsStmt->bind_param("i", $invoice_id);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        $items = [];
        
        if ($itemsResult->num_rows > 0) {
            while ($item = $itemsResult->fetch_assoc()) {
                $items[] = $item;
            }
        } else {
            // Create a default item based on invoice type
            $itemName = '';
            switch ($invoice['invoice_type']) {
                case 'rent':
                    $itemName = 'Monthly Rent';
                    break;
                case 'utility':
                    $itemName = 'Utilities Payment';
                    break;
                case 'other':
                    $itemName = 'Other Fees';
                    break;
            }
            
            $items[] = [
                'item_name' => $itemName,
                'amount' => $invoice['amount']
            ];
        }
        
        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        $invoiceNumber = $invoice['invoice_number'] ?: 'INV-' . str_pad($invoice['id'], 5, '0', STR_PAD_LEFT);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->setFrom(SMTP_USERNAME, 'PropertyWise | Invoice');
            $mail->addAddress($invoice['tenant_email'], $invoice['tenant_name']);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            
            // Build email body
            $email_body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
                <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h1 style="color: #1f2937; font-size: 24px; font-weight: bold; margin-bottom: 10px;">Invoice: ' . $invoiceNumber . '</h1>
                        <p style="color: #6b7280; font-size: 16px; margin-bottom: 20px;">Please find your invoice details below</p>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <p style="margin: 5px 0;">Dear ' . htmlspecialchars($invoice['tenant_name']) . ',</p>
                        <p style="margin: 5px 0;">Please find your invoice details below for Unit ' . htmlspecialchars($invoice['unit_no']) . '.</p>';
            
            // Add additional message if provided
            if (!empty($additionalMessage)) {
                $email_body .= '<p style="margin: 10px 0;">' . nl2br(htmlspecialchars($additionalMessage)) . '</p>';
            }
            
            $email_body .= '
                    </div>
                    
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 8px 0; font-weight: bold;">Issue Date:</td>
                                <td style="padding: 8px 0;">' . date('M d, Y', strtotime($invoice['issue_date'])) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; font-weight: bold;">Due Date:</td>
                                <td style="padding: 8px 0;">' . date('M d, Y', strtotime($invoice['due_date'])) . '</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <h2 style="color: #1f2937; font-size: 18px; margin-bottom: 10px;">Invoice Items</h2>
                        <table style="width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb;">
                            <thead>
                                <tr style="background-color: #f3f4f6;">
                                    <th style="text-align: left; padding: 12px 15px; border-bottom: 1px solid #e5e7eb;">Item</th>
                                    <th style="text-align: right; padding: 12px 15px; border-bottom: 1px solid #e5e7eb;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>';
            
            $totalAmount = 0;
            foreach ($items as $item) {
                $email_body .= '
                                <tr>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #e5e7eb;">' . htmlspecialchars($item['item_name']) . '</td>
                                    <td style="padding: 12px 15px; border-bottom: 1px solid #e5e7eb; text-align: right;">₱' . number_format($item['amount'], 2) . '</td>
                                </tr>';
                $totalAmount += $item['amount'];
            }
            
            $email_body .= '
                                <tr style="font-weight: bold; background-color: #f8fafc;">
                                    <td style="padding: 12px 15px;">Total Amount</td>
                                    <td style="padding: 12px 15px; text-align: right;">₱' . number_format($invoice['amount'], 2) . '</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>';
            
            if (!empty($invoice['description'])) {
                $email_body .= '
                    <div style="margin-bottom: 20px;">
                        <h3 style="color: #1f2937; font-size: 16px; margin-bottom: 10px;">Additional Information</h3>
                        <p style="margin: 5px 0; color: #4b5563;">' . nl2br(htmlspecialchars($invoice['description'])) . '</p>
                    </div>';
            }
            
            $email_body .= '
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                        <p style="margin: 5px 0;">Please make your payment before the due date to avoid late fees.</p>
                        <p style="margin: 5px 0;">Thank you for your prompt attention to this invoice.</p>
                        <p style="margin: 15px 0 5px 0;">Regards,<br>PropertyWise Management</p>
                    </div>
                    
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px;">
                        <p style="margin: 5px 0;">This is an automated message, please do not reply.</p>
                        <p style="margin: 5px 0;">&copy; ' . date("Y") . ' PropertyWise. All rights reserved.</p>
                    </div>
                </div>
            </div>';
            
            $mail->Body = $email_body;
            $mail->AltBody = "Invoice: $invoiceNumber\nDue Date: " . date('M d, Y', strtotime($invoice['due_date'])) . "\nTotal Amount: ₱" . number_format($invoice['amount'], 2);
            
            if ($mail->send()) {
                // Separate try-catch for notifications
                try {
                    // Create notifications
                    $tenantMessage = "A new invoice #{$invoiceNumber} has been sent to your email.";
                    createNotification($invoice['user_id'], $tenantMessage, 'invoice_sent');

                    $adminMessage = "Invoice #{$invoiceNumber} was sent to {$invoice['tenant_name']}.";
                    createNotification($_SESSION['user_id'], $adminMessage, 'admin_invoice');
                } catch (Exception $notifError) {
                    error_log("Notification error: " . $notifError->getMessage());
                    // Continue execution even if notification fails
                }

                // Update invoice status
                $stmt = $conn->prepare("UPDATE invoices SET email_sent = 1, email_sent_date = NOW() WHERE id = ?");
                $stmt->bind_param("i", $invoice_id);
                $stmt->execute();

                echo json_encode([
                    'success' => true,
                    'message' => 'Invoice email sent successfully'
                ]);
                exit;
            }
            // ...existing error handling code...
        } catch (Exception $e) {
            throw new Exception('Email sending failed: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        // Clean any existing output
        while (ob_get_level()) ob_end_clean();

        // Log the error
        error_log('Invoice email error: ' . $e->getMessage());

        // Send error response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Update invoice status
function updateInvoiceStatus() {
    global $conn;
    
    try {
        // Check required parameters
        if (!isset($_POST['invoice_id']) || !isset($_POST['status'])) {
            throw new Exception('Invoice ID and status are required');
        }
        
        $invoice_id = (int)$_POST['invoice_id'];
        $status = $_POST['status'];
        
        // Validate status value (allow 'paid', 'unpaid', or 'overdue')
        if ($status !== 'paid' && $status !== 'unpaid' && $status !== 'overdue') {
            throw new Exception('Invalid status value. Must be "paid", "unpaid", or "overdue"');
        }
        
        // Get invoice and tenant details
        $stmt = $conn->prepare("
            SELECT i.invoice_number, i.tenant_id, t.user_id, u.name as tenant_name, p.unit_no 
            FROM invoices i
            JOIN tenants t ON i.tenant_id = t.tenant_id
            JOIN users u ON t.user_id = u.user_id
            JOIN property p ON t.unit_rented = p.unit_id
            WHERE i.id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        
        // Update the invoice status
        $updateStmt = $conn->prepare("UPDATE invoices SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $status, $invoice_id);
        
        if ($updateStmt->execute()) {
            // Separate try-catch for notifications
            try {
                switch($status) {
                    case 'paid':
                        createNotification(
                            $invoice['user_id'],
                            "Your invoice #{$invoice['invoice_number']} for Unit {$invoice['unit_no']} has been marked as paid.",
                            'invoice_paid'
                        );
                        createNotification(
                            $_SESSION['user_id'],
                            "Invoice #{$invoice['invoice_number']} for {$invoice['tenant_name']} has been marked as paid.",
                            'admin_invoice'
                        );
                        break;

                    case 'overdue':
                        createNotification(
                            $invoice['user_id'],
                            "Your invoice #{$invoice['invoice_number']} for Unit {$invoice['unit_no']} is overdue.",
                            'invoice_overdue'
                        );
                        createNotification(
                            $_SESSION['user_id'],
                            "Invoice #{$invoice['invoice_number']} for {$invoice['tenant_name']} is marked as overdue.",
                            'admin_invoice'
                        );
                        break;
                }
            } catch (Exception $notifError) {
                error_log("Notification error: " . $notifError->getMessage());
                // Continue execution even if notification fails
            }

            echo json_encode([
                'success' => true,
                'message' => 'Invoice status updated successfully',
                'new_status' => $status
            ]);
            exit;
        }
        // ...existing error handling code...
    } catch (Exception $e) {
        // ...existing error handling code...
    }
}
?>