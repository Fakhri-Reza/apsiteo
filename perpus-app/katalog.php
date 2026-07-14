<?php
// katalog.php — Halaman publik (tidak perlu login)
require_once 'config/database.php';

$keyword = trim($_GET['q'] ?? '');
$buku_list = [];

try {
    if (!empty($keyword)) {
        // Cari di semua kolom sekaligus dengan LIKE (prepared statement safe)
        $like = '%' . $keyword . '%';
        $stmt = $pdo->prepare("
            SELECT * FROM buku
            WHERE judul     LIKE :q1
               OR pengarang LIKE :q2
               OR kategori  LIKE :q3
               OR penerbit  LIKE :q4
            ORDER BY judul ASC
        ");
        $stmt->execute(['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like]);
    } else {
        $stmt = $pdo->query("SELECT * FROM buku ORDER BY judul ASC");
    }
    $buku_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Gagal mengambil data buku: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku — Perpustakaan Digital</title>
    <meta name="description" content="Temukan koleksi buku perpustakaan kami. Cari berdasarkan judul, pengarang, kategori, atau penerbit.">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f1f5f9;
            min-height: 100vh;
        }

        /* ── Hero Header ── */
        .hero-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #1d4ed8 100%);
            padding: 4rem 1rem 3rem;
            text-align: center;
            color: #fff;
        }
        .hero-section h1 {
            font-size: clamp(1.8rem, 5vw, 2.8rem);
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .hero-section p {
            color: #94a3b8;
            font-size: 1rem;
            max-width: 520px;
            margin: 0.5rem auto 1.8rem;
        }

        /* ── Search Box ── */
        .search-wrapper {
            max-width: 620px;
            margin: 0 auto;
        }
        .search-wrapper .input-group {
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        .search-wrapper .form-control {
            border: none;
            padding: 0.85rem 1.4rem;
            font-size: 1rem;
            border-radius: 0;
        }
        .search-wrapper .form-control:focus {
            box-shadow: none;
            outline: none;
        }
        .search-wrapper .btn-search {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 0.85rem 1.6rem;
            font-weight: 600;
            transition: background 0.2s;
        }
        .search-wrapper .btn-search:hover {
            background: #1d4ed8;
        }

        /* ── Main Content ── */
        main {
            padding: 2.5rem 1rem 4rem;
        }

        /* ── Book Card ── */
        .book-card {
            background: #fff;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .book-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px -4px rgba(0,0,0,0.12);
        }
        .book-card-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 1.5rem 1.25rem 1rem;
            position: relative;
        }
        .book-icon {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.4rem;
            margin-bottom: 0.75rem;
        }
        .book-card-header .book-title {
            font-size: 1rem;
            font-weight: 700;
            color: #f8fafc;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .book-card-body {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .book-meta {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }
        .book-meta i {
            width: 16px;
            margin-top: 2px;
            color: #94a3b8;
            flex-shrink: 0;
        }
        .book-card-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .badge-tersedia {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            font-weight: 600;
            padding: 0.4rem 0.9rem;
            border-radius: 50px;
            font-size: 0.8rem;
        }
        .badge-habis {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            font-weight: 600;
            padding: 0.4rem 0.9rem;
            border-radius: 50px;
            font-size: 0.8rem;
        }
        .stok-text {
            font-size: 0.8rem;
            color: #94a3b8;
        }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.4;
        }
        .empty-state h5 {
            color: #64748b;
            font-weight: 600;
        }

        /* ── Highlight keyword ── */
        mark {
            background: #fef08a;
            color: #713f12;
            border-radius: 3px;
            padding: 0 2px;
        }

        /* ── Navbar publik ── */
        .public-nav {
            background: #0f172a;
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .public-nav .brand {
            color: #f8fafc;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .public-nav .btn-login {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 0.45rem 1.2rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }
        .public-nav .btn-login:hover {
            background: #1d4ed8;
            color: #fff;
        }
    </style>
</head>
<body>

<!-- Navbar Publik -->
<nav class="public-nav">
    <a href="katalog.php" class="brand">
        <i class="fa-solid fa-book-bookmark text-primary"></i>
        Perpustakaan Digital
    </a>
    <a href="login.php" class="btn-login">
        <i class="fa-solid fa-lock me-1"></i>Admin Login
    </a>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <h1><i class="fa-solid fa-magnifying-glass me-2 opacity-75"></i>Katalog Buku</h1>
    <p>Temukan buku yang Anda cari dari koleksi perpustakaan kami. Cari berdasarkan judul, pengarang, kategori, atau penerbit.</p>

    <!-- Search Bar -->
    <div class="search-wrapper">
        <form action="katalog.php" method="GET" role="search">
            <div class="input-group">
                <input type="text" class="form-control" name="q"
                       id="search-input"
                       value="<?= htmlspecialchars($keyword) ?>"
                       placeholder="Cari judul, pengarang, kategori, penerbit..."
                       autocomplete="off"
                       aria-label="Cari buku">
                <?php if (!empty($keyword)): ?>
                    <a href="katalog.php" class="btn btn-outline-light d-flex align-items-center px-3" title="Hapus pencarian">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
                <button type="submit" class="btn-search" id="search-btn">
                    <i class="fa-solid fa-search me-1"></i>Cari
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Main Content -->
<main>
    <div class="container-xl">

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <!-- Result Info -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <?php if (!empty($keyword)): ?>
                    <h5 class="mb-0 text-dark fw-semibold">
                        Hasil pencarian untuk: <mark><?= htmlspecialchars($keyword) ?></mark>
                    </h5>
                    <p class="text-muted mb-0 small mt-1">
                        Ditemukan <strong><?= count($buku_list) ?></strong> buku.
                    </p>
                <?php else: ?>
                    <h5 class="mb-0 text-dark fw-semibold">Semua Koleksi Buku</h5>
                    <p class="text-muted mb-0 small mt-1">
                        Total <strong><?= count($buku_list) ?></strong> buku tersedia.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Book Card Grid -->
        <?php if (empty($buku_list)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-book-open-reader d-block"></i>
                <h5>Tidak ada buku ditemukan</h5>
                <p class="mb-0">Coba gunakan kata kunci yang berbeda.</p>
                <?php if (!empty($keyword)): ?>
                    <a href="katalog.php" class="btn btn-outline-secondary mt-3 px-4">
                        <i class="fa-solid fa-arrow-left me-2"></i>Lihat Semua Buku
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <?php foreach ($buku_list as $buku):
                    $tersedia = intval($buku['stok']) > 0;
                ?>
                    <div class="col">
                        <div class="book-card">
                            <!-- Card Header -->
                            <div class="book-card-header">
                                <div class="book-icon">
                                    <i class="fa-solid fa-book"></i>
                                </div>
                                <div class="book-title"><?= htmlspecialchars($buku['judul']) ?></div>
                            </div>

                            <!-- Card Body -->
                            <div class="book-card-body">
                                <div class="book-meta">
                                    <i class="fa-solid fa-feather-pointed"></i>
                                    <span><?= htmlspecialchars($buku['pengarang']) ?></span>
                                </div>
                                <div class="book-meta">
                                    <i class="fa-solid fa-building-columns"></i>
                                    <span><?= htmlspecialchars($buku['penerbit']) ?></span>
                                </div>
                                <div class="book-meta">
                                    <i class="fa-solid fa-tag"></i>
                                    <span>
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border" style="font-size: 0.75rem;">
                                            <?= htmlspecialchars($buku['kategori']) ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="book-meta">
                                    <i class="fa-solid fa-calendar"></i>
                                    <span><?= htmlspecialchars($buku['tahun_terbit']) ?></span>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="book-card-footer">
                                <?php if ($tersedia): ?>
                                    <span class="badge-tersedia">
                                        <i class="fa-solid fa-circle-check me-1"></i>Tersedia
                                    </span>
                                    <span class="stok-text">Stok: <?= intval($buku['stok']) ?></span>
                                <?php else: ?>
                                    <span class="badge-habis">
                                        <i class="fa-solid fa-circle-xmark me-1"></i>Stok Habis
                                    </span>
                                    <span class="stok-text">Stok: 0</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- Footer -->
<footer class="text-center text-muted py-4 border-top bg-white" style="font-size: 0.85rem;">
    &copy; <?= date('Y') ?> Perpustakaan Digital. Semua hak dilindungi.
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-fokus ke search input saat halaman dimuat
    document.getElementById('search-input').focus();
</script>
</body>
</html>
