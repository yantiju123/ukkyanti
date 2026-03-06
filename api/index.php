<?php
// api/index.php
// Bridge router for Vercel

$uri = $_SERVER['REQUEST_URI'];
$uri = explode('?', $uri)[0]; // Hilangkan query string

// Tentukan file mana yang akan dipanggil
$file = __DIR__ . '/..' . $uri;

if ($uri == '/' || $uri == '') {
    // Jika akses root, panggil index.php di folder utama
    require __DIR__ . '/../index.php';
} elseif (file_exists($file) && is_file($file) && pathinfo($file, PATHINFO_EXTENSION) == 'php') {
    // Jika file PHP yang diminta ada (contoh: /pages/admin/dashboard.php)
    require $file;
} else {
    // Fallback ke index.php utama jika tidak ditemukan
    http_response_code(404);
    require __DIR__ . '/../index.php';
}
