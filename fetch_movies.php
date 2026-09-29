<?php
/**
 * fetch_movies.php
 * ---------------------------------------------------------
 * AJAX endpoint (GET) - returns movies belonging to the
 * logged-in user as JSON. Supports search (by title) and
 * filter (by genre / status).
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

$user_id = $_SESSION['user_id'];
$search  = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre   = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$status  = isset($_GET['status']) ? trim($_GET['status']) : '';

$sql = "SELECT * FROM movies WHERE user_id = ?";
$types = "i";
$params = [$user_id];

if ($search !== '') {
    $sql .= " AND title LIKE ?";
    $types .= "s";
    $params[] = "%" . $search . "%";
}

if ($genre !== '' && $genre !== 'All') {
    $sql .= " AND genre = ?";
    $types .= "s";
    $params[] = $genre;
}

if ($status !== '' && $status !== 'All') {
    $sql .= " AND status = ?";
    $types .= "s";
    $params[] = $status;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$movies = [];
while ($row = $result->fetch_assoc()) {
    $movies[] = $row;
}
$stmt->close();

echo json_encode(['success' => true, 'movies' => $movies, 'count' => count($movies)]);
?>
