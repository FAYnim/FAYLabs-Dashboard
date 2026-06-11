// ============================================================
// Markdown Editor JS
// Toolbar · Live Preview · Tag Input · Cover Upload
// ============================================================

(function ($) {
  'use strict';

  let debounceTimer = null;
  const DEBOUNCE_MS = 300;

  // ── Markdown Preview ─────────────────────────────────────

  function renderPreview(markdown) {
    if (typeof marked === 'undefined') return;

    marked.setOptions({
      breaks: true,
      gfm: true,
    });

    // Mark highlight.js renderer if available
    if (typeof hljs !== 'undefined') {
      marked.setOptions({
        highlight: function (code, lang) {
          if (lang && hljs.getLanguage(lang)) {
            return hljs.highlight(code, { language: lang }).value;
          }
          return hljs.highlightAuto(code).value;
        }
      });
    }

    let html = marked.parse(markdown || '');

    // Sanitize with DOMPurify
    if (typeof DOMPurify !== 'undefined') {
      html = DOMPurify.sanitize(html);
    }

    $('#editor-preview').html(html);

    // Apply hljs to any code blocks
    if (typeof hljs !== 'undefined') {
      $('#editor-preview pre code').each(function () {
        hljs.highlightElement(this);
      });
    }
  }

  // ── Tabs ─────────────────────────────────────────────────

  function initTabs() {
    $('.editor-tab').on('click', function () {
      const target = $(this).data('target');

      $('.editor-tab').removeClass('active');
      $(this).addClass('active');

      if (target === 'preview') {
        $('#editor-textarea-area').hide();
        $('#editor-preview').show().addClass('active');
        renderPreview($('#editor-content').val());
      } else {
        $('#editor-preview').hide().removeClass('active');
        $('#editor-textarea-area').show();
        $('#editor-content').focus();
      }
    });
  }

  // ── Toolbar ───────────────────────────────────────────────

  function insertAtCursor($textarea, before, after = '', placeholder = '') {
    const el    = $textarea[0];
    const start = el.selectionStart;
    const end   = el.selectionEnd;
    const selected = el.value.substring(start, end) || placeholder;
    const newText  = before + selected + after;
    const newVal   = el.value.substring(0, start) + newText + el.value.substring(end);

    el.value = newVal;
    $textarea.trigger('input');

    // Restore cursor
    const cursorPos = start + before.length + selected.length;
    el.setSelectionRange(cursorPos, cursorPos);
    el.focus();
  }

  function initToolbar() {
    const $ta = $('#editor-content');
    if (!$ta.length) return;

    const actions = {
      'h1':       () => insertAtCursor($ta, '# ', '', 'Heading 1'),
      'h2':       () => insertAtCursor($ta, '## ', '', 'Heading 2'),
      'h3':       () => insertAtCursor($ta, '### ', '', 'Heading 3'),
      'bold':     () => insertAtCursor($ta, '**', '**', 'bold text'),
      'italic':   () => insertAtCursor($ta, '_', '_', 'italic text'),
      'link':     () => insertAtCursor($ta, '[', '](https://example.com)', 'link text'),
      'image':    () => insertAtCursor($ta, '![', '](https://example.com/image.jpg)', 'alt text'),
      'code':     () => insertAtCursor($ta, '`', '`', 'code'),
      'codeblock':() => insertAtCursor($ta, '```\n', '\n```', 'code here'),
      'quote':    () => insertAtCursor($ta, '> ', '', 'blockquote'),
      'ul':       () => insertAtCursor($ta, '- ', '', 'list item'),
      'ol':       () => insertAtCursor($ta, '1. ', '', 'list item'),
      'hr':       () => insertAtCursor($ta, '\n---\n', ''),
      'table':    () => insertAtCursor($ta, '\n| Column | Column |\n| ------ | ------ |\n| Value  | Value  |\n', ''),
    };

    $('.toolbar-btn').on('click', function (e) {
      e.preventDefault();
      const action = $(this).data('action');
      if (actions[action]) actions[action]();
    });
  }

  // ── Live Preview Debounce ─────────────────────────────────

  function initLivePreview() {
    $('#editor-content').on('input', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        if ($('#editor-preview').is(':visible')) {
          renderPreview($(this).val());
        }
      }, DEBOUNCE_MS);
    });
  }

  // ── Tag Input ─────────────────────────────────────────────

  function initTagInput() {
    const $wrapper = $('#tag-input-wrapper');
    const $hidden  = $('#tech-stack-hidden');
    const $input   = $('#tag-real-input');

    if (!$wrapper.length) return;

    let tags = [];

    // Load existing tags if editing
    try {
      const existing = JSON.parse($hidden.val() || '[]');
      if (Array.isArray(existing)) {
        existing.forEach(t => addTag(t));
      }
    } catch (e) {}

    function addTag(text) {
      text = text.trim();
      if (!text || tags.includes(text) || tags.length >= 20) return;
      tags.push(text);
      renderTags();
      syncHidden();
    }

    function removeTag(text) {
      tags = tags.filter(t => t !== text);
      renderTags();
      syncHidden();
    }

    function renderTags() {
      $wrapper.find('.tag-item').remove();
      tags.forEach(tag => {
        const $tag = $(`
          <span class="tag-item">
            ${escapeHtml(tag)}
            <button type="button" data-tag="${escapeHtml(tag)}" aria-label="Remove ${escapeHtml(tag)}">×</button>
          </span>
        `);
        $tag.find('button').on('click', function () {
          removeTag($(this).data('tag'));
        });
        $wrapper.prepend($tag);
      });
    }

    function syncHidden() {
      $hidden.val(JSON.stringify(tags));
    }

    $input.on('keydown', function (e) {
      if ((e.key === 'Enter' || e.key === ',') && $(this).val().trim()) {
        e.preventDefault();
        addTag($(this).val());
        $(this).val('');
      } else if (e.key === 'Backspace' && !$(this).val() && tags.length) {
        removeTag(tags[tags.length - 1]);
      }
    });

    $wrapper.on('click', function () {
      $input.focus();
    });
  }

  // ── Cover Image Upload ────────────────────────────────────

  function initCoverUpload() {
    const $area    = $('#cover-upload-area');
    const $fileIn  = $('#cover-file-input');
    const $preview = $('#cover-preview');
    const $img     = $('#cover-preview-img');
    const $urlIn   = $('#cover-image-url');
    const $pidIn   = $('#cover-public-id');
    const $progress = $('#cover-upload-progress');

    if (!$area.length) return;

    $area.on('click', () => $fileIn.trigger('click'));

    $area.on('dragover', function (e) {
      e.preventDefault();
      $(this).addClass('drag-over');
    });

    $area.on('dragleave', function () {
      $(this).removeClass('drag-over');
    });

    $area.on('drop', function (e) {
      e.preventDefault();
      $(this).removeClass('drag-over');
      const file = e.originalEvent.dataTransfer.files[0];
      if (file) uploadFile(file);
    });

    $fileIn.on('change', function () {
      if (this.files[0]) uploadFile(this.files[0]);
    });

    $('#cover-remove-btn').on('click', function () {
      $preview.hide();
      $area.show();
      $urlIn.val('');
      $pidIn.val('');
    });

    function uploadFile(file) {
      // Validate client-side
      const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
      if (!allowed.includes(file.type)) {
        window.AdminToast && window.AdminToast.show('Invalid image format. Allowed: JPG, PNG, WEBP.', 'error');
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        window.AdminToast && window.AdminToast.show('Image size must not exceed 2 MB.', 'error');
        return;
      }

      $area.hide();
      $progress.show().text('Uploading cover image...');

      const formData = new FormData();
      formData.append('cover', file);
      formData.append('csrf_token', FAY_CONFIG.csrfToken);

      $.ajax({
        url: FAY_CONFIG.apiBase + '/uploads/cover.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function () {
          const xhr = new window.XMLHttpRequest();
          xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
              const pct = Math.round((e.loaded / e.total) * 100);
              $progress.text(`Uploading cover image... ${pct}%`);
            }
          });
          return xhr;
        },
        success: function (res) {
          $progress.hide();
          if (res.success) {
            $urlIn.val(res.data.secure_url);
            $pidIn.val(res.data.public_id);
            $img.attr('src', res.data.secure_url);
            $preview.show();
            window.AdminToast && window.AdminToast.show('Cover image uploaded successfully.', 'success');
          } else {
            $area.show();
            window.AdminToast && window.AdminToast.show(res.message || 'Failed to upload image.', 'error');
          }
        },
        error: function () {
          $progress.hide();
          $area.show();
          window.AdminToast && window.AdminToast.show('Network error. Please try again.', 'error');
        },
      });
    }
  }

  // ── Slug Auto-gen from Title ──────────────────────────────

  function initSlugGen() {
    const $title = $('#project-title');
    const $slug  = $('#project-slug');
    let slugManuallyEdited = $slug.val() !== '';

    $slug.on('input', function () {
      slugManuallyEdited = true;
    });

    $title.on('input', function () {
      if (!slugManuallyEdited || $slug.val() === '') {
        const slug = $(this).val()
          .toLowerCase()
          .replace(/[^a-z0-9\s-]/g, '')
          .trim()
          .replace(/[\s_]+/g, '-')
          .replace(/-+/g, '-');
        $slug.val(slug);
        slugManuallyEdited = false;
      }
    });
  }

  // ── Form Submission ───────────────────────────────────────

  function initFormSubmit() {
    $('[data-action="save-draft"], [data-action="publish"]').on('click', function (e) {
      e.preventDefault();
      const action  = $(this).data('action');
      const status  = action === 'publish' ? 'published' : 'draft';
      const label   = action === 'publish' ? 'Publishing...' : 'Saving draft...';
      const $btn    = $(this);

      $btn.html(`<span class="spinner"></span> ${label}`).prop('disabled', true);
      $('input[name="status"]').val(status);
      $btn.closest('form').trigger('submit');
    });
  }

  // ── Char Counter ──────────────────────────────────────────

  function initCharCounters() {
    $('[data-maxlength]').each(function () {
      const $input = $(this);
      const max    = parseInt($input.data('maxlength'));
      const $counter = $('<span class="form-hint char-counter"></span>');
      $input.after($counter);

      function update() {
        const len = $input.val().length;
        $counter.text(`${len} / ${max}`);
        $counter.css('color', len > max ? 'var(--danger)' : 'var(--text-muted)');
      }

      $input.on('input', update);
      update();
    });
  }

  // ── Utilities ─────────────────────────────────────────────

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
  }

  // ── Init ──────────────────────────────────────────────────

  $(document).ready(function () {
    initTabs();
    initToolbar();
    initLivePreview();
    initTagInput();
    initCoverUpload();
    initSlugGen();
    initFormSubmit();
    initCharCounters();

    // Apply hljs on load (for existing code blocks)
    if (typeof hljs !== 'undefined') {
      hljs.highlightAll();
    }
  });

}(jQuery));
