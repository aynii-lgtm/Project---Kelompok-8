<?php 
    include 'backend/koneksi.php';
    session_start();
    if(!isset($_SESSION['status']) || $_SESSION['status'] != true) {
        header("Location: login.php?pesan=belum_login");
        exit();
    }

    $query = "SELECT * FROM menu";
    $result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Menu - Mie Kiro</title>
</head>
<body>
    
</body>
</html>