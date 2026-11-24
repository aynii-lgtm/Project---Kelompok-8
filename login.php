<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <title>Login - Mie Kiro</title>
    
    <style>
        body {
            background-image: url('pictures/bg_login.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
        }
        .container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #login-card {
            width: 100%;
            max-width: 595px; 
            background-color: rgba(214, 18, 24, 0.95); 
            border-radius: 15px;
            padding: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.75);
        }
        #login-title {
            text-align: center;
            color: white;
            margin-bottom: 35px;
        }
        #login-title h1{ font-weight: bold; margin-bottom: 0; }
        
        .form-control {
            background-color: #f79396;
            border-radius: 50px;
            padding: 15px 20px;
            border: none;
            margin-bottom: 25px;
            color: white;
        }
        .form-control::placeholder { color: white; opacity: 0.8; }
        
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
            right: 20px;      
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: white;
        }

        #login-button {
            color: #d61218;
            background-color: white;
            width: 100%;
            border-radius: 50px;
            padding: 15px;
            font-weight: bolder;
            border: none;
            transition: all 0.3s ease;
        }
        #login-button:hover {
            background-color: #f79396;
            color: white;
        }
        p, a { color: white; font-size: 15px; }
        a:hover { color: white; text-decoration: underline; }

        .remember-me-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .remember-me-wrapper label {
            color: white;
            margin-bottom: 0;
            cursor: pointer;
        }
        .remember-me-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #d61218;
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="login-card">
            <div id="login-title">
                <h1>Welcome to MieKiro</h1>
                <p>Login to your account first</p>
            </div>

            <?php 
                if(isset($_GET['pesan'])) {
                    $pesan = $_GET['pesan'];

                    if($pesan == 'belum_login') {
                        echo ' <div class="alert alert-warning">Anda belum login</div>';
                    } elseif($pesan == "akun_dibuat") {
                        echo ' <div class = "alert alert-success">Akun berhasil dibuat</div>';
                    } elseif($pesan == "harus_diisi") {
                        echo ' <div class = "alert alert-danger">Email dan password harus diisi</div>';
                    } elseif($pesan == "logout") {
                        echo ' <div class = "alert alert-primary">Anda berhasil logout</div>';
                    } elseif($pesan == "akun_dihapus") {
                        echo ' <div class = "alert alert-primary">Akun berhasil dihapus</div>';
                    } elseif($pesan == "data_salah") {
                        echo '<div class = "alert alert-danger">Email atau password salah</div>';
                    }
                }
            ?>
            
            <form method="POST" action="backend/proses_login.php">
                <div class="input-wrapper">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="input-wrapper">
                    <input type="password" class="form-control" name="password" minlength="8" placeholder="Password" required>
                    <i class="bi bi-lock-fill"></i>
                </div>

                <button type="submit" class="btn" id="login-button">Login</button>
                
                <div class="remember-me-wrapper">
                    <input type="checkbox" name="ingat_saya" id="ingatSaya">
                    <label for="ingatSaya">Ingat saya di perangkat ini</label>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <a href="signup.php">Tidak punya akun? Signup disini</a>
            </div>
        </div>
    </div>
</body>
</html>