<?php
// pages/admin/area.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

cekLogin();
cekRole(['admin']);

$page_title = "Kelola Area Parkir";
$error = '';
$success = '';

// Default values for form
$is_edit = false;
$edit_id = '';
$edit_nama = '';
$edit_kapasitas = '';
$edit_jenis = '';

// Handle Delete
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM area_parkir WHERE id_area = $id");
    catatLog($conn, $_SESSION['user_id'], "Menghapus area parkir ID: $id");
    header("Location: area.php?msg=deleted");
    exit;
}

// Handle Form Submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_input = bersihkanInput($_POST['nama_area']);
    $jenis_input = bersihkanInput($_POST['jenis_kendaraan']);
    $kapasitas_input = intval($_POST['kapasitas']);
    $id_input = isset($_POST['id_area']) ? intval($_POST['id_area']) : 0;
    
    if ($id_input > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE area_parkir SET nama_area=?, jenis_kendaraan=?, kapasitas=? WHERE id_area=?");
        mysqli_stmt_bind_param($stmt, "ssii", $nama_input, $jenis_input, $kapasitas_input, $id_input);
        if (mysqli_stmt_execute($stmt)) {
            catatLog($conn, $_SESSION['user_id'], "Edit area: $nama_input");
            header("Location: area.php?msg=updated");
            exit;
        } else { $error = "Gagal mengupdate area."; }
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO area_parkir (nama_area, jenis_kendaraan, kapasitas) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssi", $nama_input, $jenis_input, $kapasitas_input);
        if (mysqli_stmt_execute($stmt)) {
            catatLog($conn, $_SESSION['user_id'], "Tambah area baru: $nama_input");
            header("Location: area.php?msg=added");
            exit;
        } else { $error = "Gagal menambah area."; }
    }
}

// Handle Edit Selection
if (isset($_GET['edit'])) {
    $is_edit = true;
    $edit_id = intval($_GET['edit']);
    $query_edit = "SELECT * FROM area_parkir WHERE id_area = $edit_id";
    $res_edit = mysqli_query($conn, $query_edit);
    if ($row_edit = mysqli_fetch_assoc($res_edit)) {
        $edit_nama = $row_edit['nama_area'];
        $edit_kapasitas = $row_edit['kapasitas'];
        $edit_jenis = $row_edit['jenis_kendaraan'];
    }
}

// Fetch Stats
$total_area = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM area_parkir"))['total'];
$total_kapasitas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(kapasitas) as total FROM area_parkir"))['total'];
$total_terisi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(terisi) as total FROM area_parkir"))['total'];

include __DIR__ . '/../../includes/header.php';
?>

<!-- Statistics Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition border-l-4 border-teal-500">
        <div class="p-4 bg-teal-50 rounded-xl text-teal-600 font-bold">
            <i class="fas fa-layer-group text-2xl"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Area</p>
            <h4 class="text-2xl font-black text-gray-800"><?php echo $total_area; ?></h4>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition border-l-4 border-blue-500">
        <div class="p-4 bg-blue-50 rounded-xl text-blue-600 font-bold">
            <i class="fas fa-grip text-2xl"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kapasitas Total</p>
            <h4 class="text-2xl font-black text-gray-800"><?php echo $total_kapasitas ?? 0; ?></h4>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition border-l-4 border-orange-500">
        <div class="p-4 bg-orange-50 rounded-xl text-orange-600 font-bold">
            <i class="fas fa-car-tunnel text-2xl"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kendaraan Terparkir</p>
            <h4 class="text-2xl font-black text-gray-800"><?php echo $total_terisi ?? 0; ?></h4>
        </div>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-8">
    <!-- Kolom Kiri: Tabel Area Parkir -->
    <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-6 bg-teal-500 rounded-full"></div>
                    <h3 class="text-lg font-bold text-gray-700">Daftar Slot & Area</h3>
                </div>
                <?php if(isset($_GET['msg'])): ?>
                    <span class="text-[10px] font-black px-2 py-1 bg-green-100 text-green-700 rounded-lg animate-pulse uppercase">Berhasil Diperbarui</span>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto p-6">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-400 text-xs font-black uppercase tracking-widest border-b border-gray-100">
                            <th class="pb-4">Nama Area</th>
                            <th class="pb-4">Kapasitas / Okupansi</th>
                            <th class="pb-4 text-center">Status</th>
                            <th class="pb-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php
                        $query = "SELECT * FROM area_parkir ORDER BY nama_area ASC";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)):
                            $terisi = $row['terisi'];
                            $kapasitas = $row['kapasitas'];
                            $jenis = $row['jenis_kendaraan'];
                            $persen = ($kapasitas > 0) ? ($terisi / $kapasitas) * 100 : 0;
                            $color = $persen >= 90 ? 'bg-red-500' : ($persen >= 70 ? 'bg-orange-500' : 'bg-teal-500');

                            // Icon and Style based on Type
                            $icon = 'fa-car';
                            $type_bg = 'bg-indigo-50 text-indigo-600';
                            if ($jenis == 'Motor') { $icon = 'fa-motorcycle'; $type_bg = 'bg-teal-50 text-teal-600'; }
                            if ($jenis == 'Truk') { $icon = 'fa-truck-moving'; $type_bg = 'bg-amber-50 text-amber-600'; }
                        ?>
                        <tr class="group hover:bg-gray-50 transition-colors">
                            <td class="py-5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl <?php echo $type_bg; ?> flex items-center justify-center shadow-sm border border-current border-opacity-10">
                                        <i class="fas <?php echo $icon; ?> text-lg"></i>
                                    </div>
                                    <div>
                                        <div class="font-black text-gray-700 tracking-tight"><?php echo htmlspecialchars($row['nama_area']); ?></div>
                                        <div class="text-[9px] font-black uppercase text-gray-400">ID: AREA-0<?php echo $row['id_area']; ?> • <?php echo $jenis; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5">
                                <div class="flex items-center justify-between mb-1.5 px-1">
                                    <span class="text-xs font-bold text-gray-500 leading-none"><?php echo $terisi; ?> / <?php echo $kapasitas; ?> <small class="text-[9px] opacity-70">Slot</small></span>
                                    <span class="text-[10px] font-black text-teal-600 leading-none"><?php echo round($persen); ?>%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                                    <div class="<?php echo $color; ?> h-full transition-all duration-1000" style="width: <?php echo $persen; ?>%"></div>
                                </div>
                            </td>
                            <td class="py-5 text-center">
                                <?php if($persen >= 100): ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-[9px] font-black uppercase ring-1 ring-red-200">Full</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[9px] font-black uppercase ring-1 ring-green-200">Available</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-5 text-right">
                                <div class="flex justify-end space-x-1.5">
                                    <a href="area.php?edit=<?php echo $row['id_area']; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm border border-blue-100">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <a href="area.php?hapus=<?php echo $row['id_area']; ?>" class="w-8 h-8 rounded-lg flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm border border-red-100" onclick="return confirm('Hapus area ini?');">
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

    <!-- Kolom Kanan: Form Area -->
    <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden sticky top-8">
            <div class="bg-gradient-to-br from-teal-600 to-teal-400 px-6 py-7 text-white relative">
                <i class="fas fa-map-marked-alt absolute right-[-10px] bottom-[-10px] text-7xl opacity-10"></i>
                <h3 class="text-xl font-black italic tracking-tighter uppercase leading-none">
                    <?php echo $is_edit ? 'Update Konfigurasi' : 'Registrasi Area'; ?>
                </h3>
                <p class="text-teal-100 text-[10px] font-bold uppercase tracking-widest mt-2">Space Management System</p>
            </div>

            <form method="POST" action="area.php" class="p-6 space-y-5">
                <input type="hidden" name="id_area" value="<?php echo $edit_id; ?>">
                
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 pl-1">Nama Lokasi / Area</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-3 text-gray-300 group-focus-within:text-teal-500 transition"><i class="fas fa-location-dot"></i></span>
                        <input type="text" name="nama_area" class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white transition text-gray-700 font-bold" 
                        value="<?php echo htmlspecialchars($edit_nama); ?>" required placeholder="Mis: Lantai 1">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 pl-1">Jenis Kendaraan</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-3 text-gray-300 group-focus-within:text-teal-500 transition"><i class="fas fa-car-side"></i></span>
                        <select name="jenis_kendaraan" class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white transition text-gray-700 font-bold appearance-none">
                            <option value="Motor" <?php echo $edit_jenis == 'Motor' ? 'selected' : ''; ?>>Motor</option>
                            <option value="Mobil" <?php echo $edit_jenis == 'Mobil' ? 'selected' : ''; ?>>Mobil</option>
                            <option value="Truk" <?php echo $edit_jenis == 'Truk' ? 'selected' : ''; ?>>Truk</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 pl-1">Kapasitas Maksimal (Slot)</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-3 text-gray-300 group-focus-within:text-teal-500 transition"><i class="fas fa-expand"></i></span>
                        <input type="number" name="kapasitas" class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white transition text-gray-700 font-black" 
                        value="<?php echo htmlspecialchars($edit_kapasitas); ?>" required placeholder="100">
                    </div>
                </div>

                <div class="pt-2 space-y-3">
                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-black py-4 rounded-xl transition shadow-lg shadow-teal-100 flex items-center justify-center space-x-2 uppercase text-sm tracking-tighter">
                        <i class="fas <?php echo $is_edit ? 'fa-rotate' : 'fa-plus-circle'; ?>"></i>
                        <span><?php echo $is_edit ? 'Simpan Perubahan' : 'Daftarkan Lokasi'; ?></span>
                    </button>
                    
                    <?php if($is_edit): ?>
                        <a href="area.php" class="block w-full text-center py-2 text-gray-400 font-bold hover:text-gray-600 transition text-xs">Batalkan Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
