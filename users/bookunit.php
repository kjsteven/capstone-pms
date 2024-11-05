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


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <title>Reserve a Unit</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
</head>


<style>
    /* Hide the content by default */
    #content {
        display: none;
    }

    /* Show the loading spinner by default */
    #loading {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100vh;
    }
</style>

<body class="bg-gray-100 font-[Poppins]">

    <!-- Include Navbar -->
    <?php include('navbar.php'); ?>

    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Loading Spinner -->
    <div id="loading" class="flex items-center justify-center w-full h-screen">
        <div role="status">
            <svg aria-hidden="true" class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill" />
            </svg>
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- Content Wrapper to avoid overlap -->
    <div class="sm:ml-64 p-8 mt-20">
        <!-- Title and Search Form -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Reserve a Unit</h2>
            <form class="max-w-lg w-full">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full sm:w-1/3 mb-4 sm:mb-0">
                        <label for="search-dropdown" class="mb-2 text-sm font-medium text-gray-900 sr-only">Categories</label>
                        <button id="dropdown-button" class="flex-shrink-0 z-10 inline-flex rounded-l-lg items-center h-full py-2 px-4 text-sm font-medium text-center text-gray-900 bg-gray-100 border border-gray-300 rounded-l-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100" type="button" onclick="toggleDropdown()">
                            All Categories
                            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>
                        <div id="dropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 absolute">
                            <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdown-button">
                                <li>
                                    <button type="button" class="inline-flex w-full px-4 py-2 hover:bg-gray-100" onclick="selectCategory('All Categories')">All Categories</button>
                                </li>
                                <li>
                                    <button type="button" class="inline-flex w-full px-4 py-2 hover:bg-gray-100" onclick="selectCategory('Commercial')">Commercial</button>
                                </li>
                                <li>
                                    <button type="button" class="inline-flex w-full px-4 py-2 hover:bg-gray-100" onclick="selectCategory('Warehouse')">Warehouse</button>
                                </li>
                                <li>
                                    <button type="button" class="inline-flex w-full px-4 py-2 hover:bg-gray-100" onclick="selectCategory('Office')">Office</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="relative w-full sm:w-2/3">
                        <input type="search" id="search-dropdown" class="block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Search..." required />
                        <button type="submit" class="absolute top-0 right-0 h-full p-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                            <span class="sr-only">Search</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Cards Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Card 1 -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <figure>
                    <img class="w-full h-48 object-cover" src="../images/bg2.jpg" alt="Warehouse Image" />
                </figure>
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Unit Details</h2>
                    <div class="space-y-2">
                        <p><span class="font-semibold">Unit No:</span> 302</p>
                        <p><span class="font-semibold">Unit Type:</span> Warehouse</p>
                        <p><span class="font-semibold">Monthly Rent:</span> ₱25,000</p>
                        <p><span class="font-semibold">Square Meter:</span> 75 sqm</p>
                    </div>
                    <div class="flex justify-center mt-6">
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300">Reserve Now</button>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <figure>
                    <img class="w-full h-48 object-cover" src="../images/bg2.jpg" alt="Commercial Image" />
                </figure>
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Unit Details</h2>
                    <div class="space-y-2">
                        <p><span class="font-semibold">Unit No:</span> 101</p>
                        <p><span class="font-semibold">Unit Type:</span> Commercial</p>
                        <p><span class="font-semibold">Monthly Rent:</span> ₱30,000</p>
                        <p><span class="font-semibold">Square Meter:</span> 100 sqm</p>
                    </div>
                    <div class="flex justify-center mt-6">
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300">Reserve Now</button>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <figure>
                    <img class="w-full h-48 object-cover" src="../images/bg2.jpg" alt="Commercial Image" />
                </figure>
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Unit Details</h2>
                    <div class="space-y-2">
                        <p><span class="font-semibold">Unit No:</span> 201</p>
                        <p><span class="font-semibold">Unit Type:</span> Commercial</p>
                        <p><span class="font-semibold">Monthly Rent:</span> ₱40,000</p>
                        <p><span class="font-semibold">Square Meter:</span> 150 sqm</p>
                    </div>
                    <div class="flex justify-center mt-6">
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300">Reserve Now</button>
                    </div>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <figure>
                    <img class="w-full h-48 object-cover" src="../images/bg2.jpg" alt="Office Image" />
                </figure>
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Unit Details</h2>
                    <div class="space-y-2">
                        <p><span class="font-semibold">Unit No:</span> 505</p>
                        <p><span class="font-semibold">Unit Type:</span> Office</p>
                        <p><span class="font-semibold">Monthly Rent:</span> ₱20,000</p>
                        <p><span class="font-semibold">Square Meter:</span> 50 sqm</p>
                    </div>
                    <div class="flex justify-center mt-6">
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300">Reserve Now</button>
                    </div>
                </div>
            </div>

            <!-- Card 5 -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <figure>
                    <img class="w-full h-48 object-cover" src="../images/bg2.jpg" alt="Commercial Image" />
                </figure>
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Unit Details</h2>
                    <div class="space-y-2">
                        <p><span class="font-semibold">Unit No:</span> 401</p>
                        <p><span class="font-semibold">Unit Type:</span> Commercial</p>
                        <p><span class="font-semibold">Monthly Rent:</span> ₱50,000</p>
                        <p><span class="font-semibold">Square Meter:</span> 200 sqm</p>
                    </div>
                    <div class="flex justify-center mt-6">
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300">Reserve Now</button>
                    </div>
                </div>
            </div>

            <!-- Card 6 -->
            <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <figure>
                    <img class="w-full h-48 object-cover" src="../images/bg2.jpg" alt="Commercial Image" />
                </figure>
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Unit Details</h2>
                    <div class="space-y-2">
                        <p><span class="font-semibold">Unit No:</span> 501</p>
                        <p><span class="font-semibold">Unit Type:</span> Warehouse</p>
                        <p><span class="font-semibold">Monthly Rent:</span> ₱40,000</p>
                        <p><span class="font-semibold">Square Meter:</span> 150 sqm</p>
                    </div>
                    <div class="flex justify-center mt-6">
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300">Reserve Now</button>
                    </div>
                </div>
            </div>

              <!-- Card 7 -->
              <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <figure>
                    <img class="w-full h-48 object-cover" src="../images/bg2.jpg" alt="Commercial Image" />
                </figure>
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Unit Details</h2>
                    <div class="space-y-2">
                        <p><span class="font-semibold">Unit No:</span> 601</p>
                        <p><span class="font-semibold">Unit Type:</span> Office</p>
                        <p><span class="font-semibold">Monthly Rent:</span> ₱50,000</p>
                        <p><span class="font-semibold">Square Meter:</span> 150 sqm</p>
                    </div>
                    <div class="flex justify-center mt-6">
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300">Reserve Now</button>
                    </div>
                </div>
            </div>


              <!-- Card 8-->
              <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                <figure>
                    <img class="w-full h-48 object-cover" src="../images/bg2.jpg" alt="Commercial Image" />
                </figure>
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Unit Details</h2>
                    <div class="space-y-2">
                        <p><span class="font-semibold">Unit No:</span> 801</p>
                        <p><span class="font-semibold">Unit Type:</span> Warehouse</p>
                        <p><span class="font-semibold">Monthly Rent:</span> ₱60,000</p>
                        <p><span class="font-semibold">Square Meter:</span> 250 sqm</p>
                    </div>
                    <div class="flex justify-center mt-6">
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300">Reserve Now</button>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <!-- JavaScript for Dropdown -->
    <script>
         // Show the loading spinner when the page starts loading
         function showLoading() {
            document.getElementById('loading').style.display = 'flex';
        }

        // Hide the loading spinner after a set time
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        // Call showLoading on page load
        window.onload = function() {
            showLoading();
            // Simulate loading delay (e.g., 2 seconds)
            setTimeout(hideLoading, 1000); // Adjust the time as needed
        };

        // Toggle dropdown visibility
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdown');
            dropdown.classList.toggle('hidden');
        }

        // Select a category from the dropdown
        function selectCategory(category) {
            document.getElementById('dropdown-button').innerText = category;
            toggleDropdown(); // Close the dropdown after selection
        }

    </script>
</body>

</html>
