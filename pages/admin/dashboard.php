<?php
// pages/admin/dashboard.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

cekLogin();
cekRole(['admin']);

$page_title = "Admin Dashboard";

// Stats Data fetching
$res_user = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$total_user = mysqli_fetch_assoc($res_user)['total'];

$today = date('Y-m-d');
$res_trx = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(total_bayar) as omset FROM transaksi WHERE DATE(jam_masuk) = '$today'");
$data_trx = mysqli_fetch_assoc($res_trx);
$total_trx = $data_trx['total'] ?? 0;
$omset_today = $data_trx['omset'] ?? 0;

$res_parkir = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status = 'masuk'");
$parkir_aktif = mysqli_fetch_assoc($res_parkir)['total'] ?? 0;

$res_cap = mysqli_query($conn, "SELECT SUM(kapasitas) as total FROM area_parkir");
$total_kapasitas = mysqli_fetch_assoc($res_cap)['total'] ?? 0;

include __DIR__ . '/../../includes/header.php';
?>

<!-- Statistics Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 group">
    <!-- Card 1: Users -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-xl hover:-translate-y-1 transition duration-300 border-l-4 border-purple-500">
        <div class="p-4 bg-purple-50 rounded-xl text-purple-600">
            <i class="fas fa-users-viewfinder text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Total Pengguna</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $total_user; ?></h4>
            <div class="text-[9px] text-purple-400 font-bold">Terdaftar di sistem</div>
        </div>
    </div>
    
    <!-- Card 2: Transaksi -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-xl hover:-translate-y-1 transition duration-300 border-l-4 border-blue-500">
        <div class="p-4 bg-blue-50 rounded-xl text-blue-600">
            <i class="fas fa-hand-holding-dollar text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Transaksi Hari Ini</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $total_trx; ?></h4>
            <div class="text-[9px] text-blue-400 font-bold">+<?php echo $total_trx; ?> entri baru</div>
        </div>
    </div>

    <!-- Card 3: Omset -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-xl hover:-translate-y-1 transition duration-300 border-l-4 border-teal-500">
        <div class="p-4 bg-teal-50 rounded-xl text-teal-600">
            <i class="fas fa-sack-dollar text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Omset Hari Ini</p>
            <h4 class="text-xl font-black text-gray-800 tracking-tighter"><?php echo formatRupiah($omset_today); ?></h4>
            <div class="text-[9px] text-teal-400 font-bold">Total pendapatan</div>
        </div>
    </div>

    <!-- Card 4: Parkir Aktif -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-xl hover:-translate-y-1 transition duration-300 border-l-4 border-orange-500">
        <div class="p-4 bg-orange-50 rounded-xl text-orange-600">
            <i class="fas fa-square-p text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Slot Terisi</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $parkir_aktif; ?> <small class="text-xs text-gray-400">/ <?php echo $total_kapasitas; ?></small></h4>
            <div class="w-24 h-1 bg-gray-100 rounded-full mt-1 overflow-hidden">
                <div class="bg-orange-500 h-full" style="width: <?php echo ($total_kapasitas > 0) ? ($parkir_aktif/$total_kapasitas)*100 : 0; ?>%"></div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- Kolom Kiri: Transaksi Terbaru -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <div class="flex items-center space-x-2">
                <div class="w-1.5 h-5 bg-teal-600 rounded-full"></div>
                <h3 class="font-black text-gray-700 tracking-tight">Transaksi Terakhir</h3>
            </div>
            <a href="../admin/logs.php" class="text-[10px] font-black text-teal-600 hover:text-teal-700 uppercase tracking-widest">Lihat Semua</a>
        </div>
        <div class="p-0 flex-grow">
            <table class="w-full text-left text-sm">
                <tbody class="divide-y divide-gray-50">
                    <?php
                    $q_recent = "SELECT t.*, k.no_polisi, k.jenis_kendaraan, a.nama_area 
                                FROM transaksi t 
                                JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                                JOIN area_parkir a ON t.id_area = a.id_area 
                                ORDER BY t.jam_masuk DESC LIMIT 5";
                    $res_recent = mysqli_query($conn, $q_recent);
                    while($row = mysqli_fetch_assoc($res_recent)):
                    ?>
                    <tr class="hover:bg-gray-50/50 transition whitespace-nowrap">
                        <td class="px-6 py-4">
                            <span class="bg-gray-900 text-white px-2 py-0.5 rounded text-[10px] font-black tracking-widest border border-gray-700">
                                <?php echo $row['no_polisi']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-bold text-gray-700"><?php echo $row['nama_area']; ?></div>
                            <div class="text-[9px] text-gray-400 font-bold uppercase"><?php echo $row['jenis_kendaraan']; ?></div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($row['status'] == 'masuk'): ?>
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-[9px] font-black uppercase">Parking</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-400 rounded text-[9px] font-black uppercase">Exit</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right text-[10px] font-bold text-gray-400">
                            <?php echo date('H:i', strtotime($row['jam_masuk'])); ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($res_recent) == 0): ?>
                        <tr><td colspan="4" class="p-10 text-center text-gray-400 italic text-xs">Belum ada transaksi terekam.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kolom Kanan: Status Area Parkir & Info Sistem -->
    <div class="space-y-8">
        <!-- Status Area -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center space-x-2 bg-gray-50/50">
                <div class="w-1.5 h-5 bg-orange-500 rounded-full"></div>
                <h3 class="font-black text-gray-700 tracking-tight">Okupansi Area</h3>
            </div>
            <div class="p-6 space-y-4">
                <?php
                $q_area = "SELECT * FROM area_parkir LIMIT 3";
                $res_area = mysqli_query($conn, $q_area);
                while($row = mysqli_fetch_assoc($res_area)):
                    $percent = ($row['kapasitas'] > 0) ? ($row['terisi']/$row['kapasitas'])*100 : 0;
                ?>
                <div>
                    <div class="flex justify-between text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1.5">
                        <span><?php echo $row['nama_area']; ?></span>
                        <span><?php echo $row['terisi']; ?> / <?php echo $row['kapasitas']; ?></span>
                    </div>
                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="bg-teal-500 h-full rounded-full" style="width: <?php echo $percent; ?>%"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Info Sistem -->
        <div class="bg-gradient-to-br from-teal-600 to-teal-700 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
            <i class="fas fa-shield-halved absolute -right-4 -bottom-4 text-8xl opacity-10"></i>
            <h3 class="text-xl font-black tracking-tighter italic mb-4">SYSTEM STATUS</h3>
            <div class="space-y-4 relative">
                <div class="flex justify-between border-b border-white/10 pb-2">
                    <span class="text-xs font-bold text-teal-100 uppercase tracking-widest">Waktu Server</span>
                    <span class="text-sm font-black font-mono"><?php echo date('H:i'); ?></span>
                </div>
                <div class="flex justify-between border-b border-white/10 pb-2">
                    <span class="text-xs font-bold text-teal-100 uppercase tracking-widest">Database</span>
                    <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full font-black">STABLE</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs font-bold text-teal-100 uppercase tracking-widest">Versi Aplikasi</span>
                    <span class="text-sm font-black">v2.0.26-PRO</span>
                </div>
            </div>
            <div class="mt-6 pt-6 border-t border-white/10">
                <p class="text-[10px] italic text-teal-100/70 leading-relaxed">
                    Terima kasih telah menggunakan layanan **E-PARKIR PRO**. Semua aktivitas Anda dipantau dan dilindungi oleh protokol enkripsi standar industri.
                </p>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
