<?php
// ============================================================
// Public Project Detail Page
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/helpers.php';

$slug = trim($_GET['slug'] ?? '');

if (empty($slug)) {
    http_response_code(404);
    $notFound = true;
} else {
    try {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM projects
            WHERE slug = ? AND status = 'published'
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $project = $stmt->fetch();

        if (!$project) {
            http_response_code(404);
            $notFound = true;
        } else {
            // Increment views
            $viewStmt = $pdo->prepare("UPDATE projects SET views = views + 1 WHERE id = ?");
            $viewStmt->execute([$project['id']]);

            $techStack = parseTechStack($project['tech_stack']);

            // SEO
            $seoTitle = $project['seo_title']
                ? $project['seo_title']
                : $project['title'] . ' | ' . APP_NAME;

            $seoDesc = $project['seo_description'] ?: $project['description'];

            $pageTitle    = $seoTitle;
            $metaDesc     = $seoDesc;
            $ogTitle      = $seoTitle;
            $ogImage      = $project['cover_image'];
            $ogType       = 'article';
            $canonicalUrl = APP_URL . '/projects/' . $project['slug'];
            $activeNav    = 'projects';
            $loadMarkdown = true;
        }
    } catch (Exception $e) {
        http_response_code(500);
        $notFound = true;
    }
}

if (!empty($notFound)) {
    $pageTitle = 'Project Not Found · ' . APP_NAME;
    $metaDesc  = '';
    $activeNav = 'projects';
    require_once ROOT_PATH . '/public/partials/head.php';
    ?>
    <main>
      <div class="container" style="padding-top:80px;padding-bottom:80px;text-align:center;">
        <div style="font-size:64px;margin-bottom:16px;">404</div>
        <h1 style="font-size:24px;font-weight:600;margin-bottom:8px;">Project Not Found</h1>
        <p style="color:var(--text-secondary);margin-bottom:24px;">
          The project you're looking for doesn't exist or has been removed.
        </p>
        <a href="<?= APP_URL ?>/projects" class="btn-load-more">← Back to Projects</a>
      </div>
    </main>
    <?php
    require_once ROOT_PATH . '/public/partials/footer.php';
    exit;
}

require_once ROOT_PATH . '/public/partials/head.php';
?>

<main>
  <div class="container">
    <article class="project-detail">

      <!-- Back Link -->
      <a href="<?= APP_URL ?>/projects" class="back-link">
        <svg viewBox="0 0 16 16"><path d="M7.78 12.53a.75.75 0 01-1.06 0L2.47 8.28a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 1.06L4.81 7h7.44a.75.75 0 010 1.5H4.81l2.97 2.97a.75.75 0 010 1.06z"/></svg>
        All Projects
      </a>

      <!-- Cover Image -->
      <?php if ($project['cover_image']): ?>
      <div class="project-detail-cover">
        <img src="<?= e($project['cover_image']) ?>"
             alt="<?= e($project['title']) ?> cover"
             width="1200" height="675">
      </div>
      <?php endif; ?>

      <!-- Meta -->
      <div class="project-detail-meta">
        <span class="label-badge <?= getLabelClass($project['label']) ?>">
          <?= e($project['label']) ?>
        </span>
      </div>

      <!-- Title -->
      <h1 class="project-detail-title"><?= e($project['title']) ?></h1>

      <!-- Description -->
      <p class="project-detail-desc"><?= e($project['description']) ?></p>

      <!-- Info Row -->
      <div class="project-detail-info">
        <!-- Year -->
        <div class="info-item">
          <span class="info-label">Year</span>
          <span class="info-value"><?= (int)$project['project_year'] ?></span>
        </div>

        <!-- Tech Stack -->
        <?php if (!empty($techStack)): ?>
        <div class="info-item" style="flex:1;min-width:200px;">
          <span class="info-label">Tech Stack</span>
          <div class="tech-tags" style="margin-top:6px;">
            <?php foreach ($techStack as $tech): ?>
            <span class="tech-tag"><?= e($tech) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- GitHub -->
        <?php if ($project['github_url']): ?>
        <div class="info-item">
          <span class="info-label">Source</span>
          <a href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener noreferrer"
             class="info-link">
            <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor">
              <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
            </svg>
            GitHub
          </a>
        </div>
        <?php endif; ?>

        <!-- Demo -->
        <?php if ($project['demo_url']): ?>
        <div class="info-item">
          <span class="info-label">Live Demo</span>
          <a href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener noreferrer"
             class="info-link">
            <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor">
              <path d="M3.75 2h3.5a.75.75 0 010 1.5h-3.5a.25.25 0 00-.25.25v8.5c0 .138.112.25.25.25h8.5a.25.25 0 00.25-.25v-3.5a.75.75 0 011.5 0v3.5A1.75 1.75 0 0112.25 14h-8.5A1.75 1.75 0 012 12.25v-8.5C2 2.784 2.784 2 3.75 2zm6.854-1h4.146a.25.25 0 01.25.25v4.146a.25.25 0 01-.427.177L13.03 4.03 9.28 7.78a.75.75 0 01-1.06-1.06l3.75-3.75-1.543-1.543A.25.25 0 0110.604 1z"/>
            </svg>
            View Live
          </a>
        </div>
        <?php endif; ?>

        <!-- Published -->
        <?php if ($project['published_at']): ?>
        <div class="info-item">
          <span class="info-label">Published</span>
          <span class="info-value"><?= formatDate($project['published_at']) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Markdown Content -->
      <div class="project-detail-content">
        <!-- Hidden raw content for JS renderer -->
        <script id="raw-content" type="text/plain"><?= htmlspecialchars($project['content'], ENT_QUOTES, 'UTF-8') ?></script>

        <!-- Rendered output -->
        <div id="rendered-content" class="markdown-body" aria-label="Project documentation">
          <p style="color:var(--text-muted);font-style:italic">Loading content...</p>
        </div>
      </div>

    </article>
  </div>
</main>

<?php require_once ROOT_PATH . '/public/partials/footer.php'; ?>
