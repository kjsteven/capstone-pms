<?php

session_start();

require '../vendor/autoload.php'; 
require '../session/db.php';
require '../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// OTP Verification logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["verify"])) {
    $userId = $_SESSION["user_id"];
    $enteredOTP = '';

    // Collect the OTP from all input fields
    for ($i = 0; $i < 6; $i++) {
        if (isset($_POST["otp$i"])) {
            $enteredOTP .= $_POST["otp$i"];
        }
    }
     
    $userId = $_SESSION['user_id'];

    $query = "SELECT OTP, OTP_expiration, role FROM users WHERE user_id = $userId";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $storedOTP = $row["OTP"];
        $otpExpiration = strtotime($row["OTP_expiration"]);

        if ($otpExpiration > time()) {
            if ($enteredOTP === $storedOTP) {
                // Set the session variables for authenticated user
                $_SESSION["otp_verified"] = true;
                $_SESSION["role"] = $row["role"];
                
                // Redirect based on role
                $dashboardURL = ($row["role"] == "Admin") ? "../admin/dashboardAdmin.php" : "../users/dashboard.php";
                header("Location: $dashboardURL");
                exit;
            } else {
                echo "<script>alert('Incorrect OTP. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('OTP has expired. Please request a new one.');</script>";
        }
    } else {
        echo "Error retrieving user data: " . mysqli_error($conn);
    }
}

// Handle OTP resend
if (isset($_POST["resendOTP"])) {
    $newOTP = mt_rand(100000, 999999);
    $userId = $_SESSION["user_id"];

    // Set new expiration date for OTP (e.g., 10 minutes from now)
    $otpExpiration = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $updateQuery = "UPDATE users SET OTP = ?, OTP_used = 0, OTP_expiration = ? WHERE user_id = $userId";
    $updateResult = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($updateResult, "is", $newOTP, $otpExpiration);
    mysqli_stmt_execute($updateResult);

    if (!$updateResult) {
        die("Database query error: " . mysqli_error($conn));
    }

    $to = $_SESSION["username"];
    $subject = "Your New OTP";
    $message = "Your new OTP is: $newOTP";

    require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require '../vendor/phpmailer/phpmailer/src/SMTP.php';
    require '../vendor/phpmailer/phpmailer/src/Exception.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->setFrom(SMTP_USERNAME, 'PropertyWise | OTP Verification');
        $mail->addAddress($to);
        $mail->isHTML(true);
        
        $mail->Subject = 'Your New OTP Code';
        $mail->Body = '
        <div style="font-family: \'Arial\', sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
            <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="color: #1f2937; font-size: 24px; font-weight: bold; margin-bottom: 10px;">New OTP Verification</h1>
                    <p style="color: #6b7280; font-size: 16px; margin-bottom: 20px;">Here is your new OTP code</p>
                </div>
                
                <div style="background-color: #f8fafc; border: 1px dashed #e2e8f0; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;">
                    <span style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #3b82f6;">' . $newOTP . '</span>
                </div>
                
                <div style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 20px;">
                    <p>This OTP will expire in 10 minutes.</p>
                    <p style="margin-top: 10px;">If you did not request this OTP, please ignore this email.</p>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px;">
                    <p>This is an automated message, please do not reply.</p>
                    <p style="margin-top: 5px;">&copy; ' . date("Y") . ' PropertyWise. All rights reserved.</p>
                </div>
            </div>
        </div>';

        $mail->AltBody = "Your new OTP is: $newOTP\nThis code will expire in 10 minutes.";

        $mail->send();
        echo "<script>alert('OTP has been resent. Check your email.');</script>";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-cover bg-center flex items-center justify-center py-6 px-4" style="background-image: url('../images/bg3.jpg');">

    <div class="max-w-md w-full" style="background-color: #1f2937; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
        <header class="mb-8 text-center">
            <h1 class="text-2xl font-bold mb-1 text-white">OTP Verification</h1>
            <p class="text-[15px] text-slate-300">Enter the 6-digit verification code that was sent to your email.</p>
        </header>
        <form id="otp-form" method="POST" action="">
            <div class="flex items-center justify-center gap-3">
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" name="otp<?php echo $i; ?>" class="w-14 h-14 text-center text-2xl font-extrabold text-slate-900 bg-slate-100 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" maxlength="1" />
                <?php endfor; ?>
            </div>
            <input type="hidden" name="verify" value="true" />
            <div class="max-w-[260px] mx-auto mt-4">
                <button type="submit" class="w-full inline-flex justify-center whitespace-nowrap rounded-lg bg-indigo-500 px-3.5 py-2.5 text-sm font-medium text-white shadow-sm shadow-indigo-950/10 hover:bg-indigo-600 focus:outline-none focus:ring focus:ring-indigo-300 focus-visible:outline-none focus-visible:ring focus-visible:ring-indigo-300 transition-colors duration-150">Verify Account</button>
            </div>
        </form>
        <div class="text-sm text-slate-300 mt-4 text-center">Didn't receive code? <button type="submit" name="resendOTP" form="otp-form" class="font-medium text-indigo-500 hover:text-indigo-600">Resend</button></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('otp-form');
            const inputs = [...form.querySelectorAll('input[type=text]')];
            const submit = form.querySelector('button[type=submit]');

            const handleKeyDown = (e) => {
                if (!/^[0-9]{1}$/.test(e.key) && e.key !== 'Backspace') {
                    e.preventDefault();
                }
            };

            inputs.forEach((input, index) => {
                input.addEventListener('keydown', handleKeyDown);

                input.addEventListener('input', (e) => {
                    if (e.target.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });

                input.addEventListener('focus', () => {
                    inputs.forEach((input) => {
                        input.classList.remove('border-indigo-500');
                    });
                    input.classList.add('border-indigo-500');
                });
            });

            inputs[0].focus();
        });
    </script>
</body>
</html>
