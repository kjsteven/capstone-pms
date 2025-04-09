<?php
// Start output buffering - but DON'T clear it before output!
ob_start();

require '../session/db.php';

try {
    // First check database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Enhanced query to get tenant information including profile image
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
            u.profile_image, 
            u.email,
            u.phone,
            p.unit_no,
            p.unit_type,
            p.square_meter
        FROM tenants t
        JOIN users u ON t.user_id = u.user_id
        JOIN property p ON t.unit_rented = p.unit_id
        WHERE t.status = 'active'";

    $result = $conn->query($query);
    
    if ($result === false) {
        throw new Exception("Query execution failed: " . $conn->error);
    }

    $tenants = [];
    while ($row = $result->fetch_assoc()) {
        $tenant_name = $row['tenant_name'];
        
        if (!isset($tenants[$tenant_name])) {
            // Set default profile image if none exists
            $profileImage = $row['profile_image'] ? $row['profile_image'] : '../images/avatar_fallback.png';
            
            $tenants[$tenant_name] = [
                'user_id' => $row['user_id'],
                'name' => $tenant_name,
                'email' => $row['email'],
                'phone' => $row['phone'],
                'profile_picture' => $profileImage,
                'status' => $row['status'],
                'units' => [],
                'payments' => [], // Initialize payments array
                'maintenance' => [], // Initialize maintenance array
                'reservations' => [] // Initialize reservations array
            ];
        }

        // Add unit information with all details
        $tenants[$tenant_name]['units'][] = [
            'tenant_id' => $row['tenant_id'],
            'unit_no' => $row['unit_no'],
            'unit_type' => $row['unit_type'],
            'unit_size' => $row['square_meter'],
            'rent_from' => $row['rent_from'],
            'rent_until' => $row['rent_until'],
            'monthly_rate' => $row['monthly_rate'],
            'outstanding_balance' => $row['outstanding_balance']
        ];
    }
    
    // Fetch payment history for each tenant
    foreach ($tenants as $tenant_name => &$tenant_data) {
        $user_id = $tenant_data['user_id'];
        
        $payment_query = "
            SELECT 
                p.payment_id,
                p.amount,
                p.payment_date,
                p.reference_number,
                p.status,
                p.receipt_image,
                p.gcash_number,
                p.payment_type,
                p.bill_item,
                pr.unit_no
            FROM payments p
            JOIN tenants t ON p.tenant_id = t.tenant_id
            JOIN property pr ON t.unit_rented = pr.unit_id
            WHERE t.user_id = ?
            ORDER BY p.payment_date DESC";
        
        $stmt = $conn->prepare($payment_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $payment_result = $stmt->get_result();
        
        while ($payment = $payment_result->fetch_assoc()) {
            $tenant_data['payments'][] = $payment;
        }
        $stmt->close();

        // Fetch maintenance requests
        $maintenance_query = "
            SELECT 
                m.id,
                m.unit,
                m.issue,
                m.description,
                m.service_date,
                m.status,
                m.image,
                m.archived
            FROM maintenance_requests m
            WHERE m.user_id = ? AND m.archived = 0
            ORDER BY m.service_date DESC";
        
        $stmt = $conn->prepare($maintenance_query);
        $stmt->bind_param("i", $tenant_data['user_id']);
        $stmt->execute();
        $maintenance_result = $stmt->get_result();
        
        while ($maintenance = $maintenance_result->fetch_assoc()) {
            $tenant_data['maintenance'][] = $maintenance;
        }
        $stmt->close();

        // Fetch reservation history
        $reservation_query = "
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
        
        $stmt = $conn->prepare($reservation_query);
        $stmt->bind_param("i", $tenant_data['user_id']);
        $stmt->execute();
        $reservation_result = $stmt->get_result();
        
        while ($reservation = $reservation_result->fetch_assoc()) {
            $tenant_data['reservations'][] = $reservation;
        }
        $stmt->close();
    }
    
} catch (Exception $e) {
    error_log("Error in tenant_information.php: " . $e->getMessage());
    $tenants = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Profiles</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.10.3/dist/cdn.min.js" defer></script>
    <style>
        /* Optional: Custom styles for smooth transitions */
        .transition-transform {
            transition: transform 0.3s ease;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Add these styles to your existing styles */
        @keyframes scale-in {
            from {
                transform: scale(0.95);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .animate-scale-in {
            animation: scale-in 0.2s ease-out forwards;
        }

        /* Add hover animation for tenant names */
        .tenant-card h2 {
            transition: all 0.3s ease;
            position: relative;
        }

        .tenant-card h2:hover {
            color: #2563eb;
            transform: translateY(-1px);
        }

        .tenant-card h2::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #2563eb;
            transition: width 0.3s ease;
        }

        .tenant-card h2:hover::after {
            width: 100%;
        }

        /* Add a subtle cursor indicator */
        .tenant-card h2:hover::before {
            content: '👆';
            position: absolute;
            right: -25px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            opacity: 0.7;
        }
    </style>

</head>

<body class="bg-gray-100 text-gray-900">

    <!-- Include Navbar -->
    <?php include('navbarAdmin.php'); ?>

    <!-- Include Sidebar -->
    <?php include('sidebarAdmin.php'); ?>

    <!-- Main Content -->
    <div class="sm:ml-64 p-8 mt-20 mx-auto">
        <!-- Header Section -->
        <div class="mb-6">
            <h1 class="text-3xl font-semibold text-gray-800">Tenant Information</h1>
            <p class="text-gray-600">Manage and view detailed tenant profiles</p>
        </div>

        <!-- Combined Container for Search and Cards -->
        <div class="max-w-7xl mx-auto px-4 space-y-6">
            <!-- Search Section -->
            <div class="relative max-w-xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-feather="search" class="h-5 w-5 text-gray-500"></i>
                </div>
                <input type="text" 
                    id="searchTenant" 
                    placeholder="Search tenant name"
                    class="w-full pl-12 pr-12 py-3.5 text-gray-700 bg-white border border-gray-200 
                           rounded-xl shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 
                           transition-all duration-300 text-base">
                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                    <button class="text-gray-400 hover:text-gray-600">
                        <i data-feather="sliders" class="h-5 w-5"></i>
                    </button>
                </div>
            </div>

            <!-- Cards Grid - Changed to 3 columns for larger screens -->
            <div id="tenantList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($tenants)): ?>
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">No tenants found. (<?= count($tenants) ?> tenants in data)</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tenants as $tenant): ?>
                        <div class="tenant-card bg-white shadow-lg rounded-xl overflow-hidden" 
                             data-name="<?= strtolower($tenant['name']) ?>">
                            <!-- Card Header -->
                            <div class="p-6 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <img src="<?= htmlspecialchars($tenant['profile_picture']) ?>" 
                                             alt="<?= htmlspecialchars($tenant['name']) ?>'s Photo" 
                                             onerror="this.src='../images/avatar_fallback.png'"
                                             class="w-16 h-16 rounded-full object-cover"> <!-- Changed from w-16 h-16 to w-32 h-32 for 128x128 size -->
                                        <div>
                                            <h2 class="text-xl font-bold text-gray-800">
                                                <?= htmlspecialchars($tenant['name']) ?>
                                            </h2>
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center space-x-2 text-gray-500 text-sm">
                                                    <i data-feather="mail" class="w-4 h-4"></i>
                                                    <span><?= htmlspecialchars($tenant['email']) ?></span>
                                                </div>
                                                <div class="flex items-center space-x-2 text-gray-500 text-sm">
                                                    <i data-feather="phone" class="w-4 h-4"></i>
                                                    <span><?= htmlspecialchars($tenant['phone']) ?></span>
                                                </div>
                                                <div class="flex items-center space-x-2 text-gray-500 text-sm mt-1">
                                                    <i data-feather="home" class="w-4 h-4"></i>
                                                    <span><?= count($tenant['units']) ?> Unit(s) Rented</span>
                                                    <span class="px-2 py-1 text-xs rounded-full 
                                                        <?= strtolower($tenant['status']) === 'active' 
                                                            ? 'bg-green-100 text-green-800' 
                                                            : 'bg-gray-100 text-gray-800' ?>">
                                                        <?= ucfirst($tenant['status']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button onclick="exportToExcel('<?= htmlspecialchars($tenant['units'][0]['tenant_id']) ?>')"
                                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                                        <i data-feather="file-text" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Tabs Navigation - Fixed Active Tab -->
                            <div class="border-b bg-gray-50">
                                <div class="flex">
                                    <button class="tab-btn active flex items-center px-4 py-3 text-sm font-medium text-blue-600 border-b-2 border-blue-600" data-tab="payments-<?= $tenant['user_id'] ?>">
                                        <i data-feather="credit-card" class="w-4 h-4 mr-1"></i>
                                        <span class="hidden sm:inline">Payments</span>
                                    </button>
                                    <button class="tab-btn flex items-center px-4 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent" data-tab="maintenance-<?= $tenant['user_id'] ?>">
                                        <i data-feather="tool" class="w-4 h-4 mr-1"></i>
                                        <span class="hidden sm:inline">Maint.</span>
                                    </button>
                                    <button class="tab-btn flex items-center px-4 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent" data-tab="reservations-<?= $tenant['user_id'] ?>">
                                        <i data-feather="calendar" class="w-4 h-4 mr-1"></i>
                                        <span class="hidden sm:inline">Reserv.</span>
                                    </button>
                                    <button class="tab-btn flex items-center px-4 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent" data-tab="unit_rented-<?= $tenant['user_id'] ?>">
                                        <i data-feather="home" class="w-4 h-4 mr-1"></i>
                                        <span class="hidden sm:inline">Unit</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Tab Content -->
                            <div class="p-6">
                                <!-- Payments Tab -->
                                <div id="payments-<?= $tenant['user_id'] ?>" class="tab-content block">
                                    <div class="space-y-4">
                                        <h3 class="text-lg font-semibold mb-4">
                                            <i data-feather="credit-card" class="inline-block w-4 h-4 mr-1"></i> Payment History
                                        </h3>
                                        <?php if (empty($tenant['payments'])): ?>
                                            <div class="text-center text-gray-500 py-4">
                                                No payment history available for this tenant
                                            </div>
                                        <?php else: ?>
                                            <div class="overflow-x-auto">
                                                <table class="w-full min-w-full border border-gray-300">
                                                    <thead>
                                                        <tr class="bg-gray-200">
                                                            <th class="py-2 px-4 border text-sm">Unit</th>
                                                            <th class="py-2 px-4 border text-sm">Amount</th>
                                                            <th class="py-2 px-4 border text-sm">Type</th>
                                                            <th class="py-2 px-4 border text-sm">Method</th>
                                                            <th class="py-2 px-4 border text-sm">Reference #</th>
                                                            <th class="py-2 px-4 border text-sm">GCash #</th>
                                                            <th class="py-2 px-4 border text-sm">Date</th>
                                                            <th class="py-2 px-4 border text-sm">Status</th>
                                                            <th class="py-2 px-4 border text-sm">Receipt</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="payment-history" data-tenant="<?= $tenant['user_id'] ?>">
                                                        <?php 
                                                        $paymentCount = 0;
                                                        foreach ($tenant['payments'] as $payment): 
                                                            $hideClass = $paymentCount >= 3 ? 'hidden' : '';
                                                            $paymentCount++;
                                                        ?>
                                                            <tr class="payment-row <?= $hideClass ?>">
                                                                <td class="py-2 px-4 border text-sm"><?= htmlspecialchars($payment['unit_no']) ?></td>
                                                                <td class="py-2 px-4 border text-sm">₱<?= number_format($payment['amount'], 2) ?></td>
                                                                <td class="py-2 px-4 border text-sm">
                                                                    <?php 
                                                                        $paymentType = $payment['payment_type'] ?? 'rent';
                                                                        echo ucfirst(htmlspecialchars($paymentType));
                                                                        if (!empty($payment['bill_item'])) {
                                                                            echo '<br><span class="text-xs text-gray-500">' . htmlspecialchars($payment['bill_item']) . '</span>';
                                                                        }
                                                                    ?>
                                                                </td>
                                                                <td class="py-2 px-4 border text-sm">
                                                                    <?php if (!empty($payment['gcash_number'])): ?>
                                                                        <span class="flex items-center">
                                                                            <img src="../images/gcash.png" alt="GCash" class="w-4 h-4 mr-1">
                                                                            GCash
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="flex items-center">
                                                                            <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                                                                            Cash
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="py-2 px-4 border text-sm"><?= !empty($payment['reference_number']) ? htmlspecialchars($payment['reference_number']) : 'N/A' ?></td>
                                                                <td class="py-2 px-4 border text-sm"><?= !empty($payment['gcash_number']) ? htmlspecialchars($payment['gcash_number']) : 'N/A' ?></td>
                                                                <td class="py-2 px-4 border text-sm"><?= date('Y-m-d', strtotime($payment['payment_date'])) ?></td>
                                                                <td class="py-2 px-4 border text-sm">
                                                                    <?php if ($payment['status'] === 'Received'): ?>
                                                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Received</span>
                                                                    <?php elseif ($payment['status'] === 'Pending'): ?>
                                                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                                    <?php elseif ($payment['status'] === 'Rejected'): ?>
                                                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Rejected</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="py-2 px-4 border text-sm text-center">
                                                                    <?php if (!empty($payment['receipt_image'])): ?>
                                                                        <button 
                                                                            class="view-receipt bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs"
                                                                            data-receipt="../<?php echo htmlspecialchars($payment['receipt_image']); ?>"
                                                                        >
                                                                            <i class="fas fa-eye mr-1"></i> View
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="text-gray-500 text-xs">No receipt</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <?php if ($paymentCount > 3): ?>
                                                    <div class="text-center mt-3">
                                                        <button 
                                                            class="toggle-payments bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm"
                                                            data-tenant="<?= $tenant['user_id'] ?>"
                                                            data-expanded="false"
                                                        >
                                                            Show More Payments
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Maintenance Tab -->
                                <div id="maintenance-<?= $tenant['user_id'] ?>" class="tab-content hidden">
                                    <div class="space-y-4">
                                        <h3 class="text-lg font-semibold mb-4">
                                            <i data-feather="tool" class="inline-block w-4 h-4 mr-1"></i> Maintenance History
                                        </h3>
                                        <?php if (empty($tenant['maintenance'])): ?>
                                            <div class="text-center text-gray-500 py-4">
                                                No maintenance requests found
                                            </div>
                                        <?php else: ?>
                                            <div class="overflow-x-auto">
                                                <table class="w-full min-w-full border border-gray-300">
                                                    <thead>
                                                        <tr class="bg-gray-200">
                                                            <th class="py-2 px-4 border text-sm">Unit</th>
                                                            <th class="py-2 px-4 border text-sm">Issue</th>
                                                            <th class="py-2 px-4 border text-sm">Description</th>
                                                            <th class="py-2 px-4 border text-sm">Service Date</th>
                                                            <th class="py-2 px-4 border text-sm">Status</th>
                                                            <th class="py-2 px-4 border text-sm">Image</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="maintenance-history" data-tenant="<?= $tenant['user_id'] ?>">
                                                        <?php foreach ($tenant['maintenance'] as $index => $maintenance): 
                                                            $hideClass = $index >= 3 ? 'hidden' : '';
                                                        ?>
                                                            <tr class="maintenance-row <?= $hideClass ?>">
                                                                <td class="py-2 px-4 border text-sm"><?= htmlspecialchars($maintenance['unit']) ?></td>
                                                                <td class="py-2 px-4 border text-sm"><?= htmlspecialchars($maintenance['issue']) ?></td>
                                                                <td class="py-2 px-4 border text-sm"><?= htmlspecialchars($maintenance['description']) ?></td>
                                                                <td class="py-2 px-4 border text-sm"><?= date('Y-m-d', strtotime($maintenance['service_date'])) ?></td>
                                                                <td class="py-2 px-4 border text-sm">
                                                                    <?php
                                                                        $statusClass = match($maintenance['status']) {
                                                                            'Completed' => 'bg-green-100 text-green-800',
                                                                            'In Progress' => 'bg-blue-100 text-blue-800',
                                                                            'Pending' => 'bg-yellow-100 text-yellow-800',
                                                                            default => 'bg-gray-100 text-gray-800'
                                                                        };
                                                                    ?>
                                                                    <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>">
                                                                        <?= htmlspecialchars($maintenance['status']) ?>
                                                                    </span>
                                                                </td>
                                                                <td class="py-2 px-4 border text-sm text-center">
                                                                    <?php if (!empty($maintenance['image'])): ?>
                                                                        <button 
                                                                            class="view-maintenance-image bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs"
                                                                            data-image="../users/<?php echo htmlspecialchars($maintenance['image']); ?>"
                                                                        >
                                                                            <i class="fas fa-eye mr-1"></i> View
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="text-gray-500 text-xs">No image</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <?php if (count($tenant['maintenance']) > 3): ?>
                                                    <div class="text-center mt-3">
                                                        <button 
                                                            class="toggle-maintenance bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm"
                                                            data-tenant="<?= $tenant['user_id'] ?>"
                                                            data-expanded="false"
                                                        >
                                                            Show More Maintenance Requests
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Reservations Tab -->
                                <div id="reservations-<?= $tenant['user_id'] ?>" class="tab-content hidden">
                                    <div class="space-y-4">
                                        <h3 class="text-lg font-semibold mb-4">
                                            <i data-feather="calendar" class="inline-block w-4 h-4 mr-1"></i> Reservation History
                                        </h3>
                                        <?php if (empty($tenant['reservations'])): ?>
                                            <div class="text-center text-gray-500 py-4">
                                                No reservation history found
                                            </div>
                                        <?php else: ?>
                                            <div class="overflow-x-auto">
                                                <table class="w-full min-w-full border border-gray-300">
                                                    <thead>
                                                        <tr class="bg-gray-200">
                                                            <th class="py-2 px-4 border text-sm">ID</th>
                                                            <th class="py-2 px-4 border text-sm">Unit</th>
                                                            <th class="py-2 px-4 border text-sm">Type</th>
                                                            <th class="py-2 px-4 border text-sm">Rate</th>
                                                            <th class="py-2 px-4 border text-sm">Size</th>
                                                            <th class="py-2 px-4 border text-sm">Viewing Date</th>
                                                            <th class="py-2 px-4 border text-sm">Time</th>
                                                            <th class="py-2 px-4 border text-sm">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($tenant['reservations'] as $reservation): ?>
                                                            <tr>
                                                                <td class="py-2 px-4 border text-sm text-center">
                                                                    RSV-<?= str_pad($reservation['reservation_id'], 5, '0', STR_PAD_LEFT) ?>
                                                                </td>
                                                                <td class="py-2 px-4 border text-sm text-center"><?= htmlspecialchars($reservation['unit_no']) ?></td>
                                                                <td class="py-2 px-4 border text-sm text-center"><?= htmlspecialchars($reservation['unit_type']) ?></td>
                                                                <td class="py-2 px-4 border text-sm text-center">₱<?= number_format($reservation['monthly_rent'], 2) ?></td>
                                                                <td class="py-2 px-4 border text-sm text-center"><?= htmlspecialchars($reservation['square_meter']) ?> sqm</td>
                                                                <td class="py-2 px-4 border text-sm text-center"><?= date('M d, Y', strtotime($reservation['viewing_date'])) ?></td>
                                                                <td class="py-2 px-4 border text-sm text-center"><?= date('h:i A', strtotime($reservation['viewing_time'])) ?></td>
                                                                <td class="py-2 px-4 border text-sm text-center">
                                                                    <?php
                                                                        $statusClass = match(strtolower($reservation['status'])) {
                                                                            'confirmed' => 'bg-green-100 text-green-800',
                                                                            'completed' => 'bg-blue-100 text-blue-800',
                                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                                            default => 'bg-yellow-100 text-yellow-800'
                                                                        };
                                                                    ?>
                                                                    <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>">
                                                                        <?= ucfirst($reservation['status']) ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Unit Tab -->
                                <div id="unit_rented-<?= $tenant['user_id'] ?>" class="tab-content hidden">
                                    <div class="space-y-4">
                                        <h3 class="text-lg font-semibold mb-4">
                                            <i data-feather="home" class="inline-block w-4 h-4 mr-1"></i> Rented Units
                                        </h3>
                                        <div class="space-y-3">
                                            <?php foreach ($tenant['units'] as $index => $unit): ?>
                                            <div class="border rounded-lg overflow-hidden">
                                                <button onclick="toggleUnitDetails('unit<?= $tenant['user_id'] ?>_<?= $index ?>')" 
                                                    class="w-full p-4 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors">
                                                    <div class="flex items-center space-x-3">
                                                        <i data-feather="home" class="w-5 h-5 text-blue-600"></i>
                                                        <span class="font-medium">Unit <?= htmlspecialchars($unit['unit_no']) ?></span>
                                                    </div>
                                                    <i data-feather="chevron-down" class="w-5 h-5 transform transition-transform unit-chevron"></i>
                                                </button>
                                                <div id="unit<?= $tenant['user_id'] ?>_<?= $index ?>" class="hidden p-4 border-t">
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <?php
                                                        $details = [
                                                            'Unit Type' => htmlspecialchars($unit['unit_type']),
                                                            'Unit Size' => htmlspecialchars($unit['unit_size'] . ' sqm'),
                                                            'Monthly Rate' => '₱' . number_format((float)$unit['monthly_rate'], 2),
                                                            'Outstanding Balance' => '₱' . number_format((float)$unit['outstanding_balance'], 2),
                                                            'Rent From' => date('F j, Y', strtotime($unit['rent_from'])),
                                                            'Rent Until' => date('F j, Y', strtotime($unit['rent_until']))
                                                        ];
                                                        foreach ($details as $label => $value):
                                                        ?>
                                                        <div class="p-3 bg-gray-50 rounded-lg">
                                                            <p class="text-sm text-gray-600"><?= $label ?></p>
                                                            <p class="font-medium"><?= $value ?></p>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modify the Receipt Modal to be more generic -->
    <div id="receipt-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold" id="modal-title">View Image</h3>
                <button id="close-receipt-modal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex justify-center">
                <img id="modal-receipt-img" class="max-h-96 object-contain" src="" alt="Image Preview">
            </div>
            <div class="mt-4 flex justify-center">
                <a id="download-receipt" href="#" download class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-download mr-2"></i><span id="download-text">Download</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Tenant Detail Modal -->
    <div id="tenant-detail-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl mx-auto">
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-6 border-b">
                    <div class="flex items-center space-x-4">
                        <img id="modal-profile-img" class="w-24 h-24 rounded-full object-cover" src="" alt="Profile">
                        <div>
                            <h3 id="modal-tenant-name" class="text-2xl font-semibold text-gray-800"></h3>
                            <div class="flex flex-col gap-1 mt-1">
                                <div class="flex items-center space-x-2 text-gray-600">
                                    <i data-feather="mail" class="w-4 h-4"></i>
                                    <span id="modal-email"></span>
                                </div>
                                <div class="flex items-center space-x-2 text-gray-600">
                                    <i data-feather="phone" class="w-4 h-4"></i>
                                    <span id="modal-phone"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button onclick="closeTenantModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Tabs -->
                <div class="border-b bg-gray-50">
                    <div class="flex" id="modal-tabs">
                        <button class="modal-tab-btn active flex items-center px-6 py-4 text-sm font-medium border-b-2" data-tab="modal-payments">
                            <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>Payments
                        </button>
                        <button class="modal-tab-btn flex items-center px-6 py-4 text-sm font-medium border-b-2" data-tab="modal-maintenance">
                            <i data-feather="tool" class="w-4 h-4 mr-2"></i>Maintenance
                        </button>
                        <button class="modal-tab-btn flex items-center px-6 py-4 text-sm font-medium border-b-2" data-tab="modal-reservations">
                            <i data-feather="calendar" class="w-4 h-4 mr-2"></i>Reservations
                        </button>
                        <button class="modal-tab-btn flex items-center px-6 py-4 text-sm font-medium border-b-2" data-tab="modal-units">
                            <i data-feather="home" class="w-4 h-4 mr-2"></i>Units
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <div id="modal-payments" class="modal-tab-content"></div>
                    <div id="modal-maintenance" class="modal-tab-content hidden"></div>
                    <div id="modal-reservations" class="modal-tab-content hidden"></div>
                    <div id="modal-units" class="modal-tab-content hidden"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="../node_modules/feather-icons/dist/feather.min.js"></script>
    <!-- Add Toastify CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>

    <script>
        // Initialize Feather Icons
        feather.replace();

        // Track the original order of tenant cards
        let originalOrder = [];
        
        // Store original order on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tab display correctly for each card
            document.querySelectorAll('.tenant-card').forEach(card => {
                // Hide all tab contents except the active one
                card.querySelectorAll('.tab-content').forEach(content => {
                    if (content.id.includes('payments')) {
                        content.classList.remove('hidden');
                    } else {
                        content.classList.add('hidden');
                    }
                });
            });
            
            // Store the original order of tenant cards
            originalOrder = Array.from(document.querySelectorAll('.tenant-card'));
        });

        // Enhanced Search Function with Reordering
        document.getElementById("searchTenant").addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();
            const tenantCards = document.querySelectorAll(".tenant-card");
            const tenantList = document.getElementById("tenantList");
            
            // If search is empty, restore original order
            if (!query) {
                // Remove highlighting
                tenantCards.forEach(card => {
                    card.classList.remove("border-4", "border-blue-500", "shadow-xl", "scale-105");
                });
                
                // Restore original order
                originalOrder.forEach(card => {
                    tenantList.appendChild(card);
                });
                return;
            }
            
            // For non-empty search, reorganize cards
            const matchingCards = [];
            const nonMatchingCards = [];
            
            tenantCards.forEach(card => {
                const name = card.getAttribute("data-name");
                if (name && name.includes(query)) {
                    // Add highlighting
                    card.classList.add("border-4", "border-blue-500", "shadow-xl", "scale-105");
                    matchingCards.push(card);
                } else {
                    // Remove highlighting
                    card.classList.remove("border-4", "border-blue-500", "shadow-xl", "scale-105");
                    nonMatchingCards.push(card);
                }
            });
            
            // Reorder: first all matching cards, then non-matching ones
            matchingCards.concat(nonMatchingCards).forEach(card => {
                tenantList.appendChild(card);
            });
        });

        // Unit details toggle function
        function toggleUnitDetails(unitId) {
            const detailsDiv = document.getElementById(unitId);
            const button = detailsDiv.previousElementSibling;
            const chevron = button.querySelector('.unit-chevron');
            
            if (detailsDiv.classList.contains('hidden')) {
                detailsDiv.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                detailsDiv.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        // Tab switching functionality
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.tenant-card');
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all tabs in this card
                card.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('text-blue-600', 'border-blue-600');
                    btn.classList.add('text-gray-600', 'border-transparent');
                });
                
                // Add active class to clicked tab
                this.classList.remove('text-gray-600', 'border-transparent');
                this.classList.add('text-blue-600', 'border-blue-600');
                
                // Hide all tab content in this card
                card.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Show selected tab content
                card.querySelector(`#${tabId}`).classList.remove('hidden');
            });
        });

        // Add export to Excel function
        function exportToExcel(tenantId) {
            try {
                if (!tenantId) {
                    throw new Error('No tenant ID provided');
                }

                // Show loading toast
                Toastify({
                    text: "Exporting tenant information...",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#4CAF50",
                }).showToast();

                // Create and trigger download using absolute path
                const exportUrl = `${window.location.origin}/admin/export_tenant.php?tenant_id=${tenantId}`;
                window.location.href = exportUrl;
            } catch (error) {
                console.error('Export error:', error);
                Toastify({
                    text: "Error exporting tenant information: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#ff6b6b",
                }).showToast();
            }
        }

        // Update view receipt functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('view-receipt') || e.target.closest('.view-receipt')) {
                const button = e.target.classList.contains('view-receipt') ? e.target : e.target.closest('.view-receipt');
                const receiptPath = button.getAttribute('data-receipt');
                
                // Set modal for payment receipt
                document.getElementById('modal-title').textContent = 'Payment Receipt';
                document.getElementById('download-text').textContent = 'Download Receipt';
                document.getElementById('modal-receipt-img').src = receiptPath;
                document.getElementById('download-receipt').href = receiptPath;
                
                document.getElementById('receipt-modal').classList.remove('hidden');
            }
        });

        // Update maintenance image functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('view-maintenance-image') || e.target.closest('.view-maintenance-image')) {
                const button = e.target.classList.contains('view-maintenance-image') ? e.target : e.target.closest('.view-maintenance-image');
                const imagePath = button.getAttribute('data-image');
                
                // Set modal for maintenance image
                document.getElementById('modal-title').textContent = 'Maintenance Image';
                document.getElementById('download-text').textContent = 'Download Image';
                document.getElementById('modal-receipt-img').src = imagePath;
                document.getElementById('download-receipt').href = imagePath;
                
                document.getElementById('receipt-modal').classList.remove('hidden');
            }
        });

        // Close receipt modal
        document.getElementById('close-receipt-modal').addEventListener('click', function() {
            document.getElementById('receipt-modal').classList.add('hidden');
        });

        // Close modal when clicking outside
        document.getElementById('receipt-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        // Add payment history toggle functionality
        document.querySelectorAll('.toggle-payments').forEach(button => {
            button.addEventListener('click', function() {
                const tenantId = this.getAttribute('data-tenant');
                const isExpanded = this.getAttribute('data-expanded') === 'true';
                const tbody = document.querySelector(`.payment-history[data-tenant="${tenantId}"]`);
                const rows = tbody.querySelectorAll('.payment-row');

                rows.forEach((row, index) => {
                    if (index >= 3) {
                        row.classList.toggle('hidden');
                    }
                });

                // Update button text and state
                if (isExpanded) {
                    this.textContent = 'Show More Payments';
                    this.setAttribute('data-expanded', 'false');
                } else {
                    this.textContent = 'Show Less Payments';
                    this.setAttribute('data-expanded', 'true');
                }
            });
        });

        // Add maintenance history toggle functionality
        document.querySelectorAll('.toggle-maintenance').forEach(button => {
            button.addEventListener('click', function() {
                const tenantId = this.getAttribute('data-tenant');
                const isExpanded = this.getAttribute('data-expanded') === 'true';
                const tbody = document.querySelector(`.maintenance-history[data-tenant="${tenantId}"]`);
                const rows = tbody.querySelectorAll('.maintenance-row');

                rows.forEach((row, index) => {
                    if (index >= 3) {
                        row.classList.toggle('hidden');
                    }
                });

                // Update button text and state
                if (isExpanded) {
                    this.textContent = 'Show More Maintenance Requests';
                    this.setAttribute('data-expanded', 'false');
                } else {
                    this.textContent = 'Show Less Maintenance Requests';
                    this.setAttribute('data-expanded', 'true');
                }
            });
        });

        function showTenantModal(nameElement) {
            try {
                const card = nameElement.closest('.tenant-card');
                const modal = document.getElementById('tenant-detail-modal');
                
                // Get elements with error handling
                const profileImg = card.querySelector('img')?.src || '../images/avatar_fallback.png';
                const tenantName = card.querySelector('h2')?.textContent?.trim() || 'N/A';
                
                // Get email and phone with better selectors
                const emailSpan = card.querySelector('.flex.items-center.space-x-2:nth-child(1) span')?.textContent || 'N/A';
                const phoneSpan = card.querySelector('.flex.items-center.space-x-2:nth-child(2) span')?.textContent || 'N/A';
                
                // Set tenant info safely
                document.getElementById('modal-profile-img').src = profileImg;
                document.getElementById('modal-tenant-name').textContent = tenantName;
                document.getElementById('modal-email').textContent = emailSpan;
                document.getElementById('modal-phone').textContent = phoneSpan;
                
                // Clone content for each tab
                const tabContents = {
                    'modal-payments': card.querySelector(`[id^="payments-"]`),
                    'modal-maintenance': card.querySelector(`[id^="maintenance-"]`),
                    'modal-reservations': card.querySelector(`[id^="reservations-"]`),
                    'modal-units': card.querySelector(`[id^="unit_rented-"]`)
                };
                
                Object.entries(tabContents).forEach(([modalId, content]) => {
                    if (content) {
                        const modalContent = document.getElementById(modalId);
                        if (modalContent) {
                            modalContent.innerHTML = content.innerHTML;
                            initializeToggles(modalContent);
                            initializeViewButtons(modalContent);
                        }
                    }
                });

                // After cloning the content, reinitialize unit toggles for the modal
                const modalUnits = document.getElementById('modal-units');
                if (modalUnits) {
                    const toggleButtons = modalUnits.querySelectorAll('button[onclick^="toggleUnitDetails"]');
                    toggleButtons.forEach(button => {
                        // Remove the original onclick attribute
                        const unitId = button.getAttribute('onclick').match(/'([^']+)'/)[1];
                        button.removeAttribute('onclick');
                        
                        // Add new click event listener
                        button.addEventListener('click', () => {
                            const detailsDiv = modalUnits.querySelector(`#${unitId}`);
                            const chevron = button.querySelector('.unit-chevron');
                            
                            if (detailsDiv) {
                                if (detailsDiv.classList.contains('hidden')) {
                                    detailsDiv.classList.remove('hidden');
                                    chevron.style.transform = 'rotate(180deg)';
                                } else {
                                    detailsDiv.classList.add('hidden');
                                    chevron.style.transform = 'rotate(0deg)';
                                }
                            }
                        });
                    });
                }

                modal.classList.remove('hidden');
                feather.replace();
                
                // Show first tab content
                showModalTab('modal-payments');
            } catch (error) {
                console.error('Error in showTenantModal:', error);
                Toastify({
                    text: "Error loading tenant details",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#ff6b6b",
                }).showToast();
            }
        }

        function initializeToggles(container) {
            // Reinitialize show more/less buttons
            container.querySelectorAll('[class*="toggle-"]').forEach(button => {
                const originalButton = button.cloneNode(true);
                button.parentNode.replaceChild(originalButton, button);
                
                originalButton.addEventListener('click', function() {
                    const isPayments = this.classList.contains('toggle-payments');
                    const tbody = this.closest('.overflow-x-auto').querySelector('tbody');
                    const rows = tbody.querySelectorAll(isPayments ? '.payment-row' : '.maintenance-row');
                    
                    rows.forEach((row, index) => {
                        if (index >= 3) {
                            row.classList.toggle('hidden');
                        }
                    });
                    
                    this.textContent = this.textContent.includes('More') ? 
                        `Show Less ${isPayments ? 'Payments' : 'Maintenance Requests'}` :
                        `Show More ${isPayments ? 'Payments' : 'Maintenance Requests'}`;
                });
            });
        }

        function initializeViewButtons(container) {
            // Reinitialize receipt/image view buttons
            container.querySelectorAll('.view-receipt, .view-maintenance-image').forEach(button => {
                const originalButton = button.cloneNode(true);
                button.parentNode.replaceChild(originalButton, button);
                
                originalButton.addEventListener('click', function() {
                    const receiptModal = document.getElementById('receipt-modal');
                    const modalImg = document.getElementById('modal-receipt-img');
                    const downloadLink = document.getElementById('download-receipt');
                    const imagePath = this.getAttribute('data-receipt') || this.getAttribute('data-image');
                    
                    modalImg.src = imagePath;
                    downloadLink.href = imagePath;
                    receiptModal.classList.remove('hidden');
                });
            });
        }

        function showModalTab(tabId) {
            // Handle tab button styling
            document.querySelectorAll('.modal-tab-btn').forEach(btn => {
                btn.classList.remove('text-blue-600', 'border-blue-600');
                btn.classList.add('text-gray-600', 'border-transparent');
            });
            document.querySelector(`[data-tab="${tabId}"]`).classList.add('text-blue-600', 'border-blue-600');
            
            // Show selected content
            document.querySelectorAll('.modal-tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById(tabId).classList.remove('hidden');
        }

        // Add modal tab functionality
        document.querySelectorAll('.modal-tab-btn').forEach(button => {
            button.addEventListener('click', () => showModalTab(button.getAttribute('data-tab')));
        });

        // Modify the search function to exclude modal content
        document.getElementById("searchTenant").addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();
            const tenantCards = document.querySelectorAll("#tenantList > .tenant-card");
            // Rest of the search function remains the same...
        });

        // Add these new functions after existing JavaScript
        function closeTenantModal() {
            const modal = document.getElementById('tenant-detail-modal');
            modal.classList.add('hidden');
        }

        // Add click handler to tenant names
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.tenant-card h2').forEach(name => {
                name.style.cursor = 'pointer';
                name.addEventListener('click', function() {
                    showTenantModal(this);
                });
            });
        });

        // Close modal when clicking outside
        document.getElementById('tenant-detail-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTenantModal();
            }
        });
    </script>

    <style>
    /* Add these styles to your existing styles */
    @keyframes scale-in {
        from {
            transform: scale(0.95);
            opacity: 0;
        }
        to {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .animate-scale-in {
        animation: scale-in 0.2s ease-out forwards;
    }
    </style>

</body>
</html>
