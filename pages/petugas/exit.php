<?php
// pages/petugas/exit.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['petugas', 'admin']);

$page_title = "Kendaraan Keluar & Pembayaran";

// Search Logic for Parked Vehicles
$where = "WHERE t.status = 'masuk'";
$search_query = '';
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_query = bersihkanInput($_GET['q']);
    $where .= " AND k.no_polisi LIKE '%$search_query%'";
}

// Stats Data
$today = date('Y-m-d');
$total_exit_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status = 'keluar' AND DATE(jam_keluar) = '$today'"))['total'] ?? 0;
$current_parked = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status = 'masuk'"))['total'] ?? 0;
$revenue_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_bayar) as total FROM transaksi WHERE status = 'keluar' AND DATE(jam_keluar) = '$today'"))['total'] ?? 0;

include '../../includes/header.php';
?>

<!-- Statistics Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 border-l-4 border-red-500">
        <div class="p-4 bg-red-50 rounded-xl text-red-600">
            <i class="fas fa-arrow-left-to-bracket fa-flip-horizontal text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Keluar Hari Ini</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $total_exit_today; ?></h4>
            <div class="text-[9px] text-red-400 font-bold">Checkout processed</div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 border-l-4 border-orange-500">
        <div class="p-4 bg-orange-50 rounded-xl text-orange-600">
            <i class="fas fa-car-tunnel text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Masih Parkir</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $current_parked; ?></h4>
            <div class="text-[9px] text-orange-400 font-bold">Waiting to checkout</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 border-l-4 border-teal-500">
        <div class="p-4 bg-teal-50 rounded-xl text-teal-600">
            <i class="fas fa-wallet text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Kas Masuk Hari Ini</p>
            <h4 class="text-xl font-black text-gray-800 tracking-tighter"><?php echo formatRupiah($revenue_today); ?></h4>
            <div class="text-[9px] text-teal-400 font-bold">Total revenue collected</div>
        </div>
    </div>
</div>

<div class="flex flex-col xl:flex-row gap-8">
    
    <!-- Left Column: Active Parked Vehicles (Search & Process) -->
    <div class="w-full xl:w-2/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-6 bg-teal-500 rounded-full"></div>
                    <h3 class="text-lg font-bold text-gray-700 tracking-tight">Proses Checkout Kendaraan</h3>
                </div>
                
                <form method="GET" class="relative group w-full sm:w-64">
                    <i class="fas fa-search absolute left-4 top-3.5 text-gray-400 group-focus-within:text-teal-500 transition"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" 
                           placeholder="Cari Plat Nomor..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border-2 border-gray-100 rounded-xl focus:outline-none focus:border-teal-500 transition text-sm font-bold text-gray-700 uppercase">
                </form>
            </div>

            <div class="overflow-x-auto p-6">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-400 text-[10px] font-black uppercase tracking-widest border-b border-gray-100">
                            <th class="pb-4">Plat Nomor</th>
                            <th class="pb-4">Informasi Area</th>
                            <th class="pb-4">Waktu Masuk</th>
                            <th class="pb-4">Durasi</th>
                            <th class="pb-4 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php
                        $query = "SELECT t.*, k.no_polisi, k.jenis_kendaraan, a.nama_area 
                                  FROM transaksi t
                                  JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan
                                  JOIN area_parkir a ON t.id_area = a.id_area
                                  $where
                                  ORDER BY t.jam_masuk ASC";
                        $result = mysqli_query($conn, $query);
                        
                        while ($row = mysqli_fetch_assoc($result)):
                            $masuk = strtotime($row['jam_masuk']);
                            $diff = time() - $masuk;
                            $hours = ceil($diff / 3600);
                        ?>
                        <tr class="group hover:bg-teal-50/30 transition-colors">
                            <td class="py-4">
                                <span class="bg-gray-900 text-white px-3 py-1.5 rounded border-2 border-gray-700 font-black tracking-widest text-xs shadow-sm inline-block">
                                    <?php echo $row['no_polisi']; ?>
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="font-bold text-gray-700 text-sm"><?php echo $row['jenis_kendaraan']; ?></div>
                                <div class="text-[10px] text-teal-600 font-bold uppercase tracking-tighter"><?php echo $row['nama_area']; ?></div>
                            </td>
                            <td class="py-4 text-xs font-bold text-gray-500">
                                <?php echo date('d M, H:i', $masuk); ?>
                            </td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-[9px] font-black uppercase ring-1 ring-blue-100">
                                    ~ <?php echo $hours; ?> Jam
                                </span>
                            </td>
                            <td class="py-4 text-center">
                                <a href="exit_process.php?id=<?php echo $row['id_transaksi']; ?>" 
                                   class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all shadow-md shadow-red-100 inline-flex items-center space-x-2">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Checkout</span>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($result) == 0): ?>
                            <tr><td colspan="5" class="p-12 text-center text-gray-400 italic">Tidak ada kendaraan parkir aktif yang ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Recent Exits Table (Audit) -->
    <div class="w-full xl:w-1/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center space-x-2 bg-gray-50/50">
                <div class="w-1.5 h-5 bg-red-500 rounded-full"></div>
                <h3 class="font-black text-gray-700 tracking-tight uppercase text-sm">Update Keluar Terbaru</h3>
            </div>
            
            <div class="p-0">
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-gray-50">
                        <?php
                        $q_recent_exits = "SELECT t.*, k.no_polisi, k.jenis_kendaraan 
                                           FROM transaksi t 
                                           JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                                           WHERE t.status = 'keluar' 
                                           ORDER BY t.jam_keluar DESC LIMIT 8";
                        $res_recent = mysqli_query($conn, $q_recent_exits);
                        while($row = mysqli_fetch_assoc($res_recent)):
                        ?>
                        <tr class="hover:bg-gray-50/50 transition whitespace-nowrap">
                            <td class="px-6 py-4">
                                <span class="bg-gray-800 text-white px-2 py-0.5 rounded text-[9px] font-black tracking-widest border border-gray-600">
                                    <?php echo $row['no_polisi']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-[9px] text-gray-400 font-bold uppercase leading-none"><?php echo $row['jenis_kendaraan']; ?></div>
                                <div class="text-[10px] font-black text-teal-600 mt-1"><?php echo formatRupiah($row['total_bayar']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-[10px] font-black text-gray-300 italic">
                                    <?php echo date('H:i', strtotime($row['jam_keluar'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($res_recent) == 0): ?>
                            <tr><td class="p-10 text-center text-gray-300 italic text-[10px]">Belum ada data checkout.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <p class="text-[9px] text-center font-bold text-gray-400 uppercase tracking-widest leading-relaxed">
                    Data logout kendaraan diperbarui secara real-time dari database pusat.
                </p>
            </div>
        </div>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>
