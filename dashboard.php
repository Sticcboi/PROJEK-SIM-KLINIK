<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Judul halaman (opsional, bisa dipakai di header nanti)
$page_title = "Dashboard";

include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-people-fill"></i> Pasien Hari Ini</h5>
                    <p class="card-text display-4 fw-bold">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-cash"></i> Pendapatan Hari Ini</h5>
                    <p class="card-text display-6 fw-bold">Rp 0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
             <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person-user"></i> Dokter Jaga</h5>
                    <p class="card-text display-4 fw-bold">2</p>
                </div>
            </div> 
        </div>
         <div class="col-md-3 mb-4">
             <div class="card text-white bg-danger h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-exclamation-triangle"></i> Stok Obat Menipis</h5>
                    <p class="card-text display-4 fw-bold">5</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            Selamat Datang di SIM KLINIK
        </div>
        <div class="card-body">
            <p>Anda login sebagai <strong><?= ucfirst($_SESSION['role']); ?></strong>.</p>
            <p>Silakan gunakan menu di sebelah kiri untuk mulai bekerja.</p>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>