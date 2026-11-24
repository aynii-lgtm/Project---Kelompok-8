<?php
    include "koneksi.php";
    session_start();

    $id = $_GET['id'];
    $query = mysqli_query($koneksi, "DELETE FROM akun WHERE id='$id'");

    session_destroy();

    header("Location: ../login.php?pesan=akun_dihapus");
    exit();
?>