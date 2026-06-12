<?php
// ============================================================
// Login Page
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/csrf.php';

csrfStart();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_PATH . '/');
    exit;
}

$csrfToken = csrfGenerate();
$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <meta name="base-path" content="<?= htmlspecialchars(BASE_PATH) ?>">
  <title>Admin Login — FAY Labs</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/admin.css">
  <script src="<?= BASE_PATH ?>/assets/js/theme.js"></script>
</head>
<body class="login-page">

<div class="login-card">
  <!-- Logo -->
  <div class="login-logo">
    <div class="brand-icon" aria-hidden="true">
      <i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i>
    </div>
    <h1>FAY Labs</h1>
    <p>Sign in to your admin dashboard</p>
  </div>

  <!-- Error Alert -->
  <div id="login-error" class="alert alert-danger" style="display:none">
    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
    <span id="login-error-msg">Invalid username or password.</span>
  </div>

  <!-- Login Form -->
  <form id="login-form" novalidate autocomplete="off">
    <input type="hidden" id="csrf-token-field" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="form-group">
      <label class="form-label" for="username">Username</label>
      <input type="text" id="username" name="username" class="form-control"
             placeholder="Enter username" required autocomplete="username"
             autofocus>
    </div>

    <div class="form-group">
      <label class="form-label" for="password">Password</label>
      <input type="password" id="password" name="password" class="form-control"
             placeholder="Enter password" required autocomplete="current-password">
    </div>

    <button type="submit" id="login-btn" class="btn btn-primary w-100 btn-lg" style="margin-top:8px">
      Sign In
    </button>
  </form>

  <!-- Theme toggle -->
  <div style="text-align:center; margin-top:20px;">
    <button data-theme-toggle class="btn btn-secondary btn-sm" style="font-size:12px; gap:6px;">
      <i class="bi bi-sun-fill icon-sun" style="display:block" aria-hidden="true"></i>
      <i class="bi bi-moon-stars-fill icon-moon" style="display:none" aria-hidden="true"></i>
      <span class="theme-label">Dark Mode</span>
    </button>
  </div>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
const BASE_PATH = document.querySelector('meta[name="base-path"]').content;

$(document).ready(function () {
  $('#login-form').on('submit', function (e) {
    e.preventDefault();

    const username = $('#username').val().trim();
    const password = $('#password').val();

    if (!username || !password) {
      showError('Please enter both username and password.');
      return;
    }

    const $btn = $('#login-btn');
    $btn.html('<span class="spinner"></span> Logging in...').prop('disabled', true);
    $('#login-error').hide();

    $.ajax({
      url: BASE_PATH + '/api/auth/login.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({
        username:   username,
        password:   password,
        csrf_token: $('#csrf-token-field').val()
      }),
      success: function (res) {
        if (res.success) {
          $btn.html('<span class="spinner"></span> Redirecting...');
          window.location.href = BASE_PATH + '/';
        } else {
          showError(res.message || 'Invalid username or password.');
          $btn.html('Sign In').prop('disabled', false);
        }
      },
      error: function () {
        showError('Network error. Please try again.');
        $btn.html('Sign In').prop('disabled', false);
      }
    });
  });

  function showError(msg) {
    $('#login-error-msg').text(msg);
    $('#login-error').show();
  }
});
</script>

</body>
</html>
