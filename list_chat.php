<?php
session_start();
include 'koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) { 
    header("Location: login.php"); 
    exit; 
}

$saya_id = $_SESSION['id'];
$role_saya = $_SESSION['role'];

// Tentukan link tombol kembali berdasarkan role
$link_kembali = ($role_saya == 'penjual') ? 'dashboard_penjual.php' : 'marketplace_pembeli.php';

// Ambil daftar ID orang yang pernah chat dengan saya (sebagai pengirim maupun penerima)
$query_list = "SELECT DISTINCT 
    CASE WHEN pengirim_id = '$saya_id' THEN penerima_id ELSE pengirim_id END AS lawan_id 
    FROM messages 
    WHERE pengirim_id = '$saya_id' OR penerima_id = '$saya_id'";
$q_list = mysqli_query($conn, $query_list);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kotak Masuk Pesan - SELLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .hover-bg:hover { background-color: #f8f9fa; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <a href="<?php echo $link_kembali; ?>" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white pt-4 pb-3 border-bottom-0">
                    <h4 class="fw-bold m-0"><i class="bi bi-chat-dots-fill text-success me-2"></i> Kotak Masuk Pesan</h4>
                </div>
                
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-bottom">
                        
                        <?php if(mysqli_num_rows($q_list) > 0): ?>
                            <?php while($l = mysqli_fetch_assoc($q_list)): 
                                $lawan_id = $l['lawan_id'];
                                
                                // Ambil info username lawan bicara
                                $info_lawan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username, role FROM users WHERE id = '$lawan_id'"));
                                
                                // Hitung berapa pesan yang belum dibaca dari lawan ini
                                $q_unread = mysqli_query($conn, "SELECT COUNT(id) as total_unread FROM messages WHERE pengirim_id = '$lawan_id' AND penerima_id = '$saya_id' AND is_read = 0");
                                $unread = mysqli_fetch_assoc($q_unread)['total_unread'];
                                
                                // Ambil pesan terakhir untuk preview singkat (Opsional agar terlihat lebih keren)
                                $q_last_msg = mysqli_query($conn, "SELECT pesan, tanggal FROM messages WHERE (pengirim_id = '$saya_id' AND penerima_id = '$lawan_id') OR (pengirim_id = '$lawan_id' AND penerima_id = '$saya_id') ORDER BY tanggal DESC LIMIT 1");
                                $last_msg = mysqli_fetch_assoc($q_last_msg);
                            ?>
                            
                            <a href="chat.php?lawan_id=<?php echo $lawan_id; ?>" class="list-group-item list-group-item-action p-3 border-bottom hover-bg">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center overflow-hidden">
                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                            <?php echo strtoupper(substr($info_lawan['username'], 0, 1)); ?>
                                        </div>
                                        
                                        <div class="text-truncate">
                                            <h6 class="mb-1 fw-bold text-dark">
                                                <?php echo $info_lawan['username']; ?> 
                                                <?php if($info_lawan['role'] == 'penjual'): ?>
                                                    <i class="bi bi-shop text-success ms-1" style="font-size: 12px;" title="Penjual"></i>
                                                <?php endif; ?>
                                            </h6>
                                            <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;">
                                                <?php echo htmlspecialchars($last_msg['pesan']); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="text-end d-flex flex-column align-items-end">
                                        <small class="text-muted mb-1" style="font-size: 11px;">
                                            <?php echo date('d M', strtotime($last_msg['tanggal'])); ?>
                                        </small>
                                        <?php if($unread > 0): ?>
                                            <span class="badge bg-danger rounded-pill px-2 py-1"><?php echo $unread; ?> Baru</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                            
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-chat-square-text text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">Belum ada percakapan pesan.</p>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

</body>
</html>