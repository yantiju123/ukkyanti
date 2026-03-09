<?php
// api/setup.php
// Foolproof Script: SQL is embedded directly to avoid "File Not Found" errors.

require_once __DIR__ . '/../config/db.php';

// The SQL content embedded directly
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_md5 VARCHAR(255) NOT NULL,
    role ENUM('admin', 'petugas', 'owner') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tarif (
    id_tarif INT AUTO_INCREMENT PRIMARY KEY,
    jenis_kendaraan VARCHAR(50) NOT NULL,
    tarif INT NOT NULL
);

CREATE TABLE IF NOT EXISTS area_parkir (
    id_area INT AUTO_INCREMENT PRIMARY KEY,
    nama_area VARCHAR(50) NOT NULL,
    kapasitas INT NOT NULL,
    terisi INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS kendaraan (
    id_kendaraan INT AUTO_INCREMENT PRIMARY KEY,
    no_polisi VARCHAR(20) NOT NULL,
    jenis_kendaraan VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (no_polisi)
);

CREATE TABLE IF NOT EXISTS transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    id_kendaraan INT NOT NULL,
    id_area INT NOT NULL,
    jam_masuk DATETIME NOT NULL,
    jam_keluar DATETIME NULL,
    total_bayar INT NULL,
    id_petugas INT NOT NULL,
    status ENUM('masuk', 'keluar') DEFAULT 'masuk',
    FOREIGN KEY (id_kendaraan) REFERENCES kendaraan(id_kendaraan) ON DELETE CASCADE,
    FOREIGN KEY (id_area) REFERENCES area_parkir(id_area) ON DELETE CASCADE,
    FOREIGN KEY (id_petugas) REFERENCES users(id_user) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS log_aktivitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    aktivitas TEXT NOT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- Insert Default Data
INSERT IGNORE INTO users (username, password_md5, role) VALUES 
('admin', MD5('admin123'), 'admin'),
('petugas', MD5('petugas123'), 'petugas'),
('owner', MD5('owner123'), 'owner');

INSERT IGNORE INTO tarif (jenis_kendaraan, tarif) VALUES 
('Motor', 2000),
('Mobil', 5000),
('Truk', 10000);

INSERT IGNORE INTO area_parkir (nama_area, kapasitas) VALUES 
('Lantai 1 - Motor', 100),
('Lantai 2 - Mobil', 50);
";

echo "<h2>💎 Ultimate Setup - Database Aiven 💎</h2>";
echo "<p>Menjalankan query di database: <b>" . ($db ?? 'defaultdb') . "</b></p>";

// Force select database just in case
mysqli_select_db($conn, $db ?? 'defaultdb');

if (mysqli_multi_query($conn, $sql)) {
    $executed = 0;
    do {
        $executed++;
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
    
    echo "<h1 style='color:#10b981;'>✅ IMPOR BERHASIL!</h1>";
    echo "<p>Total $executed blok query telah dieksekusi di database Aiven.</p>";
    echo "<hr>";
    echo "<h3>Langkah selanjutnya:</h3>";
    echo "<ul>
            <li>Halaman Login: <a href='/' style='color:#2563eb; font-weight:bold;'>Buka E-Parking</a></li>
            <li>Username: <b>admin</b></li>
            <li>Password: <b>admin123</b></li>
          </ul>";
} else {
    echo "<h3 style='color:#ef4444;'>❌ IMPOR GAGAL!</h3>";
    echo "<p>Error: " . mysqli_error($conn) . "</p>";
}
?>
