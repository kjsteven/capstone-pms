<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Profiles</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.10.3/dist/cdn.min.js" defer></script>
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

<body class="bg-gray-100 text-gray-900">

    <!-- Include Navbar -->
    <?php include('navbarAdmin.php'); ?>

    <!-- Include Sidebar -->
    <?php include('sidebarAdmin.php'); ?>

    <!-- Main Content -->
    <div class="p-4 sm:ml-64 mt-14">
        <!-- Header Section -->
        <div class="mb-6">
            <h1 class="text-3xl font-semibold text-gray-800">Tenant Information</h1>
            <p class="text-gray-600">Manage and view detailed tenant profiles</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
            <div class="relative max-w-xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-feather="search" class="h-5 w-5 text-gray-400"></i>
                </div>
                <input type="text" id="searchTenant" 
                    placeholder="Search by tenant name, unit, or status..."
                    class="w-full pl-10 pr-4 py-3 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg 
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    transition-all duration-300">
            </div>
        </div>

        <!-- Tenant List Container -->
        <div id="tenantList" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mx-auto">
            <!-- Tenant Card -->
            <div class="tenant-card bg-white shadow-lg rounded-xl overflow-hidden transition-all duration-300 hover:shadow-xl" 
                data-name="john doe">
                <!-- Card Header -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <img src="../images/kj1.jpg" alt="Tenant Photo" 
                                class="w-16 h-16 rounded-full ring-2 ring-blue-500 ring-offset-2">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">John Doe</h2>
                                <div class="flex items-center space-x-2 text-gray-500 text-sm">
                                    <i data-feather="home" class="w-4 h-4"></i>
                                    <span>Unit A-101</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                <i data-feather="edit-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="border-b bg-gray-50">
                    <div class="flex">
                        <button class="tab-btn flex items-center px-6 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-blue-600 hover:border-blue-600 transition-all active" data-tab="payments1">
                            <i data-feather="credit-card" class="w-4 h-4 mr-2"></i>
                            Payments
                        </button>
                        <button class="tab-btn flex items-center px-6 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-blue-600 hover:border-blue-600 transition-all" data-tab="maintenance1">
                            <i data-feather="tool" class="w-4 h-4 mr-2"></i>
                            Maintenance
                        </button>
                        <button class="tab-btn flex items-center px-6 py-3 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-blue-600 hover:border-blue-600 transition-all" data-tab="reservations1">
                            <i data-feather="calendar" class="w-4 h-4 mr-2"></i>
                            Reservations
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Payments Tab -->
                    <div id="payments1" class="tab-content opacity-0 transition-all duration-300">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium">Last Payment</p>
                                        <p class="text-sm text-gray-500">March 2024 - GCash</p>
                                    </div>
                                </div>
                                <span class="text-lg font-semibold">₱500</span>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance Tab -->
                    <div id="maintenance1" class="tab-content opacity-0 transition-all duration-300 hidden">
                        <h3 class="text-lg font-semibold mb-2">
                            <i data-feather="tool" class="inline-block w-4 h-4 mr-1"></i> Maintenance Requests
                        </h3>
                        <p><i data-feather="alert-circle" class="inline-block w-4 h-4 mr-1"></i> Leak in the kitchen sink (Pending)</p>
                        <p><i data-feather="check-circle" class="inline-block w-4 h-4 mr-1"></i> Air conditioner repair (Completed)</p>
                    </div>

                    <!-- Reservations Tab -->
                    <div id="reservations1" class="tab-content opacity-0 transition-all duration-300 hidden">
                        <h3 class="text-lg font-semibold mb-2">
                            <i data-feather="calendar" class="inline-block w-4 h-4 mr-1"></i> Upcoming Reservations
                        </h3>
                        <p><i data-feather="activity" class="inline-block w-4 h-4 mr-1"></i> Gym - April 5, 2024</p>
                        <p><i data-feather="home" class="inline-block w-4 h-4 mr-1"></i> Clubhouse - April 20, 2024</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->

    <script src="../node_modules/feather-icons/dist/feather.min.js"></script>

    <script>
        // Initialize Feather Icons
        feather.replace();
    </script>

    <!-- Include Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>

    <script>
        // Search Function with Animation
        document.getElementById("searchTenant").addEventListener("input", function () {
            const query = this.value.toLowerCase().trim();
            const tenantCards = document.querySelectorAll(".tenant-card");

            tenantCards.forEach(card => {
                const name = card.getAttribute("data-name");
                if (query && name.includes(query)) {
                    card.classList.add("border-4", "border-blue-500", "shadow-xl", "scale-105");
                } else {
                    card.classList.remove("border-4", "border-blue-500", "shadow-xl", "scale-105");
                }
            });
        });

        // Enhanced tab switching with smooth transitions
        document.querySelectorAll(".tab-btn").forEach(button => {
            button.addEventListener("click", function() {
                const tabGroup = this.closest(".tenant-card");
                
                // Remove active states
                tabGroup.querySelectorAll(".tab-btn").forEach(btn => {
                    btn.classList.remove("text-blue-600", "border-blue-600");
                    btn.classList.add("text-gray-600", "border-transparent");
                });

                // Add active states
                this.classList.remove("text-gray-600", "border-transparent");
                this.classList.add("text-blue-600", "border-blue-600");

                // Handle content transition
                tabGroup.querySelectorAll(".tab-content").forEach(tab => {
                    tab.classList.add("opacity-0");
                    setTimeout(() => tab.classList.add("hidden"), 300);
                });

                const selectedTab = tabGroup.querySelector(`#${this.dataset.tab}`);
                selectedTab.classList.remove("hidden");
                setTimeout(() => selectedTab.classList.remove("opacity-0"), 10);
            });
        });

        // Initialize first tab as active
        document.querySelectorAll(".tenant-card").forEach(card => {
            const firstTab = card.querySelector(".tab-btn");
            const firstContent = card.querySelector(".tab-content");
            if (firstTab && firstContent) {
                firstTab.classList.add("text-blue-600", "border-blue-600");
                firstContent.classList.remove("hidden", "opacity-0");
            }
        });
    </script>

</body>
</html>
