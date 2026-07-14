<?php
// anggota/list.php
require_once '../auth.php';
require_once '../config/database.php';

try {
    $stmt = $pdo->query("SELECT * FROM anggota ORDER BY id_anggota DESC");
    $anggota_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Gagal mengambil data anggota: " . $e->getMessage();
}

$page_title = 'Data Anggota';
$active_page = 'anggota';

include '../header.php';
include '../sidebar.php';
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-dark mb-1">Daftar Anggota</h4>
            <p class="text-secondary mb-0">Kelola informasi data anggota perpustakaan.</p>
        </div>
        <a href="tambah.php" class="btn btn-primary px-4 py-2">
            <i class="fa-solid fa-user-plus me-2"></i>Tambah Anggota Baru
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

<!-- Tabel Data Anggota -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Anggota</th>
                    <th>Alamat</th>
                    <th style="width: 150px;">No Telepon</th>
                    <th>Email</th>
                    <th style="width: 180px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($anggota_list)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">Belum ada data anggota. Klik tombol "Tambah Anggota Baru" untuk menambahkan data.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1;
                    foreach ($anggota_list as $anggota): 
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($anggota['nama']) ?></td>
                            <td><?= htmlspecialchars($anggota['alamat']) ?></td>
                            <td><?= htmlspecialchars($anggota['no_telepon']) ?></td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($anggota['email']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($anggota['email']) ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="edit.php?id=<?= $anggota['id_anggota'] ?>" class="btn btn-warning btn-sm d-flex align-items-center gap-1.5 px-3">
                                        <i class="fa-solid fa-user-pen"></i>Edit
                                    </a>
                                    <a href="hapus.php?id=<?= $anggota['id_anggota'] ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus anggota \'<?= addslashes(htmlspecialchars($anggota['nama'])) ?>\'?')" 
                                       class="btn btn-danger btn-sm d-flex align-items-center gap-1.5 px-3">
                                        <i class="fa-solid fa-user-minus"></i>Hapus
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
