<?php
// Start session first before any output
require_once '../session/session_manager.php';
require '../session/db.php';

// Buffer output to prevent headers already sent error
ob_start();

try {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Simplified query to get tenant data
    $query = "
        SELECT 
            t.tenant_id, 
            t.user_id,
            t.unit_rented,
            t.rent_from,
            t.rent_until,
            t.monthly_rate,
            t.outstanding_balance,
            t.downpayment_amount,
            t.status,
            u.name AS tenant_name,
            p.unit_no,
            p.unit_type,
            p.unit_size
        FROM tenants t
        JOIN users u ON t.user_id = u.user_id
        JOIN property p ON t.unit_rented = p.unit_id
        WHERE t.status = 'active'
        ORDER BY u.name";

    $result = $conn->query($query);
    
    if ($result === false) {
        throw new Exception("Query execution failed: " . $conn->error);
    }

    // Process results
    $tenants = [];
    while ($row = $result->fetch_assoc()) {
        $tenant_name = $row['tenant_name'];
        
        if (!isset($tenants[$tenant_name])) {
            $tenants[$tenant_name] = [
                'user_id' => $row['user_id'],
                'name' => $tenant_name,
                'profile_picture' => '../images/default_avatar.png',
                'units' => []
            ];
        }

        // Add unit information with all details
        $tenants[$tenant_name]['units'][] = [
            'tenant_id' => $row['tenant_id'],
            'unit_no' => $row['unit_no'],
            'unit_type' => $row['unit_type'],
            'unit_size' => $row['unit_size'],
            'rent_from' => $row['rent_from'],
            'rent_until' => $row['rent_until'],
            'monthly_rate' => $row['monthly_rate'],
            'outstanding_balance' => $row['outstanding_balance'],
            'downpayment_amount' => $row['downpayment_amount']
        ];
    }
    
} catch (Exception $e) {
    error_log("Error in tenant_information.php: " . $e->getMessage());
    $tenants = [];
}

// Now we can start outputting HTML
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
                    placeholder="Search by tenant name, unit, or status..."
                    class="w-full pl-12 pr-12 py-3.5 text-gray-700 bg-white border border-gray-200 
                           rounded-xl shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 
                           transition-all duration-300 text-base">
                <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                    <button class="text-gray-400 hover:text-gray-600">
                        <i data-feather="sliders" class="h-5 w-5"></i>
                    </button>
                </div>
            </div>

            <!-- Cards Grid -->
            <div id="tenantList" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if (empty($tenants)): ?>
                    <div class="col-span-2 text-center py-8">
                        <p class="text-gray-500">No active tenants found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tenants as $tenant): ?>
                        <div class="tenant-card bg-white shadow-lg rounded-xl overflow-hidden">
                            <!-- Card Header -->
                            <div class="p-6 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <img src="<?= htmlspecialchars($tenant['profile_picture']) ?>" 
                                             alt="<?= htmlspecialchars($tenant['name']) ?>'s Photo" 
                                             class="w-16 h-16 rounded-full object-cover">
                                        <div>
                                            <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($tenant['name']) ?></h2>
                                            <div class="flex items-center space-x-2 text-gray-500 text-sm">
                                                <i data-feather="home" class="w-4 h-4"></i>
                                                <span><?= count($tenant['units']) ?> Unit(s) Rented</span>
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabs Navigation -->
                            <div class="border-b bg-gray-50">
                                <div class="flex">
                                    <button class="tab-btn active flex items-center px-6 py-3 text-sm font-medium text-blue-600 border-b-2 border-blue-600" data-tab="payments-<?= $tenant['user_id'] ?>">
                                        <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
                                        Payments
                                    </button>
                                    <button class="tab-btn flex items-center px-6 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent" data-tab="maintenance-<?= $tenant['user_id'] ?>">
                                        <i data-feather="tool" class="w-4 h-4 mr-2"></i>
                                        Maintenance
                                    </button>
                                    <button class="tab-btn flex items-center px-6 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent" data-tab="reservations-<?= $tenant['user_id'] ?>">
                                        <i data-feather="calendar" class="w-4 h-4 mr-2"></i>
                                        Reservations
                                    </button>
                                    <button class="tab-btn flex items-center px-6 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent" data-tab="unit_rented-<?= $tenant['user_id'] ?>">
                                        <i data-feather="home" class="w-4 h-4 mr-2"></i>
                                        Unit
                                    </button>
                                </div>
                            </div>

                            <!-- Tab Content -->
                            <div class="p-6">
                                <!-- Payments Tab (Empty for now) -->
                                <div id="payments-<?= $tenant['user_id'] ?>" class="tab-content hidden">
                                    <div class="text-center text-gray-500 py-4">
                                        Payment history will be added later
                                    </div>
                                </div>

                                <!-- Maintenance Tab (Empty for now) -->
                                <div id="maintenance-<?= $tenant['user_id'] ?>" class="tab-content hidden">
                                    <div class="text-center text-gray-500 py-4">
                                        Maintenance history will be added later
                                    </div>
                                </div>

                                <!-- Reservations Tab (Empty for now) -->
                                <div id="reservations-<?= $tenant['user_id'] ?>" class="tab-content hidden">
                                    <div class="text-center text-gray-500 py-4">
                                        Reservation history will be added later
                                    </div>
                                </div>

                                <!-- Unit Tab -->
                                <div id="unit_rented-<?= $tenant['user_id'] ?>" class="tab-content block">
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
                                                            'Unit Type' => $unit['unit_type'],
                                                            'Unit Size' => $unit['unit_size'],
                                                            'Monthly Rate' => '₱' . number_format($unit['monthly_rate'], 2),
                                                            'Outstanding Balance' => '₱' . number_format($unit['outstanding_balance'], 2),
                                                            'Rent From' => $unit['rent_from'],
                                                            'Rent Until' => $unit['rent_until']
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

    <!-- JavaScript -->

    <script src="../node_modules/feather-icons/dist/feather.min.js"></script>

    <script>
        // Initialize Feather Icons
        feather.replace();

        // Search Function with Animation
        document.getElementById("searchTenant").addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();
            const tenantCards = document.querySelectorAll(".tenant-card");

            tenantCards.forEach(card => {
                const name = card.getAttribute("data-name");
                if (query && name.includes(query)) {
                    card.classList.add("border-4", "border-blue-500", "shadow-xl", "scale-105");
                } else {
                    card.classList.remove("border-4", "border-blue-500", "shadow-xl", "scale-105");
                }
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
    </script>

</body>
</html>
