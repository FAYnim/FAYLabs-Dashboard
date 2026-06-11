<?php
// ============================================================
// POST /api/uploads/cover.php — Upload cover image to Cloudinary
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/cloudinary.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/response.php';
require_once ROOT_PATH . '/includes/csrf.php';

csrfStart();
if (!isLoggedIn()) unauthorized();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') methodNotAllowed();

// Verify CSRF
if (!csrfVerify($_POST['csrf_token'] ?? '')) {
    jsonError('Invalid CSRF token.', [], 403);
}

if (empty($_FILES['cover'])) {
    jsonError('No file uploaded.');
}

$file = $_FILES['cover'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonError('Upload error. Please try again.');
}

// Validate size (max 2MB)
if ($file['size'] > 2 * 1024 * 1024) {
    jsonError('Image size must not exceed 2 MB.');
}

// Validate MIME type
$allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
$finfo        = finfo_open(FILEINFO_MIME_TYPE);
$mimeType     = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimes, true)) {
    jsonError('Invalid image format. Allowed: JPG, PNG, WEBP.');
}

// Validate extension
$ext          = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];
if (!in_array($ext, $allowedExts, true)) {
    jsonError('Invalid image format. Allowed: JPG, PNG, WEBP.');
}

try {
    $result = Cloudinary::upload($file['tmp_name']);
    jsonSuccess('Cover image uploaded successfully.', [
        'secure_url' => $result['secure_url'],
        'public_id'  => $result['public_id'],
    ]);
} catch (Exception $e) {
    jsonError('Failed to upload cover image: ' . $e->getMessage());
}
