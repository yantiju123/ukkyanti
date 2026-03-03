<?php
// includes/auth.php
session_start();

// Function to check if user is logged in
function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php?pesan=belum_login");
        exit;
    }
}

// Function to check specific role
function cekRole($allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        // Redirect to their dashboard if logged in but wrong role
        if (isset($_SESSION['role'])) {
            header("Location: ../pages/" . $_SESSION['role'] . "/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    }
}

// Simple CSRF Token (Optional for UKK but good practice)
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
?>
