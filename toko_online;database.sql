-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2025 at 02:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_online`
--

-- --------------------------------------------------------

--
-- Table structure for table `dkeranjang`
--

CREATE TABLE `dkeranjang` (
  `idkeranjang` int(11) NOT NULL,
  `idproduk` varchar(10) DEFAULT NULL,
  `id` varchar(10) NOT NULL,
  `idtransaksi` varchar(10) NOT NULL,
  `harga` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `keterangan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dkeranjang`
--

INSERT INTO `dkeranjang` (`idkeranjang`, `idproduk`, `id`, `idtransaksi`, `harga`, `quantity`, `keterangan`) VALUES
(5, 'p202504002', 'U202505002', 'T202505001', 1200, 1, 'Lunas'),
(6, 'p202504003', 'U202505002', 'T202505001', 2800, 1, 'Lunas'),
(7, 'p202504004', '0', 'T202505002', 3750, 2, 'Lunas'),
(8, 'p202504003', '0', 'T202505002', 2800, 1, 'Lunas'),
(9, 'p202504009', 'U202505003', 'T202505002', 3300, 1, 'Lunas'),
(10, 'p202504003', 'U202505003', 'T202505002', 2800, 1, 'Lunas'),
(11, 'p202504013', '0', 'T202505002', 6000, 1, 'Lunas'),
(12, 'p202504012', '0', 'T202505002', 2400, 1, 'Lunas'),
(19, 'p202504013', '0', 'T202505003', 6000, 2, 'Lunas'),
(20, 'p202504012', '0', 'T202505003', 2400, 2, 'Lunas'),
(21, 'p202504014', '0', 'T202505003', 12000, 1, 'Lunas'),
(22, 'p202504013', '0', '', 6000, 2, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `hkeranjang`
--

CREATE TABLE `hkeranjang` (
  `idtransaksi` varchar(10) NOT NULL,
  `id` varchar(10) DEFAULT NULL,
  `tgltransaksi` date NOT NULL,
  `nama` varchar(50) NOT NULL,
  `alamat` varchar(50) NOT NULL,
  `kodepos` varchar(50) NOT NULL,
  `nohp` varchar(14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hkeranjang`
--

INSERT INTO `hkeranjang` (`idtransaksi`, `id`, `tgltransaksi`, `nama`, `alamat`, `kodepos`, `nohp`) VALUES
('T202505001', 'T202505001', '2025-05-05', 'hiasdaiud', 'aahashjd', '722', '0896765365123'),
('T202505002', 'T202505002', '2025-05-16', 'Rowan William Khang', '3123123', '22212', '0897627362123'),
('T202505003', 'T202505003', '2025-05-18', 'Rowan William Khang', '3123123', '22212', '0897627362123'),
('T202505004', 'T202505004', '2025-05-18', 'Rowan William Khang', '3123123', '22212', '0897627362123');

-- --------------------------------------------------------

--
-- Table structure for table `identitas_usaha`
--

CREATE TABLE `identitas_usaha` (
  `id` int(11) NOT NULL,
  `nama_usaha` varchar(100) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `no_telepon` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `website` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `idproduk` varchar(10) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga` int(11) NOT NULL,
  `gambar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`idproduk`, `nama_produk`, `harga`, `gambar`) VALUES
('p202504001', 'Small Beam tote', 3000, 'bag9.png'),
('p202504002', 'Ophidia Pochette', 1200, 'bag2.png'),
('p202504003', 'GG Marmont Woven Medium Shoulder Bag', 2800, 'bag3.png'),
('p202504004', 'Gucci Jackie 1961 Medium Bag', 3750, 'bag4.png'),
('p202504005', 'Ophidia Small Boston Bag', 2500, 'bag1.png'),
('p202504006', 'Small Featherlight Puzzle bag in nappa lambskin', 4340, 'bag5.png'),
('p202504007', 'LE 5 À 7 BEA IN SUEDE', 3500, 'bag7.png'),
('p202504008', 'LE 5 À 7 SUPPLE SMALL IN GRAINED LEATHER', 2400, 'bag6.png'),
('p202504009', 'Teen CELINE JOSEPHINE bag in shiny Calfskin', 3300, 'bag8.png'),
('p202504011', 'Garden Party 49 voyage bag', 7100, 'bag10.png'),
('p202504012', 'Balusoie bag', 2400, 'bag11.png'),
('p202504013', 'Herbag Zip cabine bag', 6000, 'bag12.png'),
('p202504014', 'Neo Medor clutch', 12000, 'bag13.png'),
('p202504016', 'Vintage Alhambra bracelet, 5 motifs, 18K yellow gold, onyx', 4960, 'jewelry1.jpeg'),
('p202504017', 'Alhambra watch, small model\r\n18K yellow gold, Onyx', 9000, 'jewelry2.jpeg'),
('p202504018', 'Seafoam Diamond Pendant With Blue Sapphire', 1613, 'jewelry3.jpeg'),
('p202504019', 'Tiffany Forge, Medium Link Bracelet,\r\nin Blackened Sterling Silver', 1710, 'jewelry4.jpeg'),
('p202504020', 'Vintage Alhambra long necklace, 20 motifs,\r\n18K rose gold, Carnelian', 2370, 'jewelry5.jpeg'),
('p202504022', 'Check Cashmere Scarf', 610, 'scarves1.jpg'),
('p202504023', 'Anagram scarf in alpaca and wool blend', 600, 'scarves2.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` varchar(10) NOT NULL,
  `email_user` varchar(50) NOT NULL,
  `nama_user` varchar(50) NOT NULL,
  `password_user` varchar(255) NOT NULL,
  `foto_user` varchar(255) DEFAULT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email_user`, `nama_user`, `password_user`, `foto_user`, `tanggal_daftar`) VALUES
('U202504001', 'richie@gmail.com', 'richie', '$2y$10$vylKQfoBBbhhNZ41R.aFoewzTMCHGQiwfCSGpH0rI1NjwcHjWSwLq', 'Uploads/user.jpg', '2025-04-28 09:25:34'),
('U202505001', 'ssss@gmail.com', 'ssss', '$2y$10$Cojwp1Z44PkFZ0GluuCPqOIGMzKcqIMlX6pGNTANXa6R4KmYXg2lG', 'Uploads/user.jpg', '2025-05-05 08:35:40'),
('U202505002', '21323@gmail.com', 'KING', '$2y$10$mwEoQXz/83GvZzWRXigsUOQMFzI04/lhbWUoBP6Bp.nyX6t1cBQWm', 'Uploads/user.jpg', '2025-05-05 08:36:12'),
('U202505003', 'rose123@gmail.com', 'Rowannn', '$2y$10$7.0LXCnJNSDUcd5Rsfqzcuz1Dxg6Zad7E19vq9E4h0jFcbytwxjz2', 'Uploads/U202505003.png', '2025-05-14 05:36:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dkeranjang`
--
ALTER TABLE `dkeranjang`
  ADD PRIMARY KEY (`idkeranjang`),
  ADD UNIQUE KEY `idproduk` (`idproduk`,`id`,`idtransaksi`);

--
-- Indexes for table `hkeranjang`
--
ALTER TABLE `hkeranjang`
  ADD PRIMARY KEY (`idtransaksi`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `identitas_usaha`
--
ALTER TABLE `identitas_usaha`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`idproduk`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_user` (`email_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dkeranjang`
--
ALTER TABLE `dkeranjang`
  MODIFY `idkeranjang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `identitas_usaha`
--
ALTER TABLE `identitas_usaha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
