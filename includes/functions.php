<?php
// includes/functions.php

// Format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Log Activity
function catatLog($conn, $id_user, $aktivitas) {
    $stmt = mysqli_prepare($conn, "INSERT INTO log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "is", $id_user, $aktivitas);
    mysqli_stmt_execute($stmt);
}

// Clean Input
function bersihkanInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}
?>
