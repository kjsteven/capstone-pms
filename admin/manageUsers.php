<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require '../vendor/autoload.php'; 
require '../config/config.php';

start_secure_session();


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../authentication/login.php'); // Adjust the path as necessary
    exit();
}

// Query to fetch user details
$query = "SELECT user_id, name, email, phone, role, status FROM users";
$result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$result) {
    die('Error: ' . mysqli_error($conn));
}

// Query to fetch staff details 
$query_staff = "SELECT staff_id, Name, Email, Specialty, Phone_Number, status FROM staff";
$result_staff = mysqli_query($conn, $query_staff);

if (!$result_staff) {
    die('Error: ' . mysqli_error($conn));
}



// Check if a role change is requested
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    // Update the role in the database using a prepared statement
    $query = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt === false) {
        die('Error preparing the query: ' . mysqli_error($conn));
    }

    // Bind the parameters to the prepared statement
    mysqli_stmt_bind_param($stmt, 'si', $role, $user_id);

    // Execute the prepared statement
    if (mysqli_stmt_execute($stmt)) {
        // Optionally, display a success message
        $message = "Role updated successfully.";
        // Re-fetch the updated user list after the role change
        $query = "SELECT user_id, name, email, phone, role FROM users";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            die('Error: ' . mysqli_error($conn));
        }
        
        // Include the script to trigger the notification on the page
        echo "<script>showNotification();</script>";
    } else {
        $message = "Error updating role: " . mysqli_stmt_error($stmt);
    }

    // Close the statement
    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <title>Manage Users</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        
    </style>
</head>
<body class="bg-gray-100">

<!-- Include Navbar -->
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>



<div class="sm:ml-64 p-8 mt-20 mx-auto">
<h1 class="text-xl font-semibold text-gray-800 mb-6">List of Users and Staff</h1>
    <!-- Tabs Navigation (Placed at the top) -->
    <div class="flex mb-6 border-b">
        <button id="tab-users" class="py-2 px-4 text-gray-700 focus:outline-none border-b-4 border-blue-600">Users</button>
        <button id="tab-staff" class="py-2 px-4 text-gray-700 focus:outline-none ml-4 border-b-4 border-transparent hover:border-blue-600">Staff</button>
    </div>


   <!-- Users Tab Content -->
<div id="tab-content-users" class="tab-content block">
    <form class="space-y-6">
        <!-- Search Bar and Print Button Form -->
        <div class="flex items-center space-x-4 mb-4">
            <div class="relative w-full sm:w-1/4">
                <input type="text" id="search-keyword" placeholder="Search by Name..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                <button type="button" id="search-button" class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                    <svg data-feather="search" class="w-4 h-4"></svg>
                </button>
            </div>

            <!-- Print Button -->
            <button id="print-button" class="px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2">
                <svg data-feather="printer" class="w-4 h-4"></svg>
                Print
            </button>
        </div>

        <!-- Table Form -->
        <div class="overflow-x-auto shadow-lg rounded-lg">
            <table class="min-w-full bg-white" id="users-table">
                <thead>
                    <tr>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">User ID</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Name</th>
                        <!-- Hidden columns -->
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Phone Number</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) :
                        // Set status based on the database value
                        $status = $row['status']; 

                        // Determine the class and icon based on the status
                        $status_class = ($status == 'active') ? 'bg-green-500' : 'bg-red-500';
                        $icon = ($status == 'active') ? 'check-circle' : 'x-circle';
                    ?>
                    <tr class="user-row hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['name']); ?></td>
                        <!-- Hidden columns -->
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200 email-column"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200 phone-column"><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200 role-column"><?php echo htmlspecialchars($row['role']); ?></td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200 role-column">
                            <form action="manageUsers.php" method="POST" class="flex items-center  mt-3">
                                <select name="role" class="border px-2 py-1 rounded mr-2">
                                    <option value="Admin" <?php if ($row['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
                                    <option value="User" <?php if ($row['role'] == 'User') echo 'selected'; ?>>User</option>
                                </select>
                                <button type="submit" class="px-2 py-1 bg-blue-600 text-white rounded ">Update</button>
                            </form>
                        </td>
                            <td class="hidden additional-info px-4 py-4 whitespace-no-wrap border-b border-gray-200">
                            <button class="flex items-center justify-center px-4 py-2 rounded text-white text-xs <?php echo $status_class; ?>">
                                <i data-feather="<?php echo $icon; ?>" class="w-4 h-4"></i>
                                <?php echo ucfirst($status); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<!-- Staff Tab Content -->
<div id="tab-content-staff" class="tab-content hidden">
    <form class="space-y-6">
        <!-- Search Bar and Add Staff Button -->
        <div class="relative flex items-center w-full">
            <!-- Search Bar -->
            <div class="relative w-full sm:w-1/4">
                <input type="text" id="search-keyword-staff" placeholder="Search by Name..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                <button type="button" id="search-button-staff" class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                    <svg data-feather="search" class="w-4 h-4"></svg>
                </button>
            </div>

            <a href="staff_form.php" class="px-4 py-2 ml-4 bg-blue-600 text-white rounded-lg flex items-center gap-2">
                <svg data-feather="plus" class="w-4 h-4"></svg>
                Add Account
            </a>
        </div>

        <!-- Table Form for displaying staff list -->
        <div class="overflow-x-auto shadow-lg rounded-lg mt-4">
            <table class="min-w-full bg-white" id="staff-table">
                <thead>
                    <tr>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Staff ID</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Name</th>
                        <!-- Hidden columns -->
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Specialty</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Phone Number</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php
                    // Display fetched staff data
                    while ($row_staff = mysqli_fetch_assoc($result_staff)) :
                    ?>
                    <tr class="staff-row hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['staff_id']); ?></td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Name']); ?></td>
                        <!-- Hidden columns -->
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Email']); ?></td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Specialty']); ?></td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Phone_Number']); ?></td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['status']); ?></td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                             <div class="action-buttons flex items-center gap-2">
                                <!-- Edit Button -->
                                <button class="px-2 py-2 bg-green-600 text-white rounded flex items-center text-sm" onclick="editStaff(<?php echo $row_staff['staff_id']; ?>)">
                                    <i data-feather="edit" class="mr-2 w-4 h-4"></i> Edit
                                </button>

                                <!-- Archive Button -->
                                <button class="px-2 py-2 bg-red-600 text-white rounded flex items-center text-sm" onclick="archiveStaff(<?php echo $row_staff['staff_id']; ?>)">
                                    <i data-feather="archive" class="mr-2 w-4 h-4"></i> Archive
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>


<script src="../node_modules/feather-icons/dist/feather.min.js"></script>

<script>
    // Initialize Feather Icons
    feather.replace();
</script>

<script>
    // Tab Navigation Functionality
    document.getElementById('tab-users').addEventListener('click', function() {
        showTabContent('users');
    });

    document.getElementById('tab-staff').addEventListener('click', function() {
        showTabContent('staff');
    });

    function showTabContent(tab) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => content.classList.add('hidden'));

        // Remove active class from all buttons
        const tabButtons = document.querySelectorAll('button');
        tabButtons.forEach(button => button.classList.remove('border-blue-600'));

        // Show the clicked tab content and add active class to the button
        if (tab === 'users') {
            document.getElementById('tab-content-users').classList.remove('hidden');
            document.getElementById('tab-users').classList.add('border-blue-600');
        } else if (tab === 'staff') {
            document.getElementById('tab-content-staff').classList.remove('hidden');
            document.getElementById('tab-staff').classList.add('border-blue-600');
        }
    }
</script>


<script>

document.addEventListener('DOMContentLoaded', function() {
    // Users Table Search
    const userSearchInput = document.getElementById('search-keyword');
    const userSearchButton = document.getElementById('search-button');
    const usersTable = document.getElementById('users-table');

    // Staff Table Search
    const staffSearchInput = document.getElementById('search-keyword-staff');
    const staffSearchButton = document.getElementById('search-button-staff');
    const staffTable = document.getElementById('staff-table');

    // Function to toggle additional info columns
    function toggleAdditionalColumns(table, show = false) {
        const additionalColumns = table.querySelectorAll('.additional-info');
        additionalColumns.forEach(column => {
            column.classList.toggle('hidden', !show);
        });
    }

    // Function to perform search
    function performSearch(searchInput, table, rowClass) {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const rows = table.querySelectorAll(`.${rowClass}`);

        rows.forEach(row => {
            // Find the name cell (second column)
            const nameCell = row.children[1];
            const name = nameCell.textContent.toLowerCase();

            // If search term is empty, reset to initial state
            if (searchTerm === '') {
                row.style.display = '';
                toggleAdditionalColumns(table, false);
                return;
            }

            // Show/hide rows based on search
            if (name.includes(searchTerm)) {
                row.style.display = '';
                toggleAdditionalColumns(table, true);
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Event listeners for users table search
    userSearchInput.addEventListener('input', function() {
        performSearch(userSearchInput, usersTable, 'user-row');
    });

    userSearchButton.addEventListener('click', function() {
        performSearch(userSearchInput, usersTable, 'user-row');
    });

    // Event listeners for staff table search
    staffSearchInput.addEventListener('input', function() {
        performSearch(staffSearchInput, staffTable, 'staff-row');
    });

    staffSearchButton.addEventListener('click', function() {
        performSearch(staffSearchInput, staffTable, 'staff-row');
    });
});

</script>



<script>
    // Print Function
    document.getElementById('print-button').addEventListener('click', function() {
        // Get the table content
        const tableContent = document.querySelector('.overflow-x-auto').innerHTML;

        // Create a temporary element to hold the content
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = tableContent;

        // Remove the "Action" column header (assuming it's the last column)
        const headers = tempDiv.querySelectorAll('th');
        const actionHeaderIndex = Array.from(headers).findIndex(header => header.textContent.trim() === 'Action');
        if (actionHeaderIndex !== -1) {
            headers[actionHeaderIndex].remove(); // Remove the "Action" header
        }

        // Remove the "Action" column from each row in the table
        const rows = tempDiv.querySelectorAll('tr');
        rows.forEach(row => {
            const columns = row.querySelectorAll('td');
            if (columns[actionHeaderIndex]) {
                columns[actionHeaderIndex].remove(); // Remove the "Action" column from the row
            }
        });

        // Open a new window for printing
        const printWindow = window.open('', '', 'height=600,width=800');

        // Write the HTML structure for the print window
        printWindow.document.write('<html><head><title>Users Table</title><style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
        printWindow.document.write('th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }');
        printWindow.document.write('th { background-color: #f2f2f2; font-weight: bold; }');
        printWindow.document.write('</style></head><body>');

        // Insert the modified table content into the print window
        printWindow.document.write(tempDiv.innerHTML);

        // Close the HTML structure and trigger print
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });
</script>

<script>
    
    // Function to show notification for Updating role
    function showNotification() {
        const notification = document.getElementById('roleUpdateNotification');
        notification.classList.remove('hidden');
        notification.classList.add('opacity-100');
        setTimeout(function() {
            notification.classList.remove('opacity-100');
            notification.classList.add('opacity-0');
            setTimeout(function() {
                notification.classList.add('hidden');
            }, 500);
        }, 3000); // Hide the notification after 3 seconds
    }

    // Trigger the notification if the role update was successful
    <?php if (isset($message) && $message == "Role updated successfully.") : ?>
        showNotification();
    <?php endif; ?>

</script>





</body>
</html>
