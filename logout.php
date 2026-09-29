<?php
/**
 * logout.php
 * ---------------------------------------------------------
 * Destroys the session and redirects to the login page.
 * ---------------------------------------------------------
 */
session_start();
$_SESSION = array();
session_destroy();
header("Location: index.php");
exit();
?>
