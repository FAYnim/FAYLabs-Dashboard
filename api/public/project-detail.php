<?php
// ============================================================
// GET /api/public/project-detail.php?slug={slug}
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/response.php';
require_once ROOT_PATH . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') methodNotAllowed();

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) jsonError('Slug is required.');

try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->prepare("
        SELECT id, title, slug, description, cover_image, label, content, tech_stack,
               github_url, demo_url, project_year, status, seo_title, seo_description,
               views, published_at
        FROM projects
        WHERE slug = ? AND status = 'published'
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $project = $stmt->fetch();

    if (!$project) notFound('Project not found.');

    // Increment view counter
    $viewStmt = $pdo->prepare("UPDATE projects SET views = views + 1 WHERE id = ?");
    $viewStmt->execute([$project['id']]);

    $project['tech_stack'] = parseTechStack($project['tech_stack']);
    jsonSuccess('', $project);

} catch (Exception $e) {
    jsonError('Failed to fetch project.', [], 500);
}
