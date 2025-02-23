<?php
require '../session/db.php';
require '../vendor/autoload.php';
require '../config/config.php';
require_once '../session/audit_trail.php'; // Add this line

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM staff WHERE Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();

    if ($staff) {
        // First check if account is suspended
        if ($staff['status'] === 'Suspended') {
            $error = "Your account has been suspended. Please contact the administrator.";
        } 
        // If not suspended, proceed with password verification
        else if (password_verify($password, $staff['Password'])) {
            // Log the staff login activity
            logActivity(
                $staff['staff_id'],
                'Staff Login',
                'Staff member logged in successfully',
                $_SERVER['REMOTE_ADDR']
            );

            // Generate OTP and continue with existing login process
            $otp = mt_rand(100000, 999999);
            $otpExpiration = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            // Update staff record with OTP
            $updateQuery = "UPDATE staff SET OTP = ?, OTP_expiration = ?, OTP_used = 0 WHERE staff_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ssi", $otp, $otpExpiration, $staff['staff_id']);
            
            if ($updateStmt->execute()) {
                // Start session and store staff data
                session_start();
                $_SESSION['staff_id'] = $staff['staff_id'];
                $_SESSION['staff_email'] = $staff['Email'];
                
                // Send OTP email
                $mail = new PHPMailer(true);
                
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    $mail->Username = SMTP_USERNAME;
                    $mail->Password = SMTP_PASSWORD;
                    $mail->setFrom(SMTP_USERNAME, 'PropertyWise | Staff Verification');
                    $mail->addAddress($staff['Email']);
                    $mail->isHTML(true);
                    
                    $mail->Subject = 'Staff Login OTP Verification';
                    $mail->Body = '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
                        <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <h1 style="color: #1f2937; font-size: 24px; margin-bottom: 20px; text-align: center;">Staff Login Verification</h1>
                            <p style="color: #6b7280; margin-bottom: 20px; text-align: center;">Your OTP for staff login verification is:</p>
                            <div style="background-color: #f8fafc; border: 1px dashed #e2e8f0; padding: 20px; text-align: center; margin: 20px 0;">
                                <span style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #3b82f6;">' . $otp . '</span>
                            </div>
                            <p style="color: #6b7280; font-size: 14px; text-align: center;">This OTP will expire in 10 minutes.</p>
                        </div>
                    </div>';

                    $mail->send();
                    header("Location: staff_otp.php");
                    exit();
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Error updating OTP";
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
            <h2 class="text-white text-center text-2xl font-bold">Staff Sign in</h2>
            
            <form method="POST" class="mt-8 space-y-4">
                <div>
                    <label class="text-white text-sm mb-2 block">Email</label>
                    <div class="relative flex items-center">
                        <input name="username" type="text" required class="w-full text-gray-800 text-sm border border-gray-300 px-4 py-3 rounded-md outline-blue-600" placeholder="Enter Staff Email" />
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
                        <a href="../session/staff_password.php" class="text-blue-600 hover:underline font-semibold">Forgot your password?</a>
                    </div>
                </div>

                <div class="!mt-8">
                    <button type="submit" class="w-full py-3 px-4 text-sm tracking-wide rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">Sign in</button>
                </div>
                <div class="text-sm text-center mt-4">
                    <a href="login.php" class="text-blue-600 hover:underline font-semibold">
                        Back to Sign in<i class="fa fa-arrow-right ml-1"></i>
                    </a>    
                 </div>


            </form>

            <!-- Error message handling -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
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