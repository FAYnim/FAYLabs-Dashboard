<?php
// Layout — <head> Partial
// Requires: $pageTitle, ROOT_PATH, config/app.php loaded
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <meta name="base-path" content="<?= htmlspecialchars(BASE_PATH) ?>">
  <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — FAY Labs Admin</title>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- highlight.js -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css"
        id="hljs-light-theme">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css"
        id="hljs-dark-theme" disabled>

  <!-- Admin CSS -->
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/admin.css">

  <!-- Theme script (before body to avoid FOUC) -->
  <script src="<?= BASE_PATH ?>/assets/js/theme.js"></script>
  <script>
    // Sync hljs theme with app theme
    document.addEventListener('DOMContentLoaded', function() {
      function syncHljsTheme() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        document.getElementById('hljs-light-theme').disabled = isDark;
        document.getElementById('hljs-dark-theme').disabled  = !isDark;
      }
      syncHljsTheme();
      document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
        btn.addEventListener('click', function() {
          setTimeout(syncHljsTheme, 50);
        });
      });
    });
  </script>
</head>
<body>
