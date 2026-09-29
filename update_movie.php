<?php
/**
 * update_movie.php
 * ---------------------------------------------------------
 * AJAX endpoint (POST) - updates an existing movie owned by
 * the logged-in user. Also used for the quick "toggle watch
 * status" action from the movie cards. Returns JSON.
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

$user_id  = $_SESSION['user_id'];
$movie_id = trim($_POST['movie_id'] ?? '');

if (!ctype_digit((string)$movie_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid movie ID.']);
    exit();
}

// ---- Verify ownership ----
$check = $conn->prepare("SELECT movie_id FROM movies WHERE movie_id = ? AND user_id = ?");
$check->bind_param("ii", $movie_id, $user_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Movie not found or access denied.']);
    exit();
}
$check->close();

// ---- Mode 1: Quick status toggle only ----
if (isset($_POST['toggle_status']) && $_POST['toggle_status'] === '1') {
    $status = trim($_POST['status'] ?? '');
    if (!in_array($status, ['Watched', 'Not Watched'], true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status.']);
        exit();
    }
    $stmt = $conn->prepare("UPDATE movies SET status = ? WHERE movie_id = ? AND user_id = ?");
    $stmt->bind_param("sii", $status, $movie_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
    }
    $stmt->close();
    exit();
}

// ---- Mode 2: Full movie update ----
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

$stmt = $conn->prepare("UPDATE movies SET
    title = ?, genre = ?, language = ?, release_year = ?, rating = ?, status = ?, poster = ?, review = ?
    WHERE movie_id = ? AND user_id = ?");
$stmt->bind_param(
    "sssiisssii",
    $title, $genre, $language, $release_year, $rating, $status, $poster, $review, $movie_id, $user_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Movie updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update movie. Please try again.']);
}
$stmt->close();
?>
