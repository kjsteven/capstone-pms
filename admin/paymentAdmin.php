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

// Get total number of payments
$totalQuery = "SELECT COUNT(*) as total FROM payments";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $entriesPerPage);

// Get payments data with pagination using prepared statement for safety
$query = "SELECT p.*, t.user_id, u.name as tenant_name, pr.unit_no 
          FROM payments p
          JOIN tenants t ON p.tenant_id = t.tenant_id
          JOIN users u ON t.user_id = u.user_id
          JOIN property pr ON t.unit_rented = pr.unit_id
          ORDER BY p.payment_date DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $entriesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$payments = [];
if ($result) {
    $payments = $result->fetch_all(MYSQLI_ASSOC);
}

// Get active tenants for manual payment entry
$tenantsQuery = "SELECT t.tenant_id, u.name as tenant_name, pr.unit_no, t.outstanding_balance
                FROM tenants t
                JOIN users u ON t.user_id = u.user_id
                JOIN property pr ON t.unit_rented = pr.unit_id
                WHERE t.status = 'active'
                ORDER BY u.name";
$tenantsResult = $conn->query($tenantsQuery);
$tenants = [];
if ($tenantsResult) {
    $tenants = $tenantsResult->fetch_all(MYSQLI_ASSOC);
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <title>Payment Management</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .custom-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .payment-status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }
        .status-received {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .status-rejected {
            background-color: #FEE2E2;
            color: #B91C1C;
        }
        /* Loader */
        .loader {
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid #3498db;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Animation for payment actions */
        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }
        .fade-out {
            animation: fadeOut 0.5s forwards;
        }
        /* Hover effects for action buttons */
        .action-button {
            transition: all 0.2s;
        }
        .action-button:hover {
            transform: translateY(-2px);
        }
        /* Center search icon */
        .search-icon {
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- Include Navbar -->
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>

<!-- Main Content -->
<div class="sm:ml-64 p-4 md:p-8 mt-16 sm:mt-20 mx-auto">
    <div class="container mx-auto max-w-7xl">
        
        <div class="mb-6 flex flex-col lg:flex-row justify-between items-start gap-4">
            <h1 class="text-xl md:text-2xl font-semibold text-gray-800">Payment Management</h1>
            <div class="flex flex-col sm:flex-row gap-3">
                <button id="manualPaymentBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Record Manual Payment
                </button>
                <button id="exportReportBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center">
                    <i class="fas fa-file-export mr-2"></i> Export Report
                </button>
            </div>
        </div>

        <!-- Filters and Search Section -->
        <div class="mb-6 bg-white rounded-lg p-4 custom-shadow">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                    <select id="status-filter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Received">Received</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                
                <!-- Date Range Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <input type="text" id="date-range" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Select date range">
                </div>
                
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" id="search-input" class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by tenant or reference...">
                        <div class="absolute left-3 search-icon text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-lg overflow-hidden custom-shadow mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference #</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="payments-table-body">
                        <?php if (empty($payments)) : ?>
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-gray-500">No payments found</td>
                        </tr>
                        <?php else : ?>
                            <?php foreach ($payments as $payment) : ?>
                            <tr id="payment-row-<?= $payment['payment_id'] ?>" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">PAY-<?= str_pad($payment['payment_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($payment['tenant_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($payment['unit_no']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₱<?= number_format($payment['amount'], 2) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php if (!empty($payment['gcash_number'])) : ?>
                                        <span class="inline-flex items-center">
                                            <img src="../images/gcash.png" alt="GCash" class="w-4 h-4 mr-1">
                                            GCash
                                        </span>
                                    <?php else : ?>
                                        <span class="inline-flex items-center">
                                            <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                                            Cash
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= !empty($payment['reference_number']) ? htmlspecialchars($payment['reference_number']) : 'N/A' ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="payment-status-badge <?= strtolower($payment['status']) === 'pending' ? 'status-pending' : (strtolower($payment['status']) === 'received' ? 'status-received' : 'status-rejected') ?>">
                                        <?= $payment['status'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button class="text-blue-600 hover:text-blue-900 action-button" onclick="viewPayment(<?= $payment['payment_id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($payment['status'] === 'Pending') : ?>
                                            <button class="text-green-600 hover:text-green-900 action-button" onclick="approvePayment(<?= $payment['payment_id'] ?>, <?= $payment['tenant_id'] ?>, <?= $payment['amount'] ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900 action-button" onclick="rejectPayment(<?= $payment['payment_id'] ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <a href="?page=<?= max(1, $page - 1) ?>&entries=<?= $entriesPerPage ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                    <a href="?page=<?= min($totalPages, $page + 1) ?>&entries=<?= $entriesPerPage ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium"><?= $offset + 1 ?></span> to <span class="font-medium"><?= min($offset + $entriesPerPage, $totalRows) ?></span> of <span class="font-medium"><?= $totalRows ?></span> results
                        </p>
                    </div>
                    <?php if($totalPages > 1): ?>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <!-- Previous Page -->
                            <a href="?page=<?= max(1, $page - 1) ?>&entries=<?= $entriesPerPage ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            
                            <!-- Page Numbers -->
                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if($i == 1 || $i == $totalPages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                                    <a href="?page=<?= $i ?>&entries=<?= $entriesPerPage ?>" 
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?= $i == $page ? 'text-blue-600 bg-blue-50 border-blue-500' : 'text-gray-700 hover:bg-gray-50' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php elseif(($i == 2 && $page > 3) || ($i == $totalPages - 1 && $page < $totalPages - 2)): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                        ...
                                    </span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <!-- Next Page -->
                            <a href="?page=<?= min($totalPages, $page + 1) ?>&entries=<?= $entriesPerPage ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Entries per page selector -->
        <div class="mt-4 flex items-center justify-end">
            <div class="text-sm text-gray-700 mr-3">Show entries:</div>
            <select id="entries-select" onchange="changeEntriesPerPage(this)" class="border border-gray-300 rounded-md text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="10" <?= $entriesPerPage == 10 ? 'selected' : '' ?>>10</option>
                <option value="25" <?= $entriesPerPage == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= $entriesPerPage == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= $entriesPerPage == 100 ? 'selected' : '' ?>>100</option>
            </select>
        </div>
    </div>
</div>

<!-- Manual Payment Modal -->
<div id="manualPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Record Manual Payment</h3>
            <button class="text-gray-400 hover:text-gray-500" onclick="toggleManualModal(false)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <form id="manualPaymentForm" class="space-y-5">
                <div>
                    <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-1">Tenant</label>
                    <select id="tenant_id" name="tenant_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="" disabled selected>Select a tenant</option>
                        <?php foreach ($tenants as $tenant): ?>
                            <option value="<?= $tenant['tenant_id'] ?>" data-balance="<?= $tenant['outstanding_balance'] ?>">
                                <?= htmlspecialchars($tenant['tenant_name']) ?> (Unit <?= htmlspecialchars($tenant['unit_no']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (₱)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">₱</div>
                            <input type="number" id="amount" name="amount" step="0.01" min="1" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
                        <input type="date" id="payment_date" name="payment_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" id="method_cash" name="payment_method" value="cash" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="method_cash" class="ml-2 block text-sm text-gray-700">Cash</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="method_gcash" name="payment_method" value="gcash" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="method_gcash" class="ml-2 block text-sm text-gray-700">GCash</label>
                        </div>
                    </div>
                </div>
                
                <!-- GCash specific fields (initially hidden) -->
                <div id="gcash-fields" class="space-y-4 hidden">
                    <div>
                        <label for="gcash_number" class="block text-sm font-medium text-gray-700 mb-1">GCash Number</label>
                        <input type="text" id="gcash_number" name="gcash_number" placeholder="e.g. 09123456789" pattern="^09\d{9}$" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Philippine mobile number format (09xxxxxxxxx)</p>
                    </div>
                    
                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number" placeholder="GCash reference number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="receipt_image" class="block text-sm font-medium text-gray-700 mb-1">Receipt Image (Optional)</label>
                        <input type="file" id="receipt_image" name="receipt_image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Upload screenshot of GCash receipt</p>
                    </div>
                </div>
                
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add any notes about this payment"></textarea>
                </div>

                <div id="balance-info" class="bg-blue-50 p-3 rounded-lg hidden">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Current outstanding balance: <span id="current-balance" class="font-medium">₱0.00</span>
                    </p>
                    <p class="text-sm text-blue-800 mt-1">
                        Balance after payment: <span id="after-payment-balance" class="font-medium">₱0.00</span>
                    </p>
                </div>
                
                <div class="flex justify-end pt-4">
                    <button type="button" class="mr-3 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" onclick="toggleManualModal(false)">
                        Cancel
                    </button>
                    <button type="submit" id="save-payment-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Payment Modal -->
<div id="viewPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
        <div class="flex justify-between items-center p-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Payment Details</h3>
            <button class="text-gray-400 hover:text-gray-500" onclick="toggleViewModal(false)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div id="payment-details" class="space-y-4">
                <!-- Payment details will be loaded here -->
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-1/4 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-2/3 mb-2"></div>
                </div>
            </div>
            
            <div id="receipt-container" class="mt-6 border-t border-gray-200 pt-4 hidden">
                <h4 class="font-medium text-gray-700 mb-2">Receipt</h4>
                <div class="flex justify-center">
                    <img id="receipt-image" src="" alt="Payment Receipt" class="max-h-64 object-contain border border-gray-300 rounded">
                </div>
                <div class="flex justify-center mt-2">
                    <a id="download-receipt" href="#" download class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                        <i class="fas fa-download mr-1"></i> Download Receipt
                    </a>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-6">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" onclick="toggleViewModal(false)">
                    Close
                </button>
                <button id="print-details-btn" type="button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Export Report Modal -->
<div id="exportReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center p-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Export Payment Report</h3>
            <button class="text-gray-400 hover:text-gray-500" onclick="toggleExportModal(false)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <form id="exportForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                    <select id="report_type" name="report_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="all">All Payments</option>
                        <option value="received">Received Payments</option>
                        <option value="pending">Pending Payments</option>
                        <option value="rejected">Rejected Payments</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" id="from_date" name="from_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" id="to_date" name="to_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Format</label>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex items-center">
                            <input type="radio" id="format_csv" name="format" value="csv" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="format_csv" class="ml-2 block text-sm text-gray-700">CSV (Excel Compatible)</label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end pt-4">
                    <button type="button" class="mr-3 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" onclick="toggleExportModal(false)">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-download mr-2"></i>Export Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toastify JS -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date picker for date range
        flatpickr("#date-range", {
            mode: "range",
            dateFormat: "Y-m-d",
            placeholder: "Select date range"
        });

        // Set today's date as the default payment date
        document.getElementById('payment_date').valueAsDate = new Date();
        
        // Set default dates for export form
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        document.getElementById('to_date').valueAsDate = today;
        document.getElementById('from_date').valueAsDate = thirtyDaysAgo;
        
        // Show manual payment modal when button is clicked
        document.getElementById('manualPaymentBtn').addEventListener('click', function() {
            toggleManualModal(true);
        });
        
        // Show export report modal when button is clicked
        document.getElementById('exportReportBtn').addEventListener('click', function() {
            toggleExportModal(true);
        });
        
        // Toggle GCash fields visibility based on payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const gcashFields = document.getElementById('gcash-fields');
                if (this.value === 'gcash') {
                    gcashFields.classList.remove('hidden');
                } else {
                    gcashFields.classList.add('hidden');
                }
            });
        });
        
        // Show balance info when tenant is selected
        document.getElementById('tenant_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                const balance = parseFloat(selectedOption.getAttribute('data-balance')) || 0;
                
                document.getElementById('current-balance').textContent = '₱' + balance.toFixed(2);
                document.getElementById('balance-info').classList.remove('hidden');
                
                // Update "after payment" balance when amount changes
                document.getElementById('amount').addEventListener('input', function() {
                    const amount = parseFloat(this.value) || 0;
                    const newBalance = Math.max(0, balance - amount);
                    document.getElementById('after-payment-balance').textContent = '₱' + newBalance.toFixed(2);
                });
                
                // Trigger input event to calculate initial after-payment balance
                const event = new Event('input');
                document.getElementById('amount').dispatchEvent(event);
            }
        });
        
        // Handle manual payment form submission
        document.getElementById('manualPaymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitManualPayment();
        });
        
        // Handle export form submission
        document.getElementById('exportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            exportReport();
        });
        
        // Apply filters when status or search input changes
        document.getElementById('status-filter').addEventListener('change', applyFilters);
        document.getElementById('search-input').addEventListener('input', applyFilters);
        document.getElementById('date-range').addEventListener('change', applyFilters);
        
        // Print details button
        document.getElementById('print-details-btn').addEventListener('click', printPaymentDetails);
    });

    // Toggle manual payment modal visibility
    function toggleManualModal(show) {
        const modal = document.getElementById('manualPaymentModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    
    // Toggle view payment modal visibility
    function toggleViewModal(show) {
        const modal = document.getElementById('viewPaymentModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    
    // Toggle export report modal visibility
    function toggleExportModal(show) {
        const modal = document.getElementById('exportReportModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    
    // Submit manual payment form with AJAX
    function submitManualPayment() {
        // Collect form data
        const formData = new FormData(document.getElementById('manualPaymentForm'));
        
        // Show loading state
        document.getElementById('save-payment-btn').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
        document.getElementById('save-payment-btn').disabled = true;
        
        // Send AJAX request
        fetch('process_manual_payment.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success toast
                showToast(data.message, 'success');
                
                // Close modal and reset form
                toggleManualModal(false);
                document.getElementById('manualPaymentForm').reset();
                
                // Reload the page after a delay to show the new payment
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message, 'error');
            
            // Reset button state
            document.getElementById('save-payment-btn').innerHTML = 'Save Payment';
            document.getElementById('save-payment-btn').disabled = false;
        });
    }
    
    // View payment details
    function viewPayment(paymentId) {
        // Show loading state
        toggleViewModal(true);
        document.getElementById('payment-details').innerHTML = `
            <div class="animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-1/4 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-2/3 mb-2"></div>
            </div>
        `;
        
        // Hide receipt container until loaded
        document.getElementById('receipt-container').classList.add('hidden');
        
        // Fetch payment details
        fetch(`get_payment_details.php?id=${paymentId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text().then(text => {
                    try {
                        // Try to parse as JSON
                        return JSON.parse(text);
                    } catch (e) {
                        // If parsing fails, log the actual response for debugging
                        console.error('Server returned invalid JSON:', text);
                        throw new Error('Invalid server response format');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    const payment = data.payment;
                    const statusClass = payment.status === 'Pending' ? 'status-pending' : 
                                       (payment.status === 'Received' ? 'status-received' : 'status-rejected');
                    
                    // Format payment details HTML - Modified to show who processed the payment
                    document.getElementById('payment-details').innerHTML = `
                        <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                            <div class="text-sm text-gray-600">Payment ID:</div>
                            <div class="text-sm font-medium">PAY-${String(payment.payment_id).padStart(5, '0')}</div>
                            
                            <div class="text-sm text-gray-600">Tenant:</div>
                            <div class="text-sm font-medium">${payment.tenant_name}</div>
                            
                            <div class="text-sm text-gray-600">Unit:</div>
                            <div class="text-sm font-medium">${payment.unit_no}</div>
                            
                            <div class="text-sm text-gray-600">Amount:</div>
                            <div class="text-sm font-medium">₱${parseFloat(payment.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                            
                            <div class="text-sm text-gray-600">Payment Method:</div>
                            <div class="text-sm font-medium">
                                ${payment.gcash_number ? 
                                    `<span class="inline-flex items-center">
                                        <img src="../images/gcash.png" alt="GCash" class="w-4 h-4 mr-1">
                                        GCash (${payment.gcash_number})
                                    </span>` : 
                                    `<span class="inline-flex items-center">
                                        <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                                        Cash
                                    </span>`
                                }
                            </div>
                            
                            ${payment.reference_number ? `
                                <div class="text-sm text-gray-600">Reference #:</div>
                                <div class="text-sm font-medium">${payment.reference_number}</div>
                            ` : ''}
                            
                            <div class="text-sm text-gray-600">Payment Date:</div>
                            <div class="text-sm font-medium">${new Date(payment.payment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                            
                            <div class="text-sm text-gray-600">Status:</div>
                            <div class="text-sm font-medium">
                                <span class="payment-status-badge ${statusClass}">
                                    ${payment.status}
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-600">Last Updated:</div>
                            <div class="text-sm font-medium">${new Date(payment.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                            
                            <div class="text-sm text-gray-600">Processed By:</div>
                            <div class="text-sm font-medium">${payment.processed_by_name || 'System'}</div>
                            
                            ${payment.notes ? `
                                <div class="col-span-2 mt-2">
                                    <div class="text-sm text-gray-600 mb-1">Notes:</div>
                                    <div class="text-sm bg-gray-50 p-2 rounded">${payment.notes}</div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                    
                    // Show receipt if available
                    if (payment.receipt_image) {
                        document.getElementById('receipt-image').src = '../' + payment.receipt_image;
                        document.getElementById('download-receipt').href = '../' + payment.receipt_image;
                        document.getElementById('receipt-container').classList.remove('hidden');
                    }
                } else {
                    document.getElementById('payment-details').innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle text-red-500 text-3xl mb-2"></i>
                            <p class="text-gray-700">Error loading payment details.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('payment-details').innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-circle text-red-500 text-3xl mb-2"></i>
                        <p class="text-gray-700">Error loading payment details.</p>
                        <p class="text-sm text-gray-500">${error.message}</p>
                    </div>
                `;
            });
    }
    
    // Approve pending payment
    function approvePayment(paymentId, tenantId, amount) {
        if (confirm('Are you sure you want to approve this payment?')) {
            // Show loading indicator on the button
            const approveButton = document.querySelector(`#payment-row-${paymentId} .text-green-600`);
            const originalHTML = approveButton.innerHTML;
            approveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            approveButton.disabled = true;
            
            fetch('process_payment_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=approve&payment_id=${paymentId}&tenant_id=${tenantId}&amount=${amount}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success toast
                    showToast(data.message, 'success');
                    
                    // Update the payment row
                    const row = document.getElementById(`payment-row-${paymentId}`);
                    row.classList.add('fade-out');
                    
                    // Reload the page after animation completes
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    throw new Error(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(error.message, 'error');
                
                // Reset button state
                approveButton.innerHTML = originalHTML;
                approveButton.disabled = false;
            });
        }
    }
    
    // Reject pending payment
    function rejectPayment(paymentId) {
        if (confirm('Are you sure you want to reject this payment?')) {
            // Show loading indicator on the button
            const rejectButton = document.querySelector(`#payment-row-${paymentId} .text-red-600`);
            const originalHTML = rejectButton.innerHTML;
            rejectButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            rejectButton.disabled = true;
            
            fetch('process_payment_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=reject&payment_id=${paymentId}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success toast
                    showToast(data.message, 'success');
                    
                    // Update the payment row
                    const row = document.getElementById(`payment-row-${paymentId}`);
                    row.classList.add('fade-out');
                    
                    // Reload the page after animation completes
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    throw new Error(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(error.message, 'error');
                
                // Reset button state
                rejectButton.innerHTML = originalHTML;
                rejectButton.disabled = false;
            });
        }
    }
    
    // Export payment report
    function exportReport() {
        const formData = new FormData(document.getElementById('exportForm'));
        const queryString = new URLSearchParams(formData).toString();
        
        // Redirect to export script with parameters
        window.open(`export_payment_report.php?${queryString}`, '_blank');
        
        // Close modal
        toggleExportModal(false);
    }
    
    // Apply filters to payments table
    function applyFilters() {
        const statusFilter = document.getElementById('status-filter').value.toLowerCase();
        const searchInput = document.getElementById('search-input').value.toLowerCase();
        const dateRange = document.getElementById('date-range').value;
        
        let startDate, endDate;
        if (dateRange) {
            const dates = dateRange.split(' to ');
            startDate = dates[0] ? new Date(dates[0]) : null;
            endDate = dates[1] ? new Date(dates[1]) : startDate;
            
            // Set end date time to end of day for inclusive filtering
            if (endDate) {
                endDate.setHours(23, 59, 59, 999);
            }
        }
        
        const rows = document.querySelectorAll('#payments-table-body tr');
        
        rows.forEach(row => {
            if (row.cells.length <= 1) return; // Skip "No payments found" row
            
            const tenant = row.cells[1].textContent.toLowerCase();
            const unit = row.cells[2].textContent.toLowerCase();
            const method = row.cells[4].textContent.toLowerCase();
            const reference = row.cells[5].textContent.toLowerCase();
            const dateText = row.cells[6].textContent;
            const paymentDate = new Date(dateText);
            const status = row.cells[7].textContent.trim().toLowerCase();
            
            // Check if the row matches all active filters
            const matchesStatus = !statusFilter || status === statusFilter;
            const matchesSearch = !searchInput || 
                tenant.includes(searchInput) || 
                unit.includes(searchInput) || 
                reference.includes(searchInput);
            const matchesDate = !startDate || !endDate || 
                (paymentDate >= startDate && paymentDate <= endDate);
            
            // Show or hide the row based on filters
            if (matchesStatus && matchesSearch && matchesDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Print payment details
    function printPaymentDetails() {
        const content = document.getElementById('payment-details').innerHTML;
        const receiptImage = document.getElementById('receipt-container').classList.contains('hidden') ? '' :
            `<div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 20px;">
                <h4 style="margin-bottom: 10px; font-weight: bold;">Receipt</h4>
                <div style="text-align: center;">
                    <img src="${document.getElementById('receipt-image').src}" style="max-height: 300px;">
                </div>
            </div>`;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Payment Details</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 20px;
                    }
                    .payment-info {
                        margin-bottom: 20px;
                    }
                    .payment-status-badge {
                        padding: 3px 10px;
                        border-radius: 20px;
                        font-size: 12px;
                        font-weight: bold;
                        display: inline-block;
                    }
                    .status-pending {
                        background-color: #FEF3C7;
                        color: #92400E;
                    }
                    .status-received {
                        background-color: #D1FAE5;
                        color: #065F46;
                    }
                    .status-rejected {
                        background-color: #FEE2E2;
                        color: #B91C1C;
                    }
                    h1 {
                        font-size: 18px;
                        border-bottom: 1px solid #ddd;
                        padding-bottom: 10px;
                        margin-bottom: 20px;
                    }
                    .logo {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .print-date {
                        font-size: 12px;
                        color: #666;
                        text-align: right;
                        margin-top: 30px;
                    }
                </style>
            </head>
            <body>
                <div class="logo">
                    <img src="../images/logo.png" alt="Logo" height="80">
                    <h1>Payment Details</h1>
                </div>
                <div class="payment-info">${content}</div>
                ${receiptImage}
                <div class="print-date">Printed on ${new Date().toLocaleString()}</div>
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                            window.close();
                        }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
    }
    
    // Show toast notification
    function showToast(message, type = 'success') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: type === 'success' ? "#4CAF50" : "#F44336",
            stopOnFocus: true
        }).showToast();
    }
    
    // Handle entries per page change
    function changeEntriesPerPage(select) {
        window.location.href = `?page=1&entries=${select.value}`;
    }
</script>

</body>
</html>
