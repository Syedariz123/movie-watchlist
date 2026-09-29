<?php
/**
 * includes/navbar.php
 * ---------------------------------------------------------
 * Shared navigation bar for all logged-in pages.
 * Expects session to already be started by the including page.
 * ---------------------------------------------------------
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <i class="bi bi-film"></i> CineList
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $current_page === 'movies.php' ? 'active' : ''; ?>" href="movies.php">
            <i class="bi bi-collection-play"></i> My Movies
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $current_page === 'add_movie.php' ? 'active' : ''; ?>" href="add_movie.php">
            <i class="bi bi-plus-circle"></i> Add Movie
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
            <i class="bi bi-person-circle"></i> Profile
          </a>
        </li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item d-flex align-items-center me-3">
          <span class="text-secondary small">
            <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>
          </span>
        </li>
        <li class="nav-item">
          <a class="btn btn-outline-danger btn-sm" href="logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
