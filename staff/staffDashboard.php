<?php
require_once '../session/session_manager.php';
require '../session/db.php';

// Stats query
$stats_query = "SELECT 
    COUNT(*) as total_work_orders,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as ongoing_orders,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_orders
FROM maintenance_requests 
WHERE assigned_to = ? AND archived = 0";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Recent orders query
$recent_orders_query = "SELECT 
    m.id,
    m.issue,
    m.description,
    m.status,
    m.service_date,
    m.created_at,
    p.unit_no,
    p.unit_type,
    p.status as unit_status
FROM maintenance_requests m
JOIN property p ON m.unit = p.unit_no
WHERE m.assigned_to = ? 
AND m.archived = 0
ORDER BY 
    CASE m.status
        WHEN 'Pending' THEN 1
        WHEN 'In Progress' THEN 2
        WHEN 'Completed' THEN 3
    END,
    m.created_at DESC 
LIMIT 5";

$stmt = $conn->prepare($recent_orders_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>

</head>
<body class="bg-gray-100">

<?php include('staffNavbar.php'); ?>
<?php include('staffSidebar.php'); ?>

<div class="p-4 mt-5 sm:ml-64">
    <div class="mt-14">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-tools text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Total Orders</h3>
                        <p class="text-2xl font-semibold"><?= $stats['total_work_orders'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Pending</h3>
                        <p class="text-2xl font-semibold"><?= $stats['pending_orders'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100">
                        <i class="fas fa-spinner text-indigo-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">In Progress</h3>
                        <p class="text-2xl font-semibold"><?= $stats['ongoing_orders'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Completed</h3>
                        <p class="text-2xl font-semibold"><?= $stats['completed_orders'] ?></p>
                    </div>
                </div>
            </div>
        </div>

         <!-- Recent Orders Table -->
         <div class="mt-8 bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Recent Work Orders</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4"><?= htmlspecialchars($order['unit_no']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($order['issue']) ?></td>
                                <td class="px-6 py-4"><?= date('M d, Y', strtotime($order['service_date'])) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= match($order['status']) {
                                            'Pending' => 'bg-yellow-100 text-yellow-800',
                                            'In Progress' => 'bg-blue-100 text-blue-800',
                                            'Completed' => 'bg-green-100 text-green-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        } ?>">
                                        <?= $order['status'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>

</body>
</html>