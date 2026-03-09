<?php
// pages/petugas/dashboard.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

cekLogin();
cekRole(['petugas', 'admin']);

$page_title = "Petugas Dashboard";

// Stats Summary
$today = date('Y-m-d');
$user_id = $_SESSION['user_id'];

// 1. Transaksi Saya Hari Ini
$q_my_trx = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE id_petugas = '$user_id' AND DATE(jam_masuk) = '$today'");
$my_trx = mysqli_fetch_assoc($q_my_trx)['total'] ?? 0;

// 2. Parkir Aktif (Global)
$q_active = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status = 'masuk'");
$active_park = mysqli_fetch_assoc($q_active)['total'] ?? 0;

// 3. Omset Saya (Opsional, jika ingin ditampilkan)
$q_omset = mysqli_query($conn, "SELECT SUM(total_bayar) as total FROM transaksi WHERE id_petugas = '$user_id' AND DATE(jam_keluar) = '$today'");
$my_omset = mysqli_fetch_assoc($q_omset)['total'] ?? 0;

include __DIR__ . '/../../includes/header.php';
?>

<!-- Statistics Summary Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 group">
    <!-- My Transactions -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-xl hover:-translate-y-1 transition duration-300 border-l-4 border-teal-500">
        <div class="p-4 bg-teal-50 rounded-xl text-teal-600">
            <i class="fas fa-clipboard-list text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Entri Saya Hari Ini</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $my_trx; ?></h4>
            <div class="text-[9px] text-teal-400 font-bold">Dokumentasi operasional</div>
        </div>
    </div>
    
    <!-- Active Parking -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-xl hover:-translate-y-1 transition duration-300 border-l-4 border-orange-500">
        <div class="p-4 bg-orange-50 rounded-xl text-orange-600">
            <i class="fas fa-car-side text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Total Parkir Aktif</p>
            <h4 class="text-2xl font-black text-gray-800 tracking-tighter"><?php echo $active_park; ?></h4>
            <div class="text-[9px] text-orange-400 font-bold">Kendaraan di dalam</div>
        </div>
    </div>

    <!-- Personal Revenue -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-xl hover:-translate-y-1 transition duration-300 border-l-4 border-blue-500">
        <div class="p-4 bg-blue-50 rounded-xl text-blue-600">
            <i class="fas fa-money-bill-transfer text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Pendapatan Sesi Ini</p>
            <h4 class="text-xl font-black text-gray-800 tracking-tighter"><?php echo formatRupiah($my_omset); ?></h4>
            <div class="text-[9px] text-blue-400 font-bold">Total selesai bayar</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    
    <!-- Quick Action Card -->
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden flex flex-col items-center justify-center p-10 text-center relative">
        <div class="bg-teal-500 absolute top-0 left-0 w-full h-2"></div>
        <div class="mb-6">
            <h3 class="text-xl font-black text-gray-800 tracking-tighter italic">KONTROL OPERASIONAL</h3>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Pilih aksi cepat untuk kendaraan</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-4 w-full max-w-md">
            <a href="entry.php" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white p-6 rounded-2xl transition duration-300 shadow-lg shadow-teal-200 group">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition">
                    <i class="fas fa-sign-in-alt text-xl"></i>
                </div>
                <div class="font-black text-sm uppercase tracking-widest">Masuk</div>
                <div class="text-[10px] font-bold text-teal-100 mt-1 opacity-70 italic">Entry Registration</div>
            </a>
            
            <a href="exit.php" class="flex-1 bg-red-500 hover:bg-red-600 text-white p-6 rounded-2xl transition duration-300 shadow-lg shadow-red-200 group">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </div>
                <div class="font-black text-sm uppercase tracking-widest">Keluar</div>
                <div class="text-[10px] font-bold text-red-100 mt-1 opacity-70 italic">Payment & Exit</div>
            </a>
        </div>
    </div>

    <!-- Occupancy Monitoring Area -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
        <div class="px-8 py-6 border-b border-gray-50 flex items-center space-x-3 bg-gray-50/50">
            <div class="w-2 h-6 bg-teal-600 rounded-full"></div>
            <h3 class="font-black text-gray-700 tracking-tight italic">LIVE OCCUPANCY</h3>
        </div>
        <div class="p-8 space-y-6">
            <?php
            $q_areas = mysqli_query($conn, "SELECT * FROM area_parkir");
            while($area = mysqli_fetch_assoc($q_areas)):
                $percent = ($area['kapasitas'] > 0) ? ($area['terisi']/$area['kapasitas'])*100 : 0;
                $color = $percent >= 90 ? 'bg-red-500' : ($percent >= 70 ? 'bg-orange-500' : 'bg-teal-500');
            ?>
            <div>
                <div class="flex justify-between items-end mb-2 px-1">
                    <div>
                        <span class="block text-xs font-black text-gray-700 uppercase tracking-widest"><?php echo $area['nama_area']; ?></span>
                        <span class="text-[10px] font-bold text-gray-400">ID: SECTION-<?php echo $area['id_area']; ?></span>
                    </div>
                    <div class="text-right">
                        <span class="block text-sm font-black text-gray-800 leading-none"><?php echo $area['terisi']; ?> / <?php echo $area['kapasitas']; ?></span>
                        <span class="text-[10px] font-black text-teal-600 uppercase tracking-tighter"><?php echo round($percent); ?>% Occupied</span>
                    </div>
                </div>
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden shadow-inner border border-gray-50">
                    <div class="<?php echo $color; ?> h-full rounded-full transition-all duration-1000 shadow-sm" style="width: <?php echo $percent; ?>%"></div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-3xl p-8 text-white relative overflow-hidden shadow-2xl">
    <div class="absolute right-[-20px] bottom-[-20px] opacity-10">
        <i class="fas fa-parking text-[150px]"></i>
    </div>
    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <h2 class="text-2xl font-black italic tracking-tighter mb-2">PANDUAN OPERASIONAL PETUGAS</h2>
            <p class="text-sm text-gray-400 font-medium max-w-xl leading-relaxed">
                Pastikan nomor polisi yang diinput sesuai dengan STNK. Gunakan fitur **Cetak Struk** setelah kendaraan terdaftar masuk untuk diberikan kepada pemilik kendaraan sebagai bukti pengambilan.
            </p>
        </div>
        <div class="flex items-center space-x-3 bg-white/10 px-6 py-4 rounded-2xl border border-white/10 backdrop-blur-sm">
            <div class="text-center">
                <div class="text-[10px] font-black text-teal-400 uppercase tracking-widest">Status Sesi</div>
                <div class="text-lg font-black tracking-tighter italic">AKTIF & AMAN</div>
            </div>
            <i class="fas fa-shield-check text-2xl text-teal-400"></i>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
