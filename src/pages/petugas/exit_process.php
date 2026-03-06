<?php
// pages/petugas/exit_process.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['petugas', 'admin']);

if (!isset($_GET['id'])) {
    header("Location: exit.php");
    exit;
}

$id_transaksi = intval($_GET['id']);

// Get Transaction Info
$query = "SELECT t.*, k.no_polisi, k.jenis_kendaraan, a.nama_area, a.id_area 
          FROM transaksi t
          JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan
          JOIN area_parkir a ON t.id_area = a.id_area
          WHERE t.id_transaksi = $id_transaksi AND t.status = 'masuk'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "Transaksi tidak ditemukan atau sudah keluar.";
    exit;
}

$data = mysqli_fetch_assoc($result);

// Calculate Cost
$jam_masuk = strtotime($data['jam_masuk']);
$jam_keluar = time(); // Current Time
$durasi_detik = $jam_keluar - $jam_masuk;
$durasi_jam = ceil($durasi_detik / 3600);
if ($durasi_jam < 1) $durasi_jam = 1;

// Get Rate
$jenis = $data['jenis_kendaraan'];
$q_tarif = mysqli_query($conn, "SELECT tarif FROM tarif WHERE jenis_kendaraan = '$jenis'");
$r_tarif = mysqli_fetch_assoc($q_tarif);
$tarif_per_jam = $r_tarif['tarif'] ?? 2000; // Default fallback

$total_bayar = $durasi_jam * $tarif_per_jam;

// Process POST Confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_begin_transaction($conn);
    try {
        $waktu_keluar_db = date('Y-m-d H:i:s', $jam_keluar);
        
        // Update Transaksi
        $stmt = mysqli_prepare($conn, "UPDATE transaksi SET jam_keluar=?, total_bayar=?, status='keluar' WHERE id_transaksi=?");
        mysqli_stmt_bind_param($stmt, "sii", $waktu_keluar_db, $total_bayar, $id_transaksi);
        mysqli_stmt_execute($stmt);

        // Update Area Capacity
        mysqli_query($conn, "UPDATE area_parkir SET terisi = terisi - 1 WHERE id_area = " . $data['id_area']);

        catatLog($conn, $_SESSION['user_id'], "Proses keluar: " . $data['no_polisi']);

        mysqli_commit($conn);
        header("Location: cetak_struk.php?id=$id_transaksi&tipe=keluar");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Gagal memproses: " . $e->getMessage();
    }
}

$page_title = "Konfirmasi Keluar";
include '../../includes/header.php';
?>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <h3 style="text-align: center; border-bottom: 2px dashed #ccc; padding-bottom: 15px;">Konfirmasi Pembayaran</h3>
    
    <div style="margin: 20px 0;">
        <p><strong>No Polisi:</strong> <?php echo $data['no_polisi']; ?></p>
        <p><strong>Jam Masuk:</strong> <?php echo $data['jam_masuk']; ?></p>
        <p><strong>Jam Keluar:</strong> <?php echo date('Y-m-d H:i:s', $jam_keluar); ?></p>
        <p><strong>Durasi:</strong> <?php echo $durasi_jam; ?> Jam</p>
        <p><strong>Tarif/Jam:</strong> <?php echo formatRupiah($tarif_per_jam); ?></p>
        <hr>
        <h2 style="text-align: center; color: var(--primary);">
            Total: <?php echo formatRupiah($total_bayar); ?>
        </h2>
    </div>

    <form method="POST">
        <button type="submit" class="btn btn-success" style="width: 100%; padding: 15px; font-size: 1.1em;">BAYAR & SELESAI</button>
        <br><br>
        <a href="exit.php" class="btn btn-danger" style="width: 100%; text-align: center;">Batal</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
