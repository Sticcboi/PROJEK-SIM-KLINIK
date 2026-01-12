<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$page_title = "Riwayat Rekam Medis";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Riwayat Rekam Medis Pasien</h1>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabelRiwayat">
                    <thead class="table-dark">
                        <tr><th>Tgl Periksa</th><th>No. RM</th><th>Nama Pasien</th><th>Diagnosa</th><th>Dokter</th><th>Aksi</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="loading" class="text-center my-3"><div class="spinner-border text-primary"></div></div>
        </div>
    </div>
</main>

<div class="modal fade" id="modalDetail" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header bg-info text-white"><h5 class="modal-title">Detail Pemeriksaan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="detailBody">
        <div class="text-center"><div class="spinner-border text-info"></div></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
</div></div></div>

<?php include 'templates/footer.php'; ?>

<script>
const modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));

// 1. LOAD DAFTAR RIWAYAT (HEADER)
// Kita perlu JOIN banyak tabel agar infonya lengkap: rekam_medis -> pendaftarans -> pasiens & dokters -> users
const API_RIWAYAT = 'api.php/records/rekam_medis?join=pendaftarans,pendaftarans.pasiens,pendaftarans.dokters,pendaftarans.dokters.users&order=tgl_periksa,desc';

function loadRiwayat() {
    fetch(API_RIWAYAT).then(r => r.json()).then(data => {
        let html = '';
        data.records.forEach(item => {
            html += `<tr>
                <td>${item.tgl_periksa.substring(0, 10)}</td>
                <td><span class="badge bg-secondary">${item.pendaftaran_id.pasien_id.no_rm}</span></td>
                <td class="fw-bold">${item.pendaftaran_id.pasien_id.nama_pasien}</td>
                <td>${item.diagnosa}</td>
                <td>${item.pendaftaran_id.dokter_id.user_id.nama_lengkap}</td>
                <td><button class="btn btn-sm btn-info text-white" onclick="lihatDetail(${item.id})"><i class="bi bi-eye"></i> Detail</button></td>
            </tr>`;
        });
        document.querySelector('#tabelRiwayat tbody').innerHTML = html;
        document.getElementById('loading').style.display = 'none';
    });
}

// 2. LOAD DETAIL (TINDAKAN) SAAT TOMBOL DIKLIK
// Fitur keren lain dari API: bisa filter berdasarkan ID parent
function lihatDetail(rmId) {
    modalDetail.show();
    document.getElementById('detailBody').innerHTML = '<div class="text-center"><div class="spinner-border text-info"></div></div>';

    // Ambil data HEADER lagi (untuk memastikan dapat data fresh)
    fetch('api.php/records/rekam_medis/'+rmId+'?join=pendaftarans,pendaftarans.pasiens,pendaftarans.dokters,pendaftarans.dokters.users')
    .then(r => r.json()).then(header => {
        // Ambil data DETAIL TINDAKAN (filter by rekam_medis_id)
        fetch('api.php/records/tindakan_rm?filter=rekam_medis_id,eq,'+rmId+'&join=layanans')
        .then(r => r.json()).then(detail => {
            let listTindakan = '';
            let totalBiaya = 0;
            detail.records.forEach(t => {
                listTindakan += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    ${t.layanan_id.nama_layanan}
                    <span>Rp ${new Intl.NumberFormat('id-ID').format(t.harga_saat_ini)}</span>
                </li>`;
                totalBiaya += parseInt(t.harga_saat_ini);
            });
 
            // Render Tampilan Detail Lengkap
            let html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Pasien:</strong> ${header.pendaftaran_id.pasien_id.nama_pasien} (${header.pendaftaran_id.pasien_id.no_rm})<br>
                        <strong>Dokter:</strong> ${header.pendaftaran_id.dokter_id.user_id.nama_lengkap}<br>
                        <strong>Tgl Periksa:</strong> ${header.tgl_periksa}
                    </div>
                    <div class="col-md-6 bg-light p-2 rounded">
                        <strong>Keluhan Utama:</strong><br> ${header.keluhan_utama}<br>
                        <strong>Diagnosa:</strong><br> <span class="text-danger fw-bold">${header.diagnosa}</span>
                    </div>
                </div>
                <h6>Tindakan yang Diberikan:</h6>
                <ul class="list-group mb-3">${listTindakan}</ul>
                <h5 class="text-end text-primary">Total Biaya Tindakan: Rp ${new Intl.NumberFormat('id-ID').format(totalBiaya)}</h5>
                <div class="alert alert-warning mt-3 py-2"><small><strong>Catatan Dokter:</strong> ${header.catatan_dokter || '-'}</small></div>
            `;
            document.getElementById('detailBody').innerHTML = html;
        });
    });
}

loadRiwayat();
</script>