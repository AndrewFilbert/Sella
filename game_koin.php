<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: login.php"); exit;
}

$pembeli_id = $_SESSION['id'];
$today = date('Y-m-d');
$max_koin_harian = 100; // Batas koin per hari

// SIHIR AUTO-UPDATE DATABASE: Otomatis bikin kolom limit harian jika belum ada
$cek_kolom = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'tgl_main'");
if(mysqli_num_rows($cek_kolom) == 0){
    mysqli_query($conn, "ALTER TABLE users ADD limit_koin INT DEFAULT 0 AFTER sella_coins, ADD tgl_main DATE NULL AFTER limit_koin");
}

// Ambil data user
$q_user = mysqli_query($conn, "SELECT sella_coins, limit_koin, tgl_main FROM users WHERE id = '$pembeli_id'");
$user = mysqli_fetch_assoc($q_user);

// Reset limit jika hari sudah berganti
$limit_sekarang = ($user['tgl_main'] == $today) ? $user['limit_koin'] : 0;

// PROSES AJAX SIMPAN KOIN
if (isset($_POST['klaim_koin'])) {
    $koin_dapat = (int)$_POST['koin'];
    
    if($koin_dapat > 0) {
        $sisa_kuota = $max_koin_harian - $limit_sekarang;
        $koin_masuk = min($koin_dapat, $sisa_kuota); // Hanya masukkan sisa limit yang tersedia
        
        if($koin_masuk > 0){
            // Update saldo koin, limit harian, dan tanggal terakhir main
            mysqli_query($conn, "UPDATE users SET sella_coins = sella_coins + $koin_masuk, limit_koin = limit_koin + $koin_masuk, tgl_main = '$today' WHERE id = '$pembeli_id'");
        }
    }
    echo "sukses"; // Selalu kirim 'sukses' walau dapat 0 agar game bisa direstart
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sella Arcade - Kumpulkan Koin!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body { background-color: #212529; font-family: 'Courier New', Courier, monospace; color: white; display: flex; flex-direction: column; align-items: center; min-height: 100vh; margin: 0; padding-top: 20px; }
        .top-nav { width: 100%; max-width: 800px; display: flex; justify-content: space-between; padding: 0 20px; margin-bottom: 20px; }
        .top-nav a { color: white; font-size: 24px; text-decoration: none; }
        
        /* KANVAS GAME BERGAYA RETRO */
        #gameCanvas { background-color: #87CEEB; border: 4px solid #fff; border-radius: 10px; box-shadow: 0 0 20px rgba(255, 255, 255, 0.2); max-width: 100%; display: block; margin: 0 auto; image-rendering: pixelated; }
        
        .hud { display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 800px; padding: 10px 20px; background: rgba(0,0,0,0.5); border-radius: 10px; margin-bottom: 20px; }
        .hud-text { font-size: 18px; font-weight: bold; color: #ffc107; }
        
        #gameOverScreen { display: none; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>

<div class="top-nav">
    <a href="dompet_pembeli.php"><i class="bi bi-arrow-left"></i> Kembali</a>
    <span class="fs-5 fw-bold text-warning"><i class="bi bi-coin"></i> Total Koin: <span id="koin_total"><?php echo $user['sella_coins'] ?? 0; ?></span></span>
</div>

<div class="hud">
    <div class="hud-text">Score: <span id="scoreDisplay">0</span></div>
    <div class="text-white small fw-bold">Limit Harian: <span class="<?php echo ($limit_sekarang >= $max_koin_harian) ? 'text-danger' : 'text-success'; ?>"><?php echo $limit_sekarang; ?>/<?php echo $max_koin_harian; ?></span></div>
    <div class="hud-text text-white small d-none d-md-block">Tap / Spasi = Lompat</div>
</div>

<!-- INFO JIKA LIMIT HABIS -->
<?php if($limit_sekarang >= $max_koin_harian): ?>
<div class="alert alert-danger text-center w-100 mb-3 fw-bold" style="max-width: 800px; border-radius: 10px;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> Limit koin harian Anda sudah habis! Anda tetap bisa bermain, tapi koin tidak akan bertambah.
</div>
<?php endif; ?>

<canvas id="gameCanvas" width="800" height="300"></canvas>

<div id="gameOverScreen">
    <h2 class="text-danger fw-bold">GAME OVER</h2>
    <p>Anda mendapatkan <span id="finalScore" class="text-warning fw-bold fs-4">0</span> Koin!</p>
    <button id="btnKlaim" class="btn btn-success fw-bold px-4 rounded-pill mt-2">Klaim & Main Lagi</button>
</div>

<script>
const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");

let score = 0;
let isGameOver = false;
let frameCount = 0;

// --- KONFIGURASI SPRITESHEET 2D ANIMATION ---
const imgPlayer = new Image();
imgPlayer.src = 'uploads/spritesheet.png'; 

const spriteWidth = 64;  
const spriteHeight = 64; 
let frameX = 0;          
let frameY = 0;          
let gameFrame = 0;
const staggerFrames = 5; 

const ROW_IDLE = 0;
const ROW_WALK = 1;
const ROW_JUMP = 2;

// GRAVITASI DIBUAT LEBIH RINGAN AGAR LEBIH MUDAH
const player = { x: 50, y: 176, width: 64, height: 64, dy: 0, gravity: 0.5, jumpPower: -10, isGrounded: false };
const obstacles = [];
const coins = [];

// Kontrol Lompat
function jump() {
    if (player.isGrounded && !isGameOver) {
        player.dy = player.jumpPower;
        player.isGrounded = false;
    }
}
window.addEventListener("keydown", (e) => { if (e.code === "Space") jump(); });
canvas.addEventListener("touchstart", (e) => { e.preventDefault(); jump(); });
canvas.addEventListener("mousedown", jump);

function spawnObjects() {
    // RINTANGAN MUNCUL LEBIH LAMA (150 frame, tadinya 120)
    if (frameCount % 150 === 0) obstacles.push({ x: canvas.width, y: 210, width: 30, height: 30, speed: 3 });
    // KOIN MUNCUL NORMAL
    if (frameCount % 90 === 0) coins.push({ x: canvas.width, y: Math.random() * 80 + 100, radius: 10, speed: 3 });
}

function update() {
    if (isGameOver) return;

    player.y += player.dy;
    player.dy += player.gravity;
    if (player.y >= 176) { player.y = 176; player.isGrounded = true; player.dy = 0; }

    for (let i = 0; i < obstacles.length; i++) {
        let obs = obstacles[i];
        obs.x -= obs.speed; // KECEPATAN MUSUH DIPERLAMBAT MENJADI 3
        if (player.x + 15 < obs.x + obs.width && player.x + player.width - 15 > obs.x && player.y + 10 < obs.y + obs.height && player.y + player.height > obs.y) {
            isGameOver = true;
            document.getElementById('gameOverScreen').style.display = 'block';
            document.getElementById('finalScore').innerText = score;
        }
    }

    for (let i = 0; i < coins.length; i++) {
        let c = coins[i];
        c.x -= c.speed;
        if (c.radius > 0 && player.x + 15 < c.x + c.radius && player.x + player.width - 15 > c.x - c.radius && player.y + 10 < c.y + c.radius && player.y + player.height > c.y - c.radius) {
            score++;
            document.getElementById('scoreDisplay').innerText = score;
            c.radius = 0; 
        }
    }
    frameCount++;
}

function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Tanah
    ctx.fillStyle = "#228B22";
    ctx.fillRect(0, 240, canvas.width, 60);

    // Render Animasi Player
    if (!player.isGrounded) {
        frameY = ROW_JUMP; 
        frameX = 0; 
    } else {
        frameY = ROW_WALK; 
        if (gameFrame % staggerFrames === 0) {
            frameX = (frameX < 5) ? frameX + 1 : 0; 
        }
    }
    
    if(imgPlayer.complete && imgPlayer.naturalHeight !== 0) {
        ctx.drawImage(imgPlayer, frameX * spriteWidth, frameY * spriteHeight, spriteWidth, spriteHeight, player.x, player.y, player.width, player.height);
    } else {
        ctx.fillStyle = "#fa591d";
        ctx.fillRect(player.x, player.y, player.width, player.height);
    }

    ctx.fillStyle = "#dc3545";
    obstacles.forEach(obs => ctx.fillRect(obs.x, obs.y, obs.width, obs.height));

    ctx.fillStyle = "#ffc107";
    coins.forEach(c => {
        if(c.radius > 0) {
            ctx.beginPath();
            ctx.arc(c.x, c.y, c.radius, 0, Math.PI * 2);
            ctx.fill();
            ctx.closePath();
        }
    });
    gameFrame++;
}

function gameLoop() {
    update();
    draw();
    spawnObjects();
    if (!isGameOver) requestAnimationFrame(gameLoop);
}

imgPlayer.onload = function() { gameLoop(); };
imgPlayer.onerror = function() { gameLoop(); };

// --- BUG 0 KOIN FIXED DI SINI ---
$('#btnKlaim').click(function(){
    var koinDidapat = score;
    $(this).prop('disabled', true).text('Memproses...');
    
    // Jika skor 0, langsung reload tanpa perlu buang-buang waktu tembak AJAX
    if (koinDidapat === 0) {
        location.reload();
    } else {
        $.post('game_koin.php', {klaim_koin: true, koin: koinDidapat}, function(res){
            if(res.trim() == 'sukses') location.reload(); 
        });
    }
});
</script>
</body>
</html>