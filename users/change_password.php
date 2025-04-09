<?php
require_once '../session/session_manager.php';
require '../session/db.php';
require '../session/audit_trail.php';
require_once '../notification/notif_handler.php';  // Add this line

start_secure_session();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../authentication/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!password_verify($current_password, $user['password'])) {
            throw new Exception("Current password is incorrect");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("New passwords do not match");
        }

        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user_id);

        if ($update_stmt->execute()) {
            // Log the password change
            $action_details = "Password changed successfully";
            logActivity($user_id, "Password Change", $action_details);

            // Create notification for password change
            $notification_message = "Your account password was changed successfully. If you didn't make this change, please contact support immediately.";
            createNotification($user_id, $notification_message, 'security');

            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Password updated successfully'
            ];
        } else {
            throw new Exception("Failed to update password");
        }

    } catch (Exception $e) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => $e->getMessage()
        ];
    }

    header('Location: profile.php');
    exit();
}
?>