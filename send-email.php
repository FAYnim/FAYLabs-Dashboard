<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/config/app.php";
require __DIR__ . "/includes/emails.php";

loadEnv(__DIR__ . "/.env");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php#contact");
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$reason = trim($_POST["reason"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $reason === "" || $message === "") {
    header("Location: index.php#contact?status=invalid");
    exit;
}

saveIncomingEmail($name, $email, $reason, $message);

$mail = new PHPMailer(true);
$receiver = $email;

try {
    $mail->isSMTP();
    $mail->Host = "mail.faylabs.my.id";
    $mail->SMTPAuth = true;
    $mail->Username = "admin@faylabs.my.id";
    $mail->Password = $_ENV["EMAIL_PASSWORD"] ?? getenv("EMAIL_PASSWORD");
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom("admin@faylabs.my.id", "Faylabs Admin");
    $mail->addAddress($receiver);

    $safeName = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
    $safeReason = htmlspecialchars($reason, ENT_QUOTES, "UTF-8");
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, "UTF-8"));

    $replies = [
        "collaboration" => [
            "subject" => "Collaboration Message Received",
            "intro" => "Terima kasih sudah menghubungi Faylabs untuk peluang kolaborasi. Pesan Anda telah diterima dan akan ditinjau untuk melihat ruang kerja sama yang paling sesuai.",
            "closing" => "Jika cocok, kami akan membalas melalui email ini untuk membahas langkah berikutnya.",
        ],
        "project-inquiry" => [
            "subject" => "Project Inquiry Received",
            "intro" => "Terima kasih sudah mengirim inquiry project ke Faylabs. Pesan Anda telah diterima dan akan ditinjau berdasarkan kebutuhan, scope, dan potensi solusi yang bisa dibangun.",
            "closing" => "Kami akan menghubungi Anda kembali melalui email ini jika detail tambahan diperlukan.",
        ],
        "hiring" => [
            "subject" => "Hiring Message Received",
            "intro" => "Terima kasih sudah menghubungi Faylabs terkait peluang hiring. Pesan Anda telah diterima dan akan ditinjau dengan serius.",
            "closing" => "Jika peluangnya relevan, kami akan membalas melalui email ini untuk diskusi lanjutan.",
        ],
        "general-networking" => [
            "subject" => "Message Received",
            "intro" => "Terima kasih sudah menghubungi Faylabs. Pesan networking Anda telah diterima dan akan dibaca dengan baik.",
            "closing" => "Kami akan membalas melalui email ini jika ada hal yang bisa dilanjutkan.",
        ],
    ];
    $reply = $replies[$reason] ?? $replies["general-networking"];
    $safeIntro = htmlspecialchars($reply["intro"], ENT_QUOTES, "UTF-8");
    $safeClosing = htmlspecialchars($reply["closing"], ENT_QUOTES, "UTF-8");

    $mail->isHTML(true);
    $mail->Subject = $reply["subject"];
    $mail->Body = "<h3>Halo {$safeName},</h3><p>{$safeIntro}</p><p><strong>Ringkasan pesan:</strong></p><p><strong>Reason:</strong> {$safeReason}</p><p><strong>Message:</strong><br>{$safeMessage}</p><p>{$safeClosing}</p>";
    $mail->AltBody = "Halo {$name},\n\n{$reply["intro"]}\n\nRingkasan pesan:\nReason: {$reason}\nMessage:\n{$message}\n\n{$reply["closing"]}";

    $mail->send();
    header("Location: index.php#contact?status=sent");
    exit;
} catch (Exception $e) {
    file_put_contents(__DIR__ . "/send-email.log", "Email gagal dikirim: " . $mail->ErrorInfo . PHP_EOL, FILE_APPEND);
    header("Location: index.php#contact?status=failed");
    exit;
}
