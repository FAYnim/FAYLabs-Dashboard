# Bootstrap Icons Migration Design

**Date:** 2025-06-12  
**Author:** FAY Labs Admin Team  
**Status:** Approved  
**Topic:** Replacing inline SVG icons with Bootstrap Icons CDN

---

## Overview

This design specifies the migration from inline SVG icons to Bootstrap Icons via CDN for the FAY Labs Admin CMS. The goal is to streamline icon management while maintaining visual consistency and theme support.

### Goals

- Replace all inline `<svg>` elements with Bootstrap Icon classes
- Maintain existing light/dark theme toggle behavior
- Keep bundle size minimal (Bootstrap Icons ~7KB vs Font Awesome ~40KB)
- Ensure zero impact on backend functionality or database

### Non-Goals

- No changes to PHP logic or data structures
- No custom CSS additions required
- No JavaScript runtime modifications
- No breaking changes to existing features

---

## Architecture

### Changes Summary

**Add:**
- Bootstrap Icons CDN link to `partials/head.php`

**Modify:**
- All pages (`index.php`, `create.php`, `edit.php`, `login.php`)
- All partials (`sidebar.php`)

**No Changes Required:**
- Backend code
- Database schema
- Theme switching logic (`assets/js/theme.js`)
- Asset files beyond what's provided by CDN

### File Structure

```
C:\xampp\htdocs\faydev\faylabs-dashboard\
├── partials/
│   ├── head.php          → Add Bootstrap Icons CDN
│   └── sidebar.php       → Replace ~6 icons
├── pages/
│   ├── index.php         → Replace ~8 icons (project cards, actions)
│   ├── create.php        → Replace form icons (~3-4)
│   ├── edit.php          → Replace form icons (~3-4)
│   └── login.php         → Replace theme & brand icons (~2)
└── assets/
    └── js/
        └── theme.js      → No changes needed
```

---

## Icon Mapping

### Sidebar Navigation

| Current Icon | Bootstrap Icon Class | Usage Location |
|--------------|---------------------|----------------|
| Hamburger menu (3 lines) | `bi bi-list` | Sidebar toggle button |
| Brand logo (triple line motif) | `bi bi-grid-3x3-gap-fill` | Sidebar brand area |
| Projects (list format) | `bi bi-files` | nav-projects link |
| New Project (+) | `bi bi-plus-circle` | nav-create link & topbar button |
| Sun icon (light mode) | `bi bi-sun-fill` | Theme toggle - sun state |
| Moon icon (dark mode) | `bi bi-moon-stars-fill` | Theme toggle - moon state |
| Logout (power outlet) | `bi bi-box-arrow-right` | Logout link |

### Dashboard Page (index.php)

| Current Icon | Bootstrap Icon Class | Usage Location |
|--------------|---------------------|----------------|
| Delete modal (trash can) | `bi bi-trash3-fill` | Modal header icon |
| Error alert (exclamation triangle) | `bi bi-exclamation-triangle-fill` | Alert message icon |
| Empty state (collection/grid) | `bi bi-collection` | Empty projects state |
| Edit button (pencil) | `bi bi-pencil-square` | Project edit action |
| Delete button (trash) | `bi bi-trash-fill` | Project delete action |
| Cover placeholder (image) | `bi bi-image` | Missing cover fallback |

### Create/Edit Pages

| Current Icon | Bootstrap Icon Class | Usage Location |
|--------------|---------------------|----------------|
| Form section dividers | `bi bi-plus-circle` | Field group indicators |
| Preview toggle | `bi bi-eye` / `bi bi-eye-slash` | Tab preview indicator |

### Login Page

| Current Icon | Bootstrap Icon Class | Usage Location |
|--------------|---------------------|----------------|
| Brand icon | `bi bi-grid-3x3-gap-fill` | Login page header |
| Theme toggle | Same as sidebar | Login page footer |

**Total Icon Replacements: ~18 instances across 6 files**

---

## Implementation Details

### CDN Integration

Add this line to `partials/head.php` after Bootstrap CSS:

```html
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
```

### Replacement Pattern

**Before:**
```html
<svg viewBox="0 0 16 16"><path d="M7.75 2a.75.75..."/></svg>
```

**After:**
```html
<i class="bi bi-plus-circle"></i>
```

**With Size Utility:**
```html
<i class="bi bi-plus-circle fs-5"></i>
```

### Accessibility Considerations

- Decorative icons add `aria-hidden="true"` attribute
- Icon-only buttons include `aria-label` for screen readers
- Maintains original keyboard navigation patterns

### CSS Compatibility

Bootstrap Icons uses `currentColor` for fill color, enabling automatic theme integration:

- **Light Mode:** Icons inherit `var(--text-color)`
- **Dark Mode:** Icons automatically switch via theme change event
- **Hover States:** Existing button styles apply unchanged
- **Spacing:** Uses Bootstrap utilities (`me-2`, `ms-1`, etc.)

### Theme Toggle Behavior

The theme toggle maintains exact current behavior:

```html
<!-- Light state (default) -->
<i class="bi bi-sun-fill icon-sun" style="display:block"></i>
<i class="bi bi-moon-stars-fill icon-moon" style="display:none"></i>

<!-- Dark state (via JS toggling display) -->
<i class="bi bi-sun-fill icon-sun" style="display:none"></i>
<i class="bi bi-moon-stars-fill icon-moon" style="display:block"></i>
```

No JavaScript changes required - existing `theme.js` handles display toggling.

---

## Edge Cases Handled

### 1. Loading State
Bootstrap Icons renders immediately via CDN; no Flash of Unstyled Content (FOUC).

### 2. Icon Sizing
Size adjustments use Bootstrap utility classes:
- Small: `fs-6` (~0.875rem)
- Medium: `fs-5` (~1rem) ✓ Default
- Large: `fs-4` (~1.125rem)

### 3. Color Inheritance
All icons inherit `currentColor`, so they automatically adapt to:
- Primary button colors
- Danger/destructive states
- Disabled gray states
- Theme light/dark modes

### 4. Browser Support
Bootstrap Icons supports all browsers supported by Bootstrap 5.x:
- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+
- Mobile Safari iOS 12+
- Chrome Android 60+

---

## Testing Checklist

### Visual Regression Tests

- [ ] Sidebar navigation icons render correctly in light/dark mode
- [ ] Brand logo displays at correct size in sidebar
- [ ] Theme toggle switches between sun/moon icons
- [ ] Logout icon shows power outlet symbol
- [ ] Project grid: edit/delete buttons show correct icons
- [ ] Empty state shows collection icon when no projects exist
- [ ] Delete modal shows trash icon in header
- [ ] Error alerts show exclamation triangle icon
- [ ] Cover image placeholder shows image icon fallback
- [ ] Create/Edit page form icons align properly

### Functional Tests

- [ ] Light/dark mode toggle works after icon swap
- [ ] Clicking any navigational element functions correctly
- [ ] Delete confirmation modal triggers properly
- [ ] Button hover states render correctly with new icons
- [ ] Mobile responsive layout unaffected

### Performance Tests

- [ ] Page load time within acceptable range (< 1s for first paint)
- [ ] No excessive network requests added
- [ ] CDN caching effective (Bootstrap Icons is widely cached)

---

## Rollback Plan

If issues arise during deployment:

1. **Immediate Rollback:** Remove Bootstrap Icons CDN link from `head.php`
2. **Partial Rollback:** Revert individual file changes one-at-a-time
3. **Communication:** Notify admin team of icon system status

**Rollback Timeline:** Can revert entire change in < 5 minutes

---

## Success Criteria

The migration is successful when:

- ✅ All 18 icon instances replaced with Bootstrap Icons
- ✅ Light/dark theme toggles function without modification
- ✅ Visual appearance matches pre-migration state
- ✅ No console errors or warnings in browser DevTools
- ✅ Lighthouse accessibility score maintained or improved
- ✅ Page load performance unchanged or improved

---

## Appendix: Icon Reference

### Common Icons Used

| Bootstrap Class | Description |
|----------------|-------------|
| `bi bi-list` | Hamburger menu / sidebar toggle |
| `bi bi-grid-3x3-gap-fill` | Grid logo / brand mark |
| `bi bi-files` | Document list / projects |
| `bi bi-plus-circle` | Add / create new item |
| `bi bi-pencil-square` | Edit / modify |
| `bi bi-trash3-fill` | Trash / delete (filled variant) |
| `bi bi-trash-fill
