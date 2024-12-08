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



// Close the connection
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
        <form class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700" for="report-type">
                    Report Type
                </label>
                <select class="mt-1 block w-full py-2 px-3 mt-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="report-type">
                    <option>Unit Occupancy Report</option>
                    <option>Property Availability Report</option>
                    <option>Property Maintenance Report</option>
                    <option>Monthly Payments Report</option>
                    <option>Rental Balance Report</option>
                </select>
            </div>
            <div>
                <button class="bg-indigo-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-indigo-700 transition duration-200" type="submit">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    <h2 class="text-lg md:text-md font-semibold mt-8 md:mt-12 mb-4 md:mb-6">Generated Reports</h2>
    <div class="bg-gray-50 p-4 md:p-6 border border-gray-200 rounded-lg shadow-md overflow-x-auto">
        <table class="w-full bg-white">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-2 md:px-4 text-left text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wider">Report Name</th>
                    <th class="py-2 px-2 md:px-4 text-left text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wider">Date Generated</th>
                    <th class="py-2 px-2 md:px-4 text-center text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-2 md:px-4 border-b border-gray-200">
                        <span class="block text-sm md:text-base text-gray-900">Sample Report</span>
                    </td>
                    <td class="py-3 px-2 md:px-4 border-b border-gray-200">
                        <span class="block text-sm md:text-base text-gray-600">Sample Date</span>
                    </td>
                    <td class="py-3 px-2 md:px-4 border-b border-gray-200 text-center">
                        <div class="flex justify-center space-x-2">
                            <button class="bg-green-600 text-white text-xs md:text-sm py-1 px-2 md:px-3 rounded-md shadow-md hover:bg-blue-600 transition duration-200">Download</button>
                            <button class="bg-red-500 text-white text-xs md:text-sm py-1 px-2 md:px-3 rounded-md shadow-md hover:bg-red-600 transition duration-200">Delete</button>
                        </div>
                    </td>
                </tr>
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
                    <h3 class="font-semibold text-md text-gray-800 mb-4">Property Maintenance</h3>
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



 <!-- SCRIPT FOR REPORT GENERATION -->   
<script>

document.addEventListener('DOMContentLoaded', function() {
    const reportForm = document.querySelector('#content-reports form');
    const generatedReportsTable = document.querySelector('#content-reports tbody');

    reportForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Get selected report type
        const reportType = document.getElementById('report-type').value;

        // Disable submit button and show loading state
        const submitButton = e.target.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = 'Generating Report...';

        // Create FormData to send report type and other potential parameters
        const formData = new FormData();
        formData.append('report_type', reportType);
        formData.append('action', 'generate_report');

        // Send AJAX request to generate report
        fetch('generate_report.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Add new report to the generated reports table
                const newReportRow = `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 md:px-4 border-b border-gray-200">
                            <span class="block text-sm md:text-base text-gray-900">${reportType}</span>
                        </td>
                        <td class="py-3 px-2 md:px-4 border-b border-gray-200">
                            <span class="block text-sm md:text-base text-gray-600">${new Date().toLocaleDateString()}</span>
                        </td>
                        <td class="py-3 px-2 md:px-4 border-b border-gray-200 text-center">
                            <div class="flex justify-center space-x-2">
                                <button onclick="downloadReport('${data.filename}')" class="bg-green-600 text-white text-xs md:text-sm py-1 px-2 md:px-3 rounded-md shadow-md hover:bg-blue-600 transition duration-200">Download</button>
                                <button onclick="deleteReport(this, ${data.report_id})" class="bg-red-500 text-white text-xs md:text-sm py-1 px-2 md:px-3 rounded-md shadow-md hover:bg-red-600 transition duration-200">Delete</button>
                            </div>
                        </td>
                    </tr>
                `;
                generatedReportsTable.insertAdjacentHTML('afterbegin', newReportRow);

                // Show success notification
                showNotification('Report generated successfully', 'success');


                 // Refresh the page after report generation
                 setTimeout(() => {
                    location.reload();
                }, 1000); // 1000 ms = 1 second
            } else {
                // Show error notification
                showNotification(data.message || 'Failed to generate report', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An unexpected error occurred', 'error');
        })
        .finally(() => {
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.innerHTML = 'Generate Report';
        });
    });

    // Function to download report
    window.downloadReport = function(filename) {
        // Create a temporary link to trigger download
        const link = document.createElement('a');
        link.href = `../reports/${filename}`;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // Function to delete report
    window.deleteReport = function(buttonElement, reportId) {
        const row = buttonElement.closest('tr');

        fetch('delete_report.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `report_id=${reportId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove row from table
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

    // Notification function
    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.classList.add(
            'fixed', 'top-4', 'right-4', 'z-50', 'px-4', 'py-2', 'rounded-md', 
            type === 'success' ? 'bg-green-500' : 'bg-red-500', 
            'text-white', 'transition-all', 'duration-300', 'ease-in-out'
        );
        notification.textContent = message;

        // Add to body
        document.body.appendChild(notification);

        // Remove after 3 seconds
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



        // ApexCharts Data for Property Availability Report
        var propertyAvailabilityOptions = {
            chart: { type: 'bar', width: '100%', height: '100%' },
            series: [{
                name: 'Available Units',
                data: [10, 15, 5, 20, 12, 24, 16, 7, 3, 10, 8, 3],
            }],
            xaxis: {
                categories: ['Ground Floor', 'Mezzanine', 'First Floor', 'Second Floor', 'Third Floor', 'Fourth Floor', 'Fifth Floor', 'Sixth Floor', 'Seventh Floor', 'Eight Floor', 'Nineth Floor', 'Tenth Floor']
            },
            legend: {
                position: 'top'
            }
        };
        var propertyAvailabilityChart = new ApexCharts(document.querySelector("#property-availability-chart"), propertyAvailabilityOptions);
        propertyAvailabilityChart.render();

        // ApexCharts Data for Property Maintenance Report
        var propertyMaintenanceOptions = {
            chart: { type: 'line', width: '100%', height: '100%' },
            series: [{
                name: 'Completed',
                data: [5, 8, 12, 15, 20, 5, 8, 12, 15, 20, 22, 14],
            }, {
                name: 'Pending',
                data: [0, 2, 5, 3, 1, 0, 2, 4, 2, 7, 4, 5],
            }],
            xaxis: {
                categories: ['Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec']
            },
            legend: {
                position: 'top'
            }
        };
        var propertyMaintenanceChart = new ApexCharts(document.querySelector("#property-maintenance-chart"), propertyMaintenanceOptions);
        propertyMaintenanceChart.render();

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
