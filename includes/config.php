<?php
// includes/config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'jp_annahl';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Helper untuk mencegah XSS
if (!function_exists('esc')) {
    function esc($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Helper untuk memformat mata uang Rupiah
if (!function_exists('format_rupiah')) {
    function format_rupiah($number) {
        return "Rp " . number_format((float)$number, 0, ',', '.');
    }
}

// Helper untuk validasi form input
if (!function_exists('old')) {
    function old($key, $default = '') {
        return isset($_POST[$key]) ? esc($_POST[$key]) : esc($default);
    }
}
