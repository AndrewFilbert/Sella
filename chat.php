<?php
session_start();
include 'koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) { 
    header("Location: login.php"); 
    exit; 
}

$saya_id = $_SESSION['id'];
$lawan_id = $_GET['lawan_id'];
$role_saya = $_SESSION['role'];

// Ambil info nama lawan bicara
$q_lawan = mysqli_query($conn, "SELECT username FROM users WHERE id = '$lawan_id'");
$lawan = mysqli_fetch_assoc($q_lawan);

// Jika lawan_id tidak ditemukan/tidak valid, kembali ke beranda
if (!$lawan) {
    header("Location: marketplace_pembeli.php");
    exit;
}

// 1. Proses kirim pesan biasa
if (isset($_POST['kirim_pesan'])) {
    $pesan = mysqli_real_escape_string($conn, $_POST['isi_pesan']);
    if (!empty($pesan)) {
        mysqli_query($conn, "INSERT INTO messages (pengirim_id, penerima_id, pesan) VALUES ('$saya_id', '$lawan_id', '$pesan')");
        header("Location: chat.php?lawan_id=$lawan_id"); 
        exit;
    }
}

// 2. Proses kirim tawaran harga (Hanya Pembeli yang bisa menawar)
if (isset($_POST['kirim_tawaran']) && $role_saya == 'pembeli') {
    $produk_id = $_POST['produk_id'];
    $harga_nego = $_POST['harga_nego'];
    
    // Validasi harga tidak boleh kosong
    if ($harga_nego > 0) {
        $pesan = "Saya ingin menawar produk ini seharga Rp " . number_format($harga_nego, 0, ',', '.');
        
        mysqli_query($conn, "INSERT INTO messages (pengirim_id, penerima_id, pesan, produk_id, harga_tawaran, status_tawaran) 
                             VALUES ('$saya_id', '$lawan_id', '$pesan', '$produk_id', '$harga_nego', 'pending')");
        header("Location: chat.php?lawan_id=$lawan_id"); 
        exit;
    }
}

// Tandai semua pesan dari lawan ini sebagai 'sudah dibaca'
mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE pengirim_id = '$lawan_id' AND penerima_id = '$saya_id'");

// Ambil riwayat chat lengkap antara saya dan lawan
$q_chat = mysqli_query($conn, "SELECT messages.*, products.nama_produk, products.gambar 
    FROM messages 
    LEFT JOIN products ON messages.produk_id = products.id
    WHERE (pengirim_id = '$saya_id' AND penerima_id = '$lawan_id') 
    OR (pengirim_id = '$lawan_id' AND penerima_id = '$saya_id') 
    ORDER BY tanggal ASC");

// Ambil daftar produk milik lawan untuk diisi di form tawar (Khusus jika saya pembeli)
if ($role_saya == 'pembeli') {
    $q_produk_lawan = mysqli_query($conn, "SELECT id, nama_produk, harga FROM products WHERE penjual_id = '$lawan_id'");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Chat: <?php echo $lawan['username']; ?> - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .chat-box { height: 60vh; overflow-y: auto; background: #e5ddd5; padding: 20px; }
        .bubble { max-width: 80%; padding: 10px 15px; border-radius: 15px; margin-bottom: 10px; position: relative; word-wrap: break-word; }
        .me { background: #dcf8c6; color: black; align-self: flex-end; border-bottom-right-radius: 0; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
        .them { background: #ffffff; color: black; align-self: flex-start; border-bottom-left-radius: 0; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
        .tawaran-box { background: white; color: black; border: 2px solid #ffc107; padding: 12px; border-radius: 10px; margin-top: 10px; }
        .time-stamp { font-size: 10px; opacity: 0.7; text-align: right; margin-top: 4px; display: block; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4 mb-4">
    <div class="card shadow border-0 mx-auto" style="max-width: 600px;">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <?php 
                    // Tombol back dinamis sesuai role
                    $back_link = ($role_saya == 'penjual') ? 'list_chat.php' : 'marketplace_pembeli.php';
                ?>
                <a href="<?php echo $back_link; ?>" class="btn btn-sm btn-light me-3"><i class="bi bi-arrow-left"></i></a>
                <div class="d-flex flex-column">
                    <span class="fs-5 m-0"><?php echo $lawan['username']; ?></span>
                    <small class="text-muted fw-normal" style="font-size: 12px;"><i class="bi bi-circle-fill text-success" style="font-size: 8px;"></i> Online</small>
                </div>
            </div>

            <?php if($role_saya == 'pembeli'): ?>
                <button class="btn btn-warning fw-bold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTawar">
                    <i class="bi bi-tags-fill"></i> Tawar Harga
                </button>
            <?php endif; ?>
        </div>
        
        <div class="card-body d-flex flex-column chat-box" id="chat-container">
            <?php if(mysqli_num_rows($q_chat) == 0): ?>
                <div class="text-center text-muted mt-5 bg-white p-2 rounded mx-auto" style="font-size: 12px;">Pesan dan panggilan dilindungi enkripsi end-to-end.<br>Mulai percakapan dengan <?php echo $lawan['username']; ?>.</div>
            <?php endif; ?>

            <?php while($c = mysqli_fetch_assoc($q_chat)): ?>
                <div class="bubble <?php echo ($c['pengirim_id'] == $saya_id) ? 'me' : 'them'; ?>">
                    
                    <?php echo nl2br($c['pesan']); ?>
                    
                    <?php if($c['produk_id'] != NULL): ?>
                        <div class="tawaran-box shadow-sm">
                            <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                                <img src="uploads/<?php echo $c['gambar']; ?>" width="50" height="50" style="object-fit:cover; border-radius:5px;" class="me-3">
                                <div class="text-truncate">
                                    <small class="fw-bold d-block text-truncate"><?php echo $c['nama_produk']; ?></small>
                                    <span class="badge bg-secondary">Tawaran: Rp <?php echo number_format($c['harga_tawaran'],0,',','.'); ?></span>
                                </div>
                            </div>
                            
                            <?php 
                                // Pewarnaan Status
                                $bg_status = 'warning text-dark';
                                if ($c['status_tawaran'] == 'diterima') $bg_status = 'success';
                                if ($c['status_tawaran'] == 'ditolak') $bg_status = 'danger';
                            ?>
                            <div class="text-center mb-2">
                                <span class="badge bg-<?php echo $bg_status; ?> w-100 py-2">Status: <?php echo strtoupper($c['status_tawaran']); ?></span>
                            </div>
                            
                            <?php if($role_saya == 'penjual' && $c['status_tawaran'] == 'pending'): ?>
                                <div class="d-flex gap-2 mt-2">
                                    <a href="proses_tawaran.php?aksi=diterima&id_pesan=<?php echo $c['id']; ?>&lawan_id=<?php echo $lawan_id; ?>" class="btn btn-sm btn-success w-100 fw-bold shadow-sm">Terima</a>
                                    <a href="proses_tawaran.php?aksi=ditolak&id_pesan=<?php echo $c['id']; ?>&lawan_id=<?php echo $lawan_id; ?>" class="btn btn-sm btn-danger w-100 fw-bold shadow-sm">Tolak</a>
                                </div>
                            <?php endif; ?>

                            <?php if($role_saya == 'pembeli' && $c['status_tawaran'] == 'diterima'): ?>
                                <a href="tambah_keranjang_nego.php?produk_id=<?php echo $c['produk_id']; ?>&harga=<?php echo $c['harga_tawaran']; ?>" class="btn btn-sm btn-success w-100 mt-2 fw-bold shadow-sm"><i class="bi bi-cart-plus"></i> Masukkan ke Keranjang</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <span class="time-stamp">
                        <?php echo date('H:i', strtotime($c['tanggal'])); ?>
                        <?php if($c['pengirim_id'] == $saya_id): ?>
                            <i class="bi bi-check-all text-primary ms-1"></i>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="card-footer bg-white p-3">
            <form method="POST" class="d-flex align-items-center">
                <input type="text" name="isi_pesan" class="form-control rounded-pill me-2 px-3" placeholder="Ketik pesan..." autocomplete="off" required>
                <button type="submit" name="kirim_pesan" class="btn rounded-circle text-white shadow-sm" style="background: #118a44; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<?php if($role_saya == 'pembeli'): ?>
<div class="modal fade" id="modalTawar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-tags"></i> Buat Tawaran Harga</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body p-4">
              <div class="mb-4">
                  <label class="form-label fw-bold">Pilih Produk yang Ingin Ditawar</label>
                  <select name="produk_id" class="form-select" required>
                      <option value="">-- Pilih Produk di Toko Ini --</option>
                      <?php while($p = mysqli_fetch_assoc($q_produk_lawan)): ?>
                          <option value="<?php echo $p['id']; ?>"><?php echo $p['nama_produk']; ?> (Harga Normal: Rp <?php echo number_format($p['harga'],0,',','.'); ?>)</option>
                      <?php endwhile; ?>
                  </select>
              </div>
              <div class="mb-2">
                  <label class="form-label fw-bold">Harga Tawaran Anda (Rp)</label>
                  <div class="input-group">
                      <span class="input-group-text bg-light fw-bold">Rp</span>
                      <input type="number" name="harga_nego" class="form-control" placeholder="Contoh: 85000" min="1000" required>
                  </div>
                  <small class="text-muted mt-1 d-block">Penjual berhak menolak atau menerima tawaran Anda.</small>
              </div>
          </div>
          <div class="modal-footer border-0 pb-4 pt-0">
              <button type="button" class="btn btn-light w-100 mb-2" data-bs-dismiss="modal">Batal</button>
              <button type="submit" name="kirim_tawaran" class="btn btn-warning fw-bold w-100 m-0 shadow-sm">Kirim Tawaran Sekarang</button>
          </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script agar chat otomatis di-scroll ke paling bawah saat halaman dibuka
    var objDiv = document.getElementById("chat-container");
    objDiv.scrollTop = objDiv.scrollHeight;
</script>
</body>
</html>