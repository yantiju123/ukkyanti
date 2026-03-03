<?php
// pages/owner/dashboard.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['owner', 'admin']);

$page_title = "Executive Dashboard";

// Financial Metrics
$today = date('Y-m-d');
$month = date('m');
$year = date('Y');

// 1. Income Today (Completed Payments)
$res_today = mysqli_query($conn, "SELECT SUM(total_bayar) as total FROM transaksi WHERE status = 'keluar' AND DATE(jam_keluar) = '$today'");
$income_today = mysqli_fetch_assoc($res_today)['total'] ?? 0;

// 2. Income This Month
$res_month = mysqli_query($conn, "SELECT SUM(total_bayar) as total FROM transaksi WHERE status = 'keluar' AND MONTH(jam_keluar) = '$month' AND YEAR(jam_keluar) = '$year'");
$income_month = mysqli_fetch_assoc($res_month)['total'] ?? 0;

// 3. Transactions Count This Month
$res_trx_month = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status = 'keluar' AND MONTH(jam_keluar) = '$month' AND YEAR(jam_keluar) = '$year'");
$trx_month = mysqli_fetch_assoc($res_trx_month)['total'] ?? 0;

// 4. Current Vehicles in Lot (Operational Risk)
$current_parked = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status = 'masuk'"))['total'] ?? 0;

include '../../includes/header.php';
?>

<!-- Owner Insights Hero -->
<div class="mb-8">
    <h2 class="text-[10px] font-black text-teal-600 uppercase tracking-[0.3em] mb-2 leading-none">Business Intelligence Overview</h2>
    <h1 class="text-3xl font-black text-gray-800 tracking-tighter leading-none italic">Laporan Eksekutif Pemilik</h1>
</div>

<!-- Financial Highlight Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 group">
    <!-- Today's Revenue -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition duration-300 border-b-4 border-teal-500 overflow-hidden relative">
        <i class="fas fa-coins absolute -right-4 -top-4 text-6xl opacity-5 text-teal-600"></i>
        <div class="mb-4">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-2">Omset Hari Ini</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo formatRupiah($income_today); ?></h4>
        </div>
        <div class="flex items-center text-[10px] font-bold text-teal-600">
            <i class="fas fa-circle-check mr-1 text-[8px]"></i> Real-time calculation
        </div>
    </div>
    
    <!-- This Month Revenue -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition duration-300 border-b-4 border-blue-500 overflow-hidden relative">
        <i class="fas fa-chart-line absolute -right-4 -top-4 text-6xl opacity-5 text-blue-600"></i>
        <div class="mb-4">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-2">Omset Bulan Ini</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo formatRupiah($income_month); ?></h4>
        </div>
        <div class="flex items-center text-[10px] font-bold text-blue-600">
            <i class="fas fa-calendar-alt mr-1 text-[8px]"></i> Periode <?php echo date('F Y'); ?>
        </div>
    </div>

    <!-- Monthly Throughput -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition duration-300 border-b-4 border-purple-500 overflow-hidden relative">
        <i class="fas fa-handshake absolute -right-4 -top-4 text-6xl opacity-5 text-purple-600"></i>
        <div class="mb-4">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-2">Total Layanan</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $trx_month; ?> <small class="text-xs text-gray-400">UNIT</small></h4>
        </div>
        <div class="flex items-center text-[10px] font-bold text-purple-600">
            <i class="fas fa-hashtag mr-1 text-[8px]"></i> Diselesaikan bulan ini
        </div>
    </div>

    <!-- Current Operations -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition duration-300 border-b-4 border-orange-500 overflow-hidden relative">
        <i class="fas fa-warehouse absolute -right-4 -top-4 text-6xl opacity-5 text-orange-600"></i>
        <div class="mb-4">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-2">Kendaraan Dalam Lot</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $current_parked; ?> <small class="text-xs text-gray-400">TERPARKIR</small></h4>
        </div>
        <div class="flex items-center text-[10px] font-bold text-orange-600">
            <i class="fas fa-triangle-exclamation mr-1 text-[8px]"></i> Potensi pendapatan aktif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Revenue Stream Monitoring -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
            <div class="flex items-center space-x-3">
                <div class="w-1.5 h-6 bg-teal-600 rounded-full shadow-sm shadow-teal-200"></div>
                <h3 class="font-black text-gray-700 tracking-tight italic">TRANSAKSI REVENUE TERBARU</h3>
            </div>
            <a href="laporan.php" class="text-[10px] font-black text-teal-600 hover:text-teal-700 uppercase tracking-[0.2em]">Buka Laporan Penuh <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
        <div class="p-0">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        <th class="px-8 py-4">Waktu Keluar</th>
                        <th class="px-8 py-4">Status Kendaraan</th>
                        <th class="px-8 py-4">Petugas</th>
                        <th class="px-8 py-4 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php
                    $q_recent_income = "SELECT t.*, k.no_polisi, k.jenis_kendaraan, u.username 
                                       FROM transaksi t 
                                       JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                                       JOIN users u ON t.id_petugas = u.id_user 
                                       WHERE t.status = 'keluar' 
                                       ORDER BY t.jam_keluar DESC LIMIT 5";
                    $res_income = mysqli_query($conn, $q_recent_income);
                    while($row = mysqli_fetch_assoc($res_income)):
                    ?>
                    <tr class="hover:bg-teal-50/20 transition group">
                        <td class="px-8 py-5">
                            <div class="text-xs font-black text-gray-700 leading-none mb-1"><?php echo date('H:i', strtotime($row['jam_keluar'])); ?></div>
                            <div class="text-[9px] text-gray-400 font-bold uppercase"><?php echo date('d M Y', strtotime($row['jam_keluar'])); ?></div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center space-x-3">
                                <span class="bg-gray-900 text-white px-2 py-0.5 rounded text-[10px] font-black tracking-widest border border-gray-700">
                                    <?php echo $row['no_polisi']; ?>
                                </span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest"><?php echo $row['jenis_kendaraan']; ?></span>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 rounded-full bg-teal-400"></div>
                                <span class="text-[10px] font-black text-gray-600 uppercase tracking-tighter"><?php echo $row['username']; ?></span>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-right font-black text-gray-800 text-sm italic tracking-tighter">
                            <?php echo formatRupiah($row['total_bayar']); ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($res_income) == 0): ?>
                        <tr><td colspan="4" class="p-12 text-center text-gray-300 italic font-medium">Belum ada pendapatan terekam hari ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Executive Greeting & Action -->
    <div class="space-y-8">
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl shadow-2xl p-8 text-white relative overflow-hidden h-full flex flex-col justify-center">
            <i class="fas fa-crown absolute -right-6 -bottom-6 text-[120px] opacity-10 rotate-12"></i>
            <div class="relative z-10">
                <span class="inline-block bg-teal-500/20 text-teal-400 text-[10px] font-black px-3 py-1 rounded-full border border-teal-500/20 uppercase tracking-widest mb-4">Owner Panel v2.0</span>
                <h2 class="text-2xl font-black italic tracking-tighter mb-4 leading-tight">SELAMAT DATANG,<br>DIRECTOR.</h2>
                <p class="text-gray-400 text-sm font-medium leading-relaxed mb-8">
                    Pantau kinerja bisnis Anda secara absolut. Seluruh data keuangan telah dienkripsi dan divalidasi secara real-time.
                </p>
                <div class="flex flex-col space-y-3">
                    <a href="laporan.php" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-black py-4 rounded-xl transition flex items-center justify-center space-x-2 uppercase text-xs tracking-tighter shadow-lg shadow-teal-900/40">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Audit Laporan Keuangan</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>
