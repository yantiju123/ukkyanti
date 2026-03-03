<?php
// pages/admin/area_form.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['admin']);

$page_title = "Form Area Parkir";
$is_edit = false;
$nama = '';
$kapasitas = '';

if (isset($_GET['id'])) {
    $is_edit = true;
    $id = intval($_GET['id']);
    $query = "SELECT * FROM area_parkir WHERE id_area = $id";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $nama = $row['nama_area'];
        $kapasitas = $row['kapasitas'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_input = bersihkanInput($_POST['nama']);
    $kapasitas_input = intval($_POST['kapasitas']);

    if ($is_edit) {
        $stmt = mysqli_prepare($conn, "UPDATE area_parkir SET nama_area=?, kapasitas=? WHERE id_area=?");
        mysqli_stmt_bind_param($stmt, "sii", $nama_input, $kapasitas_input, $id);
        catatLog($conn, $_SESSION['user_id'], "Edit area $nama_input");
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO area_parkir (nama_area, kapasitas) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "si", $nama_input, $kapasitas_input);
        catatLog($conn, $_SESSION['user_id'], "Tambah area $nama_input");
    }
    mysqli_stmt_execute($stmt);
    header("Location: area.php");
    exit;
}

include '../../includes/header.php';
?>

<div class="card" style="max-width: 500px;">
    <h3><?php echo $is_edit ? 'Edit Area' : 'Tambah Area'; ?></h3>
    <br>
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Nama Area</label>
            <input type="text" name="nama" class="form-control" value="<?php echo $nama; ?>" required placeholder="Contoh: Lantai 1">
        </div>
        <div class="form-group">
            <label class="form-label">Kapasitas</label>
            <input type="number" name="kapasitas" class="form-control" value="<?php echo $kapasitas; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="area.php" class="btn" style="background: #e2e8f0; color: #333;">Batal</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
