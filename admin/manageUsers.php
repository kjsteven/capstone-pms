<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require '../vendor/autoload.php'; 
require '../config/config.php';
require_once '../session/audit_trail.php';

start_secure_session();


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../authentication/login.php'); // Adjust the path as necessary
    exit();
}

// Pagination settings
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;

// Modify the users query
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total'];
$total_users_pages = ceil($total_users / $entriesPerPage);

$query = "SELECT user_id, name, email, phone, role, status FROM users LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $entriesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Modify the staff query
$total_staff_query = "SELECT COUNT(*) as total FROM staff";
$total_staff_result = $conn->query($total_staff_query);
$total_staff = $total_staff_result->fetch_assoc()['total'];
$total_staff_pages = ceil($total_staff / $entriesPerPage);

$query_staff = "SELECT staff_id, Name, Email, Specialty, Phone_Number, status FROM staff LIMIT ? OFFSET ?";
$stmt_staff = $conn->prepare($query_staff);
$stmt_staff->bind_param('ii', $entriesPerPage, $offset);
$stmt_staff->execute();
$result_staff = $stmt_staff->get_result();

// Check if a role change is requested
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = $_POST['role'];

    // Validate role
    $allowed_roles = ['Admin', 'User'];
    if (!in_array($role, $allowed_roles)) {
        die('Invalid role specified');
    }

    // Get the old role before update
    $old_role_query = "SELECT role FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($old_role_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old_role = $result->fetch_assoc()['role'];

    // Update the role
    $update_query = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $role, $user_id);

    if ($stmt->execute()) {
        // Log the role change
        $details = "Changed user (ID: $user_id) role from $old_role to $role";
        logActivity($_SESSION['user_id'], "Update User Role", $details);
        
        $message = "Role updated successfully.";
        
        // Refresh the users list
        $query = "SELECT user_id, name, email, phone, role, status FROM users LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $entriesPerPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $message = "Error updating role: " . $stmt->error;
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
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Show entries:</label>
                <select id="entriesPerPage" class="border rounded px-2 py-1.5" onchange="changeEntries(this.value, 'users')">
                    <option value="10" <?php echo $entriesPerPage == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?php echo $entriesPerPage == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo $entriesPerPage == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $entriesPerPage == 100 ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
            <div class="relative w-full sm:w-1/3">
                <input type="text" id="search-keyword" placeholder="Search by Name..." 
                    class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300">
                <button type="button" id="search-button" 
                        class="absolute right-0 top-0 h-full px-3 bg-blue-600 text-white rounded-r-lg">
                    <svg data-feather="search" class="w-4 h-4"></svg>
                </button>
            </div>
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
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider sensitive-info hidden">Email</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider sensitive-info hidden">Phone Number</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider sensitive-info hidden">Role</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider sensitive-info hidden">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) :
                        // Set status based on the database value
                        $status = $row['status']; 

                        // Enhanced status styling
                        $statusBg = $status == 'active' ? 'bg-green-100' : 'bg-red-100';
                        $statusText = $status == 'active' ? 'text-green-800' : 'text-red-800';
                        $statusDot = $status == 'active' ? 'bg-green-400' : 'bg-red-400';
                    ?>
                    <tr class="user-row hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 sensitive-info hidden"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 sensitive-info hidden"><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 sensitive-info hidden"><?php echo htmlspecialchars($row['role']); ?></td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium <?php echo $statusBg . ' ' . $statusText; ?>">
                                <span class="flex-shrink-0 w-2 h-2 mr-1.5 rounded-full <?php echo $statusDot; ?>"></span>
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 sensitive-info hidden">
                            <form action="" method="POST" class="flex items-center space-x-2">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['user_id']); ?>">
                                <select name="role" class="border px-2 py-1 rounded">
                                    <option value="Admin" <?php echo ($row['role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="User" <?php echo ($row['role'] == 'User') ? 'selected' : ''; ?>>User</option>
                                </select>
                                <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Update
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Users Pagination controls -->
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $entriesPerPage, $total_users); ?> of <?php echo $total_users; ?> entries
            </div>
            <div class="flex gap-2">
                <?php if($total_users_pages > 1): ?>
                    <?php for($i = 1; $i <= $total_users_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&entries=<?php echo $entriesPerPage; ?>" 
                        class="px-3 py-1 border rounded <?php echo $page === $i ? 'bg-blue-600 text-white' : 'text-gray-600'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Staff Tab Content -->
<div id="tab-content-staff" class="tab-content hidden">
    <form class="space-y-6">
        <!-- Search Bar and Add Staff Button -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Show entries:</label>
                <select id="entriesPerPageStaff" class="border rounded px-2 py-1.5" onchange="changeEntries(this.value, 'staff')">
                    <option value="10" <?php echo $entriesPerPage == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?php echo $entriesPerPage == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo $entriesPerPage == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $entriesPerPage == 100 ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
            <div class="relative w-full sm:w-1/3">
                <input type="text" id="search-keyword-staff" placeholder="Search by Name..." 
                    class="w-full p-2.5 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300">
                <button type="button" id="search-button-staff" 
                        class="absolute right-0 top-0 h-full px-3 bg-blue-600 text-white rounded-r-lg">
                    <svg data-feather="search" class="w-4 h-4"></svg>
                </button>
            </div>
            <a href="staff_form.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2">
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
                        // Convert status to lowercase for consistent comparison
                        $status = strtolower($row_staff['status']);
                        
                        // Determine status styles
                        switch($status) {
                            case 'available':
                                $statusBg = 'bg-green-100';
                                $statusText = 'text-green-800';
                                $statusDot = 'bg-green-400';
                                break;
                            case 'busy':
                                $statusBg = 'bg-yellow-100';
                                $statusText = 'text-yellow-800';
                                $statusDot = 'bg-yellow-400';
                                break;
                            case 'suspended':
                                $statusBg = 'bg-red-100';
                                $statusText = 'text-red-800';
                                $statusDot = 'bg-red-400';
                                break;
                            default:
                                $statusBg = 'bg-gray-100';
                                $statusText = 'text-gray-800';
                                $statusDot = 'bg-gray-400';
                        }
                    ?>
                    <tr class="staff-row hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['staff_id']); ?></td>
                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Name']); ?></td>
                        <!-- Hidden columns -->
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Email']); ?></td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Specialty']); ?></td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Phone_Number']); ?></td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium <?php echo $statusBg . ' ' . $statusText; ?>">
                                <span class="flex-shrink-0 w-2 h-2 mr-1.5 rounded-full <?php echo $statusDot; ?>"></span>
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td class="hidden additional-info px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                            <div class="flex space-x-2">
                                <button type="button" 
                                    onclick="event.preventDefault(); event.stopPropagation(); openEditModal(<?php echo htmlspecialchars($row_staff['staff_id']); ?>, 
                                    '<?php echo htmlspecialchars($row_staff['Name']); ?>', 
                                    '<?php echo htmlspecialchars($row_staff['Email']); ?>', 
                                    '<?php echo htmlspecialchars($row_staff['Specialty']); ?>', 
                                    '<?php echo htmlspecialchars($row_staff['Phone_Number']); ?>', 
                                    '<?php echo htmlspecialchars($row_staff['status']); ?>')" 
                                    class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"/>
                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                    </svg>
                                    Edit
                                </button>
                                <button onclick="confirmSuspend(<?php echo htmlspecialchars($row_staff['staff_id']); ?>)" 
                                    class="inline-flex items-center px-3 py-2 <?php echo $row_staff['status'] === 'suspended' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'; ?> text-white text-sm font-medium rounded-md transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"/>
                                    </svg>
                                    <?php echo $row_staff['status'] === 'suspended' ? 'Activate' : 'Suspend'; ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Staff Pagination controls -->
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $entriesPerPage, $total_staff); ?> of <?php echo $total_staff; ?> entries
            </div>
            <div class="flex gap-2">
                <?php if($total_staff_pages > 1): ?>
                    <?php for($i = 1; $i <= $total_staff_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&entries=<?php echo $entriesPerPage; ?>&tab=staff" 
                        class="px-3 py-1 border rounded <?php echo $page === $i ? 'bg-blue-600 text-white' : 'text-gray-600'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
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

    // Function to toggle sensitive info columns for users table
    function toggleSensitiveColumns(show = false) {
        const sensitiveColumns = document.querySelectorAll('.sensitive-info');
        sensitiveColumns.forEach(column => {
            if (show) {
                column.classList.remove('hidden');
            } else {
                column.classList.add('hidden');
            }
        });
    }

    // Function to toggle additional info columns for staff table
    function toggleAdditionalColumns(table, show = false) {
        const additionalColumns = table.querySelectorAll('.additional-info');
        additionalColumns.forEach(column => {
            column.classList.toggle('hidden', !show);
        });
    }

    // Function to perform search for users table
    function performUsersSearch(searchInput) {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const rows = usersTable.querySelectorAll('.user-row');

        rows.forEach(row => {
            const nameCell = row.children[1]; // Name is in second column
            const name = nameCell.textContent.toLowerCase();

            if (searchTerm === '') {
                row.style.display = '';
                toggleSensitiveColumns(false);
            } else {
                if (name.includes(searchTerm)) {
                    row.style.display = '';
                    toggleSensitiveColumns(true);
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    // Function to perform search for staff table
    function performStaffSearch(searchInput) {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const rows = staffTable.querySelectorAll('.staff-row');

        rows.forEach(row => {
            const nameCell = row.children[1];
            const name = nameCell.textContent.toLowerCase();

            if (searchTerm === '') {
                row.style.display = '';
                toggleAdditionalColumns(staffTable, false);
            } else {
                if (name.includes(searchTerm)) {
                    row.style.display = '';
                    toggleAdditionalColumns(staffTable, true);
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    // Event listeners for users table search
    userSearchInput.addEventListener('input', function() {
        performUsersSearch(userSearchInput);
    });

    userSearchButton.addEventListener('click', function() {
        performUsersSearch(userSearchInput);
    });

    // Event listeners for staff table search
    staffSearchInput.addEventListener('input', function() {
        performStaffSearch(staffSearchInput);
    });

    staffSearchButton.addEventListener('click', function() {
        performStaffSearch(staffSearchInput);
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

    function changeEntries(value, tab) {
        const url = new URL(window.location.href);
        url.searchParams.set('entries', value);
        url.searchParams.set('page', '1');
        if (tab === 'staff') {
            url.searchParams.set('tab', 'staff');
        }
        window.location.href = url.toString();
    }

    // Maintain active tab after page reload
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab === 'staff') {
            showTabContent('staff');
        }
    });

</script>

<div id="editStaffModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full">
        <h2 class="text-2xl font-bold mb-4">Edit Staff Information</h2>
        <form id="editStaffForm" class="space-y-4">
            <input type="hidden" id="editStaffId" name="staff_id">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" id="editName" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" disabled>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="editEmail" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" disabled>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Specialty</label>
                <input type="text" id="editSpecialty" name="specialty" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="text" id="editPhone" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select id="editStatus" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="Available">Available</option>
                    <option value="Busy">Busy</option>
                    <option value="Active">Active</option>
                    <option value="Suspended">Suspended</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(staffId, name, email, specialty, phone, status) {
    document.getElementById('editStaffId').value = staffId;
    document.getElementById('editName').value = name;
    document.getElementById('editEmail').value = email;
    document.getElementById('editSpecialty').value = specialty;
    document.getElementById('editPhone').value = phone;
    document.getElementById('editStatus').value = status;
    document.getElementById('editStaffModal').classList.remove('hidden');
    document.getElementById('editStaffModal').classList.add('flex');
}

function closeEditModal() {
    document.getElementById('editStaffModal').classList.add('hidden');
    document.getElementById('editStaffModal').classList.remove('flex');
}

document.getElementById('editStaffForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent the default form submission
    e.stopPropagation(); // Stop event bubbling
    
    const formData = new FormData(this);
    
    // Add loading state to submit button
    const submitButton = this.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.innerHTML = 'Saving...';
    submitButton.disabled = true;

    // Add the current user's ID to the form data
    formData.append('current_user_id', <?php echo $_SESSION['user_id']; ?>);

    fetch('update_staff.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Toastify({
                text: "Staff information updated successfully",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#4CAF50"
            }).showToast();
            closeEditModal();
            // Reload the page after a short delay
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message || 'Error updating staff information');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toastify({
            text: error.message || "An error occurred while updating staff information",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#F44336"
        }).showToast();
    })
    .finally(() => {
        // Reset button state
        submitButton.innerHTML = originalButtonText;
        submitButton.disabled = false;
    });
});

// Add event listener to prevent modal from closing when clicking inside
document.querySelector('#editStaffModal .bg-white').addEventListener('click', function(e) {
    e.stopPropagation();
});

// Add event listener to close modal when clicking outside
document.getElementById('editStaffModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

function confirmSuspend(staffId) {
    if (confirm("Are you sure you want to change this staff member's status?")) {
        fetch('toggle_staff_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                staff_id: staffId,
                current_user_id: <?php echo $_SESSION['user_id']; ?>
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Toastify({
                    text: data.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: data.newStatus === 'Suspended' ? "#EF4444" : "#10B981",
                    className: "toast-notification"
                }).showToast();
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Error updating staff status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toastify({
                text: error.message || "An error occurred while updating staff status",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#F44336",
                className: "toast-notification"
            }).showToast();
        });
    }
}

// Update status styles based on new status options
function getStatusStyle(status) {
    switch(status.toLowerCase()) {
        case 'available':
            return {
                bg: 'bg-green-100',
                text: 'text-green-800',
                dot: 'bg-green-400'
            };
        case 'busy':
            return {
                bg: 'bg-yellow-100',
                text: 'text-yellow-800',
                dot: 'bg-yellow-400'
            };
        case 'active':
            return {
                bg: 'bg-blue-100',
                text: 'text-blue-800',
                dot: 'bg-blue-400'
            };
        case 'suspended':
            return {
                bg: 'bg-red-100',
                text: 'text-red-800',
                dot: 'bg-red-400'
            };
        default:
            return {
                bg: 'bg-gray-100',
                text: 'text-gray-800',
                dot: 'bg-gray-400'
            };
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($message)): ?>
        Toastify({
            text: "<?php echo addslashes($message); ?>",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "<?php echo strpos($message, 'successfully') !== false ? '#4CAF50' : '#F44336'; ?>",
        }).showToast();
    <?php endif; ?>
});
</script>

</body>
</html>
