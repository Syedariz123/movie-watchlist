<?php
/**
 * insert_movie.php
 * ---------------------------------------------------------
 * AJAX endpoint (POST) - inserts a new movie for the
 * logged-in user. Returns JSON.
 * ---------------------------------------------------------
 */
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id      = $_SESSION['user_id'];
$title        = trim($_POST['title'] ?? '');
$genre        = trim($_POST['genre'] ?? '');
$language     = trim($_POST['language'] ?? '');
$release_year = trim($_POST['release_year'] ?? '');
$rating       = trim($_POST['rating'] ?? '0');
$status       = trim($_POST['status'] ?? 'Not Watched');
$poster       = trim($_POST['poster'] ?? '');
$review       = trim($_POST['review'] ?? '');

// ---- Server-side (PHP) validation ----
$errors = [];

if ($title === '')                       $errors[] = "Title is required.";
if ($genre === '')                       $errors[] = "Genre is required.";
if ($language === '')                    $errors[] = "Language is required.";
if (!ctype_digit((string)$release_year) || $release_year < 1900 || $release_year > (date('Y') + 1)) {
    $errors[] = "Please enter a valid release year.";
}
if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
    $errors[] = "Rating must be between 1 and 5.";
}
if (!in_array($status, ['Watched', 'Not Watched'], true)) {
    $errors[] = "Invalid watch status.";
}
if ($poster !== '' && !filter_var($poster, FILTER_VALIDATE_URL)) {
    $errors[] = "Poster must be a valid URL.";
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO movies (user_id, title, genre, language, release_year, rating, status, poster, review)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    "isssiisss",
    $user_id, $title, $genre, $language, $release_year, $rating, $status, $poster, $review
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Movie added successfully!', 'movie_id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add movie. Please try again.']);
}
$stmt->close();
?>
