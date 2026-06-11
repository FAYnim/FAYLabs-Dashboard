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
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/admin.css">
  <script src="<?= BASE_PATH ?>/assets/js/theme.js"></script>
</head>
<body class="login-page">

<div class="login-card">
  <!-- Logo -->
  <div class="login-logo">
    <div class="brand-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="26" height="26">
        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
      </svg>
    </div>
    <h1>FAY Labs</h1>
    <p>Sign in to your admin dashboard</p>
  </div>

  <!-- Error Alert -->
  <div id="login-error" class="alert alert-danger" style="display:none">
    <svg viewBox="0 0 16 16" width="16" height="16" fill="currentColor">
      <path d="M8 1a7 7 0 100 14A7 7 0 008 1zm1 10.5a1 1 0 11-2 0 1 1 0 012 0zm-.25-4.75a.75.75 0 00-1.5 0v3a.75.75 0 001.5 0v-3z"/>
    </svg>
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
      <svg class="icon-sun" viewBox="0 0 16 16" width="12" height="12" style="display:block; fill:currentColor">
        <path d="M8 11a3 3 0 110-6 3 3 0 010 6zm0 1a4 4 0 100-8 4 4 0 000 8zM8 0a.5.5 0 01.5.5v2a.5.5 0 01-1 0v-2A.5.5 0 018 0zm0 13a.5.5 0 01.5.5v2a.5.5 0 01-1 0v-2A.5.5 0 018 13z"/>
      </svg>
      <svg class="icon-moon" viewBox="0 0 16 16" width="12" height="12" style="display:none; fill:currentColor">
        <path d="M6 .278a.768.768 0 01.08.858 7.208 7.208 0 00-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 01.81.316.733.733 0 01-.031.893A8.349 8.349 0 018.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 016 .278z"/>
      </svg>
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
