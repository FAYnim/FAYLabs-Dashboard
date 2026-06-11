# PRD — Portfolio Project CMS Dashboard

## 1. Project Overview

Buat sebuah dashboard internal untuk mengelola konten proyek pada landing page portfolio pribadi.

Dashboard ini berfungsi sebagai CMS sederhana untuk menambah, mengedit, menyimpan draft, mempublikasikan, melihat, dan menghapus proyek.

Setiap proyek akan memiliki metadata dasar, cover image, informasi teknologi, link terkait, serta konten detail proyek berbasis Markdown dengan gaya README GitHub.

Dashboard hanya digunakan oleh satu admin.

---

# 2. Main Goals

Tujuan utama sistem:

1. Memudahkan admin menambahkan proyek portfolio baru.
2. Menyimpan metadata proyek secara terstruktur.
3. Menulis konten proyek menggunakan Markdown.
4. Menyimpan proyek sebagai draft sebelum dipublikasikan.
5. Menampilkan proyek terbaru pada halaman portfolio publik.
6. Menampilkan halaman detail proyek berbasis slug.
7. Menyediakan pengalaman dashboard sederhana, cepat, dan ringan.

---

# 3. Non-Goals

Fitur berikut tidak perlu dibuat pada versi awal:

* Multi-user
* Role dan permission
* Register user
* Forgot password
* Media library
* Gallery manager
* Multi-image upload
* Rich text editor
* Drag and drop sorting
* Analytics lengkap
* Search project
* Filter project publik
* Category management kompleks
* Review workflow
* Scheduled publish
* Blog CMS
* Comment system

---

# 4. User Role

Hanya ada satu role:

```txt
Admin
```

Admin dapat:

* Login
* Logout
* Melihat daftar proyek
* Membuat proyek baru
* Mengedit proyek
* Menyimpan draft
* Mempublikasikan proyek
* Menghapus proyek
* Melihat project detail publik
* Mengunggah cover image ke Cloudinary

Tidak ada user registration.

---

# 5. Tech Stack

## 5.1 Frontend

Gunakan:

```txt
HTML5
CSS3
Bootstrap 5
Custom CSS
Vanilla JavaScript
jQuery
```

jQuery harus diambil dari CDN Cloudflare:

```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
```

Bootstrap dapat menggunakan CDN.

---

## 5.2 Backend

Gunakan:

```txt
PHP Native
```

Tidak perlu framework PHP.

Backend harus menangani:

* Authentication
* PHP Session
* CRUD project
* Upload image ke Cloudinary
* JSON API
* Validation
* Error handling

---

## 5.3 Database

Gunakan:

```txt
MySQL
```

---

## 5.4 Markdown Renderer

Gunakan:

```txt
marked.js
highlight.js
```

Tujuan:

* Render Markdown preview
* Render halaman detail proyek
* Syntax highlighting untuk code block

Gunakan CDN.

---

## 5.5 Image Storage

Gunakan:

```txt
Cloudinary
```

Cloudinary digunakan hanya untuk cover image proyek.

---

## 5.6 Authentication

Gunakan:

```txt
PHP Session
password_hash()
password_verify()
```

---

# 6. General UI Direction

## 6.1 Style

Gunakan gaya visual:

```txt
GitHub-inspired dashboard
```

Karakter visual:

* Bersih
* Sederhana
* Profesional
* Developer-oriented
* Minim dekorasi
* Banyak ruang putih
* Border halus
* Komponen jelas
* Fokus pada fungsi

---

## 6.2 Theme

Sediakan:

```txt
Light Mode
Dark Mode
```

Gunakan toggle theme di sidebar atau topbar.

Simpan preferensi theme menggunakan:

```txt
localStorage
```

---

## 6.3 Layout

Gunakan layout:

```txt
Sidebar + Main Content
```

Struktur umum:

```txt
Sidebar
├── Logo / Brand
├── Projects
├── Create Project
├── View Portfolio
├── Theme Toggle
└── Logout

Main Content
└── Page Content
```

Sidebar desktop selalu terlihat.

Pada mobile:

* Sidebar collapsible
* Gunakan tombol hamburger

---

# 7. Information Architecture

## 7.1 Admin Routes

```txt
/admin/login.php
/admin/projects/index.php
/admin/projects/create.php
/admin/projects/edit.php?id={id}
/admin/logout.php
```

---

## 7.2 Public Routes

```txt
/
/projects
/projects/{slug}
```

Jika menggunakan hosting PHP biasa, implementasi dapat menggunakan:

```txt
/projects/index.php
/projects/detail.php?slug={slug}
```

Namun gunakan rewrite URL jika server mendukung `.htaccess`.

Target URL publik:

```txt
/projects/travel-crm
/projects/visura
/projects/lynk-manager
```

---

# 8. Dashboard Pages

## 8.1 Login Page

Route:

```txt
/admin/login.php
```

### Fields

```txt
Username
Password
```

### Button

```txt
Login
```

### Requirements

* Username wajib diisi
* Password wajib diisi
* Tampilkan error jika kredensial salah
* Redirect ke halaman project list jika login berhasil
* Redirect ke login jika admin membuka protected page tanpa session

### Error Message

```txt
Invalid username or password.
```

---

## 8.2 Projects List Page

Route:

```txt
/admin/projects/index.php
```

Ini adalah halaman utama setelah login.

### Header

```txt
Projects
Manage your portfolio projects.
```

### Main CTA

```txt
+ New Project
```

### Project Card

Setiap proyek tampil sebagai card.

Struktur:

```txt
[Cover Image]

[Label]

Project Title

Short Description

View
Edit
Delete
```

### Required Data

Setiap card menampilkan:

* Cover image
* Label
* Title
* Short description

### Label Examples

```txt
AI
Web App
SaaS
IoT
Mobile
Other
```

### Action Buttons

```txt
View
Edit
Delete
```

### Sorting

Urutkan proyek berdasarkan:

```sql
ORDER BY created_at DESC
```

Untuk project published di halaman publik, gunakan:

```sql
ORDER BY published_at DESC
```

### Empty State

Jika belum ada proyek:

```txt
No projects yet.

Start by creating your first portfolio project.

[ Create First Project ]
```

---

## 8.3 Create Project Page

Route:

```txt
/admin/projects/create.php
```

Gunakan layout dua area:

```txt
Main Editor Area
Sidebar Metadata
```

### Main Area

Berisi:

```txt
Markdown Editor
Markdown Preview
```

Gunakan tab:

```txt
Write
Preview
```

Atau split view desktop:

```txt
Editor | Preview
```

Pada mobile:

```txt
Write / Preview tab
```

---

## 8.4 Edit Project Page

Route:

```txt
/admin/projects/edit.php?id={id}
```

Struktur sama seperti create project.

Tambahkan informasi:

```txt
Last updated: {datetime}
```

---

# 9. Project Form Fields

## 9.1 Required Fields

### Title

```txt
Project Title
```

Contoh:

```txt
Visura
Travel CRM
Lynk Manager
```

---

### Slug

```txt
project-slug
```

Contoh:

```txt
visura
travel-crm
lynk-manager
```

Slug dapat otomatis digenerate dari title.

Admin tetap dapat mengedit slug manual.

---

### Short Description

Digunakan untuk card project dan meta description default.

Contoh:

```txt
A premium portfolio content studio for developers and creators.
```

---

### Cover Image

Upload satu cover image.

Gunakan Cloudinary.

---

### Label

Gunakan select dropdown:

```txt
AI
Web App
SaaS
IoT
Mobile
Other
```

---

### Markdown Content

Textarea Markdown editor.

Contoh:

```md
# Overview

Visura is a premium portfolio content studio.

## Features

- Instagram carousel generator
- LinkedIn post generator
- AI-assisted content creation

## Tech Stack

- PHP
- MySQL
- Bootstrap
```

---

## 9.2 Metadata Fields

### Tech Stack

Gunakan input tag sederhana.

Admin dapat mengetik lalu menekan Enter.

Contoh:

```txt
PHP
MySQL
Bootstrap
JavaScript
Cloudinary
```

Simpan sebagai JSON string di database.

Contoh:

```json
["PHP", "MySQL", "Bootstrap", "JavaScript"]
```

---

### GitHub URL

Optional.

Contoh:

```txt
https://github.com/faynim/visura
```

---

### Demo URL

Optional.

Contoh:

```txt
https://visura.example.com
```

---

### Project Year

Required.

Contoh:

```txt
2026
```

---

### Status

Gunakan select:

```txt
Draft
Published
```

---

## 9.3 Action Buttons

Pada create dan edit page:

```txt
Save Draft
Publish
Cancel
```

### Save Draft

* Simpan project
* Set status menjadi `draft`
* Jangan tampilkan di public portfolio

### Publish

* Simpan project
* Set status menjadi `published`
* Isi `published_at`
* Tampilkan di public portfolio

### Cancel

* Kembali ke project list

---

# 10. Database Schema

Gunakan dua tabel utama:

```txt
admins
projects
```

---

## 10.1 Table: admins

```sql
CREATE TABLE admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 10.2 Table: projects

```sql
CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description VARCHAR(300) NOT NULL,

    cover_image VARCHAR(500) NOT NULL,
    cover_public_id VARCHAR(255) NULL,

    label ENUM(
        'AI',
        'Web App',
        'SaaS',
        'IoT',
        'Mobile',
        'Other'
    ) NOT NULL DEFAULT 'Other',

    content LONGTEXT NOT NULL,

    tech_stack JSON NOT NULL,

    github_url VARCHAR(500) NULL,
    demo_url VARCHAR(500) NULL,

    project_year YEAR NOT NULL,

    status ENUM(
        'draft',
        'published'
    ) NOT NULL DEFAULT 'draft',

    seo_title VARCHAR(180) NULL,
    seo_description VARCHAR(300) NULL,

    views INT UNSIGNED NOT NULL DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL
);
```

---

## 10.3 Field Notes

### cover_public_id

Digunakan untuk menghapus image lama dari Cloudinary jika cover diganti.

### tech_stack

Gunakan JSON.

Contoh:

```json
["PHP", "MySQL", "Bootstrap"]
```

### views

Digunakan untuk future analytics sederhana.

Belum perlu ditampilkan di dashboard V1.

### seo_title

Optional.

Jika kosong, gunakan:

```txt
{project_title} | FAY Portfolio
```

### seo_description

Optional.

Jika kosong, gunakan field:

```txt
description
```

---

# 11. API Specification

Semua endpoint admin wajib protected dengan PHP session.

Base endpoint:

```txt
/api/projects
```

Gunakan response JSON.

---

## 11.1 GET /api/projects

Mengambil seluruh project untuk admin.

### Query Parameters

Optional:

```txt
status=draft
status=published
```

### Response

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Visura",
      "slug": "visura",
      "description": "Premium portfolio content studio.",
      "cover_image": "https://res.cloudinary.com/example/image/upload/visura.jpg",
      "label": "AI",
      "status": "published",
      "created_at": "2026-06-11 12:00:00",
      "updated_at": "2026-06-11 12:00:00",
      "published_at": "2026-06-11 12:00:00"
    }
  ]
}
```

---

## 11.2 GET /api/projects/:id

Mengambil detail satu project.

### Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Visura",
    "slug": "visura",
    "description": "Premium portfolio content studio.",
    "cover_image": "https://res.cloudinary.com/example/image/upload/visura.jpg",
    "cover_public_id": "portfolio/visura",
    "label": "AI",
    "content": "# Overview\n...",
    "tech_stack": [
      "PHP",
      "MySQL",
      "Bootstrap"
    ],
    "github_url": "https://github.com/faynim/visura",
    "demo_url": "https://visura.example.com",
    "project_year": 2026,
    "status": "published",
    "seo_title": null,
    "seo_description": null,
    "created_at": "2026-06-11 12:00:00",
    "updated_at": "2026-06-11 12:00:00",
    "published_at": "2026-06-11 12:00:00"
  }
}
```

---

## 11.3 POST /api/projects

Membuat project baru.

### Request

```json
{
  "title": "Visura",
  "slug": "visura",
  "description": "Premium portfolio content studio.",
  "cover_image": "https://res.cloudinary.com/example/image/upload/visura.jpg",
  "cover_public_id": "portfolio/visura",
  "label": "AI",
  "content": "# Overview\n...",
  "tech_stack": [
    "PHP",
    "MySQL",
    "Bootstrap"
  ],
  "github_url": "https://github.com/faynim/visura",
  "demo_url": "https://visura.example.com",
  "project_year": 2026,
  "status": "draft",
  "seo_title": null,
  "seo_description": null
}
```

### Response

```json
{
  "success": true,
  "message": "Project created successfully.",
  "data": {
    "id": 1
  }
}
```

---

## 11.4 PATCH /api/projects/:id

Mengedit project.

### Request

Gunakan struktur payload yang sama dengan POST.

### Response

```json
{
  "success": true,
  "message": "Project updated successfully."
}
```

---

## 11.5 DELETE /api/projects/:id

Menghapus project.

### Response

```json
{
  "success": true,
  "message": "Project deleted successfully."
}
```

Sebelum delete:

* Tampilkan confirmation modal
* Hapus cover image dari Cloudinary jika `cover_public_id` tersedia
* Hapus project dari database

---

# 12. Public Portfolio Requirements

## 12.1 Homepage

Route:

```txt
/
```

Homepage portfolio menampilkan section:

```txt
Latest Projects
```

Tampilkan maksimal:

```txt
6 proyek terbaru
```

Gunakan query:

```sql
SELECT *
FROM projects
WHERE status = 'published'
ORDER BY published_at DESC
LIMIT 6;
```

Tambahkan CTA:

```txt
View All Projects
```

CTA menuju:

```txt
/projects
```

---

## 12.2 Projects Page

Route:

```txt
/projects
```

Tampilkan:

```txt
6 proyek terbaru
```

Tambahkan tombol:

```txt
Load More
```

Ketika Load More diklik:

* Ambil 6 proyek berikutnya
* Append card baru tanpa reload page
* Gunakan AJAX jQuery

Query:

```sql
SELECT *
FROM projects
WHERE status = 'published'
ORDER BY published_at DESC
LIMIT 6 OFFSET {offset};
```

Jika sudah tidak ada data:

* Sembunyikan tombol Load More

---

## 12.3 Project Detail Page

Route:

```txt
/projects/{slug}
```

Struktur halaman:

```txt
Cover Image

Label
Title
Description

Year
Tech Stack
GitHub
Live Demo

Divider

README Content
```

### README Content

Render Markdown seperti GitHub README.

Support:

```txt
Heading
Paragraph
Bold
Italic
Blockquote
Ordered List
Unordered List
Checklist
Table
Code Block
Inline Code
Link
Image via URL
Horizontal Rule
```

Gunakan:

```txt
marked.js
highlight.js
```

---

# 13. Markdown Requirements

## 13.1 Editor

Gunakan textarea besar.

Tambahkan toolbar sederhana:

```txt
Heading
Bold
Italic
Link
Image
Code
Quote
List
Table
```

Toolbar hanya membantu menyisipkan syntax Markdown.

Contoh:

````md
# Heading

**bold**

_italic_

[Link](https://example.com)

![Image](https://example.com/image.jpg)

```js
console.log("Hello");
````

> Quote

* Item

| Column | Column |
| ------ | ------ |
| Value  | Value  |

````

---

## 13.2 Live Preview

Preview diperbarui saat admin mengetik.

Gunakan debounce:

```txt
300 ms
````

---

## 13.3 Security

Markdown renderer harus aman.

Sanitize HTML hasil render.

Gunakan library seperti:

```txt
DOMPurify
```

Jangan render raw HTML tanpa sanitization.

---

# 14. Validation Rules

## 14.1 Title

```txt
Required
Minimum 3 characters
Maximum 150 characters
```

---

## 14.2 Slug

```txt
Required
Unique
Lowercase only
Allow letters, numbers, and hyphens
No spaces
No special characters
Minimum 3 characters
Maximum 180 characters
```

Regex:

```txt
^[a-z0-9]+(?:-[a-z0-9]+)*$
```

---

## 14.3 Description

```txt
Required
Minimum 10 characters
Maximum 300 characters
```

---

## 14.4 Cover Image

```txt
Required
Allowed formats:
- JPG
- JPEG
- PNG
- WEBP

Maximum size:
2 MB
```

---

## 14.5 Label

```txt
Required
Must match predefined options
```

---

## 14.6 Markdown Content

```txt
Required
Minimum 20 characters
```

---

## 14.7 Tech Stack

```txt
Required
Minimum 1 item
Maximum 20 items
```

---

## 14.8 GitHub URL

```txt
Optional
Must be valid URL
```

---

## 14.9 Demo URL

```txt
Optional
Must be valid URL
```

---

## 14.10 Project Year

```txt
Required
Minimum 2020
Maximum current year + 1
```

---

## 14.11 Status

```txt
Required
Allowed:
- draft
- published
```

---

# 15. Loading States

Gunakan loading state pada aksi penting.

## 15.1 Login

```txt
Logging in...
```

---

## 15.2 Save Draft

```txt
Saving draft...
```

---

## 15.3 Publish

```txt
Publishing...
```

---

## 15.4 Update

```txt
Updating project...
```

---

## 15.5 Delete

```txt
Deleting project...
```

---

## 15.6 Cover Upload

```txt
Uploading cover image...
```

---

## 15.7 Load More

```txt
Loading more projects...
```

---

# 16. Success Messages

Gunakan Bootstrap toast.

Contoh:

```txt
Project created successfully.
Draft saved successfully.
Project published successfully.
Project updated successfully.
Project deleted successfully.
Cover image uploaded successfully.
```

---

# 17. Error Messages

Gunakan Bootstrap alert atau toast.

Contoh:

```txt
Invalid username or password.
Project not found.
Slug already exists.
Failed to upload cover image.
Failed to save project.
Failed to publish project.
Failed to delete project.
Invalid image format.
Image size must not exceed 2 MB.
Network error. Please try again.
Session expired. Please log in again.
```

---

# 18. Delete Confirmation Modal

Saat admin menekan Delete:

```txt
Delete Project?

Are you sure you want to delete "{project_title}"?

This action cannot be undone.

[ Cancel ]
[ Delete Project ]
```

---

# 19. SEO Requirements

## 19.1 Project Detail Title

Jika `seo_title` tersedia:

```txt
{seo_title}
```

Jika kosong:

```txt
{project_title} | FAY Portfolio
```

---

## 19.2 Meta Description

Jika `seo_description` tersedia:

```txt
{seo_description}
```

Jika kosong:

```txt
{description}
```

---

## 19.3 Open Graph

Gunakan:

```txt
og:title
og:description
og:image
og:url
og:type
```

Contoh:

```html
<meta property="og:title" content="Visura | FAY Portfolio">
<meta property="og:description" content="Premium portfolio content studio.">
<meta property="og:image" content="https://res.cloudinary.com/example/image/upload/visura.jpg">
<meta property="og:type" content="article">
```

---

## 19.4 Canonical URL

Tambahkan canonical URL:

```html
<link rel="canonical" href="https://example.com/projects/visura">
```

---

# 20. Cloudinary Upload Flow

Flow upload:

```txt
Admin selects image
        ↓
Validate image type and file size
        ↓
Upload to Cloudinary
        ↓
Receive secure_url and public_id
        ↓
Save secure_url to cover_image
        ↓
Save public_id to cover_public_id
```

---

## 20.1 Environment Variables

Gunakan:

```txt
CLOUDINARY_CLOUD_NAME
CLOUDINARY_API_KEY
CLOUDINARY_API_SECRET
```

Simpan credential Cloudinary di backend.

Jangan expose API secret di frontend.

---

# 21. Authentication and Security

## 21.1 Session

Gunakan:

```php
session_start();
```

Protected pages wajib memeriksa:

```php
$_SESSION['admin_id']
```

Jika tidak tersedia:

```txt
Redirect ke /admin/login.php
```

---

## 21.2 Password

Gunakan:

```php
password_hash($password, PASSWORD_DEFAULT);
password_verify($password, $hash);
```

---

## 21.3 SQL Injection Protection

Wajib gunakan:

```txt
PDO Prepared Statements
```

Jangan gunakan raw query concatenation.

---

## 21.4 XSS Protection

Gunakan:

```txt
htmlspecialchars()
DOMPurify
```

---

## 21.5 CSRF Protection

Gunakan CSRF token untuk:

```txt
POST
PATCH
DELETE
Login
Logout
```

---

## 21.6 Upload Security

Validasi:

```txt
MIME type
File extension
File size
```

Jangan percaya ekstensi file saja.

---

# 22. Suggested Folder Structure

```txt
portfolio-cms/
│
├── admin/
│   ├── login.php
│   ├── logout.php
│   │
│   └── projects/
│       ├── index.php
│       ├── create.php
│       └── edit.php
│
├── api/
│   ├── auth/
│   │   ├── login.php
│   │   └── logout.php
│   │
│   ├── projects/
│   │   ├── index.php
│   │   ├── show.php
│   │   ├── create.php
│   │   ├── update.php
│   │   └── delete.php
│   │
│   ├── public/
│   │   ├── latest-projects.php
│   │   ├── load-projects.php
│   │   └── project-detail.php
│   │
│   └── uploads/
│       └── cover.php
│
├── config/
│   ├── database.php
│   ├── cloudinary.php
│   └── app.php
│
├── includes/
│   ├── auth.php
│   ├── csrf.php
│   ├── response.php
│   ├── validator.php
│   └── helpers.php
│
├── public/
│   ├── index.php
│   │
│   ├── projects/
│   │   ├── index.php
│   │   └── detail.php
│   │
│   └── assets/
│       ├── css/
│       │   ├── admin.css
│       │   └── portfolio.css
│       │
│       ├── js/
│       │   ├── admin.js
│       │   ├── editor.js
│       │   ├── theme.js
│       │   └── projects.js
│       │
│       └── images/
│
├── database/
│   ├── schema.sql
│   └── seed-admin.php
│
├── .env
├── .htaccess
└── README.md
```

---

# 23. Admin UI Components

Gunakan komponen berikut:

```txt
Sidebar
Topbar
Project Card
Project Form
Markdown Editor
Markdown Preview
Toast
Alert
Confirmation Modal
Theme Toggle
Loading Spinner
Empty State
Tag Input
```

---

# 24. Responsive Requirements

## Desktop

Gunakan:

```txt
Sidebar fixed
Editor and metadata sidebar side-by-side
Markdown preview split view
```

---

## Tablet

Gunakan:

```txt
Sidebar collapsible
Editor and metadata stacked if needed
```

---

## Mobile

Gunakan:

```txt
Hamburger sidebar
Single column layout
Markdown Write/Preview tabs
Full-width buttons
```

---

# 25. Project Card Public UI

Gunakan layout card sederhana:

```txt
[Cover Image]

[Label]

Project Title

Short Description

View Project →
```

Card harus:

* Responsive
* Clickable
* Hover state ringan
* Tidak terlalu banyak efek
* Tetap konsisten di light dan dark mode

---

# 26. API Response Standard

Gunakan format:

## Success

```json
{
  "success": true,
  "message": "Project created successfully.",
  "data": {}
}
```

## Error

```json
{
  "success": false,
  "message": "Slug already exists.",
  "errors": {
    "slug": "This slug is already in use."
  }
}
```

---

# 27. Development Phases

## Phase 1 — Setup

Buat:

* Struktur folder
* Database config
* Environment config
* Database schema
* Admin seed
* Basic layout

---

## Phase 2 — Authentication

Buat:

* Login
* Logout
* Session validation
* Protected routes
* CSRF token

---

## Phase 3 — Project CRUD

Buat:

* Project list
* Create project
* Edit project
* Delete project
* Validation
* Toast messages

---

## Phase 4 — Cloudinary Upload

Buat:

* Image validation
* Upload endpoint
* Cover image preview
* Replace image
* Delete old Cloudinary image

---

## Phase 5 — Markdown Editor

Buat:

* Textarea editor
* Toolbar
* Live preview
* marked.js
* highlight.js
* DOMPurify

---

## Phase 6 — Public Portfolio Integration

Buat:

* Homepage latest projects section
* Projects page
* Load More AJAX
* Project detail page
* Markdown rendering

---

## Phase 7 — SEO and Refinement

Buat:

* SEO meta tags
* Open Graph
* Canonical URL
* Empty states
* Loading states
* Error states
* Responsive polish
* Light and dark mode

---

# 28. Future Improvements

Fitur berikut dapat dibuat nanti, tetapi jangan diimplementasikan sekarang:

```txt
Featured projects
Manual sorting
Project search
Category filter
Tag management
Gallery images
Blog CMS
Analytics dashboard
Project views chart
Draft preview URL
Scheduled publish
Multiple admin
Editor role
Image media library
SEO sitemap generator
RSS feed
Related projects
Project archive
Soft delete
```

---

# 29. Final Acceptance Criteria

Aplikasi dianggap selesai jika:

1. Admin dapat login.
2. Admin dapat logout.
3. Halaman admin terlindungi session.
4. Admin dapat membuat proyek.
5. Admin dapat menyimpan draft.
6. Draft tidak tampil di halaman publik.
7. Admin dapat mempublikasikan proyek.
8. Published project tampil di halaman publik.
9. Admin dapat mengedit proyek.
10. Admin dapat menghapus proyek.
11. Admin dapat upload cover ke Cloudinary.
12. Admin dapat mengganti cover.
13. Markdown preview berjalan.
14. Markdown dirender pada halaman detail proyek.
15. Homepage menampilkan maksimal 6 project terbaru.
16. Halaman `/projects` menampilkan 6 proyek awal.
17. Tombol Load More menambahkan 6 proyek berikutnya.
18. Halaman detail proyek dapat dibuka melalui slug.
19. SEO metadata tampil pada halaman detail proyek.
20. Light mode dan dark mode berfungsi.
21. Layout responsive di desktop dan mobile.
22. Semua query database menggunakan PDO prepared statements.
23. Validasi form berjalan di frontend dan backend.
24. Error state dan loading state tersedia.

---

# 30. Implementation Notes for AI Coding Agent

Gunakan pendekatan berikut:

```txt
Keep it simple.
Use native PHP.
Use Bootstrap.
Use jQuery for AJAX.
Use MySQL with PDO.
Avoid unnecessary abstractions.
Avoid overengineering.
Use reusable helper functions.
Separate admin API and public API.
Make the code easy to deploy on shared hosting.
```

Prioritaskan:

```txt
Simplicity
Maintainability
Shared hosting compatibility
Security
Fast implementation
```

Jangan gunakan:

```txt
Node.js
React
Vue
Laravel
Symfony
Composer-heavy setup
Complex build tools
Docker
```

Kecuali benar-benar diperlukan.

Target utama adalah CMS portfolio internal yang ringan, cepat, dan mudah dikembangkan lebih lanjut.
