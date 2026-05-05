<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php"); exit;
}

$pembeli_id = $_SESSION['id'];

// Simulasi Proses Top-Up Saldo
if (isset($_POST['topup'])) {
    $nominal = (int)$_POST['nominal'];
    mysqli_query($conn, "UPDATE users SET saldo = saldo + $nominal WHERE id = '$pembeli_id'");
    echo "<script>alert('Top-up berhasil! Saldo SELLA Pay Anda bertambah Rp " . number_format($nominal, 0, ',', '.') . ".'); window.location='dompet_pembeli.php';</script>";
    exit;
}

$q_user = mysqli_query($conn, "SELECT saldo, sella_coins FROM users WHERE id = '$pembeli_id'");
$user = mysqli_fetch_assoc($q_user);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dompet Saya - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Courier New', Courier, monospace; }
        
        /* HEADER KHUSUS PC (DESKTOP) */
        .desktop-header { background: linear-gradient(135deg, #118a44, #18b35a); padding: 15px 0; color: white; position: sticky; top: 0; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .desktop-header a { color: white; text-decoration: none; }
        
        /* HEADER KHUSUS HP (MOBILE) */
        .mobile-header { background: #fff; padding: 15px 0; position: sticky; top: 0; z-index: 1030; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .mobile-header a { color: #333; font-size: 24px; text-decoration: none; margin-right: 15px; }
        
        /* KARTU SALDO & KOIN */
        .wallet-card { background: linear-gradient(135deg, #118a44, #18b35a); color: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(17, 138, 68, 0.2); position: relative; overflow: hidden; height: 100%; transition: 0.3s; }
        .wallet-card:hover { transform: translateY(-5px); }
        .wallet-card::after { content: ''; position: absolute; top: -20px; right: -20px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%; }
        
        .coin-card { background: linear-gradient(135deg, #f6c23e, #dda20a); color: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(246, 194, 62, 0.2); position: relative; overflow: hidden; height: 100%; transition: 0.3s; }
        .coin-card:hover { transform: translateY(-5px); }
        
        .section-card { background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; border: 1px solid #eee; }
        
        /* BOTTOM NAV (MOBILE) */
        .bottom-nav { background: #fff; position: fixed; bottom: 0; width: 100%; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 1030; display: flex; padding: 10px 0; }
        .nav-item { flex: 1; text-align: center; color: #888; text-decoration: none; font-size: 12px; display: flex; flex-direction: column; align-items: center; }
        .nav-item.active { color: #118a44; font-weight: 600; }
        .nav-item i { font-size: 22px; margin-bottom: 2px; }
        
        /* RESPONSIVE LOGIC */
        @media (min-width: 768px) {
            .mobile-only { display: none !important; }
        }
        @media (max-width: 767px) {
            .desktop-only { display: none !important; }
            body { padding-bottom: 80px; }
        }
    </style>
</head>
<body>

<!-- 1. HEADER PC (Tersembunyi di HP) -->
<div class="desktop-header desktop-only">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="marketplace_pembeli.php" class="d-flex align-items-center text-white text-decoration-none">
            <h4 class="fw-bold m-0"><i class="bi bi-bag-heart-fill me-2"></i>SELLA</h4>
        </a>
        <div class="d-flex gap-4">
            <a href="marketplace_pembeli.php" class="text-white fs-5" title="Beranda"><i class="bi bi-house-door"></i></a>
            <a href="wishlist.php" class="text-white fs-5" title="Favorit"><i class="bi bi-heart"></i></a>
            <a href="keranjang.php" class="text-white fs-5" title="Keranjang"><i class="bi bi-cart3"></i></a>
            <a href="pesanan_saya.php" class="text-white fs-5" title="Pesanan"><i class="bi bi-receipt"></i></a>
            <a href="logout.php" class="text-white fs-5" title="Keluar"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- 2. HEADER HP (Tersembunyi di PC) -->
<div class="mobile-header mobile-only">
    <div class="container d-flex align-items-center">
        <a href="marketplace_pembeli.php"><i class="bi bi-arrow-left"></i></a>
        <h5 class="m-0 fw-bold">Dompet SELLA</h5>
    </div>
</div>

<!-- 3. KONTEN UTAMA -->
<div class="container mt-4 mb-5" style="max-width: 1000px;">
    
    <div class="d-flex align-items-center mb-4 desktop-only">
        <h4 class="fw-bold m-0"><i class="bi bi-wallet2-fill text-success me-2"></i>Dompet Saya</h4>
    </div>

    <!-- PENGATURAN LAYOUT: 2 Kolom di PC, 1 Kolom Numpuk di HP -->
    <div class="row g-4">
        
        <!-- KOLOM KIRI (Saldo, Koin, Arcade) -->
        <div class="col-lg-7">
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="wallet-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span style="font-size: 14px; opacity: 0.9;">Saldo SELLA Pay</span>
                            <i class="bi bi-wallet2 fs-3"></i>
                        </div>
                        <h3 class="fw-bold mb-0">Rp <?php echo number_format($user['saldo'], 0, ',', '.'); ?></h3>
                    </div>
                </div>
                
                <div class="col-sm-6">
                    <div class="coin-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span style="font-size: 14px; opacity: 0.9;">SELLA Coins</span>
                            <i class="bi bi-coin fs-3"></i>
                        </div>
                        <h3 class="fw-bold mb-0"><?php echo number_format($user['sella_coins'] ?? 0, 0, ',', '.'); ?> <span style="font-size: 14px; font-weight: normal;">Koin</span></h3>
                    </div>
                </div>
            </div>

            <!-- TIKET MASUK ZONA GAME ARCADE -->
            <div class="section-card p-4 shadow-sm" style="background: linear-gradient(135deg, #212529, #343a40); color: white; border: none; border-radius: 15px;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-center text-md-start">
                    <div class="mb-3 mb-md-0">
                        <h5 class="fw-bold mb-1 text-warning"><i class="bi bi-joystick me-2"></i>SELLA Arcade Zone</h5>
                        <p class="small text-light m-0 opacity-75">Mainkan mini-game seru dan kumpulkan koin gratis setiap hari!</p>
                    </div>
                    <a href="game_koin.php" class="btn btn-warning fw-bold rounded-pill shadow-lg px-4 py-2" style="white-space: nowrap;">
                        <i class="bi bi-play-fill me-1"></i> Main Sekarang
                    </a>
                </div>
            </div>
        </div>
        
        <!-- KOLOM KANAN (Form Top Up) -->
        <div class="col-lg-5">
            <div class="section-card h-100 border-top border-success border-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle-fill text-success me-2"></i>Isi Saldo (Top-Up)</h6>
                <p class="text-muted small mb-4">Pilih nominal top-up. (Ini adalah simulasi, uang virtual akan langsung masuk ke akun Anda).</p>
                
                <form action="" method="POST">
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="nominal" id="btn-50k" value="50000" required>
                            <label class="btn btn-outline-success w-100 fw-bold py-2" for="btn-50k">50 Ribu</label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="nominal" id="btn-100k" value="100000">
                            <label class="btn btn-outline-success w-100 fw-bold py-2" for="btn-100k">100 Ribu</label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="nominal" id="btn-500k" value="500000">
                            <label class="btn btn-outline-success w-100 fw-bold py-2" for="btn-500k">500 Ribu</label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="nominal" id="btn-1m" value="1000000">
                            <label class="btn btn-outline-success w-100 fw-bold py-2" for="btn-1m">1 Juta</label>
                        </div>
                    </div>
                    <button type="submit" name="topup" class="btn btn-success w-100 fw-bold rounded-pill shadow-sm py-3">Proses Top-Up Sekarang</button>
                </form>
            </div>
        </div>

    </div> <!-- Akhir Row -->
</div>

<!-- 4. BOTTOM NAV (Khusus Mobile, Hilang di PC) -->
<div class="bottom-nav mobile-only">
    <a href="marketplace_pembeli.php" class="nav-item">
        <i class="bi bi-house-door"></i><span>Beranda</span>
    </a>
    <a href="keranjang.php" class="nav-item">
        <i class="bi bi-cart3"></i><span>Keranjang</span>
    </a>
    <a href="pesanan_saya.php" class="nav-item">
        <i class="bi bi-receipt"></i><span>Pesanan</span>
    </a>
    <a href="dompet_pembeli.php" class="nav-item active">
        <i class="bi bi-wallet2-fill"></i><span>Dompet</span>
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>