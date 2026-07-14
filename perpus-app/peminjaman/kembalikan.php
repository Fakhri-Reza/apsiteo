<?php
// peminjaman/kembalikan.php
require_once '../auth.php';
require_once '../config/database.php';

$id_peminjaman = $_GET['id'] ?? '';

if (empty($id_peminjaman)) {
    header("Location: list.php");
    exit;
}

try {
    // Ambil data peminjaman (hanya yang masih berstatus 'dipinjam')
    $stmt = $pdo->prepare("SELECT * FROM peminjaman WHERE id_peminjaman = :id AND status = 'dipinjam' LIMIT 1");
    $stmt->execute(['id' => $id_peminjaman]);
    $peminjaman = $stmt->fetch();

    if (!$peminjaman) {
        $_SESSION['error_msg'] = 'Data peminjaman tidak ditemukan atau sudah dikembalikan!';
        header("Location: list.php");
        exit;
    }

    $tanggal_kembali     = date('Y-m-d');
    $tanggal_jatuh_tempo = $peminjaman['tanggal_jatuh_tempo'];

    // Hitung selisih hari keterlambatan
    $status_telat     = 'tidak';
    $jumlah_hari_telat = 0;
    $jumlah_denda      = 0;

    if ($tanggal_kembali > $tanggal_jatuh_tempo) {
        $status_telat      = 'ya';
        $date_jatuh        = new DateTime($tanggal_jatuh_tempo);
        $date_kembali      = new DateTime($tanggal_kembali);
        $jumlah_hari_telat = (int) $date_jatuh->diff($date_kembali)->days;
        $jumlah_denda      = $jumlah_hari_telat * 2000; // Rp 2.000 per hari
    }

    // =====================================================
    // Mulai PDO Transaction (semua langkah harus berhasil)
    // =====================================================
    $pdo->beginTransaction();

    // Langkah 1: INSERT ke tabel pengembalian
    $stmt_kembali = $pdo->prepare("
        INSERT INTO pengembalian (id_peminjaman, tanggal_kembali, status_telat)
        VALUES (:id_peminjaman, :tanggal_kembali, :status_telat)
    ");
    $stmt_kembali->execute([
        'id_peminjaman'  => $id_peminjaman,
        'tanggal_kembali' => $tanggal_kembali,
        'status_telat'   => $status_telat,
    ]);
    $id_pengembalian = $pdo->lastInsertId();

    // Langkah 2: INSERT ke tabel denda jika terlambat
    if ($status_telat === 'ya') {
        $stmt_denda = $pdo->prepare("
            INSERT INTO denda (id_pengembalian, jumlah_hari_telat, jumlah_denda, status_bayar)
            VALUES (:id_pengembalian, :jumlah_hari_telat, :jumlah_denda, 'belum')
        ");
        $stmt_denda->execute([
            'id_pengembalian'  => $id_pengembalian,
            'jumlah_hari_telat' => $jumlah_hari_telat,
            'jumlah_denda'     => $jumlah_denda,
        ]);
    }

    // Langkah 3: UPDATE status peminjaman → 'kembali'
    $stmt_upd = $pdo->prepare("UPDATE peminjaman SET status = 'kembali' WHERE id_peminjaman = :id");
    $stmt_upd->execute(['id' => $id_peminjaman]);

    // Langkah 4: Kembalikan stok buku +1
    $stmt_stok = $pdo->prepare("UPDATE buku SET stok = stok + 1 WHERE id_buku = :id_buku");
    $stmt_stok->execute(['id_buku' => $peminjaman['id_buku']]);

    // Commit jika semua langkah berhasil
    $pdo->commit();
    // =====================================================
    // Akhir PDO Transaction
    // =====================================================

    // Susun pesan hasil sesuai permintaan
    if ($status_telat === 'ya') {
        $denda_format = 'Rp' . number_format($jumlah_denda, 0, ',', '.');
        $_SESSION['success_msg'] = "Pengembalian berhasil, denda {$denda_format} untuk keterlambatan {$jumlah_hari_telat} hari.";
    } else {
        $_SESSION['success_msg'] = 'Pengembalian berhasil, tidak ada denda.';
    }

} catch (PDOException $e) {
    // ROLLBACK jika salah satu langkah gagal
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error_msg'] = 'Gagal memproses pengembalian: ' . $e->getMessage();
}

header("Location: list.php");
exit;
