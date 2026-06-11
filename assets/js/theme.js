// ============================================================
// Theme Toggle — Light / Dark Mode
// Persists in localStorage
// ============================================================

(function () {
  'use strict';

  const STORAGE_KEY = 'fay_theme';
  const DEFAULT     = 'light';

  function getTheme() {
    return localStorage.getItem(STORAGE_KEY) || DEFAULT;
  }

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem(STORAGE_KEY, theme);

    // Update all sun/moon icon pairs
    document.querySelectorAll('.icon-sun').forEach(el => {
      el.style.display = theme === 'dark' ? 'none' : 'block';
    });
    document.querySelectorAll('.icon-moon').forEach(el => {
      el.style.display = theme === 'dark' ? 'block' : 'none';
    });

    // Update toggle label text if present
    document.querySelectorAll('.theme-label').forEach(el => {
      el.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
    });
  }

  function toggleTheme() {
    const current = getTheme();
    applyTheme(current === 'dark' ? 'light' : 'dark');
  }

  // Apply on load immediately (before DOM ready to avoid flash)
  applyTheme(getTheme());

  document.addEventListener('DOMContentLoaded', function () {
    applyTheme(getTheme());

    // Attach all toggle buttons
    document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
      btn.addEventListener('click', toggleTheme);
    });
  });

  // Expose globally
  window.themeToggle = toggleTheme;
}());
