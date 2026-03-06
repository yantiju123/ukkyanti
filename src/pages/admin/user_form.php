<?php
// pages/admin/user_form.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['admin']);

$page_title = "Form User";
$error = '';
$is_edit = false;
$username = '';
$role = 'petugas';

if (isset($_GET['id'])) {
    $is_edit = true;
    $id = intval($_GET['id']);
    $query = "SELECT * FROM users WHERE id_user = $id";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $username = $row['username'];
        $role = $row['role'];
    } else {
        header("Location: users.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_input = bersihkanInput($_POST['username']);
    $role_input = bersihkanInput($_POST['role']);
    $password_input = $_POST['password'];

    if ($is_edit) {
        if (!empty($password_input)) {
            $pass_md5 = md5($password_input);
            $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, password_md5=?, role=? WHERE id_user=?");
            mysqli_stmt_bind_param($stmt, "sssi", $username_input, $pass_md5, $role_input, $id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, role=? WHERE id_user=?");
            mysqli_stmt_bind_param($stmt, "ssi", $username_input, $role_input, $id);
        }
        mysqli_stmt_execute($stmt);
        catatLog($conn, $_SESSION['user_id'], "Edit user: $username_input");
    } else {
        if (!empty($password_input)) {
            $pass_md5 = md5($password_input);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password_md5, role) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username_input, $pass_md5, $role_input);
            mysqli_stmt_execute($stmt);
            catatLog($conn, $_SESSION['user_id'], "Tambah user baru: $username_input");
        } else {
            $error = "Password wajib diisi untuk user baru!";
        }
    }

    if (!$error) {
        header("Location: users.php");
        exit;
    }
}

include '../../includes/header.php';
?>

<div class="card" style="max-width: 600px;">
    <h3><?php echo $is_edit ? 'Edit User' : 'Tambah User'; ?></h3>
    <br>
    
    <?php if ($error): ?>
        <div style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Password <?php echo $is_edit ? '(Kosongkan jika tidak diganti)' : ''; ?></label>
            <input type="password" name="password" class="form-control" <?php echo $is_edit ? '' : 'required'; ?>>
        </div>

        <div class="form-group">
            <label class="form-label">Role</label>
            <select name="role" class="form-control">
                <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="petugas" <?php echo $role == 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                <option value="owner" <?php echo $role == 'owner' ? 'selected' : ''; ?>>Owner</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="users.php" class="btn" style="background: #e2e8f0; color: #333;">Batal</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
