<?php
session_start();
// HANYA ADMIN yang boleh akses halaman ini!
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php"); // Tendang user non-admin kembali ke dashboard
    exit;
}
$page_title = "Manajemen User (Via API)";
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Manajemen Pengguna Sistem</h1>
        <button class="btn btn-primary" onclick="bukaModalTambah()">
            <i class="bi bi-person-plus-fill"></i> Tambah User Baru
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="tabelUser">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role (Hak Akses)</th>
                            <th>Terdaftar Sejak</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="loading" class="text-center my-3"><div class="spinner-border text-primary"></div></div>
        </div>
    </div>
</main>

<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLabel">Form User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUser">
                    <input type="hidden" id="id_user">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" required placeholder="Misal: Budi Santoso">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role / Hak Akses</label>
                        <select class="form-select" id="role" required>
                            <option value="">- Pilih Role -</option>
                            <option value="admin">Administrator (Admin)</option>
                            <option value="resepsionis">Resepsionis (Pendaftaran)</option>
                            <option value="apoteker">Apoteker (Farmasi)</option>
                            <option value="kasir">Kasir (Pembayaran)</option>
                            </select>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username Login</label>
                        <input type="text" class="form-control" id="username" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" id="labelPassword">Password</label>
                        <input type="password" class="form-control" id="password" required autocomplete="new-password">
                        <div class="form-text text-muted" id="helpPassword" style="display: none;">
                            Kosongkan jika tidak ingin mengubah password.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanUser()">Simpan User</button>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.19.0/js/md5.min.js"></script>

<script>
const API_URL = 'api.php/records/users';
const modal = new bootstrap.Modal(document.getElementById('modalUser'));

function loadData() {
    document.getElementById('loading').style.display = 'block';
    document.querySelector('#tabelUser tbody').innerHTML = '';

    fetch(API_URL + '?order=role,asc&order=nama_lengkap,asc')
        .then(r => r.json())
        .then(data => {
            let html = '';
            data.records.forEach((item, index) => {
                // Badge warna-warni untuk role berbeda
                let badgeColor = 'bg-secondary';
                if (item.role === 'admin') badgeColor = 'bg-danger';
                if (item.role === 'dokter') badgeColor = 'bg-success';
                if (item.role === 'resepsionis') badgeColor = 'bg-info text-dark';
                if (item.role === 'kasir') badgeColor = 'bg-warning text-dark';
                if (item.role === 'apoteker') badgeColor = 'bg-primary';

                // Format tanggal
                let tgl = item.created_at ? item.created_at.substring(0, 10) : '-';

                html += `<tr>
                    <td>${index + 1}</td>
                    <td class="fw-bold">${item.nama_lengkap}</td>
                    <td>${item.username}</td>
                    <td><span class="badge ${badgeColor}">${item.role.toUpperCase()}</span></td>
                    <td>${tgl}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="bukaEdit(${item.id}, '${item.nama_lengkap}', '${item.username}', '${item.role}')">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        ${item.id != <?= $_SESSION['user_id']; ?> ?
                            `<button class="btn btn-sm btn-danger" onclick="hapusUser(${item.id}, '${item.role}')">
                                <i class="bi bi-trash"></i>
                            </button>` : ''
                        }
                    </td>
                </tr>`;
            });
            document.querySelector('#tabelUser tbody').innerHTML = html;
            document.getElementById('loading').style.display = 'none';
        });
}

function simpanUser() {
    const id = document.getElementById('id_user').value;
    const username = document.getElementById('username').value;
    const passwordPlain = document.getElementById('password').value;
    const role = document.getElementById('role').value;
    const nama = document.getElementById('nama_lengkap').value;

    if (!nama || !username || !role) { alert("Nama, Username, dan Role wajib diisi!"); return; }

    let dataKirim = { username: username, nama_lengkap: nama, role: role };

    // Logika Password
    if (id) {
        // MODE EDIT: Hanya kirim password jika diisi
        if (passwordPlain !== "") {
            dataKirim.password = md5(passwordPlain); // Hash MD5 di sisi client
        }
    } else {
        // MODE TAMBAH BARU: Password wajib
        if (passwordPlain === "") { alert("Password wajib diisi untuk user baru!"); return; }
        dataKirim.password = md5(passwordPlain);
    }

    fetch(API_URL + (id ? '/' + id : ''), {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dataKirim)
    })
    .then(r => r.json())
    .then(hasil => {
        // API akan return error code jika username duplikat (biasanya error 1062)
        if (hasil.error && hasil.code === 1062) {
            alert("Gagal: Username '" + username + "' sudah digunakan!");
        } else if (hasil > 0 || hasil === 1) { // Insert returns ID (angka > 0), Update returns 1 (affected rows)
             modal.hide(); loadData(); alert('Data user tersimpan!');
        } else if (id && hasil === 0) { // Update tapi tidak ada perubahan data
             modal.hide();
        } else {
             alert("Terjadi kesalahan saat menyimpan."); console.log(hasil);
        }
    })
    .catch(e => alert("Gagal koneksi API."));
}

function hapusUser(id, role) {
    if (role === 'dokter') {
        alert("PERINGATAN: User dokter sebaiknya dihapus dari menu 'Data Dokter' agar data terkaitnya bersih.");
        return;
    }
    if (confirm('Yakin hapus user ini? Ia tidak akan bisa login lagi.')) {
        fetch(API_URL + '/' + id, { method: 'DELETE' }).then(() => loadData());
    }
}

function bukaModalTambah() {
    document.getElementById('modalLabel').innerText = 'Tambah User Baru';
    document.getElementById('formUser').reset();
    document.getElementById('id_user').value = '';
    document.getElementById('password').required = true;
    document.getElementById('helpPassword').style.display = 'none';
    document.getElementById('username').removeAttribute('readonly');
    modal.show();
} 

function bukaEdit(id, nama, username, role) {
    document.getElementById('modalLabel').innerText = 'Edit User / Reset Password';
    document.getElementById('id_user').value = id;
    document.getElementById('nama_lengkap').value = nama;
    document.getElementById('username').value = username;
    document.getElementById('username').setAttribute('readonly', true); // Username sebaiknya tidak diubah-ubah
    document.getElementById('role').value = role;

    // Password tidak wajib diisi saat edit
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('helpPassword').style.display = 'block';

    modal.show();
}

loadData();
</script>