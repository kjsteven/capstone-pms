<?php
ob_start(); 

require_once '../session/session_manager.php';
require '../session/db.php';
include('../session/auth.php');
require_once '../session/audit_trail.php';

start_secure_session();

// Set HTTP headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

echo '<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>';


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../authentication/login.php');
    exit();
}

// Check if the user is an admin
if (!check_admin_role()) {
    header('Location: error.php'); // Redirect to an error page if not admin
    exit();
}

// Get property statistics - total properties
$propertyCountQuery = "SELECT COUNT(*) as total FROM property WHERE position = 'active'";
$propertyResult = $conn->query($propertyCountQuery);
$totalProperties = $propertyResult->fetch_assoc()['total'];

// Get property status counts for the chart
$propertyStatusQuery = "SELECT status, COUNT(*) as count FROM property WHERE position = 'active' GROUP BY status";
$statusResult = $conn->query($propertyStatusQuery);
$statusCounts = [
    'Available' => 0,
    'Occupied' => 0,
    'Maintenance' => 0,
    'Reserved' => 0
];

while ($row = $statusResult->fetch_assoc()) {
    $statusCounts[$row['status']] = (int)$row['count'];
}

// Calculate occupancy percentages for the chart
$totalActiveProperties = array_sum($statusCounts);
$occupancyData = [];
$occupancyLabels = [];
$occupancyPercentages = [];

if ($totalActiveProperties > 0) {
    foreach ($statusCounts as $status => $count) {
        $percentage = round(($count / $totalActiveProperties) * 100, 1);
        $occupancyLabels[] = $status . " (" . $percentage . "%)";
        $occupancyData[] = $count;
        $occupancyPercentages[] = $percentage;
    }
}

// Pagination settings
$items_per_page = 20; // Changed from 10 to 20 (or any other number you prefer)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of activities - Adding error handling and debugging info
$total_query = "SELECT COUNT(*) as total FROM activity_logs";
$total_result = mysqli_query($conn, $total_query);
if (!$total_result) {
    // Log the error for debugging
    error_log("Error in count query: " . mysqli_error($conn));
    $total_rows = 0;
} else {
    $total_rows = mysqli_fetch_assoc($total_result)['total'];
}
$total_pages = ceil($total_rows / $items_per_page);

// Add debugging output (you can remove this later)
echo "<!-- Total rows found: $total_rows, Pages: $total_pages -->";

// Modified query to handle both users and staff - with error handling
$activities_query = "SELECT a.*, 
                    COALESCE(u.name, s.name) as name,
                    a.user_role as role
                    FROM activity_logs a 
                    LEFT JOIN users u ON a.user_id = u.user_id
                    LEFT JOIN staff s ON a.staff_id = s.staff_id 
                    ORDER BY a.timestamp DESC 
                    LIMIT $offset, $items_per_page";
$activities_result = mysqli_query($conn, $activities_query);
if (!$activities_result) {
    // Log the error for debugging
    error_log("Error in activities query: " . mysqli_error($conn));
    $activities = [];
} else {
    $activities = $activities_result->fetch_all(MYSQLI_ASSOC);
}

// Get user info for report generation
$current_user_name = '';
if (isset($_SESSION['user_id'])) {
    $user_query = "SELECT name FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $current_user_name = $result->fetch_assoc()['name'];
    }
    $stmt->close();
}

// Get tenant count - Fixed to use JOIN with users table to get unique names
$tenantCountQuery = "SELECT COUNT(DISTINCT u.name) as total 
                    FROM tenants t 
                    JOIN users u ON t.user_id = u.user_id 
                    WHERE t.status = 'active'";
$tenantResult = $conn->query($tenantCountQuery);
$totalTenants = $tenantResult->fetch_assoc()['total'];

// Get pending maintenance count
$maintenanceQuery = "SELECT COUNT(*) as total FROM maintenance_requests WHERE status = 'pending'";
$maintenanceResult = $conn->query($maintenanceQuery);
$pendingMaintenance = $maintenanceResult ? $maintenanceResult->fetch_assoc()['total'] : 0;

// Get pending reservations count
$reservationQuery = "SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'";
$reservationResult = $conn->query($reservationQuery);
$pendingReservations = $reservationResult ? $reservationResult->fetch_assoc()['total'] : 0;

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Admin Dashboard</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        /* Optional: Custom styles for smooth transitions */
        .transition-transform {
            transition: transform 0.3s ease;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        @media (max-width: 768px) {
            .chart-container {
                height: 200px;
            }
        }
        /* Add to existing styles */
        #activitiesTable {
            max-height: none;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out;
        }

        .activities-collapsed {
            max-height: 0 !important;
        }
        /* Update the activities table styles */
        #activitiesTable {
            max-height: 500px; /* Set initial height */
            transition: max-height 0.3s ease-out;
            overflow: hidden;
        }

        #activitiesTable.collapsed {
            max-height: 0;
        }
    </style>
</head>
<body> 

<!-- Include Navbar -->
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>

<div class="p-4 sm:ml-64">
    <div class="mt-20">
        <h1 class="text-2xl font-semibold text-gray-700">Dashboard Overview</h1>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <!-- Properties Card -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Properties</p>
                        <h3 class="text-2xl font-bold text-gray-700"><?php echo $totalProperties; ?></h3>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg data-feather="home" class="text-blue-600 w-6 h-6"></svg>
                    </div>
                </div>
            </div>

            <!-- Tenants Card -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Active Tenants</p>
                        <h3 class="text-2xl font-bold text-gray-700"><?php echo $totalTenants; ?></h3>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg data-feather="users" class="text-green-600 w-6 h-6"></svg>
                    </div>
                </div>
            </div>

            <!-- Maintenance Card -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pending Maintenance</p>
                        <h3 class="text-2xl font-bold text-gray-700"><?php echo $pendingMaintenance; ?></h3>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg data-feather="tool" class="text-yellow-600 w-6 h-6"></svg>
                    </div>
                </div>
            </div>

             <!-- Active Reservation -->
             <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pending Reservation</p>
                        <h3 class="text-2xl font-bold text-gray-700"><?php echo $pendingReservations; ?></h3>
                    </div>
                    <div class="p-3 bg-indigo-200 rounded-full">
                        <svg data-feather="calendar" class="text-indigo-600 w-6 h-6"></svg>
                    </div>
                </div>
            </div>

            <!-- Revenue Card -->
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Monthly Revenue</p>
                        <h3 class="text-2xl font-bold text-gray-700">$52,680</h3>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg data-feather="dollar-sign" class="text-purple-600 w-6 h-6"></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Revenue Overview</h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Occupancy Chart -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Property Occupancy</h3>
                <div class="chart-container">
                    <canvas id="occupancyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activities Section -->
        <div class="mt-6">
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
                    <div class="flex items-center gap-2 mb-2 sm:mb-0">
                        <h3 class="text-lg font-semibold text-gray-700">Recent Activities</h3>
                        <button id="toggleActivities" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="exportCurrentPage('<?php echo htmlspecialchars($current_user_name); ?>')" class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                            <i class="fas fa-file-excel"></i>
                            <span class="hidden sm:inline">Export Current Page</span>
                        </button>
                    </div>
                </div>
                <div id="activitiesTable" class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($activities as $activity): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm"><?php echo htmlspecialchars($activity['name']); ?></td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo getActivityRoleColor($activity['role']); ?>">
                                            <?php echo htmlspecialchars($activity['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm"><?php echo htmlspecialchars($activity['action']); ?></td>
                                    <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm"><?php echo htmlspecialchars($activity['details']); ?></td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm">
                                        <?php echo date('M d, Y H:i', strtotime($activity['timestamp'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page-1; ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                            <?php endif; ?>
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page+1; ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                            <?php endif; ?>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to 
                                    <span class="font-medium"><?php echo min($offset + $items_per_page, $total_rows); ?></span> of 
                                    <span class="font-medium"><?php echo $total_rows; ?></span> results
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <a href="?page=<?php echo $i; ?>" 
                                           class="relative inline-flex items-center px-4 py-2 text-sm font-semibold 
                                                  <?php echo $i === $page ? 'bg-indigo-600 text-white' : 'text-gray-900'; ?> 
                                                  ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script>
    // Initialize Feather Icons
    feather.replace();

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Monthly Revenue',
                data: [42000, 49000, 52000, 47000, 53000, 52680],
                borderColor: 'rgb(99, 102, 241)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 10,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });

    // Occupancy Chart with actual data and percentages
    const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
    new Chart(occupancyCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($occupancyLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($occupancyData); ?>,
                backgroundColor: [
                    'rgb(34, 197, 94)',   // Available - Green
                    'rgb(99, 102, 241)',  // Occupied - Indigo
                    'rgb(234, 179, 8)',   // Maintenance - Yellow
                    'rgb(79, 70, 229)'    // Reserved - Purple
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const percentage = <?php echo json_encode($occupancyPercentages); ?>[context.dataIndex];
                            return `${label.split('(')[0].trim()}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Toggle activities table
    document.getElementById('toggleActivities').addEventListener('click', function() {
        const table = document.getElementById('activitiesTable');
        const icon = this.querySelector('i');
        
        if (table.style.maxHeight) {
            table.style.maxHeight = null;
            icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
        } else {
            table.style.maxHeight = table.scrollHeight + "px";
            icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
        }
    });

    // Export to Excel function
    function exportCurrentPage(userName) {
        const table = document.querySelector('table');
        const rows = Array.from(table.querySelectorAll('tr'));
        
        // Create metadata rows for the report
        const currentDate = new Date();
        const formattedDate = currentDate.toLocaleDateString();
        const formattedTime = currentDate.toLocaleTimeString();
        
        // Create metadata for the Excel document
        const metadata = [
            ['Activity Logs Report'],
            ['Generated by: ' + userName],
            ['Date Generated: ' + formattedDate + ' ' + formattedTime],
            ['Page: ' + <?php echo $page; ?>],
            [''] // Empty row for separation
        ];
        
        // Extract table data
        const tableData = rows.map(row => {
            return Array.from(row.querySelectorAll('th, td')).map(cell => cell.textContent.trim());
        });
        
        // Combine metadata with table data
        const allData = [...metadata, ...tableData];
        
        const ws = XLSX.utils.aoa_to_sheet(allData);
        
        // Add some basic styling for the header
        ws['!merges'] = [{ s: {r: 0, c: 0}, e: {r: 0, c: 4} }]; // Merge cells for title
        
        // Set column widths
        const colWidths = [
            {wch: 15}, // User column
            {wch: 10}, // Role column
            {wch: 20}, // Action column
            {wch: 40}, // Details column
            {wch: 20}  // Date column
        ];
        ws['!cols'] = colWidths;
        
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Activities');
        
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `activities_export_page${<?php echo $page; ?>}_${timestamp}.xlsx`;
        
        XLSX.writeFile(wb, filename);
    }

    // Initialize table state
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('activitiesTable');
        table.style.maxHeight = table.scrollHeight + "px";
    });

    // Replace the toggle activities function with this updated version
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleActivities');
        const table = document.getElementById('activitiesTable');
        const icon = toggleBtn.querySelector('i');
        let isExpanded = true;

        toggleBtn.addEventListener('click', function() {
            isExpanded = !isExpanded;
            
            if (isExpanded) {
                table.classList.remove('collapsed');
                icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            } else {
                table.classList.add('collapsed');
                icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            }
        });
    });
</script>

<?php
// Helper function to get role badge color
function getActivityRoleColor($role) {
    switch (strtoupper($role)) {  // Change to uppercase comparison
        case 'ADMIN':
            return 'bg-purple-100 text-purple-800';
        case 'STAFF':
            return 'bg-blue-100 text-blue-800';
        case 'USER':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>

</body>
</html>
