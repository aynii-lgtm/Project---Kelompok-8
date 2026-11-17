<?php 
    if (!file_exists(__DIR__ . "/backend/koneksi.php")) {
        die("Error: File backend/koneksi.php tidak ditemukan!");
    }
    
    include __DIR__ . "/backend/koneksi.php";
    session_start();

    if (!isset($koneksi) || !$koneksi) {
        die("Error: Koneksi database gagal.");
    }

    if(!isset($_SESSION['status']) || $_SESSION['status'] != true) {
        header("Location: login.php?pesan=belum_login");
        exit();
    }

    $email = isset($_SESSION['email']) ? $_SESSION['email'] : 'User';
    
    // Query semua menu untuk section utama
    $query_all = "SELECT * FROM menu WHERE ketersediaan = 1 ORDER BY kategori ASC, nama_menu ASC";
    $result_all = mysqli_query($koneksi, $query_all);

    // Query khusus 3 menu teratas untuk section Best Seller
    $query_top = "SELECT * FROM menu WHERE ketersediaan = 1 ORDER BY id ASC LIMIT 3";
    $result_top = mysqli_query($koneksi, $query_top);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Mie Kiro</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>
        :root {
            --primary-color: #d61218;
            --bg-peach: #fdf5f0;
            --card-bg: #ffffff;
            --text-dark: #2c3e50;
            --text-muted: #6c757d;
        }

        body {
            background-color: var(--bg-peach);
        }

        /* --- Navbar Modern (Menu di Kanan Logo) --- */
        .top-navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 12px 0;
        }

        .navbar-brand img { height: 40px; }
        
        /* Container navbar agar item rapat tapi rapi */
        .nav-container {
            display: flex;
            align-items: center;
            gap: 40px; /* Jarak antara Logo & Menu Navigasi */
        }

        .main-nav {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 25px; /* Jarak antar link menu */
        }

        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            text-decoration: none;
            position: relative;
            transition: 0.3s;
            padding: 5px 0;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary-color);
            transition: width 0.3s;
        }

        .nav-link:hover::after { width: 100%; }

        /* --- Section Titles --- */
        .section-title {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 30px;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: var(--primary-color);
            margin-top: 8px;
            border-radius: 2px;
        }

        /* --- Grid Card Utama --- */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .menu-card {
            background: var(--card-bg);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .menu-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(214, 18, 24, 0.1); }

        .img-wrapper { position: relative; height: 200px; overflow: hidden; background: #f8f9fa; }
        .img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .menu-card:hover .img-wrapper img { transform: scale(1.05); }

        .badge-kategori {
            position: absolute; top: 15px; left: 15px;
            background: rgba(255,255,255,0.95); color: var(--primary-color);
            padding: 5px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        .card-body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .card-title { font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
        .card-desc { font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 15px; flex-grow: 1; }
        
        .card-footer-custom { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
        .price-tag { font-size: 1.15rem; font-weight: 700; color: var(--primary-color); }
        
        .btn-add {
            background: var(--primary-color); color: white; border: none;
            width: 35px; height: 35px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; transition: 0.2s;
        }
        .btn-add:hover { background: #b00e13; transform: scale(1.1); }

        /* --- 3 Card Box Horizontal (Best Seller) --- */
        .best-seller-row {
            display: flex;
            gap: 25px;
            overflow-x: auto;
            padding-bottom: 20px;
            scrollbar-width: thin;
        }

        .bs-card {
            min-width: 320px;
            background: white;
            border-radius: 20px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.03);
            transition: 0.3s;
            border: 1px solid rgba(0,0,0,0.02);
        }

        .bs-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(214, 18, 24, 0.08); }

        .bs-img {
            width: 90px; height: 90px; border-radius: 16px;
            object-fit: cover; flex-shrink: 0;
        }

        .bs-info h4 { font-size: 1rem; font-weight: 700; margin-bottom: 5px; color: var(--text-dark); }
        .bs-info p { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px; line-height: 1.4; }
        .bs-price { font-weight: 700; color: var(--primary-color); font-size: 1.05rem; }

        @media (max-width: 768px) {
            .nav-container { flex-direction: column; align-items: flex-start; gap: 15px; }
            .main-nav { width: 100%; justify-content: flex-start; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="top-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Wrapper Logo + Menu agar berdekatan -->
            <div class="nav-container">
                <a href="#" class="navbar-brand d-flex align-items-center gap-2">
                    <img src="M.png" alt="Logo Mie Kiro">
                    <span style="font-weight: 700; color: var(--primary-color); font-size: 1.3rem;">Mie Kiro</span>
                </a>
                
                <ul class="main-nav d-none d-md-flex">
                    <li><a href="#" class="nav-link active">Beranda</a></li>
                    <li><a href="#" class="nav-link">Menu</a></li>
                    <li><a href="#" class="nav-link">Merch</a></li>
                </ul>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-md-block fw-medium small">Hi, <?php echo htmlspecialchars(explode('@', $email)[0]); ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="container py-5">
        
        <!-- Section 1: Our Signature Menu (Grid) -->
        <h2 class="section-title">Our Signature Menu</h2>
        <div class="grid-container">
            <?php if(mysqli_num_rows($result_all) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result_all)): ?>
                    <div class="menu-card">
                        <div class="img-wrapper">
                            <span class="badge-kategori"><?php echo $row['kategori']; ?></span>
                            <img src="<?php echo $row['gambar']; ?>" alt="<?php echo $row['nama_menu']; ?>" loading="lazy">
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?php echo $row['nama_menu']; ?></h3>
                            <p class="card-desc"><?php echo substr($row['deskripsi'], 0, 60) . '...'; ?></p>
                            <div class="card-footer-custom">
                                <div class="price-tag">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                                <button class="btn-add"><i class="bi bi-plus-lg"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted w-100 text-center">Belum ada menu tersedia.</p>
            <?php endif; ?>
        </div>

        <!-- Section 2: Best Seller (3 Card Box Horizontal) -->
        <h2 class="section-title mt-4">Best Seller</h2>
        <div class="best-seller-row">
            <?php if(mysqli_num_rows($result_top) > 0): ?>
                <?php while($top = mysqli_fetch_assoc($result_top)): ?>
                    <div class="bs-card">
                        <img src="<?php echo $top['gambar']; ?>" alt="<?php echo $top['nama_menu']; ?>" class="bs-img">
                        <div class="bs-info">
                            <h4><?php echo $top['nama_menu']; ?></h4>
                            <p><?php echo substr($top['deskripsi'], 0, 50) . '...'; ?></p>
                            <div class="bs-price">Rp <?php echo number_format($top['harga'], 0, ',', '.'); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>