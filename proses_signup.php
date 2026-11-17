<?php
    include __DIR__.'/koneksi.php';

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if(empty($email) || empty($password)) {
        header("Location: ../signup.php?pesan=input_kosong");
        exit();
    }

    $cek_email = mysqli_query(
        $koneksi, "SELECT * FROM akun WHERE email='$email'"
    );

    if(mysqli_num_rows($cek_email) > 0) {
        header(
            "Location: ../signup.php?pesan=email_terpakai"
            exit();
        );
    } else {
        $query = "INSERT INTO akun (email, password, role) VALUES ('$email', 'password', 'role')";

        if(mysqli_query($koneksi, 4query)) {
            header(
                "Location: ../login.php?pesan=akun_dibuat"
            );
        } else {
            echo "Error: " . mysqli_error($koneksi);
        }
    }
?>