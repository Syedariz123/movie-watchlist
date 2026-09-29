<?php
/**
 * profile.php
 * ---------------------------------------------------------
 * Displays basic account info for the logged-in user.
 * ---------------------------------------------------------
 */
require_once 'includes/auth_check.php';
require_once 'db.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stats = $conn->prepare("SELECT COUNT(*) AS total,
    SUM(CASE WHEN status = 'Watched' THEN 1 ELSE 0 END) AS watched
    FROM movies WHERE user_id = ?");
$stats->bind_param("i", $user_id);
$stats->execute();
$statRow = $stats->get_result()->fetch_assoc();
$stats->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - CineList</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <h2 class="page-heading"><i class="bi bi-person-circle text-accent"></i> My Profile</h2>
      <p class="page-subheading">Your account details.</p>

      <div class="form-card text-center">
        <div class="mb-3">
          <div class="icon-circle icon-total mx-auto" style="width:80px;height:80px;font-size:2rem;">
            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
          </div>
        </div>
        <h4><?php echo htmlspecialchars($user['name']); ?></h4>
        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
        <p class="text-muted small">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>

        <hr class="border-secondary my-4">

        <div class="row text-center">
          <div class="col-6">
            <div class="value fw-bold fs-3"><?php echo (int) $statRow['total']; ?></div>
            <div class="label text-muted">Total Movies</div>
          </div>
          <div class="col-6">
            <div class="value fw-bold fs-3"><?php echo (int) $statRow['watched']; ?></div>
            <div class="label text-muted">Watched</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
