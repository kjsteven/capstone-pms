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

// Pagination settings
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;

// Get total number of records
$totalQuery = "SELECT COUNT(*) as total FROM maintenance_requests WHERE archived = 0";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $entriesPerPage);

// Modify the main query to include pagination
$query = "
    SELECT 
        mr.id, 
        u.name AS user_name, 
        mr.unit, 
        mr.issue, 
        mr.description, 
        mr.service_date, 
        mr.image,
        mr.report_pdf, 
        s.name AS staff_name, 
        mr.status
    FROM maintenance_requests mr
    JOIN users u ON mr.user_id = u.user_id
    LEFT JOIN staff s ON mr.assigned_to = s.staff_id
    WHERE mr.archived = 0
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $entriesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$toast_message = null;
$toast_type = null;

// For AJAX requests, handle them first before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Ensure no previous output
    ob_clean(); 

    // Set JSON content type
    header('Content-Type: application/json');

    // Validate and process the request
    if (isset($_POST['update_status']) && isset($_POST['status']) && isset($_POST['request_id'])) {
        $status = $_POST['status'];
        $request_id = intval($_POST['request_id']);

        // Prepare and execute update
        $update_query = "UPDATE maintenance_requests SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);

        if ($stmt) {
            $stmt->bind_param("si", $status, $request_id);

            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => "Status updated successfully!",
                    'newStatus' => $status
                ]);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => "Failed to update status: " . $stmt->error
                ]);
            }
            $stmt->close();
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => "Failed to prepare statement: " . $conn->error
            ]);
        }
        exit; // Critical to prevent any additional output
    }

    // If we reach here, it means the request was invalid
    echo json_encode([
        'status' => 'error', 
        'message' => "Invalid request"
    ]);
    exit;
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
<h1 class="text-xl font-semibold text-gray-800 mb-6">Maintenance Requests Management</h1>

<!-- Users Tab Content -->
<div class="tab-content block">
        <!-- Search Bar, Entries Selection and Print Button -->
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
            <div class="relative w-full sm:w-1/3">
                <input type="text" id="search-keyword" placeholder="Search by Name..." 
                       class="w-full p-2 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300">
                <button type="button" id="search-button" 
                        class="absolute right-0 top-0 h-full px-3 bg-blue-600 text-white rounded-r-lg">
                    <svg data-feather="search" class="w-4 h-4"></svg>
                </button>
            </div>
            <button id="print-button" class="px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2">
                <svg data-feather="printer" class="w-4 h-4"></svg>
                Print
            </button>
        </div>

         <!-- Table Form -->
         <div class="overflow-x-auto shadow-lg rounded-lg print-section">
            <table class="min-w-full bg-white" id="users-table">
                <thead>
                    <tr>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Request ID</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Unit No</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Issue</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Service Date</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Report</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Assign To</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['id']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['user_name']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['unit']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['issue']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['description']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['service_date']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200">
                                <?php if ($row['image']) : ?>
                                    <a href="../users/<?php echo htmlspecialchars($row['image']); ?>" target="_blank" class="text-blue-600">View Image</a>
                                <?php else : ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 border-b border-gray-200">
                                <?php if ($row['report_pdf']) : ?>
                                    <div class="flex flex-col space-y-2">
                                        <a href="../reports/maintenance_reports/<?php echo htmlspecialchars($row['report_pdf']); ?>" 
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200">
                                            <i class="fas fa-eye mr-2"></i>
                                            <span class="text-sm">View Report</span>
                                        </a>
                                        <a href="download_report.php?file=<?php echo urlencode($row['report_pdf']); ?>" 
                                           class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors duration-200">
                                            <i class="fas fa-download mr-2"></i>
                                            <span class="text-sm">Download</span>
                                        </a>
                                    </div>
                                <?php else : ?>
                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 rounded-md">
                                        <i class="fas fa-file-alt mr-2"></i>
                                        <span class="text-sm">No Report</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['staff_name'] ? $row['staff_name'] : 'Not Assigned'; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200">
                               
                            <!-- Status and Update Button -->
                            <form method="POST" class="update-form">
                                <div class="flex items-center space-x-4">
                                    <!-- Dropdown for Status -->
                                    <select name="status" 
                                            class="px-3 py-1 border border-gray-300 rounded-md w-40 text-center mt-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="In Progress" <?php echo $row['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="Completed" <?php echo $row['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>

                                    <!-- Hidden Input -->
                                    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">

                                    <!-- Update Button -->
                                    <button type="submit" name="update_status" 
                                            class="px-3 py-1 bg-green-500 text-white rounded-md mt-4 hover:bg-blue-600 focus:outline-none">
                                        Update
                                    </button>
                                </div>
                            </form>


                            </td>

                            <td class="px-6 py-3 border-b border-gray-200">
                                <div class="flex space-x-2">
                                    <button type="submit" name="update_status" value="Update" class="bg-blue-600 text-white edit-btn px-3 py-1 rounded-md inline-flex items-center">
                                        <i data-feather="edit-2" class="w-4 h-4 mr-1"></i>
                                        Edit
                                    </button>
                                    <a href="archive_request.php?id=<?php echo $row['id']; ?>" class="bg-red-600 text-white px-3 py-1 rounded-md inline-flex items-center">
                                        <i data-feather="archive" class="w-4 h-4 mr-1"></i>
                                        Archive
                                    </a>
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

<!-- Modal for Editing -->
<div id="edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50">
    <!-- Modal Container -->
    <div class="bg-white rounded-lg p-4 sm:p-6 w-11/12 sm:w-5/6 md:w-2/3 lg:w-1/2 xl:w-1/3">
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg sm:text-xl font-semibold">Edit Maintenance Request</h2>
            <button type="button" class="text-gray-500 hover:text-gray-700" id="close-modal">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Modal Form -->
        <form id="edit-form" class="space-y-4">
            <!-- Hidden Input for Request ID -->
            <input type="hidden" name="request_id" id="edit-request-id">

            <!-- Priority Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority Level</label>
                <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="priority" value="high" class="form-radio text-red-600">
                        <span class="ml-2 text-sm text-gray-700">High Priority</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="priority" value="medium" class="form-radio text-yellow-600">
                        <span class="ml-2 text-sm text-gray-700">Medium Priority</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="priority" value="low" class="form-radio text-green-600">
                        <span class="ml-2 text-sm text-gray-700">Low Priority</span>
                    </label>
                </div>
            </div>

            <!-- Staff Assignment -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Assign to Staff Member</label>
                <select name="staff_id" id="edit-staff" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Staff</option>
                </select>
            </div>

            <!-- Form Buttons -->
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors" id="cancel-btn">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

</div>

<script src="../node_modules/feather-icons/dist/feather.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>


<!-- Initialize Feather Icons, Update Request Status -->
<script>
    // Initialize Feather Icons
    feather.replace();

    function initializeToastify(message, type) {
        if (message) {
            Toastify({
                text: message,
                backgroundColor: type === 'info' 
                    ? 'linear-gradient(to right, #00b09b, #96c93d)' 
                    : 'linear-gradient(to right, #ff5f6d, #ffc371)',
                className: type,
                duration: 3000
            }).showToast();
        }
    }

    // Get all forms with the update-form class
    const updateForms = document.querySelectorAll('.update-form');

    updateForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            formData.append('update_status', '1'); // Explicitly add update_status

            const statusSelect = this.querySelector('select[name="status"]');
            const row = this.closest('tr');

            fetch('', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    const statusSelectedValue = formData.get('status');
                    statusSelect.value = statusSelectedValue;

                    // Update all select elements in the row
                    const selectElements = row.querySelectorAll('select[name="status"]');
                    selectElements.forEach(select => {
                        select.value = statusSelectedValue;
                    });

                    initializeToastify(data.message, data.type);
                } else {
                    initializeToastify(data.message, data.type);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                initializeToastify(`Failed to update status: ${error.message}`, 'error');
            });
        });
    });
</script>


<!-- Search, Print Functionality, Edit Request -->


<script>
    
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("edit-modal");
    const editForm = document.getElementById("edit-form");
    const staffDropdown = document.getElementById("edit-staff");

    // Event listener for edit buttons
    document.querySelectorAll(".edit-btn").forEach((button) => {
        button.addEventListener("click", (event) => {
            event.preventDefault();

            const row = button.closest("tr");
            const requestId = row.querySelector("td:first-child").textContent.trim();

            document.getElementById("edit-request-id").value = requestId;

            // Show the modal
            modal.style.display = "flex";

            // Fetch staff options
            fetch("get_staff.php")
                .then((res) => res.json())
                .then((data) => {
                    if (data.status === "success") {
                        staffDropdown.innerHTML = '<option value="">Select Staff</option>';
                        data.data.forEach((staff) => {
                            const option = document.createElement("option");
                            option.value = staff.staff_id;
                            option.textContent = `${staff.name} (${staff.specialty})`; // Display name and specialty
                            staffDropdown.appendChild(option);
                        });
                    } else {
                        alert("Failed to load staff options.");
                    }
                })
                .catch((err) => {
                    console.error("Error fetching staff:", err);
                    alert("An error occurred while fetching staff data.");
                });
        });
    });

    // Hide modal on cancel
    document.getElementById("cancel-btn").addEventListener("click", () => {
        modal.style.display = "none";
    });

    // Close modal on click outside
    document.getElementById("close-modal").addEventListener("click", () => {
        modal.style.display = "none";
    });



    // Handle form submission
    editForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const requestId = document.getElementById("edit-request-id").value;
        const staffId = document.getElementById("edit-staff").value; // Only staff field is needed

        // Log values for debugging
        console.log("Request ID:", requestId);
        console.log("Staff ID:", staffId);

        // Prepare form data
        const formData = new FormData(editForm);

        // Submit the form to update request
        fetch("update_request.php", {
            method: "POST",
            body: formData,
        })
        .then((res) => res.json())
        .then((data) => {
            console.log("Response from PHP:", data);
            if (data.status === "success") {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch((err) => {
            console.error("Error updating request:", err);
            alert(err.message || "An error occurred while updating the request.");
        });
    });
});

</script>





<!-- Archive Request -->

<script>
    
document.querySelectorAll('a[href^="archive_request.php"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to archive this request?')) {
            return;
        }

        const requestId = this.href.split('?id=')[1];
        const row = this.closest('tr');

        fetch(`archive_request.php?id=${requestId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Toastify({
                    text: data.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                    stopOnFocus: true
                }).showToast();

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toastify({
                text: error.message || "An error occurred while archiving the request",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                stopOnFocus: true
            }).showToast();
        });
});

});

</script>

<script>
// Add entries per page change handler
function changeEntries(value) {
    window.location.href = `?entries=${value}&page=1`;
}

// Print functionality
document.getElementById('print-button').addEventListener('click', function() {
    // Add a title before printing
    const title = document.createElement('h2');
    title.className = 'text-xl font-bold mb-4 print-section';
    title.style.textAlign = 'center';
    title.innerText = 'Maintenance Requests Report';
    
    const table = document.querySelector('.print-section');
    table.parentNode.insertBefore(title, table);
    
    window.print();
    
    // Remove the title after printing
    title.remove();
});
</script>

</body>
</html>