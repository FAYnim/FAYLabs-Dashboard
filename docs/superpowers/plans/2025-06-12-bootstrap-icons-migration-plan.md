# Bootstrap Icons Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (- []) syntax for tracking.

**Goal:** Replace all inline SVG icons with Bootstrap Icons CDN across the admin CMS

**Architecture:** Add Bootstrap Icons CDN stylesheet and systematically replace each <svg> element with corresponding Bootstrap Icon class, maintaining visual consistency and theme support

**Tech Stack:** PHP 7+, Bootstrap 5.3.3, Bootstrap Icons 1.11.3 via CDN

---

### Task 1: Add Bootstrap Icons CDN to head.php

**Files:**
- Modify: `partials/head.php`

- [ ] **Step 1: Read current head.php structure**

Open `/c/xampp/htdocs/faydev/faylabs-dashboard/partials/head.php` and note the position after Bootstrap CSS link.

- [ ] **Step 2: Add Bootstrap Icons CDN link**

Edit `partials/head.php` and add this line after the Bootstrap CSS link:

```html
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
```

Expected result: The CDNs section now has Bootstrap 5 CSS followed by Bootstrap Icons CSS, then highlight.js themes.

- [ ] **Step 3: Verify syntax**

Ensure no trailing commas or broken HTML tags in the modified file.

- [ ] **Step 4: Test browser rendering**

Navigate to `/c/xampp/htdocs/faydev/faylabs-dashboard/` in browser and check:
- No console errors about missing resources
- All existing icons still display correctly
- Page loads without visual glitches

- [ ] **Step 5: Commit the change**

```bash
git add partials/head.php
git commit -m "feat: add Bootstrap Icons CDN to head.php"
```

---

### Task 2: Migrate Sidebar Navigation Icons

**Files:**
- Modify: `partials/sidebar.php`

- [ ] **Step 1: Map current SVGs to Bootstrap Icons**

Open `/c/xampp/htdocs/faydev/faylabs-dashboard/partials/sidebar.php` and identify these 6 SVG elements that need replacement.

- [ ] **Step 2: Replace brand logo**

Replace the `<svg viewBox="0 0 24 24">` block in sidebar-brand div with:
```html
<i class="bi bi-grid-3x3-gap-fill"></i>
```

- [ ] **Step 3: Replace projects navigation icon**

Replace the Projects link `<svg>` with:
```html
<i class="bi bi-files"></i>
```

- [ ] **Step 4: Replace new project icon**

Replace the New Project link `<svg>` with:
```html
<i class="bi bi-plus-circle"></i>
```

- [ ] **Step 5: Replace theme toggle icons**

Replace sun SVG with `bi bi-sun-fill` and moon SVG with `bi bi-moon-stars-fill`.

- [ ] **Step 6: Replace logout icon**

Replace logout `<svg>` with:
```html
<i class="bi bi-box-arrow-right"></i>
```

- [ ] **Step 7: Verify sidebar renders correctly**

Test in browser that all sidebar icons display properly.

- [ ] **Step 8: Commit the changes**

```bash
git add partials/sidebar.php
git commit -m "refactor: migrate sidebar icons to Bootstrap Icons"
```

---

### Task 3: Migrate Dashboard (index.php) Icons

**Files:**
- Modify: `pages/index.php`

- [ ] **Step 1: Identify all SVGs to replace**

Find ~8 SVG instances in index.php including modal, alerts, cards, and buttons.

- [ ] **Step 2: Replace delete modal icon**

Replace modal trash SVG with:
```html
<i class="bi bi-trash3-fill"></i>
```

- [ ] **Step 3: Replace error alert icon**

Replace exclamation triangle SVG with:
```html
<i class="bi bi-exclamation-triangle-fill"></i>
```

- [ ] **Step 4: Replace empty state icon**

Replace empty collection SVG with:
```html
<i class="bi bi-collection"></i>
```

- [ ] **Step 5: Replace edit button icon**

Replace pencil SVG with:
```html
<i class="bi bi-pencil-square"></i>
```

- [ ] **Step 6: Replace delete button icon**

Replace trash SVG with:
```html
<i class="bi bi-trash-fill"></i>
```

- [ ] **Step 7: Replace cover placeholder icon**

Replace image SVG with:
```html
<i class="bi bi-image"></i>
```

- [ ] **Step 8: Replace topbar icons**

Replace hamburger with `bi bi-list` and New Project button with `bi bi-plus-circle`.

- [ ] **Step 9: Verify dashboard renders correctly**

Test all pages for correct icon display.

- [ ] **Step 10: Commit the changes**

```bash
git add pages/index.php
git commit -m "refactor: migrate dashboard icons to Bootstrap Icons"
```

---

### Task 4-6: Migrate Remaining Pages

**Files:**
- Modify: `pages/create.php`, `pages/edit.php`, `pages/login.php`

Apply similar icon replacements as documented in Tasks 2-3 for each page.

---

### Task 7: Final Testing & Verification

**No file changes - verification only**

- [ ] Test all pages visually in browser
- [ ] Verify light/dark theme toggles work correctly
- [ ] Test responsive mobile view
- [ ] Check browser console for errors (should be clean)
- [ ] Final commit documenting full migration

---

**Plan complete. Two execution options:**

**1. Subagent-Driven** - Dispatch fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** - Execute tasks in this session, batch execution with checkpoints

**Which approach?**
