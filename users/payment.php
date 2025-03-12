<?php

require_once '../session/session_manager.php';
require '../session/db.php';

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../authentication/login.php'); // Adjust the path as necessary
    exit();
}

// Fetch tenant data for unit selection
$query = "
    SELECT 
        p.unit_id,
        p.unit_no,
        t.outstanding_balance
    FROM tenants t
    JOIN property p ON t.unit_rented = p.unit_id
    WHERE t.user_id = ? AND t.status = 'active'
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$units = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch payment history - modified to include payment type and method
$query = "
    SELECT 
        p.payment_id,
        p.amount,
        p.payment_date,
        p.reference_number,
        p.status,
        p.receipt_image,
        p.gcash_number,
        p.payment_type,
        p.bill_item,
        pr.unit_no
    FROM payments p
    JOIN tenants t ON p.tenant_id = t.tenant_id
    JOIN property pr ON t.unit_rented = pr.unit_id
    WHERE t.user_id = ?
    ORDER BY p.payment_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$payments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Payment</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Add Toastify CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>
   
   <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        input:disabled {
            background-color: #f3f4f6;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .active-tab {
            border-bottom: 4px solid #2563eb;
            color: #2563eb;
            font-weight: 600;
        }
        
        /* File upload animation styles */
        .file-upload-wrapper {
            position: relative;
            width: 100%;
            height: auto;
            min-height: 200px;
            max-height: 300px;
            margin-bottom: 1.5rem;
        }
        .file-upload-preview {
            display: none;
            width: 100%;
            height: 100%;
            max-height: 250px;
            object-fit: contain;
            border: 2px solid #3b82f6;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .file-upload-container {
            border: 2px dashed #cbd5e1;
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .file-upload-container:hover {
            border-color: #3b82f6;
            background-color: rgba(59, 130, 246, 0.05);
        }
        .upload-animation {
            animation: bounce 1s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        /* Loading spinner */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #3b82f6;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
        <!-- Tab Navigation -->
        <div class="flex mb-4 border-b">
            <button onclick="openTab('paymentTab')" id="paymentTab-btn" class="px-6 py-2 text-gray-700 focus:outline-none border-b-4 border-transparent active-tab">
                Rental Payment
            </button>
            <button onclick="openTab('historyTab')" id="historyTab-btn" class="px-6 py-2 text-gray-700 focus:outline-none ml-4 border-b-4 border-transparent">
                Payment History
            </button>
        </div>

        <!-- Rental Payment Tab -->
        <div id="paymentTab" class="tab-content active bg-white shadow-lg rounded-lg p-6 w-full">
            <div class="flex justify-center mb-8">
                <img src="../images/gcash.png" alt="GCash Logo" class="h-16 object-contain">
            </div>

            <!-- QR Code Section -->
            <div class="mb-8 text-center">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Scan to Pay</h3>
                <div class="flex justify-center">
                    <div class="p-4 bg-white border-2 border-gray-200 rounded-lg shadow-md">
                        <img src="../images/gcash_qr.jpg" alt="GCash QR Code" class="w-64 h-64 object-contain">
                    </div>
                </div>
                <p class="mt-4 text-sm text-gray-600">Open GCash app and scan this QR code to pay</p>
                <p class="mt-2 text-xs text-blue-600">To view your outstanding balance, check the <a href="unitinfo.php" class="underline">Unit Information</a> page</p>
            </div>

            <form id="payment-form" class="space-y-6 max-w-3xl mx-auto" enctype="multipart/form-data">
                <div>
                    <label for="unit-no" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-home mr-2 text-blue-500"></i>Unit Number
                    </label>
                    <select 
                        id="unit-no" 
                        name="unit_id"
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                    >
                        <option value="">Select your unit</option>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?php echo $unit['unit_id']; ?>">
                                <?php echo htmlspecialchars($unit['unit_no']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Payment Type Selection -->
                <div>
                    <label for="payment-type" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-2 text-blue-500"></i>Payment Type
                    </label>
                    <select 
                        id="payment-type" 
                        name="payment_type"
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                        onchange="toggleBillItemField()"
                    >
                        <option value="rent">Rent Payment</option>
                        <option value="maintenance">Maintenance Fee</option>
                        <option value="utilities">Utilities</option>
                        <option value="other">Other Payment</option>
                    </select>
                </div>

                <!-- Bill Item Field (initially hidden) -->
                <div id="bill-item-container" class="hidden">
                    <label for="bill-item" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-invoice mr-2 text-blue-500"></i>Bill Item
                    </label>
                    <input 
                        type="text" 
                        id="bill-item" 
                        name="bill_item"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                        placeholder="Enter bill description (e.g., Water Bill, Electric Bill)"
                    >
                </div>

                <div>
                    <label for="gcash-number" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-mobile-alt mr-2 text-blue-500"></i>GCash Number
                    </label>
                    <input 
                        type="tel" 
                        id="gcash-number" 
                        name="gcash_number"
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                        placeholder="Enter your GCash number"
                        pattern="^09\d{9}$"
                        title="Please enter a valid Philippine mobile number starting with 09 (11 digits)"
                    >
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-money-bill-wave mr-2 text-blue-500"></i>Amount (PHP)
                    </label>
                    <input 
                        type="number" 
                        id="amount" 
                        name="amount"
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                        placeholder="Enter payment amount"
                        min="0"
                        step="0.01"
                    >
                </div>

                <div>
                    <label for="reference-number" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-receipt mr-2 text-blue-500"></i>Reference Number
                    </label>
                    <input 
                        type="text" 
                        id="reference-number" 
                        name="reference_number"
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-300"
                        placeholder="Enter GCash reference number"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-upload mr-2 text-blue-500"></i>Payment Receipt
                    </label>
                    <div class="file-upload-wrapper">
                        <!-- Image preview container -->
                        <div id="preview-container" class="mb-4 hidden">
                            <img id="receipt-preview" class="mx-auto max-h-[250px] object-contain border-2 border-blue-500 rounded-lg p-1" src="#" alt="Receipt Preview">
                            <div class="flex justify-center mt-2">
                                <button type="button" id="remove-button" class="px-3 py-1 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition duration-300">
                                    <i class="fas fa-trash mr-1"></i> Remove
                                </button>
                            </div>
                        </div>
                        
                        <!-- File upload container -->
                        <div id="upload-container" class="file-upload-container">
                            <input type="file" id="receipt-file" name="receipt" class="hidden" accept="image/*" />
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="mt-2 text-sm font-medium text-gray-700">Click to upload receipt</p>
                                <p id="file-name" class="text-sm text-gray-500 mt-1"></p>
                                <p class="text-xs text-gray-500 mt-1">Supported formats: JPG, PNG, GIF</p>
                            </div>
                        </div>
                        
                        <!-- Upload success animation (hidden by default) -->
                        <div id="upload-success" class="hidden text-center">
                            <div class="upload-animation inline-block">
                                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-green-500 font-medium mt-2">Receipt uploaded successfully!</p>
                        </div>
                    </div>
                </div>

                <div>
                    <button 
                        type="submit" 
                        id="submit-btn"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                    >
                        <i class="fas fa-paper-plane mr-2"></i>Submit Payment
                    </button>
                </div>
            </form>
        </div>

        <!-- Payment History Tab -->
        <div id="historyTab" class="tab-content bg-white shadow-lg rounded-lg p-6 w-full">
            <h2 class="text-xl font-semibold mb-6">Payment History</h2>

            <!-- Search and Filter Section -->
            <div class="mb-4 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2 w-full md:w-2/3">
                    <!-- Status Filter -->
                    <div class="relative w-1/3">
                        <select id="status-filter" class="border border-gray-300 rounded-lg px-4 py-2 w-full outline-none" onchange="filterTable()">
                            <option value="">All Status</option>
                            <option value="Received">Received</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>

                    <!-- Keyword Search -->
                    <div class="relative flex-grow w-2/3">
                        <input type="text" id="search-keyword" placeholder="Search..." oninput="filterTable()" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                        <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Print Button -->
                <div class="mt-4 md:mt-0 w-full md:w-auto">
                    <button onclick="printPaymentHistory()" class="bg-blue-600 text-white px-4 py-2 rounded-lg w-full md:w-auto">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                </div>
            </div>

            <!-- Payment History Table -->
            <div class="overflow-x-auto">
                <table id="payment-history-table" class="w-full min-w-full border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="py-2 px-4 border">Unit No</th>
                            <th class="py-2 px-4 border">Amount (PHP)</th>
                            <th class="py-2 px-4 border">Type</th>
                            <th class="py-2 px-4 border">Bill Item</th>
                            <th class="py-2 px-4 border">Method</th>
                            <th class="py-2 px-4 border">Date of Payment</th>
                            <th class="py-2 px-4 border">Reference No</th> 
                            <th class="py-2 px-4 border">Status</th>
                            <th class="py-2 px-4 border">Receipt</th>
                        </tr>
                    </thead>
                    <tbody id="payment-history-body">
                        <?php if (count($payments) > 0): ?>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td class="py-2 px-4 border"><?php echo htmlspecialchars($payment['unit_no']); ?></td>
                                    <td class="py-2 px-4 border">PHP <?php echo number_format($payment['amount'], 2); ?></td>
                                    <td class="py-2 px-4 border">
                                        <?php 
                                            $paymentType = $payment['payment_type'] ?? 'rent';
                                            echo ucfirst(htmlspecialchars($paymentType));
                                        ?>
                                    </td>
                                    <td class="py-2 px-4 border">
                                        <?php 
                                            if (!empty($payment['bill_item'])) {
                                                echo htmlspecialchars($payment['bill_item']);
                                            } else {
                                                echo $paymentType === 'rent' ? 'Monthly Rent' : 'N/A';
                                            }
                                        ?>
                                    </td>
                                    <td class="py-2 px-4 border">
                                        <?php if (!empty($payment['gcash_number'])): ?>
                                            <span class="flex items-center">
                                                <img src="../images/gcash.png" alt="GCash" class="w-4 h-4 mr-1">
                                                GCash
                                            </span>
                                        <?php else: ?>
                                            <span class="flex items-center">
                                                <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                                                Cash
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-4 border"><?php echo date('Y-m-d', strtotime($payment['payment_date'])); ?></td>
                                    <td class="py-2 px-4 border"><?php echo htmlspecialchars($payment['reference_number']); ?></td>
                                    <td class="py-2 px-4 border">
                                        <?php if ($payment['status'] === 'Received'): ?>
                                            <span class="text-green-600">Received</span>
                                        <?php elseif ($payment['status'] === 'Pending'): ?>
                                            <span class="text-yellow-600">Pending</span>
                                        <?php elseif ($payment['status'] === 'Rejected'): ?>
                                            <span class="text-red-600">Rejected</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="py-2 px-4 border text-center">
                                        <?php if (!empty($payment['receipt_image'])): ?>
                                            <button 
                                                class="view-receipt bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs"
                                                data-receipt="../<?php echo htmlspecialchars($payment['receipt_image']); ?>"
                                            >
                                                <i class="fas fa-eye mr-1"></i> View
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-500 text-xs">No receipt</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="py-4 text-center text-gray-500">No payment history found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-5 rounded-lg flex flex-col items-center">
            <div class="spinner mb-3"></div>
            <p class="text-gray-700">Processing payment...</p>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receipt-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Payment Receipt</h3>
                <button id="close-receipt-modal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex justify-center">
                <img id="modal-receipt-img" class="max-h-96 object-contain" src="" alt="Payment Receipt">
            </div>
            <div class="mt-4 flex justify-center">
                <a id="download-receipt" href="#" download class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-download mr-2"></i>Download Receipt
                </a>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        function openTab(tabId) {
            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show the selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Update tab button styles
            document.querySelectorAll('.flex.mb-4.border-b button').forEach(btn => {
                btn.classList.remove('active-tab');
            });
            
            document.getElementById(tabId + '-btn').classList.add('active-tab');
        }

        // Enhanced file upload preview
        const receiptFile = document.getElementById('receipt-file');
        const receiptPreview = document.getElementById('receipt-preview');
        const removeButton = document.getElementById('remove-button');
        const uploadContainer = document.getElementById('upload-container');
        const previewContainer = document.getElementById('preview-container');
        const uploadSuccess = document.getElementById('upload-success');
        const fileName = document.getElementById('file-name');
        const loadingOverlay = document.getElementById('loading-overlay');

        // Make the upload container clickable
        uploadContainer.addEventListener('click', function() {
            receiptFile.click();
        });

        // Handle file removal
        removeButton.addEventListener('click', function() {
            receiptFile.value = '';
            previewContainer.classList.add('hidden');
            uploadContainer.classList.remove('hidden');
            uploadSuccess.classList.add('hidden');
        });

        // Handle file selection
        receiptFile.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                const reader = new FileReader();
                
                // Show file name
                const displayName = file.name.length > 30 
                    ? file.name.substring(0, 27) + '...' 
                    : file.name;
                fileName.textContent = displayName;
                
                // Show loading animation
                uploadContainer.classList.add('hidden');
                uploadSuccess.classList.remove('hidden');
                
                reader.onload = function(e) {
                    // After a short delay, show the preview
                    setTimeout(() => {
                        // Set preview image
                        receiptPreview.src = e.target.result;
                        
                        // Hide success animation and show preview
                        uploadSuccess.classList.add('hidden');
                        previewContainer.classList.remove('hidden');
                        
                        // Show toast notification
                        Toastify({
                            text: "Receipt uploaded successfully! Please review before submitting.",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                            stopOnFocus: true
                        }).showToast();
                    }, 1000);
                }
                
                reader.readAsDataURL(file);
            }
        });

        // Form validation and submission
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic form validation
            const unitId = document.getElementById('unit-no').value;
            const paymentType = document.getElementById('payment-type').value;
            const gcashNumber = document.getElementById('gcash-number').value;
            const amount = document.getElementById('amount').value;
            const referenceNumber = document.getElementById('reference-number').value;
            
            // Validate bill item if not a rent payment
            if (paymentType !== 'rent' && !document.getElementById('bill-item').value.trim()) {
                Toastify({
                    text: "Please enter a bill item description",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    stopOnFocus: true
                }).showToast();
                return;
            }
            
            if (!unitId || !gcashNumber || !amount || !referenceNumber || !receiptFile.files[0]) {
                Toastify({
                    text: "Please fill in all required fields and upload a receipt",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    stopOnFocus: true
                }).showToast();
                return;
            }
            
            // Filipino mobile number validation (11 digits starting with 09)
            const mobileRegex = /^09\d{9}$/;
            if (!mobileRegex.test(gcashNumber)) {
                Toastify({
                    text: "Please enter a valid Philippine mobile number (e.g., 09123456789)",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    stopOnFocus: true
                }).showToast();
                return;
            }
            
            // Create FormData object to send the form data including the file
            const formData = new FormData(this);
            
            // Show loading overlay
            loadingOverlay.classList.remove('hidden');
            
            // Disable submit button to prevent double submission
            document.getElementById('submit-btn').disabled = true;
            
            // Submit the form data using fetch
            fetch('process_payment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server returned ' + response.status + ' ' + response.statusText);
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch(e) {
                        console.error('Invalid JSON response:', text);
                        throw new Error('Invalid server response format');
                    }
                });
            })
            .then(data => {
                // Hide loading overlay
                loadingOverlay.classList.add('hidden');
                
                if (data.status === 'success') {
                    // Show success message
                    Toastify({
                        text: data.message || "Payment submitted successfully!",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                        stopOnFocus: true
                    }).showToast();
                    
                    // Reset form
                    this.reset();
                    receiptPreview.src = '#';
                    previewContainer.classList.add('hidden');
                    uploadContainer.classList.remove('hidden');
                    fileName.textContent = '';
                    
                    // Refresh payment history if we're showing it
                    setTimeout(() => {
                        window.location.reload(); // Reload to see the new payment
                    }, 2000);
                } else {
                    // Show error message
                    Toastify({
                        text: data.message || "Error submitting payment. Please try again.",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        stopOnFocus: true
                    }).showToast();
                }
                
                // Re-enable submit button
                document.getElementById('submit-btn').disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                // Hide loading overlay
                loadingOverlay.classList.add('hidden');
                
                // Show error message
                Toastify({
                    text: "An error occurred while processing your payment. If the issue persists, please contact support.",
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    stopOnFocus: true
                }).showToast();
                
                // Re-enable submit button
                document.getElementById('submit-btn').disabled = false;
            });
        });

        // Payment history table filtering
        function filterTable() {
            const statusFilter = document.getElementById('status-filter').value.toLowerCase();
            const searchKeyword = document.getElementById('search-keyword').value.toLowerCase();
            const tableRows = document.getElementById('payment-history-body').getElementsByTagName('tr');
            
            Array.from(tableRows).forEach(row => {
                const unitNo = row.cells[0]?.textContent.toLowerCase() || '';
                const amount = row.cells[1]?.textContent.toLowerCase() || '';
                const paymentType = row.cells[2]?.textContent.toLowerCase() || '';
                const billItem = row.cells[3]?.textContent.toLowerCase() || '';
                const method = row.cells[4]?.textContent.toLowerCase() || '';
                const date = row.cells[5]?.textContent.toLowerCase() || '';
                const reference = row.cells[6]?.textContent.toLowerCase() || '';
                const status = row.cells[7]?.textContent.toLowerCase() || '';
                
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                const matchesKeyword = !searchKeyword || 
                    unitNo.includes(searchKeyword) || 
                    amount.includes(searchKeyword) || 
                    paymentType.includes(searchKeyword) || 
                    billItem.includes(searchKeyword) || 
                    method.includes(searchKeyword) || 
                    date.includes(searchKeyword) || 
                    reference.includes(searchKeyword);
                
                row.style.display = (matchesStatus && matchesKeyword) ? '' : 'none';
            });
        }

        // Print payment history
        function printPaymentHistory() {
            const table = document.getElementById('payment-history-table').cloneNode(true);
            
            // Remove View button from the last column before printing
            Array.from(table.querySelectorAll('tbody tr')).forEach(row => {
                if (row.style.display === 'none') {
                    row.parentNode.removeChild(row);
                } else {
                    const receiptCell = row.cells[row.cells.length - 1];
                    const hasReceipt = receiptCell.querySelector('.view-receipt') !== null;
                    receiptCell.innerHTML = hasReceipt ? 'Available' : 'Not available';
                }
            });
            
            const printWindow = window.open('', '', 'width=800,height=600');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Payment History</title>
                        <style>
                            body {
                                font-family: 'Arial', sans-serif;
                                padding: 20px;
                            }
                            h2 {
                                text-align: center;
                                margin-bottom: 20px;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-bottom: 20px;
                            }
                            th, td {
                                border: 1px solid #ddd;
                                padding: 8px;
                                text-align: left;
                                font-size: 12px;
                            }
                            th {
                                background-color: #f2f2f2;
                            }
                            .text-green-600 {
                                color: #059669;
                            }
                            .text-yellow-600 {
                                color: #d97706;
                            }
                            .print-date {
                                text-align: right;
                                margin-bottom: 20px;
                                font-size: 12px;
                            }
                            @media print {
                                table { page-break-inside: auto; }
                                tr { page-break-inside: avoid; page-break-after: auto; }
                            }
                        </style>
                    </head>
                    <body>
                        <h2>Payment History</h2>
                        <div class="print-date">Date: ${new Date().toLocaleDateString()}</div>
                        ${table.outerHTML}
                    </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.onload = () => {
                printWindow.print();
                printWindow.close();
            };
        }

        // View Receipt functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('view-receipt') || e.target.closest('.view-receipt')) {
                const button = e.target.classList.contains('view-receipt') ? e.target : e.target.closest('.view-receipt');
                const receiptPath = button.getAttribute('data-receipt');
                
                // Set the image in the modal
                document.getElementById('modal-receipt-img').src = receiptPath;
                document.getElementById('download-receipt').href = receiptPath;
                
                // Show the modal
                document.getElementById('receipt-modal').classList.remove('hidden');
            }
        });

        // Close receipt modal
        document.getElementById('close-receipt-modal').addEventListener('click', function() {
            document.getElementById('receipt-modal').classList.add('hidden');
        });

        // Close modal when clicking outside
        document.getElementById('receipt-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        // Toggle bill item field based on payment type
        function toggleBillItemField() {
            const paymentType = document.getElementById('payment-type').value;
            const billItemContainer = document.getElementById('bill-item-container');
            const billItemInput = document.getElementById('bill-item');
            
            if (paymentType === 'rent') {
                billItemContainer.classList.add('hidden');
                billItemInput.removeAttribute('required');
            } else {
                billItemContainer.classList.remove('hidden');
                billItemInput.setAttribute('required', 'required');
                
                // Set default bill item label based on payment type
                switch(paymentType) {
                    case 'maintenance':
                        billItemInput.placeholder = "Enter maintenance description (e.g., Plumbing, AC repair)";
                        break;
                    case 'utilities':
                        billItemInput.placeholder = "Enter utility type (e.g., Water Bill, Electric Bill)";
                        break;
                    case 'other':
                        billItemInput.placeholder = "Enter payment description";
                        break;
                }
            }
        }

        // Initialize bill item field on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize payment type field
            toggleBillItemField();
        });
    </script>
</body>
</html>
