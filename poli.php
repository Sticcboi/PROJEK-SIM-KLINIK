<?php
// Kita masih pakai .php CUMA untuk include header/sidebar biar tidak repot copy-paste template.
// Tapi TIDAK ADA logika database di sini.
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$page_title = "Data Poliklinik (Via API)";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Master Data Poli (API Version)</h1>
        <button type="button" class="btn btn-sm btn-primary" onclick="bukaModalTambah()">
            <i class="bi bi-plus-lg"></i> Tambah Poli
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tabelPoli">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama Poliklinik</th>
                            <th>Keterangan</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
            <div id="loading" class="text-center my-3">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="modalPoli" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Form Poliklinik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPoli">
                    <input type="hidden" id="id_poli" name="id">
                    <div class="mb-3">
                        <label class="form-label">Nama Poli</label>
                        <input type="text" class="form-control" id="nama_poli" name="nama_poli" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanData()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>

<script>
const API_URL = 'api.php/records/polis';
const modalPoli = new bootstrap.Modal(document.getElementById('modalPoli'));

// 1. FUNGSI READ (BACA DATA)
function loadData() {
    document.getElementById('loading').style.display = 'block';
    document.querySelector('#tabelPoli tbody').innerHTML = '';

    fetch(API_URL)
        .then(response => response.json())
        .then(data => {
            let html = '';
            // php-crud-api mengembalikan data dalam format { records: [...] }
            data.records.forEach(item => {
                html += `
                    <tr>
                        <td>${item.id}</td>
                        <td>${item.nama_poli}</td>
                        <td>${item.keterangan}</td>
                        <td>
                           <button class="btn btn-sm btn-warning" onclick="bukaModalEdit(${item.id}, '${item.nama_poli}', '${item.keterangan}')">
                                <i class="bi bi-pencil-square"></i>
                           </button>
                           <button class="btn btn-sm btn-danger" onclick="hapusData(${item.id})">
                                <i class="bi bi-trash"></i>
                           </button>
                        </td>
                    </tr>
                `;
            });
            document.querySelector('#tabelPoli tbody').innerHTML = html;
            document.getElementById('loading').style.display = 'none';
        })
        .catch(error => {
            alert("Gagal memuat data dari API!");
            console.error('Error:', error);
        });
}

// 2. FUNGSI CREATE/UPDATE (SIMPAN DATA)
function simpanData() {
    const id = document.getElementById('id_poli').value;
    const dataKirim = {
        nama_poli: document.getElementById('nama_poli').value,
        keterangan: document.getElementById('keterangan').value
    };

    let url = API_URL;
    let method = 'POST'; // Default: Tambah Baru

    // Jika ID tidak kosong, berarti MODE EDIT (Method PUT)
    if (id) {
        url += '/' + id;
        method = 'PUT';
    }

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dataKirim)
    })
    .then(response => response.json())
    .then(result => {
        modalPoli.hide();
        loadData(); // Reload tabel setelah simpan
        alert(id ? 'Data berhasil diupdate!' : 'Data berhasil ditambahkan!');
    })
    .catch(error => console.error('Error:', error));
}

// 3. FUNGSI DELETE (HAPUS DATA)
function hapusData(id) {
    if (confirm('Yakin ingin menghapus data ini?')) {
        fetch(API_URL + '/' + id, { method: 'DELETE' })
            .then(response => {
                 loadData(); // Reload tabel
                 // alert('Data terhapus!');
            })
            .catch(error => console.error('Error:', error));
    }
} 

// -- FUNGSI HELPER UNTUK MODAL --
function bukaModalTambah() {
    document.getElementById('modalLabel').innerText = 'Tambah Poli';
    document.getElementById('formPoli').reset();
    document.getElementById('id_poli').value = ''; // Kosongkan ID
    modalPoli.show();
}

function bukaModalEdit(id, nama, ket) {
    document.getElementById('modalLabel').innerText = 'Edit Poli';
    document.getElementById('id_poli').value = id;
    document.getElementById('nama_poli').value = nama;
    document.getElementById('keterangan').value = ket;
    modalPoli.show();
}

// Load data pertama kali saat halaman dibuka
loadData();
</script>