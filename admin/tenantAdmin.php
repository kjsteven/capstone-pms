<?php

require_once '../session/session_manager.php';
require '../session/db.php';

start_secure_session();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../authentication/login.php');
    exit();
}

// Pagination settings
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;

// Get total number of active tenants
$totalQuery = "SELECT COUNT(*) as total FROM tenants WHERE status = 'active'";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $entriesPerPage);

// Fetch units that are not yet occupied
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_units'])) {
    header('Content-Type: application/json');
    ob_clean();

    try {
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

        if (!$userId) {
            throw new Exception('User ID is required');
        }

        $query = "
            SELECT p.unit_id, p.unit_no, p.monthly_rent
            FROM reservations r
            JOIN property p ON r.unit_id = p.unit_id
            WHERE r.user_id = ? 
            AND r.status = 'confirmed'
            AND p.status != 'Occupied'
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $units = [];
        while ($row = $result->fetch_assoc()) {
            $units[] = [
                'unit_id' => $row['unit_id'],
                'unit_no' => htmlspecialchars($row['unit_no'], ENT_QUOTES, 'UTF-8'),
                'monthly_rent' => $row['monthly_rent']
            ];
        }

        echo json_encode(['success' => true, 'data' => $units]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
        ]);
    }
    exit();
}


// Fetch users from confirmed reservations who are not yet tenants
$usersQuery = "
    SELECT DISTINCT r.user_id, u.name 
    FROM reservations r
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.status = 'confirmed' 
    AND r.user_id NOT IN (
        SELECT user_id FROM tenants WHERE status = 'active'
    )";
$usersResult = $conn->query($usersQuery);
$users = $usersResult->fetch_all(MYSQLI_ASSOC);

// Fetch active tenants along with their user name and tenant id
$tenantsResult = $conn->query("SELECT tenants.*, users.name AS user_name, property.unit_no 
                              FROM tenants 
                              LEFT JOIN users ON tenants.user_id = users.user_id
                              LEFT JOIN property ON tenants.unit_rented = property.unit_id
                              WHERE tenants.status = 'active'
                              LIMIT $entriesPerPage OFFSET $offset");

$tenants = $tenantsResult->fetch_all(MYSQLI_ASSOC);

// Create an array of user IDs that are already tenants
$tenantUserIds = array_column($tenants, 'user_id');




// Handle POST requests for adding/editing tenants
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  

    $message = '';
    // Handle form submission for adding or editing tenants
    try {
        $user_id = (int)$_POST['user_id'];
        $unit_rented = (int)$_POST['unit_rented'];
        $rent_from = $_POST['rent_from'];
        $rent_until = $_POST['rent_until'];
        $monthly_rate = (float)$_POST['monthly_rate'];
        $downpayment_amount = (float)$_POST['downpayment_amount'];

        $date1 = new DateTime($rent_from);
        $date2 = new DateTime($rent_until);
        $interval = $date1->diff($date2);
        $months = $interval->m + ($interval->y * 12);

        $total_rent = $months * $monthly_rate;
        $outstanding_balance = $total_rent - $downpayment_amount;
        $payable_months = ceil($outstanding_balance / $monthly_rate);

        if (isset($_POST['tenant_id']) && !empty($_POST['tenant_id'])) {
            $tenant_id = (int)$_POST['tenant_id'];

            $stmt = $conn->prepare(
                "UPDATE tenants 
                 SET user_id = ?, unit_rented = ?, rent_from = ?, rent_until = ?, monthly_rate = ?, outstanding_balance = ?, downpayment_amount = ?, payable_months = ?, updated_at = CURRENT_TIMESTAMP 
                 WHERE tenant_id = ?"
            );
            $stmt->bind_param("isssssidi", $user_id, $unit_rented, $rent_from, $rent_until, $monthly_rate, $outstanding_balance, $downpayment_amount, $payable_months, $tenant_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO tenants (user_id, unit_rented, rent_from, rent_until, monthly_rate, outstanding_balance, downpayment_amount, payable_months, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            );
            $stmt->bind_param("isssssid", $user_id, $unit_rented, $rent_from, $rent_until, $monthly_rate, $outstanding_balance, $downpayment_amount, $payable_months);
            $stmt->execute();
        }

        $updateUnitStatus = $conn->prepare("UPDATE property SET status = 'Occupied' WHERE unit_id = ?");
        $updateUnitStatus->bind_param("i", $unit_rented);
        $updateUnitStatus->execute();

        $updateReservation = $conn->prepare("UPDATE reservations SET status = 'completed' WHERE user_id = ? AND unit_id = ? AND status = 'confirmed'");
        $updateReservation->bind_param("ii", $user_id, $unit_rented);
        $updateReservation->execute();

        echo json_encode(['success' => true, 'message' => 'Operation successful!']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit();
}

// Handle GET requests for archiving tenants 

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    try {
        if ($_GET['action'] === 'archive' && isset($_GET['id'])) {
            $tenant_id = (int)$_GET['id'];
            $stmt = $conn->prepare("SELECT unit_rented FROM tenants WHERE tenant_id = ?");
            $stmt->bind_param("i", $tenant_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $tenant = $result->fetch_assoc();

            $archiveStmt = $conn->prepare("UPDATE tenants SET status = 'archived' WHERE tenant_id = ?");
            $archiveStmt->bind_param("i", $tenant_id);
            $archiveStmt->execute();

            $updateUnitStatus = $conn->prepare("UPDATE property SET status = 'Available' WHERE unit_id = ?");
            $updateUnitStatus->bind_param("i", $tenant['unit_rented']);
            $updateUnitStatus->execute();

            echo htmlspecialchars('Tenant successfully archived.', ENT_QUOTES, 'UTF-8');
        } elseif ($_GET['action'] === 'edit' && isset($_GET['id'])) {
            $tenant_id = (int)$_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
            $stmt->bind_param("i", $tenant_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $tenant = $result->fetch_assoc();
            echo json_encode($tenant);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
    exit();
}

?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Manage Tenant</title>
    <link rel="icon" href="../images/logo.png" type="image/png">

    <!-- Toastify CSS -->
    <link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet">
</head>

<body>
    <?php include('navbarAdmin.php'); ?>
    <?php include('sidebarAdmin.php'); ?>

    <div class="sm:ml-64 p-8 mt-20 mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-semibold text-gray-800">Tenants Management</h1>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div class="flex flex-wrap items-center gap-4 flex-1">
        <!-- Entries per page -->
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Show entries:</label>
            <select id="entriesPerPage" class="border rounded px-2 py-1.5" onchange="changeEntries(this.value)">
                <option value="10" <?php echo $entriesPerPage == 10 ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo $entriesPerPage == 25 ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo $entriesPerPage == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $entriesPerPage == 100 ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
        
        <!-- Search Bar -->
        <div class="relative flex-1 max-w-sm">
            <input type="text" id="search-keyword" placeholder="Search..." 
                   class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300">
            <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                <svg data-feather="search" class="w-4 h-4"></svg>
            </button>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-2">
        <button id="printButton" class="px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2">
            <svg data-feather="printer" class="w-4 h-4"></svg>
            Print
        </button>
        <button id="newTenant" class="px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2">
            <svg data-feather="plus" class="w-4 h-4"></svg>
            New Tenant
        </button>
        <button id="newUnitforTenant" class="px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2">
            <svg data-feather="plus" class="w-4 h-4"></svg>
            Add Unit
        </button>
    </div>
</div>
  

    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="min-w-full table-auto border-collapse border border-gray-300">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2 text-left border border-gray-300">Tenant ID</th>
                <th class="px-4 py-2 text-left border border-gray-300">Name</th>
                <th class="px-4 py-2 text-left border border-gray-300 extra-column">Unit No</th>
                <th class="px-4 py-2 text-left border border-gray-300 extra-column">Rent From</th>
                <th class="px-4 py-2 text-left border border-gray-300 extra-column">Rent Until</th>
                <th class="px-4 py-2 text-left border border-gray-300 extra-column">Monthly Rate</th>
                <th class="px-4 py-2 text-left border border-gray-300 extra-column">Downpayment Amount</th>
                <th class="px-4 py-2 text-left border border-gray-300 extra-column">Outstanding Balance</th>
                <th class="px-4 py-2 text-left border border-gray-300 extra-column">Payable Months</th>
                <th class="px-4 py-2 text-left border border-gray-300 extra-column">Registration Date</th>
                <th class="px-4 py-2 text-left border border-gray-300 extra-column">Action</th>
            </tr>
        </thead>
        <tbody id="tenantTableBody">
            <?php foreach ($tenants as $index => $tenant) : ?>
            <tr>
                <td class="px-4 py-2 text-left border border-gray-300"><?= $tenant['tenant_id'] ?></td>
                <td class="px-4 py-2 text-left border border-gray-300"><?= isset($tenant['user_name']) ? $tenant['user_name'] : 'N/A' ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= $tenant['unit_no'] ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= $tenant['rent_from'] ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= $tenant['rent_until'] ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= isset($tenant['monthly_rate']) ? '₱' . number_format($tenant['monthly_rate'], 2) : '₱0.00' ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= isset($tenant['downpayment_amount']) ? '₱' . number_format($tenant['downpayment_amount'], 2) : '₱0.00' ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= isset($tenant['outstanding_balance']) ? '₱' . number_format($tenant['outstanding_balance'], 2) : '₱0.00' ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= $tenant['payable_months'] ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= $tenant['created_at'] ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column">
                    <button class="text-blue-500 hover:text-blue-700" onclick="editTenant(<?= $tenant['tenant_id'] ?>)">Edit</button>
                    <button class="text-red-500 hover:text-red-700" onclick="archiveTenant(<?= $tenant['tenant_id'] ?>)">Archive</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination controls -->
<div class="mt-4 flex flex-wrap items-center justify-between gap-4">
    <div class="text-sm text-gray-600">
        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $entriesPerPage, $totalRows); ?> of <?php echo $totalRows; ?> entries
    </div>
    <div class="flex flex-wrap gap-2">
        <?php if($totalPages > 1): ?>
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&entries=<?php echo $entriesPerPage; ?>" 
                   class="px-3 py-1 border rounded <?php echo $page === $i ? 'bg-blue-600 text-white' : 'text-gray-600'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</div>



<!-- Modal for Adding New Tenant -->
<div id="tenantModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full sm:w-96">
        <h2 id="modalTitle" class="text-xl font-semibold mb-4">New Tenant</h2>
        <form id="tenantForm" method="POST">
            <input type="hidden" id="tenant_id" name="tenant_id">
            
            <div class="mb-4">
                <label for="user_id" class="block text-sm font-semibold text-gray-700">Select User with Reservation</label>
                <select name="user_id" id="user_id" class="w-full border border-gray-300 rounded px-4 py-2" required>
                    <option value="" disabled selected>Select a user</option>
                    <?php foreach ($users as $user) : ?>
                        <?php if (!in_array($user['user_id'], $tenantUserIds)) : ?>
                            <option value="<?= $user['user_id'] ?>"><?= $user['name'] ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="unit_rented" class="block text-sm font-semibold">Reserved Unit</label>
                <select name="unit_rented" id="unit_rented" class="w-full border border-gray-300 rounded px-4 py-2" required>
                    <option value="" disabled selected>Select a user first</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="monthly_rate" class="block text-sm font-semibold">Monthly Rate</label>
                <input type="text" id="monthly_rate" name="monthly_rate" readonly class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50">
            </div>

            <div class="mb-4">
                <label for="rent_from" class="block text-sm font-semibold">Rent From</label>
                <input type="date" id="rent_from" name="rent_from" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="rent_until" class="block text-sm font-semibold">Rent Until</label>
                <input type="date" id="rent_until" name="rent_until" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="downpayment_amount" class="block text-sm font-semibold">Downpayment Amount</label>
                <input type="number" id="downpayment_amount" name="downpayment_amount" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md"
                       step="0.01" min="0">
            </div>

            <div class="flex justify-end">
                <button type="button" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md mr-2" onclick="closeModal()">Cancel</button>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md">Save</button>
            </div>
        </form>
    </div>
    </div>


   <!-- Modal for Adding Unit to Existing Tenant -->
<div id="existingTenantModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full sm:w-96">
        <h2 id="existingModalTitle" class="text-xl font-semibold mb-4">Add Unit for Existing Tenant</h2>
        <form id="existingTenantForm">
            <input type="hidden" id="existing_tenant_id" name="tenant_id">
            
            <div class="mb-4">
                <label for="existing_user_id" class="block text-sm font-semibold text-gray-700">Select Tenant</label>
                <select name="user_id" id="existing_user_id" class="w-full border border-gray-300 rounded px-4 py-2" required>
                    <option value="" disabled selected>Select a tenant</option>
                    <?php
                    $uniqueTenants = array_reduce($tenants, function($carry, $tenant) {
                        if (!isset($carry[$tenant['user_id']])) {
                            $carry[$tenant['user_id']] = $tenant;
                        }
                        return $carry;
                    }, []);
                    
                    foreach ($uniqueTenants as $tenant) : ?>
                        <option value="<?= $tenant['user_id'] ?>"><?= $tenant['user_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="existing_unit_rented" class="block text-sm font-semibold">Reserved Unit</label>
                <select name="unit_rented" id="existing_unit_rented" class="w-full border border-gray-300 rounded px-4 py-2" required>
                    <option value="" disabled selected>Select a tenant first</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="existing_monthly_rate" class="block text-sm font-semibold">Monthly Rate</label>
                <input type="text" id="existing_monthly_rate" name="monthly_rate" readonly class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50">
            </div>

            <div class="mb-4">
                <label for="existing_rent_from" class="block text-sm font-semibold">Rent From</label>
                <input type="date" id="existing_rent_from" name="rent_from" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="existing_rent_until" class="block text-sm font-semibold">Rent Until</label>
                <input type="date" id="existing_rent_until" name="rent_until" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="existing_downpayment_amount" class="block text-sm font-semibold">Downpayment Amount</label>
                <input type="number" id="existing_downpayment_amount" name="downpayment_amount" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md"
                    step="0.01" min="0">
            </div>

            <div class="flex justify-end">
                <button type="button" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md mr-2" onclick="closeExistingModal()">Cancel</button>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md">Save</button>
            </div>
        </form>
    </div>
</div>


    <script src="../node_modules/feather-icons/dist/feather.min.js"></script>

    <script>
        // Initialize Feather Icons
        feather.replace();
    </script>

    <!-- Include Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>


    <script>
        // Show modal
        document.getElementById('newUnitforTenant').addEventListener('click', function() {
            document.getElementById('existingTenantModal').classList.remove('hidden');
            document.getElementById('existingModalTitle').textContent = 'Add Unit for Existing Tenant';
            document.getElementById('existingTenantForm').reset();
            document.getElementById('existing_unit_rented').innerHTML = '<option value="" disabled selected>Select a tenant first</option>';
        });

        // Close modal
        function closeExistingModal() {
            document.getElementById('existingTenantModal').classList.add('hidden');
        }

        // Handle tenant selection
        document.getElementById('existing_user_id').addEventListener('change', async function() {
            const userId = this.value;
            const unitSelect = document.getElementById('existing_unit_rented');
            const monthlyRateInput = document.getElementById('existing_monthly_rate');

            unitSelect.innerHTML = '<option value="" disabled selected>Loading...</option>';
            monthlyRateInput.value = '';

            if (!userId) {
                unitSelect.innerHTML = '<option value="" disabled selected>Please select a tenant</option>';
                return;
            }

            try {
                const response = await fetch(`tenantAdmin.php?get_units&user_id=${userId}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Failed to fetch units');
                }

                unitSelect.innerHTML = '';
                const units = data.data;

                if (units && units.length > 0) {
                    units.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.unit_id;
                        option.textContent = `Unit ${unit.unit_no}`;
                        option.setAttribute('data-rent', unit.monthly_rent);
                        unitSelect.appendChild(option);
                    });

                    const selectedOption = unitSelect.options[0];
                    monthlyRateInput.value = selectedOption.getAttribute('data-rent');
                } else {
                    unitSelect.innerHTML = '<option value="" disabled selected>No reserved units found</option>';
                }
            } catch (error) {
                console.error('Error:', error);
                unitSelect.innerHTML = '<option value="" disabled selected>Error loading units</option>';
                showToast(error.message, 'error');
            }
        });

        // Form submission
        document.getElementById('existingTenantForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            
            try {
                const response = await fetch('rent_unit.php', {
                    method: 'POST',
                    body: new FormData(this)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Invalid response format');
                }

                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Operation failed');
                }

                closeExistingModal();
                showToast(data.message || 'Unit added successfully!', 'success');
                setTimeout(() => location.reload(), 1000);

            } catch (error) {
                console.error('Error:', error);
                showToast(error.message, 'error');
            }
        });

        // Toast utility function
        function showToast(message, type = 'success') {
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                style: {
                    background: type === 'success' ? "#4CAF50" : "#f44336"
                }
            }).showToast();
        }
    </script>

        
    <script>
        // Show the modal for creating a new tenant
        document.getElementById('newTenant').addEventListener('click', function() {
            document.getElementById('tenantModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'New Tenant';
            document.getElementById('tenantForm').reset();
            document.getElementById('unit_rented').innerHTML = '<option value="" disabled selected>Select a user first</option>';
        });

        // Handle unit selection change
        document.getElementById('unit_rented').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const rent = selectedOption.getAttribute('data-rent');
            document.getElementById('monthly_rate').value = rent || '';
        });

        // Close the modal
        function closeModal() {
            document.getElementById('tenantModal').classList.add('hidden');
        }


      
        
       // Handle form submission for adding/editing tenants
    document.getElementById('tenantForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.headers.get('content-type')?.includes('application/json')) {
                return response.json();
            } else {
                // If response is not JSON, treat as success since data was saved
                return { success: true, message: 'Tenant saved successfully!' };
            }
        })
        .then(data => {
            // Close modal first
            closeModal();
            
            // Show success notification
            Toastify({
                text: "Tenant saved successfully!",
                duration: 3000,
                gravity: "top",
                position: "right",
                style: {
                    background: "#4CAF50"
                }
            }).showToast();

            // Reload the page after a delay
            setTimeout(() => {
                location.reload();
            }, 3000);
        })
        .catch(error => {
            console.error('Error:', error);
            Toastify({
                text: "An error occurred while saving",
                duration: 3000,
                gravity: "top",
                position: "right",
                style: {
                    background: "#f44336"
                }
            }).showToast();
        });
    });

        
        // Edit tenant function
        function editTenant(tenantId) {
            fetch(`?action=edit&id=${tenantId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('tenantModal').classList.remove('hidden');
                    document.getElementById('tenant_id').value = data.tenant_id;
                    document.getElementById('user_id').value = data.user_id;
                    
                    // Trigger user selection change to load units
                    const userChangeEvent = new Event('change');
                    document.getElementById('user_id').dispatchEvent(userChangeEvent);

                    // Set remaining fields after a brief delay to ensure units are loaded
                    setTimeout(() => {
                        document.getElementById('unit_rented').value = data.unit_rented;
                        document.getElementById('rent_from').value = data.rent_from;
                        document.getElementById('rent_until').value = data.rent_until;
                        document.getElementById('monthly_rate').value = data.monthly_rate;
                        document.getElementById('downpayment_amount').value = data.downpayment_amount;
                        document.getElementById('modalTitle').textContent = 'Edit Tenant';
                    }, 500);
                });
        }



        // Search filter functionality
       document.getElementById('search-keyword').addEventListener('input', function () {
            let searchKeyword = this.value.toLowerCase();
            let tenantRows = document.querySelectorAll('#tenantTableBody tr');

            tenantRows.forEach(function (row) {
                let name = row.cells[1].textContent.toLowerCase();

                if (searchKeyword === '') {
                    // Reset to default view: hide extra columns for all rows
                    row.style.display = ''; // Show all rows
                    row.querySelectorAll('.extra-column').forEach(col => col.classList.add('hidden'));
                } else if (name.includes(searchKeyword)) {
                    row.style.display = ''; // Show matching row
                    row.querySelectorAll('.extra-column').forEach(col => col.classList.remove('hidden')); // Show extra columns
                } else {
                    row.style.display = 'none'; // Hide non-matching rows
                }
            });
        });



        //Print Function
        document.getElementById('printButton').addEventListener('click', function () {
        // Save the current HTML
        const originalContent = document.body.innerHTML;

        // Extract the table content only
        const tableContent = document.querySelector('table').outerHTML;

        // Replace body content with only the table
        document.body.innerHTML = `
            <html>
                <head>
                    <title>Print Table</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 0;
                            padding: 0;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 20px 0;
                            font-size: 12px;
                            text-align: left;
                        }
                        th, td {
                            padding: 8px;
                            border: 1px solid #ddd;
                            word-wrap: break-word; /* Ensures content wraps in narrow columns */
                        }
                        th {
                            background-color: #f4f4f4;
                        }
                    </style>
                </head>
                <body>
                    ${tableContent}
                </body>
            </html>
        `;

        // Print the table
        window.print();

        // Restore the original HTML after printing
        document.body.innerHTML = originalContent;

        // Reload the scripts (to restore JavaScript functionality)
        window.location.reload();
    });

      

       

  // Archive tenant
        function archiveTenant(tenantId) {
            if (confirm('Are you sure you want to archive this tenant?')) {
                fetch(`?action=archive&id=${tenantId}`, {
                    method: 'GET'
                })
                .then(response => response.text())
                .then(message => {
                    // Show a success notification for tenant archiving
                    Toastify({
                        text: "Tenant Archived Successfully", // The success message
                        backgroundColor: "green", // Green background for success
                        duration: 2000, // Duration of 2 seconds
                        close: true // Show close button for manual dismissal
                    }).showToast();

                    // Delay the page reload to ensure the toast is visible
                    setTimeout(() => {
                        window.location.reload(); // Reload the page after showing notification
                    }, 1000); // Wait a little longer than the toast's duration to reload
                })
                .catch(error => {
                    // Show an error notification in case of failure
                    Toastify({
                        text: "Error archiving tenant. Please try again.", // The error message
                        backgroundColor: "red", // Red background for error
                        duration: 3000, // Duration of 3 seconds
                        close: true // Show close button for manual dismissal
                    }).showToast();
                });
            }
        }


        // for fetching units and users where status is confirmed from reservations table
        document.getElementById('user_id').addEventListener('change', function() {
            const userId = this.value;
            const unitSelect = document.getElementById('unit_rented');
            const monthlyRateInput = document.getElementById('monthly_rate');

            // Reset fields
            unitSelect.innerHTML = '<option value="" disabled selected>Loading...</option>';
            monthlyRateInput.value = '';

            if (!userId) {
                unitSelect.innerHTML = '<option value="" disabled selected>Please select a user</option>';
                return;
            }

            // Debug log
            console.log('Fetching units for user:', userId);

            fetch(`tenantAdmin.php?get_units&user_id=${userId}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(response => {
                    console.log('Received data:', response);
                    
                    if (!response.success) {
                        throw new Error(response.error || 'Unknown error occurred');
                    }

                    const units = response.data;
                    unitSelect.innerHTML = '';
                    
                    if (units && units.length > 0) {
                        units.forEach(unit => {
                            const option = document.createElement('option');
                            option.value = unit.unit_id;
                            option.textContent = `Unit ${unit.unit_no}`;
                            option.setAttribute('data-rent', unit.monthly_rent);
                            unitSelect.appendChild(option);
                        });
                        
                        // Set first unit as selected and update monthly rate
                        unitSelect.selectedIndex = 0;
                        const selectedOption = unitSelect.options[0];
                        monthlyRateInput.value = selectedOption.getAttribute('data-rent');
                    } else {
                        unitSelect.innerHTML = '<option value="" disabled selected>No available units found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    unitSelect.innerHTML = '<option value="" disabled selected>Error loading units</option>';
                    Toastify({
                        text: `Error: ${error.message}`,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#ff0000"
                    }).showToast();
                });
        });

        // Add entries per page change handler
        function changeEntries(value) {
            window.location.href = `?entries=${value}&page=1`;
        }

        // Modify search functionality to work with pagination
        document.getElementById('search-keyword').addEventListener('input', function() {
            let searchTerm = this.value.toLowerCase();
            let rows = document.querySelectorAll('#tenantTableBody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                let name = row.cells[1].textContent.toLowerCase();
                let shouldShow = searchTerm === '' || name.includes(searchTerm);
                row.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visibleCount++;
                
                // Show/hide extra columns based on search
                if (searchTerm !== '') {
                    row.querySelectorAll('.extra-column').forEach(col => col.classList.remove('hidden'));
                } else {
                    row.querySelectorAll('.extra-column').forEach(col => col.classList.add('hidden'));
                }
            });
        });
    </script>


</body>

</html>

