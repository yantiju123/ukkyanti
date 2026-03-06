<?php
// src/config/db.php

// Deteksi apakah sedang di Vercel
$isVercel = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL');

// Ambil variabel lingkungan
$host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST');
$user = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER');
$pass = $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASS');
$db   = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? getenv('DB_NAME');
$port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';

// Jika di Vercel tapi variabel kosong, tampilkan pesan instruksi
if ($isVercel && !$host) {
    die("Error: Variabel Database (DB_HOST) belum terbaca di Vercel. Pastikan Anda sudah menambahkannya di Dashboard Vercel > Settings > Environment Variables dan melakukan redeploy.");
}

// Jika tidak di Vercel (Lokal), gunakan default XAMPP
if (!$isVercel && !$host) {
    $host = 'localhost';
    $user = 'root';
    $pass = ''; 
    $db   = 'ukk_yanti';
    $port = '3306';
}

// Koneksi ke Database
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi Database Gagal! Host: " . $host . " - Error: " . mysqli_connect_error());
}

// Set Timezone
date_default_timezone_set('Asia/Jakarta');
?>
