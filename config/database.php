<?php 
$host = "localhost";
$user = "root";     // Default user XAMPP
$pass = "";         // Default password XAMPP kosong
$db   = "db_simklinik";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Gagal terhubung ke database: " . mysqli_connect_error());
}
?>