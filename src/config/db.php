<?php
// src/config/db.php

// Ambil variabel lingkungan dari Vercel/Aiven
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT') ?: '3306';

// Jika variabel tidak ditemukan (biasanya saat di localhost XAMPP)
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
    die("Koneksi Database Gagal: " . mysqli_connect_error() . " (Host: $host)");
}

// Set Timezone
date_default_timezone_set('Asia/Jakarta');
?>
