<?php

require_once '../session/session_manager.php';
require '../session/db.php';

start_secure_session();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../authentication/login.php'); // Adjust the path as necessary
    exit();
}

// Fetch tenant data with last payment date
$query = "
    SELECT 
        t.tenant_id,
        t.rent_from,
        t.rent_until,
        t.monthly_rate,
        t.outstanding_balance,
        t.downpayment_amount,
        t.downpayment_receipt,
        t.payable_months,
        t.last_payment_date,
        p.unit_no,
        p.unit_type,
        p.square_meter
    FROM tenants t
    JOIN property p ON t.unit_rented = p.unit_id
    WHERE t.user_id = ? AND t.status = 'active'
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$tenants = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate invoice date (30th of current month)
$invoice_date = date('F d, Y', strtotime(date('Y-m-30')));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit Information</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">

<!-- Include Navbar -->
<?php include('navbar.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebar.php'); ?>

<!-- Main Content -->
<div class="p-8 mt-20 sm:ml-64">
    <h2 class="text-2xl font-semibold mb-6">Rented Unit Information</h2>
    
    <!-- Filter, Search Bar, and Print Button -->
    <div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <!-- Unit Type Filter -->
        <div class="relative w-full sm:w-1/4">
            <select id="status-filter" class="border border-gray-300 rounded-lg px-4 py-2 pr-8 outline-none appearance-none w-full">
                <option value="">All Unit</option>
                <option value="Office">Office</option>
                <option value="Warehouse">Warehouse</option>
                <option value="Commercial">Commercial</option>
            </select>
            <span class="absolute inset-y-0 right-2 flex items-center pointer-events-none text-gray-500">
            <svg data-feather="chevron-down" class="w-4 h-4"></svg>
            </span>
        </div>
        
        <!-- Search Bar -->
        <div class="relative w-full sm:w-1/4">
            <input type="text" id="search" placeholder="Search unit..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
            <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
            <svg data-feather="search" class="w-4 h-4"></svg>
            </button>
        </div>
        
        <!-- Print Button -->
        <button id="printBtn" class="bg-blue-600 text-white rounded-lg px-4 py-2 w-full sm:w-auto">Print</button>
    </div>
    
    <!-- Unit Table -->
    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
        <table class="min-w-full border-collapse table" id="unitTable">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-4 py-2">Unit Type</th>
                    <th class="border px-4 py-2">Unit No</th>
                    <th class="border px-4 py-2">Rent From</th>
                    <th class="border px-4 py-2">Rent Until</th>
                    <th class="border px-4 py-2">Monthly Rate</th>
                    <th class="border px-4 py-2">Invoice Date</th>
                    <th class="border px-4 py-2">Last Payment</th>
                    <th class="border px-4 py-2">Downpayment Amount</th>
                    <th class="border px-4 py-2">Downpayment Receipt</th>
                    <th class="border px-4 py-2">Outstanding Balance</th>
                    <th class="border px-4 py-2">Payable Months</th>
                </tr>
            </thead>
            <tbody id="unitTableBody">
                <?php foreach ($tenants as $tenant): ?>
                    <tr>
                        <td class="text-center border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($tenant['unit_type']); ?></td>
                        <td class="text-center border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($tenant['unit_no']); ?></td>
                        <td class="text-center border border-gray-300 px-4 py-2"><?php echo date('F d, Y', strtotime($tenant['rent_from'])); ?></td>
                        <td class="text-center border border-gray-300 px-4 py-2"><?php echo date('F d, Y', strtotime($tenant['rent_until'])); ?></td>
                        <td class="text-center border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($tenant['monthly_rate']); ?></td>
                        <td class="text-center border border-gray-300 px-4 py-2"><?php echo $invoice_date; ?></td>
                        <td class="text-center border border-gray-300 px-4 py-2">
                            <?php if ($tenant['last_payment_date']): ?>
                                <?php echo date('F d, Y', strtotime($tenant['last_payment_date'])); ?>
                            <?php else: ?>
                                No payments yet
                            <?php endif; ?>
                        </td>
                        <td class="text-center border border-gray-300 px-4 py-2"><?php echo '₱' . number_format($tenant['downpayment_amount'], 2); ?></td>
                        <td class="text-center border border-gray-300 px-4 py-2">
                            <?php if (!empty($tenant['downpayment_receipt'])): ?>
                                <button type="button" class="text-blue-500 hover:text-blue-700 underline" 
                                        onclick="viewReceipt('<?php echo htmlspecialchars($tenant['downpayment_receipt'], ENT_QUOTES, 'UTF-8'); ?>')">
                                    View Receipt
                                </button>
                            <?php else: ?>
                                No Receipt
                            <?php endif; ?>
                        </td>
                        <td class="text-center border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($tenant['outstanding_balance']); ?></td>
                        <td class="text-center border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($tenant['payable_months']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../node_modules/feather-icons/dist/feather.min.js"></script>

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
    // Feather Icons
    feather.replace();

    // Search Functionality
    const searchInput = document.getElementById('search');
    const unitTableBody = document.getElementById('unitTableBody');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        const rows = unitTableBody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    const txtValue = cells[j].textContent || cells[j].innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            rows[i].style.display = found ? "" : "none";
        }
    });

    // Unit Type Filter Functionality
    const statusFilter = document.getElementById('status-filter');
    statusFilter.addEventListener('change', function() {
        const filterValue = statusFilter.value.toLowerCase();
        const rows = unitTableBody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const unitTypeCell = rows[i].getElementsByTagName('td')[0]; // Unit Type is the first column
            if (unitTypeCell) {
                const unitType = unitTypeCell.textContent || unitTypeCell.innerText;
                if (filterValue === "" || unitType.toLowerCase() === filterValue) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    });

    // Print Functionality
    document.getElementById('printBtn').addEventListener('click', function() {
        const printContent = document.getElementById('unitTable').outerHTML;

        // Create a new window to print
        const newWin = window.open('', '', 'width=800,height=600');
        newWin.document.write(`
            <html>
                <head>
                    <title>Print Unit Information</title>
                    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
                    <style>
                        body {
                            font-family: 'Poppins', sans-serif;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        th, td {
                            border: 1px solid #000;
                            padding: 8px;
                            text-align: left;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>Unit Information</h2>
                    </div>
                    ${printContent}
                </body>
            </html>
        `);
        
        newWin.document.close(); // Close the document to complete loading
        newWin.onload = function() { // Wait until the new document is fully loaded
            newWin.print();
            newWin.close();
        };
    });

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
            alert('Receipt image could not be loaded');
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