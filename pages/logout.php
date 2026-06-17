<?php
// pages/logout.php
require_once __DIR__ . '/../includes/config.php';

// Hancurkan semua data sesi
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Mulai sesi baru hanya untuk menyimpan pesan logout sukses
session_start();
$_SESSION['flash_message'] = [
    'type' => 'success',
    'text' => 'Anda telah berhasil keluar dari akun.'
];

header("Location: /jp-annahls/index.php");
exit();
