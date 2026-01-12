<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$page_title = "Pendaftaran (Via API)";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Pendaftaran Pasien (API Version)</h1>
        <button class="btn btn-primary" onclick="bukaModalDaftar()">
            <i class="bi bi-plus-circle"></i> Daftar Pasien Baru
        </button>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0"><i class="bi bi-list-ol"></i> Antrian Kunjungan Hari Ini</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="tabelAntrian">
                    <thead class="table-dark">
                        <tr><th>No. Reg</th><th>Jam</th><th>Nama Pasien</th><th>Tujuan Dokter</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="loading" class="text-center my-3"><div class="spinner-border text-info"></div></div>
        </div>
    </div>
</main>

<div class="modal fade" id="modalDaftar" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header bg-primary text-white"><h5 class="modal-title">Form Pendaftaran</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <form id="formDaftar">
            <div class="mb-4">
                <label class="fw-bold">Pilih Pasien</label>
                <input class="form-control" list="listPasien" id="cari_pasien" placeholder="Ketik Nama Pasien..." required autocomplete="off">
                <datalist id="listPasien"></datalist>
                <input type="hidden" id="pasien_id_hidden">
            </div>
            <div class="mb-4">
                <label class="fw-bold">Dokter Tujuan</label>
                <select class="form-select" id="dokter_id" required>
                    <option value="">- Pilih Dokter -</option>
                    </select>
            </div>
            <div class="mb-3"><label class="fw-bold">Keluhan Awal</label><textarea class="form-control" id="keluhan_awal" rows="2"></textarea></div>
        </form>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button><button class="btn btn-success" onclick="simpanDaftar()">Daftarkan</button></div>
</div></div></div>

<?php include 'templates/footer.php'; ?>

<script>
const modal = new bootstrap.Modal(document.getElementById('modalDaftar'));

// 1. FETCH DATA ANTRIAN (JOIN 4 TABEL: Pasiens, Dokters, Users, Polis)
// Filter: Hanya tampilkan yang tgl_daftar hari ini (dimulai dengan tanggal YYYY-MM-DD hari ini)
const todayStr = new Date().toISOString().split('T')[0]; // Dapat format YYYY-MM-DD
const API_ANTRIAN = `api.php/records/pendaftarans?join=pasiens,dokters,dokters.users,dokters.polis&filter=tgl_daftar,sw,${todayStr}&order=tgl_daftar,desc`;

function loadAntrian() {
    document.getElementById('loading').style.display = 'block';
    document.querySelector('#tabelAntrian tbody').innerHTML = '';

    fetch(API_ANTRIAN)
        .then(r => r.json())
        .then(data => {
            let html = '';
            data.records.forEach(item => {
                // Ambil jam dari tgl_daftar (format YYYY-MM-DD HH:MM:SS)
                let jam = item.tgl_daftar.split(' ')[1].substring(0, 5);
                html += `<tr>
                    <td class="fw-bold">${item.no_registrasi}</td>
                    <td>${jam}</td>
                    <td>${item.pasien_id.nama_pasien}<br><small class="text-muted">${item.pasien_id.no_rm}</small></td>
                    <td>${item.dokter_id.poli_id.nama_poli}<br><small class="text-primary">${item.dokter_id.user_id.nama_lengkap}</small></td>
                    <td><span class="badge bg-warning text-dark">${item.status_periksa.toUpperCase()}</span></td>
                    <td>${item.status_periksa === 'antri' ? `<button class="btn btn-sm btn-outline-danger" onclick="batalkan(${item.id})">Batal</button>` : ''}</td>
                </tr>`;
            });
            document.querySelector('#tabelAntrian tbody').innerHTML = html || '<tr><td colspan="6" class="text-center fst-italic">Belum ada antrian hari ini.</td></tr>';
            document.getElementById('loading').style.display = 'none';
        });
}

// 2. FETCH DATA UNTUK DROPDOWN (Dijalankan sekali saat halaman terbuka)
function loadDropdownData() {
    // A. Load Pasien untuk Datalist
    fetch('api.php/records/pasiens?include=id,nama_pasien,no_rm')
        .then(r => r.json())
        .then(data => {
            let options = '';
            data.records.forEach(p => {
                options += `<option data-id="${p.id}" value="${p.no_rm} - ${p.nama_pasien}">`;
            });
            document.getElementById('listPasien').innerHTML = options;
        });

    // B. Load Dokter untuk Select Option (Perlu JOIN ke User & Poli biar namanya muncul)
    fetch('api.php/records/dokters?join=users,polis&include=id,user_id.nama_lengkap,poli_id.nama_poli')
        .then(r => r.json())
        .then(data => {
            let options = '<option value="">- Pilih Dokter -</option>';
            data.records.forEach(d => {
                options += `<option value="${d.id}">${d.poli_id.nama_poli} - ${d.user_id.nama_lengkap}</option>`;
            });
            document.getElementById('dokter_id').innerHTML = options;
        });
}

// 3. PROSES SIMPAN PENDAFTARAN
function simpanDaftar() {
    const pasienId = document.getElementById('pasien_id_hidden').value;
    const dokterId = document.getElementById('dokter_id').value;
    if (!pasienId || !dokterId) { alert("Pilih pasien dan dokter dengan benar!"); return; }

    // Generate No Reg Sederhana via JS (REG-TIMESTAMP) agar unik tanpa ribet query backend
    const noReg = 'REG-' + Math.floor(Date.now() / 1000);

    // Data yang akan dikirim ke API
    const dataKirim = {
        no_registrasi: noReg,
        pasien_id: pasienId,
        dokter_id: dokterId,
        keluhan_awal: document.getElementById('keluhan_awal').value,
        status_periksa: 'antri',
        // tgl_daftar otomatis terisi CURRENT_TIMESTAMP oleh database MySQL
    };

    fetch('api.php/records/pendaftarans', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(dataKirim)
    }).then(r => r.json()).then(() => {
        modal.hide();
        loadAntrian(); // Refresh tabel
        alert("Pendaftaran berhasil! No Reg: " + noReg);
    }).catch(e => alert("Gagal mendaftar."));
}

function batalkan(id) {
    if(confirm('Batalkan antrian ini?')) {
        // API Delete
        fetch('api.php/records/pendaftarans/'+id, {method: 'DELETE'}).then(() => loadAntrian());
    }
}

// Helper untuk ambil ID dari datalist pasien
document.getElementById('cari_pasien').addEventListener('input', function(e) {
    let list = document.getElementById('listPasien');
    let hidden = document.getElementById('pasien_id_hidden');
    hidden.value = ""; // Reset
    for (let i = 0; i < list.options.length; i++) {
        if (list.options[i].value === e.target.value) {
            hidden.value = list.options[i].getAttribute('data-id');
            break;
        }
    }
});

// Helper: Buka modal dan reset form
function bukaModalDaftar() {
    document.getElementById('formDaftar').reset();
    document.getElementById('pasien_id_hidden').value = '';
    modal.show();
}

// Jalankan saat pertama kali buka halaman
loadDropdownData(); 
loadAntrian();
</script>