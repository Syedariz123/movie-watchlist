<?php
/**
 * db.php
 * ---------------------------------------------------------
 * Central database connection file (mysqli, prepared statements).
 * Update the credentials below to match your local MySQL setup.
 * ---------------------------------------------------------
 */

// ----- Database credentials -----
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "movie_watchlist";

// ----- Create connection -----
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// ----- Check connection -----
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Force utf8mb4 for proper character support
$conn->set_charset("utf8mb4");
?>
