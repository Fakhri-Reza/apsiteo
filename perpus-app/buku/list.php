<?php
// buku/list.php
require_once '../auth.php';
require_once '../config/database.php';

try {
    $stmt = $pdo->query("SELECT * FROM buku ORDER BY id_buku DESC");
    $buku_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Gagal mengambil data buku: " . $e->getMessage();
}

$page_title = 'Data Buku';
$active_page = 'buku';

include '../header.php';
include '../sidebar.php';
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-dark mb-1">Daftar Buku</h4>
            <p class="text-secondary mb-0">Kelola koleksi buku yang tersedia di perpustakaan.</p>
        </div>
        <a href="tambah.php" class="btn btn-primary px-4 py-2">
            <i class="fa-solid fa-plus-circle me-2"></i>Tambah Buku Baru
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
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<!-- Tabel Data Buku -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Judul Buku</th>
                    <th>Pengarang</th>
                    <th>Penerbit</th>
                    <th style="width: 120px;" class="text-center">Tahun Terbit</th>
                    <th>Kategori</th>
                    <th style="width: 100px;" class="text-center">Stok</th>
                    <th style="width: 180px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($buku_list)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">Belum ada data buku. Klik tombol "Tambah Buku Baru" untuk menambahkan data.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1;
                    foreach ($buku_list as $buku): 
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($buku['judul']) ?></td>
                            <td><?= htmlspecialchars($buku['pengarang']) ?></td>
                            <td><?= htmlspecialchars($buku['penerbit']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($buku['tahun_terbit']) ?></td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1.5 rounded-pill fs-7">
                                    <?= htmlspecialchars($buku['kategori']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($buku['stok'] > 0): ?>
                                    <span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-3">
                                        <?= htmlspecialchars($buku['stok']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger px-2.5 py-1.5 rounded-3">
                                        Habis
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="edit.php?id=<?= $buku['id_buku'] ?>" class="btn btn-warning btn-sm d-flex align-items-center gap-1.5 px-3">
                                        <i class="fa-solid fa-pen-to-square"></i>Edit
                                    </a>
                                    <a href="hapus.php?id=<?= $buku['id_buku'] ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus buku \'<?= addslashes(htmlspecialchars($buku['judul'])) ?>\'?')" 
                                       class="btn btn-danger btn-sm d-flex align-items-center gap-1.5 px-3">
                                        <i class="fa-solid fa-trash-can"></i>Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include '../footer.php';
?>
