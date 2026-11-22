<?php 
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <title>Sign Up - Mie Kiro</title>

    <style>
        body {
            background-image: url('pictures/bg_login.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #signup-card {
            width: 100%;
            max-width: 595px; /* Batasi lebar maksimal biar rapi di layar besar */
            background-color: rgba(214, 18, 24, 0.95); /* Merah transparan dikit */
            border-radius: 15px;
            padding: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.75); /* Tambah bayangan */
        }

        #signup-title {
            text-align: center;
            color: white;
            margin-bottom: 35px;
        }

        #signup-title h1{
            font-weight: bold;
            margin-bottom: 0;
        }

        .form-control {
            background-color: #f79396;
            border-radius: 50px;
            padding: 15px 20px;
            border: none;
            margin-bottom: 25px;
        }

        .form-control::placeholder {
            color: white;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 25px;
        }

        .input-wrapper .form-control {
            padding-right: 50px;
            width: 100%;
        }

        .input-wrapper i {
            position: absolute;
            right: 20px;       /* Jarak dari tepi kanan */
            top: 50%;          /* Posisi vertikal tengah */
            transform: translateY(-50%); /* Koreksi posisi tengah */
            color: white; /* Warna putih transparan */
            font-size: 1.2rem;
            pointer-events: none; /* Agar klik tetap tembus ke input */
        }

        #signup-button {
            color: #d61218;
            background-color: white;
            width: 100%;
            border-radius: 50px;
            padding: 15px;
            font-weight:bolder;
            border: none;
        }

        #signup-button:hover {
            background-color: #d61218;
        }

        p, a {
            color: white;
            font-size: 15px;
        }

        a:hover {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="signup-card">
            <div id="signup-title">
                <h1>Welcome to MieKiro</h1>
                <p>Create your account first</p>
            </div>
            <?php 
                if(isset($_GET['pesan'])) {
                    $pesan = $_GET['pesan'];
                    if($pesan == "belum_login") {
                        echo '
                            <div class="alert alert-warning">
                                Email sudah digunakan!
                            </div>
                        ';
                    } elseif($pesan == "akun_dibuat") {
                        echo '
                            <div class="alert alert-success">
                                Akun berhasil dibuat
                            </div>
                        ';
                    }
                }
            ?>
            
            <form method="POST" action="backend/proses_signup.php">
                <div class="input-wrapper">
                    <input type="text" class="form-control" id="name" name="email" placeholder="Email" required>
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="input-wrapper">
                    <input type="password" class="form-control" name="password" id="password" minlength="8" placeholder="Password" required>
                    <i class="bi bi-lock-fill"></i>
                </div>
                <input type="hidden" name="role" value="customer">
                
                <button type="submit" class="btn mt-3" id="signup-button">Create Account</button>
            </form>

            <div class="text-center mt-3">
                <a href="login.php">Sudah punya akun? Login disini</a>
            </div>
        </div>
    </div>
</body>
</html>