<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Agreement</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-100 font-[Poppins]">

    <!-- Include Navbar -->
    <?php include('navbar.php'); ?>

    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Content Wrapper to avoid overlap -->
    <div class="sm:ml-64 p-6 mt-20">
        <!-- Title and Search Form -->
        <div class="mb-6">
            <h2 class="text-2xl font-semibold mb-4">Your Rent Agreement</h2>
            <form class="max-w-lg w-full">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full">
                        <input type="search" id="search-agreement" class="block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Search for agreements..." required />
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

        <!-- Agreement Table -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600">Unit Type</th>
                        <th class="px-4 py-2 text-left text-gray-600">Unit Number</th>
                        <th class="px-4 py-2 text-left text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td class="px-4 py-2">Standard Unit</td>
                        <td class="px-4 py-2">101</td>
                        <td class="px-4 py-2 flex space-x-4">
                            <a href="#" class="text-gray-500 hover:text-gray-700" onclick="viewAgreement()">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" onclick="window.print()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-download"></i>
                            </a>
                        </td>
                    </tr>
                    <!-- Add more rows dynamically here using PHP -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function viewAgreement() {
            alert('Viewing Rent Agreement...');
        }
    </script>
</body>

</html>
