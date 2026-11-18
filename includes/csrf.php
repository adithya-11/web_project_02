<?php
declare(strict_types=1);

function csrf_field(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function require_post(): void
{
    if (!is_post()) {
        header('Allow: POST');
        http_response_code(405);
        exit('Method not allowed.');
    }
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], post_string('csrf'))) {
        http_response_code(403);
        exit('This form has expired. Return to the previous page and reload it.');
    }
}
