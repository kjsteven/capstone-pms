<?php
session_start();
require 'db.php'; // Your database connection

// Check if reset token is provided
if (!isset($_GET['resettoken'])) {
    $_SESSION['error'] = 'Invalid or missing reset token.';
    header('Location: ../authentication/login.php'); // Redirect to login
    exit;
}

// Get the reset token from the URL
$resettoken = $_GET['resettoken'];

// Query to verify if the reset token is valid
$stmt = $conn->prepare("SELECT user_id FROM users WHERE ResetToken = ?");
$stmt->bind_param("s", $resettoken);
$stmt->execute();
$result = $stmt->get_result();

// If no matching token found, show an error
if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Invalid or expired reset token.';
    header('Location: ../authentication/login.php'); // Redirect to login
    exit;
}

// If token is valid, retrieve the user_id
$user = $result->fetch_assoc();
$userId = $user['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    // Validate passwords
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: resetpassword.php?resettoken=' . $resettoken); // Retain token in URL
        exit;
    }

    // Check for password strength (12 chars, letters, numbers, special chars, uppercase and lowercase)
    if (strlen($newPassword) < 12 ||
        !preg_match('/[A-Z]/', $newPassword) ||  // Must include an uppercase letter
        !preg_match('/[a-z]/', $newPassword) ||  // Must include a lowercase letter
        !preg_match('/[0-9]/', $newPassword) ||  // Must include a number
        !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)  // Must include a special character
    ) {
        $_SESSION['error'] = 'Password must be at least 12 characters long, include uppercase, lowercase, numbers, and special characters.';
        header('Location: resetpassword.php?resettoken=' . $resettoken); // Retain token in URL
        exit;
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database and clear the ResetToken
    $stmt = $conn->prepare("UPDATE users SET password = ?, ResetToken = NULL WHERE user_id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Password reset successfully.';
        // Do not redirect here, just reload the page to show success message
    } else {
        $_SESSION['error'] = 'Error updating password. Please try again.';
        header('Location: resetpassword.php?resettoken=' . $resettoken); // Retain token in URL
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        a {
            color: #1d4ed8; /* Tailwind's blue-600 */
        }
        
        a:hover {
            color: #2563eb; /* Tailwind's blue-700 */
        }
        
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen p-6">

<section class="flex items-center justify-center min-h-screen">
  <div class="w-full max-w-md px-4 sm:px-6 p-6 bg-white rounded-lg shadow dark:border md:mt-0 dark:bg-gray-800 dark:border-gray-700 sm:p-8">
      <h2 class="mb-1 text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
          Change Password
      </h2>

      <?php if (isset($_SESSION['error'])): ?>
          <div class="bg-red-500 text-white p-4 mb-4 rounded-lg">
              <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
          </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['success'])): ?>
          <div class="text-green-600 mb-4">
              <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
          </div>
      <?php endif; ?>

      <form class="mt-4 space-y-4 lg:mt-5 md:space-y-5" action="resetpassword.php?resettoken=<?php echo $resettoken; ?>" method="POST">
      <div>
        <label class="text-gray-900 dark:text-white text-sm mb-2 block">New Password</label>
                <div class="relative flex items-center">
                    <input id="password" name="password" type="password" required class="w-full text-white dark:bg-gray-700 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600 placeholder-gray-400" placeholder="Enter password" />
                    <button type="button" onclick="togglePassword('password', 'togglePasswordIcon')" class="absolute inset-y-0 right-4 flex items-center">
                        <i id="togglePasswordIcon" class='bx bxs-show w-4 h-4 text-gray-400'></i>
                    </button>
                </div>
                <div id="password-strength" class="mt-2 text-sm text-blue-600"></div>
            </div>

            <div>
                <label class="text-gray-900 dark:text-white text-sm mb-2 block">Confirm Password</label>
                <div class="relative flex items-center">
                    <input id="confirm-password" name="confirm-password" type="password" required class="w-full text-white dark:bg-gray-700 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600 placeholder-gray-400" placeholder="Enter password" />
                    <button type="button" onclick="togglePassword('confirm-password', 'toggleConfirmPasswordIcon')" class="absolute inset-y-0 right-4 flex items-center">
                        <i id="toggleConfirmPasswordIcon" class='bx bxs-show w-4 h-4 text-gray-400'></i>
                    </button>
                </div>
            </div>

          <button id="submit-button" type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Reset Password</button>

          <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
          Go Back to <a href="../authentication/login.php" class="font-medium text-blue-600 hover:underline dark:text-blue-500">Login</a>
          </p>
      </form>
  </div>
</section>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const eyeIcon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.remove('bxs-show');
        eyeIcon.classList.add('bxs-hide');
    } else {
        input.type = 'password';
        eyeIcon.classList.remove('bxs-hide');
        eyeIcon.classList.add('bxs-show');
    }
}

// Optional: Add password strength validation logic
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthText = document.getElementById('password-strength');
    const strength = checkPasswordStrength(password);
    strengthText.textContent = strength;
});

function checkPasswordStrength(password) {
    let strength = '';
    if (password.length < 12) {
        strength = 'Too short';
    } else if (!/[A-Z]/.test(password)) {
        strength = 'Add at least one uppercase letter'; 
    } else if (!/[a-z]/.test(password)) {
        strength = 'Add at least one lowercase letter';
    } else if (!/[0-9]/.test(password)) {
        strength = 'Add at least one number';
    } else if (!/[!@#$%^&*]/.test(password)) {
        strength = 'Add at least one special character';
    } else {
        strength = 'Strong password!';
    }
    return strength;
}
</script>

</body>
</html>
