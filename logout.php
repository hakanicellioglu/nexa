<?php
/**
 * logout.php
 *
 * Kullanıcı oturumunu sonlandıran dosya.
 * Oturum verilerini temizler ve giriş sayfasına yönlendirir.
 */

session_start();

// Tüm oturum verilerini temizle
$_SESSION = [];

// Oturum çerezini sil
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Oturumu sonlandır
session_destroy();

// Giriş sayfasına yönlendir
header('Location: login.php');
exit;
