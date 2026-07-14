<?php
// anggota/hapus.php
require_once '../auth.php';
require_once '../config/database.php';

$id_anggota = $_GET['id'] ?? '';

if (!empty($id_anggota)) {
    try {
        $stmt = $pdo->prepare("DELETE FROM anggota WHERE id_anggota = :id");
        $stmt->execute(['id' => $id_anggota]);

        $_SESSION['success_msg'] = 'Anggota berhasil dihapus!';
    } catch (PDOException $e) {
        // Jika gagal karena constraint FK (anggota sedang/pernah meminjam buku)
        $_SESSION['error_msg'] = 'Gagal menghapus anggota: Anggota ini sedang atau pernah terlibat dalam transaksi peminjaman!';
    }
}

header("Location: list.php");
exit;
