<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicDir = realpath(__DIR__);

if ($uri === false || $uri === null) {
    http_response_code(400);
    echo 'Bad Request';
    return false;
}

$uri = rtrim((string) $uri, '/') ?: '/';

if (strpos($uri, '..') !== false) {
    http_response_code(400);
    echo 'Bad Request';
    return false;
}

$file = $publicDir . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    $realFile = realpath($file);
    if ($realFile === false || strpos($realFile, $publicDir) !== 0) {
        http_response_code(403);
        echo 'Forbidden';
        return false;
    }

    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    } else {
        header('Content-Type: application/octet-stream');
    }
    readfile($file);
    return true;
}

require __DIR__ . '/index.php';
