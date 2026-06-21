<?php
// ============================================================
// POST /api/emails/send.php — Send outgoing email
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/config/app.php';
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
if ($id <= 0) {
    jsonError('Email ID is required.');
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        "SELECT id, recipient_email, subject, body, direction
         FROM emails
         WHERE id = ?
         LIMIT 1"
    );
    $stmt->execute([$id]);
    $email = $stmt->fetch();

    if (!$email) {
        notFound('Email not found.');
    }

    if ($email['direction'] !== 'outgoing') {
        jsonError('Only outgoing emails can be sent.', [], 422);
    }

    if (!filter_var($email['recipient_email'], FILTER_VALIDATE_EMAIL)) {
        jsonError('Recipient email is invalid.', [], 422);
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'mail.faylabs.my.id';
    $mail->SMTPAuth = true;
    $mail->Username = 'admin@faylabs.my.id';
    $mail->Password = $_ENV['EMAIL_PASSWORD'] ?? getenv('EMAIL_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('admin@faylabs.my.id', 'Faylabs Admin');
    $mail->addAddress($email['recipient_email']);
    $mail->isHTML(true);
    $mail->Subject = $email['subject'];
    $mail->Body = nl2br(htmlspecialchars($email['body'], ENT_QUOTES, 'UTF-8'));
    $mail->AltBody = $email['body'];
    $mail->send();

    $updateStmt = $pdo->prepare('UPDATE emails SET sent_at = NOW() WHERE id = ?');
    $updateStmt->execute([$id]);

    jsonSuccess('Email berhasil terkirim.');
} catch (MailException $e) {
    file_put_contents(ROOT_PATH . '/send-email.log', 'Email gagal dikirim: ' . $mail->ErrorInfo . PHP_EOL, FILE_APPEND);
    jsonError('Failed to send email. Please try again.', [], 500);
} catch (Throwable $e) {
    jsonError('Failed to send email. Please try again.', [], 500);
}
