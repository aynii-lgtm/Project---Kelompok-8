<?php
    include __DIR__.'/koneksi.php';

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($email) || empty($password)) {
        header("Location: ../signup.php?pesan=input_kosong");
        exit();
    }

    $cek_email = mysqli_query(
        $koneksi, "SELECT * FROM akun WHERE email='$email'"
    );


    if(mysqli_num_rows($cek_email) > 0) {
        header("Location: ../signup.php?pesan=email_terpakai");
        exit();
    } else {
        $query = "INSERT INTO akun (id, email, password) VALUES (NULL, '$email', '$password')";

        if(mysqli_query($koneksi, $query)) {
            header("Location: ../login.php?pesan=akun_dibuat");
            exit();
        } else {
            echo "Error: " . mysqli_error($koneksi);
        }
    }
?>