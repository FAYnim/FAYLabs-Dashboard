<?php
// Admin Sidebar Partial
// Requires: $activePage, ROOT_PATH already defined + config/app.php loaded
$adminBase = APP_URL . '/../admin';
?>
<!-- Sidebar Overlay (mobile) -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<!-- Sidebar -->
<aside id="admin-sidebar" class="sidebar" role="navigation" aria-label="Admin navigation">
  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="brand-icon-sm" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="18" height="18">
        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
      </svg>
    </div>
    <span class="brand-name">FAY Labs</span>
  </div>

  <!-- Nav -->
  <nav class="sidebar-nav">
    <div class="nav-section-label">Content</div>

    <a href="<?= APP_URL ?>/../admin/projects/index.php"
       class="nav-item <?= ($activePage === 'projects') ? 'active' : '' ?>"
       id="nav-projects">
      <svg viewBox="0 0 16 16">
        <path d="M1.75 2.5a.25.25 0 000 .5h12.5a.25.25 0 000-.5H1.75zM1.75 6.5a.25.25 0 000 .5h12.5a.25.25 0 000-.5H1.75zM1.5 10.75a.25.25 0 01.25-.25h8.5a.25.25 0 010 .5h-8.5a.25.25 0 01-.25-.25z"/>
      </svg>
      Projects
    </a>

    <a href="<?= APP_URL ?>/../admin/projects/create.php"
       class="nav-item <?= ($activePage === 'create') ? 'active' : '' ?>"
       id="nav-create">
      <svg viewBox="0 0 16 16">
        <path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/>
      </svg>
      New Project
    </a>

    <div class="nav-section-label" style="margin-top:12px;">Portfolio</div>

    <a href="<?= APP_URL ?>/projects" target="_blank" class="nav-item" id="nav-view-portfolio">
      <svg viewBox="0 0 16 16">
        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
      </svg>
      View Portfolio
      <svg viewBox="0 0 12 12" width="10" height="10" style="margin-left:auto;opacity:.5">
        <path d="M3.5 1a.5.5 0 000 1h5.793L1.146 10.146a.5.5 0 00.707.707L10 2.707V8.5a.5.5 0 001 0v-7A.5.5 0 0010.5 1h-7z"/>
      </svg>
    </a>
  </nav>

  <!-- Footer -->
  <div class="sidebar-footer">
    <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme">
      <svg class="icon-sun" viewBox="0 0 16 16" style="display:block">
        <path d="M8 11a3 3 0 110-6 3 3 0 010 6zm0 1a4 4 0 100-8 4 4 0 000 8zM8 0a.5.5 0 01.5.5v2a.5.5 0 01-1 0v-2A.5.5 0 018 0zm0 13a.5.5 0 01.5.5v2a.5.5 0 01-1 0v-2A.5.5 0 018 13zm8-5a.5.5 0 01-.5.5h-2a.5.5 0 010-1h2a.5.5 0 01.5.5zM3 8a.5.5 0 01-.5.5h-2a.5.5 0 010-1h2A.5.5 0 013 8zm10.657-5.657a.5.5 0 010 .707l-1.414 1.415a.5.5 0 11-.707-.708l1.414-1.414a.5.5 0 01.707 0zm-9.193 9.193a.5.5 0 010 .707L3.05 13.657a.5.5 0 01-.707-.707l1.414-1.414a.5.5 0 01.707 0zm9.193 2.121a.5.5 0 01-.707 0l-1.414-1.414a.5.5 0 01.707-.707l1.414 1.414a.5.5 0 010 .707zM4.464 4.465a.5.5 0 01-.707 0L2.343 3.05a.5.5 0 11.707-.707l1.414 1.414a.5.5 0 010 .707z"/>
      </svg>
      <svg class="icon-moon" viewBox="0 0 16 16" style="display:none">
        <path d="M6 .278a.768.768 0 01.08.858 7.208 7.208 0 00-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 01.81.316.733.733 0 01-.031.893A8.349 8.349 0 018.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 016 .278z"/>
      </svg>
      <span class="theme-label">Dark Mode</span>
      <span class="toggle-switch" aria-hidden="true"></span>
    </button>

    <a href="<?= APP_URL ?>/../admin/logout.php" class="nav-item" id="nav-logout"
       style="color: var(--danger);"
       onclick="return confirm('Are you sure you want to logout?')">
      <svg viewBox="0 0 16 16">
        <path d="M10 12.5a.5.5 0 01-.5.5h-8a.5.5 0 01-.5-.5v-9a.5.5 0 01.5-.5h8a.5.5 0 01.5.5v2a.5.5 0 001 0v-2A1.5 1.5 0 009.5 2h-8A1.5 1.5 0 000 3.5v9A1.5 1.5 0 001.5 14h8a1.5 1.5 0 001.5-1.5v-2a.5.5 0 00-1 0v2z"/>
        <path d="M15.854 8.354a.5.5 0 000-.708l-3-3a.5.5 0 00-.708.708L14.293 7.5H5.5a.5.5 0 000 1h8.793l-2.147 2.146a.5.5 0 00.708.708l3-3z"/>
      </svg>
      Logout
    </a>
  </div>
</aside>
