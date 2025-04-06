<?php
require '../session/db.php';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="tenant_information_' . date('Y-m-d') . '.csv"');

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
    $tenant_id = isset($_GET['tenant_id']) ? intval($_GET['tenant_id']) : null;
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM

    // Get tenant basic information
    $query = "
        SELECT DISTINCT
            u.name, u.email, u.phone, t.status, t.user_id
        FROM tenants t
        JOIN users u ON t.user_id = u.user_id
        WHERE t.tenant_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $tenantInfo = $stmt->get_result()->fetch_assoc();

    // SECTION 1: Tenant Information
    fputcsv($output, array("=== TENANT INFORMATION ==="));
    fputcsv($output, array("Name", "Email", "Phone", "Status"));
    fputcsv($output, array(
        cleanData($tenantInfo['name']),
        cleanData($tenantInfo['email']),
        cleanData($tenantInfo['phone']),
        cleanData($tenantInfo['status'])
    ));
    fputcsv($output, array()); // Empty line for spacing

    // SECTION 2: Units Information
    fputcsv($output, array("=== RENTED UNITS ==="));
    fputcsv($output, array(
        "Unit No", "Unit Type", "Square Meters", "Monthly Rate", 
        "Rent From", "Rent Until", "Outstanding Balance"
    ));

    // Get all units for this tenant
    $unitsQuery = "
        SELECT 
            p.unit_no, p.unit_type, p.square_meter,
            t.monthly_rate, t.rent_from, t.rent_until, t.outstanding_balance
        FROM tenants t
        JOIN property p ON t.unit_rented = p.unit_id
        WHERE t.user_id = ?";

    $stmt = $conn->prepare($unitsQuery);
    $stmt->bind_param("i", $tenantInfo['user_id']);
    $stmt->execute();
    $units = $stmt->get_result();

    while ($unit = $units->fetch_assoc()) {
        fputcsv($output, array(
            cleanData($unit['unit_no']),
            cleanData($unit['unit_type']),
            cleanData($unit['square_meter']),
            cleanData($unit['monthly_rate']),
            cleanData($unit['rent_from']),
            cleanData($unit['rent_until']),
            cleanData($unit['outstanding_balance'])
        ));
    }
    fputcsv($output, array()); // Empty line for spacing

    // SECTION 3: Payment History
    fputcsv($output, array("=== PAYMENT HISTORY ==="));
    fputcsv($output, array(
        "Date", "Unit", "Amount", "Type", "Method", 
        "Reference Number", "GCash Number", "Status"
    ));

    $paymentQuery = "
        SELECT 
            p.payment_date, pr.unit_no, p.amount, 
            p.payment_type, p.gcash_number,
            p.reference_number, p.status,
            CASE WHEN p.gcash_number IS NOT NULL THEN 'GCash' ELSE 'Cash' END as method
        FROM payments p
        JOIN tenants t ON p.tenant_id = t.tenant_id
        JOIN property pr ON t.unit_rented = pr.unit_id
        WHERE t.user_id = ?
        ORDER BY p.payment_date DESC";

    $stmt = $conn->prepare($paymentQuery);
    $stmt->bind_param("i", $tenantInfo['user_id']);
    $stmt->execute();
    $payments = $stmt->get_result();

    while ($payment = $payments->fetch_assoc()) {
        fputcsv($output, array(
            cleanData($payment['payment_date']),
            cleanData($payment['unit_no']),
            cleanData($payment['amount']),
            cleanData($payment['payment_type']),
            cleanData($payment['method']),
            cleanData($payment['reference_number']),
            cleanData($payment['gcash_number']),
            cleanData($payment['status'])
        ));
    }
    fputcsv($output, array()); // Empty line for spacing

    // SECTION 4: Maintenance History
    fputcsv($output, array("=== MAINTENANCE HISTORY ==="));
    fputcsv($output, array(
        "Date", "Unit", "Issue", "Description", "Status"
    ));

    $maintenanceQuery = "
        SELECT service_date, unit, issue, description, status
        FROM maintenance_requests
        WHERE user_id = ? AND archived = 0
        ORDER BY service_date DESC";

    $stmt = $conn->prepare($maintenanceQuery);
    $stmt->bind_param("i", $tenantInfo['user_id']);
    $stmt->execute();
    $maintenance = $stmt->get_result();

    while ($request = $maintenance->fetch_assoc()) {
        fputcsv($output, array(
            cleanData($request['service_date']),
            cleanData($request['unit']),
            cleanData($request['issue']),
            cleanData($request['description']),
            cleanData($request['status'])
        ));
    }
    fputcsv($output, array()); // Empty line for spacing

    // SECTION 5: Reservation History
    fputcsv($output, array("=== RESERVATION HISTORY ==="));
    fputcsv($output, array(
        "Date", "Time", "Unit", "Type", "Monthly Rate", "Size", "Status"
    ));

    $reservationQuery = "
        SELECT 
            r.viewing_date, r.viewing_time,
            p.unit_no, p.unit_type, p.monthly_rent,
            p.square_meter, r.status
        FROM reservations r
        JOIN property p ON r.unit_id = p.unit_id
        WHERE r.user_id = ? AND r.archived = 0
        ORDER BY r.viewing_date DESC";

    $stmt = $conn->prepare($reservationQuery);
    $stmt->bind_param("i", $tenantInfo['user_id']);
    $stmt->execute();
    $reservations = $stmt->get_result();

    while ($reservation = $reservations->fetch_assoc()) {
        fputcsv($output, array(
            cleanData($reservation['viewing_date']),
            cleanData($reservation['viewing_time']),
            cleanData($reservation['unit_no']),
            cleanData($reservation['unit_type']),
            cleanData($reservation['monthly_rent']),
            cleanData($reservation['square_meter']),
            cleanData($reservation['status'])
        ));
    }

    // Add report generation timestamp
    fputcsv($output, array());
    fputcsv($output, array("Report generated on: " . date('Y-m-d H:i:s')));

    fclose($output);

} catch (Exception $e) {
    error_log("Error in export_tenant.php: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error exporting tenant information: " . $e->getMessage();
}
