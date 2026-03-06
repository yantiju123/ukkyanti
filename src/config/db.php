<?php
// src/config/db.php

// Ambil variabel lingkungan (Sangat fleksibel)
$host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST');
$user = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER');
$pass = $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASS');

// Cek DB_NAME atau DB_NAMA (Dari screenshot user pakai DB_NAMA)
$db   = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? getenv('DB_NAME') ?? 
        $_ENV['DB_NAMA'] ?? $_SERVER['DB_NAMA'] ?? getenv('DB_NAMA');

$port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';

// Cek apakah di Vercel tapi variabel host masih kosong
if ((isset($_SERVER['VERCEL']) || getenv('VERCEL')) && !$host) {
    die("Error: Variabel DB_HOST kosong. Pastikan Anda memasukkan variabel di proyek VERCEL yang BENAR (cek apakah nama proyek di dashboard sama dengan yang di terminal).");
}

// Default Lokal (XAMPP)
if (!$host) {
    $host = 'localhost';
    $user = 'root';
    $pass = ''; 
    $db   = 'ukk_yanti';
    $port = '3306';
}

// Koneksi ke Database
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi Gagal ke Host: $host. Error: " . mysqli_connect_error());
}

// Set Timezone
date_default_timezone_set('Asia/Jakarta');
?>
