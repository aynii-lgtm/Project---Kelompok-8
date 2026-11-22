<?php
    include __DIR__ . '/koneksi.php';
    session_start();

    if (!isset($_SESSION['status']) || $_SESSION['status'] != true) {
        header("Location: ../login.php?pesan=belum_login");
        exit();
    }

    // Pakai id dari session, bukan dari form, supaya tidak bisa diubah user lain
    $id = (int) $_SESSION['id'];

    $email    = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($email)) {
        header("Location: ../profile.php?pesan=gagal");
        exit();
    }

    // Cek email dipakai akun lain
    $cek = mysqli_query($koneksi, "SELECT id FROM akun WHERE email='$email' AND id != '$id'");
    if (mysqli_num_rows($cek) > 0) {
        header("Location: ../profile.php?pesan=email_terpakai");
        exit();
    }

    $set = "email='$email'";

    // Password: hanya diganti kalau diisi
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $set .= ", password='" . mysqli_real_escape_string($koneksi, $hash) . "'";
    }

    // Foto: hanya diproses kalau ada file yang diunggah
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tipe_boleh = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($_FILES['foto']['tmp_name']);

        if (!isset($tipe_boleh[$mime]) || $_FILES['foto']['size'] > 1024 * 1024) {
            header("Location: ../profile.php?pesan=foto_tidak_valid");
            exit();
        }

        $folder = __DIR__ . '/../uploads/';
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        // Nama file tetap per user (u<id>.<ext>), jadi tidak perlu kolom di database
        foreach (glob($folder . 'u' . $id . '.*') as $lama) {
            unlink($lama);
        }
        move_uploaded_file($_FILES['foto']['tmp_name'], $folder . 'u' . $id . '.' . $tipe_boleh[$mime]);
    }

    if (mysqli_query($koneksi, "UPDATE akun SET $set WHERE id='$id'")) {
        header("Location: ../profile.php?pesan=berhasil");
    } else {
        header("Location: ../profile.php?pesan=gagal");
    }
    exit();
?>