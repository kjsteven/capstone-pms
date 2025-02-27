<?php

require '../session/db.php';
require_once '../session/session_manager.php';
require_once '../session/audit_trail.php'; // Include audit trail functionality

session_start();


// Check if the staff member is logged in
if (!isset($_SESSION['staff_id'])) {
    header('Location: ../authentication/stafflogin.php');
    exit();
}

// Get staff profile
function getStaffProfile($staff_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT staff_id, Email, Name, Specialty, Phone_Number, status FROM staff WHERE staff_id = ?");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Update staff profile
function updateStaffProfile($staff_id, $phone, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE staff SET Phone_Number = ?, status = ? WHERE staff_id = ?");
    $stmt->bind_param("ssi", $phone, $status, $staff_id);
    return $stmt->execute();
}

// Change password
function changePassword($staff_id, $current_password, $new_password) {
    global $conn;
    
    // Verify current password
    $stmt = $conn->prepare("SELECT Password FROM staff WHERE staff_id = ?");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!password_verify($current_password, $result['Password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE staff SET Password = ? WHERE staff_id = ?");
    $stmt->bind_param("si", $hashed_password, $staff_id);
    
    if ($stmt->execute()) {
        // Log the password change in the audit trail
        logActivity($staff_id, 'Password Change', 'Staff member changed their password');
        return ['success' => true, 'message' => 'Password updated successfully'];
    }
    return ['success' => false, 'message' => 'Failed to update password'];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_SESSION['staff_id'] ?? null;
    if (!$staff_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Profile update
    if (isset($_POST['update_profile'])) {
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        
        if (updateStaffProfile($staff_id, $phone, $status)) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
    }
    
    // Password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
            exit;
        }
        
        if (strlen($new_password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
            exit;
        }
        
        $result = changePassword($staff_id, $current_password, $new_password);
        echo json_encode($result);
    }
    exit;
}

// Display profile
if (isset($_SESSION['staff_id'])) {
    $profile = getStaffProfile($_SESSION['staff_id']);
    // Used by the frontend to populate form fields
    $name = $profile['Name'] ?? '';
    $email = $profile['Email'] ?? '';
    $phone = $profile['Phone_Number'] ?? '';
    $specialty = $profile['Specialty'] ?? '';
    $status = $profile['status'] ?? 'Available';
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.css">
    <title>Staff Profile</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <style>
        /* Optional: Custom styles for smooth transitions */
        .transition-transform {
            transition: transform 0.3s ease;
        }
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body> 

<!-- Include Navbar -->
<?php include('staffNavbar.php'); ?>

<!-- Include Sidebar -->
<?php include('staffSidebar.php'); ?>

  <!-- Main Content -->
  <div class="sm:ml-64 p-8 mt-20">
        <!-- Tabs Navigation -->
        <div class="flex mb-6 border-b">
            <button id="tab-info" class="py-2 px-4 text-gray-700 focus:outline-none border-b-4 border-blue-600">Personal Information</button>
            <button id="tab-password" class="py-2 px-4 text-gray-700 focus:outline-none ml-4 border-b-4 border-transparent hover:border-blue-600">Change Password</button>
        </div>

        <!-- Personal Information Form -->
    <div id="info-content" class="bg-white shadow-lg rounded-lg p-6 w-full max-w-2xl">
        <h2 class="text-xl font-semibold mb-6">Personal Information</h2>
        <form>
            <!-- Full Name (Read-only) -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="full-name">
                    Full Name
                </label>
                <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <i class="fas fa-user text-gray-500"></i>
                    <input type="text" id="full-name" class="ml-2 w-full outline-none" value="<?php echo htmlspecialchars($name); ?>" readonly />
                </div>
            </div>
            <!-- Phone Number (Editable) -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="phone-number">
                    Phone Number
                </label>
                <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <i class="fas fa-phone text-gray-500"></i>
                    <input type="text" id="phone-number" name="phone" class="ml-2 w-full outline-none" value="<?php echo htmlspecialchars($phone); ?>" readonly />
                </div>
            </div>
            <!-- Email Address (Read-only) -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email Address
                </label>
                <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <i class="fas fa-envelope text-gray-500"></i>
                    <input type="email" id="email" class="ml-2 w-full outline-none" value="<?php echo htmlspecialchars($email); ?>" readonly />
                </div>
            </div>
    
            </form>
        </div>



       <!-- Change Password Form -->
       <div id="password-content" class="hidden-tab bg-white shadow-lg rounded-lg p-6 w-full max-w-2xl">
            <h2 class="text-xl font-semibold mb-6">Change Password</h2>
            <form action="change_password.php" method="POST">
                <!-- Current Password -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="current-password">
                        Current Password
                    </label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-lock text-gray-500"></i>
                        <input type="password" id="current-password" name="current_password" class="ml-2 w-full outline-none" placeholder="Enter current password" required />
                        <i class="fas fa-eye text-gray-500 cursor-pointer ml-2" onclick="togglePasswordVisibility('current-password')"></i>
                    </div>
                </div>
                <!-- New Password -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="new-password">
                        New Password
                    </label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-lock text-gray-500"></i>
                        <input type="password" id="new-password" name="new_password" class="ml-2 w-full outline-none" placeholder="Enter new password" required />
                        <i class="fas fa-eye text-gray-500 cursor-pointer ml-2" onclick="togglePasswordVisibility('new-password')"></i>
                    </div>
                </div>
                <!-- Confirm New Password -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm-password">
                        Confirm New Password
                    </label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-lock text-gray-500"></i>
                        <input type="password" id="confirm-password" name="confirm_password" class="ml-2 w-full outline-none" placeholder="Confirm new password" required />
                        <i class="fas fa-eye text-gray-500 cursor-pointer ml-2" onclick="togglePasswordVisibility('confirm-password')"></i>
                    </div>
                </div>
                <div class="flex justify-between">
                    <button type="button" class="text-gray-700 border border-gray-400 rounded-lg px-4 py-2">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 py-2">Change Password</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="../node_modules/feather-icons/dist/feather.min.js"></script>


<!-- JavaScript for password visibility toggle -->
<script>

feather.replace();

        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = passwordInput.nextElementSibling;

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
</script>

<script>
    const tabInfo = document.getElementById('tab-info');
    const tabPassword = document.getElementById('tab-password');
    const infoContent = document.getElementById('info-content');
    const passwordContent = document.getElementById('password-content');

    // Initially hide the password content and show the info content
    infoContent.style.display = 'block';
    passwordContent.style.display = 'none';

    tabInfo.addEventListener('click', () => {
        infoContent.style.display = 'block';
        passwordContent.style.display = 'none';
        
        tabInfo.classList.add('border-blue-600');
        tabPassword.classList.remove('border-blue-600');
    });

    tabPassword.addEventListener('click', () => {
        infoContent.style.display = 'none';
        passwordContent.style.display = 'block';
        
        tabPassword.classList.add('border-blue-600');
        tabInfo.classList.remove('border-blue-600');
    });
</script>


<script>
    
document.querySelector('#password-content form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('change_password', '1');

    try {
        const response = await fetch('staffProfile.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        Toastify({
            text: result.message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: result.success ? "#4CAF50" : "#f44336"
        }).showToast();

        if (result.success) {
            e.target.reset();
        }
    } catch (error) {
        Toastify({
            text: "An error occurred",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#f44336"
        }).showToast();
    }
});

</script>

</body>
</html>
