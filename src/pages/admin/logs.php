<?php
// pages/admin/logs.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['admin']);

$page_title = "Log Aktivitas Sistem";

// Statistics for Logs
$today = date('Y-m-d');
$logs_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM log_aktivitas WHERE DATE(waktu) = '$today'"))['total'];
$most_active_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT u.username FROM log_aktivitas l JOIN users u ON l.id_user = u.id_user GROUP BY l.id_user ORDER BY COUNT(*) DESC LIMIT 1"))['username'] ?? '-';
$total_logs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM log_aktivitas"))['total'];

include '../../includes/header.php';
?>

<!-- Activity Stats Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition border-l-4 border-teal-500">
        <div class="p-4 bg-teal-50 rounded-xl text-teal-600">
            <i class="fas fa-calendar-day text-2xl"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Aktivitas Hari Ini</p>
            <h4 class="text-2xl font-black text-gray-800"><?php echo $logs_today; ?></h4>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition border-l-4 border-blue-500">
        <div class="p-4 bg-blue-50 rounded-xl text-blue-600">
            <i class="fas fa-user-gear text-2xl"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">User Teraktif</p>
            <h4 class="text-2xl font-black text-gray-800"><?php echo ucfirst($most_active_user); ?></h4>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center space-x-4 hover:shadow-md transition border-l-4 border-purple-500">
        <div class="p-4 bg-purple-50 rounded-xl text-purple-600">
            <i class="fas fa-history text-2xl"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Jejak Audit</p>
            <h4 class="text-2xl font-black text-gray-800"><?php echo $total_logs; ?></h4>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Table Header -->
    <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <div class="w-2 h-6 bg-teal-500 rounded-full"></div>
            <h3 class="text-lg font-bold text-gray-700">Audit Trail / Log Aktivitas</h3>
        </div>
        <div class="text-xs text-gray-400 font-bold uppercase tracking-widest italic">
            Menampilkan 100 aktivitas terbaru
        </div>
    </div>

    <div class="overflow-x-auto p-6">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-400 text-xs font-bold uppercase tracking-widest border-b border-gray-100">
                    <th class="pb-4 px-2">Waktu</th>
                    <th class="pb-4">Pengguna</th>
                    <th class="pb-4">Kegiatan / Aktivitas</th>
                    <th class="pb-4 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php
                $query = "SELECT l.*, u.username, u.role 
                          FROM log_aktivitas l 
                          JOIN users u ON l.id_user = u.id_user 
                          ORDER BY l.waktu DESC LIMIT 100";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)):
                ?>
                <tr class="group hover:bg-teal-50/30 transition-colors duration-200">
                    <td class="py-4 px-2">
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-gray-700"><?php echo date('H:i:s', strtotime($row['waktu'])); ?></span>
                            <span class="text-[10px] text-gray-400 font-bold uppercase"><?php echo date('d M Y', strtotime($row['waktu'])); ?></span>
                        </div>
                    </td>
                    <td class="py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 border border-teal-100 shadow-sm font-black text-xs uppercase">
                                <?php echo substr($row['username'], 0, 1); ?>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-700"><?php echo htmlspecialchars($row['username']); ?></div>
                                <?php 
                                    $roleClass = 'bg-gray-100 text-gray-400';
                                    if($row['role'] == 'admin') $roleClass = 'bg-purple-100 text-purple-600';
                                    if($row['role'] == 'petugas') $roleClass = 'bg-blue-100 text-blue-600';
                                ?>
                                <span class="text-[9px] font-black uppercase tracking-tighter px-1.5 py-0.5 rounded <?php echo $roleClass; ?>">
                                    <?php echo $row['role']; ?>
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4">
                        <div class="text-sm text-gray-600 font-medium">
                            <i class="fas fa-circle-dot text-[8px] text-teal-400 mr-2 opacity-50"></i>
                            <?php echo htmlspecialchars($row['aktivitas']); ?>
                        </div>
                    </td>
                    <td class="py-4 text-right">
                        <span class="px-3 py-1 bg-green-50 text-green-600 rounded-full text-[10px] font-black uppercase border border-green-100 shadow-sm">
                            <i class="fas fa-check-double mr-1"></i> Success
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="4" class="p-12 text-center">
                        <div class="flex flex-col items-center opacity-30">
                            <i class="fas fa-folder-open text-5xl mb-4"></i>
                            <p class="font-bold italic">Belum ada jejak audit tersimpan.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
