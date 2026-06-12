# FAYLabs Dashboard

A lightweight portfolio project CMS dashboard built with native PHP, MySQL, Bootstrap, jQuery, and Cloudinary.

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
| Frontend | HTML5, CSS3, Bootstrap 5, Vanilla JavaScript, jQuery |
| Backend | PHP Native |
| Database | MySQL |
| Markdown | marked.js, highlight.js, DOMPurify |
| Image Storage | Cloudinary |
| Authentication | PHP Session, password_hash(), password_verify() |

## Project Structure

```txt
api/
├── auth/
├── projects/
├── public/
└── uploads/

assets/
config/
database/
docs/
includes/
pages/
partials/
PRD.md
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

## Product Requirements

Detailed requirements, acceptance criteria, and implementation notes are documented in:

```txt
PRD.md
```

## Development Status

This repository follows the implementation plan in `PRD.md`, including authentication, project CRUD, Cloudinary uploads, Markdown rendering, public portfolio integration, SEO metadata, and responsive UI refinement.
