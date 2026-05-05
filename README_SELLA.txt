================================================================================
                    SELLA - MARKETPLACE PLATFORM (PHP NATIVE)
================================================================================

SELLA adalah platform marketplace modern yang dirancang untuk memfasilitasi 
transaksi antara Penjual dan Pembeli dengan antarmuka yang responsif (Mobile & PC).

--------------------------------------------------------------------------------
1. TECH STACK
--------------------------------------------------------------------------------
- Bahasa Pemrograman : PHP Native
- Database          : MySQL / MariaDB
- Framework CSS     : Bootstrap 5.3 & Bootstrap Icons
- Library JS        : jQuery 3.6
- Gaya Visual       : Responsive Design (Full PC & Mobile Mode)
- Tipografi         : Courier New, Courier, monospace

--------------------------------------------------------------------------------
2. FITUR UTAMA
--------------------------------------------------------------------------------

A. SISI PEMBELI (BUYER):
   - Registrasi & Login Akun.
   - Beranda Produk dengan Filter Kategori & Rentang Harga.
   - Detail Produk (Carousel Gambar, Pilihan Varian, & Rekomendasi Toko).
   - Sistem Wishlist (Favorit) & Keranjang Belanja Interaktif.
   - Checkout Terintegrasi (Simulasi Ongkir API, Voucher, & SELLA Coins).
   - Riwayat Pesanan dengan Pelacakan Resi.
   - Fitur Rating & Ulasan Bintang (Star Rating).
   - Dompet Digital (SELLA Pay) dengan fitur Simulasi Top-Up.

B. SISI PENJUAL (MERCHANT):
   - Dashboard Statistik (Total Produk, Pesanan Baru, & Saldo).
   - Manajemen Produk (Multiple Image Upload & Pengaturan Varian).
   - Manajemen Pesanan (Proses Pengemasan & Input Nomor Resi).
   - Kelola Etalase Toko (Hapus & Edit Produk).

C. SISI ADMIN (SISTEM):
   - Username: AdminSELLA
   - Password: SellaAdmin123

--------------------------------------------------------------------------------
3. CARA INSTALLASI (LOCAL SETUP)
--------------------------------------------------------------------------------
1. Ekstrak folder "sella" ke dalam directory "C:/xampp/htdocs/".
2. Jalankan XAMPP (Apache & MySQL).
3. Buka phpMyAdmin, buat database baru bernama "sella_db".
4. Import file "sella_db.sql" yang tersedia di folder database.
5. Pastikan konfigurasi di "koneksi.php" sudah sesuai (host, user, pass, db).
6. Pastikan folder "uploads/" tersedia di direktori utama dan memiliki izin 
   tulis (Write Permission) untuk menyimpan gambar produk.
7. Buka browser dan ketik: http://localhost/sella/login.php

--------------------------------------------------------------------------------
4. STRUKTUR FOLDER PENTING
--------------------------------------------------------------------------------
- /uploads           : Tempat penyimpanan file gambar produk.
- koneksi.php        : Konfigurasi database.
- api_*.php          : File simulasi API (Ongkir, Kota, Provinsi, Voucher).
- marketplace_*.php  : Halaman utama pembeli.
- dashboard_*.php    : Halaman utama penjual/admin.

--------------------------------------------------------------------------------
5. CATATAN PENGEMBANGAN
--------------------------------------------------------------------------------
- Proyek ini dikembangkan dengan pendekatan "Mobile-First" namun tetap 
  mendukung tampilan lebar (Full Desktop) untuk kenyamanan pengguna PC.
- Penggunaan font "Courier New" memberikan sentuhan estetik yang unik dan 
  konsisten di seluruh antarmuka aplikasi.

© 2026 - Dikembangkan oleh Tim SELLA.