<?php
require '../session/db.php';
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

    // Get tenant basic information using the same query from tenant_information.php
    $query = "
        SELECT 
            t.tenant_id, 
            t.user_id,
            t.unit_rented,
            t.rent_from,
            t.rent_until,
            t.monthly_rate,
            t.status,
            t.outstanding_balance,
            u.name AS tenant_name,
            u.email,
            u.phone,
            p.unit_no,
            p.unit_type,
            p.square_meter
        FROM tenants t
        JOIN users u ON t.user_id = u.user_id
        JOIN property p ON t.unit_rented = p.unit_id
        WHERE t.tenant_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tenantInfo = $result->fetch_assoc();

    // SECTION 1: Tenant Information
    fputcsv($output, array("============= TENANT INFORMATION ============="));
    fputcsv($output, array("Name", "Email", "Phone", "Status"));
    fputcsv($output, array(
        cleanData($tenantInfo['tenant_name']),
        cleanData($tenantInfo['email']),
        cleanData($tenantInfo['phone']),
        cleanData($tenantInfo['status'])
    ));
    fputcsv($output, array()); // Empty line for spacing

    // SECTION 2: Units Information
    fputcsv($output, array("============= RENTED UNITS ============="));
    fputcsv($output, array(
        "Unit No", "Unit Type", "Square Meters", "Monthly Rate", 
        "Rent From", "Rent Until", "Outstanding Balance"
    ));

    // Get all units for this tenant using user_id
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
    fputcsv($output, array());

    // SECTION 3: Payment History
    fputcsv($output, array("============= PAYMENT HISTORY ============="));
    fputcsv($output, array(
        "Date", "Unit", "Amount", "Type", "Method", 
        "Reference Number", "GCash Number", "Status", "Bill Item"
    ));

    $paymentQuery = "
        SELECT 
            p.payment_id,
            p.amount,
            p.payment_date,
            p.reference_number,
            p.status,
            p.gcash_number,
            p.payment_type,
            p.bill_item,
            pr.unit_no,
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
            cleanData($payment['status']),
            cleanData($payment['bill_item'])
        ));
    }
    fputcsv($output, array());

    // SECTION 4: Maintenance History
    fputcsv($output, array("============= MAINTENANCE HISTORY ============="));
    fputcsv($output, array(
        "ID", "Date", "Unit", "Issue", "Description", "Status"
    ));

    $maintenanceQuery = "
        SELECT 
            m.id,
            m.unit,
            m.issue,
            m.description,
            m.service_date,
            m.status
        FROM maintenance_requests m
        WHERE m.user_id = ? AND m.archived = 0
        ORDER BY m.service_date DESC";

    $stmt = $conn->prepare($maintenanceQuery);
    $stmt->bind_param("i", $tenantInfo['user_id']);
    $stmt->execute();
    $maintenance = $stmt->get_result();

    while ($request = $maintenance->fetch_assoc()) {
        fputcsv($output, array(
            cleanData($request['id']),
            cleanData($request['service_date']),
            cleanData($request['unit']),
            cleanData($request['issue']),
            cleanData($request['description']),
            cleanData($request['status'])
        ));
    }
    fputcsv($output, array());

    // SECTION 5: Reservation History
    fputcsv($output, array("============= RESERVATION HISTORY ============="));
    fputcsv($output, array(
        "ID", "Date", "Time", "Unit", "Type", "Monthly Rate", 
        "Square Meters", "Status", "Created At"
    ));

    $reservationQuery = "
        SELECT 
            r.reservation_id,
            r.viewing_date,
            r.viewing_time,
            r.created_at,
            r.status,
            u.unit_no,
            u.unit_type,
            u.monthly_rent,
            u.square_meter
        FROM reservations r
        JOIN property u ON r.unit_id = u.unit_id
        WHERE r.user_id = ? AND r.archived = 0
        ORDER BY r.viewing_date DESC";

    $stmt = $conn->prepare($reservationQuery);
    $stmt->bind_param("i", $tenantInfo['user_id']);
    $stmt->execute();
    $reservations = $stmt->get_result();

    while ($reservation = $reservations->fetch_assoc()) {
        fputcsv($output, array(
            'RSV-' . str_pad($reservation['reservation_id'], 5, '0', STR_PAD_LEFT),
            cleanData($reservation['viewing_date']),
            cleanData($reservation['viewing_time']),
            cleanData($reservation['unit_no']),
            cleanData($reservation['unit_type']),
            cleanData($reservation['monthly_rent']),
            cleanData($reservation['square_meter']),
            cleanData($reservation['status']),
            cleanData($reservation['created_at'])
        ));
    }

    // Add report footer
    fputcsv($output, array());
    fputcsv($output, array("============= REPORT INFORMATION ============="));
    fputcsv($output, array("Generated on:", date('Y-m-d H:i:s')));
    fputcsv($output, array("Generated for:", $tenantInfo['tenant_name']));

    fclose($output);

} catch (Exception $e) {
    error_log("Error in export_tenant.php: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error exporting tenant information: " . $e->getMessage();
}
