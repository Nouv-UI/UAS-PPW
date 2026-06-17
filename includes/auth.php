<?php
// includes/auth.php
require_once __DIR__ . '/config.php';

if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('get_logged_in_user')) {
    function get_logged_in_user() {
        global $pdo;
        if (!is_logged_in()) {
            return null;
        }
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
}

if (!function_exists('require_login')) {
    function require_login() {
        if (!is_logged_in()) {
            $_SESSION['flash_message'] = ['type' => 'warning', 'text' => 'Silakan login terlebih dahulu.'];
            header("Location: /jp-annahls/pages/login.php");
            exit();
        }
    }
}

if (!function_exists('require_admin')) {
    function require_admin() {
        require_login();
        $user = get_logged_in_user();
        if (!$user || $user['role'] !== 'admin') {
            $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Anda tidak memiliki hak akses ke halaman ini.'];
            header("Location: /jp-annahls/index.php");
            exit();
        }
    }
}
