<?php
// ============================================================
// JSON Response Helper
// ============================================================

function jsonSuccess(string $message = '', mixed $data = null, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');

    $response = ['success' => true];
    if ($message !== '') $response['message'] = $message;
    if ($data !== null)  $response['data']    = $data;

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function jsonError(string $message, array $errors = [], int $code = 400): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');

    $response = ['success' => false, 'message' => $message];
    if (!empty($errors)) $response['errors'] = $errors;

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function methodNotAllowed(): never
{
    jsonError('Method not allowed.', [], 405);
}

function unauthorized(): never
{
    jsonError('Unauthorized. Please log in.', [], 401);
}

function notFound(string $message = 'Resource not found.'): never
{
    jsonError($message, [], 404);
}
