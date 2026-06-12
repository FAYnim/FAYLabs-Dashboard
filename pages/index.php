<?php
// ============================================================
// Dashboard — Projects List
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
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

require_once ROOT_PATH . '/partials/head.php';
?>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
  <div class="modal-box">
    <div class="modal-icon" aria-hidden="true">
      <i class="bi bi-trash3-fill" aria-hidden="true"></i>
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
    apiBase:   '<?= BASE_PATH ?>/api',
    adminBase: '<?= BASE_PATH ?>',
    csrfToken: '<?= htmlspecialchars($csrfToken) ?>',
  };
</script>

<?php $activePage = 'projects'; require_once ROOT_PATH . '/partials/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-wrapper">
  <!-- Topbar -->
  <header class="topbar">
    <button id="sidebar-toggle" class="topbar-hamburger" aria-label="Toggle sidebar">
      <i class="bi bi-list" aria-hidden="true"></i>
    </button>
    <span class="topbar-title">Projects</span>
    <div class="topbar-actions">
      <a href="<?= BASE_PATH ?>/create" class="btn btn-primary">
        <i class="bi bi-plus-circle" aria-hidden="true"></i>
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
      <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
      <?= e($dbError) ?>
    </div>
    <?php endif; ?>

    <?php if (empty($projects)): ?>
    <!-- Empty State -->
    <div class="empty-state">
      <div class="empty-state-icon" aria-hidden="true">
        <i class="bi bi-collection" aria-hidden="true"></i>
      </div>
      <h3>No projects yet.</h3>
      <p>Start by creating your first portfolio project.</p>
      <a href="<?= BASE_PATH ?>/create" class="btn btn-primary" style="margin-top:16px;">
        <i class="bi bi-plus-circle" aria-hidden="true"></i>
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
            <i class="bi bi-image" aria-hidden="true"></i>
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

            <a href="<?= BASE_PATH ?>/edit?id=<?= $p['id'] ?>"
               class="btn btn-primary btn-sm">
              <i class="bi bi-pencil-square" aria-hidden="true"></i>
              Edit
            </a>

            <button class="btn btn-danger btn-sm btn-delete-project"
                    data-id="<?= $p['id'] ?>"
                    data-title="<?= e($p['title']) ?>"
                    aria-label="Delete <?= e($p['title']) ?>">
              <i class="bi bi-trash-fill" aria-hidden="true"></i>
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

<?php require_once ROOT_PATH . '/partials/footer.php'; ?>
