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
    <title>Rental Payment</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
   
   <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        input:disabled {
            background-color: #f3f4f6;
        }
        .hidden {
             display: none; 
        }

        

    </style>


</head>

<body class="bg-gray-100">

    <!-- Include Navbar -->
    <?php include('navbar.php'); ?>

    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Tab Navigation -->
    <div class="sm:ml-64 p-8 mt-20">
        <div class="flex mb-4 border-b">
            <!-- Rental Payment Tab -->
            <button onclick="showTab('payment')" id="payment-tab" class="px-4 py-2 text-gray-700 focus:outline-none border-b-4 border-transparent hover:border-blue-600">Rental Payment</button>
            <!-- Payment History Tab -->
            <button onclick="showTab('paymentHistory')" id="paymentHistory-tab" class="px-4 py-2 text-gray-700 focus:outline-none ml-4 border-b-4 border-transparent hover:border-blue-600">Payment History</button>
        </div>

        <!-- Main Content -->
        <div class="flex gap-8">
            <!-- Payment Form -->
            <div id="payment" class="bg-white shadow-lg rounded-lg p-6 w-full max-w-md">
                <h2 class="text-2xl font-semibold mb-6 text-center">Rental Payment</h2>

                <!-- GCash Logo -->
                <div class="flex justify-center mb-6">
                    <img src="../images/gcash.png" alt="GCash Logo" width="150" height="50">
                </div>

                <form id="payment-form">
                    <!-- Tenant Name -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="tenant-name">Tenant Name</label>
                        <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                            <i class="fas fa-user text-gray-500"></i>
                            <input type="text" id="tenant-name" required class="ml-2 w-full outline-none" placeholder="Enter your name">
                        </div>
                    </div>

                    <!-- Unit No -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="unit-no">Unit No</label>
                        <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                            <i class="fas fa-home text-gray-500"></i>
                            <input type="text" id="unit-no" required class="ml-2 w-full outline-none" placeholder="Enter your unit number">
                        </div>
                    </div>

                    <!-- Rental Period -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="rental-period">Rental Period</label>
                        <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                            <i class="fas fa-calendar text-gray-500"></i>
                            <input type="date" id="rental-period" required class="ml-2 w-full outline-none">
                        </div>
                    </div>

                    <!-- Amount (PHP) -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="amount">Amount (PHP)</label>
                        <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                            <i class="fas fa-money-bill-wave text-gray-500"></i>
                            <input type="number" id="amount" required class="ml-2 w-full outline-none" placeholder="Enter amount">
                        </div>
                    </div>

                    <!-- Pay Button -->
                    <button type="submit" id="pay-button" class="bg-blue-600 text-white rounded-lg px-4 py-2 w-full hover:bg-blue-700 transition">
                        Pay Now with GCash
                    </button>
                </form>
            </div>


    <div id="paymentHistory" class="hidden bg-white shadow-lg rounded-lg p-6 w-full">
    <h2 class="text-xl font-semibold mb-6 text-left">Payment History</h2>

    <!-- Search and Filter Section -->
    <div class="mb-4 flex flex-col md:flex-row items-center justify-between">
        <div class="flex items-center space-x-2 w-full lg:w-1/3 md:w-auto">
            <!-- Status Filter -->
            <div class="relative">
                <select id="status-filter" class="border border-gray-300 rounded-lg px-4 py-2 pr-8 outline-none appearance-none" onchange="filterTable()">
                    <option value="">All Status</option>
                    <option value="Received">Received</option>
                    <option value="Pending">Pending</option>
                </select>
                <span class="absolute inset-y-0 right-2 flex items-center pointer-events-none text-gray-500">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </div>

            <!-- Keyword Search -->
            <div class="relative flex-grow md:w-1/2 lg:w-1/3">
                <input type="text" id="search-keyword" placeholder="Search..." oninput="filterTable()" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Print Button -->
        <div class="mt-4 md:mt-0 md:ml-4">
          <button onclick="printPaymentHistory()" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-print mr-2"></i>Print
         </button>
        </div>

    </div>

    <!-- Payment History Table -->
    <div class="overflow-x-auto">
        <table id="payment-history-table" class="w-full min-w-full border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="py-2 px-4 border">Unit No</th>
                    <th class="py-2 px-4 border">Amount (PHP)</th>
                    <th class="py-2 px-4 border">Date of Payment</th>
                    <th class="py-2 px-4 border">Reference No</th> 
                    <th class="py-2 px-4 border">Status</th>
                </tr>
            </thead>
            <tbody id="payment-history-table-body">
                <!-- Sample Data -->
                <tr>
                    <td class="py-2 px-4 border">Unit 101</td>
                    <td class="py-2 px-4 border">PHP 15,000</td>
                    <td class="py-2 px-4 border">2024-10-05</td>
                    <td class="py-2 px-4 border">REF001</td>
                    <td class="py-2 px-4 border"><span class="text-green-600">Received</span></td>
                </tr>
                <tr>
                    <td class="py-2 px-4 border">Unit 102</td>
                    <td class="py-2 px-4 border">PHP 18,000</td>
                    <td class="py-2 px-4 border">2024-10-10</td>
                    <td class="py-2 px-4 border">REF002</td>
                    <td class="py-2 px-4 border"><span class="text-red-600">Pending</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


    </div>
</div>

    <script>
        function showTab(tabId) {
            document.getElementById('payment').classList.add('hidden');
            document.getElementById('paymentHistory').classList.add('hidden');
            document.getElementById(tabId).classList.remove('hidden');

            // Update active tab border color
            document.getElementById('payment-tab').classList.remove('border-blue-600');
            document.getElementById('paymentHistory-tab').classList.remove('border-blue-600');
            document.getElementById(tabId + '-tab').classList.add('border-blue-600');
        }
    </script>

   <!-- JavaScript for Print Table Functionality -->
    <script>
     function printPaymentHistory() {
            // Get the table HTML
            const table = document.getElementById("payment-history-table").outerHTML;

            // Create a new window
            const newWindow = window.open('', '', 'width=800,height=600');

            // Write the HTML to the new window
            newWindow.document.write(`
                <html>
                    <head>
                        <title>Payment History</title>
                        <link rel="stylesheet" href="https://cdn.tailwindcss.com">
                        <style>
                            body {
                                font-family: 'Poppins', sans-serif;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                            }
                            th, td {
                                border: 1px solid #ccc;
                                padding: 8px;
                                text-align: left;
                            }
                            th {
                                background-color: #f2f2f2;
                            }
                        </style>
                    </head>
                    <body>
                        <h2>Payment History</h2>
                        ${table}
                    </body>
                </html>
            `);

            // Close the document to finish loading
            newWindow.document.close();

            // Trigger the print dialog
            newWindow.print();
        }
    </script>


    <!-- JavaScript for Filter and Search Functionality -->
    <script>
        function filterTable() {
            const statusFilter = document.getElementById("status-filter").value.toLowerCase();
            const searchKeyword = document.getElementById("search-keyword").value.toLowerCase();
            const tableBody = document.getElementById("payment-history-table-body");
            const rows = tableBody.getElementsByTagName("tr");

            for (const row of rows) {
                const cells = row.getElementsByTagName("td");
                const refNumber = cells[0]?.textContent.toLowerCase() || "";
                const unitNo = cells[1]?.textContent.toLowerCase() || "";
                const amount = cells[2]?.textContent.toLowerCase() || "";
                const dateOfPayment = cells[3]?.textContent.toLowerCase() || "";
                const status = cells[4]?.textContent.toLowerCase() || "";

                const matchesStatus = !statusFilter || status.includes(statusFilter);
                const matchesKeyword = !searchKeyword || refNumber.includes(searchKeyword) || unitNo.includes(searchKeyword) || amount.includes(searchKeyword) || dateOfPayment.includes(searchKeyword);

                if (matchesStatus && matchesKeyword) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        }
    </script>


</body>
</html>
