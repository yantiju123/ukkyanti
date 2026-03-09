<?php
// api/index.php
// Robust Router for Vercel PHP - Handles nested directory includes

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = explode('?', $uri)[0];

// Project Root (one level up from /api)
$root = dirname(__DIR__);

// Normalize URI (remove /src/ if present)
if (strpos($uri, '/src/') === 0) {
    $uri = substr($uri, 4);
}

// Target detection
$targetFile = ($uri === '/' || $uri === '') ? $root . '/index.php' : $root . $uri;

// Directory access defaults to index.php
if (is_dir($targetFile)) {
    $targetFile = rtrim($targetFile, '/') . '/index.php';
}

if (file_exists($targetFile) && is_file($targetFile) && pathinfo($targetFile, PATHINFO_EXTENSION) === 'php') {
    // CRITICAL: Change working directory to the target file's own folder.
    // This allows relative paths like "../../config/db.php" to resolve correctly.
    chdir(dirname($targetFile));
    
    // Use absolute path for the require to be 100% sure.
    require_once $targetFile;
    exit;
}

// Fallback to 404
http_response_code(404);
echo "404 - Not Found - File: " . htmlspecialchars($uri);
