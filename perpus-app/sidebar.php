<?php
// sidebar.php
// Pastikan variabel $base_path sudah didefinisikan (dari auth.php)
$base_path = $base_path ?? '';
$active_page = $active_page ?? '';
?>

<!-- Sidebar -->
<div id="sidebar-wrapper">
    <div class="sidebar-heading">
        <i class="fa-solid fa-book-bookmark text-primary fs-4"></i>
        <span>Lib-App Admin</span>
    </div>
    <div class="list-group list-group-flush flex-grow-1">
        <a href="<?= $base_path ?>dashboard.php" class="list-group-item list-group-item-action <?= $active_page === 'dashboard' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?= $base_path ?>katalog.php" class="list-group-item list-group-item-action <?= $active_page === 'katalog' ? 'active' : '' ?>">
            <i class="fa-solid fa-book"></i>
            <span>Katalog Publik</span>
        </a>
        <a href="<?= $base_path ?>buku/list.php" class="list-group-item list-group-item-action <?= $active_page === 'buku' ? 'active' : '' ?>">
            <i class="fa-solid fa-book-open"></i>
            <span>Data Buku</span>
        </a>
        <a href="<?= $base_path ?>anggota/list.php" class="list-group-item list-group-item-action <?= $active_page === 'anggota' ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>Data Anggota</span>
        </a>
        <a href="<?= $base_path ?>peminjaman/list.php" class="list-group-item list-group-item-action <?= $active_page === 'peminjaman' ? 'active' : '' ?>">
            <i class="fa-solid fa-handshake"></i>
            <span>Peminjaman</span>
        </a>
        <a href="<?= $base_path ?>laporan/index.php" class="list-group-item list-group-item-action <?= $active_page === 'laporan' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-line"></i>
            <span>Laporan</span>
        </a>
    </div>
    
    <!-- Logout diposisikan di bagian paling bawah sidebar -->
    <div class="list-group list-group-flush mb-3">
        <a href="<?= $base_path ?>logout.php" class="list-group-item list-group-item-action text-danger border-0">
            <i class="fa-solid fa-right-from-bracket text-danger"></i>
            <span>Keluar (Logout)</span>
        </a>
    </div>
</div>

<!-- Page Content Wrapper -->
<div id="page-content-wrapper">
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid justify-content-between">
            <span class="navbar-brand mb-0 h1 fs-5 text-dark fw-bold">
                <?= $page_title ?? 'Sistem Informasi Perpustakaan' ?>
            </span>
            <div class="admin-profile">
                <div class="admin-avatar">
                    <?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="d-none d-sm-block">
                    <span class="fw-semibold text-dark d-block" style="line-height: 1.2;"><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></span>
                    <small class="text-muted font-monospace" style="font-size: 0.75rem;">Administrator</small>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Fluid Container -->
    <div class="container-fluid py-4">
