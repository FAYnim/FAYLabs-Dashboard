<?php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/response.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();
if (!isLoggedIn()) unauthorized();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') methodNotAllowed();

$input = getInput();

if (!csrfVerify($input['csrf_token'] ?? '')) {
    jsonError('Invalid CSRF token.', [], 403);
}

$id = (int) ($input['id'] ?? 0);

if ($id < 1) {
    jsonError('Invalid email ID.', ['id' => 'Email ID is required.']);
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('DELETE FROM emails WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() < 1) {
        jsonError('Email not found.', [], 404);
    }

    jsonSuccess('Email deleted successfully.');
} catch (Exception $e) {
    jsonError('Failed to delete email. Please try again.', [], 500);
}
