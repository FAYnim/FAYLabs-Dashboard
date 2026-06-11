<?php
// ============================================================
// GET /api/public/load-projects.php?offset={n} — Load more projects (AJAX)
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') methodNotAllowed();

$offset = max(0, (int) ($_GET['offset'] ?? 0));
$limit  = 6;

try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->prepare("
        SELECT id, title, slug, description, cover_image, label, project_year, published_at
        FROM projects
        WHERE status = 'published'
        ORDER BY published_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll();

    // Count total published projects for "Load More" visibility
    $countStmt = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'published'");
    $total = (int) $countStmt->fetchColumn();

    jsonSuccess('', [
        'projects' => $projects,
        'total'    => $total,
        'offset'   => $offset + $limit,
        'has_more' => ($offset + $limit) < $total,
    ]);
} catch (Exception $e) {
    jsonError('Failed to load projects.', [], 500);
}
