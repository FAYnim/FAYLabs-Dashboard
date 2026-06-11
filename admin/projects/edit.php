<?php
// ============================================================
// Admin — Edit Project
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();
requireAdmin();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . APP_URL . '/../admin/projects/index.php');
    exit;
}

// Fetch project
try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
} catch (Exception $e) {
    $project = null;
}

if (!$project) {
    header('Location: ' . APP_URL . '/../admin/projects/index.php?error=' . urlencode('Project not found.'));
    exit;
}

$techStack = parseTechStack($project['tech_stack']);
$pageTitle  = 'Edit: ' . $project['title'];
$activePage = 'projects';
$csrfToken  = csrfGenerate();
$loadEditor = true;

require_once ROOT_PATH . '/admin/partials/head.php';
?>

<script>
  const FAY_CONFIG = {
    apiBase:   '<?= APP_URL ?>/../api',
    adminBase: '<?= APP_URL ?>/../admin',
    csrfToken: '<?= htmlspecialchars($csrfToken) ?>',
  };
  const EDIT_PROJECT_ID = <?= $id ?>;
  const EDIT_TECH_STACK = <?= json_encode($techStack) ?>;
</script>

<?php require_once ROOT_PATH . '/admin/partials/sidebar.php'; ?>

<div class="main-wrapper">
  <header class="topbar">
    <button id="sidebar-toggle" class="topbar-hamburger" aria-label="Toggle sidebar">
      <svg viewBox="0 0 16 16"><path d="M1 2.75A.75.75 0 011.75 2h12.5a.75.75 0 010 1.5H1.75A.75.75 0 011 2.75zm0 5A.75.75 0 011.75 7h12.5a.75.75 0 010 1.5H1.75A.75.75 0 011 7.75zM1.75 12a.75.75 0 000 1.5h12.5a.75.75 0 000-1.5H1.75z"/></svg>
    </button>
    <span class="topbar-title">Edit Project</span>
    <div class="topbar-actions">
      <a href="<?= APP_URL ?>/../admin/projects/index.php" class="btn btn-secondary btn-sm">
        <svg viewBox="0 0 16 16"><path d="M7.78 12.53a.75.75 0 01-1.06 0L2.47 8.28a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 1.06L4.81 7h7.44a.75.75 0 010 1.5H4.81l2.97 2.97a.75.75 0 010 1.06z"/></svg>
        Back
      </a>
      <?php if ($project['status'] === 'published'): ?>
      <a href="<?= APP_URL ?>/projects/<?= e($project['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">
        <svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor"><path d="M3.75 2h3.5a.75.75 0 010 1.5h-3.5a.25.25 0 00-.25.25v8.5c0 .138.112.25.25.25h8.5a.25.25 0 00.25-.25v-3.5a.75.75 0 011.5 0v3.5A1.75 1.75 0 0112.25 14h-8.5A1.75 1.75 0 012 12.25v-8.5C2 2.784 2.784 2 3.75 2zm6.854-1h4.146a.25.25 0 01.25.25v4.146a.25.25 0 01-.427.177L13.03 4.03 9.28 7.78a.75.75 0 01-1.06-1.06l3.75-3.75-1.543-1.543A.25.25 0 0110.604 1z"/></svg>
        View Live
      </a>
      <?php endif; ?>
    </div>
  </header>

  <main class="page-content" style="max-width:none;">
    <!-- Last Updated -->
    <div class="last-updated" style="margin-bottom:16px;">
      Last updated: <?= formatDatetime($project['updated_at']) ?>
      <?php if ($project['published_at']): ?>
      · Published: <?= formatDatetime($project['published_at']) ?>
      <?php endif; ?>
    </div>

    <form id="project-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
      <input type="hidden" name="status" value="<?= e($project['status']) ?>">
      <input type="hidden" id="cover-image-url" name="cover_image" value="<?= e($project['cover_image']) ?>">
      <input type="hidden" id="cover-public-id" name="cover_public_id" value="<?= e($project['cover_public_id'] ?? '') ?>">
      <input type="hidden" id="tech-stack-hidden" name="tech_stack" value="<?= e(json_encode($techStack)) ?>">

      <div class="editor-layout">

        <!-- Main Editor Area -->
        <div class="editor-main">
          <div class="form-group">
            <label class="form-label" for="project-title">
              Project Title <span class="required">*</span>
            </label>
            <input type="text" id="project-title" name="title" class="form-control"
                   value="<?= e($project['title']) ?>"
                   placeholder="e.g. Visura" data-maxlength="150" required>
          </div>

          <!-- Markdown Editor (same as create) -->
          <div class="editor-panel">
            <div class="editor-tabs" role="tablist">
              <button type="button" class="editor-tab active" data-target="write" role="tab">
                <svg viewBox="0 0 16 16" width="14" height="14" style="fill:currentColor;vertical-align:-2px;margin-right:4px"><path d="M11.013 1.427a1.75 1.75 0 012.474 0l1.086 1.086a1.75 1.75 0 010 2.474l-8.61 8.61c-.21.21-.47.364-.756.445l-3.251.93a.75.75 0 01-.927-.928l.929-3.25c.081-.286.235-.547.445-.758l8.61-8.61z"/></svg>
                Write
              </button>
              <button type="button" class="editor-tab" data-target="preview" role="tab">
                <svg viewBox="0 0 16 16" width="14" height="14" style="fill:currentColor;vertical-align:-2px;margin-right:4px"><path d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0zM8 0a8 8 0 100 16A8 8 0 008 0z"/></svg>
                Preview
              </button>
            </div>

            <div class="editor-toolbar" role="toolbar">
              <button type="button" class="toolbar-btn" data-action="h1" title="Heading 1">H1</button>
              <button type="button" class="toolbar-btn" data-action="h2" title="Heading 2">H2</button>
              <button type="button" class="toolbar-btn" data-action="h3" title="Heading 3">H3</button>
              <span class="toolbar-separator" aria-hidden="true"></span>
              <button type="button" class="toolbar-btn" data-action="bold" title="Bold" style="font-weight:900">B</button>
              <button type="button" class="toolbar-btn" data-action="italic" title="Italic" style="font-style:italic">I</button>
              <span class="toolbar-separator" aria-hidden="true"></span>
              <button type="button" class="toolbar-btn" data-action="link" title="Link">🔗</button>
              <button type="button" class="toolbar-btn" data-action="image" title="Image">🖼</button>
              <button type="button" class="toolbar-btn" data-action="code" title="Code">&lt;/&gt;</button>
              <button type="button" class="toolbar-btn" data-action="codeblock" title="Code Block">⬛</button>
              <button type="button" class="toolbar-btn" data-action="quote" title="Quote">❝</button>
              <span class="toolbar-separator" aria-hidden="true"></span>
              <button type="button" class="toolbar-btn" data-action="ul" title="Unordered List">•—</button>
              <button type="button" class="toolbar-btn" data-action="ol" title="Ordered List">1—</button>
              <button type="button" class="toolbar-btn" data-action="table" title="Table">⊞</button>
              <button type="button" class="toolbar-btn" data-action="hr" title="Horizontal Rule">—</button>
            </div>

            <div id="editor-textarea-area">
              <textarea id="editor-content" name="content" class="editor-textarea"
                        spellcheck="false" aria-label="Markdown content editor"><?= htmlspecialchars($project['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div id="editor-preview" class="editor-preview markdown-body" aria-live="polite">
              <p style="color:var(--text-muted);font-style:italic">Click Preview tab to see rendered output.</p>
            </div>
          </div>
        </div>

        <!-- Metadata Sidebar -->
        <aside class="editor-meta">
          <h3>Project Details</h3>

          <div class="form-group">
            <label class="form-label" for="project-slug">Slug <span class="required">*</span></label>
            <input type="text" id="project-slug" name="slug" class="form-control"
                   value="<?= e($project['slug']) ?>"
                   pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$"
                   data-maxlength="180" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="project-desc">Short Description <span class="required">*</span></label>
            <textarea id="project-desc" name="description" class="form-control"
                      rows="3" data-maxlength="300" required><?= e($project['description']) ?></textarea>
          </div>

          <!-- Cover Image -->
          <div class="form-group">
            <label class="form-label">Cover Image <span class="required">*</span></label>

            <div id="cover-upload-area" class="cover-upload-area" role="button" tabindex="0"
                 style="<?= $project['cover_image'] ? 'display:none' : '' ?>">
              <svg viewBox="0 0 24 24" width="32" height="32" fill="var(--text-muted)">
                <path d="M19.35 10.04A7.49 7.49 0 0012 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 000 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
              </svg>
              <p>Drag & drop or <span>click to upload</span></p>
              <p style="font-size:11px;margin-top:4px">JPG, PNG, WEBP · Max 2 MB</p>
              <input type="file" id="cover-file-input" accept="image/jpeg,image/png,image/webp"
                     style="display:none">
            </div>

            <div id="cover-preview" class="cover-preview"
                 style="<?= $project['cover_image'] ? '' : 'display:none' ?>">
              <img id="cover-preview-img"
                   src="<?= e($project['cover_image']) ?>"
                   alt="Cover preview">
              <div class="cover-preview-actions">
                <button type="button" id="cover-remove-btn" class="btn btn-secondary btn-sm">Remove</button>
              </div>
            </div>
            <div id="cover-upload-progress" class="cover-upload-progress" style="display:none"></div>
          </div>

          <!-- Label -->
          <div class="form-group">
            <label class="form-label" for="project-label">Label <span class="required">*</span></label>
            <select id="project-label" name="label" class="form-control" required>
              <?php foreach (['AI','Web App','SaaS','IoT','Mobile','Other'] as $opt): ?>
              <option value="<?= $opt ?>" <?= $project['label'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Tech Stack -->
          <div class="form-group">
            <label class="form-label" for="tag-real-input">Tech Stack <span class="required">*</span></label>
            <div id="tag-input-wrapper" class="tag-input-wrapper" role="group" aria-label="Tech stack tags">
              <input type="text" id="tag-real-input" class="tag-real-input"
                     placeholder="Type & press Enter...">
            </div>
            <p class="form-hint">Press Enter to add. Max 20 items.</p>
          </div>

          <div class="divider"></div>
          <h3>Links & Meta</h3>

          <div class="form-group">
            <label class="form-label" for="github-url">GitHub URL</label>
            <input type="url" id="github-url" name="github_url" class="form-control"
                   value="<?= e($project['github_url'] ?? '') ?>"
                   placeholder="https://github.com/...">
          </div>

          <div class="form-group">
            <label class="form-label" for="demo-url">Demo / Live URL</label>
            <input type="url" id="demo-url" name="demo_url" class="form-control"
                   value="<?= e($project['demo_url'] ?? '') ?>"
                   placeholder="https://...">
          </div>

          <div class="form-group">
            <label class="form-label" for="project-year">Project Year <span class="required">*</span></label>
            <input type="number" id="project-year" name="project_year" class="form-control"
                   min="2020" max="<?= date('Y') + 1 ?>"
                   value="<?= (int)$project['project_year'] ?>" required>
          </div>

          <div class="divider"></div>
          <h3>SEO (Optional)</h3>

          <div class="form-group">
            <label class="form-label" for="seo-title">SEO Title</label>
            <input type="text" id="seo-title" name="seo_title" class="form-control"
                   value="<?= e($project['seo_title'] ?? '') ?>"
                   data-maxlength="180">
          </div>

          <div class="form-group">
            <label class="form-label" for="seo-desc">SEO Description</label>
            <textarea id="seo-desc" name="seo_description" class="form-control"
                      rows="2" data-maxlength="300"><?= e($project['seo_description'] ?? '') ?></textarea>
          </div>

          <div class="divider"></div>

          <!-- Status Info -->
          <div style="margin-bottom:12px;">
            <span class="form-label">Current Status</span>
            <span class="status-badge <?= getStatusClass($project['status']) ?>" style="display:inline-flex;margin-top:4px">
              <?= ucfirst($project['status']) ?>
            </span>
          </div>

          <!-- Action Buttons -->
          <div style="display:flex;flex-direction:column;gap:8px;">
            <button type="button" class="btn btn-success" data-action="publish" id="btn-publish">
              <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor"><path d="M8 0a8 8 0 110 16A8 8 0 018 0zm3.78 4.22a.75.75 0 00-1.06 0L6.75 7.19 5.28 5.72a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.06 0l4.5-4.5a.75.75 0 000-1.06z"/></svg>
              <?= $project['status'] === 'published' ? 'Update Published' : 'Publish' ?>
            </button>
            <button type="button" class="btn btn-secondary" data-action="save-draft" id="btn-draft">
              <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor"><path d="M2.75 1A1.75 1.75 0 001 2.75v10.5C1 14.216 1.784 15 2.75 15h10.5A1.75 1.75 0 0015 13.25V4.56a.25.25 0 00-.073-.177l-3.31-3.31A.25.25 0 0011.44 1H2.75z"/></svg>
              Save as Draft
            </button>
            <a href="<?= APP_URL ?>/../admin/projects/index.php" class="btn btn-secondary">Cancel</a>
          </div>
        </aside>
      </div>
    </form>
  </main>
</div>

<div id="toast-container" class="toast-container" aria-live="polite"></div>

<!-- Edit-specific form logic -->
<script>
$(document).ready(function () {
  // Pre-load tech stack from PHP
  if (typeof EDIT_TECH_STACK !== 'undefined' && EDIT_TECH_STACK.length > 0) {
    $('#tech-stack-hidden').val(JSON.stringify(EDIT_TECH_STACK));
    // Tag init will read this on load
  }

  $('#project-form').on('submit', function (e) {
    e.preventDefault();

    const status = $('input[name="status"]').val();
    const $btn   = status === 'published' ? $('#btn-publish') : $('#btn-draft');
    const label  = status === 'published' ? 'Publishing...' : 'Saving draft...';

    let techStack = [];
    try { techStack = JSON.parse($('#tech-stack-hidden').val() || '[]'); } catch(e) {}

    if (techStack.length < 1) {
      window.AdminToast.show('Please add at least one tech stack item.', 'error');
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

    $btn.html('<span class="spinner"></span> ' + label).prop('disabled', true);

    $.ajax({
      url: FAY_CONFIG.apiBase + '/projects/update.php?id=' + EDIT_PROJECT_ID,
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(payload),
      success: function (res) {
        if (res.success) {
          const msg = encodeURIComponent(res.message || 'Project updated successfully.');
          window.location.href = FAY_CONFIG.adminBase + '/projects/index.php?success=' + msg;
        } else {
          window.AdminToast.show(res.message || 'Failed to update project.', 'error');
          $btn.html(status === 'published' ? 'Update Published' : 'Save as Draft').prop('disabled', false);
        }
      },
      error: function () {
        window.AdminToast.show('Network error. Please try again.', 'error');
        $btn.html(status === 'published' ? 'Update Published' : 'Save as Draft').prop('disabled', false);
      }
    });
  });

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
