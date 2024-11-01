<?php
require '../session/db.php'; 
require '../vendor/autoload.php'; 
require '../config/config.php';

session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$error = ''; // Initialize the error variable
$maxAttempts = 5;
$lockoutTime = 30 * 60; // 30 minutes in seconds

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }


    // Prepare and execute the SQL statement with lockout check
    $stmt = $conn->prepare(
        "SELECT user_id, password, email, is_verified, otp_used, login_attempts, 
        TIMESTAMPDIFF(SECOND, last_attempt, NOW()) AS time_since_last_attempt
        FROM users 
        WHERE email = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $hashedPassword, $email, $isVerified, $otpUsed, $loginAttempts, $timeSinceLastAttempt);
        $stmt->fetch();

        // Lockout check directly from SQL result
        if ($loginAttempts >= $maxAttempts && $timeSinceLastAttempt < $lockoutTime) {
            $remainingTime = ceil(($lockoutTime - $timeSinceLastAttempt) / 60);
            $_SESSION['error_message'] = "Too many login attempts. Please try again in $remainingTime minutes.";
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
            exit;
        } elseif ($loginAttempts >= $maxAttempts && $timeSinceLastAttempt >= $lockoutTime) {
            // Reset login attempts if lockout period has expired
            $loginAttempts = 0;
            $updateAttemptsStmt = $conn->prepare("UPDATE users SET login_attempts = ?, last_attempt = NOW() WHERE user_id = ?");
            $updateAttemptsStmt->bind_param("ii", $loginAttempts, $userId);
            $updateAttemptsStmt->execute();
            $updateAttemptsStmt->close();
        }

        // Verify password
        if (password_verify($password, $hashedPassword)) {
            if ($isVerified) {
                // Successful login, reset login attempts and redirect
                $resetStmt = $conn->prepare("UPDATE users SET login_attempts = 0, last_attempt = NOW() WHERE user_id = ?");
                $resetStmt->bind_param("i", $userId);
                $resetStmt->execute();
                $resetStmt->close();

                // Redirect user based on role
                $_SESSION['user_id'] = $userId;
                $_SESSION["role"] = getUserRole($userId);
                if ($_SESSION["role"] == "Admin") {
                    header("Location: ../admin/dashboardAdmin.php");
                } else {
                    header("Location: ../users/dashboard.php");
                }
                exit;
            } else {
                $_SESSION['error_message'] = "You must verify your email before logging in.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        } else {
            // Increment login attempts only if password verification fails
            $loginAttempts++;
            $updateStmt = $conn->prepare("UPDATE users SET login_attempts = ?, last_attempt = NOW() WHERE user_id = ?");
            $updateStmt->bind_param("ii", $loginAttempts, $userId);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['error_message'] = "Wrong Password or Email. Attempt $loginAttempts of $maxAttempts.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
        $_SESSION['error_message'] = "User not found.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $stmt->close();
}

// Retrieve error message from session (if exists)
if (isset($_SESSION['error_message'])) {
    $error = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the error message after displaying
}

// Function to retrieve user role
function getUserRole($userId) {
    global $conn; // Use the existing database connection
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($role);
    $stmt->fetch();
    $stmt->close();

    return $role;
}

// Function to send OTP email (if necessary)
function sendOtpEmail($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = SMTP_USERNAME; // Use the constant
        $mail->Password = SMTP_PASSWORD; // Use the constant
        $mail->setFrom(SMTP_USERNAME, 'RentEase | OTP Verification');
        $mail->addAddress($email);

        // Email content
        $mail->Subject = 'Your OTP Code';
        $mail->Body = 'Your OTP code is: ' . $otp;

        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        /* Apply Poppins font globally */
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body>
    <div class="min-h-screen bg-cover bg-center flex items-center justify-center py-6 px-4" style="background-image: url('../images/bg3.jpg');">
        <div class="max-w-md w-full" style="background-color: #1f2937; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
            <h2 class="text-white text-center text-2xl font-bold">Sign in</h2>
            <form method="POST" class="mt-8 space-y-4">
                <div>
                    <label class="text-white text-sm mb-2 block">Email</label>
                    <div class="relative flex items-center">
                        <input name="username" type="text" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter Email" />
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" class="w-4 h-4 absolute right-4" viewBox="0 0 24 24">
                            <circle cx="10" cy="7" r="6" data-original="#000000"></circle>
                            <path d="M14 15H6a5 5 0 0 0-5 5 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 5 5 0 0 0-5-5zm8-4h-2.59l.3-.29a1 1 0 0 0-1.42-1.42l-2 2a1 1 0 0 0 0 1.42l2 2a1 1 0 0 0 1.42 0 1 1 0 0 0 0-1.42l-.3-.29H22a1 1 0 0 0 0-2z" data-original="#000000"></path>
                        </svg>
                    </div>
                </div>

                <div>
                    <label class="text-white text-sm mb-2 block">Password</label>
                    <div class="relative flex items-center">
                        <input id="password" name="password" type="password" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter password" />
                        <button type="button" onclick="togglePassword('password', 'togglePasswordIcon')" class="absolute inset-y-0 right-4 flex items-center">
                            <i id="togglePasswordIcon" class='bx bxs-show w-4 h-4 text-gray-400'></i>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 shrink-0 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                        <label for="remember-me" class="ml-3 block text-sm text-white">Remember me</label>
                    </div>
                    <div class="text-sm">
                        <a href="../session/forgotpassword.php" class="text-blue-600 hover:underline font-semibold">Forgot your password?</a>
                    </div>
                </div>

                <div class="!mt-8">
                    <button type="submit" class="w-full py-3 px-4 text-sm tracking-wide rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">Sign in</button>
                </div>

                <p class="text-white text-sm !mt-8 text-center">Don't have an account? <a href="signup.php" class="text-blue-600 hover:underline ml-1 whitespace-nowrap font-semibold">Register here</a></p>
            </form>

            <!-- Error message handling -->
            <?php if (!empty($error)): ?>
                <div class="text-red-500 text-sm mt-4 text-center"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <script defer>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("bxs-show");
                toggleIcon.classList.add("bxs-hide");
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("bxs-hide");
                toggleIcon.classList.add("bxs-show");
            }
        }
    </script>
</body>
</html>
