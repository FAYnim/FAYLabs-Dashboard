<?php
// ============================================================
// POST /api/auth/login.php
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/response.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    methodNotAllowed();
}

csrfStart();

$input    = getInput();
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');
$token    = $input['csrf_token'] ?? '';

if (!csrfVerify($token)) {
    jsonError('Invalid CSRF token.', [], 403);
}

if (empty($username) || empty($password)) {
    jsonError('Invalid username or password.');
}

try {
    $pdo  = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id, password_hash FROM admins WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        jsonError('Invalid username or password.');
    }

    loginAdmin((int) $admin['id']);
    jsonSuccess('Login successful.');

} catch (Exception $e) {
    jsonError('Server error. Please try again.', [], 500);
}
