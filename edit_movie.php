<?php
/**
 * edit_movie.php
 * ---------------------------------------------------------
 * Loads an existing movie (owned by the logged-in user) and
 * lets the user edit it. Submits via AJAX to update_movie.php.
 * ---------------------------------------------------------
 */
require_once 'includes/auth_check.php';
require_once 'db.php';

$user_id  = $_SESSION['user_id'];
$movie_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM movies WHERE movie_id = ? AND user_id = ?");
$stmt->bind_param("ii", $movie_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Movie doesn't exist or doesn't belong to this user
    header("Location: movies.php");
    exit();
}

$movie = $result->fetch_assoc();
$stmt->close();

$genres = ['Action','Adventure','Animation','Comedy','Crime','Documentary','Drama','Fantasy','Horror','Mystery','Romance','Sci-Fi','Thriller'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Movie - CineList</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <h2 class="page-heading"><i class="bi bi-pencil-square text-accent"></i> Edit Movie</h2>
      <p class="page-subheading">Update the details of "<?php echo htmlspecialchars($movie['title']); ?>".</p>

      <div id="formAlert"></div>

      <div class="form-card">
        <form id="editMovieForm" novalidate>
          <input type="hidden" id="movie_id" name="movie_id" value="<?php echo (int) $movie['movie_id']; ?>">

          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Movie Title *</label>
              <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
              <div class="invalid-feedback">Please enter the movie title.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Release Year *</label>
              <input type="number" class="form-control" id="release_year" name="release_year" value="<?php echo (int) $movie['release_year']; ?>" min="1900" max="<?php echo date('Y') + 1; ?>" required>
              <div class="invalid-feedback">Enter a valid year.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Genre *</label>
              <select class="form-select" id="genre" name="genre" required>
                <option value="">Select genre</option>
                <?php foreach ($genres as $g): ?>
                  <option value="<?php echo $g; ?>" <?php echo ($movie['genre'] === $g) ? 'selected' : ''; ?>><?php echo $g; ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Please select a genre.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Language *</label>
              <input type="text" class="form-control" id="language" name="language" value="<?php echo htmlspecialchars($movie['language']); ?>" required>
              <div class="invalid-feedback">Please enter the language.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Rating (1-5) *</label>
              <select class="form-select" id="rating" name="rating" required>
                <option value="">Select rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <option value="<?php echo $i; ?>" <?php echo ((int)$movie['rating'] === $i) ? 'selected' : ''; ?>>
                    <?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?> (<?php echo $i; ?>)
                  </option>
                <?php endfor; ?>
              </select>
              <div class="invalid-feedback">Please select a rating.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Watch Status *</label>
              <select class="form-select" id="status" name="status" required>
                <option value="Not Watched" <?php echo ($movie['status'] === 'Not Watched') ? 'selected' : ''; ?>>Not Watched</option>
                <option value="Watched" <?php echo ($movie['status'] === 'Watched') ? 'selected' : ''; ?>>Watched</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">Poster Image URL</label>
              <input type="url" class="form-control" id="poster" name="poster" value="<?php echo htmlspecialchars($movie['poster']); ?>" placeholder="https://example.com/poster.jpg">
              <div class="invalid-feedback">Please enter a valid URL.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Personal Review</label>
              <textarea class="form-control" id="review" name="review" rows="4"><?php echo htmlspecialchars($movie['review']); ?></textarea>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-accent" id="submitBtn">
              <i class="bi bi-check-circle"></i> Save Changes
            </button>
            <a href="movies.php" class="btn btn-outline-light-custom">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script>
  document.getElementById('editMovieForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = this;

    // ---- JavaScript validation ----
    let valid = true;
    ['title', 'genre', 'language', 'release_year', 'rating'].forEach(function (id) {
      const el = document.getElementById(id);
      if (!el.value || el.value.trim() === '') {
        el.classList.add('is-invalid');
        valid = false;
      } else {
        el.classList.remove('is-invalid');
      }
    });

    const posterEl = document.getElementById('poster');
    if (posterEl.value.trim() !== '') {
      try {
        new URL(posterEl.value.trim());
        posterEl.classList.remove('is-invalid');
      } catch (err) {
        posterEl.classList.add('is-invalid');
        valid = false;
      }
    }

    const yearEl = document.getElementById('release_year');
    const year = parseInt(yearEl.value, 10);
    const maxYear = new Date().getFullYear() + 1;
    if (isNaN(year) || year < 1900 || year > maxYear) {
      yearEl.classList.add('is-invalid');
      valid = false;
    }

    if (!valid) return;

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

    const formData = new FormData(form);

    submitUpdateMovie(formData, function (response) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save Changes';

      const alertBox = document.getElementById('formAlert');
      if (response.success) {
        alertBox.innerHTML = `<div class="alert alert-success">${response.message}</div>`;
        setTimeout(function () { window.location.href = 'movies.php'; }, 900);
      } else {
        alertBox.innerHTML = `<div class="alert alert-danger">${response.message}</div>`;
      }
    });
  });
</script>
</body>
</html>
