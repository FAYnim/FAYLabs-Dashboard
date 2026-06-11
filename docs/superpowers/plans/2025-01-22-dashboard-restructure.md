# Dashboard Restructure Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform this project from a landing page + CMS combo into a pure dashboard CMS module that deploys as the `/admin/` sub-folder inside a separate landing page project.

**Architecture:** All page files move from `admin/` to a flat `pages/` directory. Partials consolidate under `partials/`. Static assets move from `public/assets/` to root `assets/`. A `BASE_PATH` constant (empty for standalone, `/admin` for production) replaces all `APP_URL . '/../...'` URL patterns. The `.htaccess` rewrites clean URLs to `pages/`.

**Tech Stack:** PHP native, Bootstrap 5, jQuery, MySQL (PDO), .htaccess (Apache)

**Spec:** `docs/superpowers/specs/2025-01-22-dashboard-restructure-design.md`

---

### Task 1: Setup — Directories, Config, Auth, .htaccess

**Files:**
- Create: `pages/` (directory)
- Create: `partials/` (directory)
- Create: `assets/css/` (directory)
- Create: `assets/js/` (directory)
- Create: `assets/images/` (directory)
- Modify: `.env`
- Modify: `config/app.php`
- Modify: `includes/auth.php`
- Modify: `.htaccess`

- [ ] **Step 1: Create new directories**

```bash
mkdir -p pages partials assets/css assets/js assets/images
```

- [ ] **Step 2: Update `.env` — add BASE_PATH**

Add this line at the end of the existing `.env` file:

```env
BASE_PATH=
```

Leave it empty for standalone development. In production (as `/admin/` sub-folder), set it to `/admin`.

- [ ] **Step 3: Update `config/app.php` — add BASE_PATH constant**

Replace the entire file with:

```php
<?php
// ============================================================
// App Configuration
// ============================================================

// Load .env file
function loadEnv(string $path): void
{
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

loadEnv(ROOT_PATH . '/.env');

// App constants
define('APP_NAME', $_ENV['APP_NAME'] ?? 'FAY Labs');
define('APP_URL',  rtrim($_ENV['APP_URL'] ?? 'http://localhost/faydev/faylabs-dashboard', '/'));
define('APP_ENV',  $_ENV['APP_ENV']  ?? 'production');

// Base path prefix for all URLs
// Development standalone: '' (empty string)
// Production as sub-folder: '/admin'
define('BASE_PATH', rtrim($_ENV['BASE_PATH'] ?? '', '/'));
```

Key changes:
- Removed commented-out `ROOT_PATH` define (it's defined by each calling page)
- Updated `APP_URL` default to project root (no longer `/public`)
- Added `BASE_PATH` constant from env

- [ ] **Step 4: Update `includes/auth.php` — use BASE_PATH for redirects**

Replace the entire file with:

```php
<?php
// ============================================================
// Auth Helper — Session Protection
// ============================================================

function requireAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . BASE_PATH . '/login');
        exit;
    }
}

function isLoggedIn(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['admin_id']);
}

function loginAdmin(int $adminId): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $adminId;
}

function logoutAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
```

Key changes:
- `requireAdmin()` now uses `BASE_PATH . '/login'` instead of `getAdminBaseUrl() . '/login.php'`
- Removed `getAdminBaseUrl()` function entirely (no longer needed — `BASE_PATH` replaces it)

- [ ] **Step 5: Update `.htaccess` — new rewrite rules**

Replace the entire `.htaccess` with:

```apache
Options -Indexes
Options +FollowSymLinks

RewriteEngine On

# Skip rewrite for real files and directories
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Clean URLs for dashboard pages
RewriteRule ^login/?$ pages/login.php [L]
RewriteRule ^logout/?$ pages/logout.php [L]
RewriteRule ^create/?$ pages/create.php [L]
RewriteRule ^edit/?$ pages/edit.php [L]

# Root = dashboard
RewriteRule ^$ pages/index.php [L]
```

- [ ] **Step 6: Commit setup changes**

```bash
git add .env config/app.php includes/auth.php .htaccess
git commit -m "chore: add BASE_PATH config, update auth redirects, new .htaccess routing"
```

---

### Task 2: Create `pages/login.php`

**Files:**
- Create: `pages/login.php` (from `admin/login.php`)

- [ ] **Step 1: Create `pages/login.php`**

Copy from `admin/login.php` and replace with the following updated content. All `APP_URL` references replaced with `BASE_PATH`. ROOT_PATH adjusted for 1-level depth.

```php
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
```

- [ ] **Step 2: Commit**

```bash
git add pages/login.php
git commit -m "feat: add pages/login.php with BASE_PATH routing"
```

---

### Task 3: Create `pages/logout.php`

**Files:**
- Create: `pages/logout.php` (from `admin/logout.php`)

- [ ] **Step 1: Create `pages/logout.php`**

```php
<?php
// ============================================================
// Logout
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/includes/auth.php';

logoutAdmin();
header('Location: ' . BASE_PATH . '/login');
exit;
```

- [ ] **Step 2: Commit**

```bash
git add pages/logout.php
git commit -m "feat: add pages/logout.php with BASE_PATH redirect"
```

---

### Task 4: Create Partials

**Files:**
- Create: `partials/head.php` (from `admin/partials/head.php`)
- Create: `partials/footer.php` (from `admin/partials/footer.php`)
- Create: `partials/sidebar.php` (from `admin/partials/sidebar.php`)

- [ ] **Step 1: Create `partials/head.php`**

Replace all `APP_URL` with `BASE_PATH`. Add `<meta name="base-path">` for JS access.

```php
<?php
// Layout — <head> Partial
// Requires: $pageTitle, ROOT_PATH defined + config/app.php loaded
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
```

- [ ] **Step 2: Create `partials/footer.php`**

Replace all `APP_URL` with `BASE_PATH`.

```php
  <!-- jQuery (Cloudflare CDN) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- marked.js -->
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <!-- highlight.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
  <!-- DOMPurify -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.3/purify.min.js"></script>
  <!-- Admin JS -->
  <script src="<?= BASE_PATH ?>/assets/js/admin.js"></script>
  <?php if (isset($loadEditor) && $loadEditor): ?>
  <script src="<?= BASE_PATH ?>/assets/js/editor.js"></script>
  <?php endif; ?>

</body>
</html>
```

- [ ] **Step 3: Create `partials/sidebar.php`**

Replace all `APP_URL . '/../admin/...'` with `BASE_PATH . '/...'`. Remove "View Portfolio" section entirely.

```php
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

    <a href="<?= BASE_PATH ?>/"
       class="nav-item <?= ($activePage === 'projects') ? 'active' : '' ?>"
       id="nav-projects">
      <svg viewBox="0 0 16 16">
        <path d="M1.75 2.5a.25.25 0 000 .5h12.5a.25.25 0 000-.5H1.75zM1.75 6.5a.25.25 0 000 .5h12.5a.25.25 0 000-.5H1.75zM1.5 10.75a.25.25 0 01.25-.25h8.5a.25.25 0 010 .5h-8.5a.25.25 0 01-.25-.25z"/>
      </svg>
      Projects
    </a>

    <a href="<?= BASE_PATH ?>/create"
       class="nav-item <?= ($activePage === 'create') ? 'active' : '' ?>"
       id="nav-create">
      <svg viewBox="0 0 16 16">
        <path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/>
      </svg>
      New Project
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

    <a href="<?= BASE_PATH ?>/logout" class="nav-item" id="nav-logout"
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
```

- [ ] **Step 4: Commit**

```bash
git add partials/
git commit -m "feat: add partials (head, footer, sidebar) with BASE_PATH"
```

---

### Task 5: Create `pages/index.php` — Dashboard Project List

**Files:**
- Create: `pages/index.php` (from `admin/projects/index.php`)

- [ ] **Step 1: Create `pages/index.php`**

Key changes from original `admin/projects/index.php`:
- `ROOT_PATH` = `dirname(__DIR__)` (1 level, not 2)
- All `APP_URL . '/../admin/...'` → `BASE_PATH . '/...'`
- `APP_URL . '/../api'` → `BASE_PATH . '/api'`
- Partial includes use `ROOT_PATH . '/partials/...'` instead of `/admin/partials/...`
- FAY_CONFIG uses `BASE_PATH`

```php
<?php
// ============================================================
// Dashboard — Projects List
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();
requireAdmin();

$pageTitle  = 'Projects';
$activePage = 'projects';
$csrfToken  = csrfGenerate();

// Fetch projects
try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->query(
        "SELECT id, title, slug, description, cover_image, label, status, created_at, published_at
         FROM projects ORDER BY created_at DESC"
    );
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    $projects = [];
    $dbError  = 'Failed to load projects.';
}

require_once ROOT_PATH . '/partials/head.php';
?>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
  <div class="modal-box">
    <div class="modal-icon" aria-hidden="true">
      <svg viewBox="0 0 16 16"><path d="M11 1.75V3h2.25a.75.75 0 010 1.5H2.75a.75.75 0 010-1.5H5V1.75C5 .784 5.784 0 6.75 0h2.5C10.216 0 11 .784 11 1.75zm-4.5 0V3h3V1.75a.25.25 0 00-.25-.25h-2.5a.25.25 0 00-.25.25zM4.997 6.75a.75.75 0 00-.497.732l.5 6.5A.75.75 0 005.75 14.5h4.5a.75.75 0 00.75-.518l.5-6.5a.75.75 0 00-1.497-.116l-.44 5.714H7.443l-.44-5.714a.75.75 0 00-.756-.116z" fill-rule="evenodd"/></svg>
    </div>
    <h2 class="modal-title" id="delete-modal-title">Delete Project?</h2>
    <p class="modal-message">Are you sure you want to delete "<strong id="delete-modal-name"></strong>"?</p>
    <p class="modal-warning">This action cannot be undone. The cover image will also be removed from Cloudinary.</p>
    <div class="modal-actions">
      <button id="delete-cancel-btn" class="btn btn-secondary">Cancel</button>
      <button id="delete-confirm-btn" class="btn btn-danger" style="background:var(--danger);color:#fff;border-color:var(--danger);">
        Delete Project
      </button>
    </div>
  </div>
</div>

<!-- Global JS Config -->
<script>
  const FAY_CONFIG = {
    apiBase:   '<?= BASE_PATH ?>/api',
    adminBase: '<?= BASE_PATH ?>',
    csrfToken: '<?= htmlspecialchars($csrfToken) ?>',
  };
</script>

<?php $activePage = 'projects'; require_once ROOT_PATH . '/partials/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-wrapper">
  <!-- Topbar -->
  <header class="topbar">
    <button id="sidebar-toggle" class="topbar-hamburger" aria-label="Toggle sidebar">
      <svg viewBox="0 0 16 16"><path d="M1 2.75A.75.75 0 011.75 2h12.5a.75.75 0 010 1.5H1.75A.75.75 0 011 2.75zm0 5A.75.75 0 011.75 7h12.5a.75.75 0 010 1.5H1.75A.75.75 0 011 7.75zM1.75 12a.75.75 0 000 1.5h12.5a.75.75 0 000-1.5H1.75z"/></svg>
    </button>
    <span class="topbar-title">Projects</span>
    <div class="topbar-actions">
      <a href="<?= BASE_PATH ?>/create" class="btn btn-primary">
        <svg viewBox="0 0 16 16"><path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/></svg>
        New Project
      </a>
    </div>
  </header>

  <!-- Page Content -->
  <main class="page-content">
    <div class="page-header">
      <h2>Projects</h2>
      <p>Manage your portfolio projects.</p>
    </div>

    <?php if (isset($dbError)): ?>
    <div class="alert alert-danger">
      <svg viewBox="0 0 16 16" width="16" height="16" fill="currentColor">
        <path d="M8 1a7 7 0 100 14A7 7 0 008 1zm1 10.5a1 1 0 11-2 0 1 1 0 012 0zm-.25-4.75a.75.75 0 00-1.5 0v3a.75.75 0 001.5 0v-3z"/>
      </svg>
      <?= e($dbError) ?>
    </div>
    <?php endif; ?>

    <?php if (empty($projects)): ?>
    <!-- Empty State -->
    <div class="empty-state">
      <div class="empty-state-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24">
          <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
        </svg>
      </div>
      <h3>No projects yet.</h3>
      <p>Start by creating your first portfolio project.</p>
      <a href="<?= BASE_PATH ?>/create" class="btn btn-primary" style="margin-top:16px;">
        <svg viewBox="0 0 16 16"><path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/></svg>
        Create First Project
      </a>
    </div>

    <?php else: ?>
    <!-- Projects Grid -->
    <div id="projects-grid" class="projects-grid">
      <?php foreach ($projects as $p): ?>
      <article id="project-card-<?= $p['id'] ?>" class="project-card" style="display:flex;flex-direction:column;">
        <!-- Cover Image -->
        <?php if ($p['cover_image']): ?>
          <img src="<?= e($p['cover_image']) ?>"
               alt="<?= e($p['title']) ?>"
               class="project-card-img"
               loading="lazy">
        <?php else: ?>
          <div class="project-card-img-placeholder" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
          </div>
        <?php endif; ?>

        <div class="project-card-body">
          <!-- Meta -->
          <div class="project-card-meta">
            <span class="label-badge <?= getLabelClass($p['label']) ?>">
              <?= e($p['label']) ?>
            </span>
            <span class="status-badge <?= getStatusClass($p['status']) ?>">
              <?= ucfirst(e($p['status'])) ?>
            </span>
          </div>

          <!-- Title & Description -->
          <div class="project-card-title"><?= e($p['title']) ?></div>
          <div class="project-card-desc"><?= e($p['description']) ?></div>

          <!-- Actions -->
          <div class="project-card-actions">
            <a href="<?= BASE_PATH ?>/edit?id=<?= $p['id'] ?>"
               class="btn btn-primary btn-sm">
              <svg viewBox="0 0 16 16"><path d="M11.013 1.427a1.75 1.75 0 012.474 0l1.086 1.086a1.75 1.75 0 010 2.474l-8.61 8.61c-.21.21-.47.364-.756.445l-3.251.93a.75.75 0 01-.927-.928l.929-3.25c.081-.286.235-.547.445-.758l8.61-8.61zm.176 4.823L9.75 4.81l-6.286 6.287a.253.253 0 00-.064.108l-.558 1.953 1.953-.558a.253.253 0 00.108-.064l6.286-6.286zm1.238-3.763a.25.25 0 00-.354 0L10.811 3.75l1.439 1.44 1.263-1.263a.25.25 0 000-.354l-1.086-1.086z"/></svg>
              Edit
            </a>

            <button class="btn btn-danger btn-sm btn-delete-project"
                    data-id="<?= $p['id'] ?>"
                    data-title="<?= e($p['title']) ?>"
                    aria-label="Delete <?= e($p['title']) ?>">
              <svg viewBox="0 0 16 16"><path d="M11 1.75V3h2.25a.75.75 0 010 1.5H2.75a.75.75 0 010-1.5H5V1.75C5 .784 5.784 0 6.75 0h2.5C10.216 0 11 .784 11 1.75zM4.496 6.675a.75.75 0 10-1.492.15l.66 6.6A1.75 1.75 0 005.405 15h5.19c.9 0 1.652-.681 1.741-1.576l.66-6.6a.75.75 0 00-1.492-.149l-.66 6.6a.25.25 0 01-.249.225h-5.19a.25.25 0 01-.249-.225l-.66-6.6z"/></svg>
              Delete
            </button>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </main>
</div>

<!-- Toast container -->
<div id="toast-container" class="toast-container" aria-live="polite"></div>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
```

Key changes from original:
- Removed "View" button from project card actions (no public pages in this project)
- `FAY_CONFIG.adminBase` = `BASE_PATH` (was `APP_URL . '/../admin'`)
- `FAY_CONFIG.apiBase` = `BASE_PATH . '/api'` (was `APP_URL . '/../api'`)
- All links use `BASE_PATH` instead of `APP_URL . '/../admin/...'`

- [ ] **Step 2: Commit**

```bash
git add pages/index.php
git commit -m "feat: add pages/index.php (dashboard project list)"
```

---

### Task 6: Create `pages/create.php` — Create Project Form

**Files:**
- Create: `pages/create.php` (from `admin/projects/create.php`)

- [ ] **Step 1: Read the original file to get exact content**

Read `admin/projects/create.php` to get the full original content, then create `pages/create.php` with these specific substitutions applied throughout the file:

1. `define('ROOT_PATH', dirname(__DIR__, 2))` → `define('ROOT_PATH', dirname(__DIR__))`
2. All `APP_URL . '/../admin/projects/index.php'` → `BASE_PATH . '/'`
3. All `APP_URL . '/../admin/projects/create.php'` → `BASE_PATH . '/create'`
4. `APP_URL . '/../api'` → `BASE_PATH . '/api'` in FAY_CONFIG
5. `APP_URL . '/../admin'` → `BASE_PATH` in FAY_CONFIG
6. `require_once ROOT_PATH . '/admin/partials/head.php'` → `require_once ROOT_PATH . '/partials/head.php'`
7. `require_once ROOT_PATH . '/admin/partials/sidebar.php'` → `require_once ROOT_PATH . '/partials/sidebar.php'`
8. `require_once ROOT_PATH . '/admin/partials/footer.php'` → `require_once ROOT_PATH . '/partials/footer.php'`
9. In the inline `<script>` at bottom, `FAY_CONFIG.adminBase + '/projects/index.php?success='` → `FAY_CONFIG.adminBase + '/?success='`
10. All `<a href="...APP_URL.../admin/...">` → `<a href="...BASE_PATH.../...">`

Create the file with these substitutions applied to the original content.

- [ ] **Step 2: Verify no remaining APP_URL or /admin/ references**

```bash
grep -n "APP_URL\|/admin/" pages/create.php
```

Expected: no matches (all replaced with BASE_PATH)

- [ ] **Step 3: Commit**

```bash
git add pages/create.php
git commit -m "feat: add pages/create.php (create project form)"
```

---

### Task 7: Create `pages/edit.php` — Edit Project Form

**Files:**
- Create: `pages/edit.php` (from `admin/projects/edit.php`)

- [ ] **Step 1: Create `pages/edit.php` with updated paths**

Read `admin/projects/edit.php` to get the full original content, then create `pages/edit.php` with these specific substitutions applied throughout the file:

1. `define('ROOT_PATH', dirname(__DIR__, 2))` → `define('ROOT_PATH', dirname(__DIR__))`
2. All `APP_URL . '/../admin/projects/index.php'` → `BASE_PATH . '/'`
3. All `APP_URL . '/../admin/projects/edit.php'` → `BASE_PATH . '/edit'`
4. `APP_URL . '/../api'` → `BASE_PATH . '/api'` in FAY_CONFIG
5. `APP_URL . '/../admin'` → `BASE_PATH` in FAY_CONFIG
6. `require_once ROOT_PATH . '/admin/partials/head.php'` → `require_once ROOT_PATH . '/partials/head.php'`
7. `require_once ROOT_PATH . '/admin/partials/sidebar.php'` → `require_once ROOT_PATH . '/partials/sidebar.php'`
8. `require_once ROOT_PATH . '/admin/partials/footer.php'` → `require_once ROOT_PATH . '/partials/footer.php'`
9. In the inline `<script>` at bottom, `FAY_CONFIG.adminBase + '/projects/index.php?success='` → `FAY_CONFIG.adminBase + '/?success='`
10. Remove the "View Live" link in the topbar (no public detail page in this project):
    ```php
    <?php if ($project['status'] === 'published'): ?>
    <a href="..." target="_blank" class="btn btn-secondary btn-sm">View Live</a>
    <?php endif; ?>
    ```
    Delete this entire block.

Create the file with these substitutions applied.

- [ ] **Step 2: Verify no remaining APP_URL or /admin/ references**

```bash
grep -n "APP_URL\|/admin/" pages/edit.php
```

Expected: no matches

- [ ] **Step 3: Commit**

```bash
git add pages/edit.php
git commit -m "feat: add pages/edit.php (edit project form)"
```

---

### Task 8: Move Static Assets

**Files:**
- Create: `assets/css/admin.css` (copy from `public/assets/css/admin.css`)
- Create: `assets/js/admin.js` (copy from `public/assets/js/admin.js`)
- Create: `assets/js/editor.js` (copy from `public/assets/js/editor.js`)
- Create: `assets/js/theme.js` (copy from `public/assets/js/theme.js`)
- Create: `assets/images/` (copy from `public/assets/images/` if any files exist)

- [ ] **Step 1: Copy admin.css**

```bash
cp public/assets/css/admin.css assets/css/admin.css
```

- [ ] **Step 2: Copy JS files**

```bash
cp public/assets/js/admin.js assets/js/admin.js
cp public/assets/js/editor.js assets/js/editor.js
cp public/assets/js/theme.js assets/js/theme.js
```

- [ ] **Step 3: Update `assets/js/admin.js` — fix FAY_CONFIG URL references**

In `assets/js/admin.js`, the `checkEmpty()` function builds a link using `FAY_CONFIG.adminBase`. Update line 152:

Find:
```javascript
<a href="${FAY_CONFIG.adminBase}/projects/create.php" class="btn btn-primary mt-3">
```

Replace with:
```javascript
<a href="${FAY_CONFIG.adminBase}/create" class="btn btn-primary mt-3">
```

- [ ] **Step 4: Copy images (if any)**

```bash
cp -r public/assets/images/* assets/images/ 2>/dev/null || echo "No images to copy"
```

- [ ] **Step 5: Commit**

```bash
git add assets/
git commit -m "feat: move static assets to root assets/ directory"
```

---

### Task 9: Delete Old Files

**Files to delete:**
- `admin/` (entire directory)
- `public/` (entire directory)
- `api/public/latest-projects.php`

- [ ] **Step 1: Delete the `admin/` directory**

```bash
rm -rf admin/
```

- [ ] **Step 2: Delete the `public/` directory**

```bash
rm -rf public/
```

- [ ] **Step 3: Delete `api/public/latest-projects.php`**

```bash
rm api/public/latest-projects.php
```

- [ ] **Step 4: Commit deletions**

```bash
git add -A
git commit -m "chore: remove landing page, admin/ folder, and unused public API endpoint"
```

---

### Task 10: Verify — Smoke Test

- [ ] **Step 1: Verify folder structure**

```bash
find . -not -path './.git/*' -not -path './docs/*' -not -name '.git' | sort
```

Expected output:
```
.
./.env
./.gitignore
./.htaccess
./PRD.md
./api
./api/auth
./api/auth/login.php
./api/auth/logout.php
./api/projects
./api/projects/create.php
./api/projects/delete.php
./api/projects/index.php
./api/projects/show.php
./api/projects/update.php
./api/public
./api/public/load-projects.php
./api/public/project-detail.php
./api/uploads
./api/uploads/cover.php
./assets
./assets/css
./assets/css/admin.css
./assets/images
./assets/js
./assets/js/admin.js
./assets/js/editor.js
./assets/js/theme.js
./config
./config/app.php
./config/cloudinary.php
./config/database.php
./database
./database/schema.sql
./database/seed-admin.php
./includes
./includes/auth.php
./includes/csrf.php
./includes/helpers.php
./includes/response.php
./includes/validator.php
./pages
./pages/create.php
./pages/edit.php
./pages/index.php
./pages/login.php
./pages/logout.php
./partials
./partials/footer.php
./partials/head.php
./partials/sidebar.php
```

- [ ] **Step 2: Verify no broken references remain**

Search for any remaining references to old paths:

```bash
grep -rn "APP_URL" pages/ partials/ includes/ config/app.php
grep -rn "/admin/" pages/ partials/ includes/
grep -rn "public/assets" pages/ partials/
```

Expected: no matches in any of these searches.

- [ ] **Step 3: Test login page loads in browser**

Open `http://localhost/faydev/faylabs-dashboard/login` in browser.

Expected: Login page renders correctly with FAY Labs branding, CSS loaded, theme toggle works.

- [ ] **Step 4: Test unauthenticated redirect**

Open `http://localhost/faydev/faylabs-dashboard/` in browser (not logged in).

Expected: Redirected to `/login` page.

- [ ] **Step 5: Test login flow**

1. Enter valid credentials on login page
2. Submit form
3. Expected: Redirected to `/` (dashboard project list)

- [ ] **Step 6: Test dashboard loads**

After login, verify:
- Project list page renders at `/`
- Sidebar shows Projects and New Project (no "View Portfolio")
- Theme toggle works
- Logout link goes to `/logout`

- [ ] **Step 7: Test create page**

Navigate to `/create`. Verify:
- Form renders correctly
- Markdown editor toolbar works
- Cover upload area visible
- Cancel button links back to `/`

- [ ] **Step 8: Test edit page**

Navigate to `/edit?id=1` (if a project exists). Verify:
- Form pre-populates with project data
- Cover image displays
- Back button links to `/`

- [ ] **Step 9: Final commit (if any fixes were needed)**

```bash
git add -A
git commit -m "fix: resolve any issues found during smoke test"
```
