-- Database: ukk_parking

CREATE DATABASE IF NOT EXISTS ukk_parking;
USE ukk_parking;

-- Table: Users (Admin, Petugas, Owner)
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_md5 VARCHAR(255) NOT NULL,
    role ENUM('admin', 'petugas', 'owner') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: Tarif Parkir (e.g., Motor: 2000, Mobil: 5000)
CREATE TABLE IF NOT EXISTS tarif (
    id_tarif INT AUTO_INCREMENT PRIMARY KEY,
    jenis_kendaraan VARCHAR(50) NOT NULL,
    tarif INT NOT NULL -- Tarif per entry or per hour depending on logic
);

-- Table: Area Parkir (e.g., Lantai 1, Area Utara)
CREATE TABLE IF NOT EXISTS area_parkir (
    id_area INT AUTO_INCREMENT PRIMARY KEY,
    nama_area VARCHAR(50) NOT NULL,
    kapasitas INT NOT NULL,
    terisi INT DEFAULT 0
);

-- Table: Kendaraan (Master data of vehicles entered)
CREATE TABLE IF NOT EXISTS kendaraan (
    id_kendaraan INT AUTO_INCREMENT PRIMARY KEY,
    no_polisi VARCHAR(20) NOT NULL,
    jenis_kendaraan VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    -- Index for fast search
    , INDEX (no_polisi)
);

-- Table: Transaksi Parkir
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

-- Table: Log Aktivitas
CREATE TABLE IF NOT EXISTS log_aktivitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    aktivitas TEXT NOT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- Insert Default Users
-- admin / admin123
INSERT INTO users (username, password_md5, role) VALUES 
('admin', MD5('admin123'), 'admin'),
('petugas', MD5('petugas123'), 'petugas'),
('owner', MD5('owner123'), 'owner');

-- Insert Default Tarif
INSERT INTO tarif (jenis_kendaraan, tarif) VALUES 
('Motor', 2000),
('Mobil', 5000),
('Truk', 10000);

-- Insert Default Area
INSERT INTO area_parkir (nama_area, kapasitas) VALUES 
('Lantai 1 - Motor', 100),
('Lantai 2 - Mobil', 100),
('Lantai 3 - Truk', 100);
