<?php
/**
 * index.php - Login Page
 * ---------------------------------------------------------
 */
session_start();
require_once 'db.php';

// If already logged in, go straight to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // ---- Server-side validation ----
    if (empty($email) || empty($password)) {
        $error = "Please fill in both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "No account found with that email.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - CineList</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-wrapper">
  <div class="auth-card">
    <h2><i class="bi bi-film"></i> CineList</h2>
    <p class="subtitle">Sign in to manage your movie watchlist</p>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form id="loginForm" method="POST" action="index.php" novalidate>
      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" required>
        <div class="invalid-feedback">Please enter a valid email.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required minlength="6">
        <div class="invalid-feedback">Password must be at least 6 characters.</div>
      </div>
      <button type="submit" class="btn btn-accent w-100 mt-2">
        <i class="bi bi-box-arrow-in-right"></i> Login
      </button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="register.php">Create one</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ---- Client-side (JavaScript) validation ----
  (function () {
    const form = document.getElementById('loginForm');
    form.addEventListener('submit', function (e) {
      let valid = true;
      const email = document.getElementById('email');
      const password = document.getElementById('password');

      if (!email.value.includes('@') || email.value.trim() === '') {
        email.classList.add('is-invalid');
        valid = false;
      } else {
        email.classList.remove('is-invalid');
      }

      if (password.value.trim().length < 6) {
        password.classList.add('is-invalid');
        valid = false;
      } else {
        password.classList.remove('is-invalid');
      }

      if (!valid) e.preventDefault();
    });
  })();
</script>
</body>
</html>
