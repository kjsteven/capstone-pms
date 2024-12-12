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

// Query to fetch maintenance request details with user name
$query = "
    SELECT 
        mr.id, 
        u.name, 
        mr.unit, 
        mr.issue, 
        mr.description, 
        mr.service_date, 
        mr.image, 
        mr.status
    FROM maintenance_requests mr
    JOIN users u ON mr.user_id = u.user_id"; // Join with users table on user_id
$result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$result) {
    die('Error: ' . mysqli_error($conn));
}

$toast_message = null;
$toast_type = null;

// For AJAX requests, handle them first before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Ensure no previous output
    ob_clean(); 

    // Set JSON content type
    header('Content-Type: application/json');

    // Validate and process the request
    if (isset($_POST['update_status']) && isset($_POST['status']) && isset($_POST['request_id'])) {
        $status = $_POST['status'];
        $request_id = intval($_POST['request_id']);

        // Prepare and execute update
        $update_query = "UPDATE maintenance_requests SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);

        if ($stmt) {
            $stmt->bind_param("si", $status, $request_id);

            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => "Status updated successfully!",
                    'newStatus' => $status
                ]);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => "Failed to update status: " . $stmt->error
                ]);
            }
            $stmt->close();
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => "Failed to prepare statement: " . $conn->error
            ]);
        }
        exit; // Critical to prevent any additional output
    }

    // If we reach here, it means the request was invalid
    echo json_encode([
        'status' => 'error', 
        'message' => "Invalid request"
    ]);
    exit;
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
    <title>Maintenance Requests</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>

<!-- Include Navbar -->
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>

<div class="sm:ml-64 p-8 mt-20 mx-auto">
<h1 class="text-xl font-semibold text-gray-800 mb-6">List of Maintenance Requests</h1>

<!-- Users Tab Content -->
<div class="tab-content block">
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
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Request ID</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Unit No</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Issue</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Service Date</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-sm font-semibold text-gray-800 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['id']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['name']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['unit']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['issue']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['description']; ?></td>
                            <td class="px-6 py-3 border-b border-gray-200"><?php echo $row['service_date']; ?></td>
                           <td class="px-6 py-3 border-b border-gray-200">
                                <?php if ($row['image']) : ?>
                                    <a href="../users/<?php echo htmlspecialchars($row['image']); ?>" target="_blank" class="text-blue-600">View Image</a>
                                <?php else : ?>
                                    No Image
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-3 border-b border-gray-200">
                                <!-- Status and Update Button -->
                                 <form method="POST" class="update-form">
                                        <div class="flex items-center space-x-4 text-center">
                                            <select name="status" class="px-4 py-2 border rounded-md w-full">
                                                <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="In Progress" <?php echo $row['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="Completed" <?php echo $row['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            </select>
                                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="update_status" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none">
                                                Update
                                            </button>
                                        </div>
                                </form>
                            </td>

                            <td class="px-6 py-3 border-b border-gray-200">
                                <button type="submit" name="update_status" value="Update" class="text-blue-600">Edit</button> | 
                                <a href="archive_request.php?id=<?php echo $row['id']; ?>" class="text-red-600">Archive</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
</div>

<script src="../node_modules/feather-icons/dist/feather.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>

<script>
    // Initialize Feather Icons
    feather.replace();

    function initializeToastify(message, type) {
        if (message) {
            Toastify({
                text: message,
                backgroundColor: type === 'info' 
                    ? 'linear-gradient(to right, #00b09b, #96c93d)' 
                    : 'linear-gradient(to right, #ff5f6d, #ffc371)',
                className: type,
                duration: 3000
            }).showToast();
        }
    }

    // Get all forms with the update-form class
    const updateForms = document.querySelectorAll('.update-form');

    updateForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            formData.append('update_status', '1'); // Explicitly add update_status

            const statusSelect = this.querySelector('select[name="status"]');
            const row = this.closest('tr');

            fetch('', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    const statusSelectedValue = formData.get('status');
                    statusSelect.value = statusSelectedValue;

                    // Update all select elements in the row
                    const selectElements = row.querySelectorAll('select[name="status"]');
                    selectElements.forEach(select => {
                        select.value = statusSelectedValue;
                    });

                    initializeToastify(data.message, data.type);
                } else {
                    initializeToastify(data.message, data.type);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                initializeToastify(`Failed to update status: ${error.message}`, 'error');
            });
        });
    });
</script>

</body>
</html>