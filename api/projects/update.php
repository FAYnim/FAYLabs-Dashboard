<?php
// ============================================================
// POST /api/projects/update.php?id={id} — Update project
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

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PATCH', 'PUT'], true)) methodNotAllowed();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) jsonError('Invalid project ID.');

$input = getInput();

if (!csrfVerify($input['csrf_token'] ?? '')) {
    jsonError('Invalid CSRF token.', [], 403);
}

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

if ($v->fails()) {
    jsonError('Validation failed.', $v->errors());
}

try {
    $pdo = Database::getConnection();

    // Check project exists
    $exists = $pdo->prepare("SELECT id, status, published_at FROM projects WHERE id = ?");
    $exists->execute([$id]);
    $current = $exists->fetch();
    if (!$current) notFound('Project not found.');

    // Check slug uniqueness (exclude current project)
    $slugCheck = $pdo->prepare("SELECT id FROM projects WHERE slug = ? AND id != ?");
    $slugCheck->execute([$data['slug'], $id]);
    if ($slugCheck->fetch()) {
        jsonError('Slug already exists.', ['slug' => 'This slug is already in use.']);
    }

    $techStackJson = json_encode($data['tech_stack']);

    // Handle published_at
    if ($data['status'] === 'published' && $current['status'] !== 'published') {
        $publishedAt = date('Y-m-d H:i:s');
    } elseif ($data['status'] === 'published') {
        $publishedAt = $current['published_at'];
    } else {
        $publishedAt = null;
    }

    $stmt = $pdo->prepare("
        UPDATE projects SET
            title = ?, slug = ?, description = ?, cover_image = ?, cover_public_id = ?,
            label = ?, content = ?, tech_stack = ?, github_url = ?, demo_url = ?,
            project_year = ?, status = ?, seo_title = ?, seo_description = ?, published_at = ?
        WHERE id = ?
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
        $id,
    ]);

    $message = $data['status'] === 'published' ? 'Project updated successfully.' : 'Draft updated successfully.';
    jsonSuccess($message);

} catch (Exception $e) {
    jsonError('Failed to update project. Please try again.', [], 500);
}
