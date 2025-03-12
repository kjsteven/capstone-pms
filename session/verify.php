<?php
require 'db.php';

$message = ''; // Initialize message variable

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prepare and bind SQL statement to check the token
    $stmt = $conn->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the user to set is_verified to 1
        $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1, token = NULL WHERE token = ?");
        $updateStmt->bind_param("s", $token);
        if ($updateStmt->execute()) {
            $message = "Email verified successfully! You can now log in."; // Set success message
        } else {
            $message = "Error updating verification status."; // Set error message
        }
        $updateStmt->close(); // Close update statement
    } else {
        $message = "Invalid verification token."; // Set error message
    }

    // Close initial statement
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified</title>

    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.js" defer></script>
    <style>
        /* Include Poppins font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        /* Apply Poppins font globally */
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <script>
        // Redirect to login.php after 5 seconds if verification is successful
        window.onload = function() {
            <?php if ($redirect): ?>
                setTimeout(function() {
                    window.location.href = '../authentication/login.php';
                }, 5000); // Redirect after 5 seconds
            <?php endif; ?>
        };
    </script>
</head>

<body class="min-h-screen bg-cover bg-center flex items-center justify-center py-6 px-4" style="background-image: url('../images/bg3.jpg');">
    <div class="max-w-md w-full" style="background-color: #1f2937; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
        <div class="text-center mb-4">
            <i class='bx bx-check-circle text-green-500 text-4xl'></i> <!-- Check icon -->
        </div>
        <h2 class="text-white text-center text-2xl font-bold mb-4">Verification Status</h2>
        <p class="text-white text-center text-lg mb-6"><?php echo $message; ?></p>
        <div class="text-center">
            <a href="../authentication/login.php" class="inline-block px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition duration-200">
                Click here to login
            </a>
        </div>
    </div>
</body>

</html>
