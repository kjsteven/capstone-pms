<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Request</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
            <button id="tab-request" class="py-2 px-4 text-gray-700 focus:outline-none border-b-4 border-blue-600">Submit a Maintenance Request</button>
            <button id="tab-history" class="py-2 px-4 text-gray-700 focus:outline-none ml-4 border-b-4 border-transparent hover:border-blue-600">Request History</button>
        </div>

        <!-- Forms Section -->
        <div class="grid grid-cols-1">
            <!-- Submit a Maintenance Request Form -->
            <div id="request-content" class="bg-white shadow-lg rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold mb-6 text-center">Submit a Maintenance Request</h2>
                <form id="maintenance-form">
                    <!-- Unit Selection -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="unit">Unit</label>
                        <select id="unit" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none">
                            <option>Select your unit</option>
                            <option>Unit 101</option>
                            <option>Unit 102</option>
                            <option>Unit 201</option>
                        </select>
                    </div>

                    <!-- Issue Selection -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="issue">Issue</label>
                        <select id="issue" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none">
                            <option>Select the issue</option>
                            <option>Leaking Faucet</option>
                            <option>Broken Window</option>
                            <option>Heating Issue</option>
                            <option>Electrical Problem</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <!-- Issue Description -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="issue-description">Describe the issue</label>
                        <textarea id="issue-description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none" placeholder="Describe the issue you're experiencing..."></textarea>
                    </div>

                    <!-- Preferred Service Date -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="service-date">Preferred Date for Service</label>
                        <input type="date" id="service-date" class="w-full border border-gray-300 rounded-lg px-4 py-2 outline-none">
                    </div>

                    <!-- File Upload (Optional) -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Upload Images (Optional)</label>
                        <label for="file-upload" class="w-full h-32 flex flex-col items-center justify-center border-2 border-dashed border-gray-300 text-gray-600 rounded-lg cursor-pointer">
                            <i class="fas fa-upload text-2xl mb-2"></i>
                            <span>Click to upload or drag and drop</span>
                            <input id="file-upload" type="file" class="hidden" />
                            <span class="text-sm text-gray-500">(JPEG, PNG, GIF, max 5MB)</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between">
                        <button type="button" class="text-gray-700 border border-gray-400 rounded-lg px-4 py-2">Cancel</button>
                        <button type="button" id="submit-request" class="bg-blue-600 text-white rounded-lg px-4 py-2">Submit Request</button>
                    </div>
                </form>
            </div>

            <!-- Request History Section -->
            <div id="history-content" class="hidden-tab bg-white shadow-lg rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-6 text-left">Request History</h2>

               <!-- Search and Filter Section -->
                <div class="mb-4 flex items-center space-x-2">
                    <!-- Status Filter styled like "All Categories" with margin on arrow -->
                    <div class="relative">
                        <select id="status-filter" class="border border-gray-300 rounded-lg px-4 py-2 pr-8 outline-none appearance-none">
                            <option value="">All Status</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending">Pending</option>
                        </select>
                        <!-- Dropdown arrow margin achieved by adding an icon with padding -->
                        <span class="absolute inset-y-0 right-2 flex items-center pointer-events-none text-gray-500">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </div>

                    <!-- Keyword Search with reduced size -->
                    <div class="relative w-full sm:w-1/4">
                        <input type="text" id="search-keyword" placeholder="Search..." class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                        <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="py-2 px-4 border">Unit No</th>
                                <th class="py-2 px-4 border">Issue</th>
                                <th class="py-2 px-4 border">Description</th>
                                <th class="py-2 px-4 border">Date for Service</th>
                                <th class="py-2 px-4 border">Status</th> <!-- Added Status Column -->
                                <th class="py-2 px-4 border">Image</th>
                                <th class="py-2 px-4 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="request-table-body">
                            <!-- Sample Data -->
                            <tr>
                                <td class="py-2 px-4 border">Unit 101</td>
                                <td class="py-2 px-4 border">Leaking Faucet</td>
                                <td class="py-2 px-4 border">Water leaking from the kitchen faucet.</td>
                                <td class="py-2 px-4 border">2024-10-15</td>
                                <td class="py-2 px-4 border"><span class="text-yellow-600">In Progress</span></td> <!-- Status Example -->
                                <td class="py-2 px-4 border"><a href="path/to/image1.jpg" target="_blank" class="text-blue-600">View Image</a></td>
                                <td class="py-2 px-4 border flex justify-center">
                                    <button class="text-gray-500 hover:text-gray-800" onclick="deleteRequest(0)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-800 ml-2" onclick="downloadRequest(0)">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 border">Unit 102</td>
                                <td class="py-2 px-4 border">Broken Window</td>
                                <td class="py-2 px-4 border">The living room window is cracked.</td>
                                <td class="py-2 px-4 border">2024-10-17</td>
                                <td class="py-2 px-4 border"><span class="text-green-600">Completed</span></td> <!-- Status Example -->
                                <td class="py-2 px-4 border"><a href="path/to/image2.jpg" target="_blank" class="text-blue-600">View Image</a></td>
                                <td class="py-2 px-4 border flex justify-center">
                                    <button class="text-gray-500 hover:text-gray-800" onclick="deleteRequest(1)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-800 ml-2" onclick="downloadRequest(1)">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 border">Unit 201</td>
                                <td class="py-2 px-4 border">Heating Issue</td>
                                <td class="py-2 px-4 border">The heating system is not working.</td>
                                <td class="py-2 px-4 border">2024-10-20</td>
                                <td class="py-2 px-4 border"><span class="text-red-600">Pending</span></td> <!-- Status Example -->
                                <td class="py-2 px-4 border"><a href="path/to/image3.jpg" target="_blank" class="text-blue-600">View Image</a></td>
                                <td class="py-2 px-4 border flex justify-center">
                                    <button class="text-gray-500 hover:text-gray-600" onclick="deleteRequest(2)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-600 ml-2" onclick="downloadRequest(2)">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>


    <script>
        // JavaScript for tab navigation
        const tabRequest = document.getElementById('tab-request');
        const tabHistory = document.getElementById('tab-history');
        const requestContent = document.getElementById('request-content');
        const historyContent = document.getElementById('history-content');

        tabRequest.addEventListener('click', () => {
            requestContent.classList.remove('hidden-tab');
            historyContent.classList.add('hidden-tab');
            tabRequest.classList.add('border-blue-600');
            tabHistory.classList.remove('border-blue-600');
        });

        tabHistory.addEventListener('click', () => {
            historyContent.classList.remove('hidden-tab');
            requestContent.classList.add('hidden-tab');
            tabHistory.classList.add('border-blue-600');
            tabRequest.classList.remove('border-blue-600');
        });

        // JavaScript for filtering
        const keywordInput = document.getElementById('search-keyword');
        const statusFilter = document.getElementById('status-filter');
        const tableBody = document.getElementById('request-table-body');

        function filterRequests() {
            const keyword = keywordInput.value.toLowerCase();
            const status = statusFilter.value;

            Array.from(tableBody.children).forEach(row => {
                const rowText = row.innerText.toLowerCase();
                const rowStatus = row.querySelector('td:nth-child(5)').innerText;

                if ((rowText.includes(keyword) || keyword === '') && (status === rowStatus || status === '')) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        keywordInput.addEventListener('input', filterRequests);
        statusFilter.addEventListener('change', filterRequests);

        // Dummy functions for delete and download actions
        function deleteRequest(index) {
            alert(`Deleting request at index ${index}`);
        }

        function downloadRequest(index) {
            alert(`Downloading request at index ${index}`);
        }
    </script>


</body>
</html>
