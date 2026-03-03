<?php
// pages/admin/tarif_form.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['admin']);

$page_title = "Form Tarif";
$is_edit = false;
$jenis = '';
$tarif = '';

if (isset($_GET['id'])) {
    $is_edit = true;
    $id = intval($_GET['id']);
    $query = "SELECT * FROM tarif WHERE id_tarif = $id";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $jenis = $row['jenis_kendaraan'];
        $tarif = $row['tarif'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_input = bersihkanInput($_POST['jenis']);
    $tarif_input = intval($_POST['tarif']);

    if ($is_edit) {
        $stmt = mysqli_prepare($conn, "UPDATE tarif SET jenis_kendaraan=?, tarif=? WHERE id_tarif=?");
        mysqli_stmt_bind_param($stmt, "sii", $jenis_input, $tarif_input, $id);
        catatLog($conn, $_SESSION['user_id'], "Edit tarif $jenis_input");
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO tarif (jenis_kendaraan, tarif) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "si", $jenis_input, $tarif_input);
        catatLog($conn, $_SESSION['user_id'], "Tambah tarif $jenis_input");
    }
    mysqli_stmt_execute($stmt);
    header("Location: tarif.php");
    exit;
}

include '../../includes/header.php';
?>

<div class="card" style="max-width: 500px;">
    <h3><?php echo $is_edit ? 'Edit Tarif' : 'Tambah Tarif'; ?></h3>
    <br>
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Jenis Kendaraan</label>
            <input type="text" name="jenis" class="form-control" value="<?php echo $jenis; ?>" required placeholder="Contoh: Motor">
        </div>
        <div class="form-group">
            <label class="form-label">Tarif (Rp)</label>
            <input type="number" name="tarif" class="form-control" value="<?php echo $tarif; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="tarif.php" class="btn" style="background: #e2e8f0; color: #333;">Batal</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
