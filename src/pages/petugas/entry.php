<?php
// pages/petugas/entry.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['petugas', 'admin']);

$page_title = "Input Kendaraan Masuk";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_polisi = strtoupper(bersihkanInput($_POST['no_polisi']));
    $id_tarif = intval($_POST['id_tarif']);
    $id_area = intval($_POST['id_area']);

    // Get Tarif Info
    $q_tarif = mysqli_query($conn, "SELECT * FROM tarif WHERE id_tarif = $id_tarif");
    $d_tarif = mysqli_fetch_assoc($q_tarif);
    $jenis_kendaraan = $d_tarif['jenis_kendaraan'];

    // Check Capacity
    $q_area = mysqli_query($conn, "SELECT * FROM area_parkir WHERE id_area = $id_area");
    $d_area = mysqli_fetch_assoc($q_area);
    
    if ($d_area['terisi'] >= $d_area['kapasitas']) {
        $error = "Area Parkir sudah penuh!";
    } else {
        mysqli_begin_transaction($conn);
        try {
            // 1. Check/Insert Master Data Kendaraan
            $q_cek_ken = mysqli_query($conn, "SELECT id_kendaraan FROM kendaraan WHERE no_polisi = '$no_polisi'");
            if (mysqli_num_rows($q_cek_ken) > 0) {
                $id_kendaraan = mysqli_fetch_assoc($q_cek_ken)['id_kendaraan'];
            } else {
                mysqli_query($conn, "INSERT INTO kendaraan (no_polisi, jenis_kendaraan) VALUES ('$no_polisi', '$jenis_kendaraan')");
                $id_kendaraan = mysqli_insert_id($conn);
            }

            // 2. Insert Transaction
            $jam_masuk = date('Y-m-d H:i:s');
            $id_petugas = $_SESSION['user_id'];
            $stmt = mysqli_prepare($conn, "INSERT INTO transaksi (id_kendaraan, id_area, jam_masuk, id_petugas, status) VALUES (?, ?, ?, ?, 'masuk')");
            mysqli_stmt_bind_param($stmt, "iisi", $id_kendaraan, $id_area, $jam_masuk, $id_petugas);
            mysqli_stmt_execute($stmt);
            $id_transaksi = mysqli_insert_id($conn);

            // 3. Update Area Occupancy
            mysqli_query($conn, "UPDATE area_parkir SET terisi = terisi + 1 WHERE id_area = $id_area");
            
            catatLog($conn, $id_petugas, "Input kendaraan masuk: $no_polisi");
            mysqli_commit($conn);
            
            header("Location: cetak_struk.php?id=$id_transaksi&tipe=masuk");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Kesalahan Sistem: " . $e->getMessage();
        }
    }
}

// Stats for Header
$today = date('Y-m-d');
$total_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE DATE(jam_masuk) = '$today'"))['total'];
$current_parked = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status = 'masuk'"))['total'];

include '../../includes/header.php';
?>

<!-- Top Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 border-l-4 border-teal-500">
        <div class="p-4 bg-teal-50 rounded-xl text-teal-600">
            <i class="fas fa-arrow-right-to-bracket text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Total Masuk Hari Ini</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $total_today; ?></h4>
            <div class="text-[9px] text-teal-400 font-bold">Kendaraan terdaftar</div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 border-l-4 border-orange-500">
        <div class="p-4 bg-orange-50 rounded-xl text-orange-600">
            <i class="fas fa-car-rear text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Kendaraan Sedang Parkir</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $current_parked; ?></h4>
            <div class="text-[9px] text-orange-400 font-bold">Menunggu keluar</div>
        </div>
    </div>
</div>

<div class="flex flex-col xl:flex-row gap-8">
    
    <!-- Left Column: Entry Form -->
    <div class="w-full xl:w-1/3">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden sticky top-8">
            <div class="bg-gradient-to-r from-teal-600 to-teal-400 px-6 py-6 text-white text-center">
                <i class="fas fa-id-card-clip text-4xl mb-2 opacity-50"></i>
                <h3 class="text-xl font-black italic tracking-tighter uppercase leading-none">REGISTRASI MASUK</h3>
                <p class="text-teal-100 text-[10px] font-bold uppercase tracking-widest mt-1">Input Data Kedatangan</p>
            </div>

            <?php if ($error): ?>
                <div class="px-6 pt-4">
                    <div class="bg-red-50 text-red-600 p-3 rounded-lg text-xs font-bold flex items-center">
                        <i class="fas fa-triangle-exclamation mr-2"></i> <?php echo $error; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" class="p-8 space-y-5">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Nomor Polisi (Plat)</label>
                    <input type="text" name="no_polisi" class="w-full px-4 py-4 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 focus:bg-white transition-all text-gray-700 font-black text-2xl tracking-widest uppercase text-center placeholder-gray-200" 
                    required placeholder="B 1234 XYZ">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Tipe Kendaraan</label>
                        <select name="id_tarif" class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 transition text-gray-700 font-bold">
                            <?php
                            $q_t = mysqli_query($conn, "SELECT * FROM tarif");
                            while($t = mysqli_fetch_assoc($q_t)) {
                                echo "<option value='{$t['id_tarif']}'>{$t['jenis_kendaraan']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Area Tujuan</label>
                        <select name="id_area" class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-50 rounded-xl focus:outline-none focus:border-teal-500 transition text-gray-700 font-bold">
                            <?php
                            $q_a = mysqli_query($conn, "SELECT * FROM area_parkir");
                            while($a = mysqli_fetch_assoc($q_a)) {
                                $sisa = $a['kapasitas'] - $a['terisi'];
                                $disabled = $sisa <= 0 ? 'disabled' : '';
                                $status_area = $sisa <= 0 ? '(PENUH)' : "($sisa Slot)";
                                echo "<option value='{$a['id_area']}' $disabled>{$a['nama_area']} $status_area</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-black py-4 rounded-xl transition-all duration-300 shadow-lg shadow-teal-100 flex items-center justify-center space-x-3 uppercase tracking-tighter">
                        <i class="fas fa-print"></i>
                        <span>Proses & Cetak Struk</span>
                    </button>
                    <p class="text-[10px] text-gray-400 font-medium mt-4 italic text-center leading-none">
                        * Struk akan tercetak secara otomatis setelah data dikirim
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Recent Entries Table -->
    <div class="w-full xl:w-2/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-6 bg-teal-500 rounded-full"></div>
                    <h3 class="text-lg font-bold text-gray-700 tracking-tight">Monitoring Kendaraan Masuk</h3>
                </div>
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic">10 Transaksi Terakhir</div>
            </div>

            <div class="overflow-x-auto p-6">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-400 text-[10px] font-black uppercase tracking-widest border-b border-gray-100">
                            <th class="pb-4 px-2">Waktu Masuk</th>
                            <th class="pb-4">Plat Nomor</th>
                            <th class="pb-4">Jenis & Sektor</th>
                            <th class="pb-4 text-center">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php
                        $q_recent = "SELECT t.*, k.no_polisi, k.jenis_kendaraan, a.nama_area, u.username 
                                     FROM transaksi t 
                                     JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                                     JOIN area_parkir a ON t.id_area = a.id_area 
                                     JOIN users u ON t.id_petugas = u.id_user
                                     WHERE t.status = 'masuk'
                                     ORDER BY t.jam_masuk DESC LIMIT 10";
                        $res_recent = mysqli_query($conn, $q_recent);
                        while($row = mysqli_fetch_assoc($res_recent)):
                        ?>
                        <tr class="group hover:bg-teal-50/30 transition-colors">
                            <td class="py-4 px-2">
                                <div class="text-sm font-black text-gray-700 tracking-tighter"><?php echo date('H:i', strtotime($row['jam_masuk'])); ?></div>
                                <div class="text-[9px] text-gray-400 font-bold uppercase"><?php echo date('d M', strtotime($row['jam_masuk'])); ?></div>
                            </td>
                            <td class="py-4">
                                <span class="bg-gray-900 text-white px-3 py-1 rounded border-2 border-gray-700 font-black tracking-widest text-xs shadow-sm inline-block">
                                    <?php echo $row['no_polisi']; ?>
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="font-bold text-gray-700 text-sm"><?php echo $row['jenis_kendaraan']; ?></div>
                                <div class="text-[10px] text-teal-600 font-bold uppercase"><?php echo $row['nama_area']; ?></div>
                            </td>
                            <td class="py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-400 border border-gray-200">
                                        <?php echo strtoupper(substr($row['username'], 0,1)); ?>
                                    </div>
                                    <span class="text-[9px] font-bold text-gray-500 mt-1"><?php echo $row['username']; ?></span>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($res_recent) == 0): ?>
                            <tr><td colspan="4" class="p-12 text-center text-gray-400 italic font-medium">Belum ada kendaraan masuk hari ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>
