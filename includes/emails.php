<?php

require_once dirname(__DIR__) . '/config/database.php';

const CONTACT_EMAIL_RECIPIENT = 'admin@faylabs.my.id';

function contactReasonLabel(string $reason): string
{
    $labels = [
        'collaboration'       => 'Collaboration',
        'project-inquiry'     => 'Project Inquiry',
        'hiring'              => 'Hiring',
        'general-networking'  => 'General Networking',
    ];

    return $labels[$reason] ?? 'General Networking';
}

function buildIncomingEmailSubject(string $reason): string
{
    return 'Contact Form: ' . contactReasonLabel($reason);
}

function buildIncomingEmailBody(string $reason, string $message): string
{
    return "Reason: " . contactReasonLabel($reason) . "\n\nMessage:\n" . trim($message);
}

function saveIncomingEmail(string $senderName, string $senderEmail, string $reason, string $message): ?int
{
    $senderName  = trim($senderName);
    $senderEmail = trim($senderEmail);
    $reason      = trim($reason);
    $message     = trim($message);

    if ($senderEmail === '' || !filter_var($senderEmail, FILTER_VALIDATE_EMAIL) || $message === '') {
        return null;
    }

    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO emails
                (sender_name, sender_email, recipient_email, subject, body, direction, is_read, sent_at)
             VALUES
                (?, ?, ?, ?, ?, 'incoming', 0, NOW())"
        );

        $stmt->execute([
            $senderName !== '' ? $senderName : null,
            $senderEmail,
            $_ENV['MAIL_FROM_ADDRESS'] ?? CONTACT_EMAIL_RECIPIENT,
            buildIncomingEmailSubject($reason),
            buildIncomingEmailBody($reason, $message),
        ]);

        return (int) $pdo->lastInsertId();
    } catch (Throwable $e) {
        error_log('Failed to save incoming email: ' . $e->getMessage());
        return null;
    }
}
