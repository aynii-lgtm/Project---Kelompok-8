-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Nov 2026 pada 22.48
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
-- Database: `db_miekiro`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun`
--

CREATE TABLE `akun` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('customer','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `akun`
--

INSERT INTO `akun` (`id`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'kiroadmin@gmail.com', 'kiromin1', 'admin', '2026-11-09 21:45:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `level_pedas_id` int(11) NOT NULL,
  `harga_stauan` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `level_pedas`
--

CREATE TABLE `level_pedas` (
  `id` int(11) NOT NULL,
  `nama_level` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `level_pedas`
--

INSERT INTO `level_pedas` (`id`, `nama_level`) VALUES
(1, 'Level 0'),
(2, 'Level 1'),
(3, 'Level 2'),
(4, 'Level 3'),
(5, 'Level 4'),
(6, 'Level 5');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `kategori` enum('mie','snack','minuman') NOT NULL,
  `harga` int(50) NOT NULL,
  `deskripsi` text NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `ketersediaan` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu`
--

INSERT INTO `menu` (`id`, `nama_menu`, `kategori`, `harga`, `deskripsi`, `gambar`, `ketersediaan`, `created_at`) VALUES
(3, 'Dimsum', 'snack', 8500, 'Dimsum isi ayam yang dilengkapi dengan saus bangkok (3 pcs)', 'https://i0.wp.com/www.cakefever.com/wp-content/uploads/Siomay-mie-gacoan.jpg?w=540&ssl=1', 35, '2026-11-09 21:12:22'),
(4, 'Risoles Udang', 'snack', 8500, 'Risol udang isi keju melt yang dilengkapi dengan saus bangkok (3 pcs)', 'https://i0.wp.com/www.cakefever.com/wp-content/uploads/Udang-keju-gacoan.jpg?w=540&ssl=1', 40, '2026-11-09 21:12:22'),
(5, 'Lumpia Udang', 'snack', 8500, 'Udang cincang dibaluti dengan kulit lumpia tipis yang dilengkapi dengan saus bangkok (3 pcs)', 'https://i0.wp.com/www.cakefever.com/wp-content/uploads/Lumpia-udang-gacoan.jpg?w=540&ssl=1', 30, '2026-11-09 21:16:45'),
(6, 'Bola-bola Rambutan', 'snack', 8500, 'Irisan kulit pangsit isi adonan udang yang dilengkapi dengan saus bangkok (3 pcs)', 'https://i0.wp.com/www.cakefever.com/wp-content/uploads/Udang-rambutan-gacoan.jpg?w=540&ssl=1', 35, '2026-11-09 21:16:45'),
(7, 'Es Teh', 'minuman', 4000, 'Teh manis dingin', 'https://www.bakmigm.com/cfind/source/thumb/images/sku/cover_w480_h480_sku_teh-es.png', 50, '2026-11-09 21:22:40'),
(8, 'Es Jeruk', 'minuman', 5000, 'Jeruk peras dingin', 'https://dcostseafood.id/wp-content/uploads/2021/12/ES-JERUK-murni.jpg', 35, '2026-11-09 21:22:40'),
(9, 'Es Buah', 'minuman', 8500, 'Potongan buah-buahan segar dengan sirup manis dan es serut', 'https://miegacoanbekasi.id/images/MENU/ES-SETAN/ES-GENDERUWO.jpg', 25, '2026-11-09 21:26:04'),
(1, 'Mie Manis', 'mie', 10000, 'Mie manis dengan topping ayam tabur, bawang goreng, dan 2 pangsit goreng', 'https://down-id.img.susercontent.com/file/id-11134207-7r98z-lqn7ckd95apra1', 50, '2026-11-09 21:32:39'),
(2, 'Mie Asin', 'mie', 10000, 'Mie asin dengan topping ayam tabur, bawang goreng, dan 2 pangsit goreng', 'https://miedzohir.com/wp-content/uploads/2024/04/Mie_Setan.width-800.format-webp-1.webp', 50, '2026-11-09 21:32:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal_pesanan` datetime NOT NULL,
  `status_pesanan` enum('pending','proses','selesai','dibatalkan') NOT NULL,
  `total_harga` int(11) NOT NULL,
  `metode_pembayaran` enum('cash','qris') NOT NULL,
  `catatan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `level_pedas`
--
ALTER TABLE `level_pedas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
