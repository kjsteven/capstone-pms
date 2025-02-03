<?php
ob_start(); 

require_once '../session/session_manager.php';
require '../session/db.php';
include('../session/auth.php');

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
            height: 250px;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        @media (max-width: 768px) {
            .chart-container {
                height: 200px;
            }
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
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Activities</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">New Tenant Registration</td>
                                <td class="px-6 py-4 whitespace-nowrap">John Doe</td>
                                <td class="px-6 py-4 whitespace-nowrap">2023-12-01</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                </td>
                            </tr>
                            <!-- Add more rows as needed -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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
</script>

</body>
</html>
