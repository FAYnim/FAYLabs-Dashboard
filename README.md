# FAYLabs Dashboard

A lightweight portfolio project CMS dashboard built with native PHP, MySQL, Bootstrap, Bootstrap Icons, jQuery, and Cloudinary.

## Overview

FAYLabs Dashboard helps a single admin manage portfolio projects for a public portfolio site. It supports creating projects, editing metadata, saving drafts, publishing projects, uploading cover images, and writing project detail content in GitHub-flavored Markdown.

## Features

- Admin login and logout with PHP sessions
- Protected dashboard pages
- Project CRUD workflow
- Draft and published project statuses
- Cloudinary cover image upload
- Markdown editor and live preview
- Public project listing and detail pages
- Light and dark mode support
- Responsive dashboard layout
- JSON API endpoints for admin and public project data

## Tech Stack

| Layer | Technology |
| --- | --- |
| Frontend | HTML5, CSS3, Bootstrap 5, Bootstrap Icons, Vanilla JavaScript, jQuery |
| Backend | PHP Native |
| Database | MySQL |
| Markdown | marked.js, highlight.js, DOMPurify |
| Image Storage | Cloudinary |
| Authentication | PHP Session, password_hash(), password_verify() |

## Project Structure

```txt
api/
├── auth/
│   ├── login.php              # Handles admin login requests.
│   └── logout.php             # Handles admin logout requests.
├── projects/
│   ├── index.php              # Returns the admin project list.
│   ├── show.php               # Returns a single project by ID.
│   ├── create.php             # Creates a new project record.
│   ├── update.php             # Updates an existing project record.
│   └── delete.php             # Deletes a project record and related cover data.
├── public/
│   ├── load-projects.php      # Returns paginated published projects for public pages.
│   └── project-detail.php     # Returns public project detail data by slug.
└── uploads/
    └── cover.php              # Uploads project cover images to Cloudinary.

assets/
├── css/
│   └── admin.css              # Dashboard styles, layout, responsive rules, and themes.
└── js/
    ├── admin.js               # Dashboard interactions and project management UI logic.
    ├── editor.js              # Markdown editor, preview, and toolbar behavior.
    └── theme.js               # Light and dark mode toggle handling.

config/
├── app.php                    # App-level configuration and environment loading.
├── cloudinary.php             # Cloudinary configuration and upload helpers.
└── database.php               # MySQL PDO connection configuration.

database/
├── schema.sql                 # Database table definitions.
└── seed-admin.php             # Initial admin account seeding script.

includes/
├── auth.php                   # Session authentication guards and auth helpers.
├── csrf.php                   # CSRF token generation and validation helpers.
├── helpers.php                # Shared utility functions.
├── response.php               # JSON response helpers.
└── validator.php              # Request validation helpers.

pages/
├── login.php                  # Admin login page.
├── logout.php                 # Admin logout page.
├── index.php                  # Admin project list page.
├── create.php                 # Project creation page.
└── edit.php                   # Project editing page.

partials/
├── head.php                   # Shared document head and asset includes.
├── sidebar.php                # Shared dashboard sidebar navigation.
└── footer.php                 # Shared footer scripts and closing layout.

docs/
└── superpowers/               # Planning and specification documents.

.env.example                   # Example environment variable template.
.htaccess                      # Apache rewrite and routing rules.
PRD.md                         # Product requirements and implementation plan.
README.md                      # Project documentation.
```

## Main Routes

### Admin Pages

```txt
/pages/login.php
/pages/index.php
/pages/create.php
/pages/edit.php?id={id}
/pages/logout.php
```

### API Endpoints

```txt
/api/projects
/api/projects/:id
/api/public/latest-projects.php
/api/public/load-projects.php
/api/public/project-detail.php
/api/uploads/cover.php
```

## Environment Variables

Create a local `.env` file based on `.env.example` and configure the required database and Cloudinary credentials.

```txt
CLOUDINARY_CLOUD_NAME
CLOUDINARY_API_KEY
CLOUDINARY_API_SECRET
```

Do not commit `.env` or expose Cloudinary API secrets in frontend code.

## Database

The database schema is stored in:

```txt
database/schema.sql
```

Use the seed script to create the initial admin account:

```txt
database/seed-admin.php
```

## Security Notes

- Use PDO prepared statements for database queries.
- Validate uploads by MIME type, file extension, and file size.
- Sanitize rendered Markdown with DOMPurify.
- Protect state-changing requests with CSRF tokens.
- Keep admin-only endpoints behind PHP session checks.

