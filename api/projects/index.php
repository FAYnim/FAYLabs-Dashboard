<?php
// ============================================================
// GET /api/projects/index.php — List all projects (admin)
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/response.php';

csrfStart();
if (!isLoggedIn()) unauthorized();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') methodNotAllowed();

try {
    $pdo    = Database::getConnection();
    $status = $_GET['status'] ?? null;

    $allowed = ['draft', 'published'];
    if ($status !== null && !in_array($status, $allowed, true)) {
        jsonError('Invalid status filter.');
    }

    if ($status !== null) {
        $stmt = $pdo->prepare(
            "SELECT id, title, slug, description, cover_image, label, status, created_at, updated_at, published_at
             FROM projects WHERE status = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query(
            "SELECT id, title, slug, description, cover_image, label, status, created_at, updated_at, published_at
             FROM projects ORDER BY created_at DESC"
        );
    }

    $projects = $stmt->fetchAll();
    jsonSuccess('', $projects);

} catch (Exception $e) {
    jsonError('Failed to fetch projects.', [], 500);
}
