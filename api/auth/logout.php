<?php
// ============================================================
// POST /api/auth/logout.php
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/response.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    methodNotAllowed();
}

$input = getInput();
$token = $input['csrf_token'] ?? '';

if (!csrfVerify($token)) {
    jsonError('Invalid CSRF token.', [], 403);
}

logoutAdmin();
jsonSuccess('Logged out successfully.');
