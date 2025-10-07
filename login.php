<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Grading Portal - Login</title>

  <!-- Bootstrap (leave this) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles (make sure this is AFTER bootstrap link) -->
  <style>
    /* full height royal-blue background */
    html, body {
      height: 100%;
    }

    body {
  position: relative;
  min-height: 100vh;
  margin: 0;
  font-family: Arial, sans-serif;
  background: #003366; /* fallback royal blue if image fails */
  overflow-x: hidden;
}

body::before {
  content: "";
  position: fixed;
  inset: 0;
  background: url('anhs.jpg') no-repeat center center fixed;
  background-size: cover;
  opacity: 0.25;  /* 25% transparency */
  z-index: -1;   /* keep it behind everything */
}


    /* in case body has .bg-light or other bootstrap utility */
    body.bg-light, .bg-light {
      background: none !important;
    }

    /* card (login box) */
    .login-card {
      width: 100%;
      max-width: 420px;
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.2);
      overflow: hidden;
    }

    /* make the card white and text dark */
    .login-card .card-body {
      background: #ffffff;
      color: #0b1220;
      padding: 28px;
    }

    /* small header area on blue background */
    .login-top {
      padding: 22px;
      text-align: center;
      color: #fff;
    }

    .login-top h3 { margin: 0; font-weight: 600; }

    /* custom button: default royalblue, on hover -> white background */
    .btn-login {
      background-color: #1e40af;
      color: #fff;
      border: 2px solid #1e40af;
      transition: all .15s ease;
    }
    .btn-login:hover, .btn-login:focus {
      background-color: #ffffff;
      color: #1e40af;
      border-color: #ffffff;
      box-shadow: none;
    }

    /* small tweak for form inputs */
    .form-control:focus {
      box-shadow: 0 0 0 0.15rem rgba(30,64,175,0.15);
      border-color: #1e40af;
    }

    /* small responsive padding */
    @media (max-width: 480px) {
      .login-top { padding: 14px; }
      .login-card .card-body { padding: 18px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">

       <!-- Top area on royal background -->
<div class="login-top text-center">
  <img src="ANHS.png" alt="ANHS Logo" style="max-width: 120px; margin-bottom: 10px;">
  <h3>ANHS Grading Portal</h3>
  <p class="small">Welcome Eagle!</p>
</div>

        <!-- White card -->
        <div class="card login-card mx-auto">
          <div class="card-body">
            <?php if ($error): ?>
              <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="post" action="auth.php" autocomplete="off">
              <input type="hidden" name="action" value="login">

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-login">Log In</button>
              </div>
            </form>

            <p class="mt-3 small text-muted text-center">
              © BSCS STUDENTS

              <div class="alert alert-warning mt-3 text-center">
  ⚠️ Please note: For your privacy, do not screenshot, record, or share your grades.
</div>

            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- optional bootstrap JS (not required for styles) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
