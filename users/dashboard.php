<?php

require_once '../session/session_manager.php';
require '../session/db.php';


start_secure_session();


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../authentication/login.php'); // Adjust the path as necessary
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        <h1 class="text-2xl font-semibold text-gray-600">Welcome Back, User</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-8">
        <!-- Property and Financial Cards (Left Column, Spanning 3 Columns in Total) -->
        <div class="col-span-1 md:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4">

             <!-- Total Properties Card -->
        <a href="unitinfo.php" class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-all duration-300 block">
            <div class="flex items-center justify-between">
                <div class="text-blue-500">
                    <i class="fas fa-building fa-3x"></i>
                </div>
                <div class="text-right">
                    <p class="text-lg font-medium text-blue-600  hover:text-blue-800">View Properties</p>
                    <h3 class="text-xl font-bold text-gray-800 mt-2">10</h3>
                </div>
            </div>
        </a>

        <!-- Maintenance Card -->
        <a href="maintenance.php" class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-all duration-300 block">
            <div class="flex items-center justify-between">
                <div class="text-green-500">
                    <i class="fas fa-tools fa-3x"></i>
                </div>
                <div class="text-right">
                    <p class="text-lg font-medium text-blue-600  hover:text-blue-800">View Maintenance</p>
                    <h3 class="text-xl font-bold text-gray-800 mt-2">5</h3>
                </div>
            </div>
        </a>

        <!-- Payment Status Card -->
        <a href="payment.php?tab=paymentHistory" class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-all duration-300 block">
            <div class="flex items-center justify-between">
                <div class="text-yellow-500">
                    <i class="fas fa-money-bill-wave fa-3x"></i>
                </div>
                <div class="text-right">
                    <p class="text-lg font-medium text-blue-600  hover:text-blue-800">Track Payment</p>
                    <h3 class="text-xl font-bold text-gray-800 mt-2">5</h3>
                </div>
            </div>
        </a>

        <!-- View Agreement Card -->
        <a href="contract.php" class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-all duration-300 block">
            <div class="flex items-center justify-between">
                <div class="text-red-500">
                    <i class="fas fa-file-contract fa-3x"></i>
                </div>
                <div class="text-right">
                    <p class="text-lg font-medium text-blue-600  hover:text-blue-800">View Agreements</p>
                    <h3 class="text-xl font-bold text-gray-800 mt-2">5</h3>
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
            data: [500, 600, 700, 650, 800, 750, 900, 950, 1100, 1200, 1300, 1400] // Sample rent payment amounts for each month
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
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
</script>

</body>
</html>
