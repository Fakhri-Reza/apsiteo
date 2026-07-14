<?php
// buku/hapus.php
require_once '../auth.php';
require_once '../config/database.php';

$id_buku = $_GET['id'] ?? '';

if (!empty($id_buku)) {
    try {
        $stmt = $pdo->prepare("DELETE FROM buku WHERE id_buku = :id");
        $stmt->execute(['id' => $id_buku]);

        $_SESSION['success_msg'] = 'Buku berhasil dihapus!';
    } catch (PDOException $e) {
        // Jika gagal karena constraint FK (buku sedang dipinjam)
        $_SESSION['error_msg'] = 'Gagal menghapus buku: Buku ini sedang digunakan dalam transaksi peminjaman!';
    }
}

header("Location: list.php");
exit;
