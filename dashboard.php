<?php
/**
 * dashboard.php
 * ---------------------------------------------------------
 * Shows summary cards: Total / Watched / Watchlist movies.
 * ---------------------------------------------------------
 */
require_once 'includes/auth_check.php';
require_once 'db.php';

$user_id = $_SESSION['user_id'];

// ---- Fetch summary counts ----
$stmt = $conn->prepare("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'Watched' THEN 1 ELSE 0 END) AS watched,
    SUM(CASE WHEN status = 'Not Watched' THEN 1 ELSE 0 END) AS pending
    FROM movies WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total   = (int) ($summary['total'] ?? 0);
$watched = (int) ($summary['watched'] ?? 0);
$pending = (int) ($summary['pending'] ?? 0);

// ---- Fetch a few recently added movies ----
$recent = $conn->prepare("SELECT * FROM movies WHERE user_id = ? ORDER BY created_at DESC LIMIT 4");
$recent->bind_param("i", $user_id);
$recent->execute();
$recentMovies = $recent->get_result();
$recent->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - CineList</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <h2 class="page-heading">Welcome back, <span class="text-accent"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span> 👋</h2>
  <p class="page-subheading">Here's a quick overview of your movie watchlist.</p>

  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <div class="summary-card">
        <div class="icon-circle icon-total"><i class="bi bi-collection-play-fill"></i></div>
        <div class="value"><?php echo $total; ?></div>
        <div class="label">Total Movies</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="summary-card">
        <div class="icon-circle icon-watched"><i class="bi bi-check-circle-fill"></i></div>
        <div class="value"><?php echo $watched; ?></div>
        <div class="label">Watched Movies</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="summary-card">
        <div class="icon-circle icon-pending"><i class="bi bi-bookmark-star-fill"></i></div>
        <div class="value"><?php echo $pending; ?></div>
        <div class="label">Watchlist (Not Watched)</div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Recently Added</h4>
    <a href="movies.php" class="btn btn-outline-light-custom btn-sm">View All <i class="bi bi-arrow-right"></i></a>
  </div>

  <div class="row g-4">
    <?php if ($recentMovies->num_rows === 0): ?>
      <div class="col-12">
        <div class="empty-state">
          <i class="bi bi-camera-reels"></i>
          <p>No movies yet. Start building your watchlist!</p>
          <a href="add_movie.php" class="btn btn-accent"><i class="bi bi-plus-circle"></i> Add Your First Movie</a>
        </div>
      </div>
    <?php else: ?>
      <?php while ($movie = $recentMovies->fetch_assoc()): ?>
        <div class="col-sm-6 col-md-3">
          <div class="movie-card">
            <span class="badge status-badge <?php echo $movie['status'] === 'Watched' ? 'badge-watched' : 'badge-pending'; ?>">
              <?php echo htmlspecialchars($movie['status']); ?>
            </span>
            <div class="poster-wrap">
              <?php if (!empty($movie['poster'])): ?>
                <img src="<?php echo htmlspecialchars($movie['poster']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" onerror="this.parentElement.innerHTML='<div class=\'no-poster\'><i class=\'bi bi-film\'></i></div>'">
              <?php else: ?>
                <div class="no-poster"><i class="bi bi-film"></i></div>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <div class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></div>
              <div class="movie-meta"><?php echo htmlspecialchars($movie['genre']); ?> &bull; <?php echo (int)$movie['release_year']; ?></div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
