<?php
// dashboard.php
require_once 'auth.php';
require_once 'config/database.php';

// Inisialisasi variabel statistik
$total_buku = 0;
$total_anggota = 0;
$peminjaman_aktif = 0;
$total_denda = 0;

try {
    // 1. Total Buku
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM buku");
    $total_buku = $stmt->fetch()['total'];

    // 2. Total Anggota
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM anggota");
    $total_anggota = $stmt->fetch()['total'];

    // 3. Peminjaman Aktif (Status = 'dipinjam')
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dipinjam'");
    $peminjaman_aktif = $stmt->fetch()['total'];

    // 4. Total Denda Belum Dibayar (Status Bayar = 'belum')
    $stmt = $pdo->query("SELECT COALESCE(SUM(jumlah_denda), 0) AS total FROM denda WHERE status_bayar = 'belum'");
    $total_denda = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $error_msg = "Gagal mengambil beberapa data statistik: " . $e->getMessage();
}

$page_title = 'Dashboard';
$active_page = 'dashboard';

include 'header.php';
include 'sidebar.php';
?>

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-warning" role="alert">
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>
        
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="fw-bold text-dark mb-1">Halo, <?= htmlspecialchars($_SESSION['nama']) ?>! 👋</h3>
                    <p class="text-secondary mb-0">Selamat datang di panel admin Sistem Informasi Perpustakaan. Kelola buku, anggota, dan pantau peminjaman buku dengan mudah di sini.</p>
                </div>
                <div class="col-md-4 text-md-end text-start mt-3 mt-md-0">
                    <span class="text-muted font-monospace d-block" style="font-size: 0.85rem;">Hari ini: <?= date('d M Y') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Cards -->
<div class="row g-4">
    <!-- Card Total Buku -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted fw-semibold mb-2">Total Buku</h6>
                    <h3 class="fw-bold text-dark mb-0"><?= number_format($total_buku) ?></h3>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-4">
                    <i class="fa-solid fa-book fs-3"></i>
                </div>
            </div>
            <div class="stat-icon-bg text-primary">
                <i class="fa-solid fa-book"></i>
            </div>
        </div>
    </div>

    <!-- Card Total Anggota -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted fw-semibold mb-2">Total Anggota</h6>
                    <h3 class="fw-bold text-dark mb-0"><?= number_format($total_anggota) ?></h3>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-4">
                    <i class="fa-solid fa-users fs-3"></i>
                </div>
            </div>
            <div class="stat-icon-bg text-success">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>

    <!-- Card Peminjaman Aktif -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted fw-semibold mb-2">Peminjaman Aktif</h6>
                    <h3 class="fw-bold text-dark mb-0"><?= number_format($peminjaman_aktif) ?></h3>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-4">
                    <i class="fa-solid fa-handshake fs-3"></i>
                </div>
            </div>
            <div class="stat-icon-bg text-warning">
                <i class="fa-solid fa-handshake"></i>
            </div>
        </div>
    </div>

    <!-- Card Total Denda Belum Bayar -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card p-3">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted fw-semibold mb-2">Denda Belum Bayar</h6>
                    <h3 class="fw-bold text-danger mb-0">Rp <?= number_format($total_denda, 0, ',', '.') ?></h3>
                </div>
                <div class="bg-danger-subtle text-danger p-3 rounded-4">
                    <i class="fa-solid fa-rupiah-sign fs-3"></i>
                </div>
            </div>
            <div class="stat-icon-bg text-danger">
                <i class="fa-solid fa-rupiah-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Informasi Tambahan / Pintasan Cepat -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-link text-primary me-2"></i>Pintasan Cepat</h5>
            <div class="d-grid gap-2">
                <a href="peminjaman/tambah.php" class="btn btn-outline-primary text-start p-3 rounded-3 d-flex align-items-center justify-content-between transition-all">
                    <span><i class="fa-solid fa-plus-circle me-2"></i>Buat Transaksi Peminjaman Baru</span>
                    <i class="fa-solid fa-chevron-right fs-7"></i>
                </a>
                <a href="buku/tambah.php" class="btn btn-outline-success text-start p-3 rounded-3 d-flex align-items-center justify-content-between transition-all">
                    <span><i class="fa-solid fa-plus-circle me-2"></i>Tambah Buku Baru</span>
                    <i class="fa-solid fa-chevron-right fs-7"></i>
                </a>
                <a href="anggota/tambah.php" class="btn btn-outline-info text-start p-3 rounded-3 d-flex align-items-center justify-content-between transition-all">
                    <span><i class="fa-solid fa-plus-circle me-2"></i>Tambah Anggota Baru</span>
                    <i class="fa-solid fa-chevron-right fs-7"></i>
                </a>
                <a href="katalog.php" target="_blank" class="btn btn-outline-dark text-start p-3 rounded-3 d-flex align-items-center justify-content-between transition-all">
                    <span><i class="fa-solid fa-book-open me-2"></i>Buka Katalog Publik</span>
                    <i class="fa-solid fa-arrow-up-right-from-square fs-7"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-circle-info text-warning me-2"></i>Ketentuan Denda & Pengembalian</h5>
            <div class="text-secondary small">
                <ul class="ps-3 mb-0">
                    <li class="mb-2">Batas waktu peminjaman default ditentukan pada saat melakukan input transaksi peminjaman (tanggal jatuh tempo).</li>
                    <li class="mb-2">Jika tanggal pengembalian melewati tanggal jatuh tempo, status transaksi otomatis dianggap <strong>Telat</strong>.</li>
                    <li class="mb-2">Perhitungan denda dilakukan pada modul Pengembalian Buku secara otomatis berdasarkan jumlah hari telat.</li>
                    <li>Status denda akan tetap tercatat sebagai <strong>Belum Bayar</strong> sampai admin memproses pembayaran denda tersebut menjadi <strong>Lunas</strong>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>
