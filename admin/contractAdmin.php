<?php
require '../session/db.php';

// Modify the query to handle NULL values and use COALESCE
$query = "SELECT t.tenant_id, t.user_id, u.name AS tenant_name, p.unit_no, 
          COALESCE(t.contract_file, '') as contract_file, 
          t.contract_upload_date 
          FROM tenants t 
          JOIN users u ON t.user_id = u.user_id 
          JOIN property p ON t.unit_rented = p.unit_id 
          WHERE t.status = 'active'";
$result = $conn->query($query);
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
    </style>

    
</head>
<body>

<!-- Include Navbar -->
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>

<div class="sm:ml-64 p-8 mt-20 mx-auto">
    <h1 class="text-xl font-semibold text-gray-800 mb-6">Tenant Contracts Management</h1>

    <!-- Search and Print Buttons -->
    <div class="flex flex-wrap items-center gap-4 mb-6">
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
    <div class="overflow-x-auto bg-white rounded-lg shadow">
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
                                <a href="<?php echo htmlspecialchars($row['contract_file']); ?>" 
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
        window.print();
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
</script>

</body>
</html>