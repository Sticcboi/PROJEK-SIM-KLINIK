# SIM-KLINIK (Sistem Informasi Manajemen Klinik)

**Tugas UTS Pemrograman Web Service*
* **NIM:** [23.01.53.0017]
* **Nama:** [Firman Andre Setiawan]
  
## Deskripsi Aplikasi
Aplikasi berbasis web untuk mengelola operasional klinik pratama, mulai dari pendaftaran pasien, rekam medis elektronik, farmasi, hingga kasir. Aplikasi ini mengolah **13 tabel database** secara aktif.

## Teknologi yang Digunakan
* **Backend:** PHP Native dengan arsitektur REST API (menggunakan library `php-crud-api`).
* **Frontend:** HTML5, Bootstrap 5, JavaScript (Fetch API).
* **Database:** MySQL.

## Fitur Utama
1.  **Pendaftaran:** Manajemen antrian pasien harian.
2.  **Pemeriksaan Dokter:** Input diagnosa dan tindakan medis.
3.  **E-Resep & Farmasi:** Dokter input resep, apoteker memprosesnya.
4.  **Kasir:** Pembayaran terintegrasi (Tindakan + Obat).
5.  **Master Data Lengkap:** Pengelolaan data Pasien, Dokter, Obat, Layanan, Poli, dan User.

## Cara Instalasi
1.  Download source code ini.
2.  Import file database `db_simklinik.sql` ke phpMyAdmin.
3.  Atur koneksi database di file `api.php` (bagian paling bawah) dan `config/database.php` (jika masih ada sisa file lama yang membutuhkannya).
4.  Jalankan di local server (XAMPP/Laragon).
5.  Login default:
    * Username: `admin`
    * Password: `admin123`
