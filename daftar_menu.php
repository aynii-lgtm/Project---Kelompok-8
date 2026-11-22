<?php
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Mie Kiro</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">

    <style>

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
            <div class="nav-container">
                <a href="#" class="navbar-brand d-flex align-items-center gap-2">
                    <img src="M.png" alt="Logo Mie Kiro">
                    <span style="font-weight: 700; color: var(--primary-color); font-size: 1.3rem;">Mie Kiro</span>
                </a>
                
                <ul class="main-nav d-none d-md-flex">
                    <li><a href="tentang_kami.php" class="nav-link">Tentang Kami</a></li>
                    <li><a href="daftar_menu.php" class="nav-link active">Menu</a></li>
                </ul>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-md-block">Hi, <?php echo htmlspecialchars(explode('@', $email)[0]); ?></span>
                <a href="profile.php" class="text-decoration-none">
                    <?php if(!empty($row['foto_profile'])) { ?>
                        <img src="uploads/profil/<?php echo $row['foto_profile']; ?>" class="nav-avatar">
                    <?php } else { ?>
                        <i class="bi bi-person-circle" style="font-size: 1.6rem;" ></i>
                    <?php } ?>
                </a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="container py-5">
        
    </section>
</body>
</html>