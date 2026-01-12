<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$page_title = "Data Pasien (Via API)";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Master Data Pasien (API Version)</h1>
        <button class="btn btn-sm btn-primary" onclick="bukaModalTambah()"><i class="bi bi-person-plus-fill"></i> Tambah Pasien</button>
    </div>
    <div class="card shadow-sm"><div class="card-body"><div class="table-responsive">
        <table class="table table-striped table-hover" id="tabelPasien">
            <thead class="table-dark">
                <tr><th>No RM</th><th>NIK</th><th>Nama Pasien</th><th>L/P</th><th>Usia (Thn)</th><th>Aksi</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div><div id="loading" class="text-center my-3"><div class="spinner-border text-primary"></div></div></div></div>
</main>

<div class="modal fade" id="modalPasien" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header bg-primary text-white"><h5 class="modal-title" id="modalLabel">Form Pasien</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form id="formPasien"><input type="hidden" id="id_pasien">
        <div class="row">
            <div class="col-md-6 mb-3"><label>No. Rekam Medis (RM)</label><input type="text" class="form-control" id="no_rm" placeholder="Auto/Manual" required></div>
            <div class="col-md-6 mb-3"><label>NIK KTP</label><input type="number" class="form-control" id="nik" required></div>
        </div>
        <div class="mb-3"><label>Nama Lengkap</label><input type="text" class="form-control" id="nama_pasien" required></div>
        <div class="row">
            <div class="col-md-4 mb-3"><label>Tgl Lahir</label><input type="date" class="form-control" id="tgl_lahir" required></div>
            <div class="col-md-4 mb-3"><label>Jenis Kelamin</label><select class="form-select" id="jenis_kelamin" required><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
            <div class="col-md-4 mb-3"><label>No HP</label><input type="text" class="form-control" id="no_hp"></div>
        </div>
        <div class="mb-3"><label>Alamat Lengkap</label><textarea class="form-control" id="alamat" rows="2"></textarea></div>
    </form></div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" onclick="simpanData()">Simpan Pasien</button></div>
</div></div></div>

<?php include 'templates/footer.php'; ?>

<script>
const API_URL = 'api.php/records/pasiens';
const modal = new bootstrap.Modal(document.getElementById('modalPasien'));

// Helper hitung usia
function hitungUsia(tgl) {
    return Math.floor((new Date() - new Date(tgl).getTime()) / 3.15576e+10);
}

function loadData() {
    document.getElementById('loading').style.display = 'block';
    document.querySelector('#tabelPasien tbody').innerHTML = '';
    // Order by ID desc agar pasien terbaru muncul paling atas
    fetch(API_URL + '?order=id,desc&page=1,20').then(r => r.json()).then(data => {
        let html = '';
        data.records.forEach(item => {
            html += `<tr>
                <td><span class="badge bg-info text-dark">${item.no_rm}</span></td>
                <td>${item.nik}</td>
                <td class="fw-bold">${item.nama_pasien}</td>
                <td>${item.jenis_kelamin}</td>
                <td>${hitungUsia(item.tgl_lahir)}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="bukaEdit(${item.id}, '${item.no_rm}', '${item.nik}', '${item.nama_pasien}', '${item.tgl_lahir}', '${item.jenis_kelamin}', '${item.no_hp}', \`${item.alamat}\`)"><i class="bi bi-pencil-square"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="hapusData(${item.id})"><i class="bi bi-trash"></i></button>
                </td></tr>`;
        });
        document.querySelector('#tabelPasien tbody').innerHTML = html;
        document.getElementById('loading').style.display = 'none';
    });
}

function simpanData() {
    const id = document.getElementById('id_pasien').value;
    const data = {
        no_rm: document.getElementById('no_rm').value,
        nik: document.getElementById('nik').value,
        nama_pasien: document.getElementById('nama_pasien').value,
        tgl_lahir: document.getElementById('tgl_lahir').value,
        jenis_kelamin: document.getElementById('jenis_kelamin').value,
        no_hp: document.getElementById('no_hp').value,
        alamat: document.getElementById('alamat').value
    };
    fetch(API_URL + (id ? '/'+id : ''), { method: id ? 'PUT':'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) })
    .then(r=>r.json()).then(() => { modal.hide(); loadData(); alert('Data pasien tersimpan!'); })
    .catch(e => alert('Gagal simpan! Pastikan No RM / NIK unik.'));
}
 
function hapusData(id) { if(confirm('Hapus pasien ini?')) fetch(API_URL+'/'+id, {method:'DELETE'}).then(()=>loadData()); }

function bukaModalTambah() {
    document.getElementById('modalLabel').innerText='Tambah Pasien Baru';
    document.getElementById('formPasien').reset(); document.getElementById('id_pasien').value='';
    modal.show();
}
// Fungsi buka edit sedikit panjang karena parameternya banyak
function bukaEdit(id, rm, nik, nama, tgl, jk, hp, alamat) {
    document.getElementById('modalLabel').innerText='Edit Data Pasien';
    document.getElementById('id_pasien').value=id;
    document.getElementById('no_rm').value=rm;
    document.getElementById('nik').value=nik;
    document.getElementById('nama_pasien').value=nama;
    document.getElementById('tgl_lahir').value=tgl;
    document.getElementById('jenis_kelamin').value=jk;
    document.getElementById('no_hp').value=hp;
    document.getElementById('alamat').value=alamat;
    modal.show();
}

loadData();
</script>