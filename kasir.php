<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$page_title = "Kasir Pembayaran";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Kasir / Pembayaran</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="bi bi-hourglass-split"></i> Menunggu Pembayaran
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tabelKasir">
                            <thead class="table-dark"><tr><th>No. Reg</th><th>Pasien</th><th>Poli</th><th>Status</th><th>Aksi</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div id="loading" class="text-center my-3"><div class="spinner-border text-warning"></div></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="bi bi-cash-register"></i> Form Bayar
                </div>
                <div class="card-body">
                    <form id="formBayar">
                        <input type="hidden" id="pendaftaran_id">
                        <div class="mb-3 text-center">
                            <h6 class="text-muted">Total Tagihan</h6>
                            <h1 class="display-4 fw-bold text-success" id="tampilTotal">Rp 0</h1>
                            <input type="hidden" id="total_akhir_angka" value="0">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="fw-bold">Nama Pasien</label>
                            <input type="text" class="form-control bg-light" id="nama_pasien" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Uang Diterima (Rp)</label>
                            <input type="number" class="form-control form-control-lg border-success" id="jumlah_bayar" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Kembalian</label>
                            <input type="text" class="form-control bg-light fw-bold" id="kembalian" readonly value="Rp 0">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg" id="btnSimpanBayar" disabled>PROSES BAYAR</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>

<script>
// Ambil ID user yang sedang login dari session PHP untuk disimpan di tabel pembayaran
const KASIR_ID = <?= $_SESSION['user_id']; ?>;

// Load antrian pasien yang sudah selesai periksa tapi BELUM bayar
const API_KASIR = 'api.php/records/pendaftarans?join=pasiens,dokters,dokters.polis&filter=status_periksa,eq,selesai_periksa&filter=status_bayar,eq,belum';

function loadAntrianKasir() {
    document.getElementById('loading').style.display = 'block';
    fetch(API_KASIR).then(r => r.json()).then(data => {
        let html = '';
        data.records.forEach(item => {
            html += `<tr>
                <td class="fw-bold">${item.no_registrasi}</td>
                <td>${item.pasien_id.nama_pasien}<br><small class="text-muted">${item.pasien_id.no_rm}</small></td>
                <td>${item.dokter_id.poli_id.nama_poli}</td>
                <td><span class="badge bg-warning text-dark">Belum Bayar</span></td>
                <td><button class="btn btn-primary btn-sm fw-bold" onclick="pilihBayar(${item.id}, '${item.pasien_id.nama_pasien}')">PILIH &rarr;</button></td>
            </tr>`;
        });
        document.querySelector('#tabelKasir tbody').innerHTML = html || '<tr><td colspan="5" class="text-center p-3">Tidak ada antrian kasir.</td></tr>';
        document.getElementById('loading').style.display = 'none';
    });
}

// Saat tombol PILIH diklik
function pilihBayar(idDaftar, namaPasien) {
    document.getElementById('pendaftaran_id').value = idDaftar;
    document.getElementById('nama_pasien').value = namaPasien;
    document.getElementById('btnSimpanBayar').disabled = true;
    document.getElementById('tampilTotal').innerText = 'Loading...';

    // HITUNG TOTAL TAGIHAN: Ambil dari tabel rekam_medis -> tindakan_rm
    // Kita cari dulu ID rekam medis dari pendaftaran ini
    fetch('api.php/records/rekam_medis?filter=pendaftaran_id,eq,'+idDaftar).then(r=>r.json()).then(rmData => {
        if(rmData.records.length > 0) {
            const rmId = rmData.records[0].id;
            // Ambil detail tindakan untuk dihitung totalnya
            fetch('api.php/records/tindakan_rm?filter=rekam_medis_id,eq,'+rmId).then(r=>r.json()).then(tindakanData => {
                let total = 0;
                tindakanData.records.forEach(t => total += parseInt(t.harga_saat_ini));

                // Tampilkan total
                document.getElementById('total_akhir_angka').value = total;
                document.getElementById('tampilTotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
                document.getElementById('btnSimpanBayar').disabled = false;
                document.getElementById('jumlah_bayar').focus();
            });
        } else {
            alert("Data medis tidak ditemukan!");
        }
    });
}

// Hitung kembalian otomatis saat ketik uang diterima
document.getElementById('jumlah_bayar').addEventListener('input', function() {
    let total = parseInt(document.getElementById('total_akhir_angka').value) || 0;
    let bayar = parseInt(this.value) || 0;
    let kembali = bayar - total;
    document.getElementById('kembalian').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(kembali);
    // Disable tombol jika uang kurang
    document.getElementById('btnSimpanBayar').disabled = (bayar < total);
});

// PROSES SIMPAN PEMBAYARAN
document.getElementById('formBayar').addEventListener('submit', function(e) {
    e.preventDefault();
    if(!confirm('Proses pembayaran ini?')) return;

    const idDaftar = document.getElementById('pendaftaran_id').value;
    const total = document.getElementById('total_akhir_angka').value;
    const bayar = document.getElementById('jumlah_bayar').value;
    const kembali = bayar - total;

    // 1. Insert ke tabel pembayarans
    const dataBayar = {
        pendaftaran_id: idDaftar,
        total_biaya_tindakan: total,
        total_akhir: total, // Untuk sekarang sama, nanti kalau ada obat tinggal ditambah
        jumlah_bayar: bayar,
        kembalian: kembali,
        kasir_user_id: KASIR_ID
    };

    fetch('api.php/records/pembayarans', {
        method: 'POST', 
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(dataBayar)
    }).then(r => r.json()).then(hasil => {
        if(hasil > 0) {
            // 2. Update status pendaftaran jadi 'sudah' (bayar)
            fetch('api.php/records/pendaftarans/'+idDaftar, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ status_bayar: 'sudah' })
            }).then(() => {
                alert("Pembayaran Berhasil! Kembalian: Rp " + new Intl.NumberFormat('id-ID').format(kembali));
                document.getElementById('formBayar').reset();
                document.getElementById('tampilTotal').innerText = 'Rp 0';
                loadAntrianKasir(); // Refresh tabel
            });
        } else {
            alert("Gagal menyimpan pembayaran.");
        }
    });
});

loadAntrianKasir();
</script>