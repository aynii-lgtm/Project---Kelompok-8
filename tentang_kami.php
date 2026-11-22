<?php
    include __DIR__ . "/backend/koneksi.php";
    session_start();

    if (!isset($koneksi) || !$koneksi) {
        die("Error: Koneksi database gagal.");
    }

    if (!isset($_SESSION['status']) || $_SESSION['status'] != true) {
        header("Location: login.php?pesan=belum_login");
        exit();
    }

    $id = $_SESSION['id'];
    $query = mysqli_query($koneksi, "SELECT email FROM akun WHERE id = '$id'");
    $user = mysqli_fetch_assoc($query);
    $email = $user ? $user['email'] : 'User';

    // Foto profil untuk navbar (sama seperti profile.php)
    $file_foto = glob(__DIR__ . '/uploads/u' . $id . '.*');
    $foto = $file_foto ? 'uploads/' . basename($file_foto[0]) . '?v=' . filemtime($file_foto[0]) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Mie Kiro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">

    <style>
        .about-title {
            font-weight: 500;
            font-size: clamp(2rem, 4.2vw, 3rem);
            line-height: 1.15;
            color: #1f1a17;
            margin-bottom: 20px;
        }
        .about-title em {
            display: block;
            font-style: italic;
            font-weight: 400;
            color: var(--primary-color);
        }

        .about-lead {
            max-width: 460px;
            font-size: 0.85rem;
            line-height: 1.75;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        /* ---------- Tabs ---------- */
        .about-tabs {
            display: flex;
            gap: 28px;
            border-bottom: 1px solid var(--line);
        }
        .tab-btn {
            background: none;
            border: 0;
            padding: 0 0 12px;
            font: inherit;
            font-size: 0.8rem;
            color: var(--text-muted);
            white-space: nowrap;
            cursor: pointer;
            position: relative;
            transition: color .2s;
        }
        .tab-btn:hover { color: var(--text-dark); }
        .tab-btn.active { color: var(--text-dark); font-weight: 500; }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: -1px;
            height: 2px;
            background: var(--primary-color);
        }

        .tab-panel { display: none; padding: 56px 0 80px; }
        .tab-panel.active { display: block; }

        .panel-grid {
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            gap: 48px;
            align-items: center;
        }

        .panel-img {
            width: 100%;
            aspect-ratio: 16 / 11;
            border-radius: 17px;
            overflow: hidden;
            background: linear-gradient(135deg, #f4c9c0, #d61218);
        }
        .panel-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .panel-heading {
            font-family: var(--serif);
            font-weight: 600;
            font-size: 1.45rem;
            line-height: 1.3;
            color: #1f1a17;
            margin-bottom: 16px;
        }
        .panel-text p {
            font-size: 0.82rem;
            line-height: 1.75;
            color: var(--text-muted);
            margin-bottom: 14px;
        }

        /* ---------- Statistik ---------- */
        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px 24px;
            margin-top: 22px;
            padding-top: 22px;
            border-top: 1px solid var(--line);
        }
        .stat-num {
            font-size: 1.7rem;
            line-height: 1.1;
            color: var(--primary-dark);
        }
        .stat-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .nav-container { flex-direction: column; align-items: flex-start; gap: 15px; }
            .main-nav { width: 100%; justify-content: flex-start; }

            .about-hero { padding-top: 40px; }
            .panel-grid { grid-template-columns: 1fr; gap: 28px; }
            .tab-panel { padding: 36px 0 56px; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="top-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="nav-container">
                <a href="#" class="navbar-brand d-flex align-items-center gap-2">
                    <img src="pictures/M.png" alt="Logo Mie Kiro">
                    <span style="font-weight: 700; color: var(--primary-color); font-size: 1.3rem;">Mie Kiro</span>
                </a>

                <ul class="main-nav d-none d-md-flex">
                    <li><a href="tentang_kami.php" class="nav-link active">Tentang Kami</a></li>
                    <li><a href="daftar_menu.php" class="nav-link">Menu</a></li>
                </ul>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-md-block">Hi, <?php echo htmlspecialchars(explode('@', $email)[0]); ?></span>
                <a href="profile.php" class="text-decoration-none">
                    <?php if ($foto) { ?>
                        <img src="<?php echo htmlspecialchars($foto); ?>" class="nav-avatar" alt="Foto profil">
                    <?php } else { ?>
                        <i class="bi bi-person-circle" style="font-size: 1.6rem; line-height: 1;"></i>
                    <?php } ?>
                </a>
                <a href="bakcend/logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <header class="about-hero">
        <div class="container">
            <div class="eyebrow";>Tentang Kami</div>
            <h1 class="about-title">
                Mie pedas pilihan Indonesia,
                <em>bermula dari satu mangkuk mie.</em>
            </h1>
            <p class="about-lead">
                Sejak 2020, Mie Kiro hadir sebagai restoran mie modern yang menyajikan cita rasa pedas khas selera Indonesia.
                Kami percaya semangkuk mie yang enak lahir dari bahan baku pilihan, dapur yang bersih, dan pelayanan yang tulus.
                Prinsip itu terus kami pegang di setiap mangkuk yang kami sajikan.
            </p>

            <div class="about-tabs">
                <button class="tab-btn active" role="tab" data-tab="cerita">Fakta &amp; Cerita Brand</button>
                <button class="tab-btn" role="tab" data-tab="bahan">Sumber Bahan &amp; Kualitas</button>
                <button class="tab-btn" role="tab" data-tab="tim">Tim &amp; Kepemimpinan</button>
            </div>
        </div>
    </header>

    <!-- Konten tab -->
    <main class="container">

        <!-- Tab 1 -->
        <section class="tab-panel active" id="tab-cerita" role="tabpanel">
            <div class="panel-grid">
                <div class="panel-img">
                    <img src="pictures/outlet.png" alt="Suasana Mie Kiro" onerror="this.style.display='none'">
                </div>
                <div class="panel-text">
                    <h2 class="panel-heading">Dari kecintaan pada pedas menjadi restoran mie kepercayaan.</h2>
                    <p>Mie Kiro Indonesia berdiri tahun 2020 dengan satu fokus: menyajikan mie pedas berkualitas yang pas di lidah masyarakat Indonesia. Dengan tagline "Mie Pedas Pilihan Indonesia", kami ingin setiap pelanggan menemukan level pedas favoritnya, dari yang menggoda sampai yang menantang.</p>
                    <p>Sejak awal, kami membangun sistem operasional yang terstruktur dan konsisten, mulai dari pengadaan bahan baku, pengolahan di dapur, sampai penyajian ke meja pelanggan. Hasilnya, rasa yang sama, keamanan pangan yang terjaga, dan pengalaman makan yang nyaman di setiap kunjungan.</p>

                    <div class="stats">
                        <div>
                            <div class="stat-num">2020</div>
                            <div class="stat-label">Didirikan</div>
                        </div>
                        <div>
                            <div class="stat-num">2</div>
                            <div class="stat-label">Outlet</div>
                        </div>
                        <div>
                            <div class="stat-num">100+</div>
                            <div class="stat-label">Anggota tim</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tab 2 -->
        <section class="tab-panel" id="tab-bahan" role="tabpanel">
            <div class="panel-grid">
                <div class="panel-img">
                    <img src="pictures/bahan.jpeg" alt="Bahan baku Mie Kiro" onerror="this.style.display='none'">
                </div>
                <div class="panel-text">
                    <h2 class="panel-heading">Bahan pilihan diolah dengan standar yang disiplin.</h2>
                    <p>Kami percaya bahwa cita rasa mie yang istimewa dimulai jauh sebelum proses memasak. Oleh karena itu, kami sangat selektif dalam memilih bahan baku, mulai dari tepung terigu berkualitas tinggi hingga rempah-rempah segar pilihan. Setiap bahan yang tiba di dapur kami menjalani pemeriksaan ketat untuk memastikan kesegarannya, sehingga hanya material terbaik yang lolos standar mutu kami dan siap diolah menjadi hidangan istimewa.</p>
                    <p>Bagi kami, kebersihan bukan sekadar aturan, melainkan komitmen terhadap kesehatan pelanggan. Setiap tahap pengolahan, mulai dari pencucian, peracikan bumbu, hingga penyajian dilakukan dengan mengikuti prosedur kerja yang baku dan higienis. Dapur kami dikelola dengan standar sanitasi tinggi untuk menjamin bahwa setiap mangkuk mie yang tersaji aman, bersih, dan bebas dari kontaminasi.</p>
                    <p>Kami tidak hanya berfokus pada hasil akhir, tetapi juga pada proses yang bertanggung jawab. Kami terus berupaya menjalankan usaha yang berkelanjutan melalui pengelolaan bahan yang efisien untuk meminimalkan limbah. Selain itu, kami membangun hubungan jangka panjang yang saling menguntungkan dengan para pemasok lokal, memastikan bahwa rantai pasokan kami tetap stabil dan mendukung pertumbuhan ekonomi sekitar. Dengan cara ini, kami berharap dapat terus menyajikan mie terbaik sambil menjaga kelestarian lingkungan dan komunitas.</p>
                </div>
            </div>
        </section>

        <!-- Tab 3 -->
        <section class="tab-panel" id="tab-tim" role="tabpanel">
            <div class="panel-grid">
                <div class="panel-img">
                    <img src="pictures/karyawan.png" alt="Tim Mie Kiro" onerror="this.style.display='none'">
                </div>
                <div class="panel-text">
                    <h2 class="panel-heading">Tim yang kompeten di balik setiap mangkuk.</h2>
                    <p>Rasa yang konsisten hanya bisa dijaga oleh tim yang terlatih dan berdedikasi. Karena itu, Mie Kiro menaruh perhatian besar pada pengembangan sumber daya manusia melalui program pelatihan standar kerja yang berkelanjutan serta penerapan budaya kerja yang disiplin namun tetap saling mendukung antar sesama anggota tim. Kami percaya bahwa kompetensi teknis saja tidak cukup; setiap staf juga dibekali dengan pemahaman mendalam tentang filosofi rasa kami agar mampu menyajikan hidangan dengan ketulusan dan presisi di setiap porsinya.</p>
                    <p>Dipimpin oleh tim manajemen yang berkomitmen penuh terhadap kualitas dan kepuasan pelanggan, kami memastikan setiap proses operasional berjalan sesuai prosedur baku tanpa mengorbankan kehangatan pelayanan kepada setiap pengunjung yang datang. Evaluasi kinerja dilakukan secara berkala untuk menjaga standar tertinggi, sekaligus memberikan ruang bagi karyawan untuk menyampaikan ide-ide kreatif yang dapat meningkatkan pengalaman bersantap pelanggan secara keseluruhan.</p>
                    <p>Kami terus mengembangkan usaha dengan langkah yang terukur dan strategis, mulai dari inovasi menu hingga peningkatan fasilitas, supaya Mie Kiro semakin dipercaya sebagai pilihan mie pedas terbaik bagi masyarakat Indonesia di masa depan.</p>
                </div>
            </div>
        </section>

    </main>

    <script>
        const tabs   = document.querySelectorAll('.tab-btn');
        const panels = document.querySelectorAll('.tab-panel');

        tabs.forEach(btn => {
            btn.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                panels.forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
            });
        });
    </script>
</body>
</html>
