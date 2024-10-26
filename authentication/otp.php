<?php

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../session/session_manager.php';
require '../session/db.php';
require '../config/config.php';

start_secure_session();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../authentication/login.php");
    exit;
}

if (isset($_SESSION["otp_verified"]) && $_SESSION["otp_verified"] === true) {
    $userId = $_SESSION["user_id"];
    $query = "SELECT role FROM users WHERE user_id = $userId";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $dashboardURL = ($row["role"] == "Admin") ? "../admin/admin-dashboard.php" : "dashboard.php";
        header("Location: $dashboardURL");
        exit;
    }
}

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

    $query = "SELECT OTP, OTP_used, OTP_expiration, role FROM users WHERE user_id = $userId";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $storedOTP = $row["OTP"];
        $otpExpiration = strtotime($row["OTP_expiration"]);

        if ($otpExpiration > time()) {
            if ($enteredOTP === $storedOTP) {
                $updateQuery = "UPDATE users SET OTP_used = 1 WHERE user_id = $userId";
                $updateResult = mysqli_query($conn, $updateQuery);

                if ($updateResult) {
                    $_SESSION["otp_verified"] = true;
                    $dashboardURL = ($row["role"] == "Admin") ? "../admin/dashboardAdmin.php" : "../users/dashboard.php";
                    header("Location: $dashboardURL");
                    exit;
                } else {
                    echo "Error updating OTP: " . mysqli_error($conn);
                }
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

    $updateQuery = "UPDATE users SET OTP = $newOTP, OTP_used = 0 WHERE user_id = $userId";
    $updateResult = mysqli_query($conn, $updateQuery);

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

        $mail->setFrom(SMTP_USERNAME, 'RentEase | OTP Verification');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

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
            <h1 class="text-2xl font-bold mb-1 text-white">Email Verification</h1>
            <p class="text-[15px] text-slate-300">Enter the 6-digit verification code that was sent to your email.</p>
        </header>
        <form id="otp-form" method="POST" action="">
            <div class="flex items-center justify-center gap-3">
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" name="otp<?php echo $i; ?>" class="w-14 h-14 text-center text-2xl font-extrabold text-slate-900 bg-slate-100 border border-transparent hover:border-slate-200 appearance-none rounded p-4 outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" maxlength="1" required />
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
