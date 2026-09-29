<?php
/**
 * add_movie.php
 * ---------------------------------------------------------
 * Form to add a new movie. Submits via AJAX (XMLHttpRequest)
 * to insert_movie.php — no page reload.
 * ---------------------------------------------------------
 */
require_once 'includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Movie - CineList</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <h2 class="page-heading"><i class="bi bi-plus-circle text-accent"></i> Add a New Movie</h2>
      <p class="page-subheading">Fill in the details below to add a movie to your list.</p>

      <div id="formAlert"></div>

      <div class="form-card">
        <form id="addMovieForm" novalidate>
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Movie Title *</label>
              <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Interstellar" required>
              <div class="invalid-feedback">Please enter the movie title.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Release Year *</label>
              <input type="number" class="form-control" id="release_year" name="release_year" placeholder="2014" min="1900" max="<?php echo date('Y') + 1; ?>" required>
              <div class="invalid-feedback">Enter a valid year.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Genre *</label>
              <select class="form-select" id="genre" name="genre" required>
                <option value="">Select genre</option>
                <option>Action</option>
                <option>Adventure</option>
                <option>Animation</option>
                <option>Comedy</option>
                <option>Crime</option>
                <option>Documentary</option>
                <option>Drama</option>
                <option>Fantasy</option>
                <option>Horror</option>
                <option>Mystery</option>
                <option>Romance</option>
                <option>Sci-Fi</option>
                <option>Thriller</option>
              </select>
              <div class="invalid-feedback">Please select a genre.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Language *</label>
              <input type="text" class="form-control" id="language" name="language" placeholder="e.g. English" required>
              <div class="invalid-feedback">Please enter the language.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Rating (1-5) *</label>
              <select class="form-select" id="rating" name="rating" required>
                <option value="">Select rating</option>
                <option value="1">★☆☆☆☆ (1)</option>
                <option value="2">★★☆☆☆ (2)</option>
                <option value="3">★★★☆☆ (3)</option>
                <option value="4">★★★★☆ (4)</option>
                <option value="5">★★★★★ (5)</option>
              </select>
              <div class="invalid-feedback">Please select a rating.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Watch Status *</label>
              <select class="form-select" id="status" name="status" required>
                <option value="Not Watched">Not Watched</option>
                <option value="Watched">Watched</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">Poster Image URL</label>
              <input type="url" class="form-control" id="poster" name="poster" placeholder="https://example.com/poster.jpg">
              <div class="invalid-feedback">Please enter a valid URL.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Personal Review</label>
              <textarea class="form-control" id="review" name="review" rows="4" placeholder="What did you think about this movie?"></textarea>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-accent" id="submitBtn">
              <i class="bi bi-check-circle"></i> Add Movie
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
  document.getElementById('addMovieForm').addEventListener('submit', function (e) {
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
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';

    const formData = new FormData(form);

    submitAddMovie(formData, function (response) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Add Movie';

      const alertBox = document.getElementById('formAlert');
      if (response.success) {
        alertBox.innerHTML = `<div class="alert alert-success">${response.message}</div>`;
        form.reset();
        setTimeout(function () { window.location.href = 'movies.php'; }, 900);
      } else {
        alertBox.innerHTML = `<div class="alert alert-danger">${response.message}</div>`;
      }
    });
  });
</script>
</body>
</html>
