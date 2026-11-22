<?php
// ==========================================
// 1. BAGIAN PERSIAPAN DATA (PHP)
// ==========================================
include __DIR__ . "/backend/koneksi.php";
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] != true) {
    header("Location: login.php?pesan=belum_login");
    exit();
}

$id_user = (int) $_SESSION['id'];
$email_user = isset($_SESSION['email']) ? $_SESSION['email'] : 'User';

$file_foto = glob(__DIR__ . '/uploads/u' . $id_user . '.*');
$foto_profil = $file_foto ? 'uploads/' . basename($file_foto[0]) . '?v=' . filemtime($file_foto[0]) : '';

$daftar_menu = [];
$query_menu = "SELECT * FROM menu ORDER BY FIELD(kategori, 'mie', 'snack', 'minuman'), nama_menu";
$hasil_menu = mysqli_query($koneksi, $query_menu);
while ($baris = mysqli_fetch_assoc($hasil_menu)) {
    $daftar_menu[] = $baris;
}

$kategori_list = array_unique(array_column($daftar_menu, 'kategori'));

$level_pedas_list = [];
$query_level = "SELECT id, nama_level FROM level_pedas ORDER BY id";
$hasil_level = mysqli_query($koneksi, $query_level);
while ($baris = mysqli_fetch_assoc($hasil_level)) {
    $level_pedas_list[] = $baris;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Mie Kiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">

    <style>
        /* ===== STYLE DASAR HALAMAN ===== */
        .about-title { font-weight: 500; font-size: clamp(2rem, 4.2vw, 3rem); line-height: 1.15; color: #1f1a17; margin-bottom: 20px; }
        .about-title em { font-style: italic; font-weight: 400; color: var(--primary-color); }
        .about-lead { max-width: 560px; font-size: 0.85rem; line-height: 1.75; margin-bottom: 0; color: var(--text-muted); }
        
        .section-divider { border: none; background-color: var(--line); height: 1px; opacity: 1; }
        .menu-section { padding-bottom: 40px; } 
        .menu-note { font-size: 0.75rem; margin-top: 20px; margin-bottom: 20px; color: var(--text-muted); }
        
        .menu-layout { display: grid; grid-template-columns: 232px 1fr; gap: 52px; align-items: start; }
        
        /* Sidebar Kategori */
        .cat-list { display: flex; flex-direction: column; gap: 4px; position: sticky; top: 96px; }
        .cat-btn { background: none; border: 0; text-align: left; padding: 11px 14px; border-radius: 10px; font: inherit; font-size: 0.85rem; color: var(--text-dark); cursor: pointer; }
        .cat-btn:hover { background: rgba(214, 18, 24, 0.06); }
        .cat-btn.active { background: #f3e4dc; font-weight: 600; color: var(--primary-color); }

        /* Kartu Menu */
        .produk-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 30px; margin-bottom: 40px; }
        .produk-card { background: #fff; border: 1px solid var(--line, #e6d9d0); border-radius: 18px; overflow: hidden; }
        .produk-card.hide { display: none; }
        .produk-img { position: relative; height: 190px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid var(--line, #e6d9d0); }
        .produk-img img { width: 100%; height: 100%; object-fit: contain; padding: 14px; }
        .badge-habis { position: absolute; top: 12px; left: 12px; background: #1f1a17; color: #fff; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; padding: 4px 10px; border-radius: 20px; }
        .produk-info { padding: 18px 20px 22px; }
        .produk-nama { font-weight: 600; font-size: 1.05rem; color: #1f1a17; margin-bottom: 6px; }
        .produk-desc { font-size: 0.78rem; line-height: 1.55; color: var(--text-muted); margin-bottom: 12px; }
        .produk-harga { font-style: italic; font-size: 0.9rem; color: var(--primary-color); }

        /* Aksi di Kartu */
        .produk-aksi { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-top: 16px; }
        .produk-opsi { width: 100%; display: grid; gap: 8px; }
        .produk-opsi select, .produk-opsi input { width: 100%; font: inherit; font-size: 0.8rem; padding: 6px 10px; border: 1px solid var(--line, #e6d9d0); border-radius: 8px; }
        .qty { display: inline-flex; align-items: center; border: 1px solid var(--line, #e6d9d0); border-radius: 999px; overflow: hidden; }
        .qty-btn { width: 30px; height: 30px; border: 0; background: #fff; display: grid; place-items: center; cursor: pointer; }
        .qty-btn:hover { background: #f3e4dc; }
        .qty-val { min-width: 28px; text-align: center; font-size: 0.85rem; font-weight: 600; }
        .btn-keranjang { border: 0; background: var(--primary-color); color: #fff; font: inherit; font-size: 0.78rem; padding: 7px 14px; border-radius: 999px; cursor: pointer; }
        .btn-keranjang:hover { background: #b00e13; }
        .btn-keranjang:disabled { background: #cfc3bb; cursor: not-allowed; }

        /* Tombol Keranjang Melayang */
        .cart-fab { position: fixed; right: 28px; bottom: 28px; z-index: 1040; width: 56px; height: 56px; border: 0; border-radius: 50%; background: var(--primary-color); color: #fff; font-size: 1.4rem; display: grid; place-items: center; box-shadow: 0 10px 25px rgba(214, 18, 24, .35); cursor: pointer; }
        .cart-fab:hover { background: #b00e13; }
        .cart-badge { position: absolute; top: -4px; right: -4px; min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px; background: #1f1a17; color: #fff; font-size: 0.72rem; font-weight: 600; display: grid; place-items: center; }

        /* Panel Keranjang */
        .cart-item { display: flex; gap: 12px; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--line, #e6d9d0); }
        .cart-item img { width: 56px; height: 56px; border-radius: 12px; object-fit: cover; flex-shrink: 0; }
        .cart-info { flex: 1; min-width: 0; }
        .cart-nama { font-size: 0.85rem; font-weight: 600; }
        .cart-harga { font-size: 0.78rem; margin-bottom: 4px; }
        .cart-custom { font-size: 0.75rem; margin-bottom: 6px; }
        .cart-hapus { border: 0; background: none; color: var(--text-muted); font-size: 1rem; cursor: pointer; }
        .cart-hapus:hover { color: var(--primary-color); }
        .cart-kosong { text-align: center; font-size: 0.85rem; padding: 48px 0; }
        .cart-total { display: flex; justify-content: space-between; font-weight: 600; margin-bottom: 14px; }
        .cart-form select, .cart-form textarea { width: 100%; font: inherit; font-size: 0.8rem; padding: 6px 10px; border: 1px solid var(--line, #e6d9d0); border-radius: 8px; }
        .cart-form { display: grid; gap: 8px; margin-bottom: 14px; }
        .cart-form label { font-size: 0.75rem; font-weight: 500; }
        .cart-form textarea { resize: none; }
        .pesan-status { font-size: 0.8rem; margin-top: 10px; text-align: center; }
        .pesan-status.ok { color: #1d6b35; }
        .pesan-status.gagal { color: #a52a2a; }
        .menu-kosong { grid-column: 1 / -1; font-size: 0.9rem; }

        /* Tampilan HP */
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

    <!-- ===== NAVBAR ===== -->
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
                <span class="d-none d-md-block">Hi, <?php echo htmlspecialchars(explode('@', $email_user)[0]); ?></span>
                <a href="profile.php" class="text-decoration-none">
                    <?php if ($foto_profil) { ?>
                        <img src="<?php echo htmlspecialchars($foto_profil); ?>" class="nav-avatar" alt="Foto profil">
                    <?php } else { ?>
                        <i class="bi bi-person-circle" style="font-size: 1.6rem;"></i>
                    <?php } ?>
                </a>
                <a href="backend/logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- ===== HEADER MENU ===== -->
    <header class="about-hero">
        <div class="container">
            <div class="eyebrow">Menu</div>
            <h1 class="about-title">Setiap mangkuk,<br><em>pedasnya sesuai seleramu.</em></h1>
            <p class="about-lead">Jelajahi seluruh menu Mie Kiro, mulai dari mie pedas andalan sampai pilihan pendamping dan minuman penyegar. Pilih level pedas favoritmu, dari yang menggoda sampai yang menantang! Harga dapat berbeda berdasarkan outlet &amp; kota.</p>
        </div>
    </header>

    <!-- ===== DAFTAR MENU & SIDEBAR ===== -->
    <section class="container menu-section">
        <hr class="section-divider">
        <p class="menu-note">Harga yang ditampilkan dapat berubah seiring waktu.</p>

        <div class="menu-layout">
            <aside class="cat-list">
                <button type="button" class="cat-btn active" data-kat="semua">Semua Menu</button>
                <?php foreach ($kategori_list as $kat) { ?>
                    <button type="button" class="cat-btn" data-kat="<?php echo htmlspecialchars($kat); ?>">
                        <?php echo htmlspecialchars(ucfirst($kat)); ?>
                    </button>
                <?php } ?>
            </aside>

            <div class="produk-grid">
                <?php if (count($daftar_menu) == 0) { ?>
                    <p class="menu-kosong">Menu belum tersedia.</p>
                <?php } ?>

                <?php foreach ($daftar_menu as $m) { 
                    $gambar = $m['gambar'];
                    if ($gambar != '' && !preg_match('#^https?://#', $gambar)) {
                        $gambar = 'uploads/' . $gambar;
                    }
                    $ada_stok = ((int) $m['ketersediaan'] > 0);
                ?>
                    <article class="produk-card" data-kat="<?php echo htmlspecialchars($m['kategori']); ?>">
                        <div class="produk-img">
                            <img src="<?php echo htmlspecialchars($gambar); ?>" alt="<?php echo htmlspecialchars($m['nama_menu']); ?>" loading="lazy">
                            <?php if (!$ada_stok) { ?><span class="badge-habis">Habis</span><?php } ?>
                        </div>
                        <div class="produk-info">
                            <h3 class="produk-nama"><?php echo htmlspecialchars($m['nama_menu']); ?></h3>
                            <p class="produk-desc"><?php echo htmlspecialchars($m['deskripsi']); ?></p>
                            <div class="produk-harga">Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?></div>

                            <?php if ($ada_stok) { ?>
                                <div class="produk-aksi"
                                     data-id="<?php echo (int) $m['id']; ?>"
                                     data-nama="<?php echo htmlspecialchars($m['nama_menu']); ?>"
                                     data-harga="<?php echo (int) $m['harga']; ?>"
                                     data-gambar="<?php echo htmlspecialchars($gambar); ?>"
                                     data-kategori="<?php echo htmlspecialchars($m['kategori']); ?>">
                                    
                                    <div class="produk-opsi">
                                        <?php if ($m['kategori'] == 'mie') { ?>
                                            <select class="opsi-level">
                                                <?php foreach ($level_pedas_list as $l) { ?>
                                                    <option value="<?php echo (int) $l['id']; ?>">Pedas: <?php echo htmlspecialchars($l['nama_level']); ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                        <input type="text" class="opsi-catatan" maxlength="100" placeholder="Custom (opsional), mis. tanpa sayur">
                                    </div>

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

    <!-- ===== TOMBOL KERANJANG MELAYANG ===== -->
    <button type="button" class="cart-fab" id="btnBukaKeranjang">
        <i class="bi bi-bag"></i>
        <span class="cart-badge" id="cartBadge">0</span>
    </button>

    <!-- ===== PANEL OFFCANVAS KERANJANG ===== -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="panelKeranjang">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-semibold">Keranjang</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
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

    <!-- ===== MODAL POPUP SUKSES PESANAN ===== -->
    <div class="modal fade" id="modalSukses" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-4" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                </div>
                <h5 class="fw-bold mb-2">Pesanan Berhasil Dibuat!</h5>
                <p class="text-muted small mb-4">Terima kasih telah memesan di Mie Kiro.</p>
                <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Script Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ===== LOGIKA JAVASCRIPT ===== -->
    <script>
        const KEY_CART = 'miekiro_keranjang';
        const LEVEL_PEDAS_DATA = <?php echo json_encode($level_pedas_list); ?>;
        
        const elCartBody = document.getElementById('cartBody');
        const elCartBadge = document.getElementById('cartBadge');
        const elCartTotal = document.getElementById('cartTotal');
        const elBtnPesan = document.getElementById('btnPesan');
        const elPesanStatus = document.getElementById('pesanStatus');
        const elPanelKeranjang = document.getElementById('panelKeranjang');

        function formatRupiah(angka) { return 'Rp ' + Number(angka).toLocaleString('id-ID'); }
        function escapeHtml(teks) { return String(teks).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
        function getCart() { try { return JSON.parse(localStorage.getItem(KEY_CART) || '[]'); } catch (e) { return []; } }
        function saveCart(data) { localStorage.setItem(KEY_CART, JSON.stringify(data)); renderCart(); }

        // --- FITUR 1: FILTER KATEGORI ---
        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const kat = btn.dataset.kat;
                document.querySelectorAll('.produk-card').forEach(card => {
                    card.classList.toggle('hide', (kat !== 'semua' && card.dataset.kat !== kat));
                });
            });
        });

        // --- FITUR 2: TAMBAH ITEM KE KERANJANG ---
        document.querySelector('.produk-grid').addEventListener('click', e => {
            const aksiDiv = e.target.closest('.produk-aksi');
            if (!aksiDiv) return;
            const qtySpan = aksiDiv.querySelector('.qty-val');
            let currentQty = parseInt(qtySpan.textContent);

            if (e.target.closest('.qty-btn')) {
                const act = e.target.closest('.qty-btn').dataset.aksi;
                if (act === 'tambah') currentQty++;
                else if (currentQty > 1) currentQty--;
                qtySpan.textContent = currentQty;
                return;
            }

            if (e.target.closest('.btn-keranjang')) {
                const cart = getCart();
                const id = aksiDiv.dataset.id;
                const levelSelect = aksiDiv.querySelector('.opsi-level');
                const catatanInput = aksiDiv.querySelector('.opsi-catatan');
                const level = levelSelect ? parseInt(levelSelect.value) : 1;
                const catatan = catatanInput.value.trim();
                const uniqueKey = `${id}|${level}|${catatan.toLowerCase()}`;
                const existingItem = cart.find(i => i.kunci === uniqueKey);
                
                if (existingItem) existingItem.qty += currentQty;
                else {
                    cart.push({
                        kunci: uniqueKey, id: id, nama: aksiDiv.dataset.nama,
                        harga: parseInt(aksiDiv.dataset.harga), gambar: aksiDiv.dataset.gambar,
                        kategori: aksiDiv.dataset.kategori, level: level, catatan: catatan, qty: currentQty
                    });
                }
                saveCart(cart);
                qtySpan.textContent = 1;
                catatanInput.value = '';
                const btnAsli = e.target.closest('.btn-keranjang');
                const teksAwal = btnAsli.innerHTML;
                btnAsli.innerHTML = '<i class="bi bi-check2"></i> Ditambahkan';
                setTimeout(() => { btnAsli.innerHTML = teksAwal; }, 1200);
            }
        });

        // --- FITUR 3: RENDER TAMPILAN KERANJANG ---
        function renderCart() {
            const cart = getCart();
            let html = '', totalItem = 0, totalPrice = 0;

            cart.forEach(item => {
                totalItem += item.qty;
                totalPrice += (item.qty * item.harga);
                let infoText = '';
                if (item.kategori === 'mie') {
                    const lvl = LEVEL_PEDAS_DATA.find(x => x.id == item.level);
                    infoText += 'Pedas: ' + (lvl ? lvl.nama_level : item.level);
                }
                if (item.catatan) infoText += (infoText ? ' • ' : '') + item.catatan;

                html += `<div class="cart-item">
                    <img src="${escapeHtml(item.gambar)}" alt="" referrerpolicy="no-referrer">
                    <div class="cart-info">
                        <div class="cart-nama">${escapeHtml(item.nama)}</div>
                        <div class="cart-harga">${formatRupiah(item.harga)} x ${item.qty} = ${formatRupiah(item.harga * item.qty)}</div>
                        ${infoText ? `<div class="cart-custom">${escapeHtml(infoText)}</div>` : ''}
                        <div class="qty">
                            <button type="button" class="qty-btn" data-cart="kurang" data-kunci="${escapeHtml(item.kunci)}"><i class="bi bi-dash"></i></button>
                            <span class="qty-val">${item.qty}</span>
                            <button type="button" class="qty-btn" data-cart="tambah" data-kunci="${escapeHtml(item.kunci)}"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>
                    <button type="button" class="cart-hapus" data-cart="hapus" data-kunci="${escapeHtml(item.kunci)}"><i class="bi bi-trash"></i></button>
                </div>`;
            });

            elCartBody.innerHTML = cart.length ? html : '<p class="cart-kosong">Keranjangmu masih kosong.</p>';
            elCartTotal.textContent = formatRupiah(totalPrice);
            elCartBadge.textContent = totalItem;
            elCartBadge.style.display = totalItem > 0 ? 'grid' : 'none';
        }

        // --- FITUR 4: UBAH JUMLAH/HAPUS DI DALAM KERANJANG ---
        elCartBody.addEventListener('click', e => {
            const btn = e.target.closest('[data-cart]');
            if (!btn) return;
            const cart = getCart();
            const key = btn.dataset.kunci;
            const action = btn.dataset.cart;
            const itemIndex = cart.findIndex(i => i.kunci === key);
            if (itemIndex !== -1) {
                if (action === 'tambah') cart[itemIndex].qty++;
                if (action === 'kurang') cart[itemIndex].qty--;
                if (action === 'hapus') cart[itemIndex].qty = 0;
                saveCart(cart.filter(i => i.qty > 0));
            }
        });

        // --- FITUR 5: BUKA KERANJANG SECARA MANUAL ---
        const btnBukaKeranjang = document.getElementById('btnBukaKeranjang');
        if (btnBukaKeranjang && elPanelKeranjang) {
            btnBukaKeranjang.addEventListener('click', () => {
                bootstrap.Offcanvas.getOrCreateInstance(elPanelKeranjang).show();
            });
        }

        // --- FITUR 6: KIRIM PESANAN & TAMPILKAN POP-UP SUKSES ---
        elBtnPesan.addEventListener('click', async () => {
            const cart = getCart();
            if (cart.length === 0) {
                elPesanStatus.textContent = 'Keranjang masih kosong.';
                elPesanStatus.className = 'pesan-status gagal';
                return;
            }

            elBtnPesan.disabled = true;
            elBtnPesan.textContent = 'Memproses...';
            elPesanStatus.textContent = '';

            try {
                const response = await fetch('backend/proses_pesanan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        items: cart.map(i => ({ id: i.id, qty: i.qty, level: i.level, catatan: i.catatan })),
                        metode_pembayaran: document.getElementById('metodeBayar').value,
                        catatan: document.getElementById('catatanPesanan').value
                    })
                });
                const result = await response.json();
                
                if (result.success) {
                    saveCart([]);
                    document.getElementById('catatanPesanan').value = '';
                    
                    document.querySelectorAll('.opsi-level').forEach(select => {
                        select.selectedIndex = 0; 
                    });

                    // Tutup panel keranjang dulu
                    const bsOffcanvas = bootstrap.Offcanvas.getInstance(elPanelKeranjang);
                    if(bsOffcanvas) bsOffcanvas.hide();

                    // Tampilkan pop-up sukses
                    const modalSukses = new bootstrap.Modal(document.getElementById('modalSukses'));
                    modalSukses.show();
                } else {
                    elPesanStatus.textContent = result.message;
                    elPesanStatus.className = 'pesan-status gagal';
                }
            } catch (err) {
                elPesanStatus.textContent = 'Gagal terhubung ke server.';
                elPesanStatus.className = 'pesan-status gagal';
            }

            elBtnPesan.disabled = false;
            elBtnPesan.textContent = 'Buat Pesanan';
        });

        renderCart();
    </script>
</body>
</html>