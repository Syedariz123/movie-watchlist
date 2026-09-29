<?php
/**
 * movies.php
 * ---------------------------------------------------------
 * Lists the logged-in user's movies. Search, genre filter,
 * status filter, watch-toggle and delete are all done via
 * AJAX (XMLHttpRequest) without reloading the page.
 * ---------------------------------------------------------
 */
require_once 'includes/auth_check.php';
require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Get distinct genres for the filter dropdown
$genreResult = $conn->query("SELECT DISTINCT genre FROM movies WHERE user_id = $user_id ORDER BY genre ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Movies - CineList</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
    <div>
      <h2 class="page-heading">My Movies</h2>
      <p class="page-subheading mb-0">Search, filter and manage your entire collection.</p>
    </div>
    <a href="add_movie.php" class="btn btn-accent"><i class="bi bi-plus-circle"></i> Add Movie</a>
  </div>

  <!-- Filter Bar -->
  <div class="filter-bar">
    <div class="row g-3 align-items-end">
      <div class="col-md-5">
        <label class="form-label mb-1">Search by title</label>
        <input type="text" id="searchInput" class="form-control" placeholder="e.g. Inception">
      </div>
      <div class="col-md-3">
        <label class="form-label mb-1">Genre</label>
        <select id="genreFilter" class="form-select">
          <option value="All">All Genres</option>
          <?php while ($g = $genreResult->fetch_assoc()): ?>
            <option value="<?php echo htmlspecialchars($g['genre']); ?>"><?php echo htmlspecialchars($g['genre']); ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label mb-1">Status</label>
        <select id="statusFilter" class="form-select">
          <option value="All">All</option>
          <option value="Watched">Watched</option>
          <option value="Not Watched">Not Watched</option>
        </select>
      </div>
      <div class="col-md-1">
        <button id="resetBtn" class="btn btn-outline-light-custom w-100" title="Reset filters">
          <i class="bi bi-arrow-counterclockwise"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Movie Grid (populated via AJAX) -->
  <div id="movieGrid" class="row g-4">
    <div class="spinner-wrap col-12">
      <div class="spinner-border text-danger" role="status"></div>
    </div>
  </div>
</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light border border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger"></i> Delete Movie</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete "<span id="deleteMovieTitle" class="fw-bold"></span>"? This cannot be undone.
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-outline-light-custom" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
      </div>
    </div>
  </div>
</div>

<div id="ajaxAlert"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    loadMovies(); // initial load, defined in js/script.js

    let debounceTimer;
    document.getElementById('searchInput').addEventListener('keyup', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(loadMovies, 350); // debounce live search
    });

    document.getElementById('genreFilter').addEventListener('change', loadMovies);
    document.getElementById('statusFilter').addEventListener('change', loadMovies);

    document.getElementById('resetBtn').addEventListener('click', function () {
      document.getElementById('searchInput').value = '';
      document.getElementById('genreFilter').value = 'All';
      document.getElementById('statusFilter').value = 'All';
      loadMovies();
    });
  });
</script>
</body>
</html>
