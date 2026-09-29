<?php
/**
 * register.php - User Registration Page
 * ---------------------------------------------------------
 */
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ---- Server-side validation ----
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check duplicate email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $name, $email, $hashedPassword);

            if ($insert->execute()) {
                $success = "Account created successfully! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $insert->close();
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
  <title>Register - CineList</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-wrapper">
  <div class="auth-card">
    <h2><i class="bi bi-person-plus"></i> Create Account</h2>
    <p class="subtitle">Join CineList and start tracking movies</p>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form id="registerForm" method="POST" action="register.php" novalidate>
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" id="name" class="form-control" placeholder="John Doe" required minlength="2">
        <div class="invalid-feedback">Please enter your name.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" required>
        <div class="invalid-feedback">Please enter a valid email.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="At least 6 characters" required minlength="6">
        <div class="invalid-feedback">Password must be at least 6 characters.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter password" required minlength="6">
        <div class="invalid-feedback">Passwords do not match.</div>
      </div>
      <button type="submit" class="btn btn-accent w-100 mt-2">
        <i class="bi bi-person-check"></i> Register
      </button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="index.php">Login here</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ---- Client-side (JavaScript) validation ----
  (function () {
    const form = document.getElementById('registerForm');
    form.addEventListener('submit', function (e) {
      let valid = true;
      const name = document.getElementById('name');
      const email = document.getElementById('email');
      const password = document.getElementById('password');
      const confirm = document.getElementById('confirm_password');

      [name, email, password, confirm].forEach(el => el.classList.remove('is-invalid'));

      if (name.value.trim().length < 2) { name.classList.add('is-invalid'); valid = false; }
      if (!email.value.includes('@') || email.value.trim() === '') { email.classList.add('is-invalid'); valid = false; }
      if (password.value.trim().length < 6) { password.classList.add('is-invalid'); valid = false; }
      if (confirm.value !== password.value || confirm.value.trim() === '') { confirm.classList.add('is-invalid'); valid = false; }

      if (!valid) e.preventDefault();
    });
  })();
</script>
</body>
</html>
