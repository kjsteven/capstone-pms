<?php
require '../session/db.php';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="tenant_information.csv"');

function cleanData($str) {
    // Remove any HTML and PHP tags
    $str = strip_tags($str);
    // Convert special characters to HTML entities
    $str = htmlspecialchars($str);
    // Remove any newlines and extra spaces
    $str = trim(preg_replace('/\s+/', ' ', $str));
    // Escape any CSV delimiters
    $str = str_replace(array(',', ';', "\t", "\r", "\n"), ' ', $str);
    return $str;
}

try {
    // Get tenant ID from request
    $tenant_id = isset($_GET['tenant_id']) ? intval($_GET['tenant_id']) : null;
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM
    fputs($output, "\xEF\xBB\xBF");
    
    // Write headers for different sections
    $headers = array(
        // Tenant Information
        array("TENANT INFORMATION"),
        array("Name", "Email", "Phone", "Status"),
        // Unit Information
        array("\nUNIT INFORMATION"),
        array("Unit No", "Unit Type", "Square Meters", "Monthly Rate", "Rent From", "Rent Until", "Outstanding Balance"),
        // Payment History
        array("\nPAYMENT HISTORY"),
        array("Date", "Amount", "Type", "Method", "Reference Number", "Status"),
        // Maintenance History
        array("\nMAINTENANCE HISTORY"),
        array("Date", "Unit", "Issue", "Description", "Status"),
        // Reservation History
        array("\nRESERVATION HISTORY"),
        array("Date", "Time", "Unit", "Type", "Status")
    );

    // Write all headers
    foreach ($headers as $header) {
        fputcsv($output, $header);
    }

    // Query to get tenant information
    $query = "
        SELECT 
            u.name, u.email, u.phone, t.status,
            p.unit_no, p.unit_type, p.square_meter, t.monthly_rate,
            t.rent_from, t.rent_until, t.outstanding_balance,
            t.tenant_id, t.user_id
        FROM tenants t
        JOIN users u ON t.user_id = u.user_id
        JOIN property p ON t.unit_rented = p.unit_id
        WHERE " . ($tenant_id ? "t.tenant_id = ?" : "1");

    $stmt = $conn->prepare($query);
    if ($tenant_id) {
        $stmt->bind_param("i", $tenant_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Write tenant information
    $tenantInfo = $result->fetch_assoc();
    fputcsv($output, array(
        cleanData($tenantInfo['name']),
        cleanData($tenantInfo['email']),
        cleanData($tenantInfo['phone']),
        cleanData($tenantInfo['status'])
    ));

    // Write unit information
    fputcsv($output, array());  // Empty line
    fputcsv($output, array(
        cleanData($tenantInfo['unit_no']),
        cleanData($tenantInfo['unit_type']),
        cleanData($tenantInfo['square_meter']),
        cleanData($tenantInfo['monthly_rate']),
        cleanData($tenantInfo['rent_from']),
        cleanData($tenantInfo['rent_until']),
        cleanData($tenantInfo['outstanding_balance'])
    ));

    // Get and write payment history
    $paymentQuery = "
        SELECT payment_date, amount, payment_type, 
               CASE WHEN gcash_number IS NOT NULL THEN 'GCash' ELSE 'Cash' END as method,
               reference_number, status
        FROM payments 
        WHERE tenant_id = ?
        ORDER BY payment_date DESC";
    
    $stmt = $conn->prepare($paymentQuery);
    $stmt->bind_param("i", $tenantInfo['tenant_id']);
    $stmt->execute();
    $payments = $stmt->get_result();

    fputcsv($output, array());  // Empty line
    while ($payment = $payments->fetch_assoc()) {
        fputcsv($output, array(
            cleanData($payment['payment_date']),
            cleanData($payment['amount']),
            cleanData($payment['payment_type']),
            cleanData($payment['method']),
            cleanData($payment['reference_number']),
            cleanData($payment['status'])
        ));
    }

    // Get and write maintenance history
    $maintenanceQuery = "
        SELECT service_date, unit, issue, description, status
        FROM maintenance_requests
        WHERE user_id = ? AND archived = 0
        ORDER BY service_date DESC";
    
    $stmt = $conn->prepare($maintenanceQuery);
    $stmt->bind_param("i", $tenantInfo['user_id']);
    $stmt->execute();
    $maintenance = $stmt->get_result();

    fputcsv($output, array());  // Empty line
    while ($request = $maintenance->fetch_assoc()) {
        fputcsv($output, array(
            cleanData($request['service_date']),
            cleanData($request['unit']),
            cleanData($request['issue']),
            cleanData($request['description']),
            cleanData($request['status'])
        ));
    }

    // Get and write reservation history
    $reservationQuery = "
        SELECT r.viewing_date, r.viewing_time, 
               p.unit_no, p.unit_type, r.status
        FROM reservations r
        JOIN property p ON r.unit_id = p.unit_id
        WHERE r.user_id = ? AND r.archived = 0
        ORDER BY r.viewing_date DESC";
    
    $stmt = $conn->prepare($reservationQuery);
    $stmt->bind_param("i", $tenantInfo['user_id']);
    $stmt->execute();
    $reservations = $stmt->get_result();

    fputcsv($output, array());  // Empty line
    while ($reservation = $reservations->fetch_assoc()) {
        fputcsv($output, array(
            cleanData($reservation['viewing_date']),
            cleanData($reservation['viewing_time']),
            cleanData($reservation['unit_no']),
            cleanData($reservation['unit_type']),
            cleanData($reservation['status'])
        ));
    }

    fclose($output);

} catch (Exception $e) {
    // Log error and return error response
    error_log("Error in export_tenant.php: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error exporting tenant information: " . $e->getMessage();
}
