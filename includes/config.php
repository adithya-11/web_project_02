<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function e(string|int|float $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $page): void
{
    header('Location: ' . $page, true, 303);
    exit;
}

function take(string $key, mixed $default = null): mixed
{
    $value = $_SESSION[$key] ?? $default;
    unset($_SESSION[$key]);
    return $value;
}

function post_string(string $name): string
{
    $value = filter_input(INPUT_POST, $name, FILTER_UNSAFE_RAW);
    return is_string($value) ? $value : '';
}

function vault_theme(): string
{
    $theme = filter_input(INPUT_COOKIE, 'vault_theme', FILTER_UNSAFE_RAW);
    return in_array($theme, ['dark', 'contrast'], true) ? $theme : 'dark';
}

/* Wraps each word in a span so CSS can stagger a word-by-word reveal. */
function reveal(string $text): string
{
    $html = '';
    foreach (preg_split('/\s+/', trim($text)) as $index => $word) {
        $html .= '<span class="w" style="--i:' . $index . '">' . e($word) . '</span> ';
    }
    return '<span class="sr-only">' . e($text) . '</span><span aria-hidden="true">' . rtrim($html) . '</span>';
}

function nav_current(string $page): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? '') === $page ? ' aria-current="page"' : '';
}
