<?php

require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../session/audit_trail.php';

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

        // Convert dates to DateTime objects
        $date1 = new DateTime($rent_from);
        $date2 = new DateTime($rent_until);
        
        // Calculate exact days between dates
        $interval = $date1->diff($date2);
        $totalDays = $interval->days;
        
        // Calculate months more precisely (average month = 365.25/12 days)
        $exactMonths = $totalDays / (365.25/12);
        
        // For display purposes, round to 2 decimal places
        $displayMonths = round($exactMonths, 2);
        
        // Calculate total rent based on exact months
        $total_rent = $exactMonths * $monthly_rate;
        
        // Calculate outstanding balance
        $outstanding_balance = $total_rent - $downpayment_amount;
        
        // Calculate payable months - ensure this is consistent with outstanding balance
        // This will be a whole number of months the tenant needs to pay
        $payable_months = ceil($outstanding_balance / $monthly_rate);
        
        // Recalculate outstanding balance to ensure consistency with payable months
        // This ensures that outstanding_balance = payable_months * monthly_rate
        $outstanding_balance = $payable_months * $monthly_rate;
        
        // Handle receipt file upload
        $downpayment_receipt = null;
        if (isset($_FILES['downpayment_receipt']) && $_FILES['downpayment_receipt']['error'] == 0) {
            $upload_dir = '../uploads/downpayment/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['downpayment_receipt']['name'], PATHINFO_EXTENSION);
            $new_filename = 'receipt_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['downpayment_receipt']['tmp_name'], $upload_path)) {
                $downpayment_receipt = $upload_path;
            }
        }

        if (isset($_POST['tenant_id']) && !empty($_POST['tenant_id'])) {
            $tenant_id = (int)$_POST['tenant_id'];
            
            // Add downpayment_receipt to the query only if a file was uploaded
            if ($downpayment_receipt) {
                $stmt = $conn->prepare(
                    "UPDATE tenants 
                     SET user_id = ?, unit_rented = ?, rent_from = ?, rent_until = ?, monthly_rate = ?, 
                     outstanding_balance = ?, downpayment_amount = ?, payable_months = ?, 
                     downpayment_receipt = ?, updated_at = CURRENT_TIMESTAMP 
                     WHERE tenant_id = ?"
                );
                $stmt->bind_param("isssssdisi", $user_id, $unit_rented, $rent_from, $rent_until, $monthly_rate, 
                              $outstanding_balance, $downpayment_amount, $payable_months, $downpayment_receipt, $tenant_id);
            } else {
                $stmt = $conn->prepare(
                    "UPDATE tenants 
                     SET user_id = ?, unit_rented = ?, rent_from = ?, rent_until = ?, monthly_rate = ?, 
                     outstanding_balance = ?, downpayment_amount = ?, payable_months = ?, updated_at = CURRENT_TIMESTAMP 
                     WHERE tenant_id = ?"
                );
                $stmt->bind_param("isssssdi", $user_id, $unit_rented, $rent_from, $rent_until, $monthly_rate, 
                              $outstanding_balance, $downpayment_amount, $payable_months, $tenant_id);
            }
            $stmt->execute();
        } else {
            // Include downpayment_receipt in the INSERT query
            $stmt = $conn->prepare(
                "INSERT INTO tenants (user_id, unit_rented, rent_from, rent_until, monthly_rate, 
                outstanding_balance, downpayment_amount, payable_months, downpayment_receipt, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            );
            $stmt->bind_param("isssssdis", $user_id, $unit_rented, $rent_from, $rent_until, $monthly_rate, 
                          $outstanding_balance, $downpayment_amount, $payable_months, $downpayment_receipt);
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
            
            // Get tenant and unit details before archiving
            $stmt = $conn->prepare("
                SELECT t.*, u.name as tenant_name, p.unit_no 
                FROM tenants t 
                JOIN users u ON t.user_id = u.user_id 
                JOIN property p ON t.unit_rented = p.unit_id 
                WHERE t.tenant_id = ?
            ");
            $stmt->bind_param("i", $tenant_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $tenant = $result->fetch_assoc();

            // Archive the tenant
            $archiveStmt = $conn->prepare("UPDATE tenants SET status = 'archived' WHERE tenant_id = ?");
            $archiveStmt->bind_param("i", $tenant_id);
            $archiveStmt->execute();

            // Update unit status
            $updateUnitStatus = $conn->prepare("UPDATE property SET status = 'Available' WHERE unit_id = ?");
            $updateUnitStatus->bind_param("i", $tenant['unit_rented']);
            $updateUnitStatus->execute();

            // Log the activity
            logActivity(
                $_SESSION['user_id'],
                'Archived Tenant',
                "Archived tenant {$tenant['tenant_name']} from unit {$tenant['unit_no']}"
            );

            echo json_encode([
                'success' => true,
                'message' => 'Tenant successfully archived.'
            ]);
        } elseif ($_GET['action'] === 'edit' && isset($_GET['id'])) {
            ob_clean();
            header('Content-Type: application/json');
            $tenant_id = (int)$_GET['id'];
            
            try {
                $stmt = $conn->prepare("
                    SELECT t.*, p.unit_no, p.monthly_rent 
                    FROM tenants t
                    LEFT JOIN property p ON t.unit_rented = p.unit_id
                    WHERE t.tenant_id = ?
                ");
                $stmt->bind_param("i", $tenant_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($tenant = $result->fetch_assoc()) {
                    echo json_encode([
                        'success' => true,
                        'tenant_id' => $tenant['tenant_id'],
                        'user_id' => $tenant['user_id'],
                        'unit_rented' => $tenant['unit_rented'],
                        'monthly_rate' => $tenant['monthly_rate'],
                        'unit_no' => $tenant['unit_no']
                    ]);
                } else {
                    throw new Exception('Tenant not found');
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit();
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
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
                <th class="px-4 py-2 text-left border border-gray-300 extra-column">Downpayment Receipt</th>
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
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column">
                    <?php if (!empty($tenant['downpayment_receipt'])): ?>
                        <button type="button" class="text-blue-500 hover:text-blue-700 underline" 
                                onclick="viewReceipt('<?= htmlspecialchars($tenant['downpayment_receipt'], ENT_QUOTES, 'UTF-8') ?>')">
                            View Receipt
                        </button>
                    <?php else: ?>
                        No Receipt
                    <?php endif; ?>
                </td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= isset($tenant['outstanding_balance']) ? '₱' . number_format($tenant['outstanding_balance'], 2) : '₱0.00' ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= $tenant['payable_months'] ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column"><?= $tenant['created_at'] ?></td>
                <td class="hidden px-4 py-2 text-left border border-gray-300 extra-column">
                    <div class="flex gap-2">
                        <button class="text-blue-600 hover:text-blue-900" onclick="editTenant(<?= $tenant['tenant_id'] ?>)" title="Edit contract">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-green-600 hover:text-green-900" onclick="turnoverUnit(<?= $tenant['tenant_id'] ?>)" title="Turn over unit">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900" onclick="archiveTenant(<?= $tenant['tenant_id'] ?>)" title="Archive this tenant">
                            <i class="fas fa-archive"></i>
                        </button>
                    </div>
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
        <form id="tenantForm" method="POST" enctype="multipart/form-data">
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
            
            <div class="mb-4">
                <label for="downpayment_receipt" class="block text-sm font-semibold">Downpayment Receipt</label>
                <input type="file" id="downpayment_receipt" name="downpayment_receipt" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md"
                       accept="image/*">
                <p class="text-xs text-gray-500 mt-1">Upload image of receipt (JPG, PNG)</p>
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
        <form id="existingTenantForm" enctype="multipart/form-data">
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

            <div class="mb-4">
                <label for="existing_downpayment_receipt" class="block text-sm font-semibold">Downpayment Receipt</label>
                <input type="file" id="existing_downpayment_receipt" name="downpayment_receipt" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md"
                       accept="image/*">
                <p class="text-xs text-gray-500 mt-1">Upload image of receipt (JPG, PNG)</p>
            </div>

            <div class="flex justify-end">
                <button type="button" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md mr-2" onclick="closeExistingModal()">Cancel</button>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Add a new modal specifically for contract renewal -->
<div id="renewContractModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full sm:w-96">
        <h2 class="text-xl font-semibold mb-4">Renew Contract</h2>
        <form id="renewContractForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="renewal_tenant_id" name="tenant_id">
            <input type="hidden" id="renewal_unit_id" name="unit_rented">
            <input type="hidden" id="renewal_user_id" name="user_id">
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700">Tenant Name</label>
                <div id="renewal_tenant_name" class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50"></div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold">Unit Being Renewed</label>
                <div id="renewal_unit_no" class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50"></div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold">Monthly Rate</label>
                <input type="text" id="renewal_monthly_rate" name="monthly_rate" readonly 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50">
            </div>

            <div class="mb-4">
                <label for="renewal_rent_from" class="block text-sm font-semibold">Rent From</label>
                <input type="date" id="renewal_rent_from" name="rent_from" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="renewal_rent_until" class="block text-sm font-semibold">Rent Until</label>
                <input type="date" id="renewal_rent_until" name="rent_until" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="renewal_downpayment_amount" class="block text-sm font-semibold">Downpayment Amount</label>
                <input type="number" id="renewal_downpayment_amount" name="downpayment_amount" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md"
                       step="0.01" min="0">
            </div>

            <div class="mb-4">
                <label for="renewal_downpayment_receipt" class="block text-sm font-semibold">Downpayment Receipt</label>
                <input type="file" id="renewal_downpayment_receipt" name="downpayment_receipt" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md"
                       accept="image/*">
                <p class="text-xs text-gray-500 mt-1">Upload image of receipt (JPG, PNG)</p>
            </div>

            <div class="flex justify-end">
                <button type="button" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md mr-2" onclick="closeRenewalModal()">Cancel</button>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md">Renew Contract</button>
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
        
        // Function to close the turnover modal
        function closeTurnoverModal() {
            document.getElementById('turnoverModal').classList.add('hidden');
        }
        
        // Function to navigate between turnover steps
        function goToTurnoverStep(stepNumber) {
            // Hide all steps first
            document.querySelectorAll('.turnover-step').forEach(step => {
                step.classList.add('hidden');
            });
            
            // Show the requested step
            document.getElementById('turnover-step-' + stepNumber).classList.remove('hidden');
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
                    
                    // Add event listener to update monthly rate when unit selection changes
                    unitSelect.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        monthlyRateInput.value = selectedOption.getAttribute('data-rent');
                    });
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
    document.getElementById('tenantForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        const userId = document.getElementById('user_id').value;
        const unitNo = document.getElementById('unit_rented').options[document.getElementById('unit_rented').selectedIndex].text;

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

            // Log the activity separately
            fetch('../session/log_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'Added New Tenant',
                    details: `Added new tenant for ${unitNo}`,
                })
            });

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
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Unknown error');
                    }
                    
                    // Find the tenant name from the table
                    const tenantRows = document.querySelectorAll('#tenantTableBody tr');
                    let tenantName = 'Unknown';
                    
                    tenantRows.forEach(row => {
                        if (row.cells[0].textContent.trim() == data.tenant_id) {
                            tenantName = row.cells[1].textContent.trim();
                        }
                    });
                    
                    // Populate the renewal modal
                    document.getElementById('renewal_tenant_id').value = data.tenant_id;
                    document.getElementById('renewal_user_id').value = data.user_id;
                    document.getElementById('renewal_unit_id').value = data.unit_rented;
                    document.getElementById('renewal_tenant_name').textContent = tenantName;
                    document.getElementById('renewal_unit_no').textContent = `Unit ${data.unit_no}`;
                    document.getElementById('renewal_monthly_rate').value = data.monthly_rate;
                    
                    // Set rent_from to today's date by default for renewal
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('renewal_rent_from').value = today;
                    
                    // Show the renewal modal
                    document.getElementById('renewContractModal').classList.remove('hidden');
                    
                    // Setup the calculation events
                    setupRenewalFormEvents();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error loading tenant details: ' + error.message, 'error');
                });
        }

        // Close renewal modal
        function closeRenewalModal() {
            document.getElementById('renewContractModal').classList.add('hidden');
            document.getElementById('renewContractForm').reset();
        }

        // Add calculation functions for lease calculations
        function calculateLeaseDetails() {
            const rentFrom = document.getElementById('renewal_rent_from').value;
            const rentUntil = document.getElementById('renewal_rent_until').value;
            const monthlyRate = parseFloat(document.getElementById('renewal_monthly_rate').value);
            const downpaymentAmount = parseFloat(document.getElementById('renewal_downpayment_amount').value) || 0;
            
            if (!rentFrom || !rentUntil || isNaN(monthlyRate)) {
                return; // Not enough data to calculate
            }
            
            // Calculate exact days between dates
            const date1 = new Date(rentFrom);
            const date2 = new Date(rentUntil);
            const diffTime = Math.abs(date2 - date1);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            // Calculate months more precisely (average month = 365.25/12 days)
            const exactMonths = diffDays / (365.25/12);
            
            // Calculate total rent based on exact months
            const totalRent = exactMonths * monthlyRate;
            
            // Calculate outstanding balance
            let outstandingBalance = totalRent - downpaymentAmount;
            
            // Calculate payable months - ensure consistency with outstanding balance
            const payableMonths = Math.ceil(outstandingBalance / monthlyRate);
            
            // Recalculate outstanding balance based on payable months
            outstandingBalance = payableMonths * monthlyRate;
            
            // Create hidden fields if they don't exist
            addHiddenFieldIfNeeded('renewal_total_rent', totalRent.toFixed(2));
            addHiddenFieldIfNeeded('renewal_outstanding_balance', outstandingBalance.toFixed(2));
            addHiddenFieldIfNeeded('renewal_payable_months', payableMonths);
            addHiddenFieldIfNeeded('renewal_exact_months', exactMonths.toFixed(2));
            
            // Show calculation summary
            const summaryElement = document.getElementById('renewal_calculation_summary');
            if (summaryElement) {
                summaryElement.innerHTML = `
                    <div class="p-3 bg-blue-50 rounded-md mt-3 mb-2 text-sm">
                        <div class="flex justify-between mb-1">
                            <span>Lease Period:</span>
                            <span>${exactMonths.toFixed(2)} months (${diffDays} days)</span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span>Total Rent:</span>
                            <span>₱${totalRent.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between mb-1 font-semibold">
                            <span>Outstanding Balance:</span>
                            <span>₱${outstandingBalance.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Payable Months:</span>
                            <span>${payableMonths}</span>
                        </div>
                    </div>
                `;
            }
        }

        // Add hidden field to the form if it doesn't exist
        function addHiddenFieldIfNeeded(id, value) {
            const form = document.getElementById('renewContractForm');
            let field = document.getElementById(id);
            
            if (!field) {
                field = document.createElement('input');
                field.type = 'hidden';
                field.id = id;
                field.name = id.replace('renewal_', '');
                form.appendChild(field);
            }
            
            field.value = value;
        }

        // Handle renewal form submission
        document.getElementById('renewContractForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Calculate final values before submission
            calculateLeaseDetails();

            const formData = new FormData(this);
            const unitNo = document.getElementById('renewal_unit_no').textContent;
            const tenantName = document.getElementById('renewal_tenant_name').textContent;

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.headers.get('content-type')?.includes('application/json')) {
                    return response.json();
                } else {
                    // If response is not JSON, treat as success
                    return { success: true, message: 'Contract renewed successfully!' };
                }
            })
            .then(data => {
                // Close modal first
                closeRenewalModal();
                
                // Show success notification
                Toastify({
                    text: "Contract renewed successfully!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "#4CAF50"
                    }
                }).showToast();

                // Log the activity separately
                fetch('../session/log_activity.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'Renewed Tenant Contract',
                        details: `Renewed contract for ${tenantName} on ${unitNo}`,
                    })
                });

                // Reload the page after a delay
                setTimeout(() => {
                    location.reload();
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                Toastify({
                    text: "An error occurred while renewing contract",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "#f44336"
                    }
                }).showToast();
            });
        });

        // Add event listeners to the renewal form fields
        function setupRenewalFormEvents() {
            const dateFields = ['renewal_rent_from', 'renewal_rent_until'];
            const amountField = 'renewal_downpayment_amount';
            
            dateFields.forEach(id => {
                const field = document.getElementById(id);
                if (field) {
                    field.addEventListener('change', calculateLeaseDetails);
                }
            });
            
            const downpaymentField = document.getElementById(amountField);
            if (downpaymentField) {
                downpaymentField.addEventListener('input', calculateLeaseDetails);
                downpaymentField.addEventListener('change', calculateLeaseDetails);
            }
        }

        // Setup the events when the modal is opened
        document.getElementById('renewContractModal').addEventListener('shown.modal', setupRenewalFormEvents);

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
                        
                        // Add event listener to update monthly rate when unit selection changes
                        unitSelect.addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            monthlyRateInput.value = selectedOption.getAttribute('data-rent');
                        });
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

        // Show modal for new tenant
        document.getElementById('newTenant').addEventListener('click', function() {
            document.getElementById('tenantModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'New Tenant';
            document.getElementById('tenantForm').reset();
            document.getElementById('tenant_id').value = '';
            document.getElementById('unit_rented').innerHTML = '<option value="" disabled selected>Select a user first</option>';
        });

        // Close modal function
        function closeModal() {
            document.getElementById('tenantModal').classList.add('hidden');
        }

        // Add event listener for unit selection to update monthly rate
        document.getElementById('unit_rented').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const monthlyRate = selectedOption.getAttribute('data-rent');
            document.getElementById('monthly_rate').value = monthlyRate;
        });
        
    // Add turnover function
    function turnoverUnit(tenantId) {
            
    // Show loading indicator
    const loadingToast = Toastify({
        text: "Loading tenant information...",
        duration: 3000,
        gravity: "top",
        position: "center",
        style: {
            background: "#3498db"
        }
    });
    
    loadingToast.showToast();
    
    // Use cache-busting to prevent issues
    const timestamp = new Date().getTime();
    
    fetch(`turnover_unit.php?action=get_details&tenant_id=${tenantId}&_=${timestamp}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            // Verify the content type is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                throw new Error('Invalid response format: Expected JSON');
            }
        })
        .then(data => {
            // Hide loading toast when data is received
            if (loadingToast) {
                loadingToast.hideToast && loadingToast.hideToast();
            }
            
            if (data.success) {
                // Populate the modal with tenant information
                document.getElementById('turnover_tenant_id').value = tenantId;
                
                // Safely update text content
                const tenantNameEl = document.getElementById('turnover_tenant_name');
                const unitNoEl = document.getElementById('turnover_unit_no');
                const emailField = document.getElementById('turnover_tenant_email');
                
                if (tenantNameEl) tenantNameEl.textContent = data.tenant_name || 'Unknown';
                if (unitNoEl) unitNoEl.textContent = data.unit_no || 'Unknown';
                if (emailField) emailField.value = data.email || '';
                
                // Reset the turnover progress steps
                document.querySelectorAll('.turnover-step').forEach(step => {
                    step.classList.add('hidden');
                });
                
                // Update progress based on turnover status
                if (data.turnover_status) {
                    // Helper function to update progress indicators
                    function updateProgressIndicators(status) {
                        const progressBars = document.querySelectorAll('.progress-bar');
                        const stepIndicators = document.querySelectorAll('.flex.flex-col.items-center');
                        
                        // Reset all progress indicators first
                        progressBars.forEach(bar => {
                            if (bar) bar.style.width = '0%';
                        });
                        
                        stepIndicators.forEach((indicator, index) => {
                            if (index > 0) { // Skip the first one which is always active
                                indicator.firstElementChild.classList.remove('bg-blue-500', 'text-white');
                                indicator.firstElementChild.classList.add('bg-gray-300', 'text-gray-600');
                            }
                        });
                        
                        // Update based on status
                        if (status === 'notified' || status === 'scheduled' || status === 'inspected' || status === 'completed') {
                            if (progressBars[0]) progressBars[0].style.width = '100%';
                            if (stepIndicators[1] && stepIndicators[1].firstElementChild) {
                                stepIndicators[1].firstElementChild.classList.remove('bg-gray-300', 'text-gray-600');
                                stepIndicators[1].firstElementChild.classList.add('bg-blue-500', 'text-white');
                            }
                        }
                        
                        if (status === 'scheduled' || status === 'inspected' || status === 'completed') {
                            if (progressBars[1]) progressBars[1].style.width = '100%';
                            if (stepIndicators[2] && stepIndicators[2].firstElementChild) {
                                stepIndicators[2].firstElementChild.classList.remove('bg-gray-300', 'text-gray-600');
                                stepIndicators[2].firstElementChild.classList.add('bg-blue-500', 'text-white');
                            }
                        }
                        
                        if (status === 'inspected' || status === 'completed') {
                            if (progressBars[2]) progressBars[2].style.width = '100%';
                            if (stepIndicators[3] && stepIndicators[3].firstElementChild) {
                                stepIndicators[3].firstElementChild.classList.remove('bg-gray-300', 'text-gray-600');
                                stepIndicators[3].firstElementChild.classList.add('bg-blue-500', 'text-white');
                            }
                        }
                    }
                    
                    // Update the progress indicators
                    updateProgressIndicators(data.turnover_status);
                    
                    // Show appropriate step based on status
                    showTurnoverStep(data.turnover_status);
                } else {
                    // No status, start at step 1
                    document.getElementById('turnover-step-1').classList.remove('hidden');
                }
                
                // Show the modal
                document.getElementById('turnoverModal').classList.remove('hidden');
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            // Hide loading toast on error
            if (loadingToast) {
                loadingToast.hideToast && loadingToast.hideToast();
            }
            
            console.error('Error:', error);
            showToast('Error retrieving tenant information: ' + error.message, 'error');
        });
}

// Helper function to show the appropriate turnover step based on status
function showTurnoverStep(status) {
    switch (status) {
        case 'completed':
            document.getElementById('turnover-step-4').classList.remove('hidden');
            document.getElementById('turnover-step-4').innerHTML = `
                <div class="text-center py-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-500 mb-4">
                        <i class="fas fa-check-circle text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Turnover Process Complete!</h3>
                    <p class="text-gray-600 mb-6">This unit has been successfully turned over.</p>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4 text-sm text-blue-800">
                        The tenant status has been updated to "turnover" and the property status has been set to "Available".
                    </div>
                    <button type="button" 
                            class="mt-4 px-6 py-3 bg-gray-600 text-white text-lg font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all"
                            onclick="closeTurnoverModal()">
                        Close <i class="fas fa-times ml-2"></i>
                    </button>
                </div>
            `;
            break;
        case 'inspected':
            document.getElementById('turnover-step-3').classList.remove('hidden');
            document.getElementById('turnover-step-3').innerHTML = `
                <div class="text-center py-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-500 mb-4">
                        <i class="fas fa-check text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Inspection Already Completed!</h3>
                    <p class="text-gray-600 mb-6">The inspection results have been recorded successfully.</p>
                    <div class="border-t border-b border-gray-200 py-4 my-4">
                        <p class="text-sm font-medium text-gray-700 mb-4">Continue to the final step in the process</p>
                    </div>
                    <button type="button" 
                            class="mt-2 px-6 py-3 bg-blue-600 text-white text-lg font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all"
                            onclick="goToTurnoverStep(4)">
                        Continue to Completion <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            `;
            break;
        case 'scheduled':
            document.getElementById('turnover-step-2').classList.remove('hidden');
            document.getElementById('turnover-step-2').innerHTML = `
                <div class="text-center py-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-500 mb-4">
                        <i class="fas fa-check text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Inspection Already Scheduled!</h3>
                    <p class="text-gray-600 mb-6">The inspection has been scheduled for this unit.</p>
                    <div class="border-t border-b border-gray-200 py-4 my-4">
                        <p class="text-sm font-medium text-gray-700 mb-4">Continue to the next step in the process</p>
                    </div>
                    <button type="button" 
                            class="mt-2 px-6 py-3 bg-blue-600 text-white text-lg font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all"
                            onclick="goToTurnoverStep(3)">
                        Continue to Inspection <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            `;
            break;
        case 'notified':
            document.getElementById('turnover-step-1').classList.remove('hidden');
            document.getElementById('turnover-step-1').innerHTML = `
                <div class="text-center py-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-500 mb-4">
                        <i class="fas fa-check text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Notification Already Sent!</h3>
                    <p class="text-gray-600 mb-6">The tenant has been previously notified about the turnover process.</p>
                    <p class="text-sm text-gray-500 mb-4">Continue to the next step in the process</p>
                    <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" onclick="goToTurnoverStep(2)">
                        Continue to Scheduling
                    </button>
                </div>
            `;
            break;
        default:
            // If status is pending or unknown, show step 1 normally
            document.getElementById('turnover-step-1').classList.remove('hidden');
    }
}

// Improved scheduleInspection function with better error handling
function scheduleInspection() {
    const tenantId = document.getElementById('turnover_tenant_id').value;
    const inspectionDate = document.getElementById('inspection_date').value;
    const staffAssigned = document.getElementById('staff_assigned').value;
    const inspectionNotes = document.getElementById('inspection_notes').value || '';
    const scheduleButton = document.querySelector('button[onclick="scheduleInspection()"]');
    
    if (!inspectionDate || !staffAssigned) {
        showToast('Please fill in all required fields', 'error');
        return;
    }
    
    // Show spinner and disable button
    scheduleButton.disabled = true;
    const originalBtnText = scheduleButton.innerHTML;
    scheduleButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Scheduling...';
    
    // Add cache-busting parameter and use FormData for safer transmission
    const timestamp = new Date().getTime();
    const formData = new FormData();
    formData.append('action', 'schedule');
    formData.append('tenant_id', tenantId);
    formData.append('inspection_date', inspectionDate);
    formData.append('staff_assigned', staffAssigned);
    formData.append('notes', inspectionNotes);
    
    // Convert FormData to URL-encoded string for the fetch request
    const urlEncodedData = new URLSearchParams();
    for (const [key, value] of formData) {
        urlEncodedData.append(key, value);
    }
    
    fetch(`turnover_unit.php?_=${timestamp}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',  // Request JSON specifically
            'X-Requested-With': 'XMLHttpRequest'  // Mark as AJAX request
        },
        body: urlEncodedData
    })
    .then(response => {
        // Check if response is valid
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        // Verify content type
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error('Invalid response format: Expected JSON but got ' + contentType);
        }
    })
    .then(data => {
        if (data.success) {
            showToast('Inspection scheduled successfully!', 'success');
            
            // Safely update progress indicators
            safelyUpdateProgressIndicator(1, '100%');
            safelyUpdateStepIndicator(2);
            
            // Store the updated status safely
            const tenantIdElement = document.getElementById('turnover_tenant_id');
            if (tenantIdElement) {
                tenantIdElement.setAttribute('data-status', 'scheduled');
            }
            
            // Update step 2 content safely
            const step2 = document.getElementById('turnover-step-2');
            if (step2) {
                step2.innerHTML = `
                    <div class="text-center py-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-500 mb-4">
                            <i class="fas fa-check text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Inspection Scheduled!</h3>
                        <p class="text-gray-600 mb-6">The inspection has been scheduled and the tenant has been notified.</p>
                        <div class="border-t border-b border-gray-200 py-4 my-4">
                            <p class="text-sm font-medium text-gray-700 mb-4">Next Step: Conduct the unit inspection</p>
                        </div>
                        <button type="button" 
                                id="continue-to-inspection"
                                class="mt-2 px-6 py-3 bg-blue-600 text-white text-lg font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                            Continue to Inspection <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                `;
                
                // Add event listener with a slight delay to ensure DOM is updated
                setTimeout(() => {
                    const continueButton = document.getElementById('continue-to-inspection');
                    if (continueButton) {
                        continueButton.addEventListener('click', () => goToTurnoverStep(3));
                    }
                }, 100);
            }
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to schedule inspection: ' + error.message, 'error');
        
        // Reset button state
        if (scheduleButton) {
            scheduleButton.disabled = false;
            scheduleButton.innerHTML = originalBtnText;
        }
    });
}

// Helper function to safely update progress bar
function safelyUpdateProgressIndicator(index, width) {
    const progressBars = document.querySelectorAll('.progress-bar');
    if (progressBars && progressBars[index]) {
        progressBars[index].style.width = width;
    }
}

// Helper function to safely update step indicator
function safelyUpdateStepIndicator(index) {
    const stepIndicators = document.querySelectorAll('.flex.flex-col.items-center');
    if (stepIndicators && stepIndicators[index] && stepIndicators[index].firstElementChild) {
        stepIndicators[index].firstElementChild.classList.remove('bg-gray-300', 'text-gray-600');
        stepIndicators[index].firstElementChild.classList.add('bg-blue-500', 'text-white');
    }
}

// Function to send notification email
function sendTurnoverNotification() {
    const tenantId = document.getElementById('turnover_tenant_id').value;
    const customMessage = document.getElementById('turnover_notification_message').value;
    const notifyButton = document.querySelector('[onclick="sendTurnoverNotification()"]');
    
    // Show spinner and disable button
    notifyButton.disabled = true;
    const originalBtnText = notifyButton.innerHTML;
    notifyButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';
    
    fetch('turnover_unit.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=notify&tenant_id=${tenantId}&message=${encodeURIComponent(customMessage)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Notification sent successfully!', 'success');
            
            // Update progress indicator
            document.querySelectorAll('.progress-bar')[0].style.width = '100%';
            document.querySelectorAll('.flex.flex-col.items-center')[1].firstElementChild.classList.remove('bg-gray-300', 'text-gray-600');
            document.querySelectorAll('.flex.flex-col.items-center')[1].firstElementChild.classList.add('bg-blue-500', 'text-white');
            
            // Show transition message before moving to next step
            const step1 = document.getElementById('turnover-step-1');
            step1.innerHTML = `
                <div class="text-center py-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-500 mb-4">
                        <i class="fas fa-check text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Notification Sent Successfully!</h3>
                    <p class="text-gray-600 mb-6">The tenant has been notified via email about the upcoming turnover process.</p>
                    <p class="text-sm text-gray-500 mb-4">Next: Schedule an inspection for the unit</p>
                    <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" onclick="goToTurnoverStep(2)">
                        Continue to Scheduling
                    </button>
                </div>
            `;
        } else {
            showToast('Error: ' + data.message, 'error');
            // Reset button
            notifyButton.disabled = false;
            notifyButton.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to send notification', 'error');
        // Reset button
        notifyButton.disabled = false;
        notifyButton.innerHTML = originalBtnText;
    });
}

// Similarly update other functions with spinners
function scheduleInspection() {
    const tenantId = document.getElementById('turnover_tenant_id').value;
    const inspectionDate = document.getElementById('inspection_date').value;
    const staffAssigned = document.getElementById('staff_assigned').value;
    const inspectionNotes = document.getElementById('inspection_notes').value || '';
    const scheduleButton = document.querySelector('button[onclick="scheduleInspection()"]');
    
    if (!inspectionDate || !staffAssigned) {
        showToast('Please fill in all required fields', 'error');
        return;
    }
    
    // Show spinner and disable button
    scheduleButton.disabled = true;
    const originalBtnText = scheduleButton.innerHTML;
    scheduleButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Scheduling...';
    
    // Add cache-busting parameter
    const timestamp = new Date().getTime();
    
    // Create form data for better control
    const formData = new FormData();
    formData.append('action', 'schedule');
    formData.append('tenant_id', tenantId);
    formData.append('inspection_date', inspectionDate);
    formData.append('staff_assigned', staffAssigned);
    formData.append('notes', inspectionNotes);
    
    fetch(`turnover_unit.php?_=${timestamp}`, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error('Invalid response format: Expected JSON');
        }
    })
    .then(data => {
        if (data.success) {
            showToast('Inspection scheduled successfully!', 'success');
            
            // Update progress indicator safely
            const progressBars = document.querySelectorAll('.progress-bar');
            if (progressBars && progressBars[1]) {
                progressBars[1].style.width = '100%';
            }
            
            const stepIndicators = document.querySelectorAll('.flex.flex-col.items-center');
            if (stepIndicators && stepIndicators[2] && stepIndicators[2].firstElementChild) {
                stepIndicators[2].firstElementChild.classList.remove('bg-gray-300', 'text-gray-600');
                stepIndicators[2].firstElementChild.classList.add('bg-blue-500', 'text-white');
            }
            
            // Store the updated status safely
            const tenantIdElement = document.getElementById('turnover_tenant_id');
            if (tenantIdElement) {
                tenantIdElement.setAttribute('data-status', 'scheduled');
            }
            
            // Update step 2 content
            const step2 = document.getElementById('turnover-step-2');
            if (step2) {
                step2.innerHTML = `
                    <div class="text-center py-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-500 mb-4">
                            <i class="fas fa-check text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Inspection Scheduled!</h3>
                        <p class="text-gray-600 mb-6">The inspection has been scheduled and the tenant has been notified.</p>
                        <div class="border-t border-b border-gray-200 py-4 my-4">
                            <p class="text-sm font-medium text-gray-700 mb-4">Next Step: Conduct the unit inspection</p>
                        </div>
                        <button type="button" 
                                id="continue-to-inspection"
                                class="mt-2 px-6 py-3 bg-blue-600 text-white text-lg font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                            Continue to Inspection <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                `;
                
                // Add event listener with a slight delay to ensure DOM is updated
                setTimeout(() => {
                    const continueButton = document.getElementById('continue-to-inspection');
                    if (continueButton) {
                        continueButton.addEventListener('click', () => goToTurnoverStep(3));
                    }
                }, 100);
            }
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to schedule inspection: ' + error.message, 'error');
        
        // Reset button state
        if (scheduleButton) {
            scheduleButton.disabled = false;
            scheduleButton.innerHTML = originalBtnText;
        }
    });
}

// Function to submit inspection results
function submitInspection() {
    const tenantId = document.getElementById('turnover_tenant_id').value;
    const formData = new FormData(document.getElementById('inspection_form'));
    const submitButton = document.querySelector('button[onclick="submitInspection()"]');
    
    // Validate form inputs
    const cleanliness = document.querySelector('select[name="cleanliness"]').value;
    const damages = document.querySelector('select[name="damages"]').value;
    const equipment = document.querySelector('select[name="equipment"]').value;
    const report = document.querySelector('textarea[name="inspection_report"]').value;
    
    if (!cleanliness || !damages || !equipment || !report) {
        showToast('Please complete all required fields', 'error');
        return;
    }
    
    // Show spinner and disable button
    submitButton.disabled = true;
    const originalBtnText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';
    
    formData.append('action', 'inspect');
    formData.append('tenant_id', tenantId);
    
    fetch('turnover_unit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Inspection results saved successfully!', 'success');
            
            // Update progress indicator
            document.querySelectorAll('.progress-bar')[2].style.width = '100%';
            document.querySelectorAll('.flex.flex-col.items-center')[3].firstElementChild.classList.remove('bg-gray-300', 'text-gray-600');
            document.querySelectorAll('.flex.flex-col.items-center')[3].firstElementChild.classList.add('bg-blue-500', 'text-white');
            
            // Show transition message before moving to next step
            const step3 = document.getElementById('turnover-step-3');
            step3.innerHTML = `
                <div class="text-center py-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-500 mb-4">
                        <i class="fas fa-check text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Inspection Completed!</h3>
                    <p class="text-gray-600 mb-6">The inspection results have been recorded successfully.</p>
                    <div class="border-t border-b border-gray-200 py-4 my-4">
                        <p class="text-sm font-medium text-gray-700 mb-4">Next Step: Complete the turnover process</p>
                    </div>
                    <button type="button" 
                            class="mt-2 px-6 py-3 bg-blue-600 text-white text-lg font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all"
                            onclick="goToTurnoverStep(4)">
                        Finalize Turnover <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            `;
            
            // Add event listener to ensure button works
            setTimeout(() => {
                const continueButton = step3.querySelector('button');
                if (continueButton) {
                    continueButton.addEventListener('click', () => goToTurnoverStep(4));
                }
            }, 100);
        } else {
            showToast('Error: ' + data.message, 'error');
            // Reset button
            submitButton.disabled = false;
            submitButton.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to save inspection results', 'error');
        // Reset button
        submitButton.disabled = false;
        submitButton.innerHTML = originalBtnText;
    });
}

// Function to complete turnover
function completeTurnover() {
    const tenantId = document.getElementById('turnover_tenant_id').value;
    const keysReturned = document.getElementById('keys_returned').checked;
    const additionalNotes = document.getElementById('completion_notes').value;
    const completeButton = document.querySelector('button[onclick="completeTurnover()"]');
    
    if (!keysReturned) {
        showToast('Keys must be returned to complete turnover', 'error');
        return;
    }
    
    // Show spinner and disable button
    completeButton.disabled = true;
    const originalBtnText = completeButton.innerHTML;
    completeButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Completing...';
    
    fetch('turnover_unit.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=complete&tenant_id=${tenantId}&notes=${encodeURIComponent(additionalNotes)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Turnover completed successfully!', 'success');
            
            // Show completion message
            const step4 = document.getElementById('turnover-step-4');
            step4.innerHTML = `
                <div class="text-center py-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-500 mb-4">
                        <i class="fas fa-check-circle text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Turnover Process Complete!</h3>
                    <p class="text-gray-600 mb-6">The unit has been successfully turned over and is now available for new tenants.</p>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-4 text-sm text-blue-800">
                        The tenant status has been updated to "turnover" and the property status has been set to "Available".
                    </div>
                    <button type="button" 
                            class="mt-4 px-6 py-3 bg-gray-600 text-white text-lg font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all"
                            onclick="closeTurnoverModal()">
                        Close <i class="fas fa-times ml-2"></i>
                    </button>
                </div>
            `;
            
            // Reload page after a delay to update tenant status
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            showToast('Error: ' + data.message, 'error');
            // Reset button
            completeButton.disabled = false;
            completeButton.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to complete turnover', 'error');
        // Reset button
        completeButton.disabled = false;
        completeButton.innerHTML = originalBtnText;
    });
}
    </script>

<!-- Add Turnover Process Modal -->
<div id="turnoverModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6 border-b pb-3">
            <h2 class="text-2xl font-semibold text-gray-800">Unit Turnover Process</h2>
            <button onclick="closeTurnoverModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <input type="hidden" id="turnover_tenant_id">
        
        <!-- Tenant Info Section -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-2">Tenant Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Tenant Name:</p>
                    <p id="turnover_tenant_name" class="font-semibold"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Unit:</p>
                    <p id="turnover_unit_no" class="font-semibold"></p>
                </div>
            </div>
        </div>
        
        <!-- Progress Indicator -->
        <div class="flex justify-between mb-8">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center mb-1">1</div>
                <span class="text-xs text-center">Notification</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 self-center relative overflow-hidden">
                <div class="absolute top-0 left-0 h-full bg-blue-500 progress-bar" style="width: 0%"></div>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center mb-1">2</div>
                <span class="text-xs text-center">Schedule</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 self-center relative overflow-hidden">
                <div class="absolute top-0 left-0 h-full bg-blue-500 progress-bar" style="width: 0%"></div>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center mb-1">3</div>
                <span class="text-xs text-center">Inspection</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 self-center relative overflow-hidden">
                <div class="absolute top-0 left-0 h-full bg-blue-500 progress-bar" style="width: 0%"></div>
            </div>
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center mb-1">4</div>
                <span class="text-xs text-center">Complete</span>
            </div>
        </div>
        
        <!-- Step 1: Notify Tenant -->
        <div id="turnover-step-1" class="turnover-step">
            <h3 class="text-xl font-semibold mb-4">Step 1: Notify Tenant</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tenant Email</label>
                <input type="email" id="turnover_tenant_email" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notification Message</label>
                <textarea id="turnover_notification_message" rows="5" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">Dear tenant,

We would like to inform you of your upcoming move-out and need to schedule a unit inspection. Please prepare the unit according to our turnover guidelines.

Thank you,
Building Management</textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" onclick="closeTurnoverModal()">Cancel</button>
                <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700" onclick="sendTurnoverNotification()">Send Notification</button>
            </div>
        </div>
        
        <!-- Step 2: Schedule Inspection -->
        <div id="turnover-step-2" class="turnover-step hidden">
            <h3 class="text-xl font-semibold mb-4">Step 2: Schedule Inspection</h3>
            <div class="mb-4">
                <label for="inspection_date" class="block text-sm font-medium text-gray-700 mb-1">Inspection Date</label>
                <input type="datetime-local" id="inspection_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="staff_assigned" class="block text-sm font-medium text-gray-700 mb-1">Staff Assigned</label>
                <select id="staff_assigned" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select staff member</option>
                    <?php
                    // Query to get staff members from staff table
                    $staffQuery = "SELECT staff_id, name FROM staff ORDER BY name";
                    $staffResult = $conn->query($staffQuery);
                    while ($staff = $staffResult->fetch_assoc()) {
                        echo "<option value=\"{$staff['staff_id']}\">{$staff['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-6">
                <label for="inspection_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                <textarea id="inspection_notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" onclick="goToTurnoverStep(1)">Back</button>
                <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700" onclick="scheduleInspection()">Schedule Inspection</button>
            </div>
        </div>
        
        <!-- Step 3: Conduct Inspection -->
        <div id="turnover-step-3" class="turnover-step hidden">
            <h3 class="text-xl font-semibold mb-4">Step 3: Conduct Inspection</h3>
            <form id="inspection_form">
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cleanliness</label>
                        <select name="cleanliness" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select rating</option>
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Damages</label>
                        <select name="damages" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select rating</option>
                            <option value="none">None</option>
                            <option value="minor">Minor</option>
                            <option value="moderate">Moderate</option>
                            <option value="major">Major</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equipment Condition</label>
                        <select name="equipment" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select rating</option>
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Inspection Photos (Max 5)</label>
                        <input type="file" name="inspection_photos[]" accept="image/*" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white" onchange="previewInspectionPhotos(this)">
                        <p class="text-xs text-gray-500 mt-1">Please upload photos of any damages or issues</p>
                        <div id="photo-previews" class="grid grid-cols-3 gap-2 mt-2"></div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Inspection Notes</label>
                        <textarea name="inspection_report" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                </div>
            </form>
            <div class="flex justify-between">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" onclick="goToTurnoverStep(2)">Back</button>
                <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700" onclick="submitInspection()">Submit Inspection</button>
            </div>
        </div>
        
        <!-- Step 4: Complete Turnover -->
        <div id="turnover-step-4" class="turnover-step hidden">
            <h3 class="text-xl font-semibold mb-4">Step 4: Complete Turnover</h3>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="keys_returned" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="ml-2">Keys have been returned</span>
                </label>
            </div>
            <div class="mb-6">
                <label for="completion_notes" class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                <textarea id="completion_notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" onclick="goToTurnoverStep(3)">Back</button>
                <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700" onclick="completeTurnover()">Complete Turnover</button>
            </div>
        </div>
    </div>
</div>

<script>
// Photo preview function
function previewInspectionPhotos(input) {
    const previewsContainer = document.getElementById('photo-previews');
    previewsContainer.innerHTML = '';
    
    if (input.files) {
        const maxFiles = 5;
        const filesToShow = Math.min(input.files.length, maxFiles);
        
        for (let i = 0; i < filesToShow; i++) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'relative';
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Photo ${i+1}" class="w-full h-24 object-cover rounded-lg">
                    <span class="absolute top-0 right-0 bg-gray-800 text-white text-xs rounded-bl-lg px-1">${i+1}</span>
                `;
                previewsContainer.appendChild(preview);
            }
            
            reader.readAsDataURL(input.files[i]);
        }
        
        if (input.files.length > maxFiles) {
            const note = document.createElement('div');
            note.className = 'text-xs text-gray-500 col-span-3 mt-1';
            note.textContent = `+${input.files.length - maxFiles} more files selected`;
            previewsContainer.appendChild(note);
        }
    }
}
</script>

<!-- Add a receipt viewer modal -->
<div id="receiptModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-5 rounded-lg shadow-lg w-full max-w-xl max-h-screen overflow-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">Downpayment Receipt</h3>
            <button onclick="closeReceiptModal()" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex justify-center">
            <img id="receiptImage" src="" alt="Downpayment Receipt" class="max-w-full max-h-[70vh] object-contain">
        </div>
        <div class="mt-4 text-center">
            <a id="downloadReceiptLink" href="#" download class="px-4 py-2 bg-blue-600 text-white rounded-lg">Download Receipt</a>
        </div>
    </div>
</div>

<script>
    // Function to view receipt
    function viewReceipt(receiptPath) {
        const modal = document.getElementById('receiptModal');
        const receiptImage = document.getElementById('receiptImage');
        const downloadLink = document.getElementById('downloadReceiptLink');
        
        // Set the image source
        receiptImage.src = receiptPath;
        
        // Set the download link
        downloadLink.href = receiptPath;
        
        // Get just the filename for the download attribute
        const filename = receiptPath.substring(receiptPath.lastIndexOf('/') + 1);
        downloadLink.setAttribute('download', filename);
        
        // Show the modal
        modal.classList.remove('hidden');
        
        // Add event listener for image load error
        receiptImage.onerror = function() {
            receiptImage.src = '../images/image-not-found.png'; // Replace with a default image path
            showToast('Receipt image could not be loaded', 'error');
        };
    }
    
    // Function to close the receipt modal
    function closeReceiptModal() {
        document.getElementById('receiptModal').classList.add('hidden');
    }
    
    // Close the receipt modal if the user clicks outside of it
    document.getElementById('receiptModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeReceiptModal();
        }
    });
</script>

</body>

</html>

