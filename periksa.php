<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$page_title = "Pemeriksaan & E-Resep";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div id="viewAntrian">
        <h1 class="h2 border-bottom pb-2 mb-3">Antrian Pasien (Poli)</h1>
        <div class="card shadow-sm"><div class="card-body">
            <table class="table table-hover align-middle" id="tabelAntrianPeriksa">
                <thead class="table-dark"><tr><th>No. Reg</th><th>Jam</th><th>Pasien</th><th>Dokter</th><th>Keluhan</th><th>Aksi</th></tr></thead>
                <tbody></tbody>
            </table>
            <div id="loading1" class="text-center my-3"><div class="spinner-border text-primary"></div></div>
        </div></div>
    </div>

    <div id="viewFormPeriksa" style="display: none;">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <h1 class="h2">Pemeriksaan Medis</h1>
            <button class="btn btn-secondary" onclick="kembaliKeAntrian()"><i class="bi bi-arrow-left"></i> Kembali</button>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card bg-light border-primary h-100">
                    <div class="card-header bg-primary text-white fw-bold"><i class="bi bi-person-vcard"></i> Pasien</div>
                    <div class="card-body" id="infoPasien"></div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation"><button class="nav-link active fw-bold" id="medis-tab" data-bs-toggle="tab" data-bs-target="#medis-pane" type="button"><i class="bi bi-stethoscope"></i> 1. Rekam Medis</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link fw-bold" id="resep-tab" data-bs-toggle="tab" data-bs-target="#resep-pane" type="button"><i class="bi bi-capsule"></i> 2. E-Resep Obat</button></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <form id="formPeriksa"><input type="hidden" id="pendaftaran_id">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="medis-pane" role="tabpanel">
                                <div class="mb-3"><label class="fw-bold">Anamnesa</label><textarea id="keluhan_utama" class="form-control" rows="3" required></textarea></div>
                                <div class="mb-3"><label class="fw-bold text-danger">Diagnosa (ICD-10)</label><input type="text" id="diagnosa" class="form-control" required></div>
                                <div class="mb-3"><label class="fw-bold">Tindakan</label><div class="card bg-light"><div class="card-body" style="max-height: 150px; overflow-y: auto;" id="listLayanan"></div></div></div>
                            </div>
                            <div class="tab-pane fade" id="resep-pane" role="tabpanel">
                                <div class="row mb-3">
                                    <div class="col-md-6"><label>Cari Obat</label><input type="text" id="cariObat" class="form-control" placeholder="Ketik nama obat..."></div>
                                    <div class="col-md-4"><label>Aturan Pakai</label><input type="text" id="aturanPakai" class="form-control" placeholder="3x1 Sesudah makan"></div>
                                    <div class="col-md-2"><label>Jml</label><div class="input-group"><input type="number" id="jmlObat" class="form-control" value="10" min="1"><button class="btn btn-primary" type="button" onclick="tambahObatKeTabel()"><i class="bi bi-plus"></i></button></div></div>
                                </div>
                                <div id="hasilCariObat" class="list-group mb-3" style="position:absolute; z-index:1000; max-height:200px; overflow-y:auto; display:none; width: 45%;"></div>

                                <table class="table table-sm table-bordered" id="tabelResep">
                                    <thead class="table-light"><tr><th>Nama Obat</th><th>Jml</th><th>Aturan Pakai</th><th>Aksi</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3"><label class="fw-bold">Catatan Tambahan</label><textarea id="catatan_dokter" class="form-control" rows="1"></textarea></div>
                        <button type="button" class="btn btn-success w-100 py-2 fw-bold" onclick="simpanSemua()"><i class="bi bi-save2"></i> SIMPAN PEMERIKSAAN & RESEP</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>

<script>
const API_ANTRIAN = 'api.php/records/pendaftarans?join=pasiens,dokters,dokters.users,dokters.polis&filter=status_periksa,eq,antri&order=tgl_daftar,asc';
let keranjangResep = []; // Array untuk menyimpan obat sementara sebelum disimpan ke DB

// LOAD LAYANAN & OBAT
fetch('api.php/records/layanans').then(r=>r.json()).then(d=>{
    let html=''; d.records.forEach(l=>{html+=`<div class="form-check"><input class="form-check-input chk-layanan" type="checkbox" value="${l.id}" data-harga="${l.harga}" id="l${l.id}"><label class="form-check-label" for="l${l.id}">${l.nama_layanan} <small>(Rp ${l.harga})</small></label></div>`});
    document.getElementById('listLayanan').innerHTML=html;
});

// PENCARIAN OBAT LIVE
document.getElementById('cariObat').addEventListener('input', function(e){
    let kw = e.target.value;
    if(kw.length<2) { document.getElementById('hasilCariObat').style.display='none'; return; }
    fetch('api.php/records/obats?filter=nama_obat,cs,'+kw).then(r=>r.json()).then(d=>{
        let html=''; d.records.forEach(o=>{
            html+=`<a href="#" class="list-group-item list-group-item-action" onclick="pilihObat(${o.id}, '${o.nama_obat}', ${o.harga_jual})">${o.nama_obat} (Stok: ${o.stok})</a>`;
        });
        let hasilDiv = document.getElementById('hasilCariObat');
        hasilDiv.innerHTML=html; hasilDiv.style.display='block';
    });
});
let obatTerpilih = null;
function pilihObat(id, nama, harga) {
    obatTerpilih = {id, nama, harga};
    document.getElementById('cariObat').value = nama;
    document.getElementById('hasilCariObat').style.display='none';
}
function tambahObatKeTabel() {
    if(!obatTerpilih) return alert("Pilih obat dulu!");
    let jml = document.getElementById('jmlObat').value;
    let aturan = document.getElementById('aturanPakai').value;
    keranjangResep.push({obat_id: obatTerpilih.id, nama: obatTerpilih.nama, jumlah: jml, harga_satuan: obatTerpilih.harga, aturan_pakai: aturan});
    renderTabelResep();
    document.getElementById('cariObat').value=''; obatTerpilih=null;
}
function renderTabelResep() {
    let html=''; keranjangResep.forEach((item, i)=>{
        html+=`<tr><td>${item.nama}</td><td>${item.jumlah}</td><td>${item.aturan_pakai}</td><td><button type="button" class="btn btn-xs btn-danger" onclick="hapusResep(${i})">x</button></td></tr>`;
    });
    document.querySelector('#tabelResep tbody').innerHTML=html;
}
function hapusResep(index) { keranjangResep.splice(index, 1); renderTabelResep(); }

// FUNGSI UTAMA
function loadAntrian() {
    fetch(API_ANTRIAN).then(r=>r.json()).then(d=>{
        let html=''; d.records.forEach(i=>{
            html+=`<tr><td class="fw-bold text-primary">${i.no_registrasi}</td><td>${i.tgl_daftar.substring(11,16)}</td><td><strong>${i.pasien_id.nama_pasien}</strong><br><small>${i.pasien_id.no_rm}</small></td><td>${i.dokter_id.poli_id.nama_poli}</td><td>${i.keluhan_awal}</td><td><button class="btn btn-success btn-sm" onclick="mulai(${i.id}, '${i.pasien_id.nama_pasien}', '${i.no_registrasi}', \`${i.keluhan_awal}\`)">PERIKSA</button></td></tr>`;
        });
        document.querySelector('#tabelAntrianPeriksa tbody').innerHTML=html||'<tr><td colspan="6" class="text-center">Kosong</td></tr>';
        document.getElementById('loading1').style.display='none';
    });
}
function mulai(id, nama, reg, keluhan) {
    document.getElementById('viewAntrian').style.display='none'; document.getElementById('viewFormPeriksa').style.display='block';
    document.getElementById('pendaftaran_id').value=id; document.getElementById('keluhan_utama').value=keluhan;
    document.getElementById('infoPasien').innerHTML=`<h4>${nama}</h4><p>${reg}</p>`;
    keranjangResep=[]; renderTabelResep(); // Reset resep
}
function kembaliKeAntrian() {
    document.getElementById('viewFormPeriksa').style.display='none'; document.getElementById('viewAntrian').style.display='block';
}

// SIMPAN SUPER KOMPLEKS (Rekam Medis + Tindakan + Resep + Detail Resep)
async function simpanSemua() {
    if(!confirm('Simpan data?')) return;
    const pendaftaranId = document.getElementById('pendaftaran_id').value;

    // 1. SIAPKAN DATA REKAM MEDIS (incl. Tindakan)
    let tindakan = []; document.querySelectorAll('.chk-layanan:checked').forEach(c=>tindakan.push({layanan_id:c.value, harga_saat_ini:c.getAttribute('data-harga')}));
    let dataRM = {
        pendaftaran_id: pendaftaranId, keluhan_utama: document.getElementById('keluhan_utama').value,
        diagnosa: document.getElementById('diagnosa').value, catatan_dokter: document.getElementById('catatan_dokter').value,
        tgl_periksa: new Date().toISOString().slice(0,19).replace('T',' '), tindakan_rm: tindakan
    };

    // 2. POST REKAM MEDIS
    let respRM = await fetch('api.php/records/rekam_medis', {method:'POST', body:JSON.stringify(dataRM)}).then(r=>r.json());
    if(!respRM || typeof respRM !== 'number') return alert("Gagal simpan rekam medis!");
    const rmId = respRM; // ID Rekam Medis baru

    // 3. JIKA ADA RESEP, POST RESEP (incl. Detail Resep)
    if(keranjangResep.length > 0) {
        let detailResep = keranjangResep.map(item => ({obat_id: item.obat_id, jumlah: item.jumlah, harga_satuan: item.harga_satuan, aturan_pakai: item.aturan_pakai}));
        let dataResep = { rekam_medis_id: rmId, status_tebus: 'menunggu', detail_reseps: detailResep };
        await fetch('api.php/records/reseps', {method:'POST', body:JSON.stringify(dataResep)});
    }

    // 4. UPDATE STATUS PENDAFTARAN
    await fetch('api.php/records/pendaftarans/'+pendaftaranId, {method:'PUT', body:JSON.stringify({status_periksa:'selesai_periksa'})});
    alert("Pemeriksaan Selesai!"); kembaliKeAntrian(); loadAntrian();
}
loadAntrian();
</script> 