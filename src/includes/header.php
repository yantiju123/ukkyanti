<!-- includes/header.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Parking System</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Custom Scrollbar if needed, otherwise Tailwind checks out */
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'login_success'): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Masuk!',
            text: 'Halo <?php echo $_SESSION["username"]; ?>, selamat datang di dashboard E-Parking.',
            showConfirmButton: true,
            confirmButtonColor: '#0d9488', // teal-600
            timer: 3000,
            timerProgressBar: true
        });
        // Remove msg from URL without reloading
        window.history.replaceState({}, document.title, window.location.pathname);
    </script>
    <?php endif; ?>

    <!-- Navbar Horizontal Hijau Toska -->
    <nav class="bg-teal-600 text-white shadow-xl sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                
                <!-- Logo Aplikasi -->
                <a href="../admin/dashboard.php" class="flex items-center space-x-3 group cursor-pointer">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center transform group-hover:rotate-12 transition duration-300">
                        <i class="fas fa-parking text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tighter leading-none">E-PARKIR</h1>
                        <span class="text-[10px] text-teal-200 font-bold uppercase tracking-widest">Smart System 2026</span>
                    </div>
                </a>

                <!-- Menu Navigasi Horizontal -->
                <div class="hidden lg:flex items-center space-x-1">
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a href="../admin/dashboard.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition font-bold text-sm flex items-center space-x-2 <?php echo strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'bg-white/20' : ''; ?>">
                            <i class="fas fa-chart-line opacity-70"></i> <span>Dashboard</span>
                        </a>
                        <a href="../admin/users.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition font-bold text-sm flex items-center space-x-2 <?php echo strpos($_SERVER['PHP_SELF'], 'users.php') !== false ? 'bg-white/20' : ''; ?>">
                            <i class="fas fa-users opacity-70"></i> <span>User</span>
                        </a>
                        <a href="../admin/area.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition font-bold text-sm flex items-center space-x-2 <?php echo strpos($_SERVER['PHP_SELF'], 'area.php') !== false ? 'bg-white/20' : ''; ?>">
                            <i class="fas fa-map-location-dot opacity-70"></i> <span>Area</span>
                        </a>
                        <a href="../admin/tarif.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition font-bold text-sm flex items-center space-x-2 <?php echo strpos($_SERVER['PHP_SELF'], 'tarif.php') !== false ? 'bg-white/20' : ''; ?>">
                            <i class="fas fa-money-bill-wave opacity-70"></i> <span>Tarif</span>
                        </a>
                        <a href="../admin/logs.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition font-bold text-sm flex items-center space-x-2 <?php echo strpos($_SERVER['PHP_SELF'], 'logs.php') !== false ? 'bg-white/20' : ''; ?>">
                            <i class="fas fa-receipt opacity-70"></i> <span>Log</span>
                        </a>
                        <a href="../admin/kendaraan.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition font-bold text-sm flex items-center space-x-2 <?php echo strpos($_SERVER['PHP_SELF'], 'kendaraan.php') !== false ? 'bg-white/20' : ''; ?>">
                            <i class="fas fa-car opacity-70"></i> <span>Kendaraan</span>
                        </a>
                    <?php elseif ($_SESSION['role'] == 'petugas'): ?>
                        <a href="../petugas/dashboard.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition font-bold text-sm">Dashboard</a>
                        <a href="../petugas/entry.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition font-bold text-sm">Masuk</a>
                        <a href="../petugas/exit.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition font-bold text-sm">Keluar</a>
                    <?php endif; ?>
                </div>

                <!-- Profil User & Avatar icon -->
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-3 bg-white/10 py-1.5 pl-4 pr-1.5 rounded-full border border-white/10">
                        <div class="text-right hidden sm:block">
                            <div class="text-xs font-black uppercase tracking-tighter text-white"><?php echo $_SESSION['username']; ?></div>
                            <div class="text-[9px] font-bold text-teal-200"><?php echo strtoupper($_SESSION['role']); ?></div>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-teal-500 border-2 border-white/20 flex items-center justify-center text-white shadow-inner overflow-hidden">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                    </div>
                    
                    <a href="../../logout.php" class="w-10 h-10 rounded-xl bg-red-500 hover:bg-red-600 flex items-center justify-center transition shadow-lg shadow-red-900/20 group" title="Logout">
                        <i class="fas fa-power-off text-sm group-hover:scale-110 transition"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container mx-auto px-4 py-8 flex-grow">
        <!-- Page Title -->
        <div class="mb-6 flex justify-between items-end border-b border-gray-300 pb-2">
            <h2 class="text-2xl font-bold text-gray-700">
                <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?>
            </h2>
            <span class="text-sm text-gray-500"><?php echo date('d F Y'); ?></span>
        </div>
