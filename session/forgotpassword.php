<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../session/db.php';
require '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // Generate a reset token
        $resetToken = bin2hex(random_bytes(32));

        // Store the reset token in the database
        $stmt = $conn->prepare("UPDATE users SET ResetToken = ? WHERE Email = ?");
        $stmt->bind_param("ss", $resetToken, $email);
        $stmt->execute();
        $stmt->close();

        $_SESSION['resetEmail'] = $email;

        // Create the email content with the reset token link
        $subject = 'Password Reset';
        $body = 'Click the following link to reset your password: <a href="https://localhost/capstone-pms/session/resetpassword.php?resettoken=' . $resetToken . '">Reset Password</a>';

        // Send the email using PHPMailer
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

            $mail->setFrom(SMTP_USERNAME, 'PropertyWise | Reset Password');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            
            // Improved HTML email template
            $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f3f4f6;">
                <div style="background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    
                    <h1 style="color: #1f2937; font-size: 24px; margin-bottom: 20px; text-align: center;">Reset Your Password</h1>
                    <p style="color: #6b7280; margin-bottom: 20px; text-align: center;">You recently requested to reset your password for your PropertyWise account. Click the button below to proceed:</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="https://localhost/capstone-pms/session/resetpassword.php?resettoken=' . $resetToken . '" 
                           style="background-color: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                            Reset Password
                        </a>
                    </div>
                    <p style="color: #6b7280; font-size: 14px; text-align: center; margin-top: 20px;">
                        If you did not request a password reset, please ignore this email or contact support if you have concerns.
                    </p>
                    <div style="border-top: 1px solid #e5e7eb; margin-top: 30px; padding-top: 20px;">
                        <p style="color: #9ca3af; font-size: 12px; text-align: center; margin-bottom: 10px;">
                            For security, this request will expire in 10 minutes.
                        </p>
                    </div>
                    <div style="border-top: 1px solid #e5e7eb; margin-top: 30px; padding-top: 20px; text-align: center;">
                        <p style="color: #9ca3af; font-size: 12px; margin-bottom: 10px;">
                            This is an automated message, please do not reply.
                        </p>
                        <p style="color: #9ca3af; font-size: 12px;">
                            PropertyWise &copy; ' . date('Y') . ' All rights reserved.
                        </p>
                    </div>
                </div>
            </div>';

            // Plain text alternative
            $mail->AltBody = 'Reset your password by copying and pasting this link into your browser: https://localhost/capstone-pms/session/resetpassword.php?resettoken=' . $resetToken;

            $mail->send();

            $_SESSION['successMessage'] = 'Reset password link sent successfully.';
            header("Location: forgotpassword.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['errorMessage'] = 'Error sending the password reset link.';
            header("Location: forgotpassword.php");
            exit();
        }
    } else {
        $_SESSION['errorMessage'] = 'Email not found in the database.';
        header("Location: forgotpassword.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../images/logo.png" type="image/png">

    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>

<section class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center p-6">
  <div class="w-full p-6 bg-white rounded-lg shadow dark:border sm:max-w-md dark:bg-gray-800 dark:border-gray-700 px-4 sm:px-6">
      <h2 class="mb-1 text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
          Password Recovery
      </h2>
      <p class="text-gray-500 dark:text-gray-400">Please enter your email to recover your password</p>
      
      <!-- Display success or error message -->
      <?php if (isset($_SESSION['successMessage'])): ?>
          <div id="successMessage" class="p-4 mb-4 text-green-700 bg-green-100 rounded-lg" role="alert">
              <?php echo $_SESSION['successMessage']; unset($_SESSION['successMessage']); ?>
          </div>
      <?php elseif (isset($_SESSION['errorMessage'])): ?>
          <div id="errorMessage" class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg" role="alert">
              <?php echo $_SESSION['errorMessage']; unset($_SESSION['errorMessage']); ?>
          </div>
      <?php endif; ?>

      <form class="mt-4 space-y-4 lg:mt-5 md:space-y-5" action="" method="post">
          <div>
              <input type="email" name="email" id="email" placeholder="name@example.com" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">
          </div>
          
          <!-- Updated button background color to blue -->
          <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
              Send Password Reset Link
          </button>
      </form>
      
      <!-- Updated login link color to blue -->
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
          Already have an account? <a href="../authentication/login.php" class="font-medium text-blue-600 hover:underline dark:text-blue-500">Login</a>
      </p>
  </div>
</section>

</body>
</html>
