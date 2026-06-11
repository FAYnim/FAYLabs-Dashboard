<?php
// ============================================================
// Admin — Create Project
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/helpers.php';
require_once ROOT_PATH . '/includes/validator.php';

csrfStart();
requireAdmin();

$pageTitle  = 'Create Project';
$activePage = 'create';
$csrfToken  = csrfGenerate();
$loadEditor = true;

// Handle form POST (non-AJAX fallback)
$errors  = [];
$success = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST;
    $techStack = json_decode($_POST['tech_stack_json'] ?? '[]', true) ?: [];

    $data = [
        'title'           => trim($input['title']           ?? ''),
        'slug'            => trim($input['slug']            ?? ''),
        'description'     => trim($input['description']     ?? ''),
        'cover_image'     => trim($input['cover_image']     ?? ''),
        'cover_public_id' => trim($input['cover_public_id'] ?? ''),
        'label'           => trim($input['label']           ?? ''),
        'content'         => trim($input['content']         ?? ''),
        'tech_stack'      => $techStack,
        'github_url'      => trim($input['github_url']      ?? ''),
        'demo_url'        => trim($input['demo_url']        ?? ''),
        'project_year'    => (int)($input['project_year']   ?? date('Y')),
        'status'          => trim($input['status']          ?? 'draft'),
        'seo_title'       => trim($input['seo_title']       ?? ''),
        'seo_description' => trim($input['seo_description'] ?? ''),
    ];

    $formData = $data;

    if (!csrfVerify($input['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        // ... validation would go here for fallback
        // Primary path is AJAX
    }
}

require_once ROOT_PATH . '/admin/partials/head.php';
?>

<!-- Global JS Config -->
<script>
  const FAY_CONFIG = {
    apiBase:   '<?= APP_URL ?>/../api',
    adminBase: '<?= APP_URL ?>/../admin',
    csrfToken: '<?= htmlspecialchars($csrfToken) ?>',
  };
</script>

<?php require_once ROOT_PATH . '/admin/partials/sidebar.php'; ?>

<div class="main-wrapper">
  <!-- Topbar -->
  <header class="topbar">
    <button id="sidebar-toggle" class="topbar-hamburger" aria-label="Toggle sidebar">
      <svg viewBox="0 0 16 16"><path d="M1 2.75A.75.75 0 011.75 2h12.5a.75.75 0 010 1.5H1.75A.75.75 0 011 2.75zm0 5A.75.75 0 011.75 7h12.5a.75.75 0 010 1.5H1.75A.75.75 0 011 7.75zM1.75 12a.75.75 0 000 1.5h12.5a.75.75 0 000-1.5H1.75z"/></svg>
    </button>
    <span class="topbar-title">Create Project</span>
    <div class="topbar-actions">
      <a href="<?= APP_URL ?>/../admin/projects/index.php" class="btn btn-secondary btn-sm">
        <svg viewBox="0 0 16 16"><path d="M7.78 12.53a.75.75 0 01-1.06 0L2.47 8.28a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 1.06L4.81 7h7.44a.75.75 0 010 1.5H4.81l2.97 2.97a.75.75 0 010 1.06z"/></svg>
        Back
      </a>
    </div>
  </header>

  <main class="page-content" style="max-width:none;">
    <form id="project-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
      <input type="hidden" name="status" value="draft">
      <input type="hidden" id="cover-image-url" name="cover_image" value="">
      <input type="hidden" id="cover-public-id" name="cover_public_id" value="">
      <input type="hidden" id="tech-stack-hidden" name="tech_stack" value="[]">

      <div class="editor-layout">

        <!-- ── Main Editor Area ── -->
        <div class="editor-main">
          <!-- Title -->
          <div class="form-group">
            <label class="form-label" for="project-title">
              Project Title <span class="required">*</span>
            </label>
            <input type="text" id="project-title" name="title" class="form-control"
                   placeholder="e.g. Visura, Travel CRM, Lynk Manager"
                   data-maxlength="150" required>
          </div>

          <!-- Markdown Editor -->
          <div class="editor-panel">
            <div class="editor-tabs" role="tablist">
              <button type="button" class="editor-tab active" data-target="write"
                      role="tab" aria-selected="true" id="tab-write">
                <svg viewBox="0 0 16 16" width="14" height="14" style="fill:currentColor;vertical-align:-2px;margin-right:4px">
                  <path d="M11.013 1.427a1.75 1.75 0 012.474 0l1.086 1.086a1.75 1.75 0 010 2.474l-8.61 8.61c-.21.21-.47.364-.756.445l-3.251.93a.75.75 0 01-.927-.928l.929-3.25c.081-.286.235-.547.445-.758l8.61-8.61z"/>
                </svg>
                Write
              </button>
              <button type="button" class="editor-tab" data-target="preview"
                      role="tab" aria-selected="false" id="tab-preview">
                <svg viewBox="0 0 16 16" width="14" height="14" style="fill:currentColor;vertical-align:-2px;margin-right:4px">
                  <path d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0zM8 0a8 8 0 100 16A8 8 0 008 0zM6.379 5.227A.25.25 0 006 5.442v5.117a.25.25 0 00.379.214l4.264-2.559a.25.25 0 000-.428L6.379 5.227z"/>
                </svg>
                Preview
              </button>
            </div>

            <!-- Toolbar -->
            <div class="editor-toolbar" role="toolbar" aria-label="Markdown formatting">
              <button type="button" class="toolbar-btn" data-action="h1" title="Heading 1">H1</button>
              <button type="button" class="toolbar-btn" data-action="h2" title="Heading 2">H2</button>
              <button type="button" class="toolbar-btn" data-action="h3" title="Heading 3">H3</button>
              <span class="toolbar-separator" aria-hidden="true"></span>
              <button type="button" class="toolbar-btn" data-action="bold" title="Bold" style="font-weight:900">B</button>
              <button type="button" class="toolbar-btn" data-action="italic" title="Italic" style="font-style:italic">I</button>
              <span class="toolbar-separator" aria-hidden="true"></span>
              <button type="button" class="toolbar-btn" data-action="link" title="Link">
                <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"/></svg>
              </button>
              <button type="button" class="toolbar-btn" data-action="image" title="Image">
                <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M16 13.25A1.75 1.75 0 0114.25 15H1.75A1.75 1.75 0 010 13.25V2.75A1.75 1.75 0 011.75 1h12.5A1.75 1.75 0 0116 2.75v10.5zM6 5a1 1 0 100 2 1 1 0 000-2zm-1.5 5.5L6 9l2 2.5 2.5-3L13 14H3l1.5-3.5z"/></svg>
              </button>
              <span class="toolbar-separator" aria-hidden="true"></span>
              <button type="button" class="toolbar-btn" data-action="code" title="Inline Code">
                <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M4.72 3.22a.75.75 0 011.06 1.06L2.06 8l3.72 3.72a.75.75 0 11-1.06 1.06L.47 8.53a.75.75 0 010-1.06l4.25-4.25zm6.56 0a.75.75 0 10-1.06 1.06L13.94 8l-3.72 3.72a.75.75 0 101.06 1.06l4.25-4.25a.75.75 0 000-1.06l-4.25-4.25z"/></svg>
              </button>
              <button type="button" class="toolbar-btn" data-action="codeblock" title="Code Block">
                <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M0 1.75C0 .784.784 0 1.75 0h12.5C15.216 0 16 .784 16 1.75v12.5A1.75 1.75 0 0114.25 16H1.75A1.75 1.75 0 010 14.25V1.75zm1.75-.25a.25.25 0 00-.25.25v12.5c0 .138.112.25.25.25h12.5a.25.25 0 00.25-.25V1.75a.25.25 0 00-.25-.25H1.75z"/></svg>
              </button>
              <button type="button" class="toolbar-btn" data-action="quote" title="Blockquote">
                <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M1 2.75A.75.75 0 011.75 2h12.5a.75.75 0 110 1.5H1.75A.75.75 0 011 2.75zm4 5A.75.75 0 015.75 7h8.5a.75.75 0 110 1.5h-8.5A.75.75 0 015 7.75zM5 12.75a.75.75 0 01.75-.75h8.5a.75.75 0 110 1.5h-8.5a.75.75 0 01-.75-.75zM1.75 7a.75.75 0 100 1.5.75.75 0 000-1.5zm0 5a.75.75 0 100 1.5.75.75 0 000-1.5z"/></svg>
              </button>
              <span class="toolbar-separator" aria-hidden="true"></span>
              <button type="button" class="toolbar-btn" data-action="ul" title="Unordered List">
                <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M2 4a1 1 0 100-2 1 1 0 000 2zm3.75-1.5a.75.75 0 000 1.5h8.5a.75.75 0 000-1.5h-8.5zm0 5a.75.75 0 000 1.5h8.5a.75.75 0 000-1.5h-8.5zm0 5a.75.75 0 000 1.5h8.5a.75.75 0 000-1.5h-8.5zM3 8a1 1 0 11-2 0 1 1 0 012 0zm-1 6a1 1 0 100-2 1 1 0 000 2z"/></svg>
              </button>
              <button type="button" class="toolbar-btn" data-action="ol" title="Ordered List">
                <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M5 3.25a.75.75 0 01.75-.75h8.5a.75.75 0 010 1.5h-8.5A.75.75 0 015 3.25zm0 5a.75.75 0 01.75-.75h8.5a.75.75 0 010 1.5h-8.5A.75.75 0 015 8.25zm0 5a.75.75 0 01.75-.75h8.5a.75.75 0 010 1.5h-8.5a.75.75 0 01-.75-.75zM1.5 2.5h1v4h-1V2.5zm.25 9h.75V11H2a.25.25 0 000 .5h.75v.5H2a.25.25 0 000 .5h1a.25.25 0 000-.5h-.25V13h-.25a.25.25 0 000 .5H2.75a.25.25 0 000-.5z"/></svg>
              </button>
              <button type="button" class="toolbar-btn" data-action="table" title="Table">
                <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><path d="M0 1.75C0 .784.784 0 1.75 0h12.5C15.216 0 16 .784 16 1.75v12.5A1.75 1.75 0 0114.25 16H1.75A1.75 1.75 0 010 14.25V1.75zm1.75-.25a.25.25 0 00-.25.25V4h13V1.75a.25.25 0 00-.25-.25H1.75zM15 5.5H9v2h6v-2zm0 3.5H9v2h6V9zm0 3.5H9v2h5.25a.25.25 0 00.25-.25V12.5zm-7 2v-2H1v1.75c0 .138.112.25.25.25H8zm-7-3.5h7V9H1v2.5zm0-4H7V5.5H1v2z"/></svg>
              </button>
              <button type="button" class="toolbar-btn" data-action="hr" title="Horizontal Rule">—</button>
            </div>

            <!-- Write Area -->
            <div id="editor-textarea-area">
              <textarea id="editor-content" name="content" class="editor-textarea"
                        placeholder="# Project Overview&#10;&#10;Write your project documentation here using Markdown..."
                        aria-label="Markdown content editor" spellcheck="false"></textarea>
            </div>

            <!-- Preview Area -->
            <div id="editor-preview" class="editor-preview markdown-body" aria-live="polite">
              <p style="color:var(--text-muted);font-style:italic">Preview will appear here...</p>
            </div>
          </div>
        </div>

        <!-- ── Metadata Sidebar ── -->
        <aside class="editor-meta">
          <h3>Project Details</h3>

          <!-- Slug -->
          <div class="form-group">
            <label class="form-label" for="project-slug">
              Slug <span class="required">*</span>
            </label>
            <input type="text" id="project-slug" name="slug" class="form-control"
                   placeholder="project-slug" data-maxlength="180"
                   pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$" required>
            <p class="form-hint">Auto-generated from title. Only lowercase letters, numbers, hyphens.</p>
          </div>

          <!-- Short Description -->
          <div class="form-group">
            <label class="form-label" for="project-desc">
              Short Description <span class="required">*</span>
            </label>
            <textarea id="project-desc" name="description" class="form-control"
                      placeholder="A brief description of the project..."
                      rows="3" data-maxlength="300" required></textarea>
          </div>

          <!-- Cover Image -->
          <div class="form-group">
            <label class="form-label">
              Cover Image <span class="required">*</span>
            </label>

            <!-- Upload Area -->
            <div id="cover-upload-area" class="cover-upload-area" role="button" tabindex="0"
                 aria-label="Upload cover image">
              <svg viewBox="0 0 24 24" width="32" height="32" fill="var(--text-muted)" aria-hidden="true">
                <path d="M19.35 10.04A7.49 7.49 0 0012 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 000 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
              </svg>
              <p>Drag & drop or <span>click to upload</span></p>
              <p style="font-size:11px;margin-top:4px">JPG, PNG, WEBP · Max 2 MB</p>
              <input type="file" id="cover-file-input" accept="image/jpeg,image/png,image/webp"
                     style="display:none" aria-label="Select cover image file">
            </div>

            <!-- Preview -->
            <div id="cover-preview" class="cover-preview" style="display:none">
              <img id="cover-preview-img" src="" alt="Cover preview">
              <div class="cover-preview-actions">
                <button type="button" id="cover-remove-btn" class="btn btn-secondary btn-sm">
                  <svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor">
                    <path d="M3.72 3.72a.75.75 0 011.06 0L8 6.94l3.22-3.22a.75.75 0 111.06 1.06L9.06 8l3.22 3.22a.75.75 0 11-1.06 1.06L8 9.06l-3.22 3.22a.75.75 0 01-1.06-1.06L6.94 8 3.72 4.78a.75.75 0 010-1.06z"/>
                  </svg>
                  Remove
                </button>
              </div>
            </div>
            <div id="cover-upload-progress" class="cover-upload-progress" style="display:none"></div>
          </div>

          <!-- Label -->
          <div class="form-group">
            <label class="form-label" for="project-label">
              Label <span class="required">*</span>
            </label>
            <select id="project-label" name="label" class="form-control" required>
              <option value="">Select label...</option>
              <option value="AI">AI</option>
              <option value="Web App">Web App</option>
              <option value="SaaS">SaaS</option>
              <option value="IoT">IoT</option>
              <option value="Mobile">Mobile</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <!-- Tech Stack -->
          <div class="form-group">
            <label class="form-label" for="tag-real-input">
              Tech Stack <span class="required">*</span>
            </label>
            <div id="tag-input-wrapper" class="tag-input-wrapper" role="group" aria-label="Tech stack tags">
              <input type="text" id="tag-real-input" class="tag-real-input"
                     placeholder="Type & press Enter..." aria-label="Add tech stack item">
            </div>
            <p class="form-hint">Press Enter or comma to add. Max 20 items.</p>
          </div>

          <div class="divider"></div>
          <h3>Links & Meta</h3>

          <!-- GitHub URL -->
          <div class="form-group">
            <label class="form-label" for="github-url">GitHub URL</label>
            <input type="url" id="github-url" name="github_url" class="form-control"
                   placeholder="https://github.com/username/repo">
          </div>

          <!-- Demo URL -->
          <div class="form-group">
            <label class="form-label" for="demo-url">Demo / Live URL</label>
            <input type="url" id="demo-url" name="demo_url" class="form-control"
                   placeholder="https://yourproject.com">
          </div>

          <!-- Project Year -->
          <div class="form-group">
            <label class="form-label" for="project-year">
              Project Year <span class="required">*</span>
            </label>
            <input type="number" id="project-year" name="project_year" class="form-control"
                   min="2020" max="<?= date('Y') + 1 ?>"
                   value="<?= date('Y') ?>" required>
          </div>

          <div class="divider"></div>
          <h3>SEO (Optional)</h3>

          <div class="form-group">
            <label class="form-label" for="seo-title">SEO Title</label>
            <input type="text" id="seo-title" name="seo_title" class="form-control"
                   placeholder="Custom page title..." data-maxlength="180">
            <p class="form-hint">Leave empty to use "{title} | FAY Portfolio"</p>
          </div>

          <div class="form-group">
            <label class="form-label" for="seo-desc">SEO Description</label>
            <textarea id="seo-desc" name="seo_description" class="form-control"
                      rows="2" placeholder="Custom meta description..."
                      data-maxlength="300"></textarea>
          </div>

          <div class="divider"></div>

          <!-- Action Buttons -->
          <div style="display:flex;flex-direction:column;gap:8px;">
            <button type="button" class="btn btn-success" data-action="publish" id="btn-publish">
              <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor">
                <path d="M8 0a8 8 0 110 16A8 8 0 018 0zm3.78 4.22a.75.75 0 00-1.06 0L6.75 7.19 5.28 5.72a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.06 0l4.5-4.5a.75.75 0 000-1.06z"/>
              </svg>
              Publish
            </button>
            <button type="button" class="btn btn-secondary" data-action="save-draft" id="btn-draft">
              <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor">
                <path d="M2.75 1A1.75 1.75 0 001 2.75v10.5C1 14.216 1.784 15 2.75 15h10.5A1.75 1.75 0 0015 13.25V4.56a.25.25 0 00-.073-.177l-3.31-3.31A.25.25 0 0011.44 1H2.75zm0 1.5h8.19l2.81 2.81v9.44a.25.25 0 01-.25.25H2.75a.25.25 0 01-.25-.25V2.75a.25.25 0 01.25-.25zM6 7.25a.75.75 0 01.75-.75h2.5a.75.75 0 010 1.5h-2.5A.75.75 0 016 7.25zM5.25 10a.75.75 0 000 1.5h5.5a.75.75 0 000-1.5h-5.5z"/>
              </svg>
              Save Draft
            </button>
            <a href="<?= APP_URL ?>/../admin/projects/index.php" class="btn btn-secondary" id="btn-cancel">
              Cancel
            </a>
          </div>

        </aside>
      </div><!-- /editor-layout -->
    </form>
  </main>
</div>

<div id="toast-container" class="toast-container" aria-live="polite"></div>

<!-- Form Submit Handler -->
<script>
$(document).ready(function () {
  $('#project-form').on('submit', function (e) {
    e.preventDefault();

    const $form   = $(this);
    const status  = $('input[name="status"]').val();
    const $btn    = status === 'published' ? $('#btn-publish') : $('#btn-draft');
    const label   = status === 'published' ? 'Publishing...' : 'Saving draft...';

    // Collect form data
    const techStackJson = $('#tech-stack-hidden').val();
    let techStack = [];
    try { techStack = JSON.parse(techStackJson); } catch(e) {}

    if (techStack.length < 1) {
      window.AdminToast.show('Please add at least one tech stack item.', 'error');
      return;
    }

    if (!$('#cover-image-url').val()) {
      window.AdminToast.show('Please upload a cover image.', 'error');
      return;
    }

    const payload = {
      csrf_token:      FAY_CONFIG.csrfToken,
      title:           $('#project-title').val().trim(),
      slug:            $('#project-slug').val().trim(),
      description:     $('#project-desc').val().trim(),
      cover_image:     $('#cover-image-url').val(),
      cover_public_id: $('#cover-public-id').val(),
      label:           $('#project-label').val(),
      content:         $('#editor-content').val().trim(),
      tech_stack:      techStack,
      github_url:      $('#github-url').val().trim(),
      demo_url:        $('#demo-url').val().trim(),
      project_year:    parseInt($('#project-year').val()),
      status:          status,
      seo_title:       $('#seo-title').val().trim(),
      seo_description: $('#seo-desc').val().trim(),
    };

    // Basic client validation
    if (!payload.title || payload.title.length < 3) {
      window.AdminToast.show('Title must be at least 3 characters.', 'error');
      return;
    }
    if (!payload.slug || payload.slug.length < 3) {
      window.AdminToast.show('Slug must be at least 3 characters.', 'error');
      return;
    }
    if (!payload.description || payload.description.length < 10) {
      window.AdminToast.show('Description must be at least 10 characters.', 'error');
      return;
    }
    if (!payload.content || payload.content.length < 20) {
      window.AdminToast.show('Content must be at least 20 characters.', 'error');
      return;
    }

    $btn.html('<span class="spinner"></span> ' + label).prop('disabled', true);

    $.ajax({
      url: FAY_CONFIG.apiBase + '/projects/create.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(payload),
      success: function (res) {
        if (res.success) {
          const msg = encodeURIComponent(res.message || 'Project created successfully.');
          window.location.href = FAY_CONFIG.adminBase + '/projects/index.php?success=' + msg;
        } else {
          window.AdminToast.show(res.message || 'Failed to save project.', 'error');
          if (res.errors) {
            const firstError = Object.values(res.errors)[0];
            if (firstError) window.AdminToast.show(firstError, 'error');
          }
          $btn.html(status === 'published' ? 'Publish' : 'Save Draft').prop('disabled', false);
        }
      },
      error: function () {
        window.AdminToast.show('Network error. Please try again.', 'error');
        $btn.html(status === 'published' ? 'Publish' : 'Save Draft').prop('disabled', false);
      }
    });
  });

  // Button click handlers
  $('#btn-publish').on('click', function () {
    $('input[name="status"]').val('published');
    $('#project-form').trigger('submit');
  });

  $('#btn-draft').on('click', function () {
    $('input[name="status"]').val('draft');
    $('#project-form').trigger('submit');
  });
});
</script>

<?php require_once ROOT_PATH . '/admin/partials/footer.php'; ?>
