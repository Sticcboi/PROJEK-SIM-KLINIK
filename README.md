# SIM-KLINIK
## Sistem Informasi Manajemen Klinik

**Tugas UTS Pemrograman Web Service**

- **NIM:** 23.01.53.0017
- **Nama:** Firman Andre Setiawan

---

## 📋 Deskripsi Aplikasi

Aplikasi berbasis web untuk mengelola operasional klinik pratama, mencakup:
- Pendaftaran pasien
- Rekam medis elektronik
- Manajemen farmasi
- Sistem pembayaran kasir

Aplikasi ini mengolah **13 tabel database** secara aktif.

---

## 💻 Teknologi yang Digunakan

| Aspek | Teknologi |
|-------|-----------|
| **Backend** | PHP Native (REST API - php-crud-api) |
| **Frontend** | HTML5, Bootstrap 5, JavaScript (Fetch API) |
| **Database** | MySQL |

---

## ✨ Fitur Utama

1. **Pendaftaran** - Manajemen antrian pasien harian
2. **Pemeriksaan Dokter** - Input diagnosa dan tindakan medis
3. **E-Resep & Farmasi** - Workflow resep dari dokter ke apoteker
4. **Kasir** - Pembayaran terintegrasi (Tindakan + Obat)
5. **Master Data Lengkap** - Manajemen Pasien, Dokter, Obat, Layanan, Poli, dan User

---

## 🚀 Cara Instalasi

1. Download source code
2. Import database `db_simklinik.sql` ke phpMyAdmin
3. Konfigurasi koneksi database:
   - Edit file `api.php` (bagian koneksi)
   - Edit file `config/database.php` (jika diperlukan)
4. Jalankan di local server (XAMPP/Laragon)
5. Login dengan akun default:
   - **Username:** `admin`
   - **Password:** `12345`

---

## 📁 Struktur Folder

```
RumahSakit/
├── api.php
├── config/
│   └── database.php
├── templates/
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
└── [file-file modul lainnya]
```

---

## 📝 Lisensi

Tugas akademik - UAS Pemrograman Web Service
