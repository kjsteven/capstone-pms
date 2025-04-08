<?php
// Start session at the very beginning, before any output
session_start();

require_once '../session/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../authentication/login.php');
    exit();
}

// Get the tenant ID associated with the logged-in user
$user_id = $_SESSION['user_id'];
$tenantQuery = "SELECT tenant_id FROM tenants WHERE user_id = ?";
$tenantStmt = $conn->prepare($tenantQuery);
$tenantStmt->bind_param("i", $user_id);
$tenantStmt->execute();
$tenantResult = $tenantStmt->get_result();

if ($tenantResult->num_rows === 0) {
    // User is not a tenant, redirect to appropriate page
    header('Location: ../users/dashboard.php');
    exit();
}

$tenant = $tenantResult->fetch_assoc();
$tenant_id = $tenant['tenant_id'];

// DEBUG: Output tenant ID to verify we have the correct tenant
var_dump("Current user ID: " . $user_id);
var_dump("Tenant ID: " . $tenant_id);

// Function to check and update overdue invoices
function updateOverdueInvoices($conn, $tenant_id) {
    // Get today's date
    $today = date('Y-m-d');
    
    // Prepare and execute query to update status of overdue invoices for this tenant
    $updateQuery = "UPDATE invoices SET status = 'overdue' 
                   WHERE tenant_id = ? AND status = 'unpaid' AND due_date < ? AND status != 'paid'";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("is", $tenant_id, $today);
    $stmt->execute();
    
    $updatedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $updatedRows;
}

// Call the function to update overdue invoices on page load
updateOverdueInvoices($conn, $tenant_id);

// Pagination settings with validation
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
if ($entriesPerPage <= 0) {
    $entriesPerPage = 10; // Default to 10 if invalid
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page <= 0) {
    $page = 1;
}

// Calculate offset - ensure it's never negative
$offset = ($page - 1) * $entriesPerPage;
if ($offset < 0) {
    $offset = 0;
}

// Get total number of invoices for this tenant
$totalQuery = "SELECT COUNT(*) as total FROM invoices WHERE tenant_id = ?";
$totalStmt = $conn->prepare($totalQuery);
$totalStmt->bind_param("i", $tenant_id);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalRows / $entriesPerPage));

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

// Get invoice data with pagination for this tenant only
$query = "SELECT i.*, u.name as tenant_name, p.unit_no 
          FROM invoices i
          JOIN tenants t ON i.tenant_id = t.tenant_id
          JOIN users u ON t.user_id = u.user_id
          JOIN property p ON t.unit_rented = p.unit_id
          WHERE i.tenant_id = ?
          ORDER BY i.created_at DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $tenant_id, $entriesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// DEBUG: Output query results for troubleshooting
var_dump("SQL Query: " . $query);
var_dump("Number of rows returned: " . ($result ? $result->num_rows : 0));

$invoices = [];
if ($result) {
    $invoices = $result->fetch_all(MYSQLI_ASSOC);
    // DEBUG: Show the actual invoice data
    var_dump("Invoice data:", $invoices);
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
    <title>My Invoices</title>
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
<?php include('navbar.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebar.php'); ?>

<!-- Main Content -->
<div class="sm:ml-64 p-8 mt-20 mx-auto">
    <div class="container mx-auto max-w-7xl">
        
        <div class="mb-6 flex flex-col lg:flex-row justify-between items-start gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">My Invoices</h1>
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
                        <input type="text" id="search-input" class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by invoice number...">
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
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No invoices found</td>
                        </tr>
                        <?php else : ?>
                            <?php foreach ($invoices as $invoice) : ?>
                            <tr class="table-cell-highlight">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">INV-<?= str_pad($invoice['id'], 5, '0', STR_PAD_LEFT) ?></td>
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
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button class="text-blue-600 hover:text-blue-900" onclick="viewInvoice(<?= $invoice['id'] ?>)" title="View Invoice">
                                            <i class="fas fa-eye"></i>
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
                <button type="button" id="print-invoice-btn" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
            </div>
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
        const dateRangeElement = document.getElementById('date-range');
        if (dateRangeElement) {
            flatpickr("#date-range", {
                mode: "range",
                dateFormat: "Y-m-d",
                placeholder: "Select date range"
            });
        }
        
        // Apply filters on change - with null checks
        const statusFilter = document.getElementById('status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', applyFilters);
        }
        
        if (dateRangeElement) {
            dateRangeElement.addEventListener('change', applyFilters);
        }
        
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('keyup', applyFilters);
        }
        
        // Print invoice - with null check
        const printInvoiceBtn = document.getElementById('print-invoice-btn');
        if (printInvoiceBtn) {
            printInvoiceBtn.addEventListener('click', function() {
                printInvoice();
            });
        }
    });
    
    // Toggle view invoice modal
    function toggleViewModal(show) {
        const modal = document.getElementById('viewInvoiceModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
    
    // View invoice details
    function viewInvoice(invoiceId) {
        // Show loading state
        toggleViewModal(true);
        document.getElementById('view-invoice-content').classList.add('animate-pulse');
        
        // Store the current invoice ID for printing
        const printBtn = document.getElementById('print-invoice-btn');
        if (printBtn) {
            printBtn.dataset.invoiceId = invoiceId;
        }
        
        // Fetch invoice details - make sure to use the correct path to invoice_actions.php
        fetch(`../admin/invoice_actions.php?action=view&id=${invoiceId}`)
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
                            </div>
                        </div>
                    `;
                } else {
                    throw new Error(data.message || 'Error fetching invoice');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const viewContent = document.getElementById('view-invoice-content');
                if (viewContent) {
                    viewContent.innerHTML = `
                        <div class="text-center p-6">
                            <div class="text-red-500 text-xl mb-2">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <p class="text-gray-700">Error loading invoice details.</p>
                            <p class="text-gray-500 text-sm mt-1">${error.message}</p>
                        </div>
                    `;
                }
            });
    }
    
    // Print invoice
    function printInvoice() {
        // Directly print the container without opening a new window
        window.print();
    }
    
    // Apply filters to the invoice table
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
            // First cell contains invoice number
            const invoiceNum = row.cells[0].textContent.toLowerCase();
            const dueDateText = row.cells[3].textContent;
            const dueDate = new Date(dueDateText);
            const status = row.cells[4].textContent.trim().toLowerCase();
            
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
                                 invoiceNum.includes(searchInput);
            
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
    
    // Show toast message with more visibility
    function showToast(message, type = 'success') {
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
    
    // Function to handle entries per page change
    function changeEntriesPerPage(select) {
        const entries = select.value;
        window.location.href = `?page=1&entries=${entries}`;
    }
</script>
</body>
</html>
