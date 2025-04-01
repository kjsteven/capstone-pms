<?php

session_start();

require_once '../session/db.php';
require_once '../session/auth.php';



// Pagination settings
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;

// Get total number of KYC submissions
$totalQuery = "SELECT COUNT(*) as total FROM kyc_verification WHERE archived = 0";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $entriesPerPage);

// Get KYC submissions with pagination
$query = "SELECT k.*, u.email as user_email, u.name as user_name 
          FROM kyc_verification k 
          JOIN users u ON k.user_id = u.user_id 
          WHERE k.archived = 0
          ORDER BY k.submission_date DESC 
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $entriesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Verification Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../images/logo.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .custom-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-50">

<?php include('navbarAdmin.php'); ?>
<?php include('sidebarAdmin.php'); ?>

<!-- Main Content -->
<div class="sm:ml-64 p-8 mt-20 mx-auto">
    <div class="container mx-auto max-w-7xl">
        <!-- Header -->
        <div class="mb-6 flex flex-col lg:flex-row justify-between items-start gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">KYC Verification Management</h1>
        </div>

        <!-- Filters Section -->
        <div class="mb-6 bg-white rounded-lg p-4 custom-shadow">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                    <select id="status-filter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Submission Date</label>
                    <input type="date" id="date-filter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" id="search-input" class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by name or email...">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KYC Table -->
        <div class="bg-white rounded-lg overflow-hidden custom-shadow mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submission Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['user_name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($row['user_email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y', strtotime($row['submission_date'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($row['id_type']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch($row['verification_status']) {
                                            case 'approved':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'rejected':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-yellow-100 text-yellow-800';
                                        }
                                        ?>">
                                        <?= ucfirst(htmlspecialchars($row['verification_status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-3">
                                        <button onclick="viewKYC(<?= $row['kyc_id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if($row['verification_status'] === 'pending'): ?>
                                            <button onclick="approveKYC(<?= $row['kyc_id'] ?>)" 
                                                    class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button onclick="showRejectModal(<?= $row['kyc_id'] ?>)" 
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="archiveKYC(<?= $row['kyc_id'] ?>)"
                                                class="text-gray-600 hover:text-gray-900">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <a href="?page=<?= max(1, $page - 1) ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                    <a href="?page=<?= min($totalPages, $page + 1) ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium"><?= $offset + 1 ?></span> to 
                            <span class="font-medium"><?= min($offset + $entriesPerPage, $totalRows) ?></span> of 
                            <span class="font-medium"><?= $totalRows ?></span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?= $i ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 <?= $i === $page ? 'bg-blue-50 text-blue-600' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View KYC Modal -->
<div id="viewKYCModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">KYC Details</h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="kycDetails" class="p-6">
            <!-- KYC details will be loaded here -->
            <div class="animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-1/4 mb-4"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
            </div>
        </div>
    </div>
</div>

<!-- Reject KYC Modal -->
<div id="rejectKYCModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Reject KYC Verification</h3>
            <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="rejectForm" onsubmit="submitReject(event)">
                <input type="hidden" id="reject_kyc_id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                    <textarea id="reject_reason" rows="4" 
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                              required></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toastify JS -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
function showToast(message, type = 'success') {
    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: type === 'success' ? "#4CAF50" : "#f44336",
        stopOnFocus: true,
    }).showToast();
}

function viewKYC(kycId) {
    const modal = document.getElementById('viewKYCModal');
    const detailsContainer = document.getElementById('kycDetails');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Fetch KYC details
    fetch(`kyc_actions.php?action=view&kyc_id=${kycId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                detailsContainer.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Personal Information</h4>
                            <div class="space-y-3">
                                <p><span class="font-medium">Name:</span> ${data.kyc.first_name} ${data.kyc.middle_name || ''} ${data.kyc.last_name}</p>
                                <p><span class="font-medium">Date of Birth:</span> ${new Date(data.kyc.date_of_birth).toLocaleDateString()}</p>
                                <p><span class="font-medium">Gender:</span> ${data.kyc.gender}</p>
                                <p><span class="font-medium">Nationality:</span> ${data.kyc.nationality}</p>
                                <p><span class="font-medium">Civil Status:</span> ${data.kyc.civil_status}</p>
                                <p><span class="font-medium">Email:</span> ${data.kyc.email}</p>
                                <p><span class="font-medium">Mobile:</span> ${data.kyc.mobile_number}</p>
                            </div>
                            
                            <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Address</h4>
                            <div class="space-y-3">
                                <p>${data.kyc.street_address}</p>
                                <p>Barangay ${data.kyc.barangay}</p>
                                <p>${data.kyc.city}, ${data.kyc.province} ${data.kyc.zip_code}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">ID Information</h4>
                            <div class="space-y-3">
                                <p><span class="font-medium">ID Type:</span> ${data.kyc.id_type}</p>
                                <p><span class="font-medium">ID Number:</span> ${data.kyc.id_number}</p>
                                <div class="mt-4">
                                    <p class="font-medium mb-2">ID Front:</p>
                                    <img src="../${data.kyc.id_front_path}" class="max-w-full h-auto rounded-lg shadow-sm">
                                </div>
                                <div class="mt-4">
                                    <p class="font-medium mb-2">ID Back:</p>
                                    <img src="../${data.kyc.id_back_path}" class="max-w-full h-auto rounded-lg shadow-sm">
                                </div>
                            </div>
                            
                            <h4 class="text-lg font-semibold text-gray-800 mt-6 mb-4">Financial Information</h4>
                            <div class="space-y-3">
                                <p><span class="font-medium">Source of Funds:</span> ${data.kyc.funds_source}</p>
                                <p><span class="font-medium">Occupation:</span> ${data.kyc.occupation}</p>
                                <p><span class="font-medium">Employer:</span> ${data.kyc.employer || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Submitted on: ${new Date(data.kyc.submission_date).toLocaleString()}</p>
                                ${data.kyc.verification_status !== 'pending' ? 
                                    `<p class="text-sm text-gray-600">
                                        ${data.kyc.verification_status === 'approved' ? 'Approved' : 'Rejected'} by: 
                                        ${data.kyc.verified_by || 'System'}
                                        ${data.kyc.verification_date ? ' on ' + new Date(data.kyc.verification_date).toLocaleString() : ''}
                                    </p>` : ''}
                            </div>
                            ${data.kyc.verification_status === 'pending' ? `
                                <div class="flex space-x-3">
                                    <button onclick="approveKYC(${data.kyc.kyc_id})" 
                                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                        Approve
                                    </button>
                                    <button onclick="showRejectModal(${data.kyc.kyc_id})" 
                                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                        Reject
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                        ${data.kyc.admin_remarks ? `
                            <div class="mt-4 p-4 bg-red-50 rounded-lg">
                                <p class="text-sm font-medium text-red-800">Admin Remarks:</p>
                                <p class="text-sm text-red-600">${data.kyc.admin_remarks}</p>
                            </div>
                        ` : ''}
                    </div>
                `;
            } else {
                detailsContainer.innerHTML = `
                    <div class="text-center text-red-600">
                        <p>Error loading KYC details: ${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            detailsContainer.innerHTML = `
                <div class="text-center text-red-600">
                    <p>Error loading KYC details: ${error.message}</p>
                </div>
            `;
        });
}

function closeViewModal() {
    const modal = document.getElementById('viewKYCModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function showRejectModal(kycId) {
    const modal = document.getElementById('rejectKYCModal');
    document.getElementById('reject_kyc_id').value = kycId;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectKYCModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('reject_reason').value = '';
}

function approveKYC(kycId) {
    if (!confirm('Are you sure you want to approve this KYC verification?')) {
        return;
    }
    
    fetch('kyc_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=approve&kyc_id=${kycId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('KYC verification approved successfully!', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Error approving KYC');
        }
    })
    .catch(error => {
        showToast(error.message, 'error');
    });
}

function archiveKYC(kycId) {
    if (!confirm('Are you sure you want to archive this KYC record?')) {
        return;
    }
    
    fetch('kyc_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=archive&kyc_id=${kycId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('KYC record archived successfully!', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Error archiving KYC');
        }
    })
    .catch(error => {
        showToast(error.message, 'error');
    });
}

function submitReject(event) {
    event.preventDefault();
    
    const kycId = document.getElementById('reject_kyc_id').value;
    const reason = document.getElementById('reject_reason').value;
    
    if (!reason.trim()) {
        showToast('Please provide a reason for rejection', 'error');
        return;
    }
    
    fetch('kyc_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=reject&kyc_id=${kycId}&reason=${encodeURIComponent(reason)}&admin_id=${<?= $_SESSION['user_id'] ?>}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('KYC verification rejected successfully!', 'success');
            closeRejectModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            throw new Error(data.message || 'Error rejecting KYC');
        }
    })
    .catch(error => {
        showToast(error.message, 'error');
    });
}

// Apply filters
document.getElementById('status-filter').addEventListener('change', applyFilters);
document.getElementById('date-filter').addEventListener('change', applyFilters);
document.getElementById('search-input').addEventListener('input', applyFilters);

function applyFilters() {
    const status = document.getElementById('status-filter').value.toLowerCase();
    const date = document.getElementById('date-filter').value;
    const search = document.getElementById('search-input').value.toLowerCase();
    
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
        const rowDate = row.querySelector('td:nth-child(2)').textContent;
        const rowText = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        
        const matchesStatus = !status || rowStatus.includes(status);
        const matchesDate = !date || rowDate.includes(date);
        const matchesSearch = !search || rowText.includes(search);
        
        row.style.display = matchesStatus && matchesDate && matchesSearch ? '' : 'none';
    });
}
</script>

</body>
</html>
