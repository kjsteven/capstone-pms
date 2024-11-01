<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit Information</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
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

<!-- Include Navbar -->
<?php include('navbar.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebar.php'); ?>

<!-- Main Content -->
<div class="p-8 mt-20 sm:ml-64">
    <h2 class="text-2xl font-semibold mb-6">View Unit Information</h2>
    
    <!-- Search Bar and Print Button -->
    <div class="mb-4 flex items-center">
        <input type="text" id="search" placeholder="Search for units..." class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none mr-4">
        <button id="printBtn" class="bg-blue-600 text-white rounded-lg px-4 py-2">Print</button>
    </div>
    
    <!-- Unit Table -->
    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
        <table class="min-w-full border-collapse table" id="unitTable">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-4 py-2">Unit Type</th>
                    <th class="border px-4 py-2">Unit No</th>
                    <th class="border px-4 py-2">Rent From</th>
                    <th class="border px-4 py-2">Rent Until</th>
                    <th class="border px-4 py-2">Monthly Rate</th>
                    <th class="border px-4 py-2">Invoice Date</th>
                    <th class="border px-4 py-2">Last Payment</th>
                    <th class="border px-4 py-2">Outstanding Balance</th>
                    <th class="border px-4 py-2">Payable Months</th>
                </tr>
            </thead>
            <tbody id="unitTableBody">
                <tr>
                    <td class="border px-4 py-2">Warehouse</td>
                    <td class="border px-4 py-2">101</td>
                    <td class="border px-4 py-2">Jan 1, 2023</td>
                    <td class="border px-4 py-2">Dec 31, 2023</td>
                    <td class="border px-4 py-2">$2,500.00</td>
                    <td class="border px-4 py-2">Jan 13, 2023</td>
                    <td class="border px-4 py-2">Oct 6, 2024</td>
                    <td class="border px-4 py-2">$2,500.00</td> <!-- Outstanding balance after last payment -->
                    <td class="border px-4 py-2">12</td> <!-- Payable months from Rent From to Rent Until -->
                </tr>
                <tr>
                    <td class="border px-4 py-2">Commercial</td>
                    <td class="border px-4 py-2">102</td>
                    <td class="border px-4 py-2">Feb 1, 2023</td>
                    <td class="border px-4 py-2">Jul 31, 2023</td>
                    <td class="border px-4 py-2">$2,500.00</td>
                    <td class="border px-4 py-2">Jan 13, 2023</td>
                    <td class="border px-4 py-2">N/A</td>
                    <td class="border px-4 py-2">$5,000.00</td> <!-- Outstanding balance after last payment -->
                    <td class="border px-4 py-2">6</td> <!-- Payable months from Rent From to Rent Until -->
                </tr>
                <tr>
                    <td class="border px-4 py-2">Office</td>
                    <td class="border px-4 py-2">201</td>
                    <td class="border px-4 py-2">Mar 1, 2023</td>
                    <td class="border px-4 py-2">May 31, 2023</td>
                    <td class="border px-4 py-2">$1,800.00</td>
                    <td class="border px-4 py-2">Jan 13, 2023</td>
                    <td class="border px-4 py-2">May 6, 2023</td>
                    <td class="border px-4 py-2">$0.00</td> <!-- Outstanding balance after last payment -->
                    <td class="border px-4 py-2">3</td> <!-- Payable months from Rent From to Rent Until -->
                </tr>
                <!-- Add more rows as needed -->
            </tbody>
        </table>
    </div>
</div>

<script>
    // Function to calculate payable months based on Rent From and Rent Until dates
    function calculatePayableMonths(rentFrom, rentUntil) {
        const startDate = new Date(rentFrom);
        const endDate = new Date(rentUntil);
        const monthsDifference = (endDate.getFullYear() - startDate.getFullYear()) * 12 + (endDate.getMonth() - startDate.getMonth());
        return monthsDifference >= 0 ? monthsDifference : 0;
    }

    // Search Functionality
    const searchInput = document.getElementById('search');
    const unitTableBody = document.getElementById('unitTableBody');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        const rows = unitTableBody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    const txtValue = cells[j].textContent || cells[j].innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            rows[i].style.display = found ? "" : "none";
        }
    });

    // Print Functionality
    document.getElementById('printBtn').addEventListener('click', function() {
        const printContent = document.getElementById('unitTable').outerHTML;

        // Create a new window to print
        const newWin = window.open('', '', 'width=800,height=600');
        newWin.document.write(`
            <html>
                <head>
                    <title>Print Unit Information</title>
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
                            border: 1px solid #000;
                            padding: 8px;
                            text-align: left;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>View Unit Information</h1>
                    </div>
                    ${printContent}
                </body>
            </html>
        `);
        
        newWin.document.close(); // Close the document to complete loading
        newWin.onload = function() { // Wait until the new document is fully loaded
            newWin.print();
            newWin.close();
        };
    });
</script>

</body>
</html>
