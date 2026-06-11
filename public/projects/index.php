<?php
// ============================================================
// Public Projects Page
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

$pageTitle    = 'Projects · ' . APP_NAME;
$metaDesc     = 'Browse all portfolio projects — web apps, SaaS, AI tools, and more.';
$ogTitle      = $pageTitle;
$canonicalUrl = APP_URL . '/projects';
$activeNav    = 'projects';
$loadProjectsJs = true;

// Fetch first 6 published projects
try {
    $pdo  = Database::getConnection();

    $stmt = $pdo->query("
        SELECT id, title, slug, description, cover_image, label, project_year, published_at
        FROM projects
        WHERE status = 'published'
        ORDER BY published_at DESC
        LIMIT 6
    ");
    $projects = $stmt->fetchAll();

    // Total count for load more
    $countStmt = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'published'");
    $total = (int) $countStmt->fetchColumn();

} catch (Exception $e) {
    $projects = [];
    $total    = 0;
}

require_once ROOT_PATH . '/public/partials/head.php';
?>

<main>
  <div class="container">
    <!-- Page Hero -->
    <div class="page-hero">
      <h1>Projects</h1>
      <p>A collection of things I've built — open source, client work, and personal experiments.</p>
    </div>

    <?php if (empty($projects)): ?>
    <div class="empty-state">
      <div class="empty-state-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
      </div>
      <h3>No published projects yet.</h3>
      <p>Check back soon!</p>
    </div>
    <?php else: ?>

    <!-- Projects Grid -->
    <div id="public-projects-grid" class="projects-grid">
      <?php foreach ($projects as $p): ?>
      <a href="<?= APP_URL ?>/projects/<?= e($p['slug']) ?>" class="project-card">
        <?php if ($p['cover_image']): ?>
        <img src="<?= e($p['cover_image']) ?>" alt="<?= e($p['title']) ?>"
             class="project-card-img" loading="lazy">
        <?php else: ?>
        <div class="project-card-img-placeholder" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
        </div>
        <?php endif; ?>

        <div class="project-card-body">
          <div class="project-card-meta">
            <span class="label-badge <?= getLabelClass($p['label']) ?>">
              <?= e($p['label']) ?>
            </span>
            <span style="color:var(--text-muted);font-size:12px"><?= (int)$p['project_year'] ?></span>
          </div>
          <div class="project-card-title"><?= e($p['title']) ?></div>
          <div class="project-card-desc"><?= e($p['description']) ?></div>
          <div class="project-card-footer">
            <span class="project-card-link">
              View Project →
            </span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Load More -->
    <?php if ($total > 6): ?>
    <div class="load-more-wrapper">
      <button id="btn-load-more" class="btn-load-more"
              data-total="<?= $total ?>">
        Load More
        <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor">
          <path d="M8 1a.75.75 0 01.75.75v5.5h5.5a.75.75 0 010 1.5h-5.5v5.5a.75.75 0 01-1.5 0v-5.5H1.75a.75.75 0 010-1.5h5.5V1.75A.75.75 0 018 1z"/>
        </svg>
      </button>
    </div>
    <?php endif; ?>

    <?php endif; ?>
  </div>
</main>

<?php require_once ROOT_PATH . '/public/partials/footer.php'; ?>
