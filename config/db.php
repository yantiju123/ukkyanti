<?php
// config/db.php

// Use environment variables for production (Aiven/Vercel)
// Default to local development if variables are not set
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: ''; 
$db   = getenv('DB_NAME') ?: 'ukk_yanti';
$port = getenv('DB_PORT') ?: '3306';

// For Aiven MySQL, we might need a SSL certificate connection in some cases, 
// but usually simple mysqli_connect works if SSL is not forced.
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set Timezone
date_default_timezone_set('Asia/Jakarta');
?>
