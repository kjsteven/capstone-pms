<?php

session_start();
ob_start();
require_once '../session/session_manager.php';
require '../session/db.php';
require_once '../session/audit_trail.php';
require_once '../notification/notif_handler.php'; // Add this line

// Check if user is logged in and has valid session data
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

// Create uploads directory if it doesn't exist
$upload_dir = "uploads/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $unit_no = mysqli_real_escape_string($conn, $_POST['unit_no']);
    $unit_type = mysqli_real_escape_string($conn, $_POST['unit_type']);
    $square_meter = floatval($_POST['square_meter']);
    $monthly_rent = floatval($_POST['monthly_rent']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Handle image uploads
    $image_urls = [];
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) { // Fix array offset error
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === 0) { // Check each file for errors
                $image_name = basename($_FILES['images']['name'][$key]);
                $target_file = $upload_dir . uniqid() . '_' . $image_name;
                
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $image_urls[] = $target_file;
                }
            }
        }
    }
    $images = !empty($image_urls) ? implode(',', $image_urls) : null;

    // Use prepared statement for database insertion
    $stmt = $conn->prepare("INSERT INTO property (unit_no, unit_type, square_meter, monthly_rent, images, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddss", $unit_no, $unit_type, $square_meter, $monthly_rent, $images, $status);

    if ($stmt->execute()) {
        // Log the activity
        logActivity($_SESSION['user_id'], "Added New Unit", "Added new unit: $unit_no ($unit_type)");

        // Create notification for admin
        $adminMessage = "New unit added: Unit {$unit_no} ({$unit_type}) has been successfully added to the system.";
        createNotification($_SESSION['user_id'], $adminMessage, 'property');

        // Create notification for available tenants with approved KYC
        $tenant_query = "SELECT user_id FROM kyc_verification WHERE verification_status = 'approved'";
        $tenant_result = $conn->query($tenant_query);
        

        $insert_status = 'success';
    } else {
        $insert_status = 'error';
        error_log("Failed to insert new property: " . $stmt->error);
    }
    $stmt->close();
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
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <title>Add Property Form</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
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

<div class="container mx-auto p-6">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg w-full mx-auto mt-20 border relative">
            <!-- Close Icon -->
            <div class="absolute top-4 right-4">
                <i data-feather="x" class="close-icon w-6 h-6 text-gray-500 hover:text-gray-700"></i>
            </div>

            <h3 class="text-xl font-semibold mb-4">Add Unit</h3>
            <form action="property_form.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <!-- Unit Number -->
                <div>
                    <label for="unit_no" class="block text-sm font-medium text-gray-700">Unit Number</label>
                    <input type="text" id="unit_no" name="unit_no" 
                           class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" 
                           required>
                </div>

                <!-- Unit Type -->
                <div>
                    <label for="unit_type" class="block text-sm font-medium text-gray-700">Unit Type</label>
                    <select id="unit_type" name="unit_type" 
                            class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" 
                            required>
                        <option value="" disabled selected>Select a unit type</option>
                        <option value="Warehouse">Warehouse</option>
                        <option value="Office">Office</option>
                        <option value="Commercial">Commercial</option>
                    </select>
                </div>

                <!-- Square Meter -->
                <div>
                    <label for="square_meter" class="block text-sm font-medium text-gray-700">Square Meter</label>
                    <input type="number" step="0.01" id="square_meter" name="square_meter" 
                           class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" 
                           oninput="computeRent()" required>
                </div>

                <!-- Rate Per Square Meter -->
                <div>
                    <label for="rate_per_sqm" class="block text-sm font-medium text-gray-700">Rate Per Square Meter</label>
                    <input type="number" step="0.01" id="rate_per_sqm" name="rate_per_sqm" 
                           class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" 
                           oninput="computeRent()" value="450" required>
                </div>

                <!-- Monthly Rent -->
                <div>
                    <label for="monthly_rent" class="block text-sm font-medium text-gray-700">Monthly Rent</label>
                    <input type="number" step="0.01" id="monthly_rent" name="monthly_rent" 
                           class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" 
                           readonly required>
                </div>

                <!-- Upload Images -->
                <div>
                    <label for="images" class="block text-sm font-medium text-gray-700">Upload Images</label>
                    <input type="file" id="images" name="images[]" accept="image/*" multiple 
                           class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none">
                </div>

                <!-- Property Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" 
                            class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" 
                            required>
                        <option value="Available">Available</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Occupied">Reserved</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 flex justify-between">
                    <button type="reset" class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500">
                        Reset
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Add Property
                    </button>
                </div>
            </form>
        </div>
    </div>


<script src="../node_modules/feather-icons/dist/feather.min.js"></script>

<script>
    // Initialize Feather Icons
    feather.replace();

    // JavaScript to compute monthly rent based on square meter and rate
    function computeRent() {
        const squareMeterInput = document.getElementById('square_meter');
        const ratePerSqmInput = document.getElementById('rate_per_sqm');
        const monthlyRentInput = document.getElementById('monthly_rent');

        // Validate and compute monthly rent
        const squareMeter = parseFloat(squareMeterInput.value);
        const ratePerSquareMeter = parseFloat(ratePerSqmInput.value);
        
        if (!isNaN(squareMeter) && !isNaN(ratePerSquareMeter) && squareMeter > 0 && ratePerSquareMeter > 0) {
            const computedRent = squareMeter * ratePerSquareMeter;
            monthlyRentInput.value = computedRent.toFixed(2);
        } else {
            monthlyRentInput.value = '';
        }
    }
</script>

<script>
 
        // Add click event for close icon
        document.querySelector('.close-icon').addEventListener('click', function() {
            window.location.href = 'propertyAdmin.php';
        });
    
</script>


<script>

    <?php if(isset($insert_status)): ?>
        <?php if($insert_status == 'success'): ?>
            Toastify({
                text: "Property added successfully!",
                duration: 2000,
                backgroundColor: "green",
                callback: function(){
                    setTimeout(function() {
                        window.location.href = 'propertyAdmin.php';
                    }, 3000);
                }
            }).showToast();
        <?php else: ?>
            Toastify({
                text: "Error adding property!",
                duration: 2000,
                backgroundColor: "red"
            }).showToast();
        <?php endif; ?>
    <?php endif; ?>

</script>



</body>
</html>
