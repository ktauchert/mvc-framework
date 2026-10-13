<?php

declare(strict_types=1);

/**
 * PHP built-in server only (composer serve).
 * Existing files under public/ are served as-is; everything else goes to the front controller.
 * Apache/nginx should rewrite to index.php instead of using this file.
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = is_string($uri) ? urldecode($uri) : '/';

if ($uri !== '/' && $uri !== '' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

require __DIR__ . '/index.php';
