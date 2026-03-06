<?php
// pages/admin/tarif.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['admin']);

$page_title = "Kelola Tarif Parkir";
$error = '';
$success = '';

// Default values for form
$is_edit = false;
$edit_id = '';
$edit_jenis = '';
$edit_tarif = '';

// Handle Delete
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM tarif WHERE id_tarif = $id");
    catatLog($conn, $_SESSION['user_id'], "Menghapus tarif ID: $id");
    header("Location: tarif.php?msg=deleted");
    exit;
}

// Handle Form Submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_input = bersihkanInput($_POST['jenis']);
    $tarif_input = intval($_POST['tarif']);
    $id_input = isset($_POST['id_tarif']) ? intval($_POST['id_tarif']) : 0;
    
    if ($id_input > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE tarif SET jenis_kendaraan=?, tarif=? WHERE id_tarif=?");
        mysqli_stmt_bind_param($stmt, "sii", $jenis_input, $tarif_input, $id_input);
        if (mysqli_stmt_execute($stmt)) {
            catatLog($conn, $_SESSION['user_id'], "Edit tarif: $jenis_input");
            header("Location: tarif.php?msg=updated");
            exit;
        } else {
            $error = "Gagal mengupdate tarif.";
        }
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO tarif (jenis_kendaraan, tarif) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "si", $jenis_input, $tarif_input);
        if (mysqli_stmt_execute($stmt)) {
            catatLog($conn, $_SESSION['user_id'], "Tambah tarif baru: $jenis_input");
            header("Location: tarif.php?msg=added");
            exit;
        } else {
            $error = "Gagal menambah tarif.";
        }
    }
}

// Handle Edit Selection
if (isset($_GET['edit'])) {
    $is_edit = true;
    $edit_id = intval($_GET['edit']);
    $query_edit = "SELECT * FROM tarif WHERE id_tarif = $edit_id";
    $res_edit = mysqli_query($conn, $query_edit);
    if ($row_edit = mysqli_fetch_assoc($res_edit)) {
        $edit_jenis = $row_edit['jenis_kendaraan'];
        $edit_tarif = $row_edit['tarif'];
    }
}

// Fetch Stats
$total_jenis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tarif"))['total'];
$max_tarif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(tarif) as max_t FROM tarif"))['max_t'];
$avg_tarif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(tarif) as avg_t FROM tarif"))['avg_t'];

include '../../includes/header.php';
?>

<!-- Statistics Cards Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition duration-300 border-l-4 border-teal-500">
        <div class="p-4 bg-teal-50 rounded-xl text-teal-600">
            <i class="fas fa-truck-pickup text-2xl"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Kategori</p>
            <h4 class="text-2xl font-black text-gray-800"><?php echo $total_jenis; ?></h4>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition duration-300 border-l-4 border-orange-500">
        <div class="p-4 bg-orange-50 rounded-xl text-orange-600">
            <i class="fas fa-arrow-up-wide-short text-2xl"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tarif Tertinggi</p>
            <h4 class="text-2xl font-black text-gray-800"><?php echo formatRupiah($max_tarif ?? 0); ?></h4>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition duration-300 border-l-4 border-blue-500">
        <div class="p-4 bg-blue-50 rounded-xl text-blue-600">
            <i class="fas fa-calculator text-2xl"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Rata-rata Tarif</p>
            <h4 class="text-2xl font-black text-gray-800"><?php echo formatRupiah($avg_tarif ?? 0); ?></h4>
        </div>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-8">
    
    <!-- Kolom Kiri: Tabel Tarif Modern -->
    <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-6 bg-teal-500 rounded-full"></div>
                    <h3 class="text-lg font-bold text-gray-700">Daftar Tarif Konfigurasi</h3>
                </div>
                <?php if(isset($_GET['msg'])): ?>
                    <div class="flex items-center space-x-2 px-3 py-1 bg-green-100 text-green-700 rounded-lg text-sm font-bold animate-pulse">
                        <i class="fas fa-check-circle"></i>
                        <span>Update Berhasil!</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto px-6 pb-6">
                <table class="w-full mt-4">
                    <thead>
                        <tr class="text-left text-gray-400 text-xs font-bold uppercase tracking-widest border-b border-gray-100">
                            <th class="pb-4 px-2">#</th>
                            <th class="pb-4">Jenis Kendaraan</th>
                            <th class="pb-4">Tarif (per Jam)</th>
                            <th class="pb-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php
                        $query = "SELECT * FROM tarif ORDER BY jenis_kendaraan ASC";
                        $result = mysqli_query($conn, $query);
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr class="group hover:bg-teal-50/30 transition-colors duration-200">
                            <td class="py-4 px-2 text-gray-400 font-medium"><?php echo $no++; ?></td>
                            <td class="py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 group-hover:bg-white flex items-center justify-center text-gray-500 group-hover:text-teal-600 border border-transparent group-hover:border-teal-100 transition shadow-sm">
                                        <?php 
                                            $jenis = strtolower($row['jenis_kendaraan']);
                                            if(strpos($jenis, 'motor') !== false) echo '<i class="fas fa-motorcycle text-lg"></i>';
                                            elseif(strpos($jenis, 'mobil') !== false) echo '<i class="fas fa-car-side text-lg"></i>';
                                            elseif(strpos($jenis, 'sepeda') !== false) echo '<i class="fas fa-bicycle text-lg"></i>';
                                            else echo '<i class="fas fa-van-shuttle text-lg"></i>';
                                        ?>
                                    </div>
                                    <span class="font-bold text-gray-700"><?php echo htmlspecialchars($row['jenis_kendaraan']); ?></span>
                                </div>
                            </td>
                            <td class="py-4 font-black text-teal-700">
                                <span class="bg-teal-100/50 px-3 py-1 rounded-full text-sm"><?php echo formatRupiah($row['tarif']); ?></span>
                            </td>
                            <td class="py-4">
                                <div class="flex justify-center space-x-2">
                                    <a href="tarif.php?edit=<?php echo $row['id_tarif']; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm" title="Edit">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                    </a>
                                    <a href="tarif.php?hapus=<?php echo $row['id_tarif']; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm" title="Hapus" onclick="return confirm('Hapus tarif ini?');">
                                        <i class="fas fa-trash-can text-xs"></i>
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

    <!-- Kolom Kanan: Form input Premium -->
    <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden sticky top-8">
            <div class="bg-gradient-to-r from-teal-600 to-teal-400 px-6 py-6 text-white text-center relative overflow-hidden">
                <i class="fas fa-money-check-dollar absolute -right-4 -bottom-4 text-7xl opacity-10"></i>
                <h3 class="text-xl font-black italic tracking-tighter">
                    <?php echo $is_edit ? 'PERBARUI DATA' : 'TAMBAH TARIF'; ?>
                </h3>
                <p class="text-teal-100 text-xs font-semibold uppercase mt-1">Konfigurasi Harga parkir</p>
            </div>

            <form method="POST" action="tarif.php" class="p-8 space-y-6">
                <input type="hidden" name="id_tarif" value="<?php echo $edit_id; ?>">
                
                <div class="space-y-2">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest pl-1">Identitas Kendaraan</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-300 group-focus-within:text-teal-500 transition">
                            <i class="fas fa-car"></i>
                        </div>
                        <input type="text" name="jenis" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white transition-all text-gray-700 font-bold placeholder-gray-300" 
                        value="<?php echo htmlspecialchars($edit_jenis); ?>" required placeholder="Mis: Motor Gede">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest pl-1">Nominal Tarif (IDR)</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-300 group-focus-within:text-teal-500 transition">
                            <i class="fas fa-coins text-lg"></i>
                        </div>
                        <input type="number" name="tarif" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white transition-all text-gray-700 font-black text-xl placeholder-gray-300" 
                        value="<?php echo htmlspecialchars($edit_tarif); ?>" required placeholder="2000">
                    </div>
                    <p class="text-[10px] text-gray-400 font-medium pl-1 italic">* Masukkan angka saja tanpa titik/koma</p>
                </div>

                <div class="pt-4 space-y-3">
                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-black py-4 rounded-xl transition duration-300 shadow-lg shadow-teal-100 flex items-center justify-center space-x-2 uppercase tracking-tighter">
                        <i class="fas <?php echo $is_edit ? 'fa-rotate' : 'fa-plus-circle'; ?>"></i>
                        <span><?php echo $is_edit ? 'Simpan Perubahan' : 'Daftarkan Tarif'; ?></span>
                    </button>
                    
                    <?php if($is_edit): ?>
                        <a href="tarif.php" class="block w-full text-center py-3 text-gray-400 font-bold hover:text-gray-600 transition text-sm">
                            Batalkan Pengeditan
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>
