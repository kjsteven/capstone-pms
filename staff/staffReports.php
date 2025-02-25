<?php


require '../session/db.php';
require_once '../session/session_manager.php';

start_secure_session();

if (!isset($_SESSION['staff_id'])) {
    header('Location: ../authentication/stafflogin.php');
    exit();
}

$staff_id = $_SESSION['staff_id'];

// Fetch maintenance statistics
$stats_query = "SELECT 
    COUNT(*) as total_tasks,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as ongoing_tasks,
    AVG(maintenance_cost) as avg_cost,
    SUM(maintenance_cost) as total_cost
FROM maintenance_requests 
WHERE assigned_to = ? AND archived = 0";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Fetch monthly completion data
$monthly_query = "SELECT 
    DATE_FORMAT(completion_date, '%Y-%m') as month,
    COUNT(*) as completed_count
FROM maintenance_requests 
WHERE assigned_to = ? 
    AND status = 'Completed' 
    AND completion_date IS NOT NULL
GROUP BY month
ORDER BY month DESC
LIMIT 6";

$stmt = $conn->prepare($monthly_query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$monthly_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch recent maintenance reports
$reports_query = "SELECT 
    mr.*,
    p.unit_type
FROM maintenance_requests mr
JOIN property p ON mr.unit = p.unit_no
WHERE mr.assigned_to = ? 
AND mr.status = 'Completed'
ORDER BY mr.completion_date DESC
LIMIT 10";

$stmt = $conn->prepare($reports_query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$recent_reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
    <title>Maintenance Reports</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50"> 

<?php include('staffNavbar.php'); ?>
<?php include('staffSidebar.php'); ?>

<div class="p-4 sm:ml-64">
    <div class="mt-20">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Maintenance Reports & Analytics</h1>
            <p class="text-gray-600">Track your maintenance performance and statistics</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-tasks text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Total Tasks</h3>
                        <p class="text-2xl font-semibold"><?= $stats['total_tasks'] ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Completed</h3>
                        <p class="text-2xl font-semibold"><?= $stats['completed_tasks'] ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">In Progress</h3>
                        <p class="text-2xl font-semibold"><?= $stats['ongoing_tasks'] ?? 0 ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <i class="fas fa-money-bill text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Avg. Cost</h3>
                        <p class="text-2xl font-semibold">₱<?= number_format($stats['avg_cost'] ?? 0, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold mb-4">Monthly Completed Tasks</h3>
                <canvas id="monthlyChart"></canvas>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold mb-4">Recent Reports</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recent_reports as $report): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($report['unit']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($report['issue']) ?></td>
                                <td class="px-6 py-4 text-sm">₱<?= number_format($report['maintenance_cost'], 2) ?></td>
                                <td class="px-6 py-4 text-sm"><?= date('M d, Y', strtotime($report['completion_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Monthly Chart
const monthlyData = <?= json_encode($monthly_data) ?>;
const labels = monthlyData.map(item => {
    const [year, month] = item.month.split('-');
    return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}).reverse();
const values = monthlyData.map(item => item.completed_count).reverse();

new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Completed Tasks',
            data: values,
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

</body>
</html>
