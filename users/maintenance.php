    <?php

    require_once '../session/session_manager.php';
    require '../session/db.php';

    start_secure_session();

    $user_id = $_SESSION['user_id']; 

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If not logged in, redirect to login page
        header('Location: ../authentication/login.php'); // Adjust the path as necessary
        exit();
    }



    $query = " SELECT unit, issue, description, service_date, status, image
    FROM maintenance_requests WHERE user_id = $user_id";

    $result = mysqli_query($conn, $query);


    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Maintenance Request</title>
        <link rel="icon" href="../images/logo.png" type="image/png">
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

        <style>
            body {
                font-family: 'Poppins', sans-serif;
            }
            .hidden-tab {
                display: none;
            }

            .file-upload-label {
                transition: background-color 0.3s ease, border-color 0.3s ease;
            }

            .file-upload-label:hover {
                background-color: #f0f8ff; /* Light blue */
                border-color: #007bff; /* Blue */
            }

            .file-upload-label.file-upload-success {
                background-color: #e6ffed; /* Light green */
                border-color: #28a745; /* Green */
                color: #28a745; /* Green text */
            }

            .file-upload-label.file-upload-success .file-upload-text {
                font-weight: bold;
            }

        </style>

    </head>
    <body class="bg-gray-100">

        <!-- Include Navbar -->
        <?php include('navbar.php'); ?>

        <!-- Include Sidebar -->
        <?php include('sidebar.php'); ?>

        <!-- Main Content -->
        <div class="sm:ml-64 p-8 mt-20">
            <!-- Tabs Navigation -->
            <div class="flex mb-6 border-b">
                <button id="tab-request" class="py-2 px-4 text-gray-700 focus:outline-none border-b-4 border-blue-600">Submit a Maintenance Request</button>
                <button id="tab-history" class="py-2 px-4 text-gray-700 focus:outline-none ml-4 border-b-4 border-transparent hover:border-blue-600">Request History</button>
            </div>

            <div id="request-content" class="bg-white shadow-lg rounded-lg p-6">
                <form id="maintenance-form" action="submit_request.php" method="POST" enctype="multipart/form-data" onsubmit="handleSubmit(event)">
                
                <!-- Unit Selection -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="unit">Unit No</label>
                        <select id="unit" name="unit" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" required>
                            <option value="" disabled selected>Select your unit number</option>
                            <?php
                                // Fetching units rented by the user
                                $unit_query = "SELECT p.unit_no
                                            FROM tenants t
                                            JOIN property p ON t.unit_rented = p.unit_id
                                            WHERE t.user_id = ?";
                            
                                $unit_stmt = mysqli_prepare($conn, $unit_query);
                                mysqli_stmt_bind_param($unit_stmt, 'i', $user_id);
                                mysqli_stmt_execute($unit_stmt);
                                $unit_result = mysqli_stmt_get_result($unit_stmt);

                                if ($unit_result) {
                                    while ($unit_row = mysqli_fetch_assoc($unit_result)) {
                                        echo '<option value="' . htmlspecialchars($unit_row['unit_no']) . '">' . htmlspecialchars($unit_row['unit_no']) . '</option>';
                                    }
                                } else {   
                                    echo '<option value="">No units found</option>';
                                }
                            ?>
                        </select>
                    </div>

                    <!-- Issue Selection -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="issue">Issue</label>
                        <select id="issue" name="issue" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" required>
                            <option value="" disabled selected>Select the issue</option>
                            <option value="Leaking Faucet">Leaking Faucet</option>
                            <option value="Broken Window">Broken Window</option>
                            <option value="Heating Issue">Heating Issue</option>
                            <option value="Electrical Problem">Electrical Problem</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Issue Description -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="issue-description">Describe the issue</label>
                        <input id="issue-description" name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" placeholder="Describe the issue you're experiencing..." style="max-height: 200px; overflow-y: auto;" required></input>
                    </div>

                    <!-- Preferred Service Date -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="service-date">Preferred Date for Service</label>
                        <input type="date" id="service-date" name="service_date" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" required>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Upload Images (Required)</label>
                        <div class="file-upload-container">
                            <label for="file_upload" class="file-upload-label w-full h-32 flex flex-col items-center justify-center border-2 border-dashed border-gray-300 text-gray-600 rounded-lg cursor-pointer">
                                <i class="fas fa-upload text-2xl mb-2"></i>
                                <span class="file-upload-text">Click to upload or drag and drop</span>
                                <input id="file_upload" type="file" name="file_upload" class="hidden" required />
                                <span class="text-sm text-gray-500">(JPEG, PNG, GIF, max 5MB)</span>
                            </label>
                        </div>
                    </div>


                    <!-- Submit Button -->
                    <div class="flex justify-between">
                        <button type="reset" class="text-gray-700 border border-gray-400 rounded-lg px-4 py-2">Cancel</button>
                        <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 py-2">Submit Request</button>
                    </div>
                </form>
            </div>

                
    <!-- Request History Section -->
    <div id="history-content" class="hidden-tab bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-6 text-left">Request History</h2>

        <!-- Search and Filter Section -->
        <div class="mb-4 flex items-center space-x-2">
            <!-- Status Filter -->
            <div class="relative">
                <select id="status-filter" class="border border-gray-300 rounded-lg px-4 py-2 pr-8 outline-none appearance-none">
                    <option value="">All Status</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Pending">Pending</option>
                </select>
                <span class="absolute inset-y-0 right-2 flex items-center pointer-events-none text-gray-500">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </div>

            <!-- Keyword Search -->
            <div class="relative w-full sm:w-1/4">
                <input type="text" id="search-keyword" placeholder="Search..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                    <i data-feather="search" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

    <div class="overflow-x-auto shadow-md rounded-lg">
        <table class="min-w-full border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="py-2 px-4 border text-center align-middle">Unit No</th>
                    <th class="py-2 px-4 border text-center align-middle">Issue</th>
                    <th class="py-2 px-4 border text-center align-middle">Description</th>
                    <th class="py-2 px-4 border text-center align-middle">Date for Service</th>
                    <th class="py-2 px-4 border text-center align-middle">Status</th>
                    <th class="py-2 px-4 border text-center align-middle">Image</th>
                    <th class="py-2 px-4 border text-center align-middle">Actions</th>
                </tr>
            </thead>
            <tbody id="request-table-body">
                    
                    <?php
                    if (isset($result) && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) :
                          
                            // Set the text color based on the status
                            $status_color = '';
                            if ($row['status'] == 'In Progress') {
                                $status_color = 'text-fuchsia-600';
                            } elseif ($row['status'] == 'Completed') {
                                $status_color = 'text-green-500';
                            } elseif ($row['status'] == 'Pending') {
                                $status_color = 'text-amber-600';
                            }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b border-gray-300 text-center"><?php echo htmlspecialchars($row['unit']); ?></td>
                            <td class="px-4 py-2 border-b border-gray-300 text-center"><?php echo htmlspecialchars($row['issue']); ?></td>
                            <td class="px-4 py-2 border-b border-gray-300 text-center"><?php echo htmlspecialchars($row['description']); ?></td>
                            <td class="px-4 py-2 border-b border-gray-300 text-center"><?php echo htmlspecialchars($row['service_date']); ?></td>
                            <td class="px-4 py-2 border-b border-gray-300 text-center">
                                <span class="<?php echo $status_color; ?>"><?php echo ucfirst($row['status']); ?></span>
                            </td>
                            <td class="px-4 py-2 border-b border-gray-300 text-center">
                                <?php if ($row['image']) : ?>
                                    <a href="<?php echo htmlspecialchars($row['image']); ?>" target="_blank" class="text-blue-600">View Image</a>
                                <?php else : ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td class="py-2 px-4 text-center border-b flex items-center justify-center gap-4">
                              
                                   <!-- Edit Button -->
                                <button class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-medium py-1 px-2 rounded-md cancel-btn flex items-center gap-2">
                                    <i data-feather="edit-2" class="w-4 h-4" ></i> Edit
                                </button>

                                <!-- Archive Button -->
                                <button class="bg-red-500 hover:bg-red-700 text-white text-sm font-medium py-1 px-2 rounded-md archive-btn flex items-center gap-2">
                                    <i data-feather="archive" class="w-4 h-4" ></i> Archive
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No request history found</td></tr>";
                    }
                    ?>
            </tbody>

        </table>
    </div>
</div>


 </div>


<script>
            // JavaScript for tab navigation
            const tabRequest = document.getElementById('tab-request');
            const tabHistory = document.getElementById('tab-history');
            const requestContent = document.getElementById('request-content');
            const historyContent = document.getElementById('history-content');

            tabRequest.addEventListener('click', () => {
                requestContent.classList.remove('hidden-tab');
                historyContent.classList.add('hidden-tab');
                tabRequest.classList.add('border-blue-600');
                tabHistory.classList.remove('border-blue-600');
            });

            tabHistory.addEventListener('click', () => {
                historyContent.classList.remove('hidden-tab');
                requestContent.classList.add('hidden-tab');
                tabHistory.classList.add('border-blue-600');
                tabRequest.classList.remove('border-blue-600');
            });



       
    // Add event listener for form submission
    document.querySelector("form").addEventListener("submit", function(event) {
        // Get the form elements
        var unit = document.getElementById("unit");
        var issue = document.getElementById("issue");
        var description = document.getElementById("issue-description");
        var serviceDate = document.getElementById("service-date");
        var fileUpload = document.getElementById("file_upload");

        // Check if all fields are filled
        if (unit.value === "" || issue.value === "" || description.value.trim() === "" || serviceDate.value === "" || fileUpload.files.length === 0) {
            // Prevent form submission
            event.preventDefault();

            // Alert user
            alert("Please fill in all the required fields.");
        }
    });




    // File upload animation
    const fileUploadInput = document.getElementById('file_upload');
    const fileUploadLabel = document.querySelector('.file-upload-label');
    const fileUploadText = fileUploadLabel.querySelector('.file-upload-text');

    fileUploadInput.addEventListener('change', function () {
        if (fileUploadInput.files.length > 0) {
            const fileName = fileUploadInput.files[0].name;

            // Update label styles and text
            fileUploadLabel.classList.add('file-upload-success');
            fileUploadText.textContent = `Selected: ${fileName}`; // Display file name
        } else {
            // Reset label styles and text
            fileUploadLabel.classList.remove('file-upload-success');
            fileUploadText.textContent = "Click to upload or drag and drop";
        }
    });


   
    function handleSubmit(event) {
    event.preventDefault(); // Prevent default form submission

    const form = document.getElementById('maintenance-form');
    const formData = new FormData(form);

    // Submit the form using Fetch API
    fetch(form.action, {
        method: 'POST',
        body: formData,
    })
    .then(response => {
        // Handle HTTP status codes
        if (response.ok) {
            return response.text(); // Get plain text response
        } else {
            return response.text().then(text => {
                throw new Error(`Server Error: ${text}`);
            });
        }
    })
    .then(message => {
        console.log("Server Response:", message);
        Toastify({
            text: "Request Submitted Successfully!",
            backgroundColor: "green",
            gravity: "top",
            position: "right",
            duration: 3000,
        }).showToast();
        form.reset();

        setTimeout(function() {  
            window.location.reload();  
        }, 1000);  
    })
    .catch(error => {
        console.error('Error:', error.message);
        Toastify({
            text: error.message || "An error occurred. Please try again later.",
            backgroundColor: "red",
            gravuity: "top",
            position: "right",
            duration: 3000,
        }).showToast();
    });
}

</script>

<script>
    
    document.addEventListener('DOMContentLoaded', function() {
    fetch('maintenance.php')  // Replace with the actual PHP script that outputs the requests as JSON
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('request-history-body');
            data.forEach(request => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 border-b border-gray-200">${request.unit}</td>
                    <td class="px-6 py-4 border-b border-gray-200">${request.issue}</td>
                    <td class="px-6 py-4 border-b border-gray-200">${request.description}</td>
                    <td class="px-6 py-4 border-b border-gray-200">${request.service_date}</td>
                    <td class="px-6 py-4 border-b border-gray-200">
                        <button class="flex items-center justify-center px-4 py-2 rounded text-white text-xs ${request.status === 'In Progress' ? 'bg-yellow-500' : request.status === 'Completed' ? 'bg-green-500' : 'bg-red-500'}">
                            <i data-feather="${request.status === 'In Progress' ? 'clock' : request.status === 'Completed' ? 'check-circle' : 'times-circle'}" class="w-4 h-4"></i>
                            ${request.status}
                        </button>
                    </td>
                    <td class="px-6 py-4 border-b border-gray-200">
                        ${request.image ? `<a href="${request.image}" target="_blank" class="text-blue-600">View Image</a>` : 'No Image'}
                    </td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
});



// for status filter and search query
document.addEventListener('DOMContentLoaded', function () {
    // Get references to the filter elements and the table rows
    const statusFilter = document.getElementById('status-filter');
    const searchKeyword = document.getElementById('search-keyword');
    const tableRows = document.querySelectorAll('#request-table-body tr');

    // Function to filter rows based on status and search keyword
    function filterTable() {
        const searchTerm = searchKeyword.value.toLowerCase(); // Get the search term
        const selectedStatus = statusFilter.value.toLowerCase(); // Get the selected status

        // Loop through all table rows and check if they match the filter criteria
        tableRows.forEach(row => {
            const unit = row.cells[0].textContent.toLowerCase();
            const issue = row.cells[1].textContent.toLowerCase();
            const description = row.cells[2].textContent.toLowerCase();
            const serviceDate = row.cells[3].textContent.toLowerCase();
            const status = row.cells[4].textContent.toLowerCase();

            // Check if row matches the search term and selected status
            const matchesSearch = unit.includes(searchTerm) || issue.includes(searchTerm) || description.includes(searchTerm);
            const matchesStatus = selectedStatus ? status.includes(selectedStatus) : true;

            // Show or hide row based on the matches
            if (matchesSearch && matchesStatus) {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
        });
    }

    // Event listener for status filter change
    statusFilter.addEventListener('change', function () {
        filterTable(); // Call the filter function when status is changed
    });

    // Event listener for keyword search input
    searchKeyword.addEventListener('input', function () {
        filterTable(); // Call the filter function when the search term is typed
    });

    // Initial call to populate the table with default filters
    filterTable();
});

</script>

</body>
</html>
