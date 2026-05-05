-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Apr 2026 pada 14.23
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sella_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `pengirim_id` int(11) NOT NULL,
  `penerima_id` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `produk_id` int(11) DEFAULT NULL,
  `harga_tawaran` int(11) DEFAULT NULL,
  `status_tawaran` enum('pending','diterima','ditolak') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `pengirim_id`, `penerima_id`, `pesan`, `is_read`, `tanggal`, `produk_id`, `harga_tawaran`, `status_tawaran`) VALUES
(1, 2, 3, 'Bang boleh tawar harga gak?', 1, '2026-04-29 06:22:35', NULL, NULL, NULL),
(2, 2, 3, 'Saya ingin menawar produk ini seharga Rp 85.000', 1, '2026-04-29 06:22:55', 2, 85000, 'pending'),
(3, 3, 2, 'Naikin dikit bang ke 90000', 1, '2026-04-29 06:28:20', NULL, NULL, NULL),
(4, 2, 3, 'Saya ingin menawar produk ini seharga Rp 90.000', 0, '2026-04-29 06:30:41', 2, 90000, 'pending'),
(5, 2, 3, 'udah bang', 0, '2026-04-29 06:30:45', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `pembeli_id` int(11) NOT NULL,
  `total_bayar` int(11) NOT NULL,
  `status_pesanan` enum('belum_bayar','dikemas','dikirim','selesai') DEFAULT 'belum_bayar',
  `metode_pembayaran` varchar(50) DEFAULT 'sella_pay',
  `no_resi` varchar(50) DEFAULT NULL,
  `tanggal_transaksi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `pembeli_id`, `total_bayar`, `status_pesanan`, `metode_pembayaran`, `no_resi`, `tanggal_transaksi`) VALUES
(1, 2, 20000, 'selesai', 'sella_pay', NULL, '2026-04-29 06:27:07'),
(2, 2, 154000, 'dikemas', 'cod', NULL, '2026-04-30 11:50:03'),
(3, 2, 144000, 'dikemas', 'cod', NULL, '2026-04-30 12:02:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `produk_id`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 2, 20000),
(2, 2, 1, 1, 10000),
(3, 2, 2, 1, 100000),
(4, 3, 2, 1, 100000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `penjual_id` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT 'Lainnya',
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL,
  `berat` int(11) DEFAULT 1000,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `harga_flash_sale` int(11) DEFAULT NULL,
  `flash_sale_end` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `penjual_id`, `nama_produk`, `kategori`, `harga`, `stok`, `berat`, `deskripsi`, `gambar`, `harga_flash_sale`, `flash_sale_end`, `created_at`) VALUES
(1, 3, 'Gambar Anime', 'Lainnya', 10000, 47, 5, 'Gambar Anime Blue Archive', '1777443476_Screenshot 2026-04-04 182526.png', NULL, NULL, '2026-04-29 06:17:56'),
(2, 3, 'Akun Neverness To Everness', 'Lainnya', 100000, 0, 0, 'Akun Neverness To Everness polosan', '1777443620_Screenshot 2026-04-27 203253.png', NULL, NULL, '2026-04-29 06:20:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `gambar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `nama_varian` varchar(100) NOT NULL,
  `harga_tambahan` int(11) DEFAULT 0,
  `stok_varian` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `pembeli_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `bintang` int(11) NOT NULL DEFAULT 5,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `komentar` text DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reviews`
--

INSERT INTO `reviews` (`id`, `produk_id`, `pembeli_id`, `order_id`, `bintang`, `rating`, `komentar`, `tanggal`) VALUES
(1, 1, 2, 1, 5, 4, 'Gambarnya bagus, tapi ada goresan dibagain tepi gambar. Overal saya suka dengan gambarnya', '2026-04-29 06:33:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shop_profiles`
--

CREATE TABLE `shop_profiles` (
  `penjual_id` int(11) NOT NULL,
  `nama_toko` varchar(100) NOT NULL,
  `deskripsi_toko` text DEFAULT NULL,
  `logo_toko` varchar(255) DEFAULT NULL,
  `banner_toko` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `shop_profiles`
--

INSERT INTO `shop_profiles` (`penjual_id`, `nama_toko`, `deskripsi_toko`, `logo_toko`, `banner_toko`) VALUES
(3, 'Anderu', '', '1777443683_logo_Screenshot 2026-04-29 115009.png', '1777443683_banner_Screenshot 2026-04-29 115028.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `topup_history`
--

CREATE TABLE `topup_history` (
  `id` int(11) NOT NULL,
  `pembeli_id` int(11) NOT NULL,
  `jumlah_topup` int(11) NOT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `status` enum('pending','sukses','ditolak') DEFAULT 'pending',
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `topup_history`
--

INSERT INTO `topup_history` (`id`, `pembeli_id`, `jumlah_topup`, `bukti_transfer`, `status`, `tanggal`) VALUES
(1, 2, 50000, '1777443831_Screenshot 2026-03-29 213445.png', 'sukses', '2026-04-29 06:23:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('pembeli','penjual','admin') NOT NULL,
  `saldo` int(11) DEFAULT 0,
  `sella_coins` int(11) DEFAULT 0,
  `kurir_aktif` varchar(100) DEFAULT 'jne,pos',
  `provinsi_id` int(11) DEFAULT NULL,
  `kota_id` int(11) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `saldo`, `sella_coins`, `kurir_aktif`, `provinsi_id`, `kota_id`, `alamat_lengkap`, `created_at`) VALUES
(1, 'AdminSELLA', '$2y$10$cB8wp9FGThLDmFJrEzWkheOs1HPIDGsTk66WcVBhQlOWTsTMo4Fdi', 'admin@sella.com', 'admin', 0, 0, 'jne,pos', NULL, NULL, NULL, '2026-04-29 06:07:02'),
(2, 'Andre', '$2y$10$DzsWb2vCjh047x/wjnYm8uQ70OYTZcnMCvOCkl9Co0GB1YeivYgU.', 'andre@gmail.com', 'pembeli', 30000, 2980, 'jne,pos', NULL, NULL, 'Kost faisa', '2026-04-29 06:08:03'),
(3, 'Anderu', '$2y$10$SmISiAMpTz8iTzTm1/3bIOGtB/QfA/9OZqrJu4Qm.NlB0xtdgp1.S', 'anderu@gmail.com', 'penjual', 20000, 0, 'jne,pos', NULL, NULL, NULL, '2026-04-29 06:09:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `penjual_id` int(11) NOT NULL,
  `kode_voucher` varchar(20) NOT NULL,
  `tipe_diskon` enum('nominal','persen') NOT NULL,
  `nilai_diskon` int(11) NOT NULL,
  `min_belanja` int(11) DEFAULT 0,
  `kuota` int(11) DEFAULT 0,
  `tanggal_berakhir` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `pembeli_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `tanggal_ditambahkan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `wishlists`
--

INSERT INTO `wishlists` (`id`, `pembeli_id`, `produk_id`, `tanggal_ditambahkan`) VALUES
(1, 2, 1, '2026-04-30 17:51:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `penjual_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `nama_bank` varchar(50) NOT NULL,
  `nomor_rekening` varchar(50) NOT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `tanggal_request` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengirim_id` (`pengirim_id`),
  ADD KEY `penerima_id` (`penerima_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembeli_id` (`pembeli_id`);

--
-- Indeks untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penjual_id` (`penjual_id`);

--
-- Indeks untuk tabel `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`),
  ADD KEY `pembeli_id` (`pembeli_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `shop_profiles`
--
ALTER TABLE `shop_profiles`
  ADD PRIMARY KEY (`penjual_id`);

--
-- Indeks untuk tabel `topup_history`
--
ALTER TABLE `topup_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembeli_id` (`pembeli_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penjual_id` (`penjual_id`);

--
-- Indeks untuk tabel `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembeli_id` (`pembeli_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penjual_id` (`penjual_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `topup_history`
--
ALTER TABLE `topup_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`pengirim_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`penerima_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`produk_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `products` (`id`);

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`penjual_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ketidakleluasaan untuk tabel `shop_profiles`
--
ALTER TABLE `shop_profiles`
  ADD CONSTRAINT `shop_profiles_ibfk_1` FOREIGN KEY (`penjual_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `topup_history`
--
ALTER TABLE `topup_history`
  ADD CONSTRAINT `topup_history_ibfk_1` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`penjual_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`penjual_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
