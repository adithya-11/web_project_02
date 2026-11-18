<?php
declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = is_string($requestPath) ? rawurldecode($requestPath) : '/';
if (preg_match('~(?:^|/)(?:data|includes|docs|\.[^/]+)(?:/|$)~i', $path)) {
    http_response_code(404);
    exit;
}
return false;
