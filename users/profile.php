<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

    <!-- Forms Section -->
    <div class="grid grid-cols-1">
        <!-- Personal Information Form -->
        <div id="info-content" class="bg-white shadow-lg rounded-lg p-6 w-full max-w-2xl">
            <h2 class="text-xl font-semibold mb-6">Personal Information</h2>
            <form>
                <!-- Full Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="full-name">
                        Full Name
                    </label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-user text-gray-500"></i>
                        <input type="text" id="full-name" class="ml-2 w-full outline-none" placeholder="Devid Jhon" />
                    </div>
                </div>
                <!-- Phone Number -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone-number">
                        Phone Number
                    </label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-phone text-gray-500"></i>
                        <input type="text" id="phone-number" class="ml-2 w-full outline-none" placeholder="+990 3343 7865" />
                    </div>
                </div>
                <!-- Email Address -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email Address
                    </label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-envelope text-gray-500"></i>
                        <input type="email" id="email" class="ml-2 w-full outline-none" placeholder="devidjond45@gmail.com" />
                    </div>
                </div>
                <div class="flex justify-between">
                    <button type="button" class="text-gray-700 border border-gray-400 rounded-lg px-4 py-2">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 py-2">Save</button>
                </div>
            </form>
        </div>

        <!-- Profile Image Form -->
        <div id="photo-content" class="hidden-tab bg-white shadow-lg rounded-lg p-6 w-full max-w-2xl">
            <h2 class="text-xl font-semibold mb-6">Your Photo</h2>
            <div class="flex flex-col items-center">
                <img class="w-24 h-24 rounded-full mb-4" src="https://randomuser.me/api/portraits/men/1.jpg" alt="Profile Photo" />
                <a href="#" class="text-blue-600 mb-2">Edit your photo</a>
                <a href="#" class="text-red-600 mb-6">Delete</a>

                <!-- File Upload -->
                <label for="file-upload" class="w-full h-32 flex flex-col items-center justify-center border-2 border-dashed border-gray-300 text-gray-600 rounded-lg cursor-pointer">
                    <i class="fas fa-upload text-2xl mb-2"></i>
                    <span>Click to upload or drag and drop</span>
                    <input id="file-upload" type="file" class="hidden" />
                    <span class="text-sm text-gray-500">(SVG, PNG, JPG or GIF, max 800 X 800px)</span>
                </label>
            </div>
            <div class="flex justify-between mt-6">
                <button type="button" class="text-gray-700 border border-gray-400 rounded-lg px-4 py-2">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 py-2">Save</button>
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

</body>
</html>
