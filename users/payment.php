<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        input:disabled {
            background-color: #f3f4f6; /* Light gray background for disabled inputs */
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
        <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-md mx-auto">
            <h2 class="text-2xl font-semibold mb-6 text-center">Rental Payment</h2>

            <!-- GCash Logo -->
            <div class="flex justify-center mb-6">
                <img src="../images/gcash.png" alt="GCash Logo" width="150" height="50">
            </div>

            <form id="payment-form">
                <!-- Tenant Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tenant-name">Tenant Name</label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-user text-gray-500"></i>
                        <input type="text" id="tenant-name" required class="ml-2 w-full outline-none" placeholder="Enter your name">
                    </div>
                </div>

                <!-- Unit No -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="unit-no">Unit No</label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-home text-gray-500"></i>
                        <input type="text" id="unit-no" required class="ml-2 w-full outline-none" placeholder="Enter your unit number">
                    </div>
                </div>

                <!-- Rental Period -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="rental-period">Rental Period</label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-calendar text-gray-500"></i>
                        <input type="date" id="rental-period" required class="ml-2 w-full outline-none">
                    </div>
                </div>

                <!-- Amount (PHP) -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="amount">Amount (PHP)</label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2">
                        <i class="fas fa-money-bill-wave text-gray-500"></i>
                        <input type="number" id="amount" required class="ml-2 w-full outline-none" placeholder="Enter amount">
                    </div>
                </div>

                <!-- Pay Button -->
                <button type="submit" id="pay-button" class="bg-blue-600 text-white rounded-lg px-4 py-2 w-full hover:bg-blue-700 transition">
                    Pay Now with GCash
                </button>
            </form>
        </div>
    </div>

    <script>
        const payButton = document.getElementById('pay-button');
        const paymentForm = document.getElementById('payment-form');

        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const amount = document.getElementById('amount').value;

            // Call your server to create a payment intent
            const response = await fetch('/create-payment-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ amount }),
            });

            const { client_secret } = await response.json();

            // Use PayMongo to confirm the payment
            const { paymentIntent } = await paymongo.confirmPaymentIntent(client_secret);
            if (paymentIntent.status === 'succeeded') {
                alert('Payment successful!');
                // Redirect or show success message
            } else {
                alert('Payment failed. Please try again.');
            }
        });
    </script>

</body>
</html>
