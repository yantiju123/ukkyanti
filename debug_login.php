<?php
require_once 'config/db.php';

$username = 'admin';
$password_plain = 'admin123';
$password_md5 = md5($password_plain);

echo "Checking login for user: $username\n";
echo "Password Plain: $password_plain\n";
echo "Password MD5 (PHP Generated): $password_md5\n";

$query = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo "User Found in DB!\n";
    echo "Role: " . $row['role'] . "\n";
    echo "Stored Password Hash: " . $row['password_md5'] . "\n";
    
    if ($row['password_md5'] === $password_md5) {
        echo "MATCH: Password hash matches!\n";
    } else {
        echo "MISMATCH: Password hash does NOT match!\n";
        echo "Length of Stored: " . strlen($row['password_md5']) . "\n";
        echo "Length of Generated: " . strlen($password_md5) . "\n";
    }
} else {
    echo "User NOT found in DB.\n";
}
?>
