<?php
/**
 * includes/auth_check.php
 * ---------------------------------------------------------
 * Include this at the very top of any page that requires
 * the user to be logged in. Redirects to login page if not.
 * ---------------------------------------------------------
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
