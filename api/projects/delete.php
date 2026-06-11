<?php
// ============================================================
// POST /api/projects/delete.php?id={id} — Delete project
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/cloudinary.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/response.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();
if (!isLoggedIn()) unauthorized();

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'], true)) methodNotAllowed();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) jsonError('Invalid project ID.');

$input = getInput();
if (!csrfVerify($input['csrf_token'] ?? '')) {
    jsonError('Invalid CSRF token.', [], 403);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT id, cover_public_id FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();

    if (!$project) notFound('Project not found.');

    // Delete from Cloudinary if public_id exists
    if (!empty($project['cover_public_id'])) {
        try {
            Cloudinary::delete($project['cover_public_id']);
        } catch (Exception) {
            // Continue even if Cloudinary delete fails
        }
    }

    $del = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $del->execute([$id]);

    jsonSuccess('Project deleted successfully.');

} catch (Exception $e) {
    jsonError('Failed to delete project.', [], 500);
}
