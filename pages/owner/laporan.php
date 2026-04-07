<?php
// pages/owner/laporan.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['owner', 'admin']);

$page_title = "Laporan Transaksi";

$where = "WHERE t.status = 'keluar'";
$filter_type = $_GET['filter_type'] ?? 'date';
$start_date = date('Y-m-01');
$end_date = date('Y-m-d');
$month_val = date('Y-m');
$year_val = date('Y');

if ($filter_type == 'date') {
    $start_date = $_GET['start'] ?? date('Y-m-01');
    $end_date = $_GET['end'] ?? date('Y-m-d');
    $where .= " AND DATE(t.jam_masuk) BETWEEN '$start_date' AND '$end_date'";
} elseif ($filter_type == 'month') {
    $month_val = $_GET['month_val'] ?? date('Y-m');
    $where .= " AND DATE_FORMAT(t.jam_masuk, '%Y-%m') = '$month_val'";
    $start_date = $month_val . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
} elseif ($filter_type == 'year') {
    $year_val = $_GET['year_val'] ?? date('Y');
    $where .= " AND YEAR(t.jam_masuk) = '$year_val'";
    $start_date = $year_val . '-01-01';
    $end_date = $year_val . '-12-31';
}

include '../../includes/header.php';
?>

<!-- Summary Stats -->
<?php
// Pre-calculate stats for the summary cards
$q_stats = "SELECT 
                COUNT(*) as total_kendaraan, 
                SUM(total_bayar) as total_omset,
                AVG(total_bayar) as avg_bayar
            FROM transaksi t 
            $where";
$res_stats = mysqli_query($conn, $q_stats);
$stats = mysqli_fetch_assoc($res_stats);

// Count days for average/day calculation
$date1 = new DateTime($start_date);
$date2 = new DateTime($end_date);
$interval = $date1->diff($date2);
$days = $interval->days + 1;
$avg_per_day = $stats['total_omset'] / $days;
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all border-l-4 border-teal-500">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Pendapatan</span>
            <div class="p-2 bg-teal-50 rounded-lg text-teal-600">
                <i class="fas fa-wallet text-sm"></i>
            </div>
        </div>
        <h3 class="text-xl font-black text-gray-800 tracking-tighter"><?php echo formatRupiah($stats['total_omset'] ?? 0); ?></h3>
        <p class="text-[9px] text-teal-500 font-bold mt-1 italic">Periode Terpilih</p>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all border-l-4 border-blue-500">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Volume Kendaraan</span>
            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                <i class="fas fa-car text-sm"></i>
            </div>
        </div>
        <h3 class="text-xl font-black text-gray-800 tracking-tighter"><?php echo number_format($stats['total_kendaraan']); ?></h3>
        <p class="text-[9px] text-blue-500 font-bold mt-1 italic">Unit Terlayani</p>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all border-l-4 border-orange-500">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Rata-rata/Hari</span>
            <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                <i class="fas fa-chart-line text-sm"></i>
            </div>
        </div>
        <h3 class="text-xl font-black text-gray-800 tracking-tighter"><?php echo formatRupiah($avg_per_day); ?></h3>
        <p class="text-[9px] text-orange-500 font-bold mt-1 italic">Efisiensi Harian</p>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-all border-l-4 border-purple-500">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Rata-rata Transaksi</span>
            <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                <i class="fas fa-receipt text-sm"></i>
            </div>
        </div>
        <h3 class="text-xl font-black text-gray-800 tracking-tighter"><?php echo formatRupiah($stats['avg_bayar'] ?? 0); ?></h3>
        <p class="text-[9px] text-purple-500 font-bold mt-1 italic">Per Tiket</p>
    </div>
</div>

<!-- Filter Section -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 mb-8 space-y-6">
    <div class="flex items-center justify-between border-b pb-4">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gray-900 rounded-2xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-filter text-xl"></i>
            </div>
            <div>
                <h3 class="font-black text-gray-800 tracking-tight italic uppercase">Filter Laporan</h3>
                <p class="text-[10px] text-gray-400 font-bold tracking-widest">Pilih salah satu metode penarikan data (3 layer)</p>
            </div>
        </div>
        <button type="button" onclick="printReport()" class="bg-white border-2 border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white px-6 py-2 rounded-xl font-black text-[11px] uppercase tracking-widest transition-all flex items-center space-x-2 h-[42px] shadow-sm">
            <i class="fas fa-print"></i> <span>Cetak</span>
        </button>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Layer 1: Filter Tanggal -->
        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 relative overflow-hidden <?php echo ($filter_type == 'date') ? 'ring-2 ring-teal-500' : ''; ?>">
            <?php if ($filter_type == 'date'): ?>
                <div class="absolute top-0 right-0 bg-teal-500 text-white text-[8px] font-black uppercase px-2 py-1 rounded-bl-lg">Aktif</div>
            <?php endif; ?>
            <form method="GET" class="flex flex-col gap-3 h-full">
                <input type="hidden" name="filter_type" value="date">
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-calendar-day text-teal-600"></i>
                    <h4 class="font-bold text-sm tracking-tight text-gray-700">Layer Hari / Tanggal</h4>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Dari Tanggal</label>
                    <input type="date" name="start" value="<?php echo ($filter_type == 'date') ? $start_date : date('Y-m-01'); ?>" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-teal-500 outline-none transition cursor-pointer">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Sampai Tanggal</label>
                    <input type="date" name="end" value="<?php echo ($filter_type == 'date') ? $end_date : date('Y-m-d'); ?>" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-teal-500 outline-none transition cursor-pointer">
                </div>
                <div class="mt-auto pt-2">
                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white px-4 py-2.5 rounded-xl font-black text-[11px] uppercase tracking-widest transition-all flex items-center justify-center space-x-2"><i class="fas fa-sync-alt"></i> <span>Terapkan</span></button>
                </div>
            </form>
        </div>

        <!-- Layer 2: Filter Bulan -->
        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 relative overflow-hidden <?php echo ($filter_type == 'month') ? 'ring-2 ring-blue-500' : ''; ?>">
            <?php if ($filter_type == 'month'): ?>
                <div class="absolute top-0 right-0 bg-blue-500 text-white text-[8px] font-black uppercase px-2 py-1 rounded-bl-lg">Aktif</div>
            <?php endif; ?>
            <form method="GET" class="flex flex-col gap-3 h-full">
                <input type="hidden" name="filter_type" value="month">
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-calendar-week text-blue-600"></i>
                    <h4 class="font-bold text-sm tracking-tight text-gray-700">Layer Bulan</h4>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Pilih Bulan & Tahun</label>
                    <input type="month" name="month_val" value="<?php echo $month_val; ?>" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 outline-none transition cursor-pointer">
                </div>
                <div class="mt-auto pt-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-black text-[11px] uppercase tracking-widest transition-all flex items-center justify-center space-x-2"><i class="fas fa-sync-alt"></i> <span>Terapkan</span></button>
                </div>
            </form>
        </div>

        <!-- Layer 3: Filter Tahun -->
        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 relative overflow-hidden <?php echo ($filter_type == 'year') ? 'ring-2 ring-purple-500' : ''; ?>">
            <?php if ($filter_type == 'year'): ?>
                <div class="absolute top-0 right-0 bg-purple-500 text-white text-[8px] font-black uppercase px-2 py-1 rounded-bl-lg">Aktif</div>
            <?php endif; ?>
            <form method="GET" class="flex flex-col gap-3 h-full">
                <input type="hidden" name="filter_type" value="year">
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-calendar text-purple-600"></i>
                    <h4 class="font-bold text-sm tracking-tight text-gray-700">Layer Tahun</h4>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-1">Pilih Tahun</label>
                    <select name="year_val" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-purple-500 outline-none transition cursor-pointer">
                        <?php 
                        $current_year = date('Y') + 1; // Future-proof
                        for ($y = $current_year; $y >= 2020; $y--) {
                            $sel = ($y == $year_val) ? 'selected' : '';
                            echo "<option value='$y' $sel>$y</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mt-auto pt-2">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2.5 rounded-xl font-black text-[11px] uppercase tracking-widest transition-all flex items-center justify-center space-x-2"><i class="fas fa-sync-alt"></i> <span>Terapkan</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden" id="report-area">
    <div class="print-header hidden p-8 border-b-2 border-gray-900 bg-gray-50 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black tracking-tighter text-gray-900 italic">LAPORAN KEUANGAN PARKIR</h1>
                <p class="text-sm font-bold text-gray-500">Sistem E-Parking UKK 2026</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Periode Laporan</p>
                <p class="text-lg font-black text-teal-600 italic"><?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?></p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">No</th>
                    <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Waktu Transaksi</th>
                    <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Detail Kendaraan</th>
                    <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Petugas</th>
                    <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Biaya Parkir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php
                $query = "SELECT t.*, k.no_polisi, k.jenis_kendaraan, u.username as petugas
                          FROM transaksi t
                          JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan
                          JOIN users u ON t.id_petugas = u.id_user
                          $where
                          ORDER BY t.jam_masuk DESC";
                $result = mysqli_query($conn, $query);
                $no = 1;
                $total_omset = 0;
                if(mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)):
                        $total_omset += $row['total_bayar'];
                ?>
                <tr class="hover:bg-gray-50/50 transition duration-150">
                    <td class="px-8 py-4 text-xs font-black text-gray-300"><?php echo str_pad($no++, 2, "0", STR_PAD_LEFT); ?></td>
                    <td class="px-6 py-4">
                        <span class="block text-xs font-bold text-gray-800 tracking-tight"><?php echo date('d M Y', strtotime($row['jam_masuk'])); ?></span>
                        <span class="text-[10px] font-bold text-gray-400 italic"><?php echo date('H:i', strtotime($row['jam_masuk'])); ?> - <?php echo date('H:i', strtotime($row['jam_keluar'])); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-block bg-gray-900 text-white px-2 py-0.5 rounded text-[10px] font-black tracking-widest uppercase mb-1"><?php echo $row['no_polisi']; ?></span>
                        <span class="block text-[10px] font-bold text-teal-600 uppercase tracking-tighter"><?php echo $row['jenis_kendaraan']; ?></span>
                    </td>
                    <td class="px-6 py-4 text-xs font-black text-gray-600 italic"><?php echo strtoupper($row['petugas']); ?></td>
                    <td class="px-8 py-4 text-right">
                        <span class="text-sm font-black text-gray-800 tracking-tighter"><?php echo formatRupiah($row['total_bayar']); ?></span>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" class="px-8 py-12 text-center text-gray-300 italic font-black text-sm">
                        <i class="fas fa-folder-open text-4xl mb-4 block opacity-20"></i>
                        Tidak ada data transaksi pada periode ini
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="bg-gray-900 text-white">
                <tr>
                    <td colspan="4" class="px-8 py-6 text-right text-[10px] font-black uppercase tracking-[0.2em] italic opacity-70">JUMLAH TOTAL PENDAPATAN</td>
                    <td class="px-8 py-6 text-right">
                        <span class="text-xl font-black tracking-tighter italic text-teal-400"><?php echo formatRupiah($total_omset); ?></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
function printReport() {
    var printContent = document.getElementById('report-area').outerHTML;
    var win = window.open('', '', 'height=800,width=1000');
    
    win.document.write('<html><head><title>Laporan Keuangan E-Parking</title>');
    win.document.write('<script src="https://cdn.tailwindcss.com"><\/script>');
    win.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">');
    win.document.write('<style>@media print { .print-header { display: block !important; } .bg-gray-900 { background-color: #111827 !important; -webkit-print-color-adjust: exact; } .text-teal-400 { color: #2dd4bf !important; -webkit-print-color-adjust: exact; } }</style>');
    win.document.write('</head><body class="bg-white p-10">');
    win.document.write(printContent);
    win.document.write('</body></html>');
    
    setTimeout(function() {
        win.print();
        win.close();
    }, 1000);
}
</script>

<?php include '../../includes/footer.php'; ?>
