<?php
// ============================================================
// GET /api/public/latest-projects.php — Latest 6 published projects
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/response.php';
require_once ROOT_PATH . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') methodNotAllowed();

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
    jsonSuccess('', $projects);
} catch (Exception $e) {
    jsonError('Failed to fetch projects.', [], 500);
}
