<?php
// ============================================================
// Public Homepage
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

// SEO
$pageTitle    = APP_NAME . ' · Portfolio';
$metaDesc     = 'Explore my latest projects — web apps, SaaS, AI tools, and more.';
$ogTitle      = $pageTitle;
$canonicalUrl = APP_URL . '/';
$activeNav    = 'home';

// Fetch latest 6 published projects
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
} catch (Exception $e) {
    $projects = [];
}

require_once ROOT_PATH . '/public/partials/head.php';
?>

<main>
  <!-- Hero -->
  <section class="hero-section">
    <div class="container">
      <div class="hero-label" aria-hidden="true">Available for work</div>
      <h1 class="hero-title">
        Building products<br>
        people <span>actually use.</span>
      </h1>
      <p class="hero-desc">
        I'm a developer who loves crafting clean, functional web applications.
        Here's a collection of projects I've built — from SaaS tools to AI-powered apps.
      </p>
    </div>
  </section>

  <!-- Latest Projects -->
  <section class="section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Latest Projects</h2>
        <?php if (count($projects) >= 6): ?>
        <a href="<?= APP_URL ?>/projects" class="section-link">
          View all →
        </a>
        <?php endif; ?>
      </div>

      <?php if (empty($projects)): ?>
      <div class="empty-state">
        <div class="empty-state-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        </div>
        <h3>No projects yet.</h3>
        <p>Check back soon!</p>
      </div>
      <?php else: ?>
      <div class="projects-grid">
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
              <span style="color:var(--text-muted);font-size:12px">
                <?= (int)$p['project_year'] ?>
              </span>
            </div>
            <div class="project-card-title"><?= e($p['title']) ?></div>
            <div class="project-card-desc"><?= e($p['description']) ?></div>
            <div class="project-card-footer">
              <span class="project-card-link">
                View Project
                <svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor">
                  <path d="M6.22 3.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06L9.94 8 6.22 4.28a.75.75 0 010-1.06z"/>
                </svg>
              </span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <?php if (count($projects) >= 6): ?>
      <div style="text-align:center;margin-top:36px;">
        <a href="<?= APP_URL ?>/projects" class="btn-load-more">
          View All Projects
          <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor">
            <path d="M6.22 3.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06L9.94 8 6.22 4.28a.75.75 0 010-1.06z"/>
          </svg>
        </a>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require_once ROOT_PATH . '/public/partials/footer.php'; ?>
