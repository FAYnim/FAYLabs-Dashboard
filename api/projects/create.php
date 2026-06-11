<?php
// ============================================================
// POST /api/projects/create.php — Create new project
// ============================================================

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/response.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/validator.php';
require_once ROOT_PATH . '/includes/helpers.php';

csrfStart();
if (!isLoggedIn()) unauthorized();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') methodNotAllowed();

$input = getInput();

// CSRF
if (!csrfVerify($input['csrf_token'] ?? '')) {
    jsonError('Invalid CSRF token.', [], 403);
}

// Validate
$data = [
    'title'           => trim($input['title']           ?? ''),
    'slug'            => trim($input['slug']            ?? ''),
    'description'     => trim($input['description']     ?? ''),
    'cover_image'     => trim($input['cover_image']     ?? ''),
    'cover_public_id' => trim($input['cover_public_id'] ?? ''),
    'label'           => trim($input['label']           ?? ''),
    'content'         => trim($input['content']         ?? ''),
    'tech_stack'      => $input['tech_stack']           ?? [],
    'github_url'      => trim($input['github_url']      ?? ''),
    'demo_url'        => trim($input['demo_url']        ?? ''),
    'project_year'    => (int) ($input['project_year']  ?? date('Y')),
    'status'          => trim($input['status']          ?? 'draft'),
    'seo_title'       => trim($input['seo_title']       ?? ''),
    'seo_description' => trim($input['seo_description'] ?? ''),
];

$v = new Validator($data);
$v->required('title', 'Title')
  ->minLength('title', 3, 'Title')
  ->maxLength('title', 150, 'Title')
  ->required('slug', 'Slug')
  ->minLength('slug', 3, 'Slug')
  ->maxLength('slug', 180, 'Slug')
  ->slug('slug', 'Slug')
  ->required('description', 'Description')
  ->minLength('description', 10, 'Description')
  ->maxLength('description', 300, 'Description')
  ->required('cover_image', 'Cover image')
  ->required('label', 'Label')
  ->inList('label', ['AI', 'Web App', 'SaaS', 'IoT', 'Mobile', 'Other'], 'Label')
  ->required('content', 'Content')
  ->minLength('content', 20, 'Content')
  ->inList('status', ['draft', 'published'], 'Status')
  ->between('project_year', 2020, (int) date('Y') + 1, 'Project year')
  ->url('github_url', 'GitHub URL')
  ->url('demo_url', 'Demo URL');

if (count($data['tech_stack']) < 1) {
    $v->errors()['tech_stack'] ?? null;
    jsonError('Tech stack must have at least 1 item.', ['tech_stack' => 'At least 1 tech stack item is required.']);
}

if ($v->fails()) {
    jsonError('Validation failed.', $v->errors());
}

try {
    $pdo = Database::getConnection();

    // Check slug uniqueness
    $slug_check = $pdo->prepare("SELECT id FROM projects WHERE slug = ?");
    $slug_check->execute([$data['slug']]);
    if ($slug_check->fetch()) {
        jsonError('Slug already exists.', ['slug' => 'This slug is already in use.']);
    }

    $techStackJson = json_encode($data['tech_stack']);
    $publishedAt   = $data['status'] === 'published' ? date('Y-m-d H:i:s') : null;

    $stmt = $pdo->prepare("
        INSERT INTO projects
            (title, slug, description, cover_image, cover_public_id, label, content, tech_stack,
             github_url, demo_url, project_year, status, seo_title, seo_description, published_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['title'],
        $data['slug'],
        $data['description'],
        $data['cover_image'],
        $data['cover_public_id'] ?: null,
        $data['label'],
        $data['content'],
        $techStackJson,
        $data['github_url'] ?: null,
        $data['demo_url']   ?: null,
        $data['project_year'],
        $data['status'],
        $data['seo_title']       ?: null,
        $data['seo_description'] ?: null,
        $publishedAt,
    ]);

    $newId = (int) $pdo->lastInsertId();

    $message = $data['status'] === 'published' ? 'Project published successfully.' : 'Draft saved successfully.';
    jsonSuccess($message, ['id' => $newId], 201);

} catch (Exception $e) {
    jsonError('Failed to save project. Please try again.', [], 500);
}
