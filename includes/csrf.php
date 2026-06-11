<?php
// ============================================================
// CSRF Token Helper
// ============================================================

function csrfStart(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function csrfGenerate(): string
{
    csrfStart();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    $token = csrfGenerate();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrfToken(): string
{
    return csrfGenerate();
}

function csrfVerify(?string $token): bool
{
    csrfStart();
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
