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
$query = "SELECT user_id, name, email, phone, role FROM users";
$result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$result) {
    die('Error: ' . mysqli_error($conn));
}

// Query to fetch staff details (you can adjust as needed)
$query_staff = "SELECT staff_id, Name, Email, Specialty, Phone_Number FROM staff";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
<h1 class="text-xl font-semibold text-gray-800 mb-6">Manage Users and Staff</h1>
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
                    <input type="text" id="search-keyword" placeholder="Search..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                    <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <!-- Print Button -->
                <button id="print-button" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Print</button>
            </div>
        
            <div id="roleUpdateNotification" class="hidden fixed top-20 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg opacity-0 transition-all duration-500">
                Role updated successfully!
            </div>

            <!-- Table Form -->
            <div class="overflow-x-auto shadow-lg rounded-lg">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">User ID</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Phone Number</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php
                        // Display fetched user data
                        while ($row = mysqli_fetch_assoc($result)) :
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['role']); ?></td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                            <form action="manageUsers.php" method="POST" class="inline-block">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>" />
                                <select name="role" class="border px-2 py-1 rounded">
                                    <option value="Admin" <?php if ($row['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
                                    <option value="User" <?php if ($row['role'] == 'User') echo 'selected'; ?>>User</option>
                                </select>
                                <button type="submit" class="px-2 py-1 ml-2 bg-blue-600 text-white rounded">Update</button>
                            </form>
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
                            <input type="text" id="search-keyword-staff" placeholder="Search..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                            <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        
                        <!-- Add Staff Button aligned to the right -->
                        <button id="add-staff-btn" type="button" class="px-4 ml-4 py-2 bg-blue-600 text-white rounded-lg">
                            Add Account
                        </button>
                    </div>

                <!-- Table Form for displaying staff list -->
                <div class="overflow-x-auto shadow-lg rounded-lg mt-4">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Staff ID</th>
                                <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Specialty</th>
                                <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Phone Number</th>
                                <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php
                            // Display fetched staff data
                            while ($row_staff = mysqli_fetch_assoc($result_staff)) :
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['staff_id']); ?></td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Name']); ?></td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Specialty']); ?></td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row_staff['Phone_Number']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

            
        <!-- Modal for Adding Staff -->
    <div id="add-staff-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex justify-center items-center">
        <div class="modal-content bg-white p-6 rounded-lg shadow-lg max-w-lg w-full">
            <h3 class="text-xl font-semibold mb-4">Add Staff</h3>
            <form id="staff-form" class="space-y-4">
                <div>
                    <label for="staff-name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="staff-name" name="staff-name" class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" required>
                </div>

                <div>
                    <label for="staff-email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="staff-email" name="staff-email" class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" required>
                </div>

                <div>
                <label for="staff-specialty" class="block text-sm font-medium text-gray-700">Specialty</label>
                <select id="staff-specialty" name="staff-specialty" class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" required>
                    <option value="">Select a specialty</option>
                    <option value="general-maintenance">General Maintenance</option>
                    <option value="electrical-specialist">Electrical Specialist</option>
                    <option value="plumbing-specialist">Plumbing Specialist</option>
                    <option value="hvac-technician">HVAC Technician</option>
                    <option value="carpentry-structural-repairs">Carpentry and Structural Repairs</option>
                    <option value="groundskeeper-landscaping">Groundskeeper/Landscaping Specialist</option>
                    <option value="appliance-technician">Appliance Technician</option>
                    <option value="painting-finishing-specialist">Painting and Finishing Specialist</option>
                    <option value="pest-control-specialist">Pest Control Specialist</option>
                    <option value="security-systems-technician">Security Systems Technician</option>
                </select>
               </div>

                <div>
                    <label for="staff-phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="text" id="staff-phone" name="staff-phone" class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" required>
                </div>

                <div class="mt-4 flex justify-between">
                    <button type="button" id="close-modal" class="px-4 py-2 bg-gray-400 text-white rounded-md">Cancel</button>
                    <button type="submit" id="submit-staff" class="px-4 py-2 bg-blue-600 text-white rounded-md">Add Staff</button>
                </div>

            </form>
        </div>
    </div>

  
</div>

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
    // Search function for Users
    document.getElementById('search-keyword').addEventListener('input', function() {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('#tab-content-users tbody tr');
        
        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const phone = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            const role = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
            
            if (name.includes(keyword) || email.includes(keyword) || phone.includes(keyword) || role.includes(keyword)) {
                row.style.display = '';  // Show row
            } else {
                row.style.display = 'none';  // Hide row
            }
        });
    });

    // Search function for Staff
    document.getElementById('search-keyword-staff').addEventListener('input', function() {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('#tab-content-staff tbody tr');
        
        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const phone = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            const role = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
            
            if (name.includes(keyword) || email.includes(keyword) || phone.includes(keyword) || role.includes(keyword)) {
                row.style.display = '';  // Show row
            } else {
                row.style.display = 'none';  // Hide row
            }
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
    // Function to show notification
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

<script>
// Open Modal when 'Add Staff' Button is Clicked
document.getElementById('add-staff-btn').addEventListener('click', function() {
    document.getElementById('add-staff-modal').classList.remove('hidden');
});

// Close Modal when 'Cancel' Button is Clicked
document.getElementById('close-modal').addEventListener('click', function() {
    document.getElementById('add-staff-modal').classList.add('hidden');
});

// Prevent modal from closing if clicking inside the modal content (important fix for the closing issue)
document.getElementById('add-staff-modal').querySelector('.modal-content').addEventListener('click', function(e) {
    e.stopPropagation();  // This prevents the click from reaching the backdrop
});

// Close Modal when clicking outside of the modal content (on the backdrop)
document.getElementById('add-staff-modal').addEventListener('click', function(e) {
    if (e.target === document.getElementById('add-staff-modal')) {
        document.getElementById('add-staff-modal').classList.add('hidden');
    }
});

// Handle staff form submission
document.getElementById('staff-form').addEventListener('submit', function(e) {
    e.preventDefault();

    // Collect form data
    const staffName = document.getElementById('staff-name').value;
    const staffEmail = document.getElementById('staff-email').value;
    const staffSpecialty = document.getElementById('staff-specialty').value;
    const staffExperience = document.getElementById('staff-experience').value;
    const staffFee = document.getElementById('staff-fee').value;
    const staffPhone = document.getElementById('staff-phone').value;

    // Simulate sending staff info to the server and email
    fetch('add_staff.php', {
        method: 'POST',
        body: JSON.stringify({
            name: staffName,
            email: staffEmail,
            specialty: staffSpecialty,
            phone: staffPhone
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert('Staff added successfully!');
        document.getElementById('add-staff-modal').classList.add('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the staff.');
    });
});
</script>

</body>
</html>
