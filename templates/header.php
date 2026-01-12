<!DOCTYPE html>
<html lang="id">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM KLINIK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar-link { color: white; text-decoration: none; display: block; padding: 10px 15px; }
        .sidebar-link:hover { background-color: #495057; color: #ffc107; }
        .sidebar-link.active { background-color: #0d6efd; color: white; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-hospital-fill me-2"></i>SIM KLINIK
            </a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Halo, <?= $_SESSION['nama_lengkap'] ?? 'User'; ?>
                </span>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout <i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">