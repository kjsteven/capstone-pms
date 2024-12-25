    <?php

    require_once '../session/session_manager.php';
    require '../session/db.php';


    start_secure_session();

    $userId = $_SESSION['user_id'];

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If not logged in, redirect to login page
        header('Location: ../authentication/login.php'); // Adjust the path as necessary
        exit();
    }

    // Query to fetch user data
    $query = "SELECT name, email, phone FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch user data
    $userData = $result->fetch_assoc();


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
        <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
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
            
            <form class="w-full">
                <div class="flex flex-wrap items-center justify-between">
                    <!-- Dropdown and Search -->
                    <div class="relative flex-grow flex items-center space-x-4">
                        <div class="relative">
                            <label for="search-dropdown" class="mb-2 text-sm font-medium text-gray-900 sr-only">Categories</label>
                            <button id="dropdown-button" class="z-10 inline-flex rounded-l-lg items-center h-10 px-4 text-sm font-medium text-center text-gray-900 bg-gray-100 border border-gray-300 hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100" type="button" onclick="toggleDropdown()">
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
                        <div class="relative w-full sm:w-1/4">
                            <input type="search" id="search-dropdown" class="block h-10 p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Search..." required />
                            <button type="submit" class="absolute top-0 right-0 h-10 px-4 text-sm font-medium text-white bg-blue-700 rounded-r-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                                <span class="sr-only">Search</span>
                            </button>
                        </div>
                    </div>
                    <!-- Reservation History Button -->
                    <div class="ml-6 mt-4">
                        <button class="h-10 px-4 py-4 flex items-center space-x-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300" onclick="location.href='reservation_history.php'">
                            <i data-feather="eye" class="w-4 h-4"></i>
                            <span>View Reservation History</span>
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
                                'Available' => 'bg-green-100 text-green-950',
                                'Occupied' => 'bg-red-100 text-red-950',
                                'Maintenance' => 'bg-yellow-100 text-yellow-950',
                                'Reserved' => 'bg-blue-100 text-blue-700'

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
                                    $isDisabled = ($status === 'Occupied' || $status === 'Maintenance' || $status === 'Reserved');
                                    ?>
                                    <button 
                                        class="reserve-now-btn bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300 
                                        <?php echo $isDisabled ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                        <?php echo $isDisabled ? 'disabled' : ''; ?>
                                        data-unit-no="<?php echo htmlspecialchars($property['unit_no']); ?>"
                                        data-unit-type="<?php echo htmlspecialchars($property['unit_type']); ?>"
                                        data-monthly-rent="<?php echo htmlspecialchars($property['monthly_rent']); ?>"
                                        data-square-meter="<?php echo htmlspecialchars($property['square_meter']); ?>">
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

       <!-- Reservation Modal -->
        <div id="reservationModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex justify-center items-center">
            <div class="bg-white rounded-lg shadow-lg w-11/12 max-h-[90vh] overflow-y-auto md:w-3/4 lg:w-1/2">
                <div class="flex justify-between items-center bg-blue-600 text-white px-4 py-2 rounded-t-lg">
                    <h2 class="text-lg font-semibold">Reservation Form</h2>
                    <button id="closeModal" class="text-xl">&times;</button>
                </div>
                <form id="reservationForm" class="p-6 space-y-4">
                    <!-- Personal Information -->
                    <fieldset>
                        <legend class="text-lg font-semibold mb-2">Personal Information</legend>
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 font-medium">Name</label>
                            <input type="text" id="name" name="name" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" value="" required disabled>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-medium">Email Address</label>
                            <input type="email" id="email" name="email" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" value="" required disabled>
                        </div>
                        <div class="mb-4">
                            <label for="contact" class="block text-gray-700 font-medium">Contact Number</label>
                            <input type="tel" id="contact" name="contact" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" value="" required disabled>
                        </div>
                    </fieldset>

                    <!-- Property Details -->
                    <fieldset>
                        <legend class="text-lg font-semibold mb-2">Property Details</legend>
                        <div class="mb-4">
                            <label for="unitType" class="block text-gray-700 font-medium">Unit Type</label>
                            <input type="text" id="unitType" name="unit_type" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" value="" required disabled>
                        </div>
                        <div class="mb-4">
                            <label for="unitNo" class="block text-gray-700 font-medium">Unit No</label>
                            <input type="text" id="unitNo" name="unit_no" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" value="" required disabled>
                        </div>
                        <div class="mb-4">
                            <label for="monthlyRent" class="block text-gray-700 font-medium">Monthly Rent</label>
                            <input type="number" id="monthlyRent" name="monthly_rent" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" value="" required disabled>
                        </div>
                        <div class="mb-4">
                            <label for="squareMeter" class="block text-gray-700 font-medium">Square Meter</label>
                            <input type="number" id="squareMeter" name="square_meter" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" value="" required disabled>
                        </div>
                    </fieldset>

                    <!-- Hidden Fields -->
                    <!-- These fields will pass the user_id (from session) and unit_id -->
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>"> <!-- User ID from session -->
                    <input type="hidden" name="unit_id" id="unitId"> <!-- Unit ID will be passed dynamically -->

                    <!-- Viewing Details -->
                    <fieldset>
                        <legend class="text-lg font-semibold mb-2">Viewing Details</legend>
                        <div class="mb-4">
                            <label for="viewingDate" class="block text-gray-700 font-medium">Preferred Viewing Date</label>
                            <input type="date" id="viewingDate" name="viewing_date" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" required>
                        </div>
                        <div class="mb-4">
                            <label for="viewingTime" class="block text-gray-700 font-medium">Preferred Viewing Time</label>
                            <input type="time" id="viewingTime" name="viewing_time" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" required>
                        </div>
                    </fieldset>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4">
                        <button type="button" id="cancelModal" class="px-4 py-2 text-gray-700 border border-gray-400 rounded-lg">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Submit</button>
                    </div>
                </form>
            </div>
        </div>



        </div>
     </div>

     <script>
    // Modal Elements
    const modal = document.getElementById('reservationModal');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelModalBtn = document.getElementById('cancelModal');

    // Loading Functions
    function showLoading() {
        document.getElementById('loading').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loading').style.display = 'none';
    }

    // Dropdown Functions
    function toggleDropdown() {
        const dropdown = document.getElementById('dropdown');
        dropdown.classList.toggle('hidden');
    }

    function selectCategory(category) {
        document.getElementById('dropdown-button').innerText = category;
        toggleDropdown(); // Close the dropdown after selection
        filterProperties(); // Filter properties based on selected category
    }

    // Properties data from PHP
    const properties = <?php echo json_encode($properties); ?>;
    const userData = <?php echo json_encode($userData); ?>;

    // Function to pre-fill personal information
    function preFillPersonalInfo() {
        if (userData) {
            document.getElementById('name').value = userData.name;
            document.getElementById('email').value = userData.email;
            document.getElementById('contact').value = userData.phone;
        }
    }

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
                    'Reserved': 'bg-blue-100 text-blue-700',
                    'Maintenance': 'bg-yellow-100 text-yellow-800'
                };
                const badgeColor = statusColors[status] || 'bg-gray-100 text-gray-800';

                // Determine button state
                const isDisabled = status === 'Occupied' || status === 'Maintenance' || status === 'Reserved';;

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
                                    class="reserve-now-btn bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-300 
                                    ${isDisabled ? 'opacity-50 cursor-not-allowed' : ''}"
                                    ${isDisabled ? 'disabled' : ''}
                                    data-unit-id="${property.unit_id}"
                                    data-unit-no="${property.unit_no}"
                                    data-unit-type="${property.unit_type}"
                                    data-monthly-rent="${property.monthly_rent}"
                                    data-square-meter="${property.square_meter}"
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

    // Event Listeners

    // Modal Events - Using event delegation for dynamically created buttons
    document.querySelector('.grid').addEventListener('click', (e) => {
        if (e.target.classList.contains('reserve-now-btn') && !e.target.disabled) {
            // Get property details from data attributes
            const unitNo = e.target.dataset.unitNo;
            const unitType = e.target.dataset.unitType;
            const monthlyRent = e.target.dataset.monthlyRent;
            const squareMeter = e.target.dataset.squareMeter;
            const unitId = e.target.dataset.unitId;

            // Fill the modal form with property details
            document.getElementById('unitNo').value = unitNo;
            document.getElementById('unitType').value = unitType;
            document.getElementById('monthlyRent').value = monthlyRent;
            document.getElementById('squareMeter').value = squareMeter;
            document.getElementById('unitId').value = unitId;

            // Show the modal
            modal.classList.remove('hidden');
        }
    });

    // Clear "Viewing Details" fields when closing or canceling the modal
    function clearViewingDetails() {
        document.getElementById('viewingDate').value = '';
        document.getElementById('viewingTime').value = '';
    }

    // Close modal events
    closeModalBtn?.addEventListener('click', () => {
        modal.classList.add('hidden');
        clearViewingDetails(); 
    });

    cancelModalBtn?.addEventListener('click', () => {
        modal.classList.add('hidden');
        clearViewingDetails(); 
    });

    // Search input event
    document.getElementById('search-dropdown').addEventListener('input', filterProperties);

    // Form submission using Fetch API
    document.querySelector('#reservationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Show loading indicator
        showLoading();

        const formData = new FormData(this);

        // Submit the form data using fetch
        fetch('submit_reservation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();

            if (data.success) {
                // Show success message with Toastify
                Toastify({
                    text: "Your reservation request has been successfully submitted!",
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                    className: "info",
                    position: "right",
                    duration: 3000
                }).showToast();

                // Optionally, close the modal
                modal.classList.add('hidden');
                clearViewingDetails(); 
                filterProperties(); // Reapply filters after submission
            } else {
                // Handle error or show failure message
                Toastify({
                    text: "There was an error submitting your reservation. Please try again.",
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc3a0)",
                    className: "error",
                    position: "right",
                    duration: 3000
                }).showToast();
            }
        })
        .catch(error => {
            hideLoading();
            Toastify({
                text: "An error occurred. Please try again later.",
                backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc3a0)",
                className: "error",
                position: "right",
                duration: 3000
            }).showToast();
        });
    });

    // Initialize page
    window.onload = function() {
        showLoading();
        setTimeout(() => {
            hideLoading();
            filterProperties(); // Initial filtering to set up the page
            preFillPersonalInfo();
        }, 1000);
    };
</script>




    </body>

    </html>
