<?php
// ============================================================
// Admin Seeder — Run this once via browser to create the admin
// URL: http://localhost/faydev/faylabs-dashboard/database/seed-admin.php
// ============================================================

// Security: Only run in development
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';

$username = 'admin';
$password = 'admin123';
$hash     = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo = Database::getConnection();

    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "<p style='color:orange'>⚠️ Admin <strong>{$username}</strong> already exists. Skipped.</p>";
    } else {
        $insert = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
        $insert->execute([$username, $hash]);
        echo "<p style='color:green'>✅ Admin <strong>{$username}</strong> created successfully.</p>";
        echo "<p><strong>Username:</strong> {$username}</p>";
        echo "<p><strong>Password:</strong> {$password}</p>";
        echo "<p style='color:red'><strong>⚠️ Please change your password after first login!</strong></p>";
    }

    echo "<p><a href='../admin/login.php'>→ Go to Login</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Make sure you've run the schema.sql first to create the database and tables.</p>";
}
