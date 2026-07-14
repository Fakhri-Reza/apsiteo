<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_admin'])) {
    // Tentukan path ke login.php secara dinamis.
    // Jika login.php ada di direktori kerja saat ini, gunakan login.php.
    // Jika tidak (berada di dalam subfolder), naik satu tingkat ke ../login.php.
    $login_path = 'login.php';
    if (!file_exists($login_path)) {
        $login_path = '../login.php';
    }
    header("Location: " . $login_path);
    exit;
}

// Tentukan BASE URL/Path secara dinamis agar link di sidebar/header konsisten.
// Jika kita berada di root perpus-app, $base_path = ''
// Jika kita berada di subfolder (seperti buku/ atau anggota/), $base_path = '../'
$base_path = '';
if (!file_exists('login.php')) {
    $base_path = '../';
}

