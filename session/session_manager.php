<?php

function start_secure_session() {   
    // Start the session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $session_timeout = 1800; // 30 minutes in seconds
    $warning_threshold = 300; // 5 minutes in seconds (adjust as needed)

    

    // Check if the session has timed out
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
        // Session expired, destroy it
        session_unset();
        session_destroy();

        // Redirect to login page
        header("Location: ../authentication/login.php");
        exit();
    } else {
        // Update the session last activity time to the current time
        $_SESSION['last_activity'] = time();
    }

    // Output JavaScript for dynamic session expiration alert
    echo '<script>
        var sessionTimeout = ' . ($session_timeout * 1000) . ';  // Timeout in milliseconds
        var warningThreshold = ' . ($warning_threshold * 1000) . ';  // Threshold in milliseconds

        function checkSessionExpiration() {
            var lastActivityTime = ' . ($_SESSION['last_activity'] * 1000) . ';
            var currentTime = new Date().getTime();
            var timeLeft = sessionTimeout - (currentTime - lastActivityTime);

            if (timeLeft < warningThreshold) {
                alert("Your session will expire soon. Please save your work or refresh the page.");
                // Optionally, add functionality to warn users repeatedly or refresh the session
            }
        }

        setInterval(checkSessionExpiration, 60000);  // Check every minute (adjust as needed)
    </script>';
}
?>
