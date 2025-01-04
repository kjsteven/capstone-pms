<?php
require_once '../session/session_manager.php';
require '../session/db.php';

start_secure_session();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../authentication/login.php'); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate current password
    $sql = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        }
    } else {
        $errors[] = "User not found.";
    }

    // Validate new password
    if (strlen($new_password) < 12) {
        $errors[] = "Password must be at least 12 characters long.";
    }
    if (!preg_match('/[A-Z]/', $new_password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $new_password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $new_password)) {
        $errors[] = "Password must contain at least one number.";
    }
    if (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
        $errors[] = "Password must contain at least one special character.";
    }
    if ($new_password !== $confirm_password) {
        $errors[] = "New password and confirm password do not match.";
    }

    // If no errors, update the password
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Password updated successfully.',
            ];
        } else {
            $errors[] = "Failed to update password. Please try again.";
        }
    }

    // Store errors in session if any
    if (!empty($errors)) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => implode("|", $errors),
        ];
    }
}

$stmt->close();
$conn->close();

// Redirect to profile.php
header('Location: profile.php');
exit();
?>