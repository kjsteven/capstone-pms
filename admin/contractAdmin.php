<?php
require '../session/db.php';
require_once '../session/session_manager.php';
 
start_secure_session();

// Pagination settings
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page']: 1; // Removed extra parenthesis
$offset = ($page - 1) * $entriesPerPage;

// Get total number of records
$totalQuery = "SELECT COUNT(*) as total FROM tenants t WHERE t.status = 'active'";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $entriesPerPage);

// Modified query with pagination
$query = "SELECT t.tenant_id, t.user_id, u.name AS tenant_name, p.unit_no, 
          COALESCE(t.contract_file, '') as contract_file, 
          t.contract_upload_date 
          FROM tenants t 
          JOIN users u ON t.user_id = u.user_id 
          JOIN property p ON t.unit_rented = p.unit_id 
          WHERE t.status = 'active'
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $entriesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Maintenance Requests</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>

    
</head>
<body>

<!-- Include Navbar -->
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>

<div class="sm:ml-64 p-8 mt-20 mx-auto">
    <h1 class="text-xl font-semibold text-gray-800 mb-6">Tenant Contracts Management</h1>

    <!-- Entries per page selector -->
    <div class="flex flex-wrap items-center gap-4 mb-6">
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Show entries:</label>
            <select id="entriesPerPage" class="border rounded px-2 py-1" onchange="changeEntries(this.value)">
                <option value="10" <?php echo $entriesPerPage == 10 ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo $entriesPerPage == 25 ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo $entriesPerPage == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $entriesPerPage == 100 ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
        <div class="relative w-full sm:w-1/3">
            <input type="text" id="searchInput" placeholder="Search tenant name or unit..." 
                   class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300">
            <button class="absolute right-0 top-0 h-full px-3 bg-blue-600 text-white rounded-r-lg">
                <svg data-feather="search" class="w-4 h-4"></svg>
            </button>
        </div>
        <button id="printButton" class="px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2">
            <svg data-feather="printer" class="w-4 h-4"></svg>
            Print
        </button>
    </div>

    <!-- Contracts Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow print-section">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contract Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['tenant_id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['tenant_name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['unit_no']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if($row['contract_file']): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Uploaded
                            </span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Pending
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo $row['contract_upload_date'] ? date('Y-m-d', strtotime($row['contract_upload_date'])) : 'Not uploaded'; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-3">
                            <?php if($row['contract_file']): ?>
                                <a href="<?php echo '../' . htmlspecialchars($row['contract_file']); ?>" 
                                   class="text-blue-600 hover:text-blue-900" target="_blank">
                                    <svg data-feather="eye" class="w-5 h-5"></svg>
                                </a>
                            <?php endif; ?>
                            <label class="cursor-pointer text-indigo-600 hover:text-indigo-900">
                                <svg data-feather="upload" class="w-5 h-5"></svg>
                                <input type="file" class="hidden contract-upload" 
                                       data-tenant-id="<?php echo $row['tenant_id']; ?>" 
                                       accept=".pdf,.doc,.docx">
                            </label>
                            <?php if($row['contract_file']): ?>
                                <button onclick="deleteContract(<?php echo $row['tenant_id']; ?>)" 
                                        class="text-red-600 hover:text-red-900">
                                    <svg data-feather="trash-2" class="w-5 h-5"></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination controls -->
    <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-600">
            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $entriesPerPage, $totalRows); ?> of <?php echo $totalRows; ?> entries
        </div>
        <div class="flex gap-2">
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
</div>

<script src="../node_modules/feather-icons/dist/feather.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>

<script>
    feather.replace();

    // Handle contract file upload
    document.querySelectorAll('.contract-upload').forEach(input => {
        input.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            const tenantId = e.target.dataset.tenantId;
            
            if (!file) return;

            const formData = new FormData();
            formData.append('contract', file);
            formData.append('tenant_id', tenantId);

            try {
                const response = await fetch('upload_contract.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    Toastify({
                        text: "Contract uploaded successfully",
                        duration: 3000,
                        backgroundColor: "green",
                    }).showToast();
                    
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Toastify({
                    text: error.message || "Error uploading contract",
                    duration: 3000,
                    backgroundColor: "red",
                }).showToast();
            }
        });
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const tenantName = row.children[1].textContent.toLowerCase();
            const unitNo = row.children[2].textContent.toLowerCase();
            const matches = tenantName.includes(searchTerm) || unitNo.includes(searchTerm);
            row.style.display = matches ? '' : 'none';
        });
    });

    // Print functionality
    document.getElementById('printButton').addEventListener('click', function() {
        // Add a title before printing
        const title = document.createElement('h2');
        title.className = 'text-xl font-bold mb-4 print-section';
        title.style.textAlign = 'center';
        title.innerText = 'Tenant Contracts Report';
        
        const table = document.querySelector('.print-section');
        table.parentNode.insertBefore(title, table);
        
        window.print();
        
        // Remove the title after printing
        title.remove();
    });

    // Delete contract
    async function deleteContract(tenantId) {
        if (!confirm('Are you sure you want to delete this contract?')) return;

        try {
            const response = await fetch('delete_contract.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ tenant_id: tenantId })
            });

            const data = await response.json();
            
            if (data.success) {
                Toastify({
                    text: "Contract deleted successfully",
                    duration: 3000,
                    backgroundColor: "green",
                }).showToast();
                
                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Toastify({
                text: error.message || "Error deleting contract",
                duration: 3000,
                backgroundColor: "red",
            }).showToast();
        }
    }

    // Add this new function for entries per page
    function changeEntries(value) {
        window.location.href = `?entries=${value}&page=1`;
    }
</script>

</body>
</html>