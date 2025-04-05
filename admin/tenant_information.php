<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../session/session_manager.php';
require '../session/db.php';

// Modify the query to handle profile images more safely
$query = "
    SELECT 
        t.tenant_id, t.user_id, t.unit_rented, t.rent_from, t.rent_until,
        t.monthly_rate, t.outstanding_balance, t.downpayment_amount,
        t.payable_months, t.downpayment_receipt, t.created_at,
        u.name AS tenant_name, 
        CASE 
            WHEN u.profile_image IS NOT NULL AND u.profile_image != '' 
            THEN u.profile_image 
            ELSE '../images/default_avatar.png' 
        END as profile_image,
        p.unit_no, p.unit_type, p.unit_size
    FROM tenants t
    JOIN users u ON t.user_id = u.user_id
    JOIN property p ON t.unit_rented = p.unit_id
    WHERE t.status = 'active'
    ORDER BY u.name, t.created_at DESC
";

try {
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $tenants = [];

    // Group tenants by name with safer profile image handling
    while ($row = $result->fetch_assoc()) {
        $tenant_name = $row['tenant_name'];
        if (!isset($tenants[$tenant_name])) {
            // Set default profile image path
            $profile_image = '../images/default_avatar.png';
            
            // Only use uploaded image if it exists
            if (!empty($row['profile_image']) && 
                file_exists($_SERVER['DOCUMENT_ROOT'] . parse_url($row['profile_image'], PHP_URL_PATH))) {
                $profile_image = $row['profile_image'];
            }
            
            $tenants[$tenant_name] = [
                'user_id' => $row['user_id'],
                'name' => $tenant_name,
                'profile_picture' => $profile_image,
                'units' => []
            ];
        }
        $tenants[$tenant_name]['units'][] = [
            'tenant_id' => $row['tenant_id'],
            'unit_no' => $row['unit_no'],
            'unit_type' => $row['unit_type'],
            'unit_size' => $row['unit_size'],
            'rent_from' => $row['rent_from'],
            'rent_until' => $row['rent_until'],
            'monthly_rate' => $row['monthly_rate'],
            'outstanding_balance' => $row['outstanding_balance'],
            'downpayment_amount' => $row['downpayment_amount'],
            'payable_months' => $row['payable_months'],
            'downpayment_receipt' => $row['downpayment_receipt']
        ];
    }
} catch (Exception $e) {
    // Log the error and show a user-friendly message
    error_log($e->getMessage());
    die("An error occurred while loading tenant information. Please try again later.");
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
                <?php foreach ($tenants as $tenant): ?>
                <div class="tenant-card bg-white shadow-lg rounded-xl overflow-hidden transition-all duration-300 hover:shadow-xl" 
                    data-name="<?= strtolower($tenant['name']) ?>">
                    <!-- Card Header -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <img src="<?= htmlspecialchars($tenant['profile_picture']) ?>" 
                                     alt="<?= htmlspecialchars($tenant['name']) ?>'s Photo" 
                                     class="w-16 h-16 rounded-full ring-2 ring-blue-500 ring-offset-2 object-cover"
                                     onerror="this.src='../images/default_avatar.png'">
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
                            <button onclick="exportToExcel('<?= htmlspecialchars($tenant['name']) ?>')"
                                class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors group relative"
                                title="Export to Excel">
                                <i data-feather="file-text" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <div class="border-b bg-gray-50">
                        <div class="flex">
                            <button class="tab-btn flex items-center px-6 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-blue-600 hover:border-blue-600 transition-all active" data-tab="payments1">
                                <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
                                Payments
                            </button>
                            <button class="tab-btn flex items-center px-6 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-blue-600 hover:border-blue-600 transition-all" data-tab="maintenance1">
                                <i data-feather="tool" class="w-4 h-4 mr-2"></i>
                                Maintenance
                            </button>
                            <button class="tab-btn flex items-center px-6 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-blue-600 hover:border-blue-600 transition-all" data-tab="reservations1">
                                <i data-feather="calendar" class="w-4 h-4 mr-2"></i>
                                Reservations
                            </button>
                            <button class="tab-btn flex items-center px-6 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-blue-600 hover:border-blue-600 transition-all" data-tab="unit_rented1">
                                <i data-feather="home" class="w-4 h-4 mr-2"></i>
                                Unit
                            </button>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Payments Tab -->
                        <div id="payments1" class="tab-content opacity-0 transition-all duration-300">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-green-100 rounded-lg">
                                            <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium">Last Payment</p>
                                            <p class="text-sm text-gray-500">March 2024 - GCash</p>
                                        </div>
                                    </div>
                                    <span class="text-lg font-semibold">₱500</span>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div id="maintenance1" class="tab-content opacity-0 transition-all duration-300 hidden">
                            <h3 class="text-lg font-semibold mb-2">
                                <i data-feather="tool" class="inline-block w-4 h-4 mr-1"></i> Maintenance Requests
                            </h3>
                            <p><i data-feather="alert-circle" class="inline-block w-4 h-4 mr-1"></i> Leak in the kitchen sink (Pending)</p>
                            <p><i data-feather="check-circle" class="inline-block w-4 h-4 mr-1"></i> Air conditioner repair (Completed)</p>
                        </div>

                        <!-- Reservations Tab -->
                        <div id="reservations1" class="tab-content opacity-0 transition-all duration-300 hidden">
                            <h3 class="text-lg font-semibold mb-2">
                                <i data-feather="calendar" class="inline-block w-4 h-4 mr-1"></i> Upcoming Reservations
                            </h3>
                            <p><i data-feather="activity" class="inline-block w-4 h-4 mr-1"></i> Gym - April 5, 2024</p>
                            <p><i data-feather="home" class="inline-block w-4 h-4 mr-1"></i> Clubhouse - April 20, 2024</p>
                        </div>

                        <!-- Unit Rented Tab -->
                        <div id="unit_rented1" class="tab-content opacity-0 transition-all duration-300 hidden">
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold mb-4">
                                    <i data-feather="home" class="inline-block w-4 h-4 mr-1"></i> Rented Units
                                </h3>
                                <!-- Unit List -->
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
                                                <!-- Unit details -->
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

        // Modified tab switching function
        document.querySelectorAll(".tab-btn").forEach(button => {
            button.addEventListener("click", function() {
                const tabGroup = this.closest(".tenant-card");
                
                // Remove active states from all tabs
                tabGroup.querySelectorAll(".tab-btn").forEach(btn => {
                    btn.classList.remove("text-blue-600", "border-blue-600");
                    btn.classList.add("text-gray-600", "border-transparent");
                });

                // Add active state to clicked tab
                this.classList.remove("text-gray-600", "border-transparent");
                this.classList.add("text-blue-600", "border-blue-600");

                // Handle content transition
                const targetId = this.dataset.tab;
                tabGroup.querySelectorAll(".tab-content").forEach(tab => {
                    if (tab.id === targetId) {
                        tab.classList.remove('hidden');
                        // Force a reflow before removing opacity
                        void tab.offsetWidth;
                        tab.classList.remove('opacity-0');
                    } else {
                        tab.classList.add('opacity-0');
                        tab.addEventListener('transitionend', function handler() {
                            if (!tab.classList.contains('opacity-0')) return;
                            tab.classList.add('hidden');
                            tab.removeEventListener('transitionend', handler);
                        });
                    }
                });
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

        // Initialize first tab as active
        document.querySelectorAll(".tenant-card").forEach(card => {
            const firstTab = card.querySelector(".tab-btn");
            const firstContent = card.querySelector(".tab-content");
            if (firstTab && firstContent) {
                firstTab.classList.add("text-blue-600", "border-blue-600");
                firstContent.classList.remove("hidden", "opacity-0");
            }
        });
    </script>

    <!-- Include Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <script>
        function exportToExcel(tenantName) {
            // Gather tenant data
            const tenantData = [
                ['Tenant Information Report'],
                [''],
                ['Name:', tenantName],
                ['Unit:', 'A-101'],
                ['Status:', 'Active'],
                [''],
                ['Payment History'],
                ['Date', 'Amount', 'Method', 'Status'],
                ['March 2024', '₱500', 'GCash', 'Paid'],
                [''],
                ['Maintenance Requests'],
                ['Issue', 'Status', 'Date'],
                ['Kitchen sink leak', 'Pending', '2024-03-01'],
                ['AC repair', 'Completed', '2024-02-15'],
                [''],
                ['Unit Details'],
                ['Type:', 'Warehouse'],
                ['Size:', '100 sqm'],
                ['Monthly Rent:', '₱15,000'],
                ['Contract End:', 'Dec 31, 2024']
            ];

            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(tenantData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Tenant Report");

            // Generate file name
            const fileName = `Tenant_Report_${tenantName.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.xlsx`;

            // Export file
            XLSX.writeFile(wb, fileName);

            // Show success message using Toastify
            Toastify({
                text: "Excel report generated successfully!",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#10B981",
                stopOnFocus: true,
            }).showToast();
        }
    </script>

</body>
</html>
