/**
 * js/script.js
 * ---------------------------------------------------------
 * All AJAX operations use XMLHttpRequest (as required) —
 * no fetch() is used anywhere in this file.
 * Handles: live search, genre/status filter, load, delete,
 * and quick watch-status toggle on movies.php.
 * ---------------------------------------------------------
 */

let movieIdPendingDelete = null;

/* ---------------------------------------------------------
   Small helper: show a bootstrap-style toast/alert
--------------------------------------------------------- */
function showAlert(message, type = 'success') {
  const box = document.getElementById('ajaxAlert');
  if (!box) return;
  box.innerHTML = `
    <div class="alert alert-${type} alert-dismissible fade show shadow" role="alert">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
  setTimeout(() => { box.innerHTML = ''; }, 3500);
}

/* ---------------------------------------------------------
   Render star rating (1-5) as HTML
--------------------------------------------------------- */
function renderStars(rating) {
  rating = parseInt(rating, 10) || 0;
  let html = '';
  for (let i = 1; i <= 5; i++) {
    html += i <= rating ? '<i class="bi bi-star-fill"></i> ' : '<i class="bi bi-star"></i> ';
  }
  return html;
}

/* ---------------------------------------------------------
   Escape HTML to avoid injection when rendering movie data
--------------------------------------------------------- */
function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

/* ---------------------------------------------------------
   LOAD / SEARCH / FILTER movies (GET via XMLHttpRequest)
   Used on movies.php
--------------------------------------------------------- */
function loadMovies() {
  const grid = document.getElementById('movieGrid');
  if (!grid) return;

  const search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
  const genre  = document.getElementById('genreFilter') ? document.getElementById('genreFilter').value : 'All';
  const status = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : 'All';

  grid.innerHTML = `<div class="spinner-wrap col-12"><div class="spinner-border text-danger" role="status"></div></div>`;

  const params = `search=${encodeURIComponent(search)}&genre=${encodeURIComponent(genre)}&status=${encodeURIComponent(status)}`;

  const xhr = new XMLHttpRequest();
  xhr.open('GET', 'fetch_movies.php?' + params, true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        try {
          const data = JSON.parse(xhr.responseText);
          if (data.success) {
            renderMovies(data.movies);
          } else {
            grid.innerHTML = `<div class="col-12"><div class="alert alert-danger">${escapeHtml(data.message)}</div></div>`;
          }
        } catch (e) {
          grid.innerHTML = `<div class="col-12"><div class="alert alert-danger">Failed to parse response.</div></div>`;
        }
      } else {
        grid.innerHTML = `<div class="col-12"><div class="alert alert-danger">Error loading movies (status ${xhr.status}).</div></div>`;
      }
    }
  };
  xhr.send();
}

/* ---------------------------------------------------------
   Render the movie cards into the grid
--------------------------------------------------------- */
function renderMovies(movies) {
  const grid = document.getElementById('movieGrid');
  if (!grid) return;

  if (!movies || movies.length === 0) {
    grid.innerHTML = `
      <div class="col-12">
        <div class="empty-state">
          <i class="bi bi-emoji-frown"></i>
          <p>No movies match your search/filters.</p>
        </div>
      </div>`;
    return;
  }

  let html = '';
  movies.forEach(movie => {
    const posterHtml = movie.poster
      ? `<img src="${escapeHtml(movie.poster)}" alt="${escapeHtml(movie.title)}" onerror="this.parentElement.innerHTML='<div class=\\'no-poster\\'><i class=\\'bi bi-film\\'></i></div>'">`
      : `<div class="no-poster"><i class="bi bi-film"></i></div>`;

    const badgeClass = movie.status === 'Watched' ? 'badge-watched' : 'badge-pending';
    const toggleLabel = movie.status === 'Watched' ? 'Mark Unwatched' : 'Mark Watched';
    const toggleIcon = movie.status === 'Watched' ? 'bi-eye-slash' : 'bi-eye';

    html += `
      <div class="col-sm-6 col-md-4 col-lg-3 fade-in" id="movie-card-${movie.movie_id}">
        <div class="movie-card">
          <span class="badge status-badge ${badgeClass}">${escapeHtml(movie.status)}</span>
          <div class="poster-wrap">${posterHtml}</div>
          <div class="card-body">
            <div class="movie-title" title="${escapeHtml(movie.title)}">${escapeHtml(movie.title)}</div>
            <div class="movie-meta">${escapeHtml(movie.genre)} &bull; ${escapeHtml(movie.release_year)} &bull; ${escapeHtml(movie.language)}</div>
            <div class="stars">${renderStars(movie.rating)}</div>
            ${movie.review ? `<div class="review-text">${escapeHtml(movie.review)}</div>` : ''}
            <div class="card-actions mb-2">
              <a href="edit_movie.php?id=${movie.movie_id}" class="btn btn-outline-light-custom btn-sm">
                <i class="bi bi-pencil-square"></i> Edit
              </a>
              <button class="btn btn-outline-danger btn-sm" onclick="openDeleteModal(${movie.movie_id}, '${escapeHtml(movie.title).replace(/'/g, "\\'")}')">
                <i class="bi bi-trash"></i> Delete
              </button>
            </div>
            <button class="btn btn-sm w-100 ${movie.status === 'Watched' ? 'btn-outline-warning' : 'btn-outline-success'}"
                    onclick="toggleStatus(${movie.movie_id}, '${movie.status}')">
              <i class="bi ${toggleIcon}"></i> ${toggleLabel}
            </button>
          </div>
        </div>
      </div>`;
  });

  grid.innerHTML = html;
}

/* ---------------------------------------------------------
   TOGGLE watched / not watched (POST via XMLHttpRequest)
--------------------------------------------------------- */
function toggleStatus(movieId, currentStatus) {
  const newStatus = currentStatus === 'Watched' ? 'Not Watched' : 'Watched';

  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'update_movie.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        try {
          const data = JSON.parse(xhr.responseText);
          if (data.success) {
            showAlert('Status updated!', 'success');
            loadMovies();
          } else {
            showAlert(data.message || 'Failed to update status.', 'danger');
          }
        } catch (e) {
          showAlert('Unexpected server response.', 'danger');
        }
      } else {
        showAlert('Server error while updating status.', 'danger');
      }
    }
  };
  const body = `movie_id=${encodeURIComponent(movieId)}&toggle_status=1&status=${encodeURIComponent(newStatus)}`;
  xhr.send(body);
}

/* ---------------------------------------------------------
   DELETE movie (POST via XMLHttpRequest) with confirm modal
--------------------------------------------------------- */
function openDeleteModal(movieId, title) {
  movieIdPendingDelete = movieId;
  document.getElementById('deleteMovieTitle').textContent = title;
  const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
  modal.show();
}

document.addEventListener('DOMContentLoaded', function () {
  const confirmBtn = document.getElementById('confirmDeleteBtn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function () {
      if (!movieIdPendingDelete) return;

      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'delete_movie.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          const modalEl = document.getElementById('deleteModal');
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();

          if (xhr.status === 200) {
            try {
              const data = JSON.parse(xhr.responseText);
              if (data.success) {
                showAlert('Movie deleted.', 'success');
                loadMovies();
              } else {
                showAlert(data.message || 'Failed to delete movie.', 'danger');
              }
            } catch (e) {
              showAlert('Unexpected server response.', 'danger');
            }
          } else {
            showAlert('Server error while deleting movie.', 'danger');
          }
          movieIdPendingDelete = null;
        }
      };
      xhr.send(`movie_id=${encodeURIComponent(movieIdPendingDelete)}`);
    });
  }
});

/* ---------------------------------------------------------
   ADD MOVIE (POST via XMLHttpRequest) - used on add_movie.php
--------------------------------------------------------- */
function submitAddMovie(formData, callback) {
  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'insert_movie.php', true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        try {
          const data = JSON.parse(xhr.responseText);
          callback(data);
        } catch (e) {
          callback({ success: false, message: 'Unexpected server response.' });
        }
      } else {
        callback({ success: false, message: 'Server error (status ' + xhr.status + ').' });
      }
    }
  };
  xhr.send(formData);
}

/* ---------------------------------------------------------
   UPDATE MOVIE (POST via XMLHttpRequest) - used on edit_movie.php
--------------------------------------------------------- */
function submitUpdateMovie(formData, callback) {
  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'update_movie.php', true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        try {
          const data = JSON.parse(xhr.responseText);
          callback(data);
        } catch (e) {
          callback({ success: false, message: 'Unexpected server response.' });
        }
      } else {
        callback({ success: false, message: 'Server error (status ' + xhr.status + ').' });
      }
    }
  };
  xhr.send(formData);
}
