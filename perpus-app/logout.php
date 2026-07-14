<?php
// logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua session data
$_SESSION = array();

// Hapus cookie session jika ada untuk keamanan penuh
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect kembali ke login.php
header("Location: login.php");
exit;
