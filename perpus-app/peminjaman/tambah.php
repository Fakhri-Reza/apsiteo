<?php
// peminjaman/tambah.php
require_once '../auth.php';
require_once '../config/database.php';

$error = '';
$selected_anggota = '';
$selected_buku = '';
$tanggal_pinjam = date('Y-m-d');
$tanggal_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));

// Ambil daftar anggota untuk dropdown
try {
    $stmt_anggota = $pdo->query("SELECT id_anggota, nama FROM anggota ORDER BY nama ASC");
    $anggota_list = $stmt_anggota->fetchAll();
} catch (PDOException $e) {
    $anggota_list = [];
    $error = "Gagal memuat data anggota: " . $e->getMessage();
}

// Ambil daftar buku dengan stok > 0 untuk dropdown
try {
    $stmt_buku = $pdo->query("SELECT id_buku, judul, stok FROM buku WHERE stok > 0 ORDER BY judul ASC");
    $buku_list = $stmt_buku->fetchAll();
} catch (PDOException $e) {
    $buku_list = [];
    $error = "Gagal memuat data buku: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_anggota = trim($_POST['id_anggota'] ?? '');
    $selected_buku = trim($_POST['id_buku'] ?? '');

    if (empty($selected_anggota) || empty($selected_buku)) {
        $error = 'Anggota dan buku wajib dipilih!';
    } else {
        try {
            // Re-check stok buku di server untuk mencegah race condition
            $check_stok = $pdo->prepare("SELECT stok FROM buku WHERE id_buku = :id_buku FOR UPDATE");
            // Note: FOR UPDATE bekerja dalam transaction, lakukan di dalam blok begin/commit
            
            // =====================================================
            // Mulai PDO Transaction
            // =====================================================
            $pdo->beginTransaction();

            // Kunci baris buku dan re-check stok
            $stmt_check = $pdo->prepare("SELECT stok FROM buku WHERE id_buku = :id_buku LIMIT 1");
            $stmt_check->execute(['id_buku' => $selected_buku]);
            $buku_data = $stmt_check->fetch();

            if (!$buku_data || intval($buku_data['stok']) <= 0) {
                $pdo->rollBack();
                $error = 'Stok buku ini sudah habis! Silakan pilih buku lain.';
            } else {
                // 1. INSERT ke tabel peminjaman
                $stmt_insert = $pdo->prepare("
                    INSERT INTO peminjaman (id_anggota, id_buku, tanggal_pinjam, tanggal_jatuh_tempo, status)
                    VALUES (:id_anggota, :id_buku, :tanggal_pinjam, :tanggal_jatuh_tempo, 'dipinjam')
                ");
                $stmt_insert->execute([
                    'id_anggota' => $selected_anggota,
                    'id_buku' => $selected_buku,
                    'tanggal_pinjam' => $tanggal_pinjam,
                    'tanggal_jatuh_tempo' => $tanggal_jatuh_tempo,
                ]);

                // 2. UPDATE stok buku -1
                $stmt_update = $pdo->prepare("
                    UPDATE buku SET stok = stok - 1 WHERE id_buku = :id_buku
                ");
                $stmt_update->execute(['id_buku' => $selected_buku]);

                // Commit jika keduanya berhasil
                $pdo->commit();
                // =====================================================
                // Akhir PDO Transaction
                // =====================================================

                $_SESSION['success_msg'] = 'Transaksi peminjaman buku berhasil dicatat!';
                header("Location: list.php");
                exit;
            }
        } catch (PDOException $e) {
            // Rollback jika terjadi error apapun
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Gagal memproses peminjaman: ' . $e->getMessage();
        }
    }
}

$page_title = 'Tambah Peminjaman';
$active_page = 'peminjaman';

include '../header.php';
include '../sidebar.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold text-dark mb-1">Transaksi Peminjaman Baru</h4>
        <p class="text-secondary">Catat transaksi peminjaman buku untuk anggota perpustakaan.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal!</strong> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($anggota_list)): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Belum ada data anggota. <a href="../anggota/tambah.php">Tambahkan anggota terlebih dahulu.</a>
                </div>
            <?php elseif (empty($buku_list)): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Tidak ada buku yang tersedia (semua stok habis). <a href="../buku/list.php">Kelola stok buku di sini.</a>
                </div>
            <?php else: ?>
                <form action="tambah.php" method="POST" autocomplete="off">
                    <div class="mb-4">
                        <label for="id_anggota" class="form-label fw-semibold">Pilih Anggota</label>
                        <select class="form-select" id="id_anggota" name="id_anggota" required>
                            <option value="">-- Pilih Anggota --</option>
                            <?php foreach ($anggota_list as $anggota): ?>
                                <option value="<?= $anggota['id_anggota'] ?>" <?= ($selected_anggota == $anggota['id_anggota']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($anggota['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="id_buku" class="form-label fw-semibold">Pilih Buku</label>
                        <select class="form-select" id="id_buku" name="id_buku" required>
                            <option value="">-- Pilih Buku --</option>
                            <?php foreach ($buku_list as $buku): ?>
                                <option value="<?= $buku['id_buku'] ?>" <?= ($selected_buku == $buku['id_buku']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($buku['judul']) ?> — Stok: <?= $buku['stok'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted">Hanya buku dengan stok tersedia yang ditampilkan.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="tanggal_pinjam" class="form-label fw-semibold">Tanggal Pinjam</label>
                            <input type="date" class="form-control bg-light" id="tanggal_pinjam" name="tanggal_pinjam"
                                   value="<?= $tanggal_pinjam ?>" readonly>
                            <div class="form-text text-muted">Otomatis diisi hari ini.</div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="tanggal_jatuh_tempo" class="form-label fw-semibold">Jatuh Tempo</label>
                            <input type="date" class="form-control bg-light" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo"
                                   value="<?= $tanggal_jatuh_tempo ?>" readonly>
                            <div class="form-text text-muted">Otomatis +7 hari dari tanggal pinjam.</div>
                        </div>
                    </div>

                    <!-- Info Box Ringkasan -->
                    <div class="alert alert-primary bg-primary-subtle border-0 rounded-3 mb-4" role="alert">
                        <h6 class="mb-1 fw-semibold"><i class="fa-solid fa-circle-info me-2"></i>Informasi Transaksi</h6>
                        <p class="mb-0 small text-secondary">
                            Setelah disimpan, stok buku yang dipilih akan otomatis berkurang 1. 
                            Operasi ini menggunakan <strong>database transaction</strong> sehingga jika salah satu operasi gagal, 
                            seluruh transaksi akan dibatalkan secara otomatis.
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Peminjaman
                        </button>
                        <a href="list.php" class="btn btn-outline-secondary px-4 py-2">Batal</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Panel Info Kanan -->
    <div class="col-lg-5 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i>Ketentuan Peminjaman</h6>
            <ul class="list-unstyled mb-0 text-secondary small">
                <li class="mb-2 d-flex gap-2">
                    <i class="fa-solid fa-circle-check text-success mt-1 flex-shrink-0"></i>
                    <span>Setiap anggota dapat meminjam buku selama <strong>7 hari</strong>.</span>
                </li>
                <li class="mb-2 d-flex gap-2">
                    <i class="fa-solid fa-circle-check text-success mt-1 flex-shrink-0"></i>
                    <span>Hanya buku dengan stok <strong>tersedia (&gt;0)</strong> yang bisa dipinjam.</span>
                </li>
                <li class="mb-2 d-flex gap-2">
                    <i class="fa-solid fa-circle-check text-success mt-1 flex-shrink-0"></i>
                    <span>Buku yang sudah dipinjam akan mengurangi stok secara otomatis.</span>
                </li>
                <li class="d-flex gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-warning mt-1 flex-shrink-0"></i>
                    <span>Pengembalian yang melebihi jatuh tempo akan dikenakan <strong>denda</strong>.</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
