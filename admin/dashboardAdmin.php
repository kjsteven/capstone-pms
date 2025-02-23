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

// Fetch recent activities
$activities = getRecentActivities(10); // Get last 10 activities

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
                        <h3 class="text-2xl font-bold text-gray-700">24</h3>
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
                        <h3 class="text-2xl font-bold text-gray-700">156</h3>
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
                        <h3 class="text-2xl font-bold text-gray-700">8</h3>
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
                        <h3 class="text-2xl font-bold text-gray-700">20</h3>
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
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-semibold text-gray-700">Recent Activities</h3>
                        <button id="toggleActivities" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="exportToExcel()" class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                            <i class="fas fa-file-excel"></i>
                            Export to Excel
                        </button>
                    </div>
                </div>
                <div id="activitiesTable" class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($activities as $activity): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($activity['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo getActivityRoleColor($activity['role']); ?>">
                                            <?php echo htmlspecialchars($activity['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($activity['action']); ?></td>
                                    <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($activity['details']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php echo date('M d, Y H:i', strtotime($activity['timestamp'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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

    // Occupancy Chart
    const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
    new Chart(occupancyCtx, {
        type: 'doughnut',
        data: {
            labels: ['Occupied', 'Vacant', 'Under Maintenance'],
            datasets: [{
                data: [75, 15, 10],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(234, 179, 8)'
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
    function exportToExcel() {
        const table = document.querySelector('table');
        const rows = Array.from(table.querySelectorAll('tr'));
        
        const data = rows.map(row => {
            return Array.from(row.querySelectorAll('th, td')).map(cell => cell.textContent.trim());
        });
        
        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Activities');
        
        // Generate timestamp for filename
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `activities_export_${timestamp}.xlsx`;
        
        // Trigger download
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
    switch (strtolower($role)) {
        case 'admin':
            return 'bg-purple-100 text-purple-800';
        case 'staff':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-green-100 text-green-800';
    }
}
?>

</body>
</html>
