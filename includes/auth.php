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
        header('Location: ' . BASE_PATH . '/login');
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
