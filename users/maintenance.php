<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Request</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
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
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-6 text-center">Submit a Maintenance Request</h2>
            <form>
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
                    <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 py-2">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
