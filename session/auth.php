<?php
// Prevent redeclaration of the function
if (!function_exists('check_admin_role')) {
    function check_admin_role() {
        if (isset($_SESSION["role"]) && $_SESSION['role'] === 'Admin') {
            return true;
        } else {
            return false;
        }
    }
}
?>
