<?php
// pages/admin/users.php
require_once '../../config/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

cekLogin();
cekRole(['admin']);

$page_title = "Pengaturan Pengguna";
$error = '';
$success = '';

// Default values for form
$is_edit = false;
$edit_id = '';
$edit_username = '';
$edit_role = 'petugas';

// Handle Delete
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if ($id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id_user = $id");
        catatLog($conn, $_SESSION['user_id'], "Menghapus user ID: $id");
        header("Location: users.php?msg=deleted");
        exit;
    } else {
        $error = "Tidak bisa menghapus akun sendiri!";
    }
}

// Handle Form Submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_input = bersihkanInput($_POST['username']);
    $role_input = bersihkanInput($_POST['role']);
    $password_input = $_POST['password'];
    $id_input = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
    
    // Check duplication if new user or changing username
    $check_query = "SELECT id_user FROM users WHERE username = '$username_input' AND id_user != $id_input";
    $check_res = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_res) > 0) {
        $error = "Username '$username_input' sudah digunakan!";
    } else {
        if ($id_input > 0) {
            // Edit
            if (!empty($password_input)) {
                $pass_md5 = md5($password_input);
                $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, password_md5=?, role=? WHERE id_user=?");
                mysqli_stmt_bind_param($stmt, "sssi", $username_input, $pass_md5, $role_input, $id_input);
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, role=? WHERE id_user=?");
                mysqli_stmt_bind_param($stmt, "ssi", $username_input, $role_input, $id_input);
            }
            if (mysqli_stmt_execute($stmt)) {
                catatLog($conn, $_SESSION['user_id'], "Edit user: $username_input");
                header("Location: users.php?msg=updated");
                exit;
            } else {
                $error = "Gagal mengupdate user.";
            }
        } else {
            // Add
            if (!empty($password_input)) {
                $pass_md5 = md5($password_input);
                $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password_md5, role) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sss", $username_input, $pass_md5, $role_input);
                if (mysqli_stmt_execute($stmt)) {
                    catatLog($conn, $_SESSION['user_id'], "Tambah user baru: $username_input");
                    header("Location: users.php?msg=added");
                    exit;
                } else {
                    $error = "Gagal menambah user.";
                }
            } else {
                $error = "Password wajib diisi untuk user baru!";
            }
        }
    }
}

// Handle Edit Selection (Populate Form)
if (isset($_GET['edit'])) {
    $is_edit = true;
    $edit_id = intval($_GET['edit']);
    $query_edit = "SELECT * FROM users WHERE id_user = $edit_id";
    $res_edit = mysqli_query($conn, $query_edit);
    if ($row_edit = mysqli_fetch_assoc($res_edit)) {
        $edit_username = $row_edit['username'];
        $edit_role = $row_edit['role'];
    }
}

include '../../includes/header.php';
?>

<div class="flex flex-col lg:flex-row gap-6">
    
    <!-- Kolom Kiri: Tabel User -->
    <div class="w-full lg:w-2/3 bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-700">Daftar Pengguna</h3>
            <?php if(isset($_GET['msg'])): ?>
                <span class="text-green-500 text-sm font-semibold">
                    <?php 
                    if($_GET['msg']=='added') echo "User berhasil ditambahkan!";
                    if($_GET['msg']=='updated') echo "User berhasil diupdate!";
                    if($_GET['msg']=='deleted') echo "User berhasil dihapus!";
                    ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-600 uppercase text-xs tracking-wider">
                        <th class="p-3 border-b">No</th>
                        <th class="p-3 border-b">Nama</th>
                        <th class="p-3 border-b">Username</th>
                        <th class="p-3 border-b">Role</th>
                        <th class="p-3 border-b">Status</th>
                        <th class="p-3 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    <?php
                    $query = "SELECT * FROM users ORDER BY id_user DESC";
                    $result = mysqli_query($conn, $query);
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr class="hover:bg-gray-50 border-b last:border-0 transition">
                        <td class="p-3"><?php echo $no++; ?></td>
                        <td class="p-3 font-semibold"><?php echo ucfirst($row['username']); ?></td> <!-- Placeholder Nama -->
                        <td class="p-3 text-gray-500"><?php echo $row['username']; ?></td>
                        <td class="p-3">
                            <?php 
                            $roleColor = 'bg-gray-200 text-gray-700';
                            if($row['role'] == 'admin') $roleColor = 'bg-purple-100 text-purple-700';
                            if($row['role'] == 'petugas') $roleColor = 'bg-blue-100 text-blue-700';
                            if($row['role'] == 'owner') $roleColor = 'bg-orange-100 text-orange-700';
                            ?>
                            <span class="px-2 py-1 rounded text-xs font-bold <?php echo $roleColor; ?>">
                                <?php echo ucfirst($row['role']); ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700">Available</span>
                        </td>
                        <td class="p-3 flex space-x-2">
                            <a href="users.php?edit=<?php echo $row['id_user']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($row['id_user'] != $_SESSION['user_id']): ?>
                                <a href="users.php?hapus=<?php echo $row['id_user']; ?>" class="text-red-500 hover:text-red-700" title="Hapus" onclick="return confirm('Yakin hapus user ini?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kolom Kanan: Form input -->
    <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
            <h3 class="text-xl font-bold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                <?php echo $is_edit ? 'Edit User' : 'Tambah User'; ?>
            </h3>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="users.php" class="space-y-4">
                <input type="hidden" name="id_user" value="<?php echo $edit_id; ?>">
                
                <div>
                    <label class="block text-gray-600 text-sm font-semibold mb-2">Username</label>
                    <input type="text" name="username" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition" value="<?php echo htmlspecialchars($edit_username); ?>" required placeholder="Masukkan username">
                </div>

                <div>
                    <label class="block text-gray-600 text-sm font-semibold mb-2">
                        Password 
                        <?php if($is_edit): ?>
                            <span class="text-xs text-gray-400 font-normal">(Biarkan kosong jika tidak diubah)</span>
                        <?php endif; ?>
                    </label>
                    <input type="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition" <?php echo $is_edit ? '' : 'required'; ?> placeholder="********">
                </div>

                <div>
                    <label class="block text-gray-600 text-sm font-semibold mb-2">Role Akses</label>
                    <div class="relative">
                        <select name="role" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 transition appearance-none bg-white">
                            <option value="admin" <?php echo $edit_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="petugas" <?php echo $edit_role == 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                            <option value="owner" <?php echo $edit_role == 'owner' ? 'selected' : ''; ?>>Owner</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex space-x-2">
                    <button type="submit" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded transition">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                    <?php if($is_edit): ?>
                        <a href="users.php" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-center transition">
                            Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>
