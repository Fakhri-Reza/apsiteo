<?php
// anggota/tambah.php
require_once '../auth.php';
require_once '../config/database.php';

$error = '';
$nama = '';
$alamat = '';
$no_telepon = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($nama) || empty($alamat) || empty($no_telepon) || empty($email)) {
        $error = 'Semua kolom formulir wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid!';
    } elseif (!ctype_digit($no_telepon)) {
        $error = 'Nomor telepon hanya boleh berisi angka!';
    } else {
        try {
            // Cek keunikan email
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM anggota WHERE email = :email");
            $check_stmt->execute(['email' => $email]);
            $email_exists = $check_stmt->fetchColumn() > 0;

            if ($email_exists) {
                $error = 'Alamat email sudah terdaftar!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO anggota (nama, alamat, no_telepon, email) VALUES (:nama, :alamat, :no_telepon, :email)");
                $stmt->execute([
                    'nama' => $nama,
                    'alamat' => $alamat,
                    'no_telepon' => $no_telepon,
                    'email' => $email
                ]);

                $_SESSION['success_msg'] = 'Anggota berhasil ditambahkan!';
                header("Location: list.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan anggota: ' . $e->getMessage();
        }
    }
}

$page_title = 'Tambah Anggota';
$active_page = 'anggota';

include '../header.php';
include '../sidebar.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold text-dark mb-1">Tambah Anggota Baru</h4>
        <p class="text-secondary">Daftarkan anggota perpustakaan baru.</p>
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
                    <label for="nama" class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required placeholder="Masukkan nama lengkap">
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label fw-semibold">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required placeholder="Alamat lengkap anggota"><?= htmlspecialchars($alamat) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="no_telepon" class="form-label fw-semibold">No Telepon</label>
                        <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="<?= htmlspecialchars($no_telepon) ?>" required placeholder="Contoh: 08123456789">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder="Contoh: nama@domain.com">
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Anggota
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
