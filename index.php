<?php
// index.php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: pages/" . $_SESSION['role'] . "/dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = bersihkanInput($_POST['username']);
    $password = md5($_POST['password']); // Using MD5 as requested

    $query = "SELECT * FROM users WHERE username = ? AND password_md5 = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Set Session
        $_SESSION['user_id'] = $row['id_user'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        // Log Login
        catatLog($conn, $row['id_user'], "Login ke sistem");

        // Redirect based on role
        header("Location: pages/" . $row['role'] . "/dashboard.php?msg=login_success");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Parking</title>
    <link rel="stylesheet" href="/assets/style.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="login-body">
    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: '<?php echo $error; ?>',
            confirmButtonColor: '#ef4444' // red-500
        });
    </script>
    <?php endif; ?>

    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'logout'): ?>
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Logout berhasil!',
            text: 'Anda tetap aman bersama E-Parking.',
            confirmButtonColor: '#0d9488' // teal-600
        });
        // cleanup url
        window.history.replaceState({}, document.title, window.location.pathname);
    </script>
    <?php endif; ?>

    <div class="login-card">
        <h2 style="text-align: center; margin-bottom: 20px; color: var(--primary);">E-Parking System</h2>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">LOGIN</button>
        </form>
        

    </div>
</body>
</html>
