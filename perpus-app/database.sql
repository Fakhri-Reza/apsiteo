CREATE DATABASE IF NOT EXISTS `perpustakaan_db`;
USE `perpustakaan_db`;

-- Tabel: admin
CREATE TABLE IF NOT EXISTS `admin` (
    `id_admin` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `nama` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel: buku
CREATE TABLE IF NOT EXISTS `buku` (
    `id_buku` INT AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(255) NOT NULL,
    `pengarang` VARCHAR(150) NOT NULL,
    `penerbit` VARCHAR(150) NOT NULL,
    `tahun_terbit` INT NOT NULL,
    `kategori` VARCHAR(100) NOT NULL,
    `stok` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel: anggota
CREATE TABLE IF NOT EXISTS `anggota` (
    `id_anggota` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(150) NOT NULL,
    `alamat` TEXT NOT NULL,
    `no_telepon` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel: peminjaman
CREATE TABLE IF NOT EXISTS `peminjaman` (
    `id_peminjaman` INT AUTO_INCREMENT PRIMARY KEY,
    `id_anggota` INT NOT NULL,
    `id_buku` INT NOT NULL,
    `tanggal_pinjam` DATE NOT NULL,
    `tanggal_jatuh_tempo` DATE NOT NULL,
    `status` ENUM('dipinjam', 'kembali') NOT NULL DEFAULT 'dipinjam',
    FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel: pengembalian
CREATE TABLE IF NOT EXISTS `pengembalian` (
    `id_pengembalian` INT AUTO_INCREMENT PRIMARY KEY,
    `id_peminjaman` INT UNIQUE NOT NULL,
    `tanggal_kembali` DATE NOT NULL,
    `status_telat` ENUM('ya', 'tidak') NOT NULL DEFAULT 'tidak',
    FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel: denda
CREATE TABLE IF NOT EXISTS `denda` (
    `id_denda` INT AUTO_INCREMENT PRIMARY KEY,
    `id_pengembalian` INT UNIQUE NOT NULL,
    `jumlah_hari_telat` INT NOT NULL DEFAULT 0,
    `jumlah_denda` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status_bayar` ENUM('belum', 'lunas') NOT NULL DEFAULT 'belum',
    FOREIGN KEY (`id_pengembalian`) REFERENCES `pengembalian` (`id_pengembalian`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Admin Default (username: admin, password: admin123)
-- Hash password: $2y$10$vsB5KPKEfTwnow5vjmhfROZrc/HMk6yAPQnJeV00LSA2FvLheTb.S
INSERT INTO `admin` (`username`, `password`, `nama`) VALUES
('admin', '$2y$10$vsB5KPKEfTwnow5vjmhfROZrc/HMk6yAPQnJeV00LSA2FvLheTb.S', 'Administrator Perpustakaan')
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);
