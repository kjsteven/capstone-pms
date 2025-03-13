<?php

require '../session/db.php';
session_start();

// Query to fetch unit statuses
$query = "SELECT status, COUNT(*) AS count FROM property GROUP BY status";
$result = mysqli_query($conn, $query);

// Initialize the data for the chart
$occupied = 0;
$available = 0;
$underMaintenance = 0;

// Loop through the result to count each status
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['status'] == 'Occupied') {
        $occupied = $row['count'];
    } elseif ($row['status'] == 'Available') {
        $available = $row['count'];
    } elseif ($row['status'] == 'Maintenance') {
        $underMaintenance = $row['count'];
    }
}

// Only get the years for the dropdown
$years_query = "
    SELECT DISTINCT YEAR(service_date) as year 
    FROM maintenance_requests 
    ORDER BY year DESC";

$years_result = mysqli_query($conn, $years_query);
$years = [];
while ($row = mysqli_fetch_assoc($years_result)) {
    $years[] = $row['year'];
}

$years_json = json_encode($years);

// Remove all maintenance data queries as we'll fetch them via AJAX
mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <title>Reports and Analytics</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .chart-container {
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            height: 400px;
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        .chart-container .chart-title {
            margin-bottom: 10px;
            font-weight: 600;
            text-align: center;
        }
        .chart-container .chart-content {
            flex-grow: 1;
            position: relative;
        }
    </style>
</head>
<body>

<!-- Include Navbar -->
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>

<div class="sm:ml-64 p-8 mt-20 mx-auto">
    <h1 class="text-xl font-semibold text-gray-800 mb-6">Reports and Analytics</h1>

    <!-- Tabs Navigation (Placed at the top) -->
    <div class="flex mb-6 border-b">
        <button id="tab-reports" class="py-2 px-4 text-gray-700 focus:outline-none border-b-4 border-blue-600">Reports</button>
        <button id="tab-analytics" class="py-2 px-4 text-gray-700 focus:outline-none ml-4 border-b-4 border-transparent hover:border-blue-600">Analytics</button>
    </div>


<!-- Generate Reports Tab Content -->
<div id="content-reports" class="tab-content">
    <h2 class="text-lg md:text-md font-semibold mt-8 md:mt-12 mb-4 md:mb-6">Generate Reports</h2>
    <div class="bg-gray-50 p-4 md:p-6 border border-gray-200 rounded-lg shadow-md">
        <form id="report-form" class="space-y-4">
            <!-- Row for Report Type, Report Month, and Report Year -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Report Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="report-type">
                        Report Type
                    </label>
                    <select class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="report-type" name="report-type">
                        <option>Unit Occupancy Report</option>
                        <option>Property Maintenance Report</option>
                    </select>
                </div>

                <!-- Report Month -->
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="report-month">
                        Report Month
                    </label>
                    <select class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="report-month" name="report-month">
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>

              <!-- Report Year -->
            <div>
                <label class="block text-sm font-medium text-gray-700" for="report-year">
                    Report Year
                </label>
                <select class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="report-year" name="report-year">
                <?php
                $current_year = date('Y');
                $start_year = $current_year; // Set the start year to the current year

                for ($year = $start_year; $year >= $start_year - 10; $year--) {
                    echo "<option value=\"$year\">$year</option>";
                }
                ?>
                </select>
            </div>
        </div>

            <!-- Submit Button -->
            <div>
                <button class="bg-indigo-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-indigo-700 transition duration-200" type="submit">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

        <!-- Table for generated reports -->
        <div class="mt-8 overflow-x-auto">
            <table class="min-w-full text-sm table-auto">
                <thead>
                    <tr>
                        <th class="py-3 px-2 md:px-4 border-b border-gray-200 text-left">Report Type</th>
                        <th class="py-3 px-2 md:px-4 border-b border-gray-200 text-left">Date Generated</th>
                        <th class="py-3 px-2 md:px-4 border-b border-gray-200 text-left">Date Period</th>
                        <th class="py-3 px-2 md:px-4 border-b border-gray-200 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="generated-reports-table">
                    <!-- Generated reports will appear here -->
                </tbody>
            </table>
        </div>
    </div>

        <!-- Tab Content for Analytics (Hidden by default) -->
        <div id="content-analytics" class="tab-content hidden">
            <!-- 2 Column Grid Layout for the First 4 Charts (2 Rows) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <!-- First Chart (Unit Occupancy) - First Column -->
                <div class="col-span-1 chart-container shadow-md p-4 border border-gray-300">
                    <h3 class="font-semibold text-md text-gray-800 ">Unit Occupancy</h3>
                    <div id="tenant-occupancy-chart"></div>
                </div>

        
                <!-- Second Chart (Property Maintenance) - Second Column -->
                <div class="col-span-1 chart-container shadow-md p-4 border border-gray-300">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-md text-gray-800">Property Maintenance</h3>
                        <select id="maintenance-year-filter" class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="property-maintenance-chart"></div>
                </div>

                <!-- Third Chart (Monthly Payments) - First Column -->
                <div class="col-span-1 chart-container shadow-md p-4 mb-4 mt-6 border border-gray-300">
                    <h3 class="font-semibold text-md text-gray-800 mb-4">Monthly Payments</h3>
                    <div id="monthly-payments-chart"></div>
                </div>

                <!-- Fourth Chart (Rental Balance) - Second Column -->
                <div class="col-span-1 chart-container shadow-md p-4 mt-6 border border-gray-300">
                    <h3 class="font-semibold text-md text-gray-800 mb-4">Rental Balance</h3>
                    <div id="rental-balance-chart"></div>
                </div>
            </div>

            <!-- Fifth Chart (Property Availability - Spanning 2 Columns) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="col-span-2 chart-container shadow-md p-4 mt-4 border border-gray-300 ">
                    <h3 class="font-semibold text-md text-gray-800 mb-4">Unit Availability</h3>
                    <div id="property-availability-chart"></div>
                </div>
            </div>
        </div>
    </div>



<!-- SCRIPT FOR TAB SWITCHING  -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabReports = document.getElementById('tab-reports');
    const tabAnalytics = document.getElementById('tab-analytics');
    const contentReports = document.getElementById('content-reports');
    const contentAnalytics = document.getElementById('content-analytics');
    
    // Tab Reports click handler
    tabReports.addEventListener('click', function() {
        // Update tab styles
        tabReports.classList.add('border-blue-600', 'text-blue-600');
        tabAnalytics.classList.remove('border-blue-600', 'text-blue-600');
        tabAnalytics.classList.add('border-transparent');
        
        // Show/hide content
        contentReports.classList.remove('hidden');
        contentAnalytics.classList.add('hidden');
    });
    
    // Tab Analytics click handler
    tabAnalytics.addEventListener('click', function() {
        // Update tab styles
        tabAnalytics.classList.add('border-blue-600', 'text-blue-600');
        tabReports.classList.remove('border-blue-600', 'text-blue-600');
        tabReports.classList.add('border-transparent');
        
        // Show/hide content
        contentAnalytics.classList.remove('hidden');
        contentReports.classList.add('hidden');
        
        // Refresh charts when switching to analytics tab
        if (typeof refreshAllCharts === 'function') {
            refreshAllCharts();
        }
    });
});
</script>


  <!-- Report Generation -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportForm = document.querySelector('#report-form');
    const generatedReportsTable = document.querySelector('#generated-reports-table');
    const progressSpinner = document.createElement('div');
    progressSpinner.innerHTML = `
        <div class="fixed inset-0 bg-black bg-opacity-25 flex justify-center items-center z-50">
            <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
    `;
    progressSpinner.style.display = 'none'; // Initially hidden
    document.body.appendChild(progressSpinner);

    reportForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const reportType = document.getElementById('report-type').value;
        const reportMonth = document.getElementById('report-month').value;
        const reportYear = document.getElementById('report-year').value;

        const submitButton = e.target.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Generating Report...';

        // Show the progress spinner
        progressSpinner.style.display = 'flex';

        const formData = new FormData();
        formData.append('report_type', reportType);
        formData.append('report_month', reportMonth);
        formData.append('report_year', reportYear);
        formData.append('action', 'generate_report');

        fetch('generate_report.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const newReportRow = `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 md:px-4 border-b border-gray-200">${reportType}</td>
                        <td class="py-3 px-2 md:px-4 border-b border-gray-200">${new Date().toLocaleDateString()}</td>
                        <td class="py-3 px-2 md:px-4 border-b border-gray-200 text-center">
                            <button onclick="downloadReport('${data.filename}')" class="bg-green-600 text-white text-xs md:text-sm py-1 px-2 md:px-3 rounded-md shadow-md hover:bg-blue-600 transition duration-200">Download</button>
                            <button onclick="deleteReport(this, ${data.report_id})" class="bg-red-500 text-white text-xs md:text-sm py-1 px-2 md:px-3 rounded-md shadow-md hover:bg-red-600 transition duration-200">Delete</button>
                        </td>
                    </tr>
                `;
                generatedReportsTable.insertAdjacentHTML('afterbegin', newReportRow);

                showNotification('Report generated successfully', 'success');

                // Reload the current page
                window.location.reload();

            } else {
                showNotification(data.message || 'Failed to generate report', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An unexpected error occurred', 'error');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = 'Generate Report';
            progressSpinner.style.display = 'none'; // Hide the spinner
        });
    });

    window.downloadReport = function(filename) {
        const link = document.createElement('a');
        const filePath = `../reports/${encodeURIComponent(filename)}`;
        fetch(filePath, { method: 'HEAD' })
            .then(response => {
                if (response.ok) {
                    link.href = filePath;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    showNotification('File not found!', 'error');
                }
            })
            .catch(error => {
                showNotification('An error occurred while checking the file', 'error');
            });
    };

    window.deleteReport = function(buttonElement, reportId) {
        const row = buttonElement.closest('tr');

        fetch('delete_report.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `report_id=${reportId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                row.remove();
                showNotification('Report deleted successfully', 'success');
            } else {
                showNotification(data.message || 'Failed to delete report', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An unexpected error occurred', 'error');
        });
    };

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.classList.add(
            'fixed', 'top-4', 'right-4', 'z-50', 'px-4', 'py-2', 'rounded-md',
            type === 'success' ? 'bg-green-500' : 'bg-red-500',
            'text-white', 'transition-all', 'duration-300', 'ease-in-out'
        );
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => document.body.removeChild(notification), 300);
        }, 3000);
    }
});
</script>




    <!-- SCRIPT TO FETCH AND DISPLAY THE REPORTS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        fetch('fetch_reports.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    populateGeneratedReports(data.reports);
                } else {
                    showNotification('Failed to load reports', 'error');
                }
            })
            .catch(error => {
                console.error('Error fetching reports:', error);
                showNotification('An error occurred while loading reports.', 'error');
            });
    });

    // Display reports
    function populateGeneratedReports(reports) {
        const tableBody = document.querySelector('#content-reports tbody');
        tableBody.innerHTML = ''; // Clear existing rows

        reports.forEach(report => {
            const row = `
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-2 md:px-4 border-b border-gray-200">
                        <span class="block text-sm md:text-base text-gray-900">${report.type}</span>
                    </td>
                    <td class="py-3 px-2 md:px-4 border-b border-gray-200">
                        <span class="block text-sm md:text-base text-gray-600">${report.date}</span>
                    </td>
                        <td class="py-3 px-2 md:px-4 border-b border-gray-200">
                        <span class="block text-sm md:text-base text-gray-600">${report.period}</span>
                    </td>
                    <td class="py-3 px-2 md:px-4 border-b border-gray-200 text-center">
                        <div class="flex justify-center space-x-2">
                            <button onclick="downloadReport('${report.filename}')" class="bg-green-600 text-white text-xs md:text-sm py-1 px-2 md:px-3 rounded-md shadow-md hover:bg-blue-600 transition duration-200">Download</button>
                            <button onclick="deleteReport(this, ${report.id})" class="bg-red-500 text-white text-xs md:text-sm py-1 px-2 md:px-3 rounded-md shadow-md hover:bg-red-600 transition duration-200">Delete</button>
                        </div>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    </script>


<!-- Initialize Charts -->
 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const years = <?php echo $years_json; ?> || [];
        let propertyAvailabilityChart = null;
        let propertyMaintenanceChart = null;
        let tenantOccupancyChart = null;
        let monthlyPaymentsChart = null;
        let rentalBalanceChart = null;

        // Define chart options first
        const tenantOccupancyOptions = {
            chart: { 
                type: 'pie', 
                height: '100%', 
                width: '100%',
                toolbar: {
                    show: true,
                    tools: {
                        download: true
                    },
                    export: {
                        csv: { filename: 'tenant_occupancy_report' },
                        png: { filename: 'tenant_occupancy_report' },
                        jpeg: { filename: 'tenant_occupancy_report' }
                    }
                }
            },
            series: [<?php echo "$occupied, $available, $underMaintenance"; ?>],
            labels: ['Occupied', 'Available', 'Under Maintenance'],
            colors: ['#e74c3c', '#228b22', '#3498db'],
            legend: {
                position: 'right',
                horizontalAlign: 'center',
                verticalAlign: 'middle',
                floating: false,
                offsetY: 0
            },
            plotOptions: {
                pie: {
                    customScale: 0.9
                }
            },
            responsive: [{
                breakpoint: 1024,
                options: {
                    chart: { width: '100%' },
                    legend: { position: 'bottom' }
                }
            }]
        };
        
        tenantOccupancyChart = new ApexCharts(
            document.querySelector("#tenant-occupancy-chart"), 
            tenantOccupancyOptions
        );
        tenantOccupancyChart.render();
        
        // 2. Property Availability Chart
        initPropertyAvailabilityChart();
     
        // 3. Monthly Payments Chart - Call the function instead of using static options
        loadMonthlyPaymentsChart();
        
        // Function to load monthly payments data and initialize chart
        function loadMonthlyPaymentsChart(year = new Date().getFullYear()) {
            fetch(`get_payments_ajax.php?year=${year}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }
                    
                    const monthlyPaymentsOptions = {
                        chart: { 
                            type: 'area', 
                            width: '100%', 
                            height: '100%',
                            toolbar: {
                                show: true
                            }
                        },
                        series: [{
                            name: 'Payments',
                            data: data.data
                        }],
                        xaxis: {
                            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                        },
                        yaxis: {
                            title: {
                                text: 'Amount (₱)'
                            },
                            labels: {
                                formatter: function(val) {
                                    return '₱' + val.toLocaleString();
                                }
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        colors: ['#2E93fA'],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.7,
                                opacityTo: 0.9,
                                stops: [0, 90, 100]
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return '₱' + val.toLocaleString();
                                }
                            }
                        },
                        title: {
                            text: `Monthly Payments - ${year}`,
                            align: 'center'
                        },
                        legend: {
                            position: 'top'
                        }
                    };

                    if (monthlyPaymentsChart) {
                        monthlyPaymentsChart.updateOptions(monthlyPaymentsOptions);
                    } else {
                        monthlyPaymentsChart = new ApexCharts(
                            document.querySelector("#monthly-payments-chart"), 
                            monthlyPaymentsOptions
                        );
                        monthlyPaymentsChart.render();
                    }

                    // If we have years data, create a year filter dropdown
                    if (data.years && data.years.length > 0) {
                        createPaymentYearFilter(data.years, year);
                    }
                })
                .catch(error => {
                    console.error('Error loading monthly payments data:', error);
                });
        }

        // Function to create year filter for payments chart
        function createPaymentYearFilter(years, selectedYear) {
            // Check if the filter already exists
            let yearFilter = document.getElementById('payment-year-filter');
            
            if (!yearFilter) {
                // Create the filter if it doesn't exist
                const chartContainer = document.querySelector("#monthly-payments-chart").closest('.chart-container');
                const titleElement = chartContainer.querySelector('h3');
                
                // Create a container for the title and filter
                const headerContainer = document.createElement('div');
                headerContainer.className = 'flex justify-between items-center mb-4';
                
                // Move the title into the container
                titleElement.parentNode.insertBefore(headerContainer, titleElement);
                headerContainer.appendChild(titleElement);
                
                // Create the select element
                yearFilter = document.createElement('select');
                yearFilter.id = 'payment-year-filter';
                yearFilter.className = 'px-3 py-1 border border-gray-300 rounded-md text-sm';
                
                // Add event listener
                yearFilter.addEventListener('change', function() {
                    loadMonthlyPaymentsChart(this.value);
                });
                
                // Add to the container
                headerContainer.appendChild(yearFilter);
            } else {
                // Clear existing options
                yearFilter.innerHTML = '';
            }
            
            // Add options to the filter
            years.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                option.selected = year == selectedYear;
                yearFilter.appendChild(option);
            });
        }

        
        // 4. Rental Balance Chart
        const rentalBalanceOptions = {
            chart: { 
                type: 'donut', 
                width: '100%', 
                height: '100%',
                toolbar: {
                    show: true,
                    tools: {
                        download: true
                    },
                    export: {
                        csv: { filename: 'rental_balance_report' },
                        png: { filename: 'rental_balance_report' },
                        jpeg: { filename: 'rental_balance_report' }
                    }
                }
            },
            series: [60, 40],
            labels: ['Paid', 'Outstanding'],
            colors: ['#27ae60', '#e74c3c'],
            legend: {
                position: 'bottom'
            }
        };
        
        rentalBalanceChart = new ApexCharts(
            document.querySelector("#rental-balance-chart"), 
            rentalBalanceOptions
        );
        rentalBalanceChart.render();
        
        // 5. Maintenance Chart
        if (years && years.length > 0) {
            const yearFilter = document.getElementById('maintenance-year-filter');
            const initialYear = years[0];
            yearFilter.value = initialYear;
            
            // Set up year filter change handler
            yearFilter.addEventListener('change', function() {
                const selectedYear = this.value;
                fetchMaintenanceData(selectedYear)
                    .then(data => initMaintenanceChart(data, selectedYear))
                    .catch(error => console.error('Error loading maintenance data:', error));
            });
            
            // Load initial data
            fetchMaintenanceData(initialYear)
                .then(data => initMaintenanceChart(data, initialYear))
                .catch(error => console.error('Error loading initial maintenance data:', error));
        }
        
        // Property Availability Chart Function - This should be INSIDE the DOMContentLoaded handler
        function initPropertyAvailabilityChart() {
            fetch('get_availability_data.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const propertyAvailabilityOptions = {
                        chart: { 
                            type: 'bar',
                            height: '100%',
                            width: '100%',
                            stacked: true,
                            toolbar: {
                                show: true
                            }
                        },
                        series: [
                            {
                                name: 'Available',
                                data: data.series[0].data,
                                color: '#228b22' // Green
                            },
                            {
                                name: 'Occupied',
                                data: data.series[1].data,
                                color: '#e74c3c' // Red
                            },
                            {
                                name: 'Maintenance',
                                data: data.series[2].data,
                                color: '#3498db' // Blue
                            }
                        ],
                        xaxis: {
                            categories: data.categories,
                            labels: {
                                rotate: -45,
                                style: {
                                    fontSize: '12px'
                                }
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '55%',
                                endingShape: 'rounded'
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val) {
                                return val.toString();
                            },
                            style: {
                                fontSize: '12px',
                                colors: ['#fff']
                            }
                        },
                        title: {
                            text: 'Unit Status by Floor',
                            align: 'center',
                            style: {
                                fontSize: '14px'
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Number of Units'
                            },
                            min: 0
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'center'
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return val + " units"
                                }
                            }
                        }
                    };

                    if (propertyAvailabilityChart) {
                        propertyAvailabilityChart.updateOptions(propertyAvailabilityOptions);
                    } else {
                        propertyAvailabilityChart = new ApexCharts(
                            document.querySelector("#property-availability-chart"), 
                            propertyAvailabilityOptions
                        );
                        propertyAvailabilityChart.render();
                    }
                })
                .catch(error => {
                    console.error('Error loading availability data:', error);
                });
        }
        
        // Maintenance Data Fetching Function
        function fetchMaintenanceData(year) {
            const url = `get_maintenance_data.php?year=${year}`;
            
            return fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.completed || !data.pending || !data.inProgress) {
                        console.error('Invalid data format received:', data);
                        throw new Error('Invalid data format received');
                    }
                    return data;
                });
        }
        
        // Initialize Maintenance Chart Function
        function initMaintenanceChart(data, year) {
            const options = {
                chart: { 
                    type: 'line',
                    height: '100%',
                    width: '100%',
                    toolbar: {
                        show: true,
                        tools: { download: true }
                    }
                },
                series: [{
                    name: 'Completed',
                    data: data.completed,
                    color: '#10B981'
                }, {
                    name: 'Pending',
                    data: data.pending,
                    color: '#F59E0B'
                }, {
                    name: 'In Progress',
                    data: data.inProgress,
                    color: '#3B82F6'
                }],
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                },
                yaxis: {
                    title: { text: 'Number of Requests' },
                    min: 0,
                    forceNiceScale: true
                },
                title: {
                    text: `Maintenance Requests - ${year}`,
                    align: 'center'
                },
                stroke: { 
                    curve: 'smooth',
                    width: 2
                },
                markers: {
                    size: 4
                },
                legend: {
                    position: 'top'
                }
            };

            if (propertyMaintenanceChart) {
                propertyMaintenanceChart.updateOptions(options);
            } else {
                propertyMaintenanceChart = new ApexCharts(
                    document.querySelector("#property-maintenance-chart"), 
                    options
                );
                propertyMaintenanceChart.render();
            }
        }
        
        // Function to load rental balance data with tenant metrics
        function loadRentalBalanceChart(sortBy = 'amount') {
            fetch('get_rental_balance_data.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        let series, labels, subtitle, title;
                        
                        if (sortBy === 'tenants') {
                            // Display tenant-based percentages
                            series = [
                                data.data.paid_tenants_percentage, 
                                data.data.outstanding_tenants_percentage
                            ];
                            subtitle = `${data.data.paid_tenants} Tenants Fully Paid | ${data.data.outstanding_tenants} Tenants with Balance`;
                            title = "Rental Balance - By Tenants";
                        } else {
                            // Display amount-based percentages (default)
                            series = [
                                data.data.paid_percentage, 
                                data.data.outstanding_percentage
                            ];
                            subtitle = `Paid: ₱${formatNumber(data.data.paid)} | Outstanding: ₱${formatNumber(data.data.outstanding)}`;
                            title = "Rental Balance - By Amount";
                        }
                        
                        const rentalBalanceOptions = {
                            chart: { 
                                type: 'donut', 
                                width: '100%', 
                                height: '100%',
                                toolbar: {
                                    show: true,
                                    tools: {
                                        download: true
                                    },
                                    export: {
                                        csv: { filename: 'rental_balance_report' },
                                        png: { filename: 'rental_balance_report' },
                                        jpeg: { filename: 'rental_balance_report' }
                                    }
                                }
                            },
                            series: series,
                            labels: ['Paid', 'Outstanding'],
                            colors: ['#27ae60', '#e74c3c'],
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                y: {
                                    formatter: function(value) {
                                        return value + '%';
                                    }
                                }
                            },
                            title: {
                                text: title,
                                align: 'center'
                            },
                            subtitle: {
                                text: subtitle,
                                align: 'center'
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Total',
                                                formatter: function (w) {
                                                    return sortBy === 'tenants' 
                                                        ? data.data.total_tenants + ' Tenants'
                                                        : '₱' + formatNumber(data.data.total);
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                            dataLabels: {
                                formatter: function (val, opts) {
                                    return opts.w.config.series[opts.seriesIndex] + '%';
                                }
                            }
                        };
                        
                        if (rentalBalanceChart) {
                            rentalBalanceChart.updateOptions(rentalBalanceOptions);
                        } else {
                            rentalBalanceChart = new ApexCharts(
                                document.querySelector("#rental-balance-chart"), 
                                rentalBalanceOptions
                            );
                            rentalBalanceChart.render();
                        }
                        
                        // Create or update sorting toggle
                        createRentalBalanceSorter(sortBy);
                    } else {
                        console.error('Error loading rental balance data:', data);
                    }
                })
                .catch(error => {
                    console.error('Error loading rental balance data:', error);
                });
        }

        // Function to create sort-by toggle for rental balance chart
        function createRentalBalanceSorter(currentSortBy) {
            const chartContainer = document.querySelector("#rental-balance-chart").closest('.chart-container');
            const titleElement = chartContainer.querySelector('h3');
            
            // Check if the filter container already exists
            let headerContainer = chartContainer.querySelector('.filter-container');
            
            if (!headerContainer) {
                // Create a container for the title and filter
                headerContainer = document.createElement('div');
                headerContainer.className = 'flex justify-between items-center mb-4 filter-container';
                
                // Move the title into the container
                titleElement.parentNode.insertBefore(headerContainer, titleElement);
                headerContainer.appendChild(titleElement);
            } else {
                // Clear existing filters but keep the title
                const title = headerContainer.querySelector('h3');
                headerContainer.innerHTML = '';
                headerContainer.appendChild(title);
            }
            
            // Create toggle selector
            const sortSelect = document.createElement('select');
            sortSelect.id = 'rental-balance-sort';
            sortSelect.className = 'px-3 py-1 border border-gray-300 rounded-md text-sm';
            
            // Add options
            const options = [
                { value: 'amount', text: 'View by Amount' },
                { value: 'tenants', text: 'View by Tenants' }
            ];
            
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                optionElement.selected = option.value === currentSortBy;
                sortSelect.appendChild(optionElement);
            });
            
            // Add event listener
            sortSelect.addEventListener('change', function() {
                loadRentalBalanceChart(this.value);
            });
            
            // Add filter to container
            headerContainer.appendChild(sortSelect);
        }
        
        // Helper function to format number with commas
        function formatNumber(num) {
            return num.toLocaleString('en-US', { maximumFractionDigits: 2, minimumFractionDigits: 2 });
        }
        
        // Replace the static rental balance chart with dynamic data loading
        loadRentalBalanceChart();
    });
    
</script>


</body>
</html>