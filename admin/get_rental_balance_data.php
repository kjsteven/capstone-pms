<?php
require '../session/db.php';

// Get counts of tenants by payment status
$tenantsQuery = "
    SELECT 
        COUNT(CASE WHEN outstanding_balance = 0 THEN 1 END) as paid_tenants,
        COUNT(CASE WHEN outstanding_balance > 0 THEN 1 END) as outstanding_tenants,
        COUNT(*) as total_tenants
    FROM tenants
    WHERE status = 'active'
";

$tenantsResult = mysqli_query($conn, $tenantsQuery);
$tenantsData = mysqli_fetch_assoc($tenantsResult);

$paidTenants = (int)$tenantsData['paid_tenants'];
$outstandingTenants = (int)$tenantsData['outstanding_tenants'];
$totalTenants = (int)$tenantsData['total_tenants'];

// Calculate tenant percentages
$paidTenantsPercentage = $totalTenants > 0 ? round(($paidTenants / $totalTenants) * 100) : 0;
$outstandingTenantsPercentage = $totalTenants > 0 ? round(($outstandingTenants / $totalTenants) * 100) : 0;

// Calculate total paid amount (sum of all received rent payments)
$paymentsQuery = "
    SELECT SUM(amount) as total_paid
    FROM payments 
    WHERE status = 'Received' AND payment_type = 'rent'
";

$paymentsResult = mysqli_query($conn, $paymentsQuery);
$paymentsData = mysqli_fetch_assoc($paymentsResult);
$totalPaid = (float)($paymentsData['total_paid'] ?: 0);

// Calculate total outstanding balance (sum of all active tenants' outstanding balances)
$outstandingQuery = "
    SELECT SUM(outstanding_balance) as total_outstanding
    FROM tenants
    WHERE status = 'active'
";

$outstandingResult = mysqli_query($conn, $outstandingQuery);
$outstandingData = mysqli_fetch_assoc($outstandingResult);
$totalOutstanding = (float)($outstandingData['total_outstanding'] ?: 0);

// Calculate total expected rent
$totalExpected = $totalPaid + $totalOutstanding;

// Prepare response with both raw values and percentages
echo json_encode([
    'success' => true,
    'data' => [
        'paid' => $totalPaid,
        'outstanding' => $totalOutstanding,
        'total' => $totalExpected,
        'paid_percentage' => $totalExpected > 0 ? round(($totalPaid / $totalExpected) * 100) : 0,
        'outstanding_percentage' => $totalExpected > 0 ? round(($totalOutstanding / $totalExpected) * 100) : 0,
        'paid_tenants' => $paidTenants,
        'outstanding_tenants' => $outstandingTenants,
        'total_tenants' => $totalTenants,
        'paid_tenants_percentage' => $paidTenantsPercentage,
        'outstanding_tenants_percentage' => $outstandingTenantsPercentage
    ]
]);

mysqli_close($conn);
?>
