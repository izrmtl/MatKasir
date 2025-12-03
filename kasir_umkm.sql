-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 03 Des 2025 pada 05.27
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
-- Database: `kasir_umkm`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `id_detail` int(11) NOT NULL,
  `id_penjualan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_penjualan`
--

INSERT INTO `detail_penjualan` (`id_detail`, `id_penjualan`, `id_produk`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(49, 16, 23, 1, 11000000.00, 11000000.00),
(50, 16, 25, 1, 14000000.00, 14000000.00),
(51, 16, 15, 1, 14000000.00, 14000000.00),
(52, 16, 13, 1, 11000000.00, 11000000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `no_telepon` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama_pelanggan`, `no_telepon`, `alamat`, `created_at`) VALUES
(1, 'Umum', '-', '-', '2025-12-01 04:54:26'),
(2, 'Budi Santoso', '081234567890', 'Jl. Merdeka No. 10, Jakarta', '2025-12-01 04:54:26'),
(3, 'Abdi Umiyah', '082345678901', 'Jl. Sudirman No. 25, Bandung', '2025-12-01 04:54:26'),
(5, 'Lestari Pusmana', '084567890123', 'Jl. Diponegoro No. 30, Yogyakarta', '2025-12-01 04:54:26'),
(6, 'mamat adiwijaya', '0834567897218', 'Jl. Gajah Mada No. 15, Surabaya', '2025-12-01 12:42:42'),
(11, 'AUL PUTRI', '09812817218', 'PONOROGO', '2025-12-03 04:08:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `tanggal_penjualan` datetime DEFAULT current_timestamp(),
  `total_harga` decimal(10,2) NOT NULL,
  `bayar` decimal(10,2) NOT NULL,
  `kembalian` decimal(10,2) NOT NULL,
  `status` enum('selesai','pending','batal') DEFAULT 'selesai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `id_pelanggan`, `tanggal_penjualan`, `total_harga`, `bayar`, `kembalian`, `status`) VALUES
(16, 1, '2025-12-03 11:04:20', 50000000.00, 99999999.99, 50000000.00, 'selesai');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `harga`, `stok`, `kategori`, `deskripsi`, `created_at`, `updated_at`) VALUES
(5, 'LAPTOP ASUS ZENBOOK', 12000000.00, 84, 'KOMPUTER', 'Laptop Spek Ultra tuf', '2025-12-01 04:54:26', '2025-12-03 04:11:42'),
(12, 'ASUS Vivobook Classic', 12000000.00, 100, 'KOMPUTER', 'KOMPUTER SPEK DISKON', '2025-12-02 13:03:41', '2025-12-03 04:13:08'),
(13, 'ASUS Vivobook Go', 11000000.00, 89, 'KOMPUTER', 'KOMPUTER ULTRA GAME', '2025-12-02 13:03:41', '2025-12-03 04:04:20'),
(14, 'ASUS Vivobook E Series', 13000000.00, 70, 'KOMPUTER', 'KOMPUTER DYNAMIC GAME', '2025-12-02 13:03:41', '2025-12-02 13:03:41'),
(15, 'ASUS TUF FX/F Series', 14000000.00, 89, 'KOMPUTER', 'KOMPUTER SPEK ULTRA', '2025-12-02 13:03:41', '2025-12-03 04:13:19'),
(16, 'ASUS ROG Strix', 15000000.00, 90, 'KOMPUTER', 'KOMPUTER ULTRA TAF', '2025-12-02 13:03:41', '2025-12-03 04:13:08'),
(17, 'ASUS ZenAiO', 16000000.00, 100, 'KOMPUTER', 'KOMPUTER SUPORT', '2025-12-02 13:03:41', '2025-12-02 13:04:30'),
(18, 'ASUS Vivo AiO', 17000000.00, 90, 'KOMPUTER', 'KOMPUTER SUPORT GAMING', '2025-12-02 13:03:41', '2025-12-03 04:13:15'),
(19, 'ASUS Transformer Book', 18000000.00, 90, 'KOMPUTER', 'KOMPUTER ULTRA GAME SPORT', '2025-12-02 13:03:41', '2025-12-03 04:13:08'),
(20, 'ASUS Eee PC', 18000000.00, 4, 'KOMPUTER', 'KOMPUTER GAME', '2025-12-02 13:03:41', '2025-12-03 04:13:19'),
(21, 'ASUS EeeBook', 19000000.00, 6, 'KOMPUTER', 'KOMPUTER SPEK GAMING', '2025-12-02 13:03:41', '2025-12-03 04:13:30'),
(22, 'ASUS ROG (Republic of Gamers)', 12000000.00, 80, 'KOMPUTER', 'KOMPUTER SPEK SUPER GAMING', '2025-12-02 13:03:41', '2025-12-03 04:13:41'),
(23, 'ASUS TUF Gaming', 11000000.00, 99, 'KOMPUTER', 'KOMPUTER SPEK GAMING', '2025-12-02 13:03:41', '2025-12-03 04:13:12'),
(24, 'ASUS ProArt', 13000000.00, 80, 'KOMPUTER', 'KOMPUTER SPEK GAMING', '2025-12-02 13:03:41', '2025-12-03 04:13:41'),
(25, 'ASUS Vivobook Go', 14000000.00, 69, 'KOMPUTER', 'KOMPUTER SPEK GAMING', '2025-12-02 13:03:41', '2025-12-03 04:04:20'),
(26, 'ASUS Vivobook Classic', 15000000.00, 90, 'KOMPUTER', 'KOMPUTER SPEK GAMING', '2025-12-02 13:03:41', '2025-12-03 04:13:08'),
(27, 'ASUS Vivobook Flip', 16000000.00, 60, 'KOMPUTER', 'KOMPUTER SPEK GAMING', '2025-12-02 13:03:41', '2025-12-02 13:03:41'),
(28, 'ASUS Vivobook Pro', 17000000.00, 70, 'KOMPUTER', 'KOMPUTER SPEK GAMING', '2025-12-02 13:03:41', '2025-12-02 13:03:41'),
(29, 'ASUS VivoBook S', 18000000.00, 80, 'KOMPUTER', 'KOMPUTER SPEK GAMING', '2025-12-02 13:03:41', '2025-12-02 13:03:41'),
(30, 'ASUS Vivobook', 19000000.00, 90, 'KOMPUTER', 'KOMPUTER SPEK GAMING', '2025-12-02 13:03:41', '2025-12-03 04:13:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `level` enum('admin','kasir') NOT NULL DEFAULT 'kasir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `level`) VALUES
(1, 'admin', '$2y$10$pwsdVuLsU9wT8sjjmMXTm..kCgez0QxfAwBgU3eo.JQPzTEwm6gVy', 'Administrator', 'admin');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_laporan_penjualan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_laporan_penjualan` (
`id_penjualan` int(11)
,`tanggal_penjualan` datetime
,`nama_pelanggan` varchar(100)
,`total_harga` decimal(10,2)
,`bayar` decimal(10,2)
,`kembalian` decimal(10,2)
,`status` enum('selesai','pending','batal')
);

-- --------------------------------------------------------

--
-- Struktur untuk view `view_laporan_penjualan`
--
DROP TABLE IF EXISTS `view_laporan_penjualan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_laporan_penjualan`  AS SELECT `p`.`id_penjualan` AS `id_penjualan`, `p`.`tanggal_penjualan` AS `tanggal_penjualan`, `pl`.`nama_pelanggan` AS `nama_pelanggan`, `p`.`total_harga` AS `total_harga`, `p`.`bayar` AS `bayar`, `p`.`kembalian` AS `kembalian`, `p`.`status` AS `status` FROM (`penjualan` `p` left join `pelanggan` `pl` on(`p`.`id_pelanggan` = `pl`.`id_pelanggan`)) ORDER BY `p`.`tanggal_penjualan` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_penjualan` (`id_penjualan`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id_penjualan`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
