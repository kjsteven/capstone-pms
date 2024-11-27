<?php

require_once '../session/session_manager.php';
require '../session/db.php';
require '../config/config.php';

start_secure_session();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../authentication/login.php');
    exit();
}

$message = '';

// Fetch users to populate the select dropdown
$usersResult = $conn->query("SELECT * FROM users WHERE role = 'user'");
$users = $usersResult->fetch_all(MYSQLI_ASSOC);

// Fetch tenants along with their user name and tenant id
$tenantsResult = $conn->query("SELECT tenants.*, users.name AS user_name FROM tenants LEFT JOIN users ON tenants.user_id = users.user_id");
$tenants = $tenantsResult->fetch_all(MYSQLI_ASSOC);

// Create an array of user IDs that are already tenants
$tenantUserIds = array_column($tenants, 'user_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission for adding or editing tenants
    $user_id = $_POST['user_id'];
    $unit_rented = $_POST['unit_rented'];
    $rent_from = $_POST['rent_from'];
    $rent_until = $_POST['rent_until'];
    $monthly_rate = $_POST['monthly_rate'];
    $downpayment_amount = $_POST['downpayment_amount'];
    $registration_date = $_POST['registration_date'];

    // Calculate the rent period (months between rent_from and rent_until)
    $date1 = new DateTime($rent_from);
    $date2 = new DateTime($rent_until);
    $interval = $date1->diff($date2);
    $months = $interval->m + ($interval->y * 12);  // Add years to months

    // Calculate the total rent
    $total_rent = $months * $monthly_rate;

    // Calculate the outstanding balance (total rent - downpayment)
    $outstanding_balance = $total_rent - $downpayment_amount;

    // Check if we are updating an existing tenant
    if (isset($_POST['tenant_id']) && !empty($_POST['tenant_id'])) {
        $tenant_id = $_POST['tenant_id'];
        $stmt = $conn->prepare("UPDATE tenants SET user_id = ?, unit_rented = ?, rent_from = ?, rent_until = ?, monthly_rate = ?, outstanding_balance = ?, registration_date = ?, downpayment_amount = ?, updated_at = NOW() WHERE tenant_id = ?");
        $stmt->bind_param("issssssdi", $user_id, $unit_rented, $rent_from, $rent_until, $monthly_rate, $outstanding_balance, $registration_date, $downpayment_amount, $tenant_id);
        $stmt->execute();
        $message = 'Tenant successfully updated!';
    } else {
        // Insert a new tenant
        $stmt = $conn->prepare("INSERT INTO tenants (user_id, unit_rented, rent_from, rent_until, monthly_rate, outstanding_balance, registration_date, downpayment_amount, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("issssssd", $user_id, $unit_rented, $rent_from, $rent_until, $monthly_rate, $outstanding_balance, $registration_date, $downpayment_amount);
        $stmt->execute();
        $message = 'Tenant successfully added!';
    }

    // Return plain text response
    echo $message;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $tenant_id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM tenants WHERE tenant_id = ?");
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();

        echo 'Tenant successfully deleted.';
        exit();
    } elseif ($_GET['action'] === 'edit' && isset($_GET['id'])) {
        $tenant_id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tenant = $result->fetch_assoc();

        echo json_encode($tenant);
        exit();
    }
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
    <title>Manage Tenant</title>
    <link rel="icon" href="../images/logo.png" type="image/png">

    <!-- Toastify CSS -->
    <link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet">
</head>

<body>
    <?php include('navbarAdmin.php'); ?>
    <?php include('sidebarAdmin.php'); ?>

    <div class="sm:ml-64 p-8 mt-20 mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">List of Tenants</h1>
        </div>

        <div class="flex flex-wrap items-center gap-4 sm:gap-6 mb-4">
        <!-- Search Bar -->
        <div class="relative w-full sm:w-1/3 md:w-1/4">
            <input type="text" id="search-keyword" placeholder="Search..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
            <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
            <svg data-feather="search" class="w-4 h-4"></svg>
            </button>
        </div>

        <!-- Buttons Container -->
        <div class="flex space-x-4">
            <!-- Print Button -->
            <button id="printButton" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2 justify-center">
                <svg data-feather="printer" class="w-4 h-4"></svg>
                Print
            </button>
            
            <!-- Add Property Button -->
            <button id="newTenant" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2 justify-center">
                <svg data-feather="plus" class="w-4 h-4"></svg>
                New Tenant
            </button>

        </div>
    </div>

        
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left border border-gray-300">Tenant ID</th>
                        <th class="px-4 py-2 text-left border border-gray-300">Name</th>
                        <th class="px-4 py-2 text-left border border-gray-300">Unit</th>
                        <th class="px-4 py-2 text-left border border-gray-300">Rent From</th>
                        <th class="px-4 py-2 text-left border border-gray-300">Rent Until</th>
                        <th class="px-4 py-2 text-left border border-gray-300">Monthly Rate</th>
                        <th class="px-4 py-2 text-left border border-gray-300">Downpayment Amount</th>
                        <th class="px-4 py-2 text-left border border-gray-300">Outstanding Balance</th>
                        <th class="px-4 py-2 text-left border border-gray-300">Registration Date</th>
                        <th class="px-4 py-2 text-left border border-gray-300">Action</th>
                    </tr>
                </thead>
                <tbody id="tenantTableBody">
                    <?php foreach ($tenants as $index => $tenant) : ?>
                    <tr>
                        <td class="px-4 py-2 text left border border-gray-300"><?= $tenant['tenant_id'] ?></td>
                        <td class="px-4 py-2 text left border border-gray-300"><?= isset($tenant['user_name']) ? $tenant['user_name'] : 'N/A' ?></td>
                        <td class="px-4 py-2 text left border border-gray-300"><?= $tenant['unit_rented'] ?></td>
                        <td class="px-4 py-2 text left border border-gray-300"><?= $tenant['rent_from'] ?></td>
                        <td class="px-4 py-2 text left border border-gray-300"><?= $tenant['rent_until'] ?></td>
                        <td class="px-4 py-2 text-left border border-gray-300"><?= $tenant['monthly_rate'] ?></td>
                        <td class="px-4 py-2 text-left border border-gray-300"><?= $tenant['downpayment_amount'] ?></td>
                        <td class="px-4 py-2 text-left border border-gray-300"><?= $tenant['outstanding_balance'] ?></td>
                        <td class="px-4 py-2 text-left border border-gray-300"><?= $tenant['registration_date'] ?></td>
                        <td class="px-4 py-2 text-left border border-gray-300">
                            <button class="text-blue-500 hover:text-blue-700" onclick="editTenant(<?= $tenant['tenant_id'] ?>)">Edit</button>
                            <button class="text-red-500 hover:text-red-700" onclick="deleteTenant(<?= $tenant['tenant_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Adding/Editing Tenant -->
    <div id="tenantModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full sm:w-96">
            <h2 id="modalTitle" class="text-xl font-semibold mb-4">New Tenant</h2>
            <form id="tenantForm" method="POST">
                <input type="hidden" id="tenant_id" name="tenant_id">
                
                <div class="mb-4">
                    <label for="user_id" class="block text-sm font-semibold text-gray-700">Select User</label>
                    <select name="user_id" id="user_id" class="w-full border border-gray-300 rounded px-4 py-2">
                        <?php foreach ($users as $user) : ?>
                            <?php if (!in_array($user['user_id'], $tenantUserIds)) : ?>
                                <option value="<?= $user['user_id'] ?>"><?= $user['name'] ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="unit_rented" class="block text-sm font-semibold">Unit Rented</label>
                    <input type="text" id="unit_rented" name="unit_rented" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>

                <div class="mb-4">
                    <label for="rent_from" class="block text-sm font-semibold">Rent From</label>
                    <input type="date" id="rent_from" name="rent_from" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>

                <div class="mb-4">
                    <label for="rent_until" class="block text-sm font-semibold">Rent Until</label>
                    <input type="date" id="rent_until" name="rent_until" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>

                <div class="mb-4">
                    <label for="monthly_rate" class="block text-sm font-semibold">Monthly Rate</label>
                    <input type="number" id="monthly_rate" name="monthly_rate" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>

                <div class="mb-4">
                    <label for="downpayment_amount" class="block text-sm font-semibold">Downpayment Amount</label>
                    <input type="number" id="downpayment_amount" name="downpayment_amount" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>

                <div class="mb-4">
                    <label for="registration_date" class="block text-sm font-semibold">Registration Date</label>
                    <input type="date" id="registration_date" name="registration_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" id="cancelButton" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" id="saveButton" class="px-4 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-600">Save</button>
                </div>
            </form>
        </div>
    </div>


    <script src="../node_modules/feather-icons/dist/feather.min.js"></script>

    <script>
        // Initialize Feather Icons
        feather.replace();
    </script>

    <!-- Include Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>

    <script>
        // Show the modal for creating a new tenant
        document.getElementById('newTenant').addEventListener('click', function () {
            document.getElementById('tenantModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'New Tenant';
            document.getElementById('tenantForm').reset();
        });

        // Close the modal when cancel button is clicked
        document.getElementById('cancelButton').addEventListener('click', function () {
            document.getElementById('tenantModal').classList.add('hidden');
        });

         // Handle form submission
        document.getElementById('tenantForm').addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent the default form submission (no page reload)

            const formData = new FormData(this);
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(message => {
                // Show a success notification with a specific message
                Toastify({
                    text: "Tenant Added Successfully",  // The success message
                    backgroundColor: "green",  // Green background for success
                    duration: 2000,  // Duration of 3 seconds
                    close: true  // Show close button for manual dismissal
                }).showToast();

                // Delay the page reload to ensure the toast is visible
                setTimeout(() => {
                    window.location.reload(); // Reload the page after showing notification
                }, 1000);  // Wait a little longer than the toast's duration to reload
            })
            .catch(error => {
                // Show an error notification with a custom message
                Toastify({
                    text: "Error saving tenant data. Please try again.",  // The error message
                    backgroundColor: "red",  // Red background for error
                    duration: 3000,  // Duration of 3 seconds
                    close: true  // Show close button for manual dismissal
                }).showToast();
            });
        });
  
        //Print Function
        document.getElementById('printButton').addEventListener('click', function () {
        // Save the current HTML
        const originalContent = document.body.innerHTML;

        // Extract the table content only
        const tableContent = document.querySelector('table').outerHTML;

        // Replace body content with only the table
        document.body.innerHTML = `
            <html>
                <head>
                    <title>Print Table</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 0;
                            padding: 0;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 20px 0;
                            font-size: 12px;
                            text-align: left;
                        }
                        th, td {
                            padding: 8px;
                            border: 1px solid #ddd;
                            word-wrap: break-word; /* Ensures content wraps in narrow columns */
                        }
                        th {
                            background-color: #f4f4f4;
                        }
                    </style>
                </head>
                <body>
                    ${tableContent}
                </body>
            </html>
        `;

        // Print the table
        window.print();

        // Restore the original HTML after printing
        document.body.innerHTML = originalContent;

        // Reload the scripts (to restore JavaScript functionality)
        window.location.reload();
    });

      

        // Edit tenant
        function editTenant(tenantId) {
            fetch(`?action=edit&id=${tenantId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('tenantModal').classList.remove('hidden');
                    document.getElementById('tenant_id').value = data.tenant_id;
                    document.getElementById('user_id').value = data.user_id;
                    document.getElementById('unit_rented').value = data.unit_rented;
                    document.getElementById('rent_from').value = data.rent_from;
                    document.getElementById('rent_until').value = data.rent_until;
                    document.getElementById('monthly_rate').value = data.monthly_rate;
                    document.getElementById('downpayment_amount').value = data.downpayment_amount;
                    document.getElementById('registration_date').value = data.registration_date;
                    document.getElementById('modalTitle').textContent = 'Edit Tenant';
                });
        }

        // Delete tenant
        function deleteTenant(tenantId) {
            if (confirm('Are you sure you want to delete this tenant?')) {
                fetch(`?action=delete&id=${tenantId}`, {
                    method: 'GET'
                })
                .then(response => response.text())
                .then(message => {
                    // Show a success notification for tenant deletion
                    Toastify({
                        text: "Tenant Deleted Successfully",  // The success message
                        backgroundColor: "green",  // Green background for success
                        duration: 2000,  // Duration of 3 seconds
                        close: true  // Show close button for manual dismissal
                    }).showToast();

                    // Delay the page reload to ensure the toast is visible
                    setTimeout(() => {
                        window.location.reload(); // Reload the page after showing notification
                    }, 1000);  // Wait a little longer than the toast's duration to reload
                })
                .catch(error => {
                    // Show an error notification in case of failure
                    Toastify({
                        text: "Error deleting tenant. Please try again.",  // The error message
                        backgroundColor: "red",  // Red background for error
                        duration: 3000,  // Duration of 3 seconds
                        close: true  // Show close button for manual dismissal
                    }).showToast();
                });
            }
        }


          // Function to filter tenants based on search input
          document.getElementById('search-keyword').addEventListener('input', function() {
            let searchKeyword = this.value.toLowerCase();
            let tenantRows = document.querySelectorAll('#tenantTableBody tr');
            
            tenantRows.forEach(function(row) {
                let name = row.cells[1].textContent.toLowerCase();
                let unit = row.cells[2].textContent.toLowerCase();
                let rentFrom = row.cells[3].textContent.toLowerCase();
                let rentUntil = row.cells[4].textContent.toLowerCase();
                
                // Check if any of the row's columns match the search keyword
                if (name.includes(searchKeyword) || unit.includes(searchKeyword) || rentFrom.includes(searchKeyword) || rentUntil.includes(searchKeyword)) {
                    row.style.display = '';  // Show row
                } else {
                    row.style.display = 'none';  // Hide row
                }
            });
        });

    </script>
</body>

</html>

