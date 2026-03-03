<?php
// config/db.php

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$db   = 'ukk_yanti';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set Timezone
date_default_timezone_set('Asia/Jakarta');
?>
