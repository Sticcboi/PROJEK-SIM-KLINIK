<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$page_title = "Data Obat (Via API)";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Master Data Obat (API Version)</h1>
        <button type="button" class="btn btn-sm btn-primary" onclick="bukaModalTambah()"><i class="bi bi-plus-lg"></i> Tambah Obat</button>
    </div>
    <div class="card shadow-sm"><div class="card-body"><div class="table-responsive">
        <table class="table table-striped table-hover align-middle" id="tabelObat">
            <thead class="table-dark">
                <tr>
                    <th>Kode</th>
                    <th>Nama Obat</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th>H. Beli</th>
                    <th>H. Jual</th>
                    <th width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div><div id="loading" class="text-center my-3"><div class="spinner-border text-primary"></div></div></div></div>
</main>

<div class="modal fade" id="modalObat" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header bg-primary text-white"><h5 class="modal-title" id="modalLabel">Form Obat</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form id="formObat"><input type="hidden" id="id_obat">
        <div class="row">
            <div class="col-md-4 mb-3"><label>Kode Obat</label><input type="text" class="form-control" id="kode_obat" required></div>
            <div class="col-md-8 mb-3"><label>Nama Obat</label><input type="text" class="form-control" id="nama_obat" required></div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><label>Stok</label><input type="number" class="form-control" id="stok" required></div>
            <div class="col-md-4 mb-3"><label>Satuan</label>
                <select class="form-select" id="satuan" required>
                    <option value="Tablet">Tablet</option><option value="Kapsul">Kapsul</option><option value="Syrup">Syrup</option><option value="Botol">Botol</option><option value="Strip">Strip</option><option value="Ampul">Ampul</option>
                </select>
            </div>
        </div>
        <div class="row bg-light p-2 mx-0 rounded">
            <div class="col-md-6 mb-3"><label>Harga Beli (Rp)</label><input type="number" class="form-control" id="harga_beli" required></div>
            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Harga Jual (Rp)</label><input type="number" class="form-control border-primary" id="harga_jual" required></div>
        </div>
    </form></div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" onclick="simpanData()">Simpan Data</button></div>
</div></div></div>

<?php include 'templates/footer.php'; ?>

<script>
const API_URL = 'api.php/records/obats';
const modal = new bootstrap.Modal(document.getElementById('modalObat'));

// Helper format rupiah
const rupiah = (num) => new Intl.NumberFormat('id-ID').format(num);

function loadData() {
    document.getElementById('loading').style.display = 'block';
    document.querySelector('#tabelObat tbody').innerHTML = '';
    fetch(API_URL + '?order=nama_obat,asc').then(r => r.json()).then(data => {
        let html = '';
        data.records.forEach(item => {
            let stokBadge = item.stok <= 10 ? 'bg-danger' : 'bg-success';
            html += `<tr>
                <td><span class="badge bg-secondary">${item.kode_obat}</span></td>
                <td class="fw-bold">${item.nama_obat}</td>
                <td><span class="badge ${stokBadge}">${item.stok}</span></td>
                <td>${item.satuan}</td>
                <td>${rupiah(item.harga_beli)}</td>
                <td>${rupiah(item.harga_jual)}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="bukaEdit(${item.id}, '${item.kode_obat}', '${item.nama_obat}', ${item.stok}, '${item.satuan}', ${item.harga_beli}, ${item.harga_jual})"><i class="bi bi-pencil-square"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="hapusData(${item.id})"><i class="bi bi-trash"></i></button>
                </td></tr>`;
        });
        document.querySelector('#tabelObat tbody').innerHTML = html;
        document.getElementById('loading').style.display = 'none';
    });
}

function simpanData() {
    const id = document.getElementById('id_obat').value;
    const data = {
        kode_obat: document.getElementById('kode_obat').value,
        nama_obat: document.getElementById('nama_obat').value,
        stok: document.getElementById('stok').value,
        satuan: document.getElementById('satuan').value,
        harga_beli: document.getElementById('harga_beli').value,
        harga_jual: document.getElementById('harga_jual').value
    };
    fetch(API_URL + (id ? '/'+id : ''), { method: id ? 'PUT':'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) })
    .then(r=>r.json()).then(() => { modal.hide(); loadData(); alert('Data tersimpan!'); });
}

function hapusData(id) { if(confirm('Yakin hapus obat ini?')) fetch(API_URL+'/'+id, {method:'DELETE'}).then(()=>loadData()); }

function bukaModalTambah() {
    document.getElementById('modalLabel').innerText='Tambah Obat Baru';
    document.getElementById('formObat').reset(); document.getElementById('id_obat').value='';
    document.getElementById('kode_obat').removeAttribute('readonly');
    modal.show();
}
function bukaEdit(id, kode, nama, stok, satuan, hb, hj) {
    document.getElementById('modalLabel').innerText='Edit Data Obat';
    document.getElementById('id_obat').value=id;
    document.getElementById('kode_obat').value=kode; document.getElementById('kode_obat').setAttribute('readonly', true);
    document.getElementById('nama_obat').value=nama;
    document.getElementById('stok').value=stok;
    document.getElementById('satuan').value=satuan;
    document.getElementById('harga_beli').value=hb;
    document.getElementById('harga_jual').value=hj;
    modal.show();
}

loadData();
</script> 