<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$page_title = "Data Dokter (Via API)";
include 'templates/header.php';
include 'templates/sidebar.php';
?> 

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Master Data Dokter (API JOIN Version)</h1>
        <button class="btn btn-sm btn-secondary" disabled title="Fitur tambah via API butuh logic kompleks (nested insert)"><i class="bi bi-lock"></i> Tambah (Disabled for Demo)</button>
    </div>
    <div class="alert alert-info small py-2"><i class="bi bi-info-circle"></i> Halaman ini mendemonstrasikan fitur <b>API JOIN</b>. Data poli dan nama diambil otomatis dari tabel lain via API.</div>

    <div class="card shadow-sm"><div class="card-body"><div class="table-responsive">
        <table class="table table-hover align-middle" id="tabelDokter">
            <thead class="table-dark">
                <tr><th>Nama Dokter (dari tb_users)</th><th>Poli (dari tb_polis)</th><th>SIP</th><th>No HP</th><th>Aksi</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div><div id="loading" class="text-center my-3"><div class="spinner-border text-primary"></div></div></div></div>
</main>

<?php include 'templates/footer.php'; ?>

<script>
// URL API KUNCI: Menggunakan parameter ?join untuk menggabungkan 3 tabel sekaligus!
const API_URL = 'api.php/records/dokters?join=users,polis';

function loadData() {
    document.getElementById('loading').style.display = 'block';
    document.querySelector('#tabelDokter tbody').innerHTML = '';

    fetch(API_URL)
        .then(response => response.json())
        .then(data => {
            let html = '';
            // Data yang dikembalikan API otomatis berbentuk object bersarang (Nested Object)
            // Contoh: item.user_id.nama_lengkap, item.poli_id.nama_poli
            data.records.forEach(item => {
                // Proteksi jika ada data null (misal poli terhapus)
                const namaDokter = item.user_id ? item.user_id.nama_lengkap : '<em class="text-danger">User Error</em>';
                const namaPoli = item.poli_id ? item.poli_id.nama_poli : '<em class="text-danger">Tanpa Poli</em>';

                html += `<tr>
                    <td class="fw-bold text-primary">${namaDokter}</td>
                    <td><span class="badge bg-info text-dark">${namaPoli}</span></td>
                    <td><small>${item.sip}</small></td>
                    <td>${item.no_hp}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="hapusData(${item.id})"><i class="bi bi-trash"></i></button>
                    </td></tr>`;
            });
            document.querySelector('#tabelDokter tbody').innerHTML = html;
            document.getElementById('loading').style.display = 'none';
        });
}

function hapusData(id) {
    // Note: Hapus via API ini hanya menghapus data di tabel 'dokters'.
    // User loginnya masih ada. Untuk UTS ini sudah cukup membuktikan API bekerja.
    if(confirm('Hapus status dokter ini?')) {
        fetch('api.php/records/dokters/'+id, {method:'DELETE'}).then(()=>loadData());
    }
}

loadData();
</script>