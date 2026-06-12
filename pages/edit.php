<?php
// ============================================================
// Edit Project
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();
requireAdmin();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_PATH . '/');
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
    header('Location: ' . BASE_PATH . '/?error=' . urlencode('Project not found.'));
    exit;
}

$techStack = parseTechStack($project['tech_stack']);
$pageTitle  = 'Edit: ' . $project['title'];
$activePage = 'projects';
$csrfToken  = csrfGenerate();
$loadEditor = true;

require_once ROOT_PATH . '/partials/head.php';
?>

<script>
  const FAY_CONFIG = {
    apiBase:   '<?= BASE_PATH ?>/api',
    adminBase: '<?= BASE_PATH ?>',
    csrfToken: '<?= htmlspecialchars($csrfToken) ?>',
  };
  const EDIT_PROJECT_ID = <?= $id ?>;
  const EDIT_TECH_STACK = <?= json_encode($techStack) ?>;
</script>

<?php require_once ROOT_PATH . '/partials/sidebar.php'; ?>

<div class="main-wrapper">
  <header class="topbar">
    <button id="sidebar-toggle" class="topbar-hamburger" aria-label="Toggle sidebar">
      <i class="bi bi-list" aria-hidden="true"></i>
    </button>
    <span class="topbar-title">Edit Project</span>
    <div class="topbar-actions">
      <a href="<?= BASE_PATH ?>/" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        Back
      </a>
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

    <form id="project-form" data-endpoint="<?= BASE_PATH ?>/api/projects/update.php?id=<?= $id ?>" novalidate>
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
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                Write
              </button>
              <button type="button" class="editor-tab" data-target="preview" role="tab">
                <i class="bi bi-eye" aria-hidden="true"></i>
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
            <span class="form-label">Cover Image <span class="required">*</span></span>

            <label id="cover-upload-area" for="cover-file-input" class="cover-upload-area"
                   style="<?= $project['cover_image'] ? 'display:none' : '' ?>">
              <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
              <p>Drag & drop or <span>click to upload</span></p>
              <p style="font-size:11px;margin-top:4px">JPG, PNG, WEBP · Max 2 MB</p>
              <input type="file" id="cover-file-input" accept="image/jpeg,image/png,image/webp"
                     style="display:none">
            </label>

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
              <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
              <?= $project['status'] === 'published' ? 'Update Published' : 'Publish' ?>
            </button>
            <button type="button" class="btn btn-secondary" data-action="save-draft" id="btn-draft">
              <i class="bi bi-save" aria-hidden="true"></i>
              Save as Draft
            </button>
            <a href="<?= BASE_PATH ?>/" class="btn btn-secondary">Cancel</a>
          </div>
        </aside>
      </div>
    </form>
  </main>
</div>

<div id="toast-container" class="toast-container" aria-live="polite"></div>

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
