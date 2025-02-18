<?php
require_once '../session/session_manager.php';
require '../session/db.php';

start_secure_session();


$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header('Location: ../authentication/login.php');
    exit();
}



if (isset($_POST['cancel_reservation'])) {
    // Prevent any output buffering issues
    ob_clean();
    
    // Set proper JSON headers
    header('Content-Type: application/json');
    
    if (!isset($_POST['reservation_id'])) {
        echo json_encode([
            'success' => false, 
            'error' => 'reservation_id was not set'
        ]);
        exit();
    }
    
    $reservation_id = $_POST['reservation_id'];
    
    // Log the incoming data
    error_log("Processing cancellation for reservation_id: " . $reservation_id);
    
    $update_query = "UPDATE reservations SET status = 'cancelled' WHERE reservation_id = ? AND user_id = ?";
    try {
        $update_stmt = $conn->prepare($update_query);
        if (!$update_stmt) {
            throw new Exception("Failed to prepare update statement: " . $conn->error);
        }

        $update_stmt->bind_param("ii", $reservation_id, $user_id);
        $update_stmt->execute();

        if ($update_stmt->affected_rows > 0) {
            $response = [
                'success' => true,
                'message' => 'Reservation cancelled successfully',
                'reservation_id' => $reservation_id
            ];
            error_log("Success response: " . json_encode($response));
            echo json_encode($response);
        } else {
            throw new Exception("No changes were made. The reservation might not exist or is already cancelled.");
        }
    } catch (Exception $e) {
        $error_response = [
            'success' => false, 
            'error' => "Error cancelling reservation: " . $e->getMessage()
        ];
        error_log("Error response: " . json_encode($error_response));
        echo json_encode($error_response);
    }
    
    exit();
}


// fetching reservation history

$query = "SELECT r.reservation_id, r.viewing_date, r.viewing_time, r.created_at, 
                 u.unit_no, u.unit_type, u.monthly_rent, u.square_meter, r.status
          FROM reservations r
          JOIN property u ON r.unit_id = u.unit_id
          WHERE r.user_id = ? AND r.archived = 0 
          ORDER BY r.created_at DESC";

    try {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $conn->error);
            echo json_encode(['success' => false, 'error' => "Failed to prepare statement: " . $conn->error]);
            exit();
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $reservations = $result->fetch_all(MYSQLI_ASSOC);

    } catch (Exception $e) {
            error_log("Error fetching reservations: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => "Error fetching reservations: " . $e->getMessage()]);
        exit();
    }



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation History</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>

    <style>
        .success-toast {
            border-radius: 4px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        }
        
        .error-toast {
            border-radius: 4px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        }

        /* Add loading state styles */
        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>

</head>
<body class="bg-gray-100">

    <!-- Include Navbar -->
    <?php include('navbar.php'); ?>

    <!-- Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main content for Reservation History -->
    <div class="p-2 sm:p-4 sm:ml-64"> <!-- Changed margin for sidebar -->
        <div class="mt-20"> <!-- Adjusted top margin -->
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-700 mb-4">Reservation History</h1>
            
            <!-- Search and Filter Section -->
            <div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:space-x-2">
                <!-- Status Filter -->
                <div class="relative w-full sm:w-auto">
                    <select id="status-filter" class="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 pr-8 outline-none appearance-none">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                    </select>
                    <span class="absolute inset-y-0 right-2 flex items-center pointer-events-none text-gray-500">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </div>

                <!-- Keyword Search -->
                <div class="relative w-full sm:w-1/4">
                    <input type="text" id="search-keyword" placeholder="Search..." class="w-full block p-2.5 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10">
                    <button class="absolute inset-y-0 right-0 flex items-center px-3 bg-blue-600 text-white rounded-r-lg">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <?php if (empty($reservations)) { ?>
                <div class="bg-white rounded-lg p-6 text-center">
                    <p class="text-gray-600">You have no reservations yet.</p>
                </div>
            <?php } else { ?>
                <div class="w-full overflow-hidden rounded-lg shadow-lg bg-white">
                    <div class="w-full overflow-x-auto">
                        <table class="w-full whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="px-3 py-3 text-xs font-semibold text-left text-gray-700 uppercase">ID</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-left text-gray-700 uppercase">Unit No.</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-left text-gray-700 uppercase">Type</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-left text-gray-700 uppercase">Monthly Rent</th>
                                    <th class="hidden md:table-cell px-3 py-3 text-xs font-semibold text-left text-gray-700 uppercase">Size</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-left text-gray-700 uppercase">Date</th>
                                    <th class="hidden sm:table-cell px-3 py-3 text-xs font-semibold text-left text-gray-700 uppercase">Time</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-left text-gray-700 uppercase">Status</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-left text-gray-700 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reservation-table-body" class="bg-white divide-y divide-gray-200">
                                <?php foreach ($reservations as $reservation) { ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200" data-reservation-id="<?= htmlspecialchars($reservation['reservation_id']); ?>">
                                        <td class="px-3 py-4 text-sm text-gray-900"><?= htmlspecialchars($reservation['reservation_id']); ?></td>
                                        <td class="px-3 py-4 text-sm text-gray-900"><?= htmlspecialchars($reservation['unit_no']); ?></td>
                                        <td class="px-3 py-4 text-sm text-gray-900"><?= htmlspecialchars($reservation['unit_type']); ?></td>
                                        <td class="px-3 py-4 text-sm text-gray-900">₱<?= number_format($reservation['monthly_rent'], 2); ?></td>
                                        <td class="hidden md:table-cell px-3 py-4 text-sm text-gray-900"><?= htmlspecialchars($reservation['square_meter']); ?> sqm</td>
                                        <td class="px-3 py-4 text-sm text-gray-900"><?= date("M d, Y", strtotime($reservation['viewing_date'])); ?></td>
                                        <td class="hidden sm:table-cell px-3 py-4 text-sm text-gray-900"><?= date("h:i A", strtotime($reservation['viewing_time'])); ?></td>
                                        <td class="px-3 py-4 text-sm">
                                            <?php
                                            $statusClass = '';
                                            $statusBg = '';
                                            switch(strtolower($reservation['status'])) {
                                                case 'pending':
                                                    $statusClass = 'text-blue-700 bg-blue-100 border-blue-500';
                                                    break;
                                                case 'confirmed':
                                                    $statusClass = 'text-green-700 bg-green-100 border-green-500';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'text-red-700 bg-red-100 border-red-500';
                                                    break;
                                                case 'completed':
                                                    $statusClass = 'text-purple-700 bg-purple-100 border-purple-500';
                                                    break;
                                            }
                                            ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                                <?= htmlspecialchars($reservation['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 text-sm">
                                            <div class="flex items-center space-x-2">
                                                <?php if (strtolower($reservation['status']) !== 'cancelled') { ?>
                                                    <button class="cancel-btn inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200" data-id="<?= htmlspecialchars($reservation['reservation_id']); ?>" data-unit="<?= htmlspecialchars($reservation['unit_no']); ?>" data-status="<?= htmlspecialchars($reservation['status']); ?>">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        Cancel
                                                    </button>
                                                <?php } ?>
                                                <?php if (strtolower($reservation['status']) === 'completed') { ?>
                                                    <button class="archive-btn inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200" data-id="<?= htmlspecialchars($reservation['reservation_id']); ?>">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                        </svg>
                                                        Archive
                                                    </button>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Modal for Cancel Reservation -->
    <div id="cancelModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-lg mx-4 sm:mx-auto">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Confirm Cancellation</h2>
            <p class="text-gray-700 mb-8">Are you sure you want to cancel this reservation?</p>
            <form id="cancelReservationForm" method="POST" action="" class="flex flex-col sm:flex-row justify-end gap-4">
                <input type="hidden" name="reservation_id" id="reservation_id">
                <input type="hidden" name="cancel_reservation" value="1">
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 transition duration-200">Cancel Reservation</button>
                <button id="closeModal" type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition duration-200">Close</button>
            </form>
        </div>
    </div>


    <script>
        document.querySelectorAll('.cancel-btn').forEach(button => {
            button.addEventListener('click', function() {
                const reservationId = this.getAttribute('data-id');
                const unitNo = this.getAttribute('data-unit');
                const status = this.getAttribute('data-status');

                if (status === 'cancelled') {
                    alert('This reservation is already cancelled.');
                    return;
                }

                document.getElementById('reservation_id').value = reservationId;
                document.getElementById('cancelModal').classList.remove('hidden');
            });
        });
        
         document.querySelectorAll('.archive-btn').forEach(button => {
            button.addEventListener('click', function() {
                const reservationId = this.getAttribute('data-id');
              
              //You can add you archive php code here
                 alert(`Archiving reservation with ID: ${reservationId}`);
            });
        });
        

        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('cancelModal').classList.add('hidden');
        });

        document.getElementById('cancelReservationForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const form = new FormData(this);
        const cancelButton = this.querySelector('button[type="submit"]');
        const closeButton = document.getElementById('closeModal');
        
        // Disable buttons and show loading state
        cancelButton.disabled = true;
        closeButton.disabled = true;
        cancelButton.innerHTML = 'Cancelling...';

        fetch(window.location.href, {
            method: 'POST',
            body: form,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json().catch(err => {
                throw new Error('Invalid JSON response from server');
            });
        })
        .then(data => {
            if (data.success) {
                Toastify({
                    text: data.message || "Reservation Cancelled Successfully!",
                    backgroundColor: "#4CAF50",
                    className: "success-toast",
                    position: "right",
                    duration: 3000,
                    close: true
                }).showToast();

                // Hide modal
                document.getElementById('cancelModal').classList.add('hidden');
                
                // Reload page after showing toast
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                throw new Error(data.error || 'Failed to cancel reservation');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toastify({
                text: error.message || "Error cancelling the reservation!",
                backgroundColor: "#FF5252",
                className: "error-toast",
                position: "right",
                duration: 4000,
                close: true
            }).showToast();
        })
        .finally(() => {
            // Re-enable buttons and restore original text
            cancelButton.disabled = false;
            closeButton.disabled = false;
            cancelButton.innerHTML = 'Cancel Reservation';
        });
    });
    </script>

    <script>

        // Function to update status cell colors
        function updateStatusColors() {}

        // Function to filter and search reservations
        function filterReservations() {
            const statusFilter = document.getElementById('status-filter').value.toLowerCase();
            const searchKeyword = document.getElementById('search-keyword').value.toLowerCase();
            const tbody = document.getElementById('reservation-table-body');
            const rows = tbody.getElementsByTagName('tr');

            for (let row of rows) {
                let showRow = true;
                const statusCell = row.querySelector('.status-cell');
                const status = statusCell.textContent.toLowerCase();
                
                // Check status filter
                if (statusFilter && status !== statusFilter) {
                    showRow = false;
                }

                // Check search keyword
                if (searchKeyword) {
                    let found = false;
                    const cells = row.getElementsByTagName('td');
                    for (let cell of cells) {
                        if (cell.textContent.toLowerCase().includes(searchKeyword)) {
                            found = true;
                            break;
                        }
                    }
                    if (!found) {
                        showRow = false;
                    }
                }

                row.style.display = showRow ? '' : 'none';
            }
        }

        // archive reservation

        function archiveReservation(reservationId) {
        const formData = new FormData();
        formData.append('archive_reservation', '1');
        formData.append('reservation_id', reservationId);

        fetch('archive_reservation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // First check if the response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            // Try to parse the JSON, if it fails, throw an error with the text
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON:', text);
                    throw new Error('Invalid server response');
                }
            });
        })
        .then(data => {
            if (data.success) {
                Toastify({
                    text: data.message || "Reservation archived successfully!",
                    backgroundColor: "#4CAF50",
                    className: "success-toast",
                    position: "right",
                    duration: 3000,
                    close: true
                }).showToast();

                // Remove the row from the table
                const row = document.querySelector(`tr[data-reservation-id="${reservationId}"]`);
                if (row) {
                    row.remove();
                }
            } else {
                throw new Error(data.error || 'Failed to archive reservation');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toastify({
                text: error.message || "Error archiving the reservation!",
                backgroundColor: "#FF5252",
                className: "error-toast",
                position: "right",
                duration: 4000,
                close: true
            }).showToast();
        });
    }

        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Initial status color update
            updateStatusColors();

            // Add event listeners for search and filter
            document.getElementById('status-filter').addEventListener('change', filterReservations);
            document.getElementById('search-keyword').addEventListener('input', filterReservations);

            // Add event listeners for archive buttons
            document.querySelectorAll('.archive-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const reservationId = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to archive this reservation?')) {
                        archiveReservation(reservationId);
                    }
                });
            });
        });
    </script>

</body>
</html>