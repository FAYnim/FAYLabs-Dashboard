// ============================================================
// Admin Dashboard JS
// ============================================================

(function ($) {
  'use strict';

  // ── Toast ─────────────────────────────────────────────────

  const Toast = {
    container: null,

    init() {
      if (!$('#toast-container').length) {
        $('body').append('<div id="toast-container" class="toast-container"></div>');
      }
      this.container = $('#toast-container');
    },

    show(message, type = 'success', duration = 4000) {
      const icons = {
        success: '<svg viewBox="0 0 16 16"><path d="M13.78 4.22a.75.75 0 010 1.06l-7.25 7.25a.75.75 0 01-1.06 0L2.22 9.28a.75.75 0 011.06-1.06L6 10.94l6.72-6.72a.75.75 0 011.06 0z"/></svg>',
        error:   '<svg viewBox="0 0 16 16"><path d="M2.343 13.657A8 8 0 1113.657 2.343 8 8 0 012.343 13.657zM6.03 4.97a.75.75 0 00-1.06 1.06L6.94 8 4.97 9.97a.75.75 0 101.06 1.06L8 9.06l1.97 1.97a.75.75 0 101.06-1.06L9.06 8l1.97-1.97a.75.75 0 10-1.06-1.06L8 6.94 6.03 4.97z"/></svg>',
        warning: '<svg viewBox="0 0 16 16"><path d="M8.22 1.754a.25.25 0 00-.44 0L1.698 13.132a.25.25 0 00.22.368h12.164a.25.25 0 00.22-.368L8.22 1.754zm-1.763-.707c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A1.75 1.75 0 0114.082 15H1.918a1.75 1.75 0 01-1.543-2.575L6.457 1.047zM9 11a1 1 0 11-2 0 1 1 0 012 0zm-.25-5.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z"/></svg>',
      };

      const $toast = $(`
        <div class="toast toast-${type}">
          <span class="toast-icon">${icons[type] || icons.success}</span>
          <span class="toast-message">${escapeHtml(message)}</span>
          <button class="toast-close" aria-label="Close">×</button>
        </div>
      `);

      this.container.append($toast);

      $toast.find('.toast-close').on('click', () => this.dismiss($toast));

      setTimeout(() => this.dismiss($toast), duration);
    },

    dismiss($toast) {
      $toast.addClass('fade-out');
      setTimeout(() => $toast.remove(), 300);
    },
  };

  // ── Delete Modal ──────────────────────────────────────────

  const DeleteModal = {
    $overlay:  null,
    $title:    null,
    $name:     null,
    $confirmBtn: null,
    callback:  null,

    init() {
      this.$overlay   = $('#delete-modal');
      this.$title     = $('#delete-modal-title');
      this.$name      = $('#delete-modal-name');
      this.$confirmBtn = $('#delete-confirm-btn');

      this.$overlay.on('click', (e) => {
        if ($(e.target).is(this.$overlay)) this.hide();
      });

      $('#delete-cancel-btn').on('click', () => this.hide());

      this.$confirmBtn.on('click', () => {
        if (this.callback) this.callback();
      });
    },

    show(projectTitle, projectId) {
      this.$name.text(projectTitle);
      this.$overlay.addClass('active');

      this.callback = () => {
        this.setLoading(true);
        this.deleteProject(projectId);
      };
    },

    hide() {
      this.$overlay.removeClass('active');
      this.callback = null;
      this.setLoading(false);
    },

    setLoading(loading) {
      if (loading) {
        this.$confirmBtn.html('<span class="spinner"></span> Deleting...').prop('disabled', true);
      } else {
        this.$confirmBtn.html('Delete Project').prop('disabled', false);
      }
    },

    deleteProject(id) {
      $.ajax({
        url: FAY_CONFIG.apiBase + '/projects/delete.php?id=' + id,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ csrf_token: FAY_CONFIG.csrfToken }),
        success: (res) => {
          if (res.success) {
            Toast.show(res.message || 'Project deleted successfully.', 'success');
            $('#project-card-' + id).fadeOut(300, function () { $(this).remove(); checkEmpty(); });
            this.hide();
          } else {
            Toast.show(res.message || 'Failed to delete project.', 'error');
            this.hide();
          }
        },
        error: () => {
          Toast.show('Network error. Please try again.', 'error');
          this.hide();
        },
      });
    },
  };

  // ── Sidebar Toggle ────────────────────────────────────────

  function initSidebar() {
    const $sidebar  = $('#admin-sidebar');
    const $overlay  = $('#sidebar-overlay');
    const $hamburger = $('#sidebar-toggle');

    $hamburger.on('click', function () {
      $sidebar.toggleClass('open');
      $overlay.toggleClass('active');
    });

    $overlay.on('click', function () {
      $sidebar.removeClass('open');
      $overlay.removeClass('active');
    });
  }

  // ── Empty State ───────────────────────────────────────────

  function checkEmpty() {
    const $grid = $('#projects-grid');
    if ($grid.length && $grid.children('.project-card').length === 0) {
      $grid.replaceWith(`
        <div class="empty-state">
          <div class="empty-state-icon">
            <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
          </div>
          <h3>No projects yet.</h3>
          <p>Start by creating your first portfolio project.</p>
          <a href="${FAY_CONFIG.adminBase}/create" class="btn btn-primary mt-3">
            <svg viewBox="0 0 16 16"><path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/></svg>
            Create First Project
          </a>
        </div>
      `);
    }
  }

  // ── Utilities ──────────────────────────────────────────────

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  // ── Init ───────────────────────────────────────────────────

  $(document).ready(function () {
    Toast.init();
    DeleteModal.init();
    initSidebar();

    // Delete button click
    $(document).on('click', '.btn-delete-project', function () {
      const id    = $(this).data('id');
      const title = $(this).data('title');
      DeleteModal.show(title, id);
    });

    // Check for success message in URL
    const urlParams = new URLSearchParams(window.location.search);
    const msg       = urlParams.get('success');
    const err       = urlParams.get('error');

    if (msg) Toast.show(decodeURIComponent(msg), 'success');
    if (err) Toast.show(decodeURIComponent(err), 'error');

    // Remove params from URL cleanly
    if (msg || err) {
      const cleanUrl = window.location.pathname;
      window.history.replaceState({}, '', cleanUrl);
    }
  });

  // Expose Toast for other scripts
  window.AdminToast = Toast;

}(jQuery));
