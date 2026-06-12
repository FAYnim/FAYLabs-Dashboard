<?php
// ============================================================
// Create Project
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
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

require_once ROOT_PATH . '/partials/head.php';
?>

<!-- Global JS Config -->
<script>
  const FAY_CONFIG = {
    apiBase:   '<?= BASE_PATH ?>/api',
    adminBase: '<?= BASE_PATH ?>',
    csrfToken: '<?= htmlspecialchars($csrfToken) ?>',
  };
</script>

<?php require_once ROOT_PATH . '/partials/sidebar.php'; ?>

<div class="main-wrapper">
  <!-- Topbar -->
  <header class="topbar">
    <button id="sidebar-toggle" class="topbar-hamburger" aria-label="Toggle sidebar">
      <i class="bi bi-list" aria-hidden="true"></i>
    </button>
    <span class="topbar-title">Create Project</span>
    <div class="topbar-actions">
      <a href="<?= BASE_PATH ?>/" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        Back
      </a>
    </div>
  </header>

  <main class="page-content" style="max-width:none;">
    <form id="project-form" data-endpoint="<?= BASE_PATH ?>/api/projects/create.php" novalidate>
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
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                Write
              </button>
              <button type="button" class="editor-tab" data-target="preview"
                      role="tab" aria-selected="false" id="tab-preview">
                <i class="bi bi-eye" aria-hidden="true"></i>
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
                <i class="bi bi-link-45deg" aria-hidden="true"></i>
              </button>
              <button type="button" class="toolbar-btn" data-action="image" title="Image">
                <i class="bi bi-image" aria-hidden="true"></i>
              </button>
              <span class="toolbar-separator" aria-hidden="true"></span>
              <button type="button" class="toolbar-btn" data-action="code" title="Inline Code">
                <i class="bi bi-code-slash" aria-hidden="true"></i>
              </button>
              <button type="button" class="toolbar-btn" data-action="codeblock" title="Code Block">
                <i class="bi bi-code-square" aria-hidden="true"></i>
              </button>
              <button type="button" class="toolbar-btn" data-action="quote" title="Blockquote">
                <i class="bi bi-quote" aria-hidden="true"></i>
              </button>
              <span class="toolbar-separator" aria-hidden="true"></span>
              <button type="button" class="toolbar-btn" data-action="ul" title="Unordered List">
                <i class="bi bi-list-ul" aria-hidden="true"></i>
              </button>
              <button type="button" class="toolbar-btn" data-action="ol" title="Ordered List">
                <i class="bi bi-list-ol" aria-hidden="true"></i>
              </button>
              <button type="button" class="toolbar-btn" data-action="table" title="Table">
                <i class="bi bi-table" aria-hidden="true"></i>
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
            <label id="cover-upload-area" for="cover-file-input" class="cover-upload-area"
                   aria-label="Upload cover image">
              <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
              <p>Drag & drop or <span>click to upload</span></p>
              <p style="font-size:11px;margin-top:4px">JPG, PNG, WEBP · Max 2 MB</p>
              <input type="file" id="cover-file-input" accept="image/jpeg,image/png,image/webp"
                     style="display:none" aria-label="Select cover image file">
            </label>

            <!-- Preview -->
            <div id="cover-preview" class="cover-preview" style="display:none">
              <img id="cover-preview-img" src="" alt="Cover preview">
              <div class="cover-preview-actions">
                <button type="button" id="cover-remove-btn" class="btn btn-secondary btn-sm">
                  <i class="bi bi-x-lg" aria-hidden="true"></i>
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
              <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
              Publish
            </button>
            <button type="button" class="btn btn-secondary" data-action="save-draft" id="btn-draft">
              <i class="bi bi-save" aria-hidden="true"></i>
              Save Draft
            </button>
            <a href="<?= BASE_PATH ?>/" class="btn btn-secondary" id="btn-cancel">
              Cancel
            </a>
          </div>

        </aside>
      </div><!-- /editor-layout -->
    </form>
  </main>
</div>

<div id="toast-container" class="toast-container" aria-live="polite"></div>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
