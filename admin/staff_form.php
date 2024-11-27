

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <title>Staff Form</title>
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
<?php include('navbarAdmin.php'); ?>

<!-- Include Sidebar -->
<?php include('sidebarAdmin.php'); ?>

<div class="container mx-auto p-6">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg w-full mx-auto mt-20 border relative">
        <!-- Close Icon -->
        <div class="absolute top-4 right-4">
            <i data-feather="x" class="close-icon w-6 h-6 text-gray-500 hover:text-gray-700"></i>
        </div>
        
        <h3 class="text-xl font-semibold mb-4">Add Staff Account</h3>
        <form id="staff-form" class="space-y-4">
            <div>
                <label for="staff-name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" id="staff-name" name="staff-name" class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="staff-email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="staff-email" name="staff-email" class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" required>
            </div>

            <div>
                <label for="staff-specialty" class="block text-sm font-medium text-gray-700">Specialty</label>
                <select id="staff-specialty" name="staff-specialty" class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" required>
                    <option value="">Select a specialty</option>
                    <option value="general-maintenance">General Maintenance</option>
                    <option value="electrical-specialist">Electrical Specialist</option>
                    <option value="plumbing-specialist">Plumbing Specialist</option>
                    <option value="hvac-technician">HVAC Technician</option>
                    <option value="pest-control-specialist">Pest Control Specialist</option>
                    <option value="security-systems-technician">Security Systems Technician</option>
                </select>
            </div>

            <div>
                <label for="staff-phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="text" id="staff-phone" name="staff-phone" class="mt-1 block w-full border-2 border-gray-300 rounded-md p-2 focus:border-blue-500 focus:outline-none" required pattern="[0-9]{11}" title="Phone number must be 11 digits">
            </div>

            <div class="mt-4 flex justify-between">
                <button type="reset" class="px-4 py-2 bg-gray-400 text-white rounded-md">Reset</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Add Staff</button>
            </div>
        </form>
    </div>
</div>

<script src="../node_modules/feather-icons/dist/feather.min.js"></script>

<script>
    // Initialize Feather Icons
    feather.replace();
</script>


<script>
    // Initialize Feather Icons
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
        
        // Add click event for close icon
        document.querySelector('.close-icon').addEventListener('click', function() {
            window.location.href = 'manageUsers.php';
        });
    });
</script>


<script>
      
      document.addEventListener('DOMContentLoaded', function() {
    const staffForm = document.getElementById('staff-form');
    const staffPhone = document.getElementById('staff-phone');
    const staffName = document.getElementById('staff-name');

    // Add input event listener for name field to show uppercase while typing
    if (staffName) {
        staffName.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    if (staffPhone) {
        staffPhone.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
        });
    }

    if (staffForm) {
        staffForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const staffName = document.getElementById('staff-name').value.trim().toUpperCase();
            const staffEmail = document.getElementById('staff-email').value.trim();
            const staffSpecialty = document.getElementById('staff-specialty').value;
            const staffPhone = document.getElementById('staff-phone').value.trim();

            // Form validation
            if (!staffName || !staffEmail || !staffSpecialty || !staffPhone) {
                showToast('Please fill out all fields.', 'red');
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(staffEmail)) {
                showToast('Please enter a valid email address.', 'red');
                return;
            }

            if (!/^\d{11}$/.test(staffPhone)) {
                showToast('Phone number must be exactly 11 digits.', 'red');
                return;
            }

            // Show loading state
            showToast('Adding staff member...', 'blue');

            try {
                const formData = new FormData();
                formData.append('name', staffName);
                formData.append('email', staffEmail);
                formData.append('specialty', formatSpecialty(staffSpecialty));
                formData.append('phone', staffPhone);

                // Log the form data being sent
                console.log('Sending form data:', Object.fromEntries(formData));

                const response = await fetch('add_staff.php', {
                    method: 'POST',
                    body: formData
                });

                // Log the raw response
                const responseText = await response.text();
                console.log('Raw server response:', responseText);

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response that failed to parse:', responseText);
                    throw new Error('Server response was not valid JSON. Check the console for details.');
                }

                if (data.success) {
                    showToast(data.message, 'green');
                    staffForm.reset();
                    
                    // Add delay before redirect
                    setTimeout(() => {
                        // Show redirect notification
                        showToast('Redirecting to Manage Users...', 'blue');
                        
                        // Add another small delay for the redirect notification to be visible
                        setTimeout(() => {
                            window.location.href = 'manageUsers.php';
                        }, 1000);
                    }, 1000);
                } else {
                    showToast(data.message || 'Error adding staff member', 'red');
                }
            } catch (error) {
                console.error('Detailed error:', error);
                showToast(`Error: ${error.message}`, 'red');
            }
        });
    }
});

// Utility function to format specialty
function formatSpecialty(specialty) {
    return specialty
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

// Enhanced toast function
function showToast(message, backgroundColor) {
    Toastify({
        text: message,
        backgroundColor: backgroundColor,
        duration: 3000,
        close: true,
        gravity: 'top',
        position: 'right',
        stopOnFocus: true
    }).showToast();
}
 
</script>

</body>
</html>
