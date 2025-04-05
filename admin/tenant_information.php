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

    <!-- Main Content - Adjusted margin for sidebar -->
    <div class="p-4 sm:ml-64 mt-14">
        <!-- Search Bar -->
        <div class="max-w-3xl mx-auto mb-6">
            <input type="text" id="searchTenant" placeholder=" Search for a tenant..."
                class="w-full p-4 text-lg border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300 transform focus:scale-105">
        </div>

        <!-- Tenant List -->
        <div id="tenantList" class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <!-- Tenant Card -->
            <div class="tenant-card border border-gray-300 bg-white shadow-lg rounded-xl p-6 transition-all transform hover:scale-105 duration-300" data-name="john doe">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img src="https://via.placeholder.com/80" alt="Tenant Photo" class="w-16 h-16 rounded-full mr-4 shadow">
                        <div>
                            <h2 class="text-2xl font-bold">John Doe</h2>
                            <p class="text-gray-500">
                                <i data-feather="home" class="inline-block w-4 h-4 mr-1"></i> 
                                Unit: A-101
                            </p>
                            <p class="text-gray-500">
                                <i data-feather="file-text" class="inline-block w-4 h-4 mr-1"></i> 
                                Contract: 12 Months
                            </p>
                        </div>
                    </div>
                    <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all duration-200 transform active:scale-90">
                        <i data-feather="edit-2" class="inline-block w-4 h-4"></i>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="border-b mt-4">
                    <div class="flex space-x-4">
                        <button class="tab-btn px-4 py-2 text-gray-700 font-semibold active" data-tab="payments1">
                            <i data-feather="credit-card" class="inline-block w-4 h-4 mr-1"></i> Payments
                        </button>
                        <button class="tab-btn px-4 py-2 text-gray-700 font-semibold" data-tab="maintenance1">
                            <i data-feather="tool" class="inline-block w-4 h-4 mr-1"></i> Maintenance
                        </button>
                        <button class="tab-btn px-4 py-2 text-gray-700 font-semibold" data-tab="reservations1">
                            <i data-feather="calendar" class="inline-block w-4 h-4 mr-1"></i> Reservations
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div id="payments1" class="tab-content py-4 opacity-0 transition-opacity duration-500">
                    <h3 class="text-lg font-semibold mb-2">
                        <i data-feather="credit-card" class="inline-block w-4 h-4 mr-1"></i> Payment History
                    </h3>
                    <p><strong>Last Payment:</strong> GCash - $500 (March 2024)</p>
                    <p><strong>Next Due:</strong> April 15, 2024</p>
                    <p><strong>Invoice:</strong> <a href="#" class="text-blue-500 underline">
                        <i data-feather="download" class="inline-block w-4 h-4 mr-1"></i> Download PDF
                    </a></p>
                </div>

                <div id="maintenance1" class="tab-content py-4 hidden opacity-0 transition-opacity duration-500">
                    <h3 class="text-lg font-semibold mb-2">
                        <i data-feather="tool" class="inline-block w-4 h-4 mr-1"></i> Maintenance Requests
                    </h3>
                    <p><i data-feather="alert-circle" class="inline-block w-4 h-4 mr-1"></i> Leak in the kitchen sink (Pending)</p>
                    <p><i data-feather="check-circle" class="inline-block w-4 h-4 mr-1"></i> Air conditioner repair (Completed)</p>
                </div>

                <div id="reservations1" class="tab-content py-4 hidden opacity-0 transition-opacity duration-500">
                    <h3 class="text-lg font-semibold mb-2">
                        <i data-feather="calendar" class="inline-block w-4 h-4 mr-1"></i> Upcoming Reservations
                    </h3>
                    <p><i data-feather="activity" class="inline-block w-4 h-4 mr-1"></i> Gym - April 5, 2024</p>
                    <p><i data-feather="home" class="inline-block w-4 h-4 mr-1"></i> Clubhouse - April 20, 2024</p>
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

        // Tab Switching Function with Fade-in Animation
        document.querySelectorAll(".tab-btn").forEach(button => {
            button.addEventListener("click", function () {
                const tabGroup = this.closest(".tenant-card");
                tabGroup.querySelectorAll(".tab-btn").forEach(btn => btn.classList.remove("text-blue-500"));
                this.classList.add("text-blue-500");

                tabGroup.querySelectorAll(".tab-content").forEach(tab => {
                    tab.classList.add("hidden");
                    tab.classList.remove("opacity-100");
                });

                const selectedTab = tabGroup.querySelector(`#${this.dataset.tab}`);
                selectedTab.classList.remove("hidden");
                setTimeout(() => selectedTab.classList.add("opacity-100"), 10);
            });
        });
    </script>

</body>
</html>
