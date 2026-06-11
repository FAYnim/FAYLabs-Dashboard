<?php
// ============================================================
// Admin — Projects List
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();
requireAdmin();

$pageTitle  = 'Projects';
$activePage = 'projects';
$csrfToken  = csrfGenerate();

// Fetch projects
try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->query(
        "SELECT id, title, slug, description, cover_image, label, status, created_at, published_at
         FROM projects ORDER BY created_at DESC"
    );
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    $projects = [];
    $dbError  = 'Failed to load projects.';
}

require_once ROOT_PATH . '/admin/partials/head.php';
?>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
  <div class="modal-box">
    <div class="modal-icon" aria-hidden="true">
      <svg viewBox="0 0 16 16"><path d="M11 1.75V3h2.25a.75.75 0 010 1.5H2.75a.75.75 0 010-1.5H5V1.75C5 .784 5.784 0 6.75 0h2.5C10.216 0 11 .784 11 1.75zm-4.5 0V3h3V1.75a.25.25 0 00-.25-.25h-2.5a.25.25 0 00-.25.25zM4.997 6.75a.75.75 0 00-.497.732l.5 6.5A.75.75 0 005.75 14.5h4.5a.75.75 0 00.75-.518l.5-6.5a.75.75 0 00-1.497-.116l-.44 5.714H7.443l-.44-5.714a.75.75 0 00-.756-.116z" fill-rule="evenodd"/></svg>
    </div>
    <h2 class="modal-title" id="delete-modal-title">Delete Project?</h2>
    <p class="modal-message">Are you sure you want to delete "<strong id="delete-modal-name"></strong>"?</p>
    <p class="modal-warning">This action cannot be undone. The cover image will also be removed from Cloudinary.</p>
    <div class="modal-actions">
      <button id="delete-cancel-btn" class="btn btn-secondary">Cancel</button>
      <button id="delete-confirm-btn" class="btn btn-danger" style="background:var(--danger);color:#fff;border-color:var(--danger);">
        Delete Project
      </button>
    </div>
  </div>
</div>

<!-- Global JS Config -->
<script>
  const FAY_CONFIG = {
    apiBase:   '<?= APP_URL ?>/../api',
    adminBase: '<?= APP_URL ?>/../admin',
    csrfToken: '<?= htmlspecialchars($csrfToken) ?>',
  };
</script>

<?php $activePage = 'projects'; require_once ROOT_PATH . '/admin/partials/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-wrapper">
  <!-- Topbar -->
  <header class="topbar">
    <button id="sidebar-toggle" class="topbar-hamburger" aria-label="Toggle sidebar">
      <svg viewBox="0 0 16 16"><path d="M1 2.75A.75.75 0 011.75 2h12.5a.75.75 0 010 1.5H1.75A.75.75 0 011 2.75zm0 5A.75.75 0 011.75 7h12.5a.75.75 0 010 1.5H1.75A.75.75 0 011 7.75zM1.75 12a.75.75 0 000 1.5h12.5a.75.75 0 000-1.5H1.75z"/></svg>
    </button>
    <span class="topbar-title">Projects</span>
    <div class="topbar-actions">
      <a href="<?= APP_URL ?>/../admin/projects/create.php" class="btn btn-primary">
        <svg viewBox="0 0 16 16"><path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/></svg>
        New Project
      </a>
    </div>
  </header>

  <!-- Page Content -->
  <main class="page-content">
    <div class="page-header">
      <h2>Projects</h2>
      <p>Manage your portfolio projects.</p>
    </div>

    <?php if (isset($dbError)): ?>
    <div class="alert alert-danger">
      <svg viewBox="0 0 16 16" width="16" height="16" fill="currentColor">
        <path d="M8 1a7 7 0 100 14A7 7 0 008 1zm1 10.5a1 1 0 11-2 0 1 1 0 012 0zm-.25-4.75a.75.75 0 00-1.5 0v3a.75.75 0 001.5 0v-3z"/>
      </svg>
      <?= e($dbError) ?>
    </div>
    <?php endif; ?>

    <?php if (empty($projects)): ?>
    <!-- Empty State -->
    <div class="empty-state">
      <div class="empty-state-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24">
          <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
        </svg>
      </div>
      <h3>No projects yet.</h3>
      <p>Start by creating your first portfolio project.</p>
      <a href="<?= APP_URL ?>/../admin/projects/create.php" class="btn btn-primary" style="margin-top:16px;">
        <svg viewBox="0 0 16 16"><path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/></svg>
        Create First Project
      </a>
    </div>

    <?php else: ?>
    <!-- Projects Grid -->
    <div id="projects-grid" class="projects-grid">
      <?php foreach ($projects as $p): ?>
      <article id="project-card-<?= $p['id'] ?>" class="project-card" style="display:flex;flex-direction:column;">
        <!-- Cover Image -->
        <?php if ($p['cover_image']): ?>
          <img src="<?= e($p['cover_image']) ?>"
               alt="<?= e($p['title']) ?>"
               class="project-card-img"
               loading="lazy">
        <?php else: ?>
          <div class="project-card-img-placeholder" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
          </div>
        <?php endif; ?>

        <div class="project-card-body">
          <!-- Meta -->
          <div class="project-card-meta">
            <span class="label-badge <?= getLabelClass($p['label']) ?>">
              <?= e($p['label']) ?>
            </span>
            <span class="status-badge <?= getStatusClass($p['status']) ?>">
              <?= ucfirst(e($p['status'])) ?>
            </span>
          </div>

          <!-- Title & Description -->
          <div class="project-card-title"><?= e($p['title']) ?></div>
          <div class="project-card-desc"><?= e($p['description']) ?></div>

          <!-- Actions -->
          <div class="project-card-actions">
            <a href="<?= APP_URL ?>/projects/<?= e($p['slug']) ?>"
               target="_blank"
               class="btn btn-secondary btn-sm"
               title="View public page"
               <?= $p['status'] !== 'published' ? 'style="opacity:.5;pointer-events:none" tabindex="-1"' : '' ?>>
              <svg viewBox="0 0 16 16"><path d="M8 0a8 8 0 100 16A8 8 0 008 0zM1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0z"/></svg>
              View
            </a>

            <a href="<?= APP_URL ?>/../admin/projects/edit.php?id=<?= $p['id'] ?>"
               class="btn btn-primary btn-sm">
              <svg viewBox="0 0 16 16"><path d="M11.013 1.427a1.75 1.75 0 012.474 0l1.086 1.086a1.75 1.75 0 010 2.474l-8.61 8.61c-.21.21-.47.364-.756.445l-3.251.93a.75.75 0 01-.927-.928l.929-3.25c.081-.286.235-.547.445-.758l8.61-8.61zm.176 4.823L9.75 4.81l-6.286 6.287a.253.253 0 00-.064.108l-.558 1.953 1.953-.558a.253.253 0 00.108-.064l6.286-6.286zm1.238-3.763a.25.25 0 00-.354 0L10.811 3.75l1.439 1.44 1.263-1.263a.25.25 0 000-.354l-1.086-1.086z"/></svg>
              Edit
            </a>

            <button class="btn btn-danger btn-sm btn-delete-project"
                    data-id="<?= $p['id'] ?>"
                    data-title="<?= e($p['title']) ?>"
                    aria-label="Delete <?= e($p['title']) ?>">
              <svg viewBox="0 0 16 16"><path d="M11 1.75V3h2.25a.75.75 0 010 1.5H2.75a.75.75 0 010-1.5H5V1.75C5 .784 5.784 0 6.75 0h2.5C10.216 0 11 .784 11 1.75zM4.496 6.675a.75.75 0 10-1.492.15l.66 6.6A1.75 1.75 0 005.405 15h5.19c.9 0 1.652-.681 1.741-1.576l.66-6.6a.75.75 0 00-1.492-.149l-.66 6.6a.25.25 0 01-.249.225h-5.19a.25.25 0 01-.249-.225l-.66-6.6z"/></svg>
              Delete
            </button>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </main>
</div>

<!-- Toast container -->
<div id="toast-container" class="toast-container" aria-live="polite"></div>

<?php require_once ROOT_PATH . '/admin/partials/footer.php'; ?>
