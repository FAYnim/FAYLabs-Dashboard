<?php
// ============================================================
// Auth Helper — Session Protection
// ============================================================

function requireAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . getAdminBaseUrl() . '/login.php');
        exit;
    }
}

function isLoggedIn(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['admin_id']);
}

function loginAdmin(int $adminId): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $adminId;
}

function logoutAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function getAdminBaseUrl(): string
{
    // Determine base URL for admin redirects
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script   = $_SERVER['SCRIPT_NAME'] ?? '';

    // Walk up until we find /admin
    $parts = explode('/', $script);
    $adminIndex = array_search('admin', $parts);

    if ($adminIndex !== false) {
        $baseParts = array_slice($parts, 0, $adminIndex + 1);
        return $protocol . '://' . $host . implode('/', $baseParts);
    }

    return $protocol . '://' . $host . '/admin';
}
