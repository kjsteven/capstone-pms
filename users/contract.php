<?php

require_once '../session/session_manager.php';
require '../session/db.php';

start_secure_session();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../authentication/login.php');
    exit();
}

// Fetch tenant's contracts
$user_id = $_SESSION['user_id'];
$query = "SELECT t.tenant_id, t.contract_file, t.contract_upload_date, 
          p.unit_no, p.unit_type, t.rent_from, t.rent_until 
          FROM tenants t 
          JOIN property p ON t.unit_rented = p.unit_id 
          WHERE t.user_id = ? AND t.status = 'active'";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$contracts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Agreement</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
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
            <h2 class="text-2xl font-semibold mb-4">Your Rent Agreements</h2>
        </div>

        <?php if (empty($contracts)): ?>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <div class="text-gray-500 mb-4">
                    <svg data-feather="file-text" class="w-16 h-16 mx-auto mb-4"></svg>
                    <p>No contracts found</p>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($contracts as $contract): ?>
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <svg data-feather="home" class="w-5 h-5 text-blue-600 mr-2"></svg>
                                <h3 class="text-lg font-semibold">Unit <?php echo htmlspecialchars($contract['unit_no']); ?></h3>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                <?php echo $contract['contract_file'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $contract['contract_file'] ? 'Active' : 'Pending'; ?>
                            </span>
                        </div>

                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <p><span class="font-semibold">Type:</span> <?php echo htmlspecialchars($contract['unit_type']); ?></p>
                            <p><span class="font-semibold">Period:</span> 
                                <?php 
                                echo date('M d, Y', strtotime($contract['rent_from'])) . ' - ' . 
                                     date('M d, Y', strtotime($contract['rent_until'])); 
                                ?>
                            </p>
                            <?php if ($contract['contract_upload_date']): ?>
                                <p><span class="font-semibold">Uploaded:</span> 
                                    <?php echo date('M d, Y', strtotime($contract['contract_upload_date'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php if ($contract['contract_file']): ?>
                            <div class="flex justify-between items-center">
                                <a href="<?php echo htmlspecialchars('../' . $contract['contract_file']); ?>" 
                                   target="_blank"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                    <svg data-feather="eye" class="w-4 h-4 mr-2"></svg>
                                    View Contract
                                </a>
                                <a href="<?php echo htmlspecialchars('../' . $contract['contract_file']); ?>" 
                                   download
                                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                                    <svg data-feather="download" class="w-4 h-4 mr-2"></svg>
                                    Download
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-gray-500">
                                <p>Contract not yet uploaded</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../node_modules/feather-icons/dist/feather.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>
    <script>
        feather.replace();

        // Show notification when contract is downloaded
        document.querySelectorAll('a[download]').forEach(link => {
            link.addEventListener('click', () => {
                Toastify({
                    text: "Downloading contract...",
                    duration: 3000,
                    backgroundColor: "#4CAF50"
                }).showToast();
            });
        });
    </script>
</body>

</html>
