<?php

// Include the database connection
require '../session/db.php';
require_once '../session/session_manager.php';

start_secure_session();


// Check if the staff member is logged in
if (!isset($_SESSION['staff_id'])) {
    header('Location: ../authentication/stafflogin.php');
    exit();
}

// Get the logged-in staff member's ID
$staffId = $_SESSION['staff_id'];

// Fetch maintenance requests assigned to the logged-in staff member
$query = "SELECT id, unit, issue, description, service_date, image, status 
          FROM maintenance_requests 
          WHERE archived = 0 AND assigned_to = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $staffId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query failed: " . $conn->error);
}

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
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
    <title>Work Orders</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        /* Optional: Custom styles for smooth transitions */
        .transition-transform {
            transition: transform 0.3s ease;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body> 

<!-- Include Navbar -->
<?php include('staffNavbar.php'); ?>

<!-- Include Sidebar -->
<?php include('staffSidebar.php'); ?>

<div class="sm:ml-64 p-4 sm:p-8 mx-auto">
    <div class="mt-16 sm:mt-20">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4 sm:mb-6">Work Orders</h1>

        <!-- Filter and Search Section -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <!-- Status Filter -->
            <div class="relative w-full sm:w-48">
                <select id="statusFilter" class="w-full p-2 border rounded-lg text-sm sm:text-base">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full sm:w-1/4">
                <input type="text" id="search-keyword" placeholder="Search..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                <button type="button" id="search-button" class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                    <svg data-feather="search" class="w-4 h-4"></svg>
                </button>
            </div>
        </div>

           
        <!-- Modal -->
         <div id="reportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 p-4">
                <div class="relative mx-auto p-4 sm:p-5 border w-full max-w-[95%] sm:max-w-[80%] md:max-w-[600px] shadow-lg rounded-md bg-white my-8">
                    <div class="mt-2 sm:mt-3">
                        <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900 mb-3 sm:mb-4">Submit Maintenance Report</h3>
                        <form id="reportForm" class="mt-3 sm:mt-4" enctype="multipart/form-data">
                            <input type="hidden" id="requestId" name="requestId">

                           <!-- Pre-filled Request Details Section -->
                            <div class="mb-3 sm:mb-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="modalUnit" class="block text-sm font-medium text-gray-700 text-left mb-1">Unit</label>
                                        <input type="text" id="modalUnit" name="modalUnit" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none text-sm bg-gray-100 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label for="modalServiceDate" class="block text-sm font-medium text-gray-700 text-left mb-1">Service Date</label>
                                        <input type="text" id="modalServiceDate" name="modalServiceDate" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none text-sm bg-gray-100 cursor-not-allowed">
                                    </div>
                                    <div class="col-span-2">
                                        <label for="modalIssue" class="block text-sm font-medium text-gray-700 text-left mb-1">Issue</label>
                                        <input type="text" id="modalIssue" name="modalIssue" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none text-sm bg-gray-100 cursor-not-allowed">
                                    </div>
                                    <div class="col-span-2">
                                        <label for="modalDescription" class="block text-sm font-medium text-gray-700 text-left mb-1">Description</label>
                                        <textarea id="modalDescription" name="modalDescription" readonly rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none text-sm bg-gray-100 cursor-not-allowed"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Update -->
                            <div class="mb-3 sm:mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700 text-left mb-1">Status Update</label>
                                <select id="status" name="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <option value="Pending">Pending</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>

                            <!-- Action Taken -->
                            <div class="mb-3 sm:mb-4">
                                <label for="actionTaken" class="block text-sm font-medium text-gray-700 text-left mb-1">Action Taken</label>
                                <textarea id="actionTaken" name="actionTaken" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                            </div>

                            <!-- Materials Used -->
                            <div class="mb-3 sm:mb-4">
                                <label for="materialsUsed" class="block text-sm font-medium text-gray-700 text-left mb-1">Materials Used</label>
                                <textarea id="materialsUsed" name="materialsUsed" rows="3" placeholder="List materials used for the maintenance work" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                            </div>

                            <!-- Materials Official Receipt Upload -->
                            <div class="mb-3 sm:mb-4">
                                <label for="receiptImage" class="block text-sm font-medium text-gray-700 text-left mb-1">Materials Official Receipt</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center w-full">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex justify-center items-center text-sm text-gray-600 w-full">
                                            <label for="receiptImage" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload receipt</span>
                                                <input id="receiptImage" name="receiptImage" type="file" class="sr-only" accept="image/png, image/jpeg, image/gif">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                        <!-- Receipt file name display container -->
                                        <div id="receiptNameContainer" class="mt-2 text-sm text-gray-700"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Completion Date -->
                            <div class="mb-3 sm:mb-4">
                                <label for="completionDate" class="block text-sm font-medium text-gray-700 text-left mb-1">Completion Date</label>
                                <input type="datetime-local" id="completionDate" name="completionDate" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                           <!-- Upload Images -->
                            <div class="mb-3 sm:mb-4">
                                <label for="uploadImages" class="block text-sm font-medium text-gray-700 text-left mb-1">Upload Images</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center w-full">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex justify-center items-center text-sm text-gray-600 w-full">
                                            <label for="uploadImages" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload files</span>
                                                <input id="uploadImages" name="uploadImages[]" type="file" class="sr-only" multiple accept="image/png, image/jpeg, image/gif">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                        
                                        <!-- File name display container -->
                                        <div id="fileNameContainer" class="mt-2 text-sm text-gray-700 space-y-1 max-h-24 overflow-y-auto"><!-- File names will be dynamically added here --></div>
                                
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 sm:mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                                <button type="button" id="closeModalBtn" class="w-full sm:w-auto px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200 text-sm">Cancel</button>
                                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200 text-sm">Submit Report</button>
                            </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Work Orders Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200 uppercase">Unit</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200 uppercase">Issue</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200 uppercase">Description</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200 uppercase">Service Date</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200 uppercase">Image</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200 uppercase">Status</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="workOrdersTableBody" class="bg-white divide-y divide-gray-200">
                        <?php foreach ($requests as $request): ?>
                            <?php
                            // Determine status color
                            $statusColor = '';
                            if ($request['status'] == 'Pending') {
                                $statusColor = 'bg-gray-100 text-gray-800';
                            } elseif ($request['status'] == 'In Progress') {
                                $statusColor = 'bg-yellow-100 text-yellow-800';
                            } elseif ($request['status'] == 'Completed') {
                                $statusColor = 'bg-green-100 text-green-800';
                            }

                            // Format the date
                            $formattedDate = date('F j, Y', strtotime($request['service_date']));
                            ?>
                            <tr>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($request['unit']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($request['issue']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($request['description']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($formattedDate); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm">
                                    <?php if ($request['image']) : ?>
                                        <a href="../users/<?php echo htmlspecialchars($request['image']); ?>" target="_blank" class="text-blue-600">View Image</a>
                                    <?php else : ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                        <?php echo htmlspecialchars($request['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm">
                                    <button onclick="viewRequest(<?= $request['id'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="openReportModal(<?= $request['id'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="archiveRequest(<?= $request['id'] ?>)" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
    </div>
</div>

<script src="../node_modules/feather-icons/dist/feather.min.js"></script>


<script>

  // Function to close modal
  function closeModal() {
        document.getElementById('reportModal').classList.add('hidden');
        document.getElementById('reportForm').reset();
    }

    function openReportModal(requestId) {
        // Find the row corresponding to the requestId
        const row = document.querySelector(`tr button[onclick="openReportModal(${requestId})"]`).closest('tr');
        
        // Pre-fill modal details
        document.getElementById('requestId').value = requestId;
        document.getElementById('modalUnit').value = row.querySelector('td:nth-child(1)').textContent;
        document.getElementById('modalServiceDate').value = row.querySelector('td:nth-child(4)').textContent;
        document.getElementById('modalIssue').value = row.querySelector('td:nth-child(2)').textContent;
        document.getElementById('modalDescription').value = row.querySelector('td:nth-child(3)').textContent;
        
        // Open the modal
        document.getElementById('reportModal').classList.remove('hidden');
    }


    // Event listener for close button
    document.getElementById('closeModalBtn').addEventListener('click', closeModal);

    // Close modal when clicking outside
    document.getElementById('reportModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeModal();
        }
    });

    
    // Display the selected file name when a file is selected

    document.getElementById('uploadImages').addEventListener('change', function(event) {
    const fileNameContainer = document.getElementById('fileNameContainer');
    fileNameContainer.innerHTML = ''; // Clear previous file names

    const files = Array.from(event.target.files);

    // Validate and display file names
    const validFiles = files.filter(file => {
        const validTypes = ['image/png', 'image/jpeg', 'image/gif'];
        const maxSize = 10 * 1024 * 1024; // 10MB

        if (!validTypes.includes(file.type)) {
            alert('Invalid file type. Only PNG, JPG, and GIF are allowed.');
            return false;
        }

        if (file.size > maxSize) {
            alert('File is too large. Maximum size is 10MB.');
            return false;
        }

        return true;
    });

    // Display file names
    if (validFiles.length > 0) {
        const fileNameList = validFiles.map(file => 
            `<div class="bg-gray-100 px-2 py-1 rounded flex justify-between items-center break-words">
            <span class="truncate flex-grow mr-2">${file.name}</span>
            <button type="button" class="text-red-500 remove-file shrink-0" data-filename="${file.name}">&times;</button>
            </div>`
        ).join('');

        fileNameContainer.innerHTML = fileNameList;

            // Add event listeners to remove buttons
            document.querySelectorAll('.remove-file').forEach(button => {
                button.addEventListener('click', function() {
                    const filename = this.getAttribute('data-filename');
                    const input = document.getElementById('uploadImages');
                    const dt = new DataTransfer();
                    
                    Array.from(input.files)
                        .filter(file => file.name !== filename)
                        .forEach(file => dt.items.add(file));
                    
                    input.files = dt.files;
                    
                    // Trigger change event to refresh display
                    input.dispatchEvent(new Event('change'));
                });
            });
        }
    });


    // Update the form submission handler with the new version
    document.getElementById('reportForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = 'Submitting...';
        
        try {
            const formData = new FormData(this);
            const response = await fetch('submit_maintenance_report.php', {
                method: 'POST',
                body: formData
            });

            // Get the response text first
            const responseText = await response.text();
            
            // Try to parse it as JSON
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Server response:', responseText);
                throw new Error('Invalid JSON response from server');
            }

            // Check if the request was successful
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }

            if (data.success) {
                alert('Report submitted successfully!');
                closeModal();
                location.reload();
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        } catch (error) {
            console.error('Submission error:', error);
            alert('Error submitting report: ' + error.message);
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Submit Report';
        }
    });


    // Function to view request details
    function viewRequest(requestId) {
        // Open the PDF report in a new tab
        window.open('view_report.php?id=' + requestId, '_blank');
    }

    // Function to archive request
    function archiveRequest(requestId) {
        if (confirm('Are you sure you want to archive this request?')) {
            fetch('archive_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'requestId=' + requestId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Request archived successfully');
                    // Remove the row from the table
                    const row = document.querySelector(`tr button[onclick="archiveRequest(${requestId})"]`).closest('tr');
                    row.remove();
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to archive request: ' + error.message);
            });
        }
    }

    // Add this to your existing script section
    document.getElementById('receiptImage').addEventListener('change', function(event) {
        const fileNameContainer = document.getElementById('receiptNameContainer');
        fileNameContainer.innerHTML = '';

        const file = event.target.files[0];
        if (file) {
            const validTypes = ['image/png', 'image/jpeg', 'image/gif'];
            const maxSize = 10 * 1024 * 1024; // 10MB

            if (!validTypes.includes(file.type)) {
                alert('Invalid file type. Only PNG, JPG, and GIF are allowed.');
                this.value = '';
                return;
            }

            if (file.size > maxSize) {
                alert('File is too large. Maximum size is 10MB.');
                this.value = '';
                return;
            }

            fileNameContainer.innerHTML = `
                <div class="bg-gray-100 px-2 py-1 rounded flex justify-between items-center">
                    <span class="truncate">${file.name}</span>
                    <button type="button" class="text-red-500" onclick="clearReceipt()">&times;</button>
                </div>`;
        }
    });

    function clearReceipt() {
        document.getElementById('receiptImage').value = '';
        document.getElementById('receiptNameContainer').innerHTML = '';
    }

</script>

<script>
    feather.replace();

    // Function to filter tasks based on status and search keyword
    function filterTasks() {
        const statusFilter = document.getElementById('statusFilter').value.trim().toLowerCase();
        const searchKeyword = document.getElementById('search-keyword').value.trim().toLowerCase();

        const rows = document.querySelectorAll('#workOrdersTableBody tr');

        rows.forEach(row => {
            const statusElement = row.querySelector('td:nth-child(6) span');
            const status = statusElement ? statusElement.textContent.trim().toLowerCase() : '';


            const issue = row.querySelector('td:nth-child(2)').textContent.trim().toLowerCase(); // Issue is in the 2nd column
            const description = row.querySelector('td:nth-child(3)').textContent.trim().toLowerCase(); // Description is in the 3rd column

            // Check if the row matches the selected status and search keyword
            const matchesStatus = statusFilter === '' || status === statusFilter;
            const matchesKeyword = searchKeyword === '' || issue.includes(searchKeyword) || description.includes(searchKeyword);

            // Show or hide the row based on the filters
            if (matchesStatus && matchesKeyword) {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
        });
    }

    // Add event listeners for the status filter and search input
    document.getElementById('statusFilter').addEventListener('change', filterTasks);
    document.getElementById('search-keyword').addEventListener('input', filterTasks);

    // Initial filter to apply any default filters
    filterTasks();
    
</script>

</body>
</html>