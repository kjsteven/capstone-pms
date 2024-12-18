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

    // Fetch available properties from the database
    $properties_query = "SELECT * FROM property";
    $properties_result = mysqli_query($conn, $properties_query);
    $properties = mysqli_fetch_all($properties_result, MYSQLI_ASSOC);

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


            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php if (!empty($properties)): ?>
                    <?php foreach ($properties as $property): ?>
                        <div class="bg-white shadow-xl rounded-xl overflow-hidden relative">
                            <!-- Status Badge -->
                            <?php 
                            $status = $property['status'] ?? 'Available'; // Default to Available if not set
                            $statusColors = [
                                'Available' => 'bg-green-50 text-green-950',
                                'Occupied' => 'bg-red-50 text-red-950',
                                'Maintenance' => 'bg-yellow-50 text-yellow-950'
                            ];
                            $badgeColor = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <div class="absolute top-4 right-4 z-10">
                                <span class="<?php echo $badgeColor; ?> px-3 py-1 rounded-full text-xs font-semibold uppercase">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </div>

                            <figure>
                                <?php
                                // Fetch the image from the database (assuming $property['images'] contains the relative path)
                                if (!empty($property['images'])) {
                                    $image_path = '../admin/' . $property['images']; // Relative path to the image
                                } else {
                                    $image_path = '../images/bg2.jpg'; // Fallback image if no image exists
                                }
                                ?>
                                <img class="w-full h-48 object-cover" src="<?php echo htmlspecialchars($image_path); ?>" alt="Property Image" />
                            </figure>
                            <div class="p-6">
                                <h2 class="text-xl font-semibold mb-4">Unit Details</h2>
                                <div class="space-y-2">
                                    <p><span class="font-semibold">Unit No:</span> <?php echo htmlspecialchars($property['unit_no']); ?></p>
                                    <p><span class="font-semibold">Unit Type:</span> <?php echo htmlspecialchars($property['unit_type']); ?></p>
                                    <p><span class="font-semibold">Monthly Rent:</span> ₱<?php echo number_format($property['monthly_rent'], 2); ?></p>
                                    <p><span class="font-semibold">Square Meter:</span> <?php echo htmlspecialchars($property['square_meter']); ?> sqm</p>
                                </div>
                                <div class="flex justify-center mt-6">
                                    <?php 
                                    $isDisabled = ($status === 'Occupied' || $status === 'Maintenance');
                                    ?>
                                    <button 
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300 
                                        <?php echo $isDisabled ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                        <?php echo $isDisabled ? 'disabled' : ''; ?>
                                    >
                                        <?php echo $isDisabled ? 'Not Available' : 'Reserve Now'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">No properties found.</p>
                    </div>
                <?php endif; ?>
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


        <script>
            // Properties data from PHP
            const properties = <?php echo json_encode($properties); ?>;

            // Function to filter properties
            function filterProperties() {
                const categoryFilter = document.getElementById('dropdown-button').innerText.trim();
                const searchTerm = document.getElementById('search-dropdown').value.toLowerCase();
                const propertiesContainer = document.querySelector('.grid');

                // Clear existing properties
                propertiesContainer.innerHTML = '';

                // Filter properties
                const filteredProperties = properties.filter(property => {
                    const matchesCategory = categoryFilter === 'All Categories' || 
                        property.unit_type.toLowerCase() === categoryFilter.toLowerCase();
                    
                    const matchesSearch = searchTerm === '' || 
                        property.unit_no.toLowerCase().includes(searchTerm) ||
                        property.unit_type.toLowerCase().includes(searchTerm);

                    return matchesCategory && matchesSearch;
                });

                // Render filtered properties
                if (filteredProperties.length > 0) {
                    filteredProperties.forEach(property => {
                        // Status determination
                        const status = property.status || 'Available';
                        const statusColors = {
                            'Available': 'bg-green-100 text-green-800',
                            'Occupied': 'bg-red-100 text-red-800',
                            'Maintenance': 'bg-yellow-100 text-yellow-800'
                        };
                        const badgeColor = statusColors[status] || 'bg-gray-100 text-gray-800';
                        
                        // Determine button state
                        const isDisabled = status === 'Occupied' || status === 'Maintenance';
                        
                        // Image path
                        const imagePath = property.images 
                            ? `../admin/${property.images}` 
                            : '../images/bg2.jpg';

                        // Create property card HTML
                        const propertyCard = `
                            <div class="bg-white shadow-xl rounded-xl overflow-hidden relative">
                                <div class="absolute top-4 right-4 z-10">
                                    <span class="${badgeColor} px-3 py-1 rounded-full text-xs font-semibold uppercase">
                                        ${status}
                                    </span>
                                </div>

                                <figure>
                                    <img class="w-full h-48 object-cover" src="${imagePath}" alt="Property Image" />
                                </figure>
                                <div class="p-6">
                                    <h2 class="text-xl font-semibold mb-4">Unit Details</h2>
                                    <div class="space-y-2">
                                        <p><span class="font-semibold">Unit No:</span> ${property.unit_no}</p>
                                        <p><span class="font-semibold">Unit Type:</span> ${property.unit_type}</p>
                                        <p><span class="font-semibold">Monthly Rent:</span> ₱${Number(property.monthly_rent).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                                        <p><span class="font-semibold">Square Meter:</span> ${property.square_meter} sqm</p>
                                    </div>
                                    <div class="flex justify-center mt-6">
                                        <button 
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300 
                                            ${isDisabled ? 'opacity-50 cursor-not-allowed' : ''}"
                                            ${isDisabled ? 'disabled' : ''}
                                        >
                                            ${isDisabled ? 'Not Available' : 'Reserve Now'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        propertiesContainer.innerHTML += propertyCard;
                    });
                } else {
                    // No properties found
                    propertiesContainer.innerHTML = `
                        <div class="col-span-full text-center py-8">
                            <p class="text-gray-500">No properties found.</p>
                        </div>
                    `;
                }
            }

            // Modify existing functions to use filterProperties
            function selectCategory(category) {
                document.getElementById('dropdown-button').innerText = category;
                toggleDropdown(); // Close the dropdown after selection
                filterProperties(); // Filter properties based on selected category
            }

            // Add event listener for search input
            document.getElementById('search-dropdown').addEventListener('input', filterProperties);

            // Modify the form submission to prevent default and trigger filtering
            document.querySelector('form').addEventListener('submit', function(e) {
                e.preventDefault();
                filterProperties();
            });

            // Original loading script
            window.onload = function() {
                showLoading();
                setTimeout(hideLoading, 1000);
                
                // Initial filtering to set up the page
                filterProperties();
            };
        </script>


    </body>

    </html>
