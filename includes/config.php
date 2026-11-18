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

/* Decorative particles: positions come from a hash so the layout is stable. */
function particles(int $count, string $class, string $salt = 'vault'): string
{
    $html = '<div class="' . e($class) . '" aria-hidden="true">';
    for ($i = 0; $i < $count; $i++) {
        $h = hash('crc32b', $salt . $i);
        $n = static fn (int $o, int $mod): int => hexdec(substr($h, $o, 2)) % $mod;
        $html .= sprintf('<i style="--x:%d%%;--y:%d;--d:%dms;--t:%dms;--r:%ddeg;--s:%.2f;--k:%d"></i>',
            $n(0, 100), $n(2, 100), $n(4, 90) * 30, 1800 + $n(6, 60) * 40, $n(1, 360), 0.6 + $n(3, 9) / 10, $i % 4);
    }
    return $html . '</div>';
}

/* A vault door with real depth: stacked discs give it thickness, the wheel sits on an axle. */
function vault3d(string $class = ''): string
{
    $slices = '';
    for ($z = -40; $z <= 0; $z += 4) {
        $slices .= '<i class="vault3d__slice" style="--z:' . $z . 'px"></i>';
    }
    $axle = '';
    for ($z = 6; $z <= 38; $z += 4) {
        $axle .= '<i class="vault3d__axle" style="--z:' . $z . 'px"></i>';
    }
    return '<div class="vault3d ' . e($class) . '" aria-hidden="true">'
        . '<span class="vault3d__shadow"></span>'
        . '<div class="vault3d__body">' . $slices
        . '<div class="vault3d__face"></div><div class="vault3d__ring"></div><div class="vault3d__bolts"></div>'
        . $axle
        . '<div class="vault3d__wheel"><i></i><i></i><i></i><b></b></div>'
        . '</div></div>';
}

function nav_current(string $page): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? '') === $page ? ' aria-current="page"' : '';
}
