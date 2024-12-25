    <?php
    require_once '../session/session_manager.php';
    require '../session/db.php';

    start_secure_session();

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If not logged in, redirect to the login page
        header('Location: ../authentication/login.php'); // Adjust the path as necessary
        exit();
    }

    // Check if a valid ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        // Redirect back with an error message if ID is invalid
        header('Location: maintenanceAdmin.php?error=invalid_request');
        exit();
    }

    $request_id = intval($_GET['id']);


    $archive_query = "UPDATE maintenance_requests SET archived = 1 WHERE id = ?";
    $stmt = $conn->prepare($archive_query);

    if ($stmt) {
        $stmt->bind_param("i", $request_id);

        if ($stmt->execute()) {
            // Redirect back with a success message
            header('Location: maintenanceAdmin.php?message=archived_successfully');
        } else {
            // Redirect back with an error message
            header('Location: maintenanceAdmin.php?error=archive_failed');
        }

        $stmt->close();
    } else {
        // Redirect back with an error message
        header('Location: maintenanceAdmin.php?error=archive_query_error');
    }

    $conn->close();
    exit();
    ?>
