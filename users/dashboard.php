<?php

require_once '../session/session_manager.php';
require '../session/db.php';


start_secure_session();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

// Security headers
header("X-Content-Type-Options: nosniff"); // Prevent MIME-type sniffing
header("X-Frame-Options: DENY"); // Prevent clickjacking

header("Referrer-Policy: strict-origin-when-cross-origin"); // More secure referrer policy

// Only add this if your site runs on HTTPS
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Strict-Transport-Security: max-age=31536000; preload"); 


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../authentication/login.php'); // Adjust the path as necessary
    exit();
}



// Prepare the statement
$query = "SELECT name, email, phone, status FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);

// Bind the parameter
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);

// Execute the statement
mysqli_stmt_execute($stmt);

// Get the result
$result = mysqli_stmt_get_result($stmt);

// Fetch the user data
$user = mysqli_fetch_assoc($result);

// Get payment data for chart - fetch rent payments by month for the current year
$current_year = date('Y');
$payment_query = "
    SELECT 
        MONTH(p.payment_date) as month,
        SUM(p.amount) as total_amount
    FROM payments p
    JOIN tenants t ON p.tenant_id = t.tenant_id
    WHERE t.user_id = ? 
    AND YEAR(p.payment_date) = ?
    AND p.status = 'Received'
    GROUP BY MONTH(p.payment_date)
    ORDER BY MONTH(p.payment_date)
";

$stmt = mysqli_prepare($conn, $payment_query);
mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $current_year);
mysqli_stmt_execute($stmt);
$payment_result = mysqli_stmt_get_result($stmt);

// Initialize array with zeros for all 12 months
$monthly_payments = array_fill(0, 12, 0);

// Fill in actual payment data
while ($row = mysqli_fetch_assoc($payment_result)) {
    $month_index = $row['month'] - 1; // Convert 1-12 to 0-11 for array index
    $monthly_payments[$month_index] = (float)$row['total_amount'];
}

// Convert to JSON for use in JavaScript
$payment_data_json = json_encode($monthly_payments);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <title>Dashboard</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
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
<?php include('navbar.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebar.php'); ?>

<div class="p-4 sm:ml-64">

    <div class="mt-20">
            <?php 

             // Set timezone to Manila
            date_default_timezone_set('Asia/Manila');

            // Determine greeting based on time of day
            $hour = date('H');
            if ($hour < 12) {
                $greeting = "Good Morning";
            } elseif ($hour < 18) {
                $greeting = "Good Afternoon";
            } else {
                $greeting = "Good Evening";
            }

            // Capitalize first letter of name or use a fallback
            $name = isset($user['name']) ? ucwords(strtolower($user['name'])) : 'User';
            ?>
            <h1 class="text-2xl font-semibold text-gray-600">
                <?php echo htmlspecialchars($greeting); ?>, 
                <span class="text-gray-600"><?php echo htmlspecialchars($name); ?></span>
            </h1>
            
            <?php 
            // Optional: Add a subtle welcome back message with current date
            $currentDate = date('l, F j, Y');
            ?>
            <p class="text-sm text-gray-500 mt-2">
                <?php echo htmlspecialchars($currentDate); ?>
            </p>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-8">
        <!-- Property and Financial Cards (Left Column, Spanning 3 Columns in Total) -->
        <div class="col-span-1 md:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4">

            <!-- Total Properties Card -->
            <a href="unitinfo.php" class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-all duration-300 block">
                <div class="flex items-center justify-between">
                    <div class="text-blue-500">
                        <!-- Replace FontAwesome Icon with Feather Icon -->
                        <svg data-feather="home" class="w-8 h-8 text-blue-500"></svg>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-medium text-blue-600  hover:text-blue-800">View Properties</p>
                      
                    </div>
                </div>
            </a>

          <!-- Maintenance Card -->
        <a href="maintenance.php" class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-all duration-300 block">
            <div class="flex items-center justify-between">
                <div class="text-green-500">
                   
                    <svg data-feather="tool" class="w-8 h-8 text-green-500"></svg>
                </div>
                <div class="text-right">
                    <p class="text-lg font-medium text-blue-600  hover:text-blue-800">Submit Maintenance Request</p>
                  
                </div>
            </div>
        </a>

            <!-- Payment Status Card -->
            <a href="payment.php?tab=paymentHistory" class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-all duration-300 block">
                <div class="flex items-center justify-between">
                    <div class="text-yellow-500">
                       
                        <svg data-feather="credit-card" class="w-8 h-8 text-yellow-500"></svg>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-medium text-blue-600  hover:text-blue-800">Track Payment</p>
                       
                    </div>
                </div>
            </a>

            <!-- View Agreement Card -->
            <a href="contract.php" class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-all duration-300 block">
                <div class="flex items-center justify-between">
                    <div class="text-red-500">
                        <!-- Replace FontAwesome Icon with Feather Icon -->
                        <svg data-feather="file" class="w-8 h-8 text-red-500"></svg>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-medium text-blue-600  hover:text-blue-800">View Agreements</p>
                       
                    </div>
                </div>
            </a>

        </div>

        <!-- Calendar Section (Right Column, Spanning 2 Columns for Larger Width) -->
        <div class="col-span-1 md:col-span-2 bg-white rounded-lg shadow-md p-6 border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <i id="prevMonth" class="fas fa-chevron-left text-gray-500 cursor-pointer"></i>
                <span id="monthYear" class="font-semibold text-lg">January</span>
                <i id="nextMonth" class="fas fa-chevron-right text-gray-500 cursor-pointer"></i>
            </div>
            <div class="grid grid-cols-7 gap-2 text-center text-gray-500 mb-4">
                <div>S</div>
                <div>M</div>
                <div>T</div>
                <div>W</div>
                <div>T</div>
                <div>F</div>
                <div>S</div>
            </div>
            <div id="calendarDays" class="grid grid-cols-7 gap-2 text-center"></div>
            <button class="mt-6 w-full bg-blue-600 text-white py-2 rounded-lg">Add event</button>
        </div>

    </div> 

    <!-- Chart Section -->
    <div class="bg-white p-6 rounded-lg shadow-md mt-6 border border-gray-200">
        <h2 class="text-xl md:text-2xl font-semibold text-gray-800 mb-4">Rent Payments per Month</h2>
        <div id="chart"></div>
    </div>

</div>

<!-- Add Feather Icon Script -->
<script src="../node_modules/feather-icons/dist/feather.min.js"></script>

<script>
    // Initialize Feather icons
    document.addEventListener('DOMContentLoaded', function () {
        feather.replace(); // This makes sure Feather icons are rendered
    });
</script>


<script>
    // Calendar Functionality
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const daysInMonth = (month, year) => new Date(year, month + 1, 0).getDate();
    const firstDayOfMonth = (month, year) => new Date(year, month, 1).getDay();

    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();

    const renderCalendar = () => {
        const calendarDays = document.getElementById('calendarDays');
        calendarDays.innerHTML = '';

        const monthYear = document.getElementById('monthYear');
        monthYear.textContent = `${monthNames[currentMonth]} ${currentYear}`;

        const firstDay = firstDayOfMonth(currentMonth, currentYear);
        const totalDays = daysInMonth(currentMonth, currentYear);

        for (let i = 0; i < firstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.classList.add('text-gray-300');
            calendarDays.appendChild(emptyCell);
        }

        for (let day = 1; day <= totalDays; day++) {
            const dayCell = document.createElement('div');
            dayCell.textContent = day;
            if (day === new Date().getDate() && currentMonth === new Date().getMonth() && currentYear === new Date().getFullYear()) {
                dayCell.classList.add('bg-black', 'text-white', 'rounded-full', 'w-8', 'h-8', 'flex', 'items-center', 'justify-center', 'mx-auto');
            }
            calendarDays.appendChild(dayCell);
        }
    };

    document.getElementById('prevMonth').addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    });

    renderCalendar();

</script>

<script>
    var options = {
        series: [{
            name: "Rent Payments",
            data: <?php echo $payment_data_json; ?> // Use actual payment data from database
        }],
        chart: {
            height: 350,
            type: 'line',
            zoom: {
                enabled: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        grid: {
            row: {
                colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                opacity: 0.5
            },
        },
        xaxis: {
            categories: monthNames,
        },
        yaxis: {
            title: {
                text: 'Amount (PHP)'
            },
            labels: {
                formatter: function(value) {
                    return "₱" + value.toFixed(2);
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return "₱" + value.toFixed(2);
                }
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
</script>

</body>
</html>
