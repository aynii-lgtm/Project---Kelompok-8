<?php
    include __DIR__.'/koneksi.php';
    session_start();

    $email = trim($_POST['email']); 
    $password = trim($_POST['password']);

    if(!empty($email) && !empty($password)) {
        $query = "SELECT * FROM akun WHERE email='$email' ";
        $result = mysqli_query($koneksi, $query);

        if($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            if($password == $row['password']) {
                $_SESSION['status'] = true;
                $_SESSION['email'] = $row['email'];
                $_SESSION['id'] = $row['id'];

                header("Location: ../tentang_kami.php");
                exit();
            } else {
                header("Location: ../login.php?pesan=data_salah");
                exit();
            }
        } else {
            header("Location: ../login.php?pesan=data_gaada");
            exit();
        } 
    }
?>