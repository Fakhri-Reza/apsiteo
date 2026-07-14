<?php
// buku/tambah.php
require_once '../auth.php';
require_once '../config/database.php';

$error = '';
$judul = '';
$pengarang = '';
$penerbit = '';
$tahun_terbit = '';
$kategori = '';
$stok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $pengarang = trim($_POST['pengarang'] ?? '');
    $penerbit = trim($_POST['penerbit'] ?? '');
    $tahun_terbit = trim($_POST['tahun_terbit'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $stok = trim($_POST['stok'] ?? '');

    if (empty($judul) || empty($pengarang) || empty($penerbit) || empty($tahun_terbit) || empty($kategori) || $stok === '') {
        $error = 'Semua kolom formulir wajib diisi!';
    } elseif (!is_numeric($tahun_terbit) || intval($tahun_terbit) < 1000 || intval($tahun_terbit) > intval(date('Y')) + 5) {
        $error = 'Tahun terbit harus berupa tahun yang valid!';
    } elseif (!is_numeric($stok) || intval($stok) < 0) {
        $error = 'Stok tidak boleh kurang dari 0!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, kategori, stok) VALUES (:judul, :pengarang, :penerbit, :tahun_terbit, :kategori, :stok)");
            $stmt->execute([
                'judul' => $judul,
                'pengarang' => $pengarang,
                'penerbit' => $penerbit,
                'tahun_terbit' => intval($tahun_terbit),
                'kategori' => $kategori,
                'stok' => intval($stok)
            ]);

            $_SESSION['success_msg'] = 'Buku berhasil ditambahkan!';
            header("Location: list.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan buku: ' . $e->getMessage();
        }
    }
}

$page_title = 'Tambah Buku';
$active_page = 'buku';

include '../header.php';
include '../sidebar.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold text-dark mb-1">Tambah Buku Baru</h4>
        <p class="text-secondary">Tambahkan koleksi buku baru ke database perpustakaan.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal!</strong> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="tambah.php" method="POST" autocomplete="off">
                <div class="mb-3">
                    <label for="judul" class="form-label fw-semibold">Judul Buku</label>
                    <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($judul) ?>" required placeholder="Masukkan judul buku">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pengarang" class="form-label fw-semibold">Pengarang</label>
                        <input type="text" class="form-control" id="pengarang" name="pengarang" value="<?= htmlspecialchars($pengarang) ?>" required placeholder="Nama pengarang">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="penerbit" class="form-label fw-semibold">Penerbit</label>
                        <input type="text" class="form-control" id="penerbit" name="penerbit" value="<?= htmlspecialchars($penerbit) ?>" required placeholder="Nama penerbit">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="tahun_terbit" class="form-label fw-semibold">Tahun Terbit</label>
                        <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" value="<?= htmlspecialchars($tahun_terbit) ?>" required placeholder="Contoh: 2024">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="kategori" class="form-label fw-semibold">Kategori</label>
                        <input type="text" class="form-control" id="kategori" name="kategori" value="<?= htmlspecialchars($kategori) ?>" required placeholder="Kategori/Genre">
                    </div>
                    <div class="col-md-4 mb-4">
                        <label for="stok" class="form-label fw-semibold">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($stok) ?>" min="0" required placeholder="Jumlah stok >= 0">
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Buku
                    </button>
                    <a href="list.php" class="btn btn-outline-secondary px-4 py-2">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include '../footer.php';
?>
