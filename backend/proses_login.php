<?php
    include __DIR__ . '/koneksi.php';
    session_start();

    $email = mysqli_real_escape_string($koneksi, trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $ingat_saya = isset($_POST['ingat_saya']);

    if (empty($email) || empty($password)) {
        header("Location: ../login.php?pesan=harus_diisi");
        exit();
    }

    $query = "SELECT * FROM akun WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($password == $user['password']) {
            $_SESSION['status'] = true;
            $_SESSION['email'] = $user['email'];
            $_SESSION['id'] = $user['id'];

            if ($ingat_saya) {
                setcookie('remember_id', $user['id'], time() + (86400 * 30), "/");
                setcookie('remember_email', $user['email'], time() + (86400 * 30), "/");
            } else {
                setcookie('remember_id', '', time() - 3600, "/");
                setcookie('remember_email', '', time() - 3600, "/");
            }

            header("Location: ../daftar_menu.php"); 
            exit();

        } else {
            header("Location: ../login.php?pesan=data_salah");
            exit();
        }
    } else {
        header("Location: ../login.php?pesan=data_gaada");
        exit();
    }
?>