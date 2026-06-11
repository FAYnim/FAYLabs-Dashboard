<?php
// ============================================================
// GET /api/projects/show.php?id={id} — Single project (admin)
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/response.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();
if (!isLoggedIn()) unauthorized();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') methodNotAllowed();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) jsonError('Invalid project ID.');

try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $project = $stmt->fetch();

    if (!$project) notFound('Project not found.');

    $project['tech_stack'] = parseTechStack($project['tech_stack']);
    jsonSuccess('', $project);

} catch (Exception $e) {
    jsonError('Failed to fetch project.', [], 500);
}
