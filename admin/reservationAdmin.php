<?php
require_once '../session/session_manager.php';
require '../session/db.php';

start_secure_session();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../authentication/login.php');
    exit();
}

// Pagination settings
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;

// Check if this is an AJAX request
if (isset($_GET['search']) || isset($_GET['status'])) {
    ob_clean();
    // Set proper JSON headers
    header('Content-Type: application/json');

    // Get search and filter parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';

    // Build the query
    $query = "SELECT r.reservation_id, r.viewing_date, r.viewing_time, r.created_at, 
                    u.unit_no, u.unit_type, u.monthly_rent, u.square_meter, r.status,
                    usr.name, usr.email, usr.phone
            FROM reservations r
            JOIN property u ON r.unit_id = u.unit_id
            JOIN users usr ON r.user_id = usr.user_id
            WHERE r.archived = 0";

    $params = [];
    $types = "";

    // Add search condition
    if (!empty($search)) {
        $query .= " AND (usr.name LIKE ? OR usr.email LIKE ? OR u.unit_no LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    // Add status filter
    if (!empty($status)) {
        $query .= " AND r.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    $query .= " ORDER BY r.created_at DESC";

    try {
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $reservations = $result->fetch_all(MYSQLI_ASSOC);

        // Output JSON data
        echo json_encode($reservations);
        exit();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

// Regular page load query
$query = "SELECT SQL_CALC_FOUND_ROWS r.reservation_id, r.viewing_date, r.viewing_time, r.created_at, 
                u.unit_no, u.unit_type, u.monthly_rent, u.square_meter, r.status,
                usr.name, usr.email, usr.phone
        FROM reservations r
        JOIN property u ON r.unit_id = u.unit_id
        JOIN users usr ON r.user_id = usr.user_id
        WHERE r.archived = 0 
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?";

try {
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $entriesPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservations = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get total rows
    $totalResult = $conn->query("SELECT FOUND_ROWS()");
    $totalRows = $totalResult->fetch_row()[0];
    $totalPages = ceil($totalRows / $entriesPerPage);
} catch (Exception $e) {
    die("Error fetching reservations: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .search-bar {
            width: 100%;
            max-width: 300px;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Include Navbar -->
    <?php include('navbarAdmin.php'); ?>

    <!-- Include Sidebar -->
    <?php include('sidebarAdmin.php'); ?>

    <!-- Main content for Admin Dashboard -->
    <div class="sm:ml-64 p-8 mt-20 mx-auto">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4 sm:mb-6">Reservation Management</h1>
        
    <!-- Filter Section -->
    <div class="flex flex-wrap items-center gap-4 mb-6">
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Show entries:</label>
            <select id="entriesPerPage" class="border rounded px-2 py-1.5" onchange="changeEntries(this.value)">
                <option value="10" <?php echo $entriesPerPage == 10 ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo $entriesPerPage == 25 ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo $entriesPerPage == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $entriesPerPage == 100 ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
        
        <div class="flex items-center gap-4 flex-1">
            <select id="admin-status-filter" class="border border-gray-300 rounded-lg px-4 py-2">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <div class="relative w-full sm:w-1/3">
                <input type="text" id="search" placeholder="Search by name or unit..." 
                       class="block w-full p-2.5 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300">
                <button class="absolute right-0 top-0 h-full px-3 bg-blue-600 text-white rounded-r-lg">
                    <svg data-feather="search" class="w-4 h-4"></svg>
                </button>
            </div>
        </div>

        <button id="bulk-archive" class="bg-red-600 text-white px-4 py-2 rounded-md inline-flex items-center gap-2">
            <i data-feather="archive" class="w-4 h-4"></i>
            <span>Archive Selected</span>
        </button>
    </div>

        <!-- Reservation Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full table-auto border border-gray-300">
                <thead class="bg-gray-200 text-gray-800">
                    <tr>
                        <th class="py-2 px-4 border-b"><input type="checkbox" id="select-all"></th>
                        <th class="py-2 px-4 border-b">Reservation ID</th>
                        <th class="py-2 px-4 border-b">Name</th>
                        <th class="py-2 px-4 border-b">Email</th>
                        <th class="py-2 px-4 border-b">Unit No</th>
                        <th class="py-2 px-4 border-b">Unit Type</th>
                        <th class="py-2 px-4 border-b">Monthly Rate</th>
                        <th class="py-2 px-4 border-b">Square Meter</th>
                        <th class="py-2 px-4 border-b">Viewing Date</th>
                        <th class="py-2 px-4 border-b">Viewing Time</th>
                        <th class="py-2 px-4 border-b">Status</th>
                        <th class="py-2 px-4 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody id="admin-reservation-table-body">
                    <?php foreach ($reservations as $reservation) { ?>
                        <tr>
                            <td class="py-2 px-4 border-b">
                                <input type="checkbox" class="select-row" value="<?= htmlspecialchars($reservation['reservation_id']); ?>">
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                                <?= htmlspecialchars($reservation['reservation_id']); ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                                <?= htmlspecialchars($reservation['name']); ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                                <?= htmlspecialchars($reservation['email']); ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                                <?= htmlspecialchars($reservation['unit_no']); ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                                <?= htmlspecialchars($reservation['unit_type']); ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                                ₱<?= number_format(htmlspecialchars($reservation['monthly_rent']), 2); ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                                <?= htmlspecialchars($reservation['square_meter']); ?> 
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                                <?= htmlspecialchars($reservation['viewing_date']); ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                                <?= date('h:i A', strtotime(htmlspecialchars($reservation['viewing_time']))); ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                                <?= htmlspecialchars($reservation['status']); ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 border-b text-center">
                            <div class="flex justify-center items-center space-x-2">
                          
                            <!-- Edit and Archive Buttons -->   
                            <button class="bg-blue-500 text-white px-3 py-1 rounded-md inline-flex items-center space-x-1">
                                <i data-feather="edit-2" class="w-4 h-4"></i>
                                <span>Edit</span>
                            </button>

                            <button class="bg-red-500 text-white px-3 py-1 rounded-md inline-flex items-center space-x-1">
                                <i data-feather="archive" class="w-4 h-4"></i>
                                <span>Archive</span>
                            </button>

                            </div>
                            </td>
                        </tr>
                    <?php } ?>
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

  <!-- Status Update Modal -->
    <div id="statusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-start justify-center pt-24">
        <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md mx-4 sm:max-w-lg">
            <div class="p-6">
                <div class="text-center">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Update Reservation Status</h3>
                    <!-- Hidden Input for Reservation ID -->
                    <input type="hidden" id="currentReservationId" name="reservation_id">
                    <div class="space-y-4">
                        <button onclick="updateStatus('Confirmed')" 
                                class="w-full flex items-center justify-center bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition duration-200">
                            <i data-feather="check-circle" class="w-5 h-5 mr-2"></i>
                            Confirm Reservation
                        </button>
                        <button onclick="updateStatus('Completed')" 
                                class="w-full flex items-center justify-center bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-200">
                            <i data-feather="check-square" class="w-5 h-5 mr-2"></i>
                            Mark as Completed
                        </button>
                        <button onclick="updateStatus('Cancelled')" 
                                class="w-full flex items-center justify-center bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition duration-200">
                            <i data-feather="x-circle" class="w-5 h-5 mr-2"></i>
                            Cancel Reservation
                        </button>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <button type="button" id="closeModal" class="w-full px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script src="../node_modules/feather-icons/dist/feather.min.js"></script>

    <script>
        // Initialize Feather Icons
        feather.replace();
    </script>

    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.select-row');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        document.getElementById('bulk-archive').addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.select-row:checked'))
                                    .map(checkbox => checkbox.value);
            if (selectedIds.length === 0) {
                alert('No reservations selected');
                return;
            }

            // Archive logic goes here
            alert(`Archiving reservations: ${selectedIds.join(', ')}`);
        });
    </script>

    <script>

      // Function to open the modal and set the reservation ID
    function editReservation(reservationId) {
        document.getElementById('currentReservationId').value = reservationId;
        document.getElementById('statusModal').classList.remove('hidden');
    }

    // Function to update the status
    function updateStatus(status) {
    const reservationId = document.getElementById('currentReservationId').value;
    const buttons = document.querySelectorAll('#statusModal button');
    
    if (!reservationId) {
        Toastify({
            text: "No reservation ID found",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#F44336"
        }).showToast();
        return;
    }

    // Disable all buttons and add loading state
    buttons.forEach(button => {
        button.disabled = true;
        if (button.getAttribute('onclick')?.includes(status)) {
            const originalContent = button.innerHTML;
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Updating...
            `;
            button.setAttribute('data-original-content', originalContent);
        }
    });

    const normalizedStatus = status.toLowerCase();

    console.log('Sending update request:', {
        reservation_id: reservationId,
        status: normalizedStatus
    });

    fetch('update_reservation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            reservation_id: reservationId,
            status: normalizedStatus
        })
    })
    .then(response => {
        console.log('Response Status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            Toastify({
                text: "Status updated successfully, Email Sent Successfully.",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#4CAF50"
            }).showToast();
            document.getElementById('statusModal').classList.add('hidden');
            updateTable();
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        Toastify({
            text: error.message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#F44336"
        }).showToast();
    })
    .finally(() => {
        // Re-enable all buttons and restore original content
        buttons.forEach(button => {
            button.disabled = false;
            const originalContent = button.getAttribute('data-original-content');
            if (originalContent) {
                button.innerHTML = originalContent;
            }
        });
    });
}

    // Add modal close handler
    document.getElementById('closeModal').addEventListener('click', function() {
        document.getElementById('statusModal').classList.add('hidden');
    });

    </script>


    <script>
        // Function to handle search and filter
        function updateTable(searchTerm = '', statusFilter = '', page = 1, entries = document.getElementById('entriesPerPage').value) {
            const tableBody = document.getElementById('admin-reservation-table-body');
            
            // Show loading state
            tableBody.innerHTML = '<tr><td colspan="12" class="text-center py-4">Loading...</td></tr>';

            // Fetch filtered and searched data
            fetch(`reservationAdmin.php?search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(statusFilter)}&page=${page}&entries=${entries}`)
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = ''; // Clear loading state
                    
                    if (data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="12" class="text-center py-4">No reservations found</td></tr>';
                        return;
                    }

                    data.forEach(reservation => {
                        const row = `
                            <tr>
                                <td class="py-2 px-4 border-b">
                                    <input type="checkbox" class="select-row" value="${reservation.reservation_id}">
                                </td>
                                <td class="px-4 py-4 border-b text-center">${reservation.reservation_id}</td>
                                <td class="px-4 py-4 border-b text-center">${reservation.name}</td>
                                <td class="px-4 py-4 border-b text-center">${reservation.email}</td>
                                <td class="px-4 py-4 border-b text-center">${reservation.unit_no}</td>
                                <td class="px-4 py-4 border-b text-center">${reservation.unit_type}</td>
                                <td class="px-4 py-4 border-b text-center">₱${Number(reservation.monthly_rent).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                <td class="px-4 py-4 border-b text-center">${reservation.square_meter}</td>
                                <td class="px-4 py-4 border-b text-center">${reservation.viewing_date}</td>
                                <td class="px-4 py-4 border-b text-center">${new Date('1970-01-01T' + reservation.viewing_time).toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true})}</td>
                                <td class="px-4 py-4 border-b text-center">${reservation.status}</td>
                                <td class="px-4 py-4 border-b text-center">
                                 <div class="flex justify-center items-center space-x-2">
                                   <button onclick="editReservation(${reservation.reservation_id})" 
                                            class="bg-blue-500 text-white px-3 py-1 rounded-md inline-flex items-center space-x-1">
                                            <i data-feather="edit-2" class="w-4 h-4"></i>
                                            <span>Edit</span>
                                    </button>

                                    <button onclick="archiveReservation(${reservation.reservation_id})" 
                                            class="bg-red-500 text-white px-3 py-1 rounded-md inline-flex items-center space-x-1">
                                            <i data-feather="archive" class="w-4 h-4"></i>
                                            <span>Archive</span>
                                   </button>
                                </div>
                                </td>
                            </tr>
                        `;
                        tableBody.insertAdjacentHTML('beforeend', row);
                    });

                    feather.replace();
                })
                .catch(error => {
                    console.error('Error:', error);
                    tableBody.innerHTML = '<tr><td colspan="12" class="text-center py-4 text-red-500">Error loading data</td></tr>';
                });
        }

        // Handle search input with debounce
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                updateTable(e.target.value, document.getElementById('admin-status-filter').value);
            }, 300);
        });

        // Handle status filter
        document.getElementById('admin-status-filter').addEventListener('change', function(e) {
            updateTable(document.getElementById('search').value, e.target.value);
        });

        // Add entries per page change handler
        function changeEntries(value) {
            window.location.href = `?entries=${value}&page=1`;
        }

    </script>

    <script>

        // Function to archive a single reservation

        function archiveReservation(reservationId) {
        if (!confirm('Are you sure you want to archive this reservation?')) return;

        fetch('archive_reservation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                reservation_ids: [reservationId]
            })
        })
        .then(async response => {
            // Log raw response for debugging
            const text = await response.text();
            console.log('Raw response:', text);
            
            try {
                // Try parsing the response text as JSON
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                console.error('JSON Parse Error:', e);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            if (data.success) {
                Toastify({
                    text: "Reservation archived successfully",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#4CAF50"
                }).showToast();
                updateTable(
                    document.getElementById('search').value,
                    document.getElementById('admin-status-filter').value
                );
            } else {
                throw new Error(data.message || 'Failed to archive reservation');
            }
        })
        .catch(error => {
            console.error('Archive error:', error);
            Toastify({
                text: error.message,
                duration: 3000,
                gravity: "top", 
                position: "right",
                backgroundColor: "#F44336"
            }).showToast();
        });
    }

        // Handle bulk archive
        document.getElementById('bulk-archive').addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.select-row:checked'))
                .map(checkbox => checkbox.value);

            if (selectedIds.length === 0) {
                Toastify({
                    text: "Please select at least one reservation to archive",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#F44336"
                }).showToast();
                return;
            }

            if (!confirm(`Are you sure you want to archive ${selectedIds.length} reservation(s)?`)) return;

            fetch('archive_reservation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    reservation_ids: selectedIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Toastify({
                        text: `${selectedIds.length} reservation(s) archived successfully`,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#4CAF50"
                    }).showToast();
                    updateTable(document.getElementById('search').value, document.getElementById('admin-status-filter').value);
                    document.getElementById('select-all').checked = false;
                } else {
                    throw new Error(data.message || 'Failed to archive reservations');
                }
            })
            .catch(error => {
                Toastify({
                    text: error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#F44336"
                }).showToast();
            });
        });

        // Initial table load
        updateTable();

    </script>

</body>
</html>