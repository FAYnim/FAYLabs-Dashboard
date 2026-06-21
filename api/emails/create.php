<?php
// ============================================================
// POST /api/emails/create.php — Save outgoing email
// ============================================================

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

$data = [
    'recipient_email' => trim($input['to'] ?? ''),
    'subject'         => trim($input['subject'] ?? ''),
    'body'            => trim($input['message'] ?? ''),
];

$errors = [];
if ($data['recipient_email'] === '') {
    $errors['to'] = 'Recipient email is required.';
} elseif (!filter_var($data['recipient_email'], FILTER_VALIDATE_EMAIL)) {
    $errors['to'] = 'Recipient email is invalid.';
}
if ($data['subject'] === '') {
    $errors['subject'] = 'Subject is required.';
}
if ($data['body'] === '') {
    $errors['message'] = 'Message is required.';
}

if (!empty($errors)) {
    jsonError('Validation failed.', $errors);
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("INSERT INTO emails
        (sender_name, sender_email, recipient_email, subject, body, direction, is_read, sent_at)
        VALUES (?, ?, ?, ?, ?, 'outgoing', 1, NOW())
    ");
    $stmt->execute([
        'FAYLabs',
        $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@faylabs.local',
        $data['recipient_email'],
        $data['subject'],
        $data['body'],
    ]);

    jsonSuccess('Email saved successfully.', ['id' => (int) $pdo->lastInsertId()], 201);
} catch (Exception $e) {
    jsonError('Failed to save email. Please try again.', [], 500);
}
