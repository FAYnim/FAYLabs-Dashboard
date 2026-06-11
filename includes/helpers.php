<?php
// ============================================================
// Helper Functions
// ============================================================

/**
 * Generate a URL-friendly slug from a string.
 */
function generateSlug(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s_]+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Sanitize output for HTML display.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Truncate a string to a given length.
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Format a datetime string for display.
 */
function formatDate(string $datetime, string $format = 'd M Y'): string
{
    try {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    } catch (Exception) {
        return $datetime;
    }
}

/**
 * Format datetime for "Last updated: X"
 */
function formatDatetime(string $datetime): string
{
    return formatDate($datetime, 'd M Y, H:i');
}

/**
 * Get the label badge class for a project label.
 */
function getLabelClass(string $label): string
{
    return match ($label) {
        'AI'     => 'label-ai',
        'Web App'=> 'label-webapp',
        'SaaS'   => 'label-saas',
        'IoT'    => 'label-iot',
        'Mobile' => 'label-mobile',
        default  => 'label-other',
    };
}

/**
 * Get the status badge class.
 */
function getStatusClass(string $status): string
{
    return match ($status) {
        'published' => 'status-published',
        'draft'     => 'status-draft',
        default     => 'status-draft',
    };
}

/**
 * Parse JSON tech stack safely.
 */
function parseTechStack(mixed $json): array
{
    if (is_array($json)) return $json;
    if (!is_string($json)) return [];
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Get request body as JSON.
 */
function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Get the current page's full URL.
 */
function currentUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Merge POST and JSON body.
 */
function getInput(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($contentType, 'application/json')) {
        return getJsonBody();
    }
    return array_merge($_POST, getJsonBody());
}
