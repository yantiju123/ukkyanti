<?php
// pages/petugas/cetak_struk.php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// No auth check required strictly for printing if we pass ID, but safer with auth
// However, typically receipts might be accessed by pure popup. 
// Just for security, we check session.
session_start();
if (!isset($_SESSION['user_id'])) die("Akses ditolak");

$id = intval($_GET['id']);
$tipe = $_GET['tipe'] ?? 'keluar'; // masuk or keluar

$query = "SELECT t.*, k.no_polisi, k.jenis_kendaraan, a.nama_area, u.username as petugas
          FROM transaksi t
          JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan
          JOIN area_parkir a ON t.id_area = a.id_area
          JOIN users u ON t.id_petugas = u.id_user
          WHERE t.id_transaksi = $id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) die("Data tidak ditemukan");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Parkir</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Courier New', Courier, monospace; width: 300px; padding: 20px; text-align: center; }
        .line { border-bottom: 1px dashed black; margin: 10px 0; }
        .left { text-align: left; }
        .right { text-align: right; float: right; }
        .row { overflow: hidden; }
        h2 { margin: 5px 0; }
        @media print {
            .swal2-container { display: none !important; }
        }
    </style>
</head>
<script>
    function openPrint() {
        Swal.fire({
            title: 'Proses Berhasil!',
            text: 'Struk parkir siap untuk dicetak.',
            icon: 'success',
            confirmButtonColor: '#0d9488', // teal-600
            confirmButtonText: '<i class="fas fa-print"></i> Cetak Sekarang',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.print();
                // Opsi kembali setelah print mengarah ke dashboard
                window.onafterprint = function() {
                    window.location.href = 'dashboard.php';
                };
                setTimeout(function() {
                    window.location.href = 'dashboard.php';
                }, 1000);
            }
        });
    }
</script>
<body onload="openPrint()">
    <h2>E-PARKING</h2>
    <p>SMK UKK 2026</p>
    <div class="line"></div>
    
    <div class="row">
        <span class="left">Tiket ID:</span>
        <span class="right">#<?php echo $data['id_transaksi']; ?></span>
    </div>
    <div class="row">
        <span class="left">No Polisi:</span>
        <span class="right"><?php echo $data['no_polisi']; ?></span>
    </div>
    <div class="row">
        <span class="left">Area:</span>
        <span class="right"><?php echo $data['nama_area']; ?></span>
    </div>
    <div class="row">
        <span class="left">Masuk:</span>
        <span class="right"><?php echo date('d/m/y H:i', strtotime($data['jam_masuk'])); ?></span>
    </div>

    <?php if ($tipe == 'keluar'): ?>
    <div class="row">
        <span class="left">Keluar:</span>
        <span class="right"><?php echo date('d/m/y H:i', strtotime($data['jam_keluar'])); ?></span>
    </div>
    <div class="line"></div>
    <div class="row" style="font-weight: bold; font-size: 1.2em;">
        <span class="left">TOTAL:</span>
        <span class="right"><?php echo formatRupiah($data['total_bayar']); ?></span>
    </div>
    <?php endif; ?>

    <div class="line"></div>
    <p>Terima Kasih</p>
    <small>Petugas: <?php echo $data['petugas']; ?></small>
    
    <br><br>
    <button onclick="window.location.href='dashboard.php'" style="display: none;" class="no-print">Kembali</button>
    <style>
        @media print {
            .no-print { display: none; }
        }
    </style>
</body>
</html>
