<?php
// api/index.php
// Ultimate Router for Vercel PHP (JuicyFX Runtime)

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = explode('?', $uri)[0];

// Root directory for our private source code
$baseDir = __DIR__ . '/../src';

// Special Handling for Root
if ($uri == '/' || $uri == '') {
    require $baseDir . '/index.php';
    exit;
}

// Map the request to our src directory
$targetFile = $baseDir . $uri;

// If the target is a directory, check for index.php inside it
if (is_dir($targetFile)) {
    $targetFile = rtrim($targetFile, '/') . '/index.php';
}

// Safety check: Only execute PHP files from within the src directory
if (file_exists($targetFile) && is_file($targetFile) && pathinfo($targetFile, PATHINFO_EXTENSION) === 'php') {
    require $targetFile;
    exit;
}

// Fallback to 404
http_response_code(404);
echo "404 - Not Found";
