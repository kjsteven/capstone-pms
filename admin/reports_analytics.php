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
                        <option>Property Availability Report</option>
                        <option>Property Maintenance Report</option>
                        <option>Monthly Payments Report</option>
                        <option>Rental Balance Report</option>
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

        // Initialize all charts function
        function initializeAllCharts() {
            // Initialize tenant occupancy chart
            tenantOccupancyChart = new ApexCharts(
                document.querySelector("#tenant-occupancy-chart"), 
                tenantOccupancyOptions
            );
            tenantOccupancyChart.render();

            // Initialize property availability chart
            initPropertyAvailabilityChart();

            // Initialize maintenance chart
            if (years && years.length > 0) {
                const yearFilter = document.getElementById('maintenance-year-filter');
                const initialYear = years[0];
                yearFilter.value = initialYear;
                fetchMaintenanceData(initialYear)
                    .then(data => initMaintenanceChart(data, initialYear));
            }

            // Initialize other charts
            const monthlyPaymentsChart = new ApexCharts(
                document.querySelector("#monthly-payments-chart"), 
                monthlyPaymentsOptions
            );
            monthlyPaymentsChart.render();

            const rentalBalanceChart = new ApexCharts(
                document.querySelector("#rental-balance-chart"), 
                rentalBalanceOptions
            );
            rentalBalanceChart.render();
        }

        // Tab switching logic
        const tabs = document.querySelectorAll('[id^="tab-"]');
        const contentSections = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('border-blue-600', 'text-blue-600'));
                this.classList.add('border-blue-600', 'text-blue-600');
                
                contentSections.forEach(content => content.classList.add('hidden'));
                const contentId = this.id.replace('tab-', 'content-');
                document.getElementById(contentId).classList.remove('hidden');

                // Refresh charts when switching to analytics tab
                if (this.id === 'tab-analytics') {
                    if (propertyAvailabilityChart) {
                        initPropertyAvailabilityChart();
                    }
                    if (propertyMaintenanceChart) {
                        const yearFilter = document.getElementById('maintenance-year-filter');
                        fetchMaintenanceData(yearFilter.value)
                            .then(data => initMaintenanceChart(data, yearFilter.value));
                    }
                }
            });
        });

        // Show first tab by default
        tabs[0].classList.add('border-blue-600', 'text-blue-600');
        contentSections[0].classList.remove('hidden');

        // Initialize all charts when page loads
        initializeAllCharts();

        // Property Availability Chart Function
        function initPropertyAvailabilityChart() {
            console.log('Initializing property availability chart...');
            fetch('get_availability_data.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received availability data:', data);
                    
                    const options = {
                        // ... your existing propertyAvailabilityOptions ...
                        chart: { 
                            type: 'bar',
                            height: '100%',
                            width: '100%'
                        },
                        series: data.series,
                        xaxis: {
                            categories: data.categories,
                            labels: {
                                rotate: -45,
                                style: { fontSize: '12px' }
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
                            offsetY: -20
                        },
                        colors: ['#2196f3']
                    };

                    if (propertyAvailabilityChart) {
                        propertyAvailabilityChart.updateOptions(options);
                    } else {
                        propertyAvailabilityChart = new ApexCharts(
                            document.querySelector("#property-availability-chart"), 
                            options
                        );
                        propertyAvailabilityChart.render();
                    }
                })
                .catch(error => {
                    console.error('Error loading availability data:', error);
                });
        }


    });
</script>


<!-- Initialize Charts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the tab buttons and content sections
        const tabs = document.querySelectorAll('[id^="tab-"]');
        const contentSections = document.querySelectorAll('.tab-content');
        
        // Add event listeners to the tabs
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active classes from all tabs
                tabs.forEach(tab => tab.classList.remove('border-blue-600', 'text-blue-600'));
                // Add active class to the clicked tab
                tab.classList.add('border-blue-600', 'text-blue-600');
                
                // Hide all content sections
                contentSections.forEach(content => content.classList.add('hidden'));
                
                // Show the content corresponding to the clicked tab
                const contentId = tab.id.replace('tab-', 'content-');
                const contentToShow = document.getElementById(contentId);
                contentToShow.classList.remove('hidden');
            });
        });

        // Optionally, show the first tab's content by default
        tabs[0].classList.add('border-blue-600', 'text-blue-600');
        contentSections[0].classList.remove('hidden');
        
    
        // ApexCharts Data for Tenant Occupancy Report
        var occupied = <?php echo $occupied; ?>;
        var available = <?php echo $available; ?>;
        var underMaintenance = <?php echo $underMaintenance; ?>;

        // Calculate the total number of units
        var totalUnits = occupied + available + underMaintenance;

        var tenantOccupancyOptions = {
            chart: { 
                type: 'pie', 
                height: '100%', 
                width: '100%',
                toolbar: {
                    show: true,
                    tools: {
                        download: true,  // Show the download button in the toolbar
                    },
                    export: {
                        csv: {
                            filename: 'tenant_occupancy_report' // Customize the CSV file name
                        },
                        png: {
                            filename: 'tenant_occupancy_report' // Customize the PNG file name
                        },
                        jpeg: {
                            filename: 'tenant_occupancy_report' // Customize the JPG file name
                        },
                    }
                }
            },
            series: [occupied, available, underMaintenance],
            labels: ['Occupied', 'Available', 'Under Maintenance', 'Total Units: ' + totalUnits], // Add total units to the labels
            colors: ['#e74c3c', '#228b22', '#3498db', '#ffffff'], // Optionally add color for the total units label
            legend: {
                position: 'right', // Moves the legend to the right
                horizontalAlign: 'center', // Centers the legend horizontally
                verticalAlign: 'middle', // Centers the legend vertically
                floating: false,
                offsetY: 0, // Aligns the legend vertically in the middle
            },
            plotOptions: {
                pie: {
                    customScale: 0.9, // Scales the pie chart to fit within the container
                },
            },
            responsive: [{
                breakpoint: 1024, // For smaller screens (e.g., tablets)
                options: {
                    chart: { width: '100%' }, 
                    legend: { position: 'bottom' }, // Moves the legend to the bottom
                }
            }]
        };

        // Render the Pie Chart
        var tenantOccupancyChart = new ApexCharts(document.querySelector("#tenant-occupancy-chart"), tenantOccupancyOptions);
        tenantOccupancyChart.render();



        // ApexCharts Data for Property Availability
        function initPropertyAvailabilityChart() {
            fetch('get_availability_data.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Availability data:', data); // Debug log
                    const propertyAvailabilityOptions = {
                        chart: { 
                            type: 'bar', 
                            height: '100%',
                            width: '100%',
                            toolbar: {
                                show: true
                            }
                        },
                        series: data.series,
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
                                endingShape: 'rounded',
                                dataLabels: {
                                    position: 'top'
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val) {
                                return val.toString();
                            },
                            offsetY: -20,
                            style: {
                                fontSize: '12px',
                                colors: ['#304758']
                            }
                        },
                        colors: ['#2196f3'],
                        title: {
                            text: 'Available Units by Floor',
                            align: 'center',
                            style: {
                                fontSize: '14px'
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Number of Available Units'
                            },
                            min: 0
                        }
                    };

                    if (window.propertyAvailabilityChart) {
                        window.propertyAvailabilityChart.updateOptions(propertyAvailabilityOptions);
                    } else {
                        window.propertyAvailabilityChart = new ApexCharts(
                            document.querySelector("#property-availability-chart"), 
                            propertyAvailabilityOptions
                        );
                        window.propertyAvailabilityChart.render();
                    }
                })
                .catch(error => {
                    console.error('Error loading availability data:', error);
                });
        }

        // Add this to your existing DOMContentLoaded event listener:
        document.addEventListener('DOMContentLoaded', function() {
            // ...existing code...
            
            // Initialize property availability chart
            initPropertyAvailabilityChart();
            
            // Refresh chart when analytics tab is clicked
            document.getElementById('tab-analytics').addEventListener('click', function() {
                // ...existing code...
                initPropertyAvailabilityChart();
            });
        });


       
        
      // Property Maintenance Chart Initialization
        function fetchMaintenanceData(year) {
            console.log('Fetching data for year:', year);
            const url = `get_maintenance_data.php?year=${year}`;
            console.log('Fetch URL:', url);
            
            return fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received maintenance data:', data);
                    if (!data.completed || !data.pending || !data.inProgress) {
                        throw new Error('Invalid data format received');
                    }
                    return data;
                });
        }

        let propertyMaintenanceChart;
        const years = <?php echo $years_json; ?>;

        function initMaintenanceChart(data, year) {
            console.log('Initializing chart with data:', data); // Add this line to log the data being passed to the chart
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

        // Load initial data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const yearFilter = document.getElementById('maintenance-year-filter');
            
            // Load initial data for the first year
            if (years && years.length > 0) {
                const initialYear = years[0];
                yearFilter.value = initialYear;
                fetchMaintenanceData(initialYear)
                    .then(data => {
                        initMaintenanceChart(data, initialYear);
                    })
                    .catch(error => {
                        console.error('Error loading maintenance data:', error);
                    });
            }

            // Handle year changes
            yearFilter.addEventListener('change', function() {
                const selectedYear = this.value;
                fetchMaintenanceData(selectedYear)
                    .then(data => {
                        initMaintenanceChart(data, selectedYear);
                    })
                    .catch(error => {
                        console.error('Error loading maintenance data:', error);
                    });
            });
        });

        // Add this console.log to check if years are available
        console.log('Available years:', <?php echo $years_json; ?>);

        // Initialize the chart when the analytics tab is shown
        document.getElementById('tab-analytics').addEventListener('click', function() {
            if (years && years.length > 0) {
                const initialYear = years[0];
                console.log('Loading data for initial year:', initialYear);
                fetchMaintenanceData(initialYear)
                    .then(data => {
                        initMaintenanceChart(data, initialYear);
                    })
                    .catch(error => {
                        console.error('Error loading maintenance data:', error);
                    });
            }
        });

        // Year filter change handler
        document.getElementById('maintenance-year-filter').addEventListener('change', function() {
            const selectedYear = this.value;
            console.log('Year changed to:', selectedYear);
            fetchMaintenanceData(selectedYear)
                .then(data => {
                    initMaintenanceChart(data, selectedYear);
                })
                .catch(error => {
                    console.error('Error loading maintenance data:', error);
                });
        });

        // ApexCharts Data for Monthly Payments Report
        var monthlyPaymentsOptions = {
            chart: { type: 'area', width: '100%', height: '100%' },
            series: [{
                name: 'Payments',
                data: [2500, 3600, 3500, 4000, 4500, 4700, 3300, 3700, 5000, 6000, 2300, 3000]
            }],
            xaxis: {
                categories: ['Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec']
            },
            legend: {
                position: 'top'
            }
        };
        var monthlyPaymentsChart = new ApexCharts(document.querySelector("#monthly-payments-chart"), monthlyPaymentsOptions);
        monthlyPaymentsChart.render();

       // ApexCharts Data for Rental Balance Report
        var rentalBalanceOptions = {
            chart: { 
                type: 'donut', 
                width: '100%', 
                height: '100%',
                toolbar: {
                    show: true, // Show the toolbar
                    tools: {
                        download: true,  // Show the download button in the toolbar
                    },
                    export: {
                        csv: {
                            filename: 'rental_balance_report' // Customize the CSV file name
                        },
                        png: {
                            filename: 'rental_balance_report' // Customize the PNG file name
                        },
                        jpeg: {
                            filename: 'rental_balance_report' // Customize the JPG file name
                        },
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

        var rentalBalanceChart = new ApexCharts(document.querySelector("#rental-balance-chart"), rentalBalanceOptions);
        rentalBalanceChart.render();

    });
</script>

</body>
</html>