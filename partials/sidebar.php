<?php
// Sidebar Partial
// Requires: $activePage, ROOT_PATH already defined + config/app.php loaded
?>
<!-- Sidebar Overlay (mobile) -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<!-- Sidebar -->
<aside id="admin-sidebar" class="sidebar" role="navigation" aria-label="Admin navigation">
  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-icon-sm" aria-hidden="true" style="background: transparent;">
      <img src="<?= BASE_PATH ?>/assets/favicon/favicon-32x32.png" alt="FAY Labs Logo" style="width: 32px; height: 32px; border-radius: 8px;">
    </div>
    <span class="brand-name">FAY Labs</span>
  </div>

  <!-- Nav -->
  <nav class="sidebar-nav">
    <div class="nav-section-label">Content</div>

    <a href="<?= BASE_PATH ?>/"
       class="nav-item <?= ($activePage === 'projects') ? 'active' : '' ?>"
       id="nav-projects">
      <i class="bi bi-files" aria-hidden="true"></i>
      Projects
    </a>

    <a href="<?= BASE_PATH ?>/create"
       class="nav-item <?= ($activePage === 'create') ? 'active' : '' ?>"
       id="nav-create">
      <i class="bi bi-plus-circle" aria-hidden="true"></i>
      New Project
    </a>

    <a href="<?= BASE_PATH ?>/emails"
       class="nav-item <?= ($activePage === 'emails') ? 'active' : '' ?>"
       id="nav-emails">
      <i class="bi bi-envelope" aria-hidden="true"></i>
      Emails
    </a>
  </nav>

  <!-- Footer -->
  <div class="sidebar-footer">
    <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme">
      <i class="bi bi-sun-fill icon-sun" style="display:block" aria-hidden="true"></i>
      <i class="bi bi-moon-stars-fill icon-moon" style="display:none" aria-hidden="true"></i>
      <span class="theme-label">Dark Mode</span>
      <span class="toggle-switch" aria-hidden="true"></span>
    </button>

    <a href="<?= BASE_PATH ?>/logout" class="nav-item" id="nav-logout"
       style="color: var(--danger);"
       onclick="return confirm('Are you sure you want to logout?')">
      <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
      Logout
    </a>
  </div>
</aside>
