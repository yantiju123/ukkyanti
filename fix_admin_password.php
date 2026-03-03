<?php
require_once 'config/db.php';

echo "Fixing Admin Password...\n";

// 1. Delete existing admin
$query_del = "DELETE FROM users WHERE username = 'admin'";
if (mysqli_query($conn, $query_del)) {
    echo "Existing admin user deleted (if any).\n";
} else {
    echo "Error deleting admin: " . mysqli_error($conn) . "\n";
}

// 2. Insert new admin with MD5
$username = 'admin';
$password_plain = 'admin123'; 
$password_md5 = md5($password_plain);
$role = 'admin';

// Use prepared statement for safety, though hardcoded here
$stmt = mysqli_prepare($conn, "INSERT INTO users (username, password_md5, role) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sss", $username, $password_md5, $role);

if (mysqli_stmt_execute($stmt)) {
    echo "SUCCESS: Admin user created with MD5 hash.\n";
    echo "Username: $username\n";
    echo "Password: $password_plain\n";
    echo "Hash: $password_md5\n";
} else {
    echo "ERROR creating admin: " . mysqli_stmt_error($stmt) . "\n";
}

// Check Petugas and Owner too just in case
$users_to_fix = [
    ['petugas', 'petugas123', 'petugas'],
    ['owner', 'owner123', 'owner']
];

foreach ($users_to_fix as $u) {
    $u_name = $u[0];
    $u_pass = md5($u[1]);
    $u_role = $u[2];
    
    // Check if exists
    $check = mysqli_query($conn, "SELECT id_user FROM users WHERE username = '$u_name'");
    if (mysqli_num_rows($check) == 0) {
        $stmt2 = mysqli_prepare($conn, "INSERT INTO users (username, password_md5, role) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt2, "sss", $u_name, $u_pass, $u_role);
        mysqli_stmt_execute($stmt2);
        echo "Fixed missing user: $u_name\n";
    }
}

echo "Database fix completed.\n";
?>
