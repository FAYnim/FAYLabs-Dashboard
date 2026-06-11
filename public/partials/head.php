<?php
// Public Layout Head Partial
// Requires: $pageTitle, $metaDesc, $ogImage, $canonicalUrl, APP_URL defined
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- SEO -->
  <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
  <meta name="description" content="<?= htmlspecialchars($metaDesc ?? '') ?>">
  <?php if (!empty($canonicalUrl)): ?>
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
  <?php endif; ?>

  <!-- Open Graph -->
  <?php if (!empty($ogTitle)): ?>
  <meta property="og:title"       content="<?= htmlspecialchars($ogTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($metaDesc ?? '') ?>">
  <?php if (!empty($ogImage)): ?>
  <meta property="og:image"       content="<?= htmlspecialchars($ogImage) ?>">
  <?php endif; ?>
  <meta property="og:type"        content="<?= htmlspecialchars($ogType ?? 'website') ?>">
  <?php if (!empty($canonicalUrl)): ?>
  <meta property="og:url"         content="<?= htmlspecialchars($canonicalUrl) ?>">
  <?php endif; ?>
  <?php endif; ?>

  <!-- highlight.js -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css" id="hljs-light-theme">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" id="hljs-dark-theme" disabled>

  <!-- Portfolio CSS -->
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/portfolio.css">

  <!-- Theme (no FOUC) -->
  <script src="<?= APP_URL ?>/assets/js/theme.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      function syncHljs() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        document.getElementById('hljs-light-theme').disabled = isDark;
        document.getElementById('hljs-dark-theme').disabled  = !isDark;
      }
      syncHljs();
      document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
        btn.addEventListener('click', () => setTimeout(syncHljs, 50));
      });
    });
  </script>
</head>
<body>

<!-- Site Header -->
<header class="site-header">
  <nav class="nav-container" role="navigation" aria-label="Main navigation">
    <a href="<?= APP_URL ?>/" class="nav-brand">
      <span class="nav-brand-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="16" height="16">
          <path stroke="white" stroke-width="2" fill="none" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
      </span>
      FAY Portfolio
    </a>

    <ul class="nav-links" role="list">
      <li><a href="<?= APP_URL ?>/" class="<?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>
      <li><a href="<?= APP_URL ?>/projects" class="<?= ($activeNav ?? '') === 'projects' ? 'active' : '' ?>">Projects</a></li>
    </ul>

    <div class="nav-actions">
      <button class="nav-theme-btn" data-theme-toggle aria-label="Toggle dark/light mode">
        <svg class="icon-sun" viewBox="0 0 16 16" style="display:block;fill:currentColor">
          <path d="M8 11a3 3 0 110-6 3 3 0 010 6zm0 1a4 4 0 100-8 4 4 0 000 8zM8 0a.5.5 0 01.5.5v2a.5.5 0 01-1 0v-2A.5.5 0 018 0zm0 13a.5.5 0 01.5.5v2a.5.5 0 01-1 0v-2A.5.5 0 018 13zm8-5a.5.5 0 01-.5.5h-2a.5.5 0 010-1h2a.5.5 0 01.5.5zM3 8a.5.5 0 01-.5.5h-2a.5.5 0 010-1h2A.5.5 0 013 8z"/>
        </svg>
        <svg class="icon-moon" viewBox="0 0 16 16" style="display:none;fill:currentColor">
          <path d="M6 .278a.768.768 0 01.08.858 7.208 7.208 0 00-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 01.81.316.733.733 0 01-.031.893A8.349 8.349 0 018.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 016 .278z"/>
        </svg>
      </button>
    </div>
  </nav>
</header>
