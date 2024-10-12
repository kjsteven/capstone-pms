<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
<body class="bg-gray-100">

<!-- Include Navbar -->
<?php include('navbar.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebar.php'); ?>

<!-- Main Content -->
<div class="sm:ml-64 p-8 mt-20">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Left Section: Personal Information -->
        <div class="bg-white shadow-lg rounded-lg p-6">
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

        <!-- Right Section: Your Photo -->
        <div class="bg-white shadow-lg rounded-lg p-6">
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
    </div>
</div>

<!-- Include your custom JavaScript here if needed -->
</body>
</html>
