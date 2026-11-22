<?php
    include __DIR__ . "/backend/koneksi.php";
    session_start();

    // Harus login dulu
    if (!isset($_SESSION['status']) || $_SESSION['status'] != true) {
        header("Location: login.php?pesan=belum_login");
        exit();
    }

    $id    = (int) $_SESSION['id'];
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : 'User';

    // Foto profil untuk navbar (disimpan di uploads/u{id}.jpg / png / webp)
    $file_foto = glob(__DIR__ . '/uploads/u' . $id . '.*');
    $foto = $file_foto ? 'uploads/' . basename($file_foto[0]) . '?v=' . filemtime($file_foto[0]) : '';

    // Ambil semua menu
    $daftar_menu = [];
    $hasil = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY FIELD(kategori, 'mie', 'snack', 'minuman'), nama_menu");
    while ($m = mysqli_fetch_assoc($hasil)) {
        $daftar_menu[] = $m;
    }

    // Kategori untuk sidebar (mie, snack, minuman)
    $kategori_list = array_unique(array_column($daftar_menu, 'kategori'));

    // Level pedas untuk pilihan di keranjang
    $level_list = [];
    $hasil_level = mysqli_query($koneksi, "SELECT id, nama_level FROM level_pedas ORDER BY id");
    while ($l = mysqli_fetch_assoc($hasil_level)) {
        $level_list[] = $l;
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Mie Kiro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="style.css">

    <style>
        /* ===== Foto profil di navbar ===== */
        .nav-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
            display: block;
        }

        /* ===== Bagian atas halaman ===== */
        .about-hero {
            padding: 64px 0 28px;
            border-bottom: 1px solid var(--line, #e6d9d0);
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 18px;
            margin-top: 36px;
        }
        .eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--primary-color);
        }
        .about-title {
            font-weight: 500;
            font-size: clamp(2rem, 4.2vw, 3rem);
            line-height: 1.15;
            color: #1f1a17;
            margin-bottom: 20px;
        }
        .about-title em {
            font-style: italic;
            font-weight: 400;
            color: var(--primary-color);
        }
        .about-lead {
            max-width: 560px;
            font-size: 0.85rem;
            line-height: 1.75;
            color: var(--text-muted);
            margin-bottom: 0;
        }

        /* ===== Sidebar kategori + daftar menu ===== */
        .menu-section {
            padding: 40px 0 80px;
        }
        .menu-note {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 32px;
        }
        .menu-layout {
            display: grid;
            grid-template-columns: 232px 1fr;
            gap: 52px;
            align-items: start;
        }

        .cat-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            position: sticky;
            top: 96px;
        }
        .cat-btn {
            background: none;
            border: 0;
            text-align: left;
            padding: 11px 14px;
            border-radius: 10px;
            font: inherit;
            font-size: 0.85rem;
            color: var(--text-dark);
            cursor: pointer;
        }
        .cat-btn:hover { background: rgba(214, 18, 24, 0.06); }
        .cat-btn.active {
            background: #f3e4dc;
            font-weight: 600;
            color: var(--primary-color);
        }

        /* ===== Kartu menu ===== */
        .produk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 30px;
        }
        .produk-card {
            background: #fff;
            border: 1px solid var(--line, #e6d9d0);
            border-radius: 18px;
            overflow: hidden;
        }
        .produk-card.hide { display: none; }

        .produk-img {
            position: relative;
            height: 190px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--line, #e6d9d0);
        }
        .produk-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 14px;
        }
        .badge-habis {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #1f1a17;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .produk-info { padding: 18px 20px 22px; }
        .produk-nama {
            font-weight: 600;
            font-size: 1.05rem;
            color: #1f1a17;
            margin-bottom: 6px;
        }
        .produk-desc {
            font-size: 0.78rem;
            line-height: 1.55;
            color: var(--text-muted);
            margin-bottom: 12px;
        }
        .produk-harga {
            font-style: italic;
            font-size: 0.9rem;
            color: var(--primary-color);
        }

        /* ===== Tombol + / - dan tombol Tambah ===== */
        .produk-aksi {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 16px;
        }
        .qty {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--line, #e6d9d0);
            border-radius: 999px;
            overflow: hidden;
        }
        .qty-btn {
            width: 30px;
            height: 30px;
            border: 0;
            background: #fff;
            display: grid;
            place-items: center;
            cursor: pointer;
        }
        .qty-btn:hover { background: #f3e4dc; }
        .qty-val {
            min-width: 28px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .btn-keranjang {
            border: 0;
            background: var(--primary-color);
            color: #fff;
            font: inherit;
            font-size: 0.78rem;
            padding: 7px 14px;
            border-radius: 999px;
            cursor: pointer;
        }
        .btn-keranjang:hover { background: #b00e13; }
        .btn-keranjang:disabled { background: #cfc3bb; cursor: not-allowed; }

        /* ===== Tombol keranjang melayang ===== */
        .cart-fab {
            position: fixed;
            right: 28px;
            bottom: 28px;
            z-index: 1040;
            width: 56px;
            height: 56px;
            border: 0;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            font-size: 1.4rem;
            display: grid;
            place-items: center;
            box-shadow: 0 10px 25px rgba(214, 18, 24, .35);
            cursor: pointer;
        }
        .cart-fab:hover { background: #b00e13; }
        .cart-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 999px;
            background: #1f1a17;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 600;
            display: grid;
            place-items: center;
        }

        /* ===== Isi panel keranjang ===== */
        .cart-item {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid var(--line, #e6d9d0);
        }
        .cart-item img {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .cart-info { flex: 1; min-width: 0; }
        .cart-nama { font-size: 0.85rem; font-weight: 600; }
        .cart-harga { font-size: 0.78rem; color: var(--text-muted); margin-bottom: 6px; }
        .cart-hapus {
            border: 0;
            background: none;
            color: var(--text-muted);
            font-size: 1rem;
            cursor: pointer;
        }
        .cart-hapus:hover { color: var(--primary-color); }
        .cart-kosong { text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 48px 0; }
        .cart-total { display: flex; justify-content: space-between; font-weight: 600; margin-bottom: 14px; }

        .cart-level { margin-bottom: 8px; }
        .cart-level select, .cart-form select, .cart-form textarea {
            width: 100%;
            font: inherit;
            font-size: 0.8rem;
            padding: 6px 10px;
            border: 1px solid var(--line, #e6d9d0);
            border-radius: 8px;
        }
        .cart-form { display: grid; gap: 8px; margin-bottom: 14px; }
        .cart-form label { font-size: 0.75rem; font-weight: 500; }
        .cart-form textarea { resize: none; }

        .pesan-status { font-size: 0.8rem; margin-top: 10px; text-align: center; }
        .pesan-status.ok { color: #1d6b35; }
        .pesan-status.gagal { color: #a52a2a; }

        .menu-kosong { grid-column: 1 / -1; color: var(--text-muted); font-size: 0.9rem; }

        /* ===== Layar kecil (HP) ===== */
        @media (max-width: 768px) {
            .nav-container { flex-direction: column; align-items: flex-start; gap: 15px; }
            .main-nav { width: 100%; justify-content: flex-start; }

            .about-hero { padding-top: 40px; }
            .menu-layout { grid-template-columns: 1fr; gap: 24px; }
            .cat-list { position: static; flex-direction: row; overflow-x: auto; }
            .cat-btn { white-space: nowrap; }
        }
    </style>
</head>
<body>

    <!-- ===== Navbar ===== -->
    <nav class="top-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="nav-container">
                <a href="#" class="navbar-brand d-flex align-items-center gap-2">
                    <img src="pictures/M.png" alt="Logo Mie Kiro">
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
                    <?php if ($foto) { ?>
                        <img src="<?php echo htmlspecialchars($foto); ?>" class="nav-avatar" alt="Foto profil">
                    <?php } else { ?>
                        <i class="bi bi-person-circle" style="font-size: 1.6rem;"></i>
                    <?php } ?>
                </a>

                <a href="backend/logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- ===== Bagian atas ===== -->
    <header class="about-hero">
        <div class="container">
            <div class="eyebrow">Menu</div>
            <h1 class="about-title">
                Setiap mangkuk, <em>pedasnya sesuai seleramu</em>
            </h1>
            <p class="about-lead">
                Jelajahi seluruh menu Mie Kiro, mulai dari mie pedas andalan sampai pilihan pendamping dan minuman penyegar. Pilih level pedas favoritmu, dari yang menggoda sampai yang menantang! Harga dapat berbeda berdasarkan outlet &amp; kota.
            </p>
        </div>
    </header>

    <!-- ===== Daftar menu ===== -->
    <section class="container menu-section">
        <p class="menu-note">Harga yang ditampilkan dapat berbeda tergantung outlet &amp; kota.</p>

        <div class="menu-layout">

            <!-- Sidebar kategori -->
            <aside class="cat-list">
                <button type="button" class="cat-btn active" data-kat="semua">Semua Menu</button>
                <?php foreach ($kategori_list as $kat) { ?>
                    <button type="button" class="cat-btn" data-kat="<?php echo htmlspecialchars($kat); ?>">
                        <?php echo htmlspecialchars(ucfirst($kat)); ?>
                    </button>
                <?php } ?>
            </aside>

            <!-- Kartu menu -->
            <div class="produk-grid">
                <?php if (count($daftar_menu) == 0) { ?>
                    <p class="menu-kosong">Menu belum tersedia.</p>
                <?php } ?>

                <?php foreach ($daftar_menu as $m) { ?>
                    <?php
                        // Gambar bisa berupa link (http...) atau nama file di folder uploads/
                        $gambar = $m['gambar'];
                        if ($gambar != '' && !preg_match('#^https?://#', $gambar)) {
                            $gambar = 'uploads/' . $gambar;
                        }
                        $ada_stok = ((int) $m['ketersediaan'] > 0);
                    ?>

                    <article class="produk-card" data-kat="<?php echo htmlspecialchars($m['kategori']); ?>">
                        <div class="produk-img">
                            <img src="<?php echo htmlspecialchars($gambar); ?>" alt="<?php echo htmlspecialchars($m['nama_menu']); ?>" loading="lazy" referrerpolicy="no-referrer">
                            <?php if (!$ada_stok) { ?>
                                <span class="badge-habis">Habis</span>
                            <?php } ?>
                        </div>

                        <div class="produk-info">
                            <h3 class="produk-nama"><?php echo htmlspecialchars($m['nama_menu']); ?></h3>
                            <p class="produk-desc"><?php echo htmlspecialchars($m['deskripsi']); ?></p>
                            <div class="produk-harga">Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?></div>

                            <?php if ($ada_stok) { ?>
                                <!-- data-... dipakai JavaScript untuk mengisi keranjang -->
                                <div class="produk-aksi"
                                     data-id="<?php echo (int) $m['id']; ?>"
                                     data-nama="<?php echo htmlspecialchars($m['nama_menu']); ?>"
                                     data-harga="<?php echo (int) $m['harga']; ?>"
                                     data-gambar="<?php echo htmlspecialchars($gambar); ?>"
                                     data-kategori="<?php echo htmlspecialchars($m['kategori']); ?>">
                                    <div class="qty">
                                        <button type="button" class="qty-btn" data-aksi="kurang"><i class="bi bi-dash"></i></button>
                                        <span class="qty-val">1</span>
                                        <button type="button" class="qty-btn" data-aksi="tambah"><i class="bi bi-plus"></i></button>
                                    </div>
                                    <button type="button" class="btn-keranjang"><i class="bi bi-bag-plus"></i> Tambah</button>
                                </div>
                            <?php } else { ?>
                                <div class="produk-aksi">
                                    <button type="button" class="btn-keranjang" disabled>Stok habis</button>
                                </div>
                            <?php } ?>
                        </div>
                    </article>
                <?php } ?>
            </div>

        </div>
    </section>

    <!-- ===== Tombol keranjang melayang ===== -->
    <button type="button" class="cart-fab" data-bs-toggle="offcanvas" data-bs-target="#panelKeranjang">
        <i class="bi bi-bag"></i>
        <span class="cart-badge" id="cartBadge">0</span>
    </button>

    <!-- ===== Panel keranjang (muncul dari kanan) ===== -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="panelKeranjang">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-semibold">Keranjang</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <!-- Isi keranjang diisi oleh JavaScript -->
        <div class="offcanvas-body" id="cartBody"></div>

        <div class="p-3 border-top">
            <div class="cart-form">
                <label for="metodeBayar">Metode pembayaran</label>
                <select id="metodeBayar">
                    <option value="cash">Cash (bayar di kasir)</option>
                    <option value="qris">QRIS</option>
                </select>

                <label for="catatanPesanan">Catatan (opsional)</label>
                <textarea id="catatanPesanan" rows="2" maxlength="255" placeholder="Contoh: tanpa bawang goreng"></textarea>
            </div>

            <div class="cart-total"><span>Total</span><span id="cartTotal">Rp 0</span></div>
            <button type="button" class="btn btn-danger w-100 rounded-pill" id="btnPesan">Buat Pesanan</button>
            <div class="pesan-status" id="pesanStatus"></div>
        </div>
    </div>

    <script>
        // =====================================================
        // Data dasar
        // =====================================================
        const KEY = 'miekiro_keranjang';   // nama tempat keranjang disimpan di browser
        const LEVEL_PEDAS = <?php echo json_encode($level_list); ?>;

        const cartBody    = document.getElementById('cartBody');
        const cartBadge   = document.getElementById('cartBadge');
        const cartTotal   = document.getElementById('cartTotal');
        const btnPesan    = document.getElementById('btnPesan');
        const pesanStatus = document.getElementById('pesanStatus');

        // =====================================================
        // Fungsi bantu
        // =====================================================
        function rupiah(angka) {
            return 'Rp ' + Number(angka).toLocaleString('id-ID');
        }

        // Mengamankan teks supaya tidak dibaca sebagai kode HTML
        function aman(teks) {
            return String(teks)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        // Keranjang disimpan di browser (localStorage), jadi tidak hilang saat halaman di-refresh
        function ambilKeranjang() {
            return JSON.parse(localStorage.getItem(KEY) || '[]');
        }

        function simpanKeranjang(data) {
            localStorage.setItem(KEY, JSON.stringify(data));
            tampilkanKeranjang();
        }

        // =====================================================
        // 1. Filter kategori di sidebar
        // =====================================================
        const tombolKategori = document.querySelectorAll('.cat-btn');
        const kartuMenu      = document.querySelectorAll('.produk-card');

        tombolKategori.forEach(tombol => {
            tombol.addEventListener('click', () => {
                tombolKategori.forEach(t => t.classList.remove('active'));
                tombol.classList.add('active');

                const kategori = tombol.dataset.kat;
                kartuMenu.forEach(kartu => {
                    const tampil = (kategori == 'semua' || kartu.dataset.kat == kategori);
                    kartu.classList.toggle('hide', !tampil);
                });
            });
        });

        // =====================================================
        // 2. Tombol + / - dan Tambah di kartu menu
        // =====================================================
        document.querySelector('.produk-grid').addEventListener('click', event => {
            const aksi = event.target.closest('.produk-aksi');
            if (!aksi) return;

            const angka = aksi.querySelector('.qty-val');
            let jumlah  = parseInt(angka.textContent);

            // Tombol + atau -
            const tombolQty = event.target.closest('.qty-btn');
            if (tombolQty) {
                if (tombolQty.dataset.aksi == 'tambah') {
                    jumlah = jumlah + 1;
                } else if (jumlah > 1) {
                    jumlah = jumlah - 1;
                }
                angka.textContent = jumlah;
                return;
            }

            // Tombol "Tambah" (masukkan ke keranjang)
            const tombolTambah = event.target.closest('.btn-keranjang');
            if (tombolTambah) {
                const keranjang = ambilKeranjang();
                const id        = aksi.dataset.id;
                const item      = keranjang.find(i => i.id == id);

                if (item) {
                    item.qty = item.qty + jumlah;    // menu sudah ada, tambah jumlahnya
                } else {
                    keranjang.push({
                        id: id,
                        nama: aksi.dataset.nama,
                        harga: parseInt(aksi.dataset.harga),
                        gambar: aksi.dataset.gambar,
                        kategori: aksi.dataset.kategori,
                        level: LEVEL_PEDAS.length ? LEVEL_PEDAS[0].id : 1,
                        qty: jumlah
                    });
                }
                simpanKeranjang(keranjang);

                angka.textContent = 1;               // kembalikan jumlah ke 1
                const teksAsli = tombolTambah.innerHTML;
                tombolTambah.innerHTML = '<i class="bi bi-check2"></i> Ditambahkan';
                setTimeout(() => { tombolTambah.innerHTML = teksAsli; }, 1200);
            }
        });

        // =====================================================
        // 3. Tampilkan isi keranjang
        // =====================================================

        // Pilihan level pedas (hanya untuk menu mie)
        function pilihanLevel(item) {
            if (item.kategori != 'mie') return '';

            let opsi = '';
            LEVEL_PEDAS.forEach(l => {
                const dipilih = (l.id == item.level) ? 'selected' : '';
                opsi += `<option value="${l.id}" ${dipilih}>Pedas: ${aman(l.nama_level)}</option>`;
            });

            return `<div class="cart-level"><select data-level="${item.id}">${opsi}</select></div>`;
        }

        function tampilkanKeranjang() {
            const keranjang = ambilKeranjang();
            let html        = '';
            let jumlahItem  = 0;
            let totalHarga  = 0;

            keranjang.forEach(item => {
                jumlahItem = jumlahItem + item.qty;
                totalHarga = totalHarga + item.qty * item.harga;

                html += `
                    <div class="cart-item">
                        <img src="${aman(item.gambar)}" alt="" referrerpolicy="no-referrer">
                        <div class="cart-info">
                            <div class="cart-nama">${aman(item.nama)}</div>
                            <div class="cart-harga">${rupiah(item.harga)} x ${item.qty} = ${rupiah(item.harga * item.qty)}</div>
                            ${pilihanLevel(item)}
                            <div class="qty">
                                <button type="button" class="qty-btn" data-cart="kurang" data-id="${item.id}"><i class="bi bi-dash"></i></button>
                                <span class="qty-val">${item.qty}</span>
                                <button type="button" class="qty-btn" data-cart="tambah" data-id="${item.id}"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <button type="button" class="cart-hapus" data-cart="hapus" data-id="${item.id}"><i class="bi bi-trash"></i></button>
                    </div>`;
            });

            if (keranjang.length == 0) {
                html = '<p class="cart-kosong">Keranjangmu masih kosong.</p>';
            }

            cartBody.innerHTML    = html;
            cartTotal.textContent = rupiah(totalHarga);
            cartBadge.textContent = jumlahItem;
            cartBadge.style.display = (jumlahItem > 0) ? 'grid' : 'none';
        }

        // Tombol + / - / hapus di dalam keranjang
        cartBody.addEventListener('click', event => {
            const tombol = event.target.closest('[data-cart]');
            if (!tombol) return;

            let keranjang  = ambilKeranjang();
            const id       = tombol.dataset.id;
            const aksi     = tombol.dataset.cart;
            const item     = keranjang.find(i => i.id == id);
            if (!item) return;

            if (aksi == 'tambah') item.qty = item.qty + 1;
            if (aksi == 'kurang') item.qty = item.qty - 1;
            if (aksi == 'hapus')  item.qty = 0;

            // Item dengan jumlah 0 dihapus dari keranjang
            keranjang = keranjang.filter(i => i.qty > 0);
            simpanKeranjang(keranjang);
        });

        // Ganti level pedas di dalam keranjang
        cartBody.addEventListener('change', event => {
            const pilihan = event.target.closest('[data-level]');
            if (!pilihan) return;

            const keranjang = ambilKeranjang();
            const item      = keranjang.find(i => i.id == pilihan.dataset.level);
            if (item) {
                item.level = parseInt(pilihan.value);
                localStorage.setItem(KEY, JSON.stringify(keranjang));
            }
        });

        // =====================================================
        // 4. Kirim pesanan ke backend/proses_pesanan.php
        // =====================================================
        function tampilStatus(teks, jenis) {
            pesanStatus.textContent = teks;
            pesanStatus.className   = 'pesan-status ' + jenis;
        }

        btnPesan.addEventListener('click', async () => {
            const keranjang = ambilKeranjang();

            if (keranjang.length == 0) {
                tampilStatus('Keranjang masih kosong.', 'gagal');
                return;
            }

            // Data yang dikirim: hanya id, jumlah, dan level pedas (harga dihitung ulang di server)
            const dataKirim = {
                items: keranjang.map(i => ({ id: i.id, qty: i.qty, level: i.level })),
                metode_pembayaran: document.getElementById('metodeBayar').value,
                catatan: document.getElementById('catatanPesanan').value
            };

            btnPesan.disabled    = true;
            btnPesan.textContent = 'Memproses...';
            tampilStatus('', '');

            try {
                const respon = await fetch('backend/proses_pesanan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataKirim)
                });
                const hasil = await respon.json();

                if (hasil.success) {
                    simpanKeranjang([]);                                  // kosongkan keranjang
                    document.getElementById('catatanPesanan').value = '';
                    tampilStatus(hasil.message, 'ok');
                } else {
                    tampilStatus(hasil.message, 'gagal');
                }
            } catch (error) {
                tampilStatus('Tidak bisa terhubung ke server.', 'gagal');
            }

            btnPesan.disabled    = false;
            btnPesan.textContent = 'Buat Pesanan';
        });

        // Tampilkan keranjang saat halaman pertama dibuka
        tampilkanKeranjang();
    </script>
</body>
</html>