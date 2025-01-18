
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Reports</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
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
<body> 

<!-- Include Navbar -->
<?php include('staffNavbar.php'); ?>

<!-- Include Sidebar -->
<?php include('staffSidebar.php'); ?>

<div class="p-4 sm:ml-64">
    <div class="mt-20">
        <h1 class="text-2xl font-semibold text-gray-600 dark:text-gray-600">Hi, Welcome </h1>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Overview of your work orders</p>
    </div>
</div>

<script src="../node_modules/feather-icons/dist/feather.min.js"></script>

</body>
</html>
