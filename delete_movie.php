<?php
/**
 * delete_movie.php
 * ---------------------------------------------------------
 * AJAX endpoint (POST) - deletes a movie owned by the
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

$user_id  = $_SESSION['user_id'];
$movie_id = trim($_POST['movie_id'] ?? '');

if (!ctype_digit((string)$movie_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid movie ID.']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM movies WHERE movie_id = ? AND user_id = ?");
$stmt->bind_param("ii", $movie_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Movie deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Movie not found or already deleted.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete movie.']);
}
$stmt->close();
?>
