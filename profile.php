<?php
    include __DIR__ . '/backend/koneksi.php';
    session_start();

    if (!isset($_SESSION['status']) || $_SESSION['status'] != true) {
        header("Location: login.php?pesan=belum_login");
        exit();
    }

    $id = (int) $_SESSION['id'];
    $query = mysqli_query($koneksi, "SELECT * FROM akun WHERE id = '$id'");
    $user = mysqli_fetch_assoc($query);

    $email = $user['email'];
    $foto = '';
    if (!empty($user['foto_profil'])) {
        $foto = 'uploads/' . htmlspecialchars($user['foto_profil']);
    }

    if (!$user) {
        header("Location: backend/logout.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile - Mie Kiro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap & ikon (untuk navbar), harus sebelum style.css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>

        * { box-sizing: border-box; margin: 0; padding: 0; }


        /* Area di bawah navbar: card dipusatkan di sini */
        .page-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 66px); /* tinggi layar dikurangi tinggi navbar */
            padding: 24px;
        }

        /* Foto profil di navbar */
        .nav-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
            display: block;
        }

        /* .card diganti .profil-card supaya tidak bentrok dengan Bootstrap */
        .profil-card {
            width: 100%;
            max-width: 800px;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .10);
            overflow: hidden;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 16px 16px 17px;
            border-bottom: 1px solid #ececec;
            font-size: 17px;
            color: #000;
        }

        .close {
            display: grid;
            place-items: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            color: #d61218;
            text-decoration: none;
        }
        .close:hover { background: #f1f1f1; }

        .notice {
            margin: 14px 16px 0;
            padding: 9px 12px;
            border-radius: 9px;
            font-size: 12.5px;
        }
        .notice.ok   { background: #e8f6ec; color: #1d6b35; }
        .notice.warn { background: #fff4de; color: #8a5a00; }
        .notice.fail { background: #fdeaea; color: #a52a2a; }

        .card-body {
            display: flex;
            padding: 20px 20px 20px 20px;
        }

        /* Kolom kiri: foto */
        .photo {
            flex: 0 0 144px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 14px 16px 0 0;
            margin-right: 20px;
            border-right: 1px dashed #e4e4e4;
            text-align: center;
        }

        .avatar {
            position: relative;
            width: 54px;
            height: 54px;
            margin-bottom: 18px;
        }
        .avatar-circle {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #ebebeb;
            overflow: hidden;
        }
        .avatar-circle svg, .avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .avatar-badge {
            position: absolute;
            top: -3px;
            right: -6px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #777;
            color: #fff;
            display: grid;
            place-items: center;
            border: 0;
            cursor: pointer;
        }

        .photo-title { font-size: 13px; font-weight: 500; }
        .photo-hint { margin-top: 6px; font-size: 11px; color: #666; }
        .photo-error { margin-top: 6px; font-size: 11px; color: #c0392b; min-height: 14px; }

        .profil-card .btn {
            font: inherit;
            font-size: 13px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }
        .profil-card .btn:focus-visible, .field input:focus-visible, .close:focus-visible, .avatar-badge:focus-visible {
            outline: 2px solid #2b5cff;
            outline-offset: 2px;
        }

        .btn-outline {
            margin-top: 8px;
            padding: 8px 14px;
            background: #fff;
            border: 1px solid #e2e2e2;
            color: #d61218;
        }
        .btn-outline:hover { background: #f7f7f7; }

        /* Kolom kanan: form */
        .fields { flex: 1; min-width: 0; padding-top: 4px; }

        .field { margin-bottom: 12px; }
        .field label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
        }
        .required { color: #2b5cff; }
        .info {
            display: inline-grid;
            place-items: center;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #d4d4d4;
            color: #fff;
            font-size: 9px;
            font-weight: 500;
            cursor: help;
        }

        .field input {
            width: 100%;
            height: 34px;
            padding: 0 10px;
            border: 1px solid #e5e5e5;
            border-radius: 9px;
            background: #fff;
            font: inherit;
            font-size: 13px;
            color: #d61218;
        }
        .field input::placeholder { color: #a5a5a5; }
        .field input:hover { border-color: #d6d6d6; }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 18px;
        }
        .btn-cancel {
            padding: 0 16px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            background: #fff;
            border: 1px solid #d61218;
            color: #d61218;
            text-decoration: none;
        }

        .btn-save {
            padding: 0 14px;
            height: 32px;
            background: #d61218;
            color: #fff;
        }
        .btn-save:hover { background: #000; }

        @media (max-width: 480px) {
            .card-body { flex-direction: column; }
            .photo {
                flex: none;
                margin: 0 0 16px;
                padding: 0 0 16px;
                border-right: 0;
                border-bottom: 1px dashed #e4e4e4;
            }
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
                    <li><a href="tentang_kami.php" class="nav-link">Tentang Kami</a></li>
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
                <a href="backend/logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- Card profil -->
    <div class="page-wrap">
        <form class="profil-card" action="backend/proses_profil.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="card-header">
                <span>Profil saya</span>
                <a class="close" href="javascript:history.back()" aria-label="Tutup">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                        <path d="M3 3l8 8M11 3l-8 8"/>
                    </svg>
                </a>
            </div>

            <?php
                $pesan = $_GET['pesan'] ?? '';
                if ($pesan == 'email_terpakai') {
                    echo '<div class="notice warn" role="alert">Email sudah dipakai, gunakan email lain.</div>';
                } elseif ($pesan == 'berhasil') {
                    echo '<div class="notice ok" role="status">Profil berhasil diperbarui.</div>';
                } elseif ($pesan == 'gagal') {
                    echo '<div class="notice fail" role="alert">Gagal memperbarui profil.</div>';
                } elseif ($pesan == 'foto_tidak_valid') {
                    echo '<div class="notice fail" role="alert">Foto harus JPG, PNG, atau WEBP dan maksimal 1MB.</div>';
                }
            ?>

            <div class="card-body">
                <!-- Foto profil -->
                <div class="photo">
                    <div class="avatar">
                        <div class="avatar-circle" id="avatarCircle">
                            <?php if ($foto): ?>
                            <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto profil">
                            <?php else: ?>
                            <svg viewBox="0 0 54 54" aria-hidden="true">
                                <rect width="54" height="54" fill="#ebebeb"/>
                                <circle cx="27" cy="22" r="8" fill="#fff"/>
                                <path d="M10 54c0-10 7-17 17-17s17 7 17 17z" fill="#f6f6f6"/>
                            </svg>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="avatar-badge" id="badgeBtn" aria-label="Tambah foto">
                            <svg width="10" height="10" viewBox="0 0 10 10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                                <path d="M5 1.5v7M1.5 5h7"/>
                            </svg>
                        </button>
                    </div>

                    <div class="photo-title">Unggah foto</div>
                    <div class="photo-hint">Ukuran maks: 1MB</div>
                    <div class="photo-error" id="photoError" role="alert"></div>

                    <button type="button" class="btn btn-outline" id="addBtn">Pilih foto</button>
                    <input type="file" id="foto" name="foto" accept="image/png,image/jpeg,image/webp" hidden>
                </div>

                <!-- Email & password -->
                <div class="fields">
                    <div class="field">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="nama@email.com" required>
                    </div>

                    <div class="field">
                        <label for="password">
                            Password
                            <span class="info" title="Kosongkan jika tidak ingin mengganti password">i</span>
                        </label>
                        <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diganti" autocomplete="new-password">
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-save">Simpan perubahan</button>
                        <button type="button" class="btn btn-cancel"
                                onclick="if(confirm(
                                    'Yakin ingin menghapus akun ini secara permanen? Semua data pesanan akan hilang.')) window.location.href='backend/delete_account.php?id=<?php echo $id; ?>'"
                        >Hapus akun</button>
                    </div>
                    
                </div>
            </div>
        </form>
    </div>

<script>
    const input  = document.getElementById('foto');
    const circle = document.getElementById('avatarCircle');
    const error  = document.getElementById('photoError');
    const MAX    = 1024 * 1024; // 1MB

    document.getElementById('addBtn').addEventListener('click', () => input.click());
    document.getElementById('badgeBtn').addEventListener('click', () => input.click());

    input.addEventListener('change', () => {
        const file = input.files[0];
        error.textContent = '';
        if (!file) return;

        if (file.size > MAX) {
            error.textContent = 'Ukuran foto melebihi 1MB.';
            input.value = '';
            return;
        }

        const img = document.createElement('img');
        img.alt = 'Foto profil';
        img.src = URL.createObjectURL(file);
        circle.replaceChildren(img);
    });
</script>

</body>
</html>
