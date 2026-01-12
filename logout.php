<?php
// Mulai sesi. Wajib dilakukan sebelum bisa menghancurkannya.
session_start();

// Kosongkan semua variabel sesi
$_SESSION = []; 

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Terakhir, hancurkan sesi
session_destroy();

// Arahkan kembali ke halaman login
header("Location: index.php");
exit;
?>