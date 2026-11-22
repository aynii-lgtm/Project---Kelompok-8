<?php
include __DIR__ . '/koneksi.php';
session_start();

// 1. Ambil data dari form login
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$ingat_saya = isset($_POST['ingat_saya']); // Cek apakah checkbox dicentang

// 2. Validasi input tidak boleh kosong
if (empty($email) || empty($password)) {
    header("Location: ../login.php?pesan=harus_diisi");
    exit();
}

// 3. Cari user di database
$query = "SELECT * FROM akun WHERE email = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // 4. Verifikasi password (sesuaikan jika pakai hash/password_verify)
    // Jika masih plain text seperti kodemu sebelumnya:
    if ($password === $row['password']) { 
        
        // Set Session (Wajib ada)
        $_SESSION['status'] = true;
        $_SESSION['email'] = $row['email'];
        $_SESSION['id'] = $row['id'];

        // --- LOGIKA COOKIE TAMBAHAN ---
        if ($ingat_saya) {
            // Simpan ID user ke cookie selama 30 hari
            // Parameter terakhir "/" artinya cookie berlaku untuk seluruh website
            setcookie('remember_id', $row['id'], time() + (86400 * 30), "/");
            
            // Opsional: Simpan email biar bisa auto-fill form login nanti
            setcookie('remember_email', $row['email'], time() + (86400 * 30), "/");
        } else {
            // Jika TIDAK centang "Ingat Saya", pastikan cookie lama terhapus
            setcookie('remember_id', '', time() - 3600, "/");
            setcookie('remember_email', '', time() - 3600, "/");
        }
        // -------------------------------

        // Redirect ke halaman menu/tentang kami
        header("Location: ../daftar_menu.php"); 
        exit();

    } else {
        // Password salah
        header("Location: ../login.php?pesan=data_salah");
        exit();
    }
} else {
    // Email tidak ditemukan
    header("Location: ../login.php?pesan=data_gaada");
    exit();
}
?>