<?php
    session_start();
    if (!isset($_SESSION['status']) || $_SESSION['status'] != true) {
        header("Location: ../login.php?message=not_logged_in");
        exit();
    }
    include __DIR__ . "/connect.php";

    $id = $_GET['id'];
    $query = mysqli_query($connect, "DELETE FROM account WHERE id='$id'");

    if($query){
    header("Location: ../login.php?message=account_deleted");
    exit();
    }
?>