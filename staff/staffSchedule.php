<?php

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
$query = "SELECT * FROM maintenance_requests WHERE archived = 0 AND assigned_to = ? ORDER BY service_date ASC";
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

// Calculate summary metrics
$totalTasks = count($requests);
$dueToday = 0;
$overdue = 0;
$completedToday = 0;

$today = date('Y-m-d');
foreach ($requests as $request) {
    if ($request['service_date'] == $today) {
        $dueToday++;
    }
    if ($request['service_date'] < $today && $request['status'] != 'Completed') {
        $overdue++;
    }
    if ($request['status'] == 'Completed' && $request['service_date'] == $today) {
        $completedToday++;
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <title>Maintenance Schedule</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        .transition-transform { transition: transform 0.3s ease; }
        body { font-family: 'Poppins', sans-serif; }
        .priority-high { background-color: #FEE2E2; }
        .priority-medium { background-color: #FEF3C7; }
        .priority-low { background-color: #ECFDF5; }
    </style>
</head>
<body>
<!-- Include Navbar -->
<?php include('staffNavbar.php'); ?>

<!-- Include Sidebar -->
<?php include('staffSidebar.php'); ?>

<div class="sm:ml-64 p-4 sm:p-8 mx-auto">
    <div class="mt-16 sm:mt-20">
        <!-- Header and Priority Labels -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
            <h1 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4 sm:mb-0">Maintenance Schedule</h1>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs sm:text-sm bg-red-100 text-red-800">
                    <span class="w-2 h-2 mr-1 rounded-full bg-red-500"></span>High Priority
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs sm:text-sm bg-yellow-100 text-yellow-800">
                    <span class="w-2 h-2 mr-1 rounded-full bg-yellow-500"></span>Medium Priority
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs sm:text-sm bg-green-100 text-green-800">
                    <span class="w-2 h-2 mr-1 rounded-full bg-green-500"></span>Low Priority
                </span>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-600">Total Tasks</h3>
                <p class="text-2xl font-bold text-gray-800"><?php echo $totalTasks; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-600">Schedule Today</h3>
                <p class="text-2xl font-bold text-blue-600"><?php echo $dueToday; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-600">Overdue</h3>
                <p class="text-2xl font-bold text-red-600"><?php echo $overdue; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-600">Completed Today</h3>
                <p class="text-2xl font-bold text-green-600"><?php echo $completedToday; ?></p>
            </div>
        </div>

        <!-- Filter and Search Section -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <div class="relative w-full sm:w-48">
                <select id="statusFilter" class="w-full p-2 border rounded-lg text-sm sm:text-base">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <div class="relative w-full sm:w-48">
                <select id="priorityFilter" class="w-full p-2 border rounded-lg text-sm sm:text-base">
                    <option value="">All Priorities</option>
                    <option value="High">High Priority</option>
                    <option value="Medium">Medium Priority</option>
                    <option value="Low">Low Priority</option>
                </select>
            </div>
            <div class="relative w-full sm:w-48">
                <select id="timeFilter" class="w-full p-2 border rounded-lg text-sm sm:text-base">
                    <option value="">All Time</option>
                    <option value="today">Due Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
            <div class="relative w-full sm:w-1/4">
                <input type="text" id="search-keyword" placeholder="Search tasks..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                <button type="button" id="search-button" class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                    <i data-feather="search" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Work Orders Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200">Priority</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200">Unit</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200">Issue</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200">Description</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200">Scedule Date</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-700 border-b-2 border-gray-200 bg-gray-200">Status</th>
                        </tr>
                    </thead>
                    <tbody id="workOrdersTableBody" class="bg-white divide-y divide-gray-200">
                        <?php foreach ($requests as $request): ?>
                            <?php
                            // Determine priority class
                            $priorityClass = '';
                            if ($request['priority'] == 'high') {
                                $priorityClass = 'priority-high';
                            } elseif ($request['priority'] == 'medium') {
                                $priorityClass = 'priority-medium';
                            } elseif ($request['priority'] == 'low') {
                                $priorityClass = 'priority-low';
                            }

                            // Determine due date color
                            $dueDateColor = '';
                            if ($request['service_date'] == $today) {
                                $dueDateColor = 'text-red-600';
                            } elseif ($request['service_date'] < $today && $request['status'] != 'Completed') {
                                $dueDateColor = 'text-red-800';
                            } else {
                                $dueDateColor = 'text-gray-800';
                            }
                            ?>
                            <tr class="<?php echo $priorityClass; ?>">
                                <td class="px-4 sm:px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $request['priority'] == 'high' ? 'red' : ($request['priority'] == 'medium' ? 'yellow' : 'green'); ?>-100 text-<?php echo $request['priority'] == 'high' ? 'red' : ($request['priority'] == 'medium' ? 'yellow' : 'green'); ?>-800">
                                        <?php echo ucfirst($request['priority']); ?>
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($request['unit']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($request['issue']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm"><?php echo htmlspecialchars($request['description']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm <?php echo $dueDateColor; ?>"><?php echo htmlspecialchars($request['service_date']); ?></td>
                                <td class="px-4 sm:px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $request['status'] == 'Pending' ? 'gray' : ($request['status'] == 'In Progress' ? 'yellow' : 'green'); ?>-100 text-<?php echo $request['status'] == 'Pending' ? 'gray' : ($request['status'] == 'In Progress' ? 'yellow' : 'green'); ?>-800">
                                        <?php echo $request['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    feather.replace();

    // Add event listeners to filters
    document.getElementById('statusFilter').addEventListener('change', filterTasks);
    document.getElementById('priorityFilter').addEventListener('change', filterTasks);
    document.getElementById('timeFilter').addEventListener('change', filterTasks);

    function filterTasks() {
        const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
        const priorityFilter = document.getElementById('priorityFilter').value.toLowerCase();
        const timeFilter = document.getElementById('timeFilter').value;

        const rows = document.querySelectorAll('#workOrdersTableBody tr');
        const today = new Date();
        const startOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 1)); // Monday
        const endOfWeek = new Date(today.setDate(startOfWeek.getDate() + 6)); // Sunday

        rows.forEach(row => {
            const status = row.cells[5].textContent.trim().toLowerCase();
            const priority = row.cells[0].textContent.trim().toLowerCase();
            const dueDate = new Date(row.cells[4].textContent.trim());
            const isToday = dueDate.toDateString() === new Date().toDateString();
            const isThisWeek = dueDate >= startOfWeek && dueDate <= endOfWeek;
            const isThisMonth = dueDate.getMonth() === today.getMonth() && dueDate.getFullYear() === today.getFullYear();

            let showRow = true;

            // Apply status filter
            if (statusFilter && status !== statusFilter) {
                showRow = false;
            }

            // Apply priority filter
            if (priorityFilter && priority !== priorityFilter) {
                showRow = false;
            }

            // Apply time filter
            if (timeFilter === 'today' && !isToday) {
                showRow = false;
            } else if (timeFilter === 'week' && !isThisWeek) {
                showRow = false;
            } else if (timeFilter === 'month' && !isThisMonth) {
                showRow = false;
            }

            // Show or hide the row
            row.style.display = showRow ? '' : 'none';
        });
    }
</script>

</body>
</html>