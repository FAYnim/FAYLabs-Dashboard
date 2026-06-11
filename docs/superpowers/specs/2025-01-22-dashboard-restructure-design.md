# Design: Restructure to Pure Dashboard CMS

## Overview

Transform the faylabs-dashboard project from a landing page + CMS combo into a pure dashboard CMS module. This project will be deployed as the `/admin/` sub-folder inside a separate landing page project.

All public-facing pages (homepage, projects list, project detail) are removed. The project focuses exclusively on admin dashboard functionality: login, project CRUD, and API endpoints.

## Goals

1. Remove all landing page and public page files.
2. Restructure the folder layout for a clean, focused dashboard module.
3. Introduce clean URLs without the `/admin/` prefix in file paths.
4. Make the project portable between standalone development and production sub-folder deployment via a `BASE_PATH` constant.

## Final Folder Structure

```
faylabs-dashboard/
│
├── .env
├── .gitignore
├── .htaccess
├── PRD.md
│
├── config/
│   ├── app.php
│   ├── database.php
│   └── cloudinary.php
│
├── includes/
│   ├── auth.php
│   ├── csrf.php
│   ├── helpers.php
│   ├── response.php
│   └── validator.php
│
├── database/
│   ├── schema.sql
│   └── seed-admin.php
│
├── pages/
│   ├── index.php           ← / (project list, auth required)
│   ├── login.php           ← /login
│   ├── logout.php          ← /logout
│   ├── create.php          ← /create (auth required)
│   └── edit.php            ← /edit?id={id} (auth required)
│
├── partials/
│   ├── head.php            ← Admin <head> section
│   ├── footer.php          ← Admin footer (scripts)
│   └── sidebar.php         ← Admin sidebar navigation
│
├── api/
│   ├── auth/
│   │   ├── login.php
│   │   └── logout.php
│   ├── projects/
│   │   ├── index.php
│   │   ├── show.php
│   │   ├── create.php
│   │   ├── update.php
│   │   └── delete.php
│   ├── public/
│   │   ├── load-projects.php
│   │   └── project-detail.php
│   └── uploads/
│       └── cover.php
│
└── assets/
    ├── css/
    │   └── admin.css
    ├── js/
    │   ├── admin.js
    │   ├── editor.js
    │   └── theme.js
    └── images/
```

## Files to Delete

| File/Folder | Reason |
|-------------|--------|
| `public/index.php` | Homepage hero section — handled by landing page |
| `public/projects/` | Public projects list and detail — handled by landing page |
| `public/partials/` | Public head/footer — no longer needed |
| `public/assets/css/portfolio.css` | Public styles — no longer needed |
| `public/assets/js/projects.js` | Public Load More JS — no longer needed |
| `public/.htaccess` | Public routing — no longer needed |
| `api/public/latest-projects.php` | Only used by homepage hero section |

## Files to Move

| From | To |
|------|-----|
| `admin/login.php` | `pages/login.php` |
| `admin/logout.php` | `pages/logout.php` |
| `admin/projects/index.php` | `pages/index.php` |
| `admin/projects/create.php` | `pages/create.php` |
| `admin/projects/edit.php` | `pages/edit.php` |
| `admin/partials/head.php` | `partials/head.php` |
| `admin/partials/footer.php` | `partials/footer.php` |
| `admin/partials/sidebar.php` | `partials/sidebar.php` |
| `public/assets/css/admin.css` | `assets/css/admin.css` |
| `public/assets/js/admin.js` | `assets/js/admin.js` |
| `public/assets/js/editor.js` | `assets/js/editor.js` |
| `public/assets/js/theme.js` | `assets/js/theme.js` |
| `public/assets/images/` | `assets/images/` |

## Folders to Remove (after moving contents)

- `admin/` (entire folder, after files moved to `pages/` and `partials/`)
- `public/` (entire folder, after assets moved and pages deleted)

## Routing

### .htaccess

Root `.htaccess` for clean URLs:

```apache
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

### URL Mapping

| URL (Production) | File | Auth Required |
|-------------------|------|---------------|
| `/admin/` | `pages/index.php` | Yes |
| `/admin/login` | `pages/login.php` | No |
| `/admin/logout` | `pages/logout.php` | Yes |
| `/admin/create` | `pages/create.php` | Yes |
| `/admin/edit?id=N` | `pages/edit.php` | Yes |
| `/admin/api/...` | Direct file access | Varies |
| `/admin/assets/...` | Direct file access | No |

## Code Changes

### BASE_PATH Constant

Add to `config/app.php`:

```php
// Development: '' (empty string — project at root)
// Production: '/admin' (project as sub-folder in landing page)
define('BASE_PATH', '/admin');
```

All URLs in PHP, HTML, and JavaScript use `BASE_PATH` as prefix.

### Include/Require Paths

Files in `pages/` are at the same directory depth as the old `admin/` folder (1 level from root). Relative paths to `config/` and `includes/` remain unchanged:

```php
require_once '../includes/auth.php';    // same
require_once '../config/app.php';       // same
```

Partial includes update:

```php
// Before (from admin/projects/index.php):
require_once '../partials/head.php';  // was admin/partials/

// After (from pages/index.php):
require_once '../partials/head.php';  // now root partials/
```

### PHP Redirects

All `header('Location: ...')` use `BASE_PATH`:

```php
// Login success → dashboard
header('Location: ' . BASE_PATH . '/');

// Auth guard (requireAdmin) → login
header('Location: ' . BASE_PATH . '/login');

// Logout → login
header('Location: ' . BASE_PATH . '/login');

// After create/edit → dashboard with success message
header('Location: ' . BASE_PATH . '/?success=created');
```

### HTML Asset Paths

All CSS and JS references use `BASE_PATH`:

```php
<!-- partials/head.php -->
<link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/admin.css">

<!-- partials/footer.php -->
<script src="<?= BASE_PATH ?>/assets/js/admin.js"></script>
<script src="<?= BASE_PATH ?>/assets/js/theme.js"></script>
```

### JavaScript API URLs

Expose `BASE_PATH` to JavaScript via a meta tag in `partials/head.php`:

```html
<meta name="base-path" content="<?= BASE_PATH ?>">
```

JavaScript reads it for API calls:

```javascript
const BASE_PATH = document.querySelector('meta[name="base-path"]').content;

$.post(`${BASE_PATH}/api/projects/create.php`, formData);
$.get(`${BASE_PATH}/api/projects/show.php?id=${id}`);
$.ajax({ url: `${BASE_PATH}/api/uploads/cover.php`, ... });
```

### Sidebar Navigation

Update `partials/sidebar.php`:

- "Projects" link → `BASE_PATH . '/'`
- "New Project" link → `BASE_PATH . '/create'`
- Remove "View Portfolio" link entirely (no public pages in this project)
- Theme toggle and Logout remain unchanged

## API Endpoints

### Removed

| Endpoint | Reason |
|----------|--------|
| `api/public/latest-projects.php` | Only used by homepage (deleted) |

### Retained — Admin (auth required)

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `api/auth/login.php` | POST | Session login |
| `api/auth/logout.php` | POST | Session destroy |
| `api/projects/index.php` | GET | List all projects |
| `api/projects/show.php` | GET | Single project by ID |
| `api/projects/create.php` | POST | Create project |
| `api/projects/update.php` | PATCH | Update project |
| `api/projects/delete.php` | DELETE | Delete project |
| `api/uploads/cover.php` | POST | Upload cover to Cloudinary |

### Retained — Public (no auth)

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `api/public/load-projects.php` | GET | Paginated projects for landing page |
| `api/public/project-detail.php` | GET | Project detail by slug for landing page |

No changes to API logic. Auth checks, validation, and response format remain identical.

## Unchanged Components

These components require no modifications:

- `config/database.php` — PDO connection
- `config/cloudinary.php` — Cloudinary upload/delete
- `includes/auth.php` — Session management (requireAdmin, isLoggedIn)
- `includes/csrf.php` — CSRF token generation and verification
- `includes/helpers.php` — Utility functions
- `includes/response.php` — JSON response helpers
- `includes/validator.php` — Form validation
- `database/schema.sql` — Table definitions
- `database/seed-admin.php` — Admin user seeder
- All API endpoint logic (CRUD, validation, responses)
