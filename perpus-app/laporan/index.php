<?php
require_once '../auth.php';
require_once '../config/database.php';

$page_title = 'Laporan';
$active_page = 'laporan';

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

try {
    $stmt = $pdo->query("SELECT * FROM buku ORDER BY judul ASC");
    $buku_list = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT * FROM anggota ORDER BY nama ASC");
    $anggota_list = $stmt->fetchAll();

    $where = [];
    $params = [];
    if (!empty($start_date)) {
        $where[] = 'p.tanggal_pinjam >= :start_date';
        $params[':start_date'] = $start_date;
    }
    if (!empty($end_date)) {
        $where[] = 'p.tanggal_pinjam <= :end_date';
        $params[':end_date'] = $end_date;
    }

    $sql = "
        SELECT 
            p.id_peminjaman,
            a.nama AS nama_anggota,
            b.judul AS judul_buku,
            p.tanggal_pinjam,
            p.tanggal_jatuh_tempo,
            p.status
        FROM peminjaman p
        JOIN anggota a ON p.id_anggota = a.id_anggota
        JOIN buku b ON p.id_buku = b.id_buku";

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY p.tanggal_pinjam DESC, p.id_peminjaman DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $peminjaman_list = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT 
            pg.id_pengembalian,
            p.id_peminjaman,
            a.nama AS nama_anggota,
            b.judul AS judul_buku,
            pg.tanggal_kembali,
            pg.status_telat,
            d.jumlah_denda,
            d.status_bayar
        FROM pengembalian pg
        JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
        JOIN anggota a ON p.id_anggota = a.id_anggota
        JOIN buku b ON p.id_buku = b.id_buku
        LEFT JOIN denda d ON pg.id_pengembalian = d.id_pengembalian
        ORDER BY pg.tanggal_kembali DESC, pg.id_pengembalian DESC
    ");
    $pengembalian_list = $stmt->fetchAll();

    $stmt = $pdo->query("
        SELECT 
            COALESCE(SUM(CASE WHEN d.status_bayar = 'lunas' THEN d.jumlah_denda ELSE 0 END), 0) AS total_denda_terkumpul,
            COALESCE(SUM(CASE WHEN d.status_bayar = 'belum' THEN d.jumlah_denda ELSE 0 END), 0) AS total_denda_belum_bayar
        FROM denda d
    ");
    $denda_summary = $stmt->fetch();

    $stmt = $pdo->query("
        SELECT 
            d.id_denda,
            pg.id_pengembalian,
            a.nama AS nama_anggota,
            b.judul AS judul_buku,
            d.jumlah_hari_telat,
            d.jumlah_denda,
            d.status_bayar
        FROM denda d
        JOIN pengembalian pg ON d.id_pengembalian = pg.id_pengembalian
        JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
        JOIN anggota a ON p.id_anggota = a.id_anggota
        JOIN buku b ON p.id_buku = b.id_buku
        ORDER BY d.id_denda DESC
    ");
    $denda_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = 'Gagal mengambil data laporan: ' . $e->getMessage();
}

include '../header.php';
include '../sidebar.php';
?>

<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">Laporan Administrasi Perpustakaan</h4>
            <p class="text-secondary mb-0">Pantau data buku, anggota, peminjaman, pengembalian, dan denda dalam satu tempat.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= $base_path ?>katalog.php" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-book-open me-1"></i>Buka Katalog
            </a>
            <div class="text-muted small">Terakhir diperbarui: <?= date('d M Y H:i') ?></div>
        </div>
    </div>
</div>

<?php if (isset($error_msg)): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <ul class="nav nav-tabs flex-wrap" id="laporanTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="buku-tab" data-bs-toggle="tab" data-bs-target="#laporan-buku" type="button" role="tab">1. Data Buku</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="anggota-tab" data-bs-toggle="tab" data-bs-target="#laporan-anggota" type="button" role="tab">2. Data Anggota</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="peminjaman-tab" data-bs-toggle="tab" data-bs-target="#laporan-peminjaman" type="button" role="tab">3. Peminjaman</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pengembalian-tab" data-bs-toggle="tab" data-bs-target="#laporan-pengembalian" type="button" role="tab">4. Pengembalian</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="denda-tab" data-bs-toggle="tab" data-bs-target="#laporan-denda" type="button" role="tab">5. Denda</button>
        </li>
    </ul>

    <div class="tab-content pt-4">
        <div class="tab-pane fade show active" id="laporan-buku" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 report-actions">
                <h5 class="fw-bold text-dark mb-0">Laporan Data Buku</h5>
                <button type="button" class="btn btn-outline-dark btn-sm print-report" data-target="#laporan-buku">
                    <i class="fa-solid fa-print me-1"></i>Print
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Penerbit</th>
                            <th>Kategori</th>
                            <th class="text-center">Tahun</th>
                            <th class="text-center">Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($buku_list)): ?>
                            <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada data buku.</td></tr>
                        <?php else: $no = 1; foreach ($buku_list as $buku): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($buku['judul']) ?></td>
                                <td><?= htmlspecialchars($buku['pengarang']) ?></td>
                                <td><?= htmlspecialchars($buku['penerbit']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($buku['kategori']) ?></span></td>
                                <td class="text-center"><?= htmlspecialchars($buku['tahun_terbit']) ?></td>
                                <td class="text-center">
                                    <?php if ((int) $buku['stok'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success"><?= htmlspecialchars($buku['stok']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger">Habis</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="laporan-anggota" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 report-actions">
                <h5 class="fw-bold text-dark mb-0">Laporan Data Anggota</h5>
                <button type="button" class="btn btn-outline-dark btn-sm print-report" data-target="#laporan-anggota">
                    <i class="fa-solid fa-print me-1"></i>Print
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>No. Telepon</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($anggota_list)): ?>
                            <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada data anggota.</td></tr>
                        <?php else: $no = 1; foreach ($anggota_list as $anggota): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($anggota['nama']) ?></td>
                                <td><?= htmlspecialchars($anggota['alamat']) ?></td>
                                <td><?= htmlspecialchars($anggota['no_telepon']) ?></td>
                                <td><?= htmlspecialchars($anggota['email']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="laporan-peminjaman" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 report-actions">
                <h5 class="fw-bold text-dark mb-0">Laporan Peminjaman</h5>
                <button type="button" class="btn btn-outline-dark btn-sm print-report" data-target="#laporan-peminjaman">
                    <i class="fa-solid fa-print me-1"></i>Print
                </button>
            </div>

            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Tanggal Awal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fa-solid fa-filter me-1"></i>Tampilkan
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th class="text-center">Tgl Pinjam</th>
                            <th class="text-center">Jatuh Tempo</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($peminjaman_list)): ?>
                            <tr><td colspan="6" class="text-center text-secondary py-4">Tidak ada data peminjaman pada rentang tanggal ini.</td></tr>
                        <?php else: $no = 1; foreach ($peminjaman_list as $p): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($p['nama_anggota']) ?></td>
                                <td><?= htmlspecialchars($p['judul_buku']) ?></td>
                                <td class="text-center"><?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></td>
                                <td class="text-center"><?= date('d M Y', strtotime($p['tanggal_jatuh_tempo'])) ?></td>
                                <td class="text-center">
                                    <?php if ($p['status'] === 'dipinjam'): ?>
                                        <span class="badge bg-warning-subtle text-warning">Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success">Dikembalikan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="laporan-pengembalian" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 report-actions">
                <h5 class="fw-bold text-dark mb-0">Laporan Pengembalian</h5>
                <button type="button" class="btn btn-outline-dark btn-sm print-report" data-target="#laporan-pengembalian">
                    <i class="fa-solid fa-print me-1"></i>Print
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th class="text-center">Tgl Kembali</th>
                            <th class="text-center">Status Telat</th>
                            <th class="text-center">Denda</th>
                            <th class="text-center">Status Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pengembalian_list)): ?>
                            <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada data pengembalian.</td></tr>
                        <?php else: $no = 1; foreach ($pengembalian_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['nama_anggota']) ?></td>
                                <td><?= htmlspecialchars($row['judul_buku']) ?></td>
                                <td class="text-center"><?= date('d M Y', strtotime($row['tanggal_kembali'])) ?></td>
                                <td class="text-center">
                                    <?php if ($row['status_telat'] === 'ya'): ?>
                                        <span class="badge bg-danger-subtle text-danger">Ya</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success">Tidak</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">Rp <?= number_format((float) ($row['jumlah_denda'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <?php if (($row['status_bayar'] ?? 'belum') === 'lunas'): ?>
                                        <span class="badge bg-success-subtle text-success">Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning">Belum</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="laporan-denda" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 report-actions">
                <h5 class="fw-bold text-dark mb-0">Laporan Denda</h5>
                <button type="button" class="btn btn-outline-dark btn-sm print-report" data-target="#laporan-denda">
                    <i class="fa-solid fa-print me-1"></i>Print
                </button>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="border rounded-4 p-3 bg-success-subtle">
                        <div class="text-muted small">Total Denda Terkumpul</div>
                        <div class="fs-4 fw-bold text-success">Rp <?= number_format((float) ($denda_summary['total_denda_terkumpul'] ?? 0), 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-4 p-3 bg-warning-subtle">
                        <div class="text-muted small">Total Denda Belum Dibayar</div>
                        <div class="fs-4 fw-bold text-warning">Rp <?= number_format((float) ($denda_summary['total_denda_belum_bayar'] ?? 0), 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th class="text-center">Hari Telat</th>
                            <th class="text-center">Jumlah Denda</th>
                            <th class="text-center">Status Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($denda_list)): ?>
                            <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada data denda.</td></tr>
                        <?php else: $no = 1; foreach ($denda_list as $item): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($item['nama_anggota']) ?></td>
                                <td><?= htmlspecialchars($item['judul_buku']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($item['jumlah_hari_telat']) ?></td>
                                <td class="text-center">Rp <?= number_format((float) $item['jumlah_denda'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <?php if ($item['status_bayar'] === 'lunas'): ?>
                                        <span class="badge bg-success-subtle text-success">Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning">Belum</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.print-report').forEach(function (button) {
            button.addEventListener('click', function () {
                const target = this.getAttribute('data-target');
                const trigger = document.querySelector('#laporanTabs button[data-bs-target="' + target + '"]');
                if (trigger) {
                    const tab = new bootstrap.Tab(trigger);
                    tab.show();
                }
                setTimeout(function () {
                    window.print();
                }, 200);
            });
        });
    });
</script>

<style>
    @media print {
        body {
            background: #fff !important;
        }

        #sidebar-wrapper,
        .navbar-custom,
        .report-actions,
        .nav-tabs,
        .btn,
        .form-control,
        .form-label {
            display: none !important;
        }

        #page-content-wrapper {
            width: 100% !important;
            min-height: auto !important;
            overflow: visible !important;
        }

        .container-fluid {
            padding: 0 !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }

        .table {
            font-size: 12px;
        }

        .tab-pane {
            display: block !important;
            opacity: 1 !important;
        }
    }
</style>

<?php include '../footer.php'; ?>
