<?php
// ============================================================
// Admin Logout
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/includes/auth.php';

logoutAdmin();
header('Location: ' . APP_URL . '/../admin/login.php');
exit;
