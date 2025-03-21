<?php
// Start session at the very beginning, before any output
session_start();

require_once '../session/db.php';
require_once '../session/audit_trail.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../authentication/login.php');
    exit();
}

// Function to check and update overdue invoices
function updateOverdueInvoices($conn) {
    // Get today's date
    $today = date('Y-m-d');
    
    // Prepare and execute query to update status of overdue invoices
    $updateQuery = "UPDATE invoices SET status = 'overdue' 
                   WHERE status = 'unpaid' AND due_date < ? AND status != 'paid'";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    
    // Return number of updated rows
    $updatedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $updatedRows;
}

// Call the function to update overdue invoices on page load
updateOverdueInvoices($conn);

// Pagination settings with validation
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
// Ensure entries per page is a positive number
if ($entriesPerPage <= 0) {
    $entriesPerPage = 10; // Default to 10 if invalid
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// Ensure page is at least 1
if ($page <= 0) {
    $page = 1;
}

// Calculate offset - ensure it's never negative
$offset = ($page - 1) * $entriesPerPage;
if ($offset < 0) {
    $offset = 0;
}

// Get total number of invoices
$totalQuery = "SELECT COUNT(*) as total FROM invoices";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalRows / $entriesPerPage)); // Ensure at least 1 page

// Adjust page if it's beyond the maximum pages
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $entriesPerPage;
    if ($offset < 0) {
        $offset = 0;
    }
}

// Get today's date for default export date range
$todayDate = date('Y-m-d');
$thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

// Get invoice data with pagination using prepared statement for safety
$query = "SELECT i.*, t.tenant_id, u.name as tenant_name, p.unit_no 
          FROM invoices i
          JOIN tenants t ON i.tenant_id = t.tenant_id
          JOIN users u ON t.user_id = u.user_id
          JOIN property p ON t.unit_rented = p.unit_id
          ORDER BY i.created_at DESC
          LIMIT ? OFFSET ?";

// Use a prepared statement
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $entriesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$invoices = [];
if ($result) {
    $invoices = $result->fetch_all(MYSQLI_ASSOC);
}

// Get active tenants for the dropdown
$tenantQuery = "SELECT t.tenant_id, u.name as tenant_name, p.unit_no, t.outstanding_balance, t.monthly_rate
               FROM tenants t
               JOIN users u ON t.user_id = u.user_id
               JOIN property p ON t.unit_rented = p.unit_id
               WHERE t.status = 'active'
               ORDER BY u.name ASC";
$tenantResult = $conn->query($tenantQuery);
$tenants = [];
if ($tenantResult) {
    $tenants = $tenantResult->fetch_all(MYSQLI_ASSOC);
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
    <title>Invoice Management</title>
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
        /* Custom styling for invoice preview */
        .invoice-preview {
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        .invoice-preview-header {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .table-cell-highlight:hover {
            background-color: rgba(243, 244, 246, 0.5);
        }
        /* Print specific styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #print-container, #print-container * {
                visibility: visible;
            }
            #print-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50"> 

<!-- Include Navbar -->
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>

<!-- Main Content -->
<div class="sm:ml-64 p-8 mt-20 mx-auto">
    <div class="container mx-auto max-w-7xl">
        
        <div class="mb-6 flex flex-col lg:flex-row justify-between items-start gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Invoice Management</h1>
            <div class="flex gap-2">
                <button id="exportInvoiceBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center">
                    <i class="fas fa-file-export mr-2"></i> Export Report
                </button>
                <button id="createInvoiceBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Create New Invoice
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
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="overdue">Overdue</option>
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
                        <input type="text" id="search-input" class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by tenant or unit...">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Table -->
        <div class="bg-white rounded-lg overflow-hidden custom-shadow mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="invoice-table-body">
                        <?php if (empty($invoices)) : ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No invoices found</td>
                        </tr>
                        <?php else : ?>
                            <?php foreach ($invoices as $invoice) : ?>
                            <tr class="table-cell-highlight">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">INV-<?= str_pad($invoice['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($invoice['tenant_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($invoice['unit_no']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₱<?= number_format($invoice['amount'], 2) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= date('M d, Y', strtotime($invoice['due_date'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    if ($invoice['status'] === 'paid') {
                                        $status_class = 'bg-green-100 text-green-800';
                                        $status_text = 'Paid';
                                    } elseif ($invoice['status'] === 'overdue' || 
                                              (strtotime($invoice['due_date']) < time() && $invoice['status'] !== 'paid')) {
                                        $status_class = 'bg-red-100 text-red-800';
                                        $status_text = 'Overdue';
                                    } else {
                                        $status_class = 'bg-yellow-100 text-yellow-800';
                                        $status_text = 'Unpaid';
                                    }
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>" id="status-badge-<?= $invoice['id'] ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button class="text-blue-600 hover:text-blue-900" onclick="viewInvoice(<?= $invoice['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="text-indigo-600 hover:text-indigo-900" onclick="sendInvoice(<?= $invoice['id'] ?>)">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                        <!-- New status toggle icon -->
                                        <button class="<?= $invoice['status'] === 'paid' ? 'text-green-600 hover:text-green-900' : 'text-yellow-600 hover:text-yellow-900' ?>" 
                                                onclick="toggleInvoiceStatus(<?= $invoice['id'] ?>, '<?= $invoice['status'] === 'paid' ? 'unpaid' : 'paid' ?>')" 
                                                title="<?= $invoice['status'] === 'paid' ? 'Mark as Unpaid' : 'Mark as Paid' ?>"
                                                id="status-icon-<?= $invoice['id'] ?>">
                                            <i class="fas <?= $invoice['status'] === 'paid' ? 'fa-times-circle' : 'fa-check-circle' ?>"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-900" onclick="deleteInvoice(<?= $invoice['id'] ?>)">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
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
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <!-- Previous Page -->
                            <a href="?page=<?= max(1, $page - 1) ?>&entries=<?= $entriesPerPage ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            
                            <!-- Page Numbers - with smart rendering for many pages -->
                            <?php 
                            // Initialize the page range
                            $startPage = max(1, $page - 2);
                            $endPage = min($startPage + 4, $totalPages);
                            
                            // Ensure we show at least 5 pages when possible
                            if ($endPage - $startPage < 4) {
                                $startPage = max(1, $endPage - 4);
                            }
                            
                            // Show first page with ellipsis if needed
                            if ($startPage > 1) {
                                echo '<a href="?page=1&entries='.$entriesPerPage.'" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                                if ($startPage > 2) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                }
                            }
                            
                            // Main page numbers
                            for ($i = $startPage; $i <= $endPage; $i++) { 
                                $isActive = $i === $page;
                                $activeClass = $isActive ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50';
                            ?>
                                <a href="?page=<?= $i ?>&entries=<?= $entriesPerPage ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border <?= $activeClass ?> text-sm font-medium">
                                    <?= $i ?>
                                </a>
                            <?php 
                            }
                            
                            // Show last page with ellipsis if needed
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                }
                                echo '<a href="?page='.$totalPages.'&entries='.$entriesPerPage.'" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">'.$totalPages.'</a>';
                            }
                            ?>
                            
                            <!-- Next Page -->
                            <a href="?page=<?= min($totalPages, $page + 1) ?>&entries=<?= $entriesPerPage ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </nav>
                    </div>
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

<!-- Create Invoice Modal -->
<div id="createInvoiceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Create New Invoice</h3>
            <button class="text-gray-400 hover:text-gray-500" onclick="toggleCreateModal(false)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <form id="invoiceForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left side - Invoice Details -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tenant</label>
                            <select id="tenant_id" name="tenant_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="" disabled selected>Select Tenant</option>
                                <?php foreach ($tenants as $tenant) : ?>
                                <option value="<?= $tenant['tenant_id'] ?>" 
                                        data-balance="<?= $tenant['outstanding_balance'] ?>"
                                        data-monthly-rate="<?= $tenant['monthly_rate'] ?>">
                                    <?= htmlspecialchars($tenant['tenant_name']) ?> (Unit <?= htmlspecialchars($tenant['unit_no']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Invoice Type</label>
                            <select id="invoice_type" name="invoice_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="rent">Monthly Rent</option>
                                <option value="utility">Utilities</option>
                                <option value="other">Other Fees</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount Due</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500">₱</span>
                                <input type="number" id="amount" name="amount" step="0.01" min="0" class="flex-1 block w-full border border-gray-300 rounded-none rounded-r-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Issue Date</label>
                            <input type="date" id="issue_date" name="issue_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input type="date" id="due_date" name="due_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                    
                    <!-- Right side - Additional Info -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Enter invoice description"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Add Line Items</label>
                            <div id="line-items" class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <input type="text" placeholder="Item" class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <input type="number" placeholder="Amount" step="0.01" min="0" class="w-24 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <button type="button" class="px-2 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" id="add-line-item" class="mt-2 px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                <i class="fas fa-plus mr-1"></i> Add Item
                            </button>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" id="send_email" name="send_email" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Send invoice via email immediately</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="toggleCreateModal(false)">
                        Cancel
                    </button>
                    <button type="button" id="preview-invoice" class="px-4 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Preview
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Invoice Preview Modal -->
<div id="invoicePreviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Invoice Preview</h3>
            <button class="text-gray-400 hover:text-gray-500" onclick="togglePreviewModal(false)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div id="invoice-preview" class="invoice-preview">
                <!-- Invoice Preview Content -->
                <div class="invoice-preview-header p-6">
                    <div class="flex flex-col md:flex-row justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">INVOICE</h2>
                            <p class="text-gray-600">Invoice #: <span id="preview-invoice-number">INV-00001</span></p>
                            <p class="text-gray-600">Issue Date: <span id="preview-issue-date">Jan 01, 2024</span></p>
                            <p class="text-gray-600">Due Date: <span id="preview-due-date">Jan 15, 2024</span></p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <h3 class="text-xl font-semibold text-gray-800">Property Information Management System</h3>
                            <p class="text-gray-600">One Soler Bldg, 1080 Soler St. 
                            Cor. Reina Regente / Felipe ll St.</p>
                            <p class="text-gray-600">Binondo Manila, 1006 Metro Manila</p>
                            <p class="text-gray-600">admin@propertywise.com</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <h4 class="font-semibold text-gray-700">Billed To:</h4>
                        <p class="text-gray-800"><span id="preview-tenant-name">Tenant Name</span></p>
                        <p class="text-gray-600">Unit: <span id="preview-unit-no">Unit 101</span></p>
                    </div>
                </div>
                
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Item</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="preview-line-items">
                            <!-- Line items will be inserted here -->
                        </tbody>
                        <tfoot class="border-t border-gray-200">
                            <tr>
                                <td class="px-4 py-3 text-right font-semibold text-gray-700">Total:</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-700">₱<span id="preview-total-amount">0.00</span></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <h4 class="font-semibold text-gray-700">Additional Information:</h4>
                        <p id="preview-description" class="text-gray-600">Invoice description will appear here.</p>
                    </div>
                    
                    <div class="mt-6 text-gray-600">
                        <p>Payment Methods: Gcash and Cash</p>
                        <p>Payment Terms: Due within 15 days of receipt</p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-6">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="togglePreviewModal(false)">
                    Cancel
                </button>
                <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="sendInvoiceEmail()">
                    Send via Email
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Invoice Modal -->
<div id="viewInvoiceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b border-gray-200 no-print">
            <h3 class="text-xl font-semibold text-gray-800">Invoice Details</h3>
            <button class="text-gray-400 hover:text-gray-500" onclick="toggleViewModal(false)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <!-- Wrapper div for print functionality -->
            <div id="print-container">
                <div class="invoice-preview">
                    <!-- Invoice content will be loaded here -->
                    <div id="view-invoice-content" class="animate-pulse">
                        <div class="h-6 bg-gray-200 rounded w-1/4 mb-4"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/3 mb-6"></div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="h-4 bg-gray-200 rounded w-full"></div>
                            <div class="h-4 bg-gray-200 rounded w-full"></div>
                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-6 no-print">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="toggleViewModal(false)">
                    Close
                </button>
                <button type="button" id="email-invoice-btn" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-paper-plane mr-2"></i> Send Email
                </button>
                <button type="button" id="print-invoice-btn" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email confirmation modal -->
<div id="emailConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <div class="text-center mb-4">
            <i class="fas fa-envelope-open-text text-blue-500 text-5xl"></i>
            <h3 class="text-xl font-semibold mt-2">Send Invoice Email</h3>
            <p class="text-gray-600 mt-1">You are about to email this invoice to the tenant.</p>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Email</label>
            <input type="email" id="recipient-email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" readonly>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
            <input type="text" id="email-subject" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="Invoice for Your Rent">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Message (Optional)</label>
            <textarea id="email-message" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Add a personal message here..."></textarea>
        </div>
        <div class="flex justify-end space-x-3">
            <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" onclick="toggleEmailModal(false)">
                Cancel
            </button>
            <button type="button" id="confirm-send-email" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Send Email
            </button>
        </div>
    </div>
</div>

<!-- Export Report Modal -->
<div id="exportReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Export Invoice Report</h3>
            <button class="text-gray-400 hover:text-gray-500" onclick="toggleExportModal(false)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <form id="exportForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date Range</label>
                    <input type="text" id="export-date-range" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Select date range">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="export-status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Statuses</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tenant (Optional)</label>
                    <select id="export-tenant" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Tenants</option>
                        <?php foreach ($tenants as $tenant) : ?>
                        <option value="<?= $tenant['tenant_id'] ?>"><?= htmlspecialchars($tenant['tenant_name']) ?> (Unit <?= htmlspecialchars($tenant['unit_no']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Export Format</label>
                    <div class="mt-1 flex space-x-4">

                        <label class="inline-flex items-center">
                            <input type="radio" name="export-format" value="csv" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">CSV</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="toggleExportModal(false)">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Export Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success toast container -->
<div id="toast-container" class="fixed top-5 right-5 z-50"></div>

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
        
        // Initialize date picker for export date range with default dates
        flatpickr("#export-date-range", {
            mode: "range",
            dateFormat: "Y-m-d",
            defaultDate: [new Date('<?= $thirtyDaysAgo ?>'), new Date('<?= $todayDate ?>')],
            placeholder: "Select date range"
        });
        
        // Show create invoice modal
        document.getElementById('createInvoiceBtn').addEventListener('click', function() {
            toggleCreateModal(true);
        });
        
        // Show export modal
        document.getElementById('exportInvoiceBtn').addEventListener('click', function() {
            toggleExportModal(true);
        });
        
        // Handle tenant selection to pre-fill monthly rate for rent invoices
        document.getElementById('tenant_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const invoiceType = document.getElementById('invoice_type').value;
            
            if (invoiceType === 'rent') {
                const monthlyRate = selectedOption.getAttribute('data-monthly-rate');
                document.getElementById('amount').value = monthlyRate;
            }
        });
        
        // Handle invoice type change to update amount for rent
        document.getElementById('invoice_type').addEventListener('change', function() {
            const tenantSelect = document.getElementById('tenant_id');
            const selectedOption = tenantSelect.options[tenantSelect.selectedIndex];
            
            if (this.value === 'rent' && selectedOption) {
                const monthlyRate = selectedOption.getAttribute('data-monthly-rate');
                document.getElementById('amount').value = monthlyRate;
            } else if (this.value !== 'rent') {
                document.getElementById('amount').value = '';
            }
        });
        
        // Add line item button
        document.getElementById('add-line-item').addEventListener('click', function() {
            const lineItemsContainer = document.getElementById('line-items');
            const newLineItem = document.createElement('div');
            newLineItem.className = 'flex items-center space-x-2';
            newLineItem.innerHTML = `
                <input type="text" placeholder="Item" class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <input type="number" placeholder="Amount" step="0.01" min="0" class="w-24 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <button type="button" class="px-2 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 remove-line-item">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            lineItemsContainer.appendChild(newLineItem);
            
            // Add event listener to the new remove button
            newLineItem.querySelector('.remove-line-item').addEventListener('click', function() {
                lineItemsContainer.removeChild(newLineItem);
            });
        });
        
        // Preview invoice button
        document.getElementById('preview-invoice').addEventListener('click', function() {
            generateInvoicePreview();
            togglePreviewModal(true);
        });
        
        // Initialize existing remove line item buttons
        document.querySelectorAll('.remove-line-item').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.flex').remove();
            });
        });
        
        // Handle invoice form submission
        document.getElementById('invoiceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            createInvoice();
        });
        
        // Handle export form submission
        document.getElementById('exportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const dateRange = document.getElementById('export-date-range').value;
            const status = document.getElementById('export-status').value;
            const tenant = document.getElementById('export-tenant').value;
            const format = document.querySelector('input[name="export-format"]:checked').value;
            
            // Create form data for submission
            const formData = new FormData();
            formData.append('date_range', dateRange);
            formData.append('status', status);
            formData.append('tenant_id', tenant);
            formData.append('format', format);
            
            // Show loading state
            const submitBtn = document.querySelector('#exportForm button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Exporting...';
            
            // Create and submit a form to download the file
            fetch('export_invoice_report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.blob();
            })
            .then(blob => {
                // Create a link to download the file
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                
                // Set the file name based on the format
                let fileName = 'invoice_report_' + new Date().toISOString().split('T')[0];
                if (format === 'pdf') {
                    fileName += '.pdf';
                } else if (format === 'excel') {
                    fileName += '.xlsx';
                } else {
                    fileName += '.csv';
                }
                a.download = fileName;
                
                // Append to the body, click and remove
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                // Close the modal
                toggleExportModal(false);
                
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                
                // Show success message
                showToast('Report exported successfully!', 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                
                showToast('Error exporting report: ' + error.message, 'error');
            });
        });
        
        // Apply filters on change
        document.getElementById('status-filter').addEventListener('change', applyFilters);
        document.getElementById('date-range').addEventListener('change', applyFilters);
        document.getElementById('search-input').addEventListener('keyup', applyFilters);
        
        // Email confirmation for view invoice
        document.getElementById('email-invoice-btn').addEventListener('click', function() {
            prepareEmailModal();
        });
        
        // Print invoice
        document.getElementById('print-invoice-btn').addEventListener('click', function() {
            printInvoice();
        });
        
        // Final email send button
        document.getElementById('confirm-send-email').addEventListener('click', function() {
            sendInvoiceEmail();
        });

        // Add context menu for status options
        const actionCells = document.querySelectorAll('#invoice-table-body tr td:last-child');
        actionCells.forEach(cell => {
            const invoiceRow = cell.closest('tr');
            const statusCell = invoiceRow.querySelector('td:nth-child(6)');
            const statusText = statusCell.textContent.trim().toLowerCase();
            
            // Add a new button for setting to overdue if it's not already overdue or paid
            if (statusText !== 'overdue' && statusText !== 'paid') {
                const buttons = cell.querySelector('.flex.space-x-2');
                
                // Create the overdue button
                const overdueBtn = document.createElement('button');
                overdueBtn.className = 'text-red-600 hover:text-red-900';
                overdueBtn.title = 'Mark as Overdue';
                overdueBtn.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
                
                // Extract the invoice ID from the existing buttons
                const viewBtn = buttons.querySelector('button');
                const onclick = viewBtn.getAttribute('onclick');
                const invoiceId = onclick.match(/\d+/)[0];
                
                // Set the click handler
                overdueBtn.onclick = function() {
                    toggleInvoiceStatus(invoiceId, 'overdue');
                };
                
                // Insert before the delete button
                buttons.insertBefore(overdueBtn, buttons.lastElementChild);
                buttons.insertBefore(document.createTextNode(' '), buttons.lastElementChild);
            }
        });
    });
    
    // Toggle create invoice modal
    function toggleCreateModal(show) {
        const modal = document.getElementById('createInvoiceModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    
    // Toggle preview modal
    function togglePreviewModal(show) {
        const modal = document.getElementById('invoicePreviewModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    
    // Toggle view invoice modal
    function toggleViewModal(show) {
        const modal = document.getElementById('viewInvoiceModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    
    // Toggle email confirmation modal
    function toggleEmailModal(show) {
        const modal = document.getElementById('emailConfirmModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    
    // Toggle export modal
    function toggleExportModal(show) {
        const modal = document.getElementById('exportReportModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    
    // Generate invoice preview from form data - Fix to show main item plus line items
    function generateInvoicePreview() {
        const tenantSelect = document.getElementById('tenant_id');
        const selectedTenant = tenantSelect.options[tenantSelect.selectedIndex];
        const tenantName = selectedTenant ? selectedTenant.text.split('(')[0].trim() : 'No tenant selected';
        const unitNo = selectedTenant ? selectedTenant.text.match(/\(Unit (.*?)\)/)[1] : '';
        
        // Get form values
        const issueDate = new Date(document.getElementById('issue_date').value);
        const dueDate = new Date(document.getElementById('due_date').value);
        const invoiceType = document.getElementById('invoice_type').value;
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const description = document.getElementById('description').value || 'No description provided';
        
        // Format dates
        const formattedIssueDate = issueDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const formattedDueDate = dueDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        
        // Update preview elements
        document.getElementById('preview-tenant-name').textContent = tenantName;
        document.getElementById('preview-unit-no').textContent = unitNo;
        document.getElementById('preview-issue-date').textContent = formattedIssueDate;
        document.getElementById('preview-due-date').textContent = formattedDueDate;
        document.getElementById('preview-description').textContent = description;
        document.getElementById('preview-total-amount').textContent = amount.toFixed(2);
        
        // Generate invoice number (for preview only)
        const randomNum = Math.floor(10000 + Math.random() * 90000);
        document.getElementById('preview-invoice-number').textContent = `INV-${randomNum}`;
        
        // Update line items - Modified to always show main invoice item plus additional items
        const lineItemsContainer = document.getElementById('preview-line-items');
        lineItemsContainer.innerHTML = '';
        
        // Always add the main invoice item first
        const mainRow = document.createElement('tr');
        mainRow.innerHTML = `
            <td class="px-4 py-3 text-sm text-gray-700">
                ${invoiceType === 'rent' ? 'Monthly Rent' : invoiceType === 'utility' ? 'Utilities Payment' : 'Other Fees'}
            </td>
            <td class="px-4 py-3 text-sm text-gray-700 text-right">₱${amount.toFixed(2)}</td>
        `;
        lineItemsContainer.appendChild(mainRow);
        
        // Track total amount separately from the main amount
        let totalAmount = amount;
        
        // Add any additional line items
        document.querySelectorAll('#line-items > div').forEach((item) => {
            const nameInput = item.querySelector('input[type="text"]');
            const amountInput = item.querySelector('input[type="number"]');
            
            if (nameInput && amountInput && 
                nameInput.value.trim() && 
                amountInput.value.trim() && 
                parseFloat(amountInput.value) > 0) {
                
                const itemName = nameInput.value.trim();
                const itemAmount = parseFloat(amountInput.value);
                
                const lineRow = document.createElement('tr');
                lineRow.innerHTML = `
                    <td class="px-4 py-3 text-sm text-gray-700">${itemName}</td>
                    <td class="px-4 py-3 text-sm text-gray-700 text-right">₱${itemAmount.toFixed(2)}</td>
                `;
                lineItemsContainer.appendChild(lineRow);
                
                // Add line item amounts to the total
                totalAmount += itemAmount;
            }
        });
        
        // Update total amount which now includes both main item and additional items
        document.getElementById('preview-total-amount').textContent = totalAmount.toFixed(2);
    }
    
    // Create and save invoice to database with improved error handling
    function createInvoice() {
        const formData = new FormData(document.getElementById('invoiceForm'));
        
        // Validate the form data before submission
        const tenantId = formData.get('tenant_id');
        const amount = formData.get('amount');
        const issueDate = formData.get('issue_date');
        const dueDate = formData.get('due_date');
        
        if (!tenantId || !amount || !issueDate || !dueDate) {
            showToast('Please fill in all required fields', 'error');
            return;
        }
        
        // Improved line items collection - collect ALL line items with values
        const lineItems = [];
        
        // Add main invoice type as a separate piece of information - not as a line item
        const invoiceType = document.getElementById('invoice_type').value;
        const mainAmount = parseFloat(document.getElementById('amount').value) || 0;
        
        // Track all additional line items separately
        document.querySelectorAll('#line-items > div').forEach((item) => {
            const nameInput = item.querySelector('input[type="text"]');
            const amountInput = item.querySelector('input[type="number"]');
            
            // Only add if both name and amount are provided and amount > 0
            if (nameInput && amountInput && 
                nameInput.value.trim() && 
                amountInput.value.trim() && 
                parseFloat(amountInput.value) > 0) {
                
                lineItems.push({
                    name: nameInput.value.trim(),
                    amount: parseFloat(amountInput.value)
                });
                
                console.log(`Added line item: ${nameInput.value.trim()} - ${parseFloat(amountInput.value)}`);
            }
        });
        
        // Always stringify the array, empty or not
        formData.append('line_items', JSON.stringify(lineItems));
        
        // Log what we're sending to help debug
        console.log('Sending line items:', JSON.stringify(lineItems));
        console.log('Form data entries:');
        for (const [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        // Show loading indicator
        const submitBtn = document.querySelector('#invoiceForm button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating...';
        
        // AJAX request to save invoice with improved error handling
        fetch('invoice_actions.php?action=create', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            // Ensure proper cache settings
            cache: 'no-cache',
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            // First try to get the raw text for debugging
            return response.text().then(text => {
                // Log the raw response for debugging
                console.log('Raw response:', text);
                
                // If the response is empty, throw a clear error
                if (!text || text.trim() === '') {
                    throw new Error('Server returned an empty response');
                }
                
                try {
                    // Try to parse the text as JSON
                    const data = JSON.parse(text);
                    
                    // Check if the request was successful based on the parsed JSON
                    if (!response.ok) {
                        throw new Error(data.message || 'Server returned an error');
                    }
                    
                    return data;
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Server error: Unable to process the response');
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Show success message
                showToast('Invoice created successfully!', 'success');
                
                // Close the modal
                toggleCreateModal(false);
                
                // Send email if checkbox is checked
                if (formData.get('send_email') === 'on' && data.invoice_id) {
                    sendEmailForInvoice(data.invoice_id);
                } else {
                    // Reload the page after a delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                throw new Error(data.message || 'Error creating invoice');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Create a more user-friendly error message
            let errorMessage = error.message;
            if (error.message.includes('Unexpected end of JSON input') || 
                error.message.includes('JSON parse error')) {
                errorMessage = 'Server communication error. Please try again or contact support.';
            }
            
            showToast(errorMessage, 'error');
            
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    }
    
    // View invoice details
    function viewInvoice(invoiceId) {
        // Show loading state
        toggleViewModal(true);
        document.getElementById('view-invoice-content').classList.add('animate-pulse');
        
        // Store the current invoice ID for email sending
        document.getElementById('email-invoice-btn').dataset.invoiceId = invoiceId;
        document.getElementById('print-invoice-btn').dataset.invoiceId = invoiceId;
        
        // Fetch invoice details
        fetch(`invoice_actions.php?action=view&id=${invoiceId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Remove loading state
                    document.getElementById('view-invoice-content').classList.remove('animate-pulse');
                    
                    // Populate invoice details
                    document.getElementById('view-invoice-content').innerHTML = `
                        <div class="invoice-preview-header p-6">
                            <div class="flex flex-col md:flex-row justify-between mb-6">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800">INVOICE</h2>
                                    <p class="text-gray-600">Invoice #: INV-${String(data.invoice.id).padStart(5, '0')}</p>
                                    <p class="text-gray-600">Issue Date: ${new Date(data.invoice.issue_date).toLocaleDateString()}</p>
                                    <p class="text-gray-600">Due Date: ${new Date(data.invoice.due_date).toLocaleDateString()}</p>
                                </div>
                                <div class="mt-4 md:mt-0">
                                    <h3 class="text-xl font-semibold text-gray-800">Property Information Management System</h3>
                                    <p class="text-gray-600">One Soler Bldg, 1080 Soler St. Cor. Reina Regente / Felipe ll St. </p>
                                    <p class="text-gray-600">Binondo Manila, 1006 Metro Manila</p>
                                    <p class="text-gray-600">admin@propertywise.com</p>
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4 mb-4">
                                <h4 class="font-semibold text-gray-700">Billed To:</h4>
                                <p class="text-gray-800">${data.tenant.name}</p>
                                <p class="text-gray-600">Unit: ${data.tenant.unit_no}</p>
                                <p class="text-gray-600">Email: ${data.tenant.email}</p>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Item</th>
                                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    ${data.items.map(item => `
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-700">${item.item_name}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 text-right">₱${parseFloat(item.amount).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                                <tfoot class="border-t border-gray-200">
                                    <tr>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-700">Total:</td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-700">₱${parseFloat(data.invoice.amount).toFixed(2)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            
                            <div class="mt-6 border-t border-gray-200 pt-4">
                                <h4 class="font-semibold text-gray-700">Additional Information:</h4>
                                <p class="text-gray-600">${data.invoice.description || 'No additional information provided.'}</p>
                            </div>
                            
                            <div class="mt-6 text-gray-600">
                                <p>Payment Methods: Gcash, and Cash</p>
                                <p>Payment Terms: Due within 15 days of receipt</p>
                                <p class="mt-2">
                                    Status: 
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                        data.invoice.status === 'paid' ? 'bg-green-100 text-green-800' : 
                                        (new Date(data.invoice.due_date) < new Date() ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')
                                    }">
                                        ${data.invoice.status === 'paid' ? 'Paid' : 
                                          (new Date(data.invoice.due_date) < new Date() ? 'Overdue' : 'Unpaid')}
                                    </span>
                                </p>
                                ${data.invoice.email_sent ? 
                                    `<p class="mt-2 text-sm text-gray-500">
                                        Email sent on ${new Date(data.invoice.email_sent_date).toLocaleString()}
                                    </p>` : ''}
                            </div>
                        </div>
                    `;
                    
                    // Store tenant email for sending
                    document.getElementById('email-invoice-btn').dataset.tenantEmail = data.tenant.email;
                    document.getElementById('email-invoice-btn').dataset.tenantName = data.tenant.name;
                } else {
                    throw new Error(data.message || 'Error fetching invoice');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('view-invoice-content').innerHTML = `
                    <div class="text-center p-6">
                        <div class="text-red-500 text-xl mb-2">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <p class="text-gray-700">Error loading invoice details.</p>
                        <p class="text-gray-500 text-sm mt-1">${error.message}</p>
                    </div>
                `;
            });
    }
    
    // Fixed print invoice function
    function printInvoice() {
        // Directly print the container without opening a new window
        window.print();
    }
    
    // Toggle invoice status (modified to handle overdue status)
    function toggleInvoiceStatus(invoiceId, newStatus) {
        // Show confirmation dialog
        let confirmMessage = '';
        if (newStatus === 'paid') {
            confirmMessage = 'Are you sure you want to mark this invoice as paid?';
        } else if (newStatus === 'unpaid') {
            confirmMessage = 'Are you sure you want to mark this invoice as unpaid?';
        } else if (newStatus === 'overdue') {
            confirmMessage = 'Are you sure you want to mark this invoice as overdue?';
        }
            
        if (!confirm(confirmMessage)) {
            return; // User cancelled
        }
        
        // Send AJAX request to update status
        fetch('invoice_actions.php?action=update_status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `invoice_id=${invoiceId}&status=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI to reflect new status
                updateStatusUI(invoiceId, newStatus);
                
                // Show success message
                showToast(`Invoice marked as ${newStatus}`, 'success');
            } else {
                throw new Error(data.message || 'Error updating status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error: ' + error.message, 'error');
        });
    }
    
    // Update status UI elements (modified for overdue status)
    function updateStatusUI(invoiceId, status) {
        // Update status badge
        const statusBadge = document.getElementById(`status-badge-${invoiceId}`);
        if (statusBadge) {
            if (status === 'paid') {
                statusBadge.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
                statusBadge.textContent = 'Paid';
            } else if (status === 'overdue') {
                statusBadge.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
                statusBadge.textContent = 'Overdue';
            } else {
                statusBadge.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800';
                statusBadge.textContent = 'Unpaid';
            }
        }
        
        // Update status icon
        const statusIcon = document.getElementById(`status-icon-${invoiceId}`);
        if (statusIcon) {
            if (status === 'paid') {
                statusIcon.className = 'text-green-600 hover:text-green-900';
                statusIcon.title = 'Mark as Unpaid';
                statusIcon.querySelector('i').className = 'fas fa-times-circle';
                statusIcon.onclick = () => toggleInvoiceStatus(invoiceId, 'unpaid');
            } else if (status === 'overdue') {
                statusIcon.className = 'text-red-600 hover:text-red-900';
                statusIcon.title = 'Mark as Paid';
                statusIcon.querySelector('i').className = 'fas fa-check-circle';
                statusIcon.onclick = () => toggleInvoiceStatus(invoiceId, 'paid');
            } else {
                statusIcon.className = 'text-yellow-600 hover:text-yellow-900';
                statusIcon.title = 'Mark as Paid';
                statusIcon.querySelector('i').className = 'fas fa-check-circle';
                statusIcon.onclick = () => toggleInvoiceStatus(invoiceId, 'paid');
            }
        }
    }
    
    // Send invoice email
    function prepareEmailModal() {
        const invoiceId = document.getElementById('email-invoice-btn').dataset.invoiceId;
        const tenantEmail = document.getElementById('email-invoice-btn').dataset.tenantEmail;
        const tenantName = document.getElementById('email-invoice-btn').dataset.tenantName;
        
        if (!invoiceId || !tenantEmail) {
            showToast('Cannot send email. Missing invoice or tenant information.', 'error');
            return;
        }
        
        // Set up the email modal
        document.getElementById('recipient-email').value = tenantEmail;
        document.getElementById('email-subject').value = `Invoice for Unit: ${document.querySelector('#view-invoice-content .text-gray-600:nth-child(2)').textContent.split(': ')[1]}`;
        document.getElementById('confirm-send-email').dataset.invoiceId = invoiceId;
        
        // Show the email modal
        toggleEmailModal(true);
    }
    
    // Send the actual email
    function sendInvoiceEmail() {
        const invoiceId = document.getElementById('confirm-send-email').dataset.invoiceId;
        const subject = document.getElementById('email-subject').value;
        const message = document.getElementById('email-message').value;
        
        // Show loading state
        document.getElementById('confirm-send-email').disabled = true;
        document.getElementById('confirm-send-email').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';
        
        // Send AJAX request to send email
        fetch('invoice_actions.php?action=send_email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `invoice_id=${invoiceId}&subject=${encodeURIComponent(subject)}&message=${encodeURIComponent(message)}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Close all modals
                toggleEmailModal(false);
                toggleViewModal(false);
                
                // Show success message
                showToast('Invoice email sent successfully!', 'success');
                
                // Reload the page after a delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Error sending email');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message, 'error');
            
            // Reset button
            document.getElementById('confirm-send-email').disabled = false;
            document.getElementById('confirm-send-email').innerHTML = 'Send Email';
        });
    }
    
    // Print invoice
    function printInvoice() {
        // Directly print the container without opening a new window
        window.print();
    }
    
    // Delete invoice
    function deleteInvoice(invoiceId) {
        if (confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
            fetch(`invoice_actions.php?action=delete&id=${invoiceId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Invoice deleted successfully!', 'success');
                    
                    // Reload the page after a delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Error deleting invoice');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(error.message, 'error');
            });
        }
    }
    
    // Apply filters to the invoice table (modified for better overdue handling)
    function applyFilters() {
        const statusFilter = document.getElementById('status-filter').value.toLowerCase();
        const dateRange = document.getElementById('date-range').value;
        const searchInput = document.getElementById('search-input').value.toLowerCase();
        
        const rows = document.querySelectorAll('#invoice-table-body tr');
        
        // Parse date range if provided
        let startDate = null;
        let endDate = null;
        if (dateRange) {
            const dates = dateRange.split(' to ');
            if (dates.length === 2) {
                startDate = new Date(dates[0]);
                endDate = new Date(dates[1]);
                // Set end date to end of day
                endDate.setHours(23, 59, 59, 999);
            } else if (dates.length === 1) {
                // Single date selected
                startDate = new Date(dates[0]);
                startDate.setHours(0, 0, 0, 0);
                endDate = new Date(dates[0]);
                endDate.setHours(23, 59, 59, 999);
            }
        }
        
        rows.forEach(row => {
            const tenant = row.cells[1].textContent.toLowerCase();
            const unit = row.cells[2].textContent.toLowerCase();
            const dueDateText = row.cells[4].textContent;
            const dueDate = new Date(dueDateText);
            const status = row.cells[5].textContent.trim().toLowerCase();
            
            // Improved status filter handling with special case for overdue
            let matchesStatus = statusFilter === '';
            if (statusFilter === 'overdue') {
                // Check if status shows as 'overdue' or if it's unpaid and past due date
                const isOverdue = status === 'overdue';
                const isPastDue = status === 'unpaid' && dueDate < new Date();
                matchesStatus = isOverdue || isPastDue;
            } else if (statusFilter === 'unpaid') {
                // Only truly unpaid, not overdue
                matchesStatus = status === 'unpaid' && dueDate >= new Date();
            } else {
                matchesStatus = status.includes(statusFilter);
            }
            
            const matchesSearch = searchInput === '' || 
                                 tenant.includes(searchInput) || 
                                 unit.includes(searchInput);
            
            // Date range filtering
            let matchesDateRange = true;
            if (startDate && endDate) {
                matchesDateRange = dueDate >= startDate && dueDate <= endDate;
            }
            
            if (matchesStatus && matchesSearch && matchesDateRange) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Send email for specific invoice
    function sendInvoice(invoiceId) {
        // First get invoice details
        fetch(`invoice_actions.php?action=view&id=${invoiceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store tenant email and invoice ID
                document.getElementById('email-invoice-btn').dataset.invoiceId = invoiceId;
                document.getElementById('email-invoice-btn').dataset.tenantEmail = data.tenant.email;
                document.getElementById('email-invoice-btn').dataset.tenantName = data.tenant.name;
                
                // Show email modal
                prepareEmailModal();
            } else {
                throw new Error(data.message || 'Error fetching invoice details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error preparing email: ' + error.message, 'error');
        });
    }
    
    // Show toast message with more visibility
    function showToast(message, type = 'success') {
        console.log(`Toast (${type}):`, message);
        
        Toastify({
            text: message,
            duration: 5000, // Display longer for better visibility
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: type === 'success' ? "#4CAF50" : "#f44336",
            stopOnFocus: true,
        }).showToast();
    }
    
    // Send email from invoice form
    function sendEmailForInvoice(invoiceId) {
        fetch(`invoice_actions.php?action=send_email`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `invoice_id=${invoiceId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Invoice created and email sent successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Error sending email');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Invoice created but email failed: ' + error.message, 'error');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        });
    }
    
    // Function to handle entries per page change
    function changeEntriesPerPage(select) {
        const entries = select.value;
        window.location.href = `?page=1&entries=${entries}`;
    }
</script>
</body>
</html>
