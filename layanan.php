<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$page_title = "Data Layanan (Via API)";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Master Data Layanan (API Version)</h1>
        <button type="button" class="btn btn-sm btn-primary" onclick="bukaModalTambah()"><i class="bi bi-plus-lg"></i> Tambah Layanan</button>
    </div>
    <div class="card shadow-sm"><div class="card-body"><div class="table-responsive">
        <table class="table table-striped table-hover" id="tabelLayanan">
            <thead class="table-dark"><tr><th>No</th><th>Nama Layanan</th><th>Harga (Rp)</th><th>Aksi</th></tr></thead>
            <tbody></tbody>
        </table>
    </div><div id="loading" class="text-center my-3"><div class="spinner-border text-primary"></div></div></div></div>
</main>

<div class="modal fade" id="modalLayanan" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="modalLabel">Form Layanan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form id="formLayanan"><input type="hidden" id="id_layanan">
        <div class="mb-3"><label>Nama Layanan</label><input type="text" class="form-control" id="nama_layanan" required></div>
        <div class="mb-3"><label>Harga (Rp)</label><input type="number" class="form-control" id="harga" required></div>
    </form></div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" onclick="simpanData()">Simpan</button></div>
</div></div></div>

<?php include 'templates/footer.php'; ?>

<script>
const API_URL = 'api.php/records/layanans';
const modal = new bootstrap.Modal(document.getElementById('modalLayanan'));

function loadData() {
    document.getElementById('loading').style.display = 'block';
    document.querySelector('#tabelLayanan tbody').innerHTML = '';
    fetch(API_URL).then(r => r.json()).then(data => {
        let html = '';
        data.records.forEach((item, i) => {
            // Format angka ke Rupiah sederhana
            let hargaRp = new Intl.NumberFormat('id-ID').format(item.harga);
            html += `<tr><td>${i+1}</td><td>${item.nama_layanan}</td><td>Rp ${hargaRp}</td>
                <td><button class="btn btn-sm btn-warning" onclick="bukaModalEdit(${item.id}, '${item.nama_layanan}', ${item.harga})"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-danger" onclick="hapusData(${item.id})"><i class="bi bi-trash"></i></button></td></tr>`;
        });
        document.querySelector('#tabelLayanan tbody').innerHTML = html;
        document.getElementById('loading').style.display = 'none';
    });
}

function simpanData() {
    const id = document.getElementById('id_layanan').value;
    const data = { nama_layanan: document.getElementById('nama_layanan').value, harga: document.getElementById('harga').value };
    fetch(API_URL + (id ? '/'+id : ''), { method: id ? 'PUT':'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) })
    .then(r=>r.json()).then(() => { modal.hide(); loadData(); });
}

function hapusData(id) { if(confirm('Hapus?')) fetch(API_URL+'/'+id, {method:'DELETE'}).then(()=>loadData()); }
function bukaModalTambah() { document.getElementById('modalLabel').innerText='Tambah'; document.getElementById('formLayanan').reset(); document.getElementById('id_layanan').value=''; modal.show(); }
function bukaModalEdit(id, nama, harga) { document.getElementById('modalLabel').innerText='Edit'; document.getElementById('id_layanan').value=id; document.getElementById('nama_layanan').value=nama; document.getElementById('harga').value=harga; modal.show(); }
loadData();
</script> 