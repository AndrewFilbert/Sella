<?php
session_start();

// Jika pengguna sudah login, langsung arahkan ke jalurnya masing-masing
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: dashboard_admin.php");
    } else if ($_SESSION['role'] == 'penjual') {
        header("Location: dashboard_penjual.php");
    } else {
        header("Location: marketplace_pembeli.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SELLA - Marketplace Terpercaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { 
            background: linear-gradient(135deg, #e8f5e9 0%, #f4f6f9 100%); 
            font-family: 'Courier New', Courier, monospace; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
        }
        
        .hero-section { 
            flex-grow: 1; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            text-align: center; 
            padding: 20px; 
        }
        
        .brand-logo { 
            width: 90px; 
            height: 90px; 
            background: #118a44; 
            color: white; 
            border-radius: 25px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 45px; 
            margin: 0 auto 25px auto; 
            transform: rotate(-10deg); 
            box-shadow: 0 10px 25px rgba(17,138,68,0.3); 
        }
        
        .title-text {
            font-weight: 800;
            color: #212529;
            margin-bottom: 15px;
        }
        
        .subtitle-text {
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto 40px auto;
            font-size: 18px;
            line-height: 1.6;
        }
        
        .btn-masuk { 
            background: linear-gradient(135deg, #118a44, #18b35a); 
            color: white; 
            border-radius: 30px; 
            font-weight: bold; 
            padding: 15px 40px; 
            border: none; 
            transition: 0.3s; 
            font-size: 18px; 
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-masuk:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 20px rgba(17,138,68,0.4); 
            color: white; 
        }
        
        .btn-daftar { 
            background: transparent; 
            color: #118a44; 
            border: 2px solid #118a44; 
            border-radius: 30px; 
            font-weight: bold; 
            padding: 13px 40px; 
            transition: 0.3s; 
            font-size: 18px; 
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-daftar:hover { 
            background: #118a44; 
            color: white; 
        }

        /* Responsive Layouts */
        @media (max-width: 576px) {
            .btn-masuk, .btn-daftar {
                width: 100%;
                margin-bottom: 15px;
            }
            .title-text { font-size: 32px; }
            .subtitle-text { font-size: 15px; }
        }
    </style>
</head>
<body>

<div class="hero-section">
    <div class="container">
        <div class="brand-logo"><i class="bi bi-bag-heart-fill"></i></div>
        <h1 class="title-text display-4">SELLA Marketplace</h1>
        <p class="subtitle-text">Platform belanja modern, responsif, dan terpercaya. Temukan berbagai macam kebutuhan Anda, atau mulai berjualan dan raih keuntungan bersama kami.</p>
        
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="login.php" class="btn-masuk shadow-sm"><i class="bi bi-box-arrow-in-right me-2"></i>Masuk</a>
            <a href="register.php" class="btn-daftar"><i class="bi bi-person-plus me-2"></i>Buat Akun</a>
        </div>
    </div>
</div>

<footer class="text-center py-4 text-muted small border-top" style="background: rgba(255,255,255,0.5);">
    <div class="container">
        &copy; <?php echo date('Y'); ?> SELLA. Dibuat dengan <i class="bi bi-heart-fill text-danger"></i>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>