<?php
// Include database connection
require '../session/db.php';

session_start();

// Fetch only active properties from database
$query = "SELECT * FROM property WHERE position = 'active' ORDER BY unit_id DESC";
$result = mysqli_query($conn, $query);

// Handle the status update via AJAX (on success, show Toastify notification)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a status update
    if (isset($_POST['status']) && isset($_POST['unit_id'])) {
        $status = $_POST['status'];
        $unit_id = $_POST['unit_id'];

        // Update the status in the database
        $update_query = "UPDATE property SET status = ? WHERE unit_id = ?";
        $stmt = $conn->prepare($update_query);

        if ($stmt === false) {
            die('Error preparing query: ' . $conn->error);
        }

        $stmt->bind_param("si", $status, $unit_id);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Status updated successfully!'];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to update status'];
        }

        $stmt->close();
        echo json_encode($response);
        exit;
    }
    
    // Handle archiving
    if (isset($_POST['archive']) && isset($_POST['unit_id'])) {
        $unit_id = $_POST['unit_id'];

        // Archive the property
        $archive_query = "UPDATE property SET position = 'archive' WHERE unit_id = ?";
        $stmt = $conn->prepare($archive_query);

        if ($stmt === false) {
            die('Error preparing query: ' . $conn->error);
        }

        $stmt->bind_param("i", $unit_id);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Unit archived successfully!'];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to archive unit'];
        }

        $stmt->close();
        echo json_encode($response);
        exit;
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
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <title>Manage Property</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }
        .hidden-content {
            visibility: hidden;
            height: 0;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
        }
    </style>
</head>
<body class="bg-gray-100"> 

<!-- Include Navbar -->
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>


<div class="sm:ml-64 p-8 mt-20 mx-auto">
    <h1 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4 sm:mb-6">Property Management</h1>

    <!-- Search Bar and Buttons -->
    <div class="flex flex-wrap items-center space-y-2 sm:space-y-0 sm:space-x-4 mb-4">
        <div class="relative w-full sm:w-1/3">
            <input type="text" id="search" placeholder="Search Unit No or Type..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
            <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                <svg data-feather="search" class="w-4 h-4"></svg>
            </button>
        </div>

        <!-- Print Button -->
        <button class="print-button w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2 justify-center">
            <svg data-feather="printer" class="w-4 h-4"></svg>
            Print
        </button>
        
        <!-- Add Property Button -->
        <a href="property_form.php" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2 justify-center">
            <svg data-feather="plus" class="w-4 h-4"></svg>
            Add Unit
        </a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto shadow-lg rounded-lg">
        <table class="min-w-full bg-white text-sm border border-gray-200" id="property-table">
            <thead>
                <tr>
                    <th class="px-4 sm:px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs sm:text-sm font-semibold text-gray-800 uppercase tracking-wider">Unit ID</th>
                    <th class="px-4 sm:px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs sm:text-sm font-semibold text-gray-800 uppercase tracking-wider">Unit No</th>
                    <th class="px-4 sm:px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs sm:text-sm font-semibold text-gray-800 uppercase tracking-wider">Unit Type</th>
                    <th class="px-4 sm:px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs sm:text-sm font-semibold text-gray-800 uppercase tracking-wider">Square Meter</th>
                    <th class="px-4 sm:px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs sm:text-sm font-semibold text-gray-800 uppercase tracking-wider">Monthly Rent</th>
                    <th class="px-4 sm:px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs sm:text-sm font-semibold text-gray-800 uppercase tracking-wider">Images</th>
                    <th class="px-4 sm:px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs sm:text-sm font-semibold text-gray-800 uppercase tracking-wider">Status</th>
                    <th class="px-4 sm:px-6 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs sm:text-sm font-semibold text-gray-800 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                <?php 
                $counter = 1;
                while ($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr class="unit-row hover:bg-gray-50" data-unit-no="<?php echo htmlspecialchars($row['unit_no']); ?>" data-unit-type="<?php echo htmlspecialchars($row['unit_type']); ?>">
                    <td class="px-4 sm:px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['unit_id']); ?></td>
                    <td class="px-4 sm:px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['unit_no']); ?></td>
                    <td class="px-4 sm:px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($row['unit_type']); ?></td>
                    <td class="px-4 sm:px-6 py-4 whitespace-no-wrap border-b border-gray-200 hidden-content"><?php echo number_format($row['square_meter'], 2); ?></td>
                    <td class="px-4 sm:px-6 py-4 whitespace-no-wrap border-b border-gray-200 hidden-content">₱<?php echo number_format($row['monthly_rent'], 2); ?></td>
                    <td class="px-4 sm:px-6 py-4 whitespace-no-wrap border-b border-gray-200 hidden-content">
                        <?php if (!empty($row['images'])): 
                            $images = explode(',', $row['images']);
                            $first_image = $images[0];
                        ?>
                            <img alt="Property Image" class="w-12 sm:w-16 h-12 sm:h-16 object-cover rounded" src="<?php echo htmlspecialchars($first_image); ?>"/>
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td class="px-4 sm:px-6 py-4 whitespace-no-wrap border-b border-gray-200 hidden-content">
                        <form method="POST" action="" class="status-form flex items-center space-x-2">
                            <select name="status" class="p-1 w-32 h-10 text-sm text-gray-800 bg-gray-50 border border-gray-300 rounded">
                                <option value="Available" <?php echo ($row['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                                <option value="Occupied" <?php echo ($row['status'] == 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
                                <option value="Maintenance" <?php echo ($row['status'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                <option value="Reserved" <?php echo ($row['status'] == 'Reserved') ? 'selected' : ''; ?>>Reserved</option>
                            </select>
                            <input type="hidden" name="unit_id" value="<?php echo $row['unit_id']; ?>" />
                            <button type="submit" class="p-1 w-24 h-10 bg-blue-600 text-white rounded text-xs">Update Status</button>
                        </form>
                    </td>
                    <td class="px-4 sm:px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                    <div class="action-buttons flex items-center gap-2">
                        <button class="px-4 py-2 bg-red-600 text-white rounded-md flex items-center" onclick="archiveUnit(<?php echo $row['unit_id']; ?>)">
                            <i data-feather="archive" class="mr-2 w-4 h-4"></i> Archive
                        </button>
                    </div>

                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>



<script src="../node_modules/feather-icons/dist/feather.min.js"></script>

<script>
    // Initialize Feather Icons
    feather.replace();
</script>

<script>
    // Handle form submission with AJAX
    const forms = document.querySelectorAll('.status-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Display Toastify notification
                if (data.status === 'success') {
                    Toastify({
                        text: data.message,
                        backgroundColor: "green",
                        duration: 3000
                    }).showToast();
                } else {
                    Toastify({
                        text: data.message,
                        backgroundColor: "red",
                        duration: 3000
                    }).showToast();
                }
            });
        });
    });

   // Search functionality
    document.getElementById("search").addEventListener("input", function() {
        const searchQuery = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll("#property-table tbody tr");

        rows.forEach(row => {
            const unitNo = row.getAttribute('data-unit-no').toLowerCase();
            const unitType = row.getAttribute('data-unit-type').toLowerCase();

            if (searchQuery === "") {
                // When search is empty, keep essential columns visible
                row.style.display = "";
                const columnsToHide = row.querySelectorAll('td:not(:nth-child(1), :nth-child(2), :nth-child(3), :nth-last-child(1))');
                columnsToHide.forEach(col => col.classList.add('hidden-content'));
            } else if (unitNo.includes(searchQuery) || unitType.includes(searchQuery)) {
                // Show row and remove hidden content for matching rows
                row.style.display = "";
                const hiddenColumns = row.querySelectorAll('.hidden-content');
                hiddenColumns.forEach(col => col.classList.remove('hidden-content'));
            } else {
                // Hide rows that don't match
                row.style.display = "none";
            }
        });
    });


    function archiveUnit(unitId) {
    // Show confirmation dialog
    if (confirm('Are you sure you want to archive this unit?')) {
        // Prepare form data
        const formData = new FormData();
        formData.append('archive', 'true');
        formData.append('unit_id', unitId);

        // Send AJAX request
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Display Toastify notification
            if (data.status === 'success') {
                Toastify({
                    text: data.message,
                    backgroundColor: "green",
                    duration: 3000
                }).showToast();

                 // Reload the page after a successful archive operation
                 setTimeout(() => {
                    window.location.reload();
                }, 1000); // 1000 ms = 1 second

                // Remove the row from the table
                const row = document.querySelector(`tr[data-unit-id="${unitId}"]`);
                if (row) {
                    row.remove();
                }
            } else {
                Toastify({
                    text: data.message,
                    backgroundColor: "red",
                    duration: 3000
                }).showToast();
            }
        });
    }
}

</script>

<script>
  function printTable() {
    // Get the table element
    var table = document.querySelector('table');

    // Create a new window for printing
    var printWindow = window.open('', '', 'height=600,width=900');
    printWindow.document.write('<html><head><title>Manage Properties</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://cdn.tailwindcss.com">');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<div class="p-8">');
    printWindow.document.write('<h1 class="text-2xl font-bold mb-4">Manage Properties</h1>');

    // Modify the table styles for better readability
    printWindow.document.write('<style>');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
    printWindow.document.write('th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }');
    printWindow.document.write('th { background-color: #f2f2f2; }');
    printWindow.document.write('</style>');

    // Iterate through table rows and modify image sources for printing
    var rows = table.getElementsByTagName('tr');
    for (var i = 0; i < rows.length; i++) {
      var cells = rows[i].getElementsByTagName('td');
      for (var j = 0; j < cells.length; j++) {
        if (cells[j].getElementsByTagName('img').length > 0) {
          var img = cells[j].getElementsByTagName('img')[0];
          img.src = img.src.replace('/api/placeholder/', '../images/');
          img.style.width = '50px';
          img.style.height = '50px';
        }
      }
    }

    printWindow.document.write(table.outerHTML);
    printWindow.document.write('</div>');
    printWindow.document.write('</body></html>');

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  }

  document.querySelector('button.print-button').addEventListener('click', printTable);
</script>



</body>
</html>
