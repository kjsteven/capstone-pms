<?php
    require_once '../session/session_manager.php';
    require '../session/db.php';
    require '../session/audit_trail.php';  // Add this line

    start_secure_session();

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../authentication/login.php'); // Adjust the path as necessary
        exit();
    }

    // Fetch user information from the database
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT u.*, COALESCE(k.verification_status, 'not_submitted') as kyc_status, k.admin_remarks 
            FROM users u 
            LEFT JOIN kyc_verification k ON u.user_id = k.user_id 
            WHERE u.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $name = $user['name'];
        $email = $user['email'];
        $phone = $user['phone'];
        $profile_image = $user['profile_image'];
        $kyc_status = $user['kyc_status'];
        $admin_remarks = $user['admin_remarks'];

        // If no profile image is set (i.e., it's NULL or empty), set the default image URL
        if (empty($profile_image)) {
            $profile_image = 'https://randomuser.me/api/portraits/men/1.jpg'; // Default image URL
        }
    } else {
        $name = $email = $phone = "";
        $profile_image = "https://randomuser.me/api/portraits/men/1.jpg"; // Default image if no user found
    }

    $imagePath = $profile_image; // Store the image path for display

    // Handle profile image upload
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $uploadDir = "uploads/";

        // Check if upload directory exists, create if it doesn't
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = uniqid() . "-" . basename($_FILES['profile_image']['name']); // Generate a unique filename
        $uploadPath = $uploadDir . $fileName;

        // File validation (size, type)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        // Validate file type and size
        if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
            echo "Invalid file type. Only JPEG, PNG, and GIF are allowed.";
        } elseif ($_FILES['profile_image']['size'] > $maxFileSize) {
            echo "File is too large. Maximum size is 2MB.";
        } else {
            // Move the uploaded file
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                // If a previous image exists and is not the default, delete it
                if ($profile_image !== 'https://randomuser.me/api/portraits/men/1.jpg' && file_exists($profile_image)) {
                    unlink($profile_image); // Delete old image
                }

                // Update the database with the new image path (relative path)
                $sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $uploadPath, $user_id);

                if ($stmt->execute()) {
                    // Add audit log for profile image update
                    $action_details = "Updated profile image: " . $fileName;
                    logActivity($_SESSION['user_id'], "Profile Image Update", $action_details);
                    
                    // Redirect to the same page to prevent form resubmission
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit(); // Make sure to exit after the redirect
                } else {
                    echo "Error updating database: " . $stmt->error . "<br>";
                }
            } else {
                echo "Failed to upload image.<br>";
            }
        }
    }


    // Handle profile image deletion
    if (isset($_GET['delete_image'])) {
        // Delete the current image if it's not the default one
        if ($profile_image !== 'https://randomuser.me/api/portraits/men/1.jpg' && file_exists($profile_image)) {
            unlink($profile_image); // Remove profile image
        }

        // Update the database to set profile_image to NULL when deleted
        $sql = "UPDATE users SET profile_image = NULL WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Set imagePath to default image
        $imagePath = 'https://randomuser.me/api/portraits/men/1.jpg';

        // Redirect to the same page to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit(); // Ensure no further code runs after the redirect
    }

    $stmt->close();


    // Check for notifications in the session
    $notification = $_SESSION['notification'] ?? null;
    unset($_SESSION['notification']); // Clear the notification after reading it


    ?>


    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

        <!-- Toastify CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
        <!-- Toastify JS -->
        <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

        <title>Profile</title>
        <link rel="icon" href="../images/logo.png" type="image/png">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
            }
            .hidden-tab {
                display: none;
            }
        </style>
    </head>
    <body class="bg-gray-100">

    <!-- Include Navbar -->
    <?php include('navbar.php'); ?>

    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content -->
    <div class="sm:ml-64 p-8 mt-20">
        <!-- Tabs Navigation -->
        <div class="flex mb-6 border-b">
            <button id="tab-info" class="py-2 px-4 text-gray-700 focus:outline-none border-b-4 border-blue-600">Personal Information</button>
            <button id="tab-photo" class="py-2 px-4 text-gray-700 focus:outline-none ml-4 border-b-4 border-transparent hover:border-blue-600">Profile Image</button>
            <button id="tab-password" class="py-2 px-4 text-gray-700 focus:outline-none ml-4 border-b-4 border-transparent hover:border-blue-600">Change Password</button>
        </div>

        <!-- Personal Information Form -->
    <div id="info-content" class="bg-white shadow-lg rounded-lg p-6 w-full max-w-2xl">
        <h2 class="text-xl font-semibold mb-6">Personal Information</h2>
        
        <!-- Add KYC Status Section -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-medium">KYC Verification Status</h4>
                    <?php
                    $statusClasses = [
                        'approved' => 'bg-green-100 text-green-800',
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'not_submitted' => 'bg-gray-100 text-gray-800'
                    ];
                    $statusClass = $statusClasses[$kyc_status] ?? $statusClasses['not_submitted'];
                    ?>
                    <div class="mt-2">
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?= $statusClass ?>">
                            <?= ucfirst(str_replace('_', ' ', $kyc_status)) ?>
                        </span>
                        <?php if ($admin_remarks && $kyc_status === 'rejected'): ?>
                            <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars($admin_remarks) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($kyc_status === 'not_submitted' || $kyc_status === 'rejected'): ?>
                    <div>
                        <a href="kyc.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                            <?= $kyc_status === 'rejected' ? 'Resubmit KYC' : 'Complete KYC' ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

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

            <!-- Profile Image Form -->
    <div id="photo-content" class="hidden-tab bg-white shadow-lg rounded-lg p-6 w-full max-w-2xl">
        <h2 class="text-xl font-semibold mb-6">Your Photo</h2>
        <div class="flex flex-col items-center">
            <img class="w-24 h-24 rounded-full mb-4" src="<?php echo htmlspecialchars($imagePath); ?>" alt="Profile Photo" />

            <!-- Edit photo link triggers file input -->
            <a href="#" id="edit-photo" class="text-blue-600 mb-2">Edit your photo</a>

            <!-- Delete photo link -->
            <a href="?delete_image=true" onclick="return confirm('Are you sure you want to delete this image?');" class="text-red-600 mb-6">Delete</a>

            <!-- File Upload -->
            <form action="" method="POST" enctype="multipart/form-data" id="upload-form">
                <label for="file-upload" class="w-full h-32 flex flex-col items-center justify-center border-2 border-dashed border-gray-300 text-gray-600 rounded-lg cursor-pointer">
                    <i class="fas fa-upload text-2xl mb-2"></i>
                    <span>Click to upload or drag and drop</span>
                    <input id="file-upload" type="file" name="profile_image" class="hidden" />
                    <span class="text-sm text-gray-500">(SVG, PNG, JPG or GIF, max 800 X 800px)</span>
                </label>
                
                <?php
                // Display the success message if file uploaded successfully
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                    echo "<p class='text-green-600 mt-4'>Image uploaded successfully";
                }
                ?>

                <div class="flex justify-between mt-6">
                    <!-- Cancel Button -->
                    <button type="button" id="cancel-button" class="text-gray-700 border border-gray-400 rounded-lg px-4 py-2">Cancel</button>
                    <!-- Save Button -->
                    <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 py-2">Save</button>
                </div>
            </form>
        </div>
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



    <script>
        
        // Function to display Toastify notifications
        function showToast(message, type) {
            const formattedMessage = message.replace(/\+/g, ' ').split('|').join('\n');

            Toastify({
                text: formattedMessage,
                duration: 5000, // 5 seconds
                close: true,
                gravity: "top", // Position: top, bottom
                position: "right", // Position: left, right, center
                backgroundColor: type === "success" ? "#4CAF50" : "#FF5252", // Green for success, red for error
            }).showToast();
        }

        // Check for notifications in the session
        <?php if ($notification): ?>
            showToast("<?php echo addslashes($notification['message']); ?>", "<?php echo $notification['type']; ?>");
        <?php endif; ?>

    </script>         

    <!-- JavaScript for password visibility toggle -->
    <script>
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
        // JavaScript for tab navigation
        const tabInfo = document.getElementById('tab-info');
        const tabPhoto = document.getElementById('tab-photo');
        const tabPassword = document.getElementById('tab-password');

        const infoContent = document.getElementById('info-content');
        const photoContent = document.getElementById('photo-content');
        const passwordContent = document.getElementById('password-content');

        tabInfo.addEventListener('click', () => {
            infoContent.style.display = 'block';
            photoContent.style.display = 'none';
            passwordContent.style.display = 'none';

            tabInfo.classList.add('border-blue-600');
            tabPhoto.classList.remove('border-blue-600');
            tabPassword.classList.remove('border-blue-600');
        });

        tabPhoto.addEventListener('click', () => {
            infoContent.style.display = 'none';
            photoContent.style.display = 'block';
            passwordContent.style.display = 'none';

            tabPhoto.classList.add('border-blue-600');
            tabInfo.classList.remove('border-blue-600');
            tabPassword.classList.remove('border-blue-600');
        });

        tabPassword.addEventListener('click', () => {
            infoContent.style.display = 'none';
            photoContent.style.display = 'none';
            passwordContent.style.display = 'block';

            tabPassword.classList.add('border-blue-600');
            tabInfo.classList.remove('border-blue-600');
            tabPhoto.classList.remove('border-blue-600');
        });
    </script>


    <script>
        // Trigger file input when "Edit your photo" is clicked
        document.getElementById('edit-photo').addEventListener('click', function(e) {
            e.preventDefault(); // Prevent the default anchor behavior
            document.getElementById('file-upload').click(); // Trigger the file input click
        });

        // Cancel button functionality
        document.getElementById('cancel-button').addEventListener('click', function() {
            // Reset the file input field
            document.getElementById('file-upload').value = ''; // Clear the selected file

            // Optionally, close the form or reset the display (if you want to hide the form)
            // document.getElementById('photo-content').classList.add('hidden-tab'); // Uncomment to hide the form

            // Reset the profile image (to show the original image again)
            document.querySelector('img').src = '<?php echo htmlspecialchars($imagePath); ?>'; // Reset to original image
        });
    </script>

    </body>
    </html>
