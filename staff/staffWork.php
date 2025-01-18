<?php
// Include the database connection
require '../session/db.php';

// Start the session (if not already started)
session_start();

// Check if the staff member is logged in
if (!isset($_SESSION['staff_id'])) {
    die("You must be logged in to view this page.");
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
                            ?>
                            <tr>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($request['unit']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($request['issue']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($request['description']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($request['service_date']); ?></td>
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
                                <td class="px-4 sm:px-6 py-4 text-sm">
                                    <a href="submit_report.php?id=<?php echo $request['id']; ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <!-- Feather icon: file-text -->
                                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                        Submit Report
                                    </a>
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
    feather.replace();

    // Function to filter tasks based on status and search keyword
    function filterTasks() {
        const statusFilter = document.getElementById('statusFilter').value.trim().toLowerCase();
        const searchKeyword = document.getElementById('search-keyword').value.trim().toLowerCase();

        const rows = document.querySelectorAll('#workOrdersTableBody tr');

        rows.forEach(row => {
            const statusElement = row.querySelector('td:nth-child(6) span');
            const status = statusElement ? statusElement.textContent.trim().toLowerCase() : '';
            console.log("Row Status:", status); // Debugging: Print the status of each row

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