<?php
// pages/admin/kendaraan.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

cekLogin();
cekRole(['admin']);

$page_title = "Manajemen Kendaraan";
$error = '';
$success = '';

// Default values for form
$is_edit = false;
$edit_id = '';
$edit_no_polisi = '';
$edit_jenis = '';
$edit_pemilik = '';
$edit_warna = '';
$edit_status = 'aktif';

// Handle Delete
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM kendaraan WHERE id_kendaraan = $id");
    catatLog($conn, $_SESSION['user_id'], "Menghapus kendaraan master ID: $id");
    header("Location: kendaraan.php?msg=deleted");
    exit;
}

// Handle Form Submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_polisi = strtoupper(bersihkanInput($_POST['no_polisi']));
    $jenis = bersihkanInput($_POST['jenis']);
    $pemilik = bersihkanInput($_POST['pemilik']);
    $warna = bersihkanInput($_POST['warna']);
    $status = bersihkanInput($_POST['status']);
    $id_input = isset($_POST['id_kendaraan']) ? intval($_POST['id_kendaraan']) : 0;
    
    if ($id_input > 0) {
        // Edit
        $stmt = mysqli_prepare($conn, "UPDATE kendaraan SET no_polisi=?, jenis_kendaraan=?, pemilik=?, warna=?, status=? WHERE id_kendaraan=?");
        mysqli_stmt_bind_param($stmt, "sssssi", $no_polisi, $jenis, $pemilik, $warna, $status, $id_input);
        if (mysqli_stmt_execute($stmt)) {
            catatLog($conn, $_SESSION['user_id'], "Update kendaraan: $no_polisi");
            header("Location: kendaraan.php?msg=updated");
            exit;
        } else { $error = "Gagal mengupdate data."; }
    } else {
        // Add
        $stmt = mysqli_prepare($conn, "INSERT INTO kendaraan (no_polisi, jenis_kendaraan, pemilik, warna, status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $no_polisi, $jenis, $pemilik, $warna, $status);
        if (mysqli_stmt_execute($stmt)) {
            catatLog($conn, $_SESSION['user_id'], "Tambah kendaraan: $no_polisi");
            header("Location: kendaraan.php?msg=added");
            exit;
        } else { $error = "Gagal menambah data."; }
    }
}

// Handle Edit Selection
if (isset($_GET['edit'])) {
    $is_edit = true;
    $edit_id = intval($_GET['edit']);
    $query_edit = "SELECT * FROM kendaraan WHERE id_kendaraan = $edit_id";
    $res_edit = mysqli_query($conn, $query_edit);
    if ($row_edit = mysqli_fetch_assoc($res_edit)) {
        $edit_no_polisi = $row_edit['no_polisi'];
        $edit_jenis = $row_edit['jenis_kendaraan'];
        $edit_pemilik = $row_edit['pemilik'];
        $edit_warna = $row_edit['warna'];
        $edit_status = $row_edit['status'];
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="flex flex-col lg:flex-row gap-8">
    
    <!-- Kolom Kiri: Tabel Data Kendaraan -->
    <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-6 bg-teal-500 rounded-full"></div>
                    <h3 class="text-lg font-bold text-gray-700">Database Kendaraan Terdaftar</h3>
                </div>
                <?php if(isset($_GET['msg'])): ?>
                    <span class="text-xs font-bold px-3 py-1 bg-green-100 text-green-700 rounded-full animate-bounce">
                        <?php 
                        if($_GET['msg']=='added') echo "Data Ditambahkan!";
                        if($_GET['msg']=='updated') echo "Data Diperbarui!";
                        if($_GET['msg']=='deleted') echo "Data Dihapus!";
                        ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto p-6">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-400 text-xs font-bold uppercase tracking-widest border-b border-gray-100">
                            <th class="pb-4">Plat Nomor</th>
                            <th class="pb-4">Jenis & Pemilik</th>
                            <th class="pb-4">Warna</th>
                            <th class="pb-4">Status</th>
                            <th class="pb-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php
                        $query = "SELECT * FROM kendaraan ORDER BY id_kendaraan DESC";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr class="group hover:bg-teal-50/30 transition-colors duration-200">
                            <td class="py-4">
                                <span class="bg-gray-900 text-white px-3 py-1.5 rounded border-2 border-gray-700 font-black tracking-widest text-sm shadow-sm inline-block">
                                    <?php echo $row['no_polisi']; ?>
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="font-bold text-gray-700"><?php echo htmlspecialchars($row['pemilik'] ?? '-'); ?></div>
                                <div class="text-xs text-teal-600 font-semibold uppercase"><?php echo $row['jenis_kendaraan']; ?></div>
                            </td>
                            <td class="py-4">
                                <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($row['warna'] ?? '-'); ?></span>
                            </td>
                            <td class="py-4">
                                <?php if(($row['status'] ?? 'aktif') == 'aktif'): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-black uppercase">Aktif</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-[10px] font-black uppercase">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4">
                                <div class="flex justify-center space-x-2">
                                    <a href="kendaraan.php?edit=<?php echo $row['id_kendaraan']; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <a href="kendaraan.php?hapus=<?php echo $row['id_kendaraan']; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm" onclick="return confirm('Hapus data kendaraan ini?');">
                                        <i class="fas fa-trash text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Form input/edit -->
    <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden sticky top-8">
            <div class="bg-gradient-to-r from-teal-600 to-teal-400 px-6 py-6 text-white">
                <h3 class="text-lg font-black italic tracking-tighter uppercase">
                    <?php echo $is_edit ? 'Edit Data Kendaraan' : 'Tambah Kendaraan Baru'; ?>
                </h3>
                <p class="text-teal-100 text-[10px] font-bold uppercase tracking-widest mt-1">Master Data Registry System</p>
            </div>

            <form method="POST" action="kendaraan.php" class="p-6 space-y-4">
                <input type="hidden" name="id_kendaraan" value="<?php echo $edit_id; ?>">
                
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nomor Polisi (Plat)</label>
                    <input type="text" name="no_polisi" class="w-full px-4 py-2.5 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white transition text-gray-700 font-bold uppercase" 
                    value="<?php echo htmlspecialchars($edit_no_polisi); ?>" required placeholder="B 1234 ABC">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jenis</label>
                        <select name="jenis" class="w-full px-4 py-2.5 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white transition text-gray-700 font-bold">
                            <option value="Motor" <?php echo $edit_jenis == 'Motor' ? 'selected' : ''; ?>>Motor</option>
                            <option value="Mobil" <?php echo $edit_jenis == 'Mobil' ? 'selected' : ''; ?>>Mobil</option>
                            <option value="Truk" <?php echo $edit_jenis == 'Truk' ? 'selected' : ''; ?>>Truk</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Warna</label>
                        <input type="text" name="warna" class="w-full px-4 py-2.5 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white transition text-gray-700 font-bold" 
                        value="<?php echo htmlspecialchars($edit_warna); ?>" placeholder="Putih">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nama Pemilik</label>
                    <input type="text" name="pemilik" class="w-full px-4 py-2.5 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white transition text-gray-700 font-bold" 
                    value="<?php echo htmlspecialchars($edit_pemilik); ?>" required placeholder="Nama Lengkap">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Keanggotaan</label>
                    <div class="flex space-x-4 mt-2">
                        <label class="flex items-center space-x-2 cursor-pointer group">
                            <input type="radio" name="status" value="aktif" <?php echo $edit_status == 'aktif' ? 'checked' : ''; ?> class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 focus:ring-teal-500">
                            <span class="text-xs font-bold text-gray-600 group-hover:text-teal-600 transition">Aktif</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer group">
                            <input type="radio" name="status" value="nonaktif" <?php echo $edit_status == 'nonaktif' ? 'checked' : ''; ?> class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 focus:ring-teal-500">
                            <span class="text-xs font-bold text-gray-600 group-hover:text-red-500 transition">Non-Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 space-y-3">
                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-black py-4 rounded-xl transition shadow-lg shadow-teal-100 flex items-center justify-center space-x-2 uppercase text-sm tracking-tighter">
                        <i class="fas <?php echo $is_edit ? 'fa-save' : 'fa-plus-circle'; ?>"></i>
                        <span><?php echo $is_edit ? 'Simpan Perubahan' : 'Daftarkan Kendaraan'; ?></span>
                    </button>
                    
                    <?php if($is_edit): ?>
                        <a href="kendaraan.php" class="block w-full text-center py-2 text-gray-400 font-bold hover:text-gray-600 transition text-xs">
                            Batal & Input Baru
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
