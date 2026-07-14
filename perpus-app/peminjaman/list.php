<?php
// peminjaman/list.php
require_once '../auth.php';
require_once '../config/database.php';

try {
    // JOIN ke tabel anggota dan buku untuk mendapatkan nama anggota dan judul buku
    $stmt = $pdo->query("
        SELECT 
            p.id_peminjaman,
            a.nama AS nama_anggota,
            b.judul AS judul_buku,
            p.tanggal_pinjam,
            p.tanggal_jatuh_tempo,
            p.status,
            CASE WHEN p.tanggal_jatuh_tempo < CURDATE() AND p.status = 'dipinjam' THEN 1 ELSE 0 END AS is_overdue
        FROM peminjaman p
        JOIN anggota a ON p.id_anggota = a.id_anggota
        JOIN buku b ON p.id_buku = b.id_buku
        ORDER BY p.id_peminjaman DESC
    ");
    $peminjaman_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Gagal mengambil data peminjaman: " . $e->getMessage();
}

$page_title = 'Data Peminjaman';
$active_page = 'peminjaman';

include '../header.php';
include '../sidebar.php';
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-dark mb-1">Transaksi Peminjaman</h4>
            <p class="text-secondary mb-0">Kelola seluruh transaksi peminjaman dan pengembalian buku.</p>
        </div>
        <a href="tambah.php" class="btn btn-primary px-4 py-2">
            <i class="fa-solid fa-plus-circle me-2"></i>Peminjaman Baru
        </a>
    </div>
</div>

<!-- Alert Notifikasi -->
<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Sukses!</strong> <?= htmlspecialchars($_SESSION['success_msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Gagal!</strong> <?= htmlspecialchars($_SESSION['error_msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<?php if (isset($error_msg)): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<!-- Keterangan Highlight -->
<div class="d-flex align-items-center gap-3 mb-3">
    <span class="d-flex align-items-center gap-1 small text-muted">
        <span style="width: 14px; height: 14px; border-radius: 3px; background: #fee2e2; border: 1px solid #fca5a5; display: inline-block;"></span>
        Baris merah = Melewati jatuh tempo &amp; belum dikembalikan
    </span>
    <span class="d-flex align-items-center gap-1 small text-muted">
        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Dipinjam</span>
        <span class="badge bg-success-subtle text-success border border-success-subtle">Dikembalikan</span>
    </span>
</div>

<!-- Tabel Peminjaman -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Anggota</th>
                    <th>Judul Buku</th>
                    <th style="width: 140px;" class="text-center">Tgl Pinjam</th>
                    <th style="width: 150px;" class="text-center">Jatuh Tempo</th>
                    <th style="width: 140px;" class="text-center">Status</th>
                    <th style="width: 140px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($peminjaman_list)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">Belum ada transaksi peminjaman. Klik "Peminjaman Baru" untuk mulai mencatat.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1;
                    foreach ($peminjaman_list as $p): 
                        $row_class = ($p['is_overdue']) ? 'table-danger' : '';
                    ?>
                        <tr class="<?= $row_class ?>">
                            <td><?= $no++ ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($p['nama_anggota']) ?></td>
                            <td><?= htmlspecialchars($p['judul_buku']) ?></td>
                            <td class="text-center"><?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></td>
                            <td class="text-center">
                                <?php if ($p['is_overdue']): ?>
                                    <span class="fw-semibold text-danger">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i><?= date('d M Y', strtotime($p['tanggal_jatuh_tempo'])) ?>
                                    </span>
                                <?php else: ?>
                                    <?= date('d M Y', strtotime($p['tanggal_jatuh_tempo'])) ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['status'] === 'dipinjam'): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
                                        <i class="fa-solid fa-clock me-1"></i>Dipinjam
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                        <i class="fa-solid fa-check-circle me-1"></i>Dikembalikan
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['status'] === 'dipinjam'): ?>
                                    <a href="kembalikan.php?id=<?= $p['id_peminjaman'] ?>" 
                                       onclick="return confirm('Konfirmasi pengembalian buku oleh <?= addslashes(htmlspecialchars($p['nama_anggota'])) ?>?')"
                                       class="btn btn-success btn-sm px-3">
                                        <i class="fa-solid fa-rotate-left me-1"></i>Kembalikan
                                    </a>
                                <?php else: ?>
                                    <span class="text-secondary small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../footer.php'; ?>
