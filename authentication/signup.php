<?php
session_start(); // Ensure session is started

require '../session/db.php';
require '../vendor/autoload.php'; // Load Composer's autoloader
require '../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$error = '';
$success = '';

// Validate email format
function isValidEmail($email) {
    $regex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    return preg_match($regex, $email);
}

// Validate phone format (simple example)
function isValidPhone($phone) {
    return preg_match('/^\+?[0-9]{10,}$/', $phone);
}

// Validate password
function isValidPassword($password) {
    $errors = [];
    if (strlen($password) < 12) {
        $errors[] = "Password must be at least 12 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    if (!preg_match('/[@$!%*?&]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }
    return $errors;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form input data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $termsAccepted = isset($_POST['termsAccepted']); // Check if terms checkbox is checked

    // Validate form fields
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif (!isValidEmail($email)) {
        $error = "Invalid email format.";
    } elseif (!isValidPhone($phone)) {
        $error = "Invalid phone number format.";
    } else {
        $passwordErrors = isValidPassword($password);
        if (count($passwordErrors) > 0) {
            $error = implode(' ', $passwordErrors);
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } elseif (!$termsAccepted) {
            $error = "You must accept the terms and conditions.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Generate a unique verification token
            $verificationToken = bin2hex(random_bytes(16));

            // Check if email already exists
            $checkEmailStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $checkEmailStmt->bind_param("s", $email);
            $checkEmailStmt->execute();
            $checkEmailResult = $checkEmailStmt->get_result();

            if ($checkEmailResult->num_rows > 0) {
                $error = "This email is already registered.";
            } else {
                // Prepare and bind SQL statement to prevent SQL injection
                $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, token, role) VALUES (?, ?, ?, ?, ?, ?)");
                $defaultRole = 'user'; // Set default role for new users
                $stmt->bind_param("ssssss", $name, $email, $phone, $hashedPassword, $verificationToken, $defaultRole);

                // Execute the insert query
                if ($stmt->execute()) {
                    // Send verification email
                    if (sendVerificationEmail($email, $verificationToken, $name)) {
                        // Set success message in session
                        $_SESSION['success'] = "Check your email for verification link.";
                        // Redirect to the signup page to prevent form resubmission
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $error = "Error sending verification email.";
                    }
                } else {
                    $error = "Error saving data. Please try again.";
                }
                // Close the prepared statement
                $stmt->close();
            }
            // Close the email check statement
            $checkEmailStmt->close();
        }
    }
}

// Function to send verification email
function sendVerificationEmail($email, $token, $name) {
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
        $mail->setFrom(SMTP_USERNAME, 'PropertyWise | Email Account Verification');
        $mail->addAddress($email); // Use the email address passed in

        // Email content
        $verificationLink = 'https://localhost/capstone-pms/session/verify.php?token=' . $token;
        $mail->Subject = 'Email Verification';
        $mail->Body = 'Hi ' . $name . ',' . "\n\n" .
                      'We just need to verify your email address before you can access our website.' . "\n\n" .
                      'To verify your email, please click this link: (' . $verificationLink . ').' . "\n\n" .
                      'Thanks! - The PropertyWise Team';

        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false; // Return false if email sending fails
    }
}

// Display success or error message
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']); // Clear message after displaying
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <style>
        /* Include Poppins font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        /* Apply Poppins font globally */
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>

    <div class="min-h-screen bg-cover bg-center flex items-center justify-center py-6 px-4" style="background-image: url('../images/bg3.jpg');">
        <div class="max-w-md w-full" style="background-color: #1f2937; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
            <h2 class="text-white text-center text-2xl font-bold">Sign up</h2>
            <form method="POST" class="mt-8 space-y-4">

                <div>
                    <label class="text-white text-sm mb-2 block">Full Name</label>
                    <div class="relative flex items-center">
                        <input name="name" type="text" id="name" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter full name" />
                    </div>
                </div>

                <div>
                    <label class="text-white text-sm mb-2 block">Email</label>
                    <div class="relative flex items-center">
                        <input name="email" type="email" id="email" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter email" />
                    </div>
                </div>

                <div>
                    <label class="text-white text-sm mb-2 block">Phone Number</label>
                    <div class="relative flex items-center">
                        <input name="phone" type="tel" id="phone" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter phone number" />
                    </div>
                </div>

                <div>
                    <label class="text-white text-sm mb-2 block">Password</label>
                    <div class="relative flex items-center">
                        <input type="password" id="password" name="password" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter password" />
                        <button type="button" class="absolute right-2" onclick="togglePassword('password', 'togglePasswordIcon')">
                            <i id="togglePasswordIcon" class='bx bxs-show w-4 h-4 text-gray-400'></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="text-white text-sm mb-2 block">Confirm Password</label>
                    <div class="relative flex items-center">
                        <input type="password" id="confirmPassword" name="confirmPassword" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Confirm password" />
                        <button type="button" class="absolute right-2" onclick="togglePassword('confirmPassword', 'toggleConfirmPasswordIcon')">
                            <i id="toggleConfirmPasswordIcon" class='bx bxs-show w-4 h-4 text-gray-400'></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center mb-4">
                    <input type="checkbox" id="termsAccepted" name="termsAccepted" required class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="termsAccepted" class="text-white text-sm ml-2">
                        I accept the 
                        <a href="terms-and-conditions.html" class="text-blue-500 hover:text-blue-700">terms and conditions</a>
                    </label>
                </div>      


                <?php if (!empty($error)): ?>
                    <div class="text-red-500 text-sm mt-2"><?= $error; ?></div>
                <?php elseif (!empty($success)): ?>
                    <div class="text-green-500 text-sm mt-2"><?= $success; ?></div>
                <?php endif; ?>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-md">Sign Up</button>

                <p class="mt-4 text-sm text-center text-white">
                    Already have an account? <a href="login.php" class="text-blue-600 hover:underline ml-1 whitespace-nowrap font-semibold">Login here</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        // Function to toggle password visibility
        function togglePassword(passwordFieldId, iconId) {
            const passwordField = document.getElementById(passwordFieldId);
            const icon = document.getElementById(iconId);
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove('bxs-show');
                icon.classList.add('bxs-hide');
            } else {
                passwordField.type = "password";
                icon.classList.remove('bxs-hide');
                icon.classList.add('bxs-show');
            }
        }



    </script>
</body>
</html>
