<!-- includes/sidebar.php -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>E-Parking</h3>
        <small>SMK UKK 2026</small>
    </div>
    <ul class="sidebar-menu">
        <!-- Menu Admin -->
        <?php if ($_SESSION['role'] == 'admin'): ?>
            <li><a href="../admin/dashboard.php">Dashboard</a></li>
            <li><a href="../admin/users.php">Kelola User</a></li>
            <li><a href="../admin/tarif.php">Kelola Tarif</a></li>
            <li><a href="../admin/area.php">Area Parkir</a></li>
            <li><a href="../admin/logs.php">Log Aktivitas</a></li>
        
        <!-- Menu Petugas -->
        <?php elseif ($_SESSION['role'] == 'petugas'): ?>
            <li><a href="../petugas/dashboard.php">Dashboard</a></li>
            <li><a href="../petugas/entry.php">Kendaraan Masuk</a></li>
            <li><a href="../petugas/exit.php">Kendaraan Keluar</a></li>
        
        <!-- Menu Owner -->
        <?php elseif ($_SESSION['role'] == 'owner'): ?>
            <li><a href="../owner/dashboard.php">Dashboard</a></li>
            <li><a href="../owner/laporan.php">Laporan</a></li>
        <?php endif; ?>
        
        <li><a href="../../logout.php" style="color: #ef4444;">Logout</a></li>
    </ul>
</div>
