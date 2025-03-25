<?php
session_start(); // Ensure session is started

require '../session/db.php';
require '../vendor/autoload.php'; // Load Composer's autoloader
require '../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

// Security headers
header("X-Content-Type-Options: nosniff"); // Prevent MIME-type sniffing
header("X-Frame-Options: DENY"); // Prevent clickjacking
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'self';"); // Prevent XSS & Base Tag Injection
header("Referrer-Policy: strict-origin-when-cross-origin"); // More secure referrer policy

// Only add this if your site runs on HTTPS
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Strict-Transport-Security: max-age=31536000; preload"); 


$error = '';
$success = '';

// Validate email format
function isValidEmail($email) {
    $regex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    return preg_match($regex, $email);
}

// Validate phone format for Philippine numbers
function isValidPhone($phone) {
    // Philippine formats: +639XXXXXXXXX, 09XXXXXXXXX
    // Remove any spaces or dashes for validation
    $phone = preg_replace('/[\s-]/', '', $phone);
    
    // Check for format +639XXXXXXXXX (13 digits with +)
    if (preg_match('/^\+639\d{9}$/', $phone)) {
        return true;
    }
    
    // Check for format 09XXXXXXXXX (11 digits)
    if (preg_match('/^09\d{9}$/', $phone)) {
        return true;
    }
    
    return false;
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

// Validate name format - must have first and last name
function isValidName($name) {
    // Remove extra spaces and split the name
    $nameParts = preg_split('/\s+/', trim($name));
    
    // Check if we have at least two parts (first and last name)
    return count($nameParts) >= 2;
}

// Add variable to track successful registration
$registrationSuccess = false;
$redirectDelay = 5; // Seconds to delay before redirect

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form input data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']); // Add address field
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $termsAccepted = isset($_POST['termsAccepted']); // Check if terms checkbox is checked

    // Validate form fields
    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif (!isValidName($name)) {
        $error = "Please enter both your first and last name.";
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
                $stmt = $conn->prepare("INSERT INTO users (name, email, phone, address, password, token, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $defaultRole = 'user'; // Set default role for new users
                $stmt->bind_param("sssssss", $name, $email, $phone, $address, $hashedPassword, $verificationToken, $defaultRole);

                // Execute the insert query
                if ($stmt->execute()) {
                    // Send verification email only
                    if (sendVerificationEmail($email, $verificationToken, $name)) {
                        // Set success message and flag instead of immediate redirect
                        $success = "Registration successful! Please check your email to verify your account. Redirecting to login page in {$redirectDelay} seconds...";
                        $registrationSuccess = true;
                        // Don't redirect immediately
                        // header("Location: login.php");
                        // exit();
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
        // $verificationLink = 'https://localhost/capstone-pms/session/verify.php?token=' . $token;

        $verificationLink = 'https://propertywise.site/session/verify.php?token=' . $token;
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
        
        /* Custom input width style */
        .form-input-container {
            width: 90%;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Modal styles */
        .modal {
            visibility: hidden;
            opacity: 0;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.7);
            transition: all 0.4s;
            z-index: 9999;
        }

        .modal.show {
            visibility: visible;
            opacity: 1;
        }

        .modal-content {
            border-radius: 8px;
            max-width: 90%;
            max-height: 90%;
            width: 800px;
            background: white;
            position: relative;
            transform: scale(0.8);
            transition: all 0.4s;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal.show .modal-content {
            transform: scale(1);
        }

        .modal-body {
            overflow-y: auto;
            padding: 1.5rem;
            flex: 1;
        }

        .modal-tabs {
            display: flex;
            border-bottom: 2px solid #e2e8f0;
        }

        .modal-tab {
            padding: 1rem 1.5rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .modal-tab.active {
            color: #3b82f6;
            border-bottom: 3px solid #3b82f6;
            margin-bottom: -2px;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.5s;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Input validation styles */
        .valid-input {
            border-color: #10b981 !important;
        }
        
        .invalid-input {
            border-color: #ef4444 !important;
        }
        
        .validation-message {
            font-size: 0.75rem;
            margin-top: 0.25rem;
            min-height: 1rem;
        }
        
        .validation-success {
            color: #10b981;
        }
        
        .validation-error {
            color: #ef4444;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen p-6">

    <div class="min-h-screen bg-cover bg-center flex items-center justify-center py-6 px-4">
        <div class="w-full max-w-6xl mx-auto">
            <div class="flex flex-col md:flex-row rounded-2xl overflow-hidden shadow-2xl">
                <!-- GIF Section (Now on Left) -->
                <div class="w-full md:w-1/2 bg-gray-800 flex items-center justify-center">
                    <div class="h-full w-full p-6 flex items-center justify-center">
                        <img src="../images/signup.gif" alt="Signup Animation" class="rounded-xl max-w-full max-h-full object-cover">
                    </div>
                </div>
                
                <!-- Form Section (Now on Right) -->
                <div class="w-full md:w-1/2" style="background-color: #1f2937; padding: 2rem;">
                    <h2 class="text-white text-center text-2xl font-bold">Sign up</h2>
                    <form method="POST" class="mt-8 space-y-4" id="signupForm">
                        <div class="form-input-container">
                            <label class="text-white text-sm mb-2 block">Full Name</label>
                            <div class="relative flex items-center">
                                <input name="name" type="text" id="name" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter full name" />
                            </div>
                            <div class="validation-message" id="nameValidation"></div>
                        </div>

                        <div class="form-input-container">
                            <label class="text-white text-sm mb-2 block">Email</label>
                            <div class="relative flex items-center">
                                <input name="email" type="email" id="email" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter email" />
                            </div>
                            <div class="validation-message" id="emailValidation"></div>
                        </div>

                        <div class="form-input-container">
                            <label class="text-white text-sm mb-2 block">Phone Number</label>
                            <div class="relative flex items-center">
                                <input name="phone" type="tel" id="phone" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter phone number" />
                            </div>
                            <div class="validation-message" id="phoneValidation"></div>
                        </div>

                        <!-- New Address Field -->
                        <div class="form-input-container">
                            <label class="text-white text-sm mb-2 block">Address</label>
                            <div class="relative flex items-center">
                                <input name="address" type="text" id="address" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter your full address" />
                            </div>
                            <div class="validation-message" id="addressValidation"></div>
                        </div>

                        <div class="form-input-container">
                            <label class="text-white text-sm mb-2 block">Password</label>
                            <div class="relative flex items-center">
                                <input type="password" id="password" name="password" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter password" />
                                <button type="button" class="absolute right-2" onclick="togglePassword('password', 'togglePasswordIcon')">
                                    <i id="togglePasswordIcon" class='bx bxs-show w-4 h-4 text-gray-400'></i>
                                </button>
                            </div>
                            <div class="validation-message" id="passwordValidation"></div>
                        </div>

                        <div class="form-input-container">
                            <label class="text-white text-sm mb-2 block">Confirm Password</label>
                            <div class="relative flex items-center">
                                <input type="password" id="confirmPassword" name="confirmPassword" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Confirm password" />
                                <button type="button" class="absolute right-2" onclick="togglePassword('confirmPassword', 'toggleConfirmPasswordIcon')">
                                    <i id="toggleConfirmPasswordIcon" class='bx bxs-show w-4 h-4 text-gray-400'></i>
                                </button>
                            </div>
                            <div class="validation-message" id="confirmPasswordValidation"></div>
                        </div>

                        <div class="form-input-container flex items-center mb-4">
                            <input type="checkbox" id="termsAccepted" name="termsAccepted" required class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="termsAccepted" class="text-white text-sm ml-2">
                                I accept the 
                                <a href="#" id="openTermsModal" class="text-blue-500 hover:text-blue-700">terms and conditions</a>
                            </label>
                        </div>
                        <div class="form-input-container">
                            <div class="validation-message" id="termsValidation"></div>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="form-input-container text-red-500 text-sm mt-2"><?= $error; ?></div>
                        <?php elseif (!empty($success)): ?>
                            <div class="form-input-container text-green-500 text-sm mt-2 p-3 bg-green-100 border border-green-300 rounded">
                                <?= $success; ?>
                                <div class="mt-2">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div id="progress-bar" class="bg-green-600 h-2.5 rounded-full" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-input-container">
                            <button type="submit" id="submitBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-md">Sign Up</button>
                        </div>

                        <div class="form-input-container">
                            <p class="mt-4 text-sm text-center text-white">
                                Already have an account? <a href="login.php" class="text-blue-600 hover:underline ml-1 whitespace-nowrap font-semibold">Login here</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="legalModal" class="modal">
        <div class="modal-content shadow-2xl">
            <!-- Close Button -->
            <div class="p-4 flex justify-end">
                <button id="closeModal" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Tabs -->
            <div class="modal-tabs">
                <div class="modal-tab active" data-tab="terms">Terms and Conditions</div>
                <div class="modal-tab" data-tab="privacy">Privacy Policy</div>
            </div>
            
            <!-- Modal Content -->
            <div class="modal-body">
                <!-- Terms and Conditions Content -->
                <div id="terms-content" class="tab-content active">
                    <h2 class="text-2xl font-semibold mb-4">1. Introduction</h2>
                    <p class="mb-4">Welcome to PropertyWise. By accessing and using our platform, you agree to comply with and be bound by the following terms and conditions. Please read them carefully before using our services.</p>

                    <h2 class="text-2xl font-semibold mb-4">2. Services</h2>
                    <p class="mb-4">PropertyWise provides property management services including, but not limited to, tenant management, lease management, maintenance requests, and payment processing. The use of these services is subject to the terms and conditions outlined here.</p>

                    <h2 class="text-2xl font-semibold mb-4">3. User Responsibilities</h2>
                    <p class="mb-4">As a user, you are responsible for maintaining the confidentiality of your account information, including your username and password. You agree to notify us immediately if there is any unauthorized use of your account.</p>

                    <h2 class="text-2xl font-semibold mb-4">4. Privacy and Data Protection</h2>
                    <p class="mb-4">Your privacy is important to us. Please review our Privacy Policy to understand how we collect, use, and protect your data.</p>

                    <h2 class="text-2xl font-semibold mb-4">5. Payments</h2>
                    <p class="mb-4">By using our platform, you agree to pay the applicable fees for the services provided. Payments will be processed through the designated payment gateway, and you agree to adhere to the payment terms outlined by the service provider.</p>

                    <h2 class="text-2xl font-semibold mb-4">6. Termination</h2>
                    <p class="mb-4">We reserve the right to suspend or terminate your account at any time if you violate these terms and conditions. Upon termination, you will no longer have access to our services, and you agree to cease using the platform immediately.</p>

                    <h2 class="text-2xl font-semibold mb-4">7. Liability</h2>
                    <p class="mb-4">PropertyWise is not responsible for any damages, losses, or liabilities arising from the use of our services, except as required by law. You use the platform at your own risk.</p>

                    <h2 class="text-2xl font-semibold mb-4">8. Changes to Terms</h2>
                    <p class="mb-4">We reserve the right to update or modify these terms at any time. Any changes will be posted on this page with the date of the latest update. Continued use of the platform after such changes constitutes your acceptance of the new terms.</p>

                    <h2 class="text-2xl font-semibold mb-4">9. Governing Law</h2>
                    <p class="mb-4">These terms and conditions shall be governed by and construed in accordance with the laws of the Philippines. Any disputes arising from these terms shall be resolved in the courts of the Philippines.</p>

                    <h2 class="text-2xl font-semibold mb-4">10. Contact Us</h2>
                    <p class="mb-4">If you have any questions or concerns regarding these terms, please contact us at <a href="mailto:support@propertywise.com" class="text-blue-600 hover:underline">support@propertywise.com</a>.</p>
                </div>
                
                <!-- Privacy Policy Content -->
                <div id="privacy-content" class="tab-content">
                    <h1 class="text-3xl font-bold mb-4">Privacy Policy</h1>

                    <p class="mb-4">
                        This privacy policy sets out how our website uses and protects any information that you give us when you use
                        this website.
                    </p>

                    <h2 class="text-2xl font-bold mb-2">Information We Collect</h2>

                    <p class="mb-4">
                        We may collect the following information:
                    </p>

                    <ul class="list-disc list-inside mb-4">
                        <li>Your name and contact information</li>
                        <li>Demographic information</li>
                        <li>Other information relevant to customer surveys and/or offers</li>
                    </ul>

                    <h2 class="text-2xl font-bold mb-2">How We Use the Information</h2>

                    <p class="mb-4">
                        We require this information to understand your needs and provide you with a better service, and in
                        particular for the following reasons:
                    </p>

                    <ul class="list-disc list-inside mb-4">
                        <li>Internal record keeping</li>
                        <li>Improving our products and services</li>
                        <li>Sending promotional emails about new products, special offers, or other information which we think you
                            may find interesting</li>
                        <li>From time to time, we may also use your information to contact you for market research purposes. We may
                            contact you by email, phone, or mail. We may use the information to customize the website according to your
                            interests.</li>
                    </ul>

                    <h2 class="text-2xl font-bold mb-2">Security</h2>

                    <p class="mb-4">
                        We are committed to ensuring that your information is secure. In order to prevent unauthorized access or
                        disclosure, we have put in place suitable physical, electronic, and managerial procedures to safeguard and secure the
                        information we collect online.
                    </p>

                    <h2 class="text-2xl font-bold mb-2">Cookies</h2>

                    <p class="mb-4">
                        A cookie is a small file that asks permission to be placed on your computer's hard drive. Once you agree,
                        the file is added, and the cookie helps analyze web traffic or lets you know when you visit a particular site.
                    </p>
                </div>
            </div>
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

        // Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get modal elements
            const modal = document.getElementById('legalModal');
            const openModalBtn = document.getElementById('openTermsModal');
            const closeModalBtn = document.getElementById('closeModal');
            const modalTabs = document.querySelectorAll('.modal-tab');
            
            // Open modal
            openModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                modal.classList.add('show');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            });
            
            // Close modal
            closeModalBtn.addEventListener('click', function() {
                modal.classList.remove('show');
                document.body.style.overflow = ''; // Re-enable scrolling
            });
            
            // Close modal if clicked outside content
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
            
            // Tab functionality
            modalTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    modalTabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all tab contents
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    // Show selected tab content
                    const tabId = this.getAttribute('data-tab') + '-content';
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Real-time form validation
            const form = document.getElementById('signupForm');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');
            const addressInput = document.getElementById('address'); // Add address input
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const termsCheckbox = document.getElementById('termsAccepted');
            const submitBtn = document.getElementById('submitBtn');
            
            // Validation elements
            const nameValidation = document.getElementById('nameValidation');
            const emailValidation = document.getElementById('emailValidation');
            const phoneValidation = document.getElementById('phoneValidation');
            const addressValidation = document.getElementById('addressValidation'); // Add address validation
            const passwordValidation = document.getElementById('passwordValidation');
            const confirmPasswordValidation = document.getElementById('confirmPasswordValidation');
            const termsValidation = document.getElementById('termsValidation');
            
            // Utility functions for validation states
            function setValid(input, validationElement, message) {
                input.classList.remove('invalid-input');
                input.classList.add('valid-input');
                validationElement.textContent = message;
                validationElement.className = 'validation-message validation-success';
            }
            
            function setInvalid(input, validationElement, message) {
                input.classList.remove('valid-input');
                input.classList.add('invalid-input');
                validationElement.textContent = message;
                validationElement.className = 'validation-message validation-error';
            }
            
            // Validate name - require first and last name
            nameInput.addEventListener('input', function() {
                const nameValue = this.value.trim();
                
                if (nameValue === '') {
                    setInvalid(this, nameValidation, 'Name is required');
                } else {
                    // Split the name by spaces and check if we have at least 2 parts
                    const nameParts = nameValue.split(/\s+/).filter(part => part.length > 0);
                    
                    if (nameParts.length < 2) {
                        setInvalid(this, nameValidation, 'Please enter both first and last name');
                    } else {
                        setValid(this, nameValidation, 'Valid name');
                    }
                }
                checkFormValidity();
            });
            
            // Validate email
            emailInput.addEventListener('input', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value.trim() === '') {
                    setInvalid(this, emailValidation, 'Email is required');
                } else if (!emailRegex.test(this.value)) {
                    setInvalid(this, emailValidation, 'Invalid email format');
                } else {
                    setValid(this, emailValidation, 'Valid email');
                }
                checkFormValidity();
            });
            
            // Validate phone for Philippine format
            phoneInput.addEventListener('input', function() {
                // Clean input - remove spaces and dashes
                let phoneValue = this.value.replace(/[\s-]/g, '');
                
                if (phoneValue.trim() === '') {
                    setInvalid(this, phoneValidation, 'Phone number is required');
                } 
                // Check +639 format (international)
                else if (/^\+639\d{9}$/.test(phoneValue)) {
                    setValid(this, phoneValidation, 'Valid phone number');
                } 
                // Check 09 format (local)
                else if (/^09\d{9}$/.test(phoneValue)) {
                    setValid(this, phoneValidation, 'Valid phone number');
                } 
                else {
                    setInvalid(this, phoneValidation, 'Invalid format. Use 09XXXXXXXXX');
                }
                checkFormValidity();
            });
            
            // Validate address
            addressInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    setInvalid(this, addressValidation, 'Address is required');
                } else if (this.value.trim().length < 10) {
                    setInvalid(this, addressValidation, 'Please enter a complete address');
                } else {
                    setValid(this, addressValidation, 'Valid address');
                }
                checkFormValidity();
            });
            
            // Password validation function
            function validatePassword() {
                const value = passwordInput.value;
                const criteria = [
                    { test: value.length >= 12, message: "At least 12 characters" },
                    { test: /[A-Z]/.test(value), message: "Uppercase letter" },
                    { test: /[a-z]/.test(value), message: "Lowercase letter" },
                    { test: /[0-9]/.test(value), message: "Number" },
                    { test: /[@$!%*?&]/.test(value), message: "Special character" }
                ];
                
                const failedCriteria = criteria.filter(c => !c.test).map(c => c.message);
                
                if (value === '') {
                    setInvalid(passwordInput, passwordValidation, 'Password is required');
                    return false;
                } else if (failedCriteria.length > 0) {
                    setInvalid(passwordInput, passwordValidation, 'Missing: ' + failedCriteria.join(', '));
                    return false;
                } else {
                    setValid(passwordInput, passwordValidation, 'Strong password');
                    return true;
                }
            }
            
            // Add password input validation event
            passwordInput.addEventListener('input', function() {
                validatePassword();
                if (confirmPasswordInput.value !== '') {
                    validateConfirmPassword();
                }
                checkFormValidity();
            });
            
            // Confirm password validation
            function validateConfirmPassword() {
                if (confirmPasswordInput.value === '') {
                    setInvalid(confirmPasswordInput, confirmPasswordValidation, 'Please confirm your password');
                    return false;
                } else if (confirmPasswordInput.value !== passwordInput.value) {
                    setInvalid(confirmPasswordInput, confirmPasswordValidation, 'Passwords do not match');
                    return false;
                } else {
                    setValid(confirmPasswordInput, confirmPasswordValidation, 'Passwords match');
                    return true;
                }
            }
            
            // Add confirm password validation event
            confirmPasswordInput.addEventListener('input', function() {
                validateConfirmPassword();
                checkFormValidity();
            });
            
            // Terms validation
            termsCheckbox.addEventListener('change', function() {
                if (!this.checked) {
                    termsValidation.textContent = 'You must accept the terms and conditions';
                    termsValidation.className = 'validation-message validation-error';
                } else {
                    termsValidation.textContent = '';
                    termsValidation.className = 'validation-message';
                }
                checkFormValidity();
            });
            
            // Check form validity
            function checkFormValidity() {
                // Get validation state
                const nameValid = nameInput.classList.contains('valid-input');
                const emailValid = emailInput.classList.contains('valid-input');
                const phoneValid = phoneInput.classList.contains('valid-input');
                const addressValid = addressInput.classList.contains('valid-input'); // Add address validity
                const passwordValid = passwordInput.classList.contains('valid-input');
                const confirmValid = confirmPasswordInput.classList.contains('valid-input');
                const termsValid = termsCheckbox.checked;
                
                // Enable/disable submit button
                submitBtn.disabled = !(nameValid && emailValid && phoneValid && addressValid && passwordValid && confirmValid && termsValid);
                submitBtn.classList.toggle('opacity-50', !nameValid || !emailValid || !phoneValid || !addressValid || !passwordValid || !confirmValid || !termsValid);
            }
            
            // Run validation on page load for prefilled fields
            nameInput.dispatchEvent(new Event('input'));
            emailInput.dispatchEvent(new Event('input'));
            phoneInput.dispatchEvent(new Event('input'));
            addressInput.dispatchEvent(new Event('input')); // Add address validation on load
            passwordInput.dispatchEvent(new Event('input'));
            confirmPasswordInput.dispatchEvent(new Event('input'));
            termsCheckbox.dispatchEvent(new Event('change'));
        });

        // Add redirect functionality for successful registration
        <?php if ($registrationSuccess): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Disable the form submission
            document.getElementById('signupForm').onsubmit = function(e) {
                e.preventDefault();
                return false;
            };
            
            // Disable the submit button
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').classList.add('opacity-50');
            
            // Set up progress bar
            const progressBar = document.getElementById('progress-bar');
            const totalTime = <?= $redirectDelay ?> * 1000;
            const interval = 50; // Update every 50ms
            let elapsed = 0;
            
            const timer = setInterval(function() {
                elapsed += interval;
                const percentage = Math.min((elapsed / totalTime) * 100, 100);
                progressBar.style.width = percentage + '%';
                
                if (elapsed >= totalTime) {
                    clearInterval(timer);
                    window.location.href = 'login.php';
                }
            }, interval);
        });
        <?php endif; ?>
    </script>
</body>
</html>
