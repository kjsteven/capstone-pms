<?php

session_start();
require '../session/db.php';
require '../vendor/autoload.php';
require '../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verify staff is coming from login
if (!isset($_SESSION['staff_id'])) {
    header("Location: stafflogin.php");
    exit();
}

// OTP Verification logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["verify"])) {
    $staffId = $_SESSION["staff_id"];
    $enteredOTP = '';

    for ($i = 0; $i < 6; $i++) {
        if (isset($_POST["otp$i"])) {
            $enteredOTP .= $_POST["otp$i"];
        }
    }

    $query = "SELECT OTP, OTP_expiration FROM staff WHERE staff_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $staffId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $storedOTP = $row["OTP"];
        $otpExpiration = strtotime($row["OTP_expiration"]);

        if ($otpExpiration > time()) {
            if ($enteredOTP === $storedOTP) {
                $_SESSION["staff_otp_verified"] = true;
                header("Location: ../staff/staffDashboard.php");
                exit;
            } else {
                echo "<script>alert('Incorrect OTP. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('OTP has expired. Please request a new one.');</script>";
        }
    }
}

// Handle OTP resend
if (isset($_POST["resendOTP"])) {
    $newOTP = mt_rand(100000, 999999);
    $staffId = $_SESSION["staff_id"];
    $otpExpiration = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $updateQuery = "UPDATE staff SET OTP = ?, OTP_used = 0, OTP_expiration = ? WHERE staff_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssi", $newOTP, $otpExpiration, $staffId);
    
    if ($stmt->execute()) {
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
            $mail->addAddress($_SESSION['staff_email']);
            $mail->isHTML(true);
            
            $mail->Subject = 'New Staff Login OTP';
            $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
                <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <h1 style="color: #1f2937; font-size: 24px; margin-bottom: 20px; text-align: center;">New Staff Login OTP</h1>
                    <p style="color: #6b7280; margin-bottom: 20px; text-align: center;">Your new OTP for staff login verification is:</p>
                    <div style="background-color: #f8fafc; border: 1px dashed #e2e8f0; padding: 20px; text-align: center; margin: 20px 0;">
                        <span style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #3b82f6;">' . $newOTP . '</span>
                    </div>
                    <p style="color: #6b7280; font-size: 14px; text-align: center;">This OTP will expire in 10 minutes.</p>
                </div>
            </div>';

            $mail->send();
            echo "<script>alert('New OTP has been sent to your email.');</script>";
        } catch (Exception $e) {
            echo "<script>alert('Could not send OTP email. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff OTP Verification</title>
    <link rel="icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .timer {
            font-size: 1.125rem;
            font-weight: 600;
        }
        /* Responsive styles */
        @media (max-width: 640px) {
            .timer {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-cover bg-center flex items-center justify-center py-6 px-4" style="background-image: url('../images/bg3.jpg');">
    <div class="max-w-md w-full" style="background-color: #1f2937; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
        <header class="mb-8 text-center">
            <h1 class="text-2xl font-bold mb-1 text-white">Staff OTP Verification</h1>
            <p class="text-[15px] text-slate-300">Enter the 6-digit verification code sent to your email.</p>
            <!-- Add timer display -->
            <p class="mt-4 text-slate-300">Time remaining: <span id="timer" class="timer text-blue-500">10:00</span></p>
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
        <div class="text-sm text-slate-300 mt-4 text-center">
            Didn't receive code? 
            <form method="POST" style="display: inline;">
                <button type="submit" name="resendOTP" id="resendBtn" class="font-medium text-indigo-500 hover:text-indigo-600">Resend</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('otp-form');
            const inputs = [...form.querySelectorAll('input[type=text]')];

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

        // Timer functionality for 10 minutes
        let timeLeft = 600; // 10 minutes in seconds (10 * 60)
        const timerDisplay = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft === 0) {
                clearInterval(timerInterval);
                timerDisplay.classList.remove('text-blue-500');
                timerDisplay.classList.add('text-red-600');
                timerDisplay.textContent = "00:00";
            } else {
                timeLeft--;
            }
        }

        const timerInterval = setInterval(updateTimer, 1000);
        updateTimer();
    </script>
</body>
</html>
