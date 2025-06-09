-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Jun 2025 pada 14.14
-- Versi server: 10.4.24-MariaDB
-- Versi PHP: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kai`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `import_data`
--

CREATE TABLE `import_data` (
  `id` int(11) NOT NULL,
  `laporan_laba_rugi_komprehensif` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `investasi`
--

CREATE TABLE `investasi` (
  `id` int(11) NOT NULL,
  `no` int(11) NOT NULL,
  `uraian` varchar(255) DEFAULT NULL,
  `wbs` varchar(50) DEFAULT NULL,
  `lokasi_pengadaan` varchar(255) DEFAULT NULL,
  `volume_satuan` varchar(50) DEFAULT NULL,
  `harga_satuan` decimal(20,2) DEFAULT NULL,
  `jumlah_dana` decimal(20,2) DEFAULT NULL,
  `budget_tahun_2024` decimal(20,2) DEFAULT NULL,
  `tambahan_dana` decimal(20,2) DEFAULT NULL,
  `total_tahun_2024` decimal(20,2) DEFAULT NULL,
  `commitment` decimal(20,2) DEFAULT NULL,
  `actual` decimal(20,2) DEFAULT NULL,
  `consumed_budget` decimal(20,2) DEFAULT NULL,
  `available_budget` decimal(20,2) DEFAULT NULL,
  `progres_saat_ini` text DEFAULT NULL,
  `tanggal_kontrak` date DEFAULT NULL,
  `no_kontrak` varchar(100) DEFAULT NULL,
  `nilai_kontrak` decimal(20,2) DEFAULT NULL,
  `ket` text DEFAULT NULL,
  `input_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `investasi`
--

INSERT INTO `investasi` (`id`, `no`, `uraian`, `wbs`, `lokasi_pengadaan`, `volume_satuan`, `harga_satuan`, `jumlah_dana`, `budget_tahun_2024`, `tambahan_dana`, `total_tahun_2024`, `commitment`, `actual`, `consumed_budget`, `available_budget`, `progres_saat_ini`, `tanggal_kontrak`, `no_kontrak`, `nilai_kontrak`, `ket`, `input_date`) VALUES
(1, 1, 'Beautifikasi Stasiun Yogyakarta Tahap 2', 'IBE.B0602501-02-02', 'Daop 6 Yk', '1', '38357439859.00', '38357439859.00', '38357439859.00', '0.00', '38357439859.00', '0.00', '0.00', '0.00', '38357439859.00', '0', '0000-00-00', '', '0.00', 'belum ada keterangannya', '0000-00-00'),
(2, 2, 'Pembangunan Griya Karya Mustika DAOP 6 Yogyakarta', 'IBE.B0602501-03-07', 'Daop 6 Yk', '1', '7446760600.00', '7446760600.00', '7446760600.00', NULL, '7446760600.00', '7446760600.00', NULL, NULL, '7446760600.00', 'SP3', NULL, NULL, NULL, 'SP3', '2025-06-01'),
(3, 3, 'Revitalisasi Stasiun Solo Balapan Tahap 1', 'IBE.B0602501-02-01', 'Daop 6 Yk', '1', '12661722500.00', '12661722500.00', '12661722500.00', NULL, '12661722500.00', NULL, NULL, NULL, '12661722500.00', 'Menunggu kelengkapan dokumen dri kantor pusat', NULL, NULL, NULL, 'Menunggu kelengkapan dokumen dri kantor pusat', '2025-06-01'),
(4, 4, 'Pembangunan Gedung Kantor Unit Fasilitas', 'IBE.B0602501-03-01', 'Daop 6 Yk', '1', '1653000000.00', '1653000000.00', '1653000000.00', NULL, '1653000000.00', '1653000000.00', NULL, NULL, '1653000000.00', 'Proses HPS', NULL, NULL, NULL, 'Permohonan Release PR', '2025-06-01'),
(5, 5, 'Pembangunan Gedung Kantor Baru UPT Resor JR 6.5 Brambanan', 'IBE.B0602501-03-02', 'Daop 6 Yk', '1', '1271175100.00', '1271175100.00', '1271175100.00', '878972325.00', '1271175100.00', '376702429.00', '1255674754.00', '15500346.00', '15500346.00', 'Proses pengadaan\nProgres Rencana Komulatif : 49.606%\nProgres Realisasi Komulatif : 55.46%', '2024-12-20', 'KL.702/XII/16/DO.6-2024', '12516881127.00', NULL, '2025-06-01'),
(6, 6, 'Pembangunan Gedung Kantor Baru UPT Resor JR 6.10 Sumberlawang', 'IBE.B0602501-03-03', 'Daop 6 Yk', '1', '1345481300.00', '1345481300.00', '1345481300.00', '930986651.00', '1345481300.00', '398994282.00', '1329980933.00', '0.00', '15500367.00', 'Proses pengadaan\nProgres Rencana Komulatif : 49.606%\nProgres Realisasi Komulatif : 55.46%', '0000-00-00', 'KL.702/XII/16/DO.6-2024', '1325987320.00', NULL, '2025-06-01'),
(7, 7, 'Pembangunan Gedung Kantor Baru UPT Resor JR 6.6 Klaten', 'IBE.B0602501-03-04', 'Daop 6 Yk', '1', '1468975600.00', '1468975600.00', '1468975600.00', '1026059439.00', '1468975600.00', '440125474.00', '1467084913.00', '1890687.00', '1890687.00', 'Proses pengadaan\nProgres Rencana Komulatif : 49.606%\nProgres Realisasi Komulatif : 55.46%', '2024-12-20', 'KL.702/XII/16/DO.6-2024', '1475072153.00', NULL, '2025-06-01'),
(8, 8, 'Pembangunan Gedung Kantor UPT Resor Sintel 6.5 Klaten untuk Mendukung Pemeliharaan Sinyal dan Telekomunikasi di Daop 6 Yogyakarta', 'IBE.B0602501-03-05', 'Daop 6 Yk', '1', '777495835.00', '777495835.00', '777495835.00', '539640825.00', '777495835.00', '231274639.00', '770915464.00', '6580371.00', '6580371.00', 'Proses pengadaan\nProgres Rencana Komulatif : 25.82%\nProgres Realisasi Komulatif : 48.663%', '2025-01-09', 'KL.702/I/10/DO.6-2025', '770915465.00', NULL, '2025-06-01'),
(9, 9, 'Pembangunan Gedung Kantor UPT Resor LAA', 'IBE.B0602501-03-06', 'Daop 6 Yk', '1', '777495835.00', '777495835.00', '777495835.00', '539640826.00', '777495835.00', '231274639.00', '770915465.00', '6580370.00', '6580370.00', 'Proses pengadaan\nProgres Rencana Komulatif : 25.82%\nProgres Realisasi Komulatif : 48.663%', '2025-01-09', 'KL.702/I/10/DO.6-2025', '770915464.00', NULL, '2025-06-01'),
(10, 10, 'Pekerjaan Renovasi Interior Ruang Bimaloka Kantor Kadaop Deputy, dan Ruang Rapat Koordinasi Daop 6 Yogyakarta', 'IBE.B0602501-03-08', 'Daop 6 Yk', '1', '1166209300.00', '1166209300.00', '1166209300.00', NULL, '1166209300.00', NULL, '1166209300.00', NULL, NULL, 'Permohonan Release PR', NULL, NULL, NULL, NULL, '2025-06-01'),
(11, 11, 'Pengadaan 1 Set Electric Lifting Jack 4 x 15 Ton untuk Depo Kereta Yogyakarta', 'IBE.B0602501-04-01', 'Daop 6 Yk', '11', '1625000000.00', '1625000000.00', '1625000000.00', NULL, '1625000000.00', '1623000000.00', '2000000.00', NULL, NULL, 'Proses pengadaan\nProgres Rencana Komulatif : 26,1%\nProgres Realisasi Komulatif : 33,7%', '2025-03-12', 'KL.702/III/8/DO.6-2025', '1623000000.00', NULL, '2025-06-01'),
(12, 12, 'Pengadaan 1 Set Electric Lifting Jack 4 x 15 Ton untuk Depo Kereta Solobalapan', 'IBE.B0602501-04-02', 'Daop 6 Yk', '1', '1625000000.00', '1625000000.00', '1625000000.00', NULL, '1625000000.00', '1623000000.00', NULL, NULL, NULL, 'Proses pengadaan\nProgres Rencana Komulatif : 26,1%\nProgres Realisasi Komulatif : 33,7%', '2025-03-12', 'KL.702/III/10/DO.6-2025', '1623000000.00', NULL, '2025-06-01'),
(13, 13, 'Pengadaan 1 Set Rerailing Equipment untuk Depo Lokomotif Solobalapan', 'IBE.B0602501-04-03', 'Daop 6 Yk', '1', '2550000000.00', '2550000000.00', '2550000000.00', NULL, '2550000000.00', '2538000000.00', '12000000.00', NULL, NULL, 'Proses pengadaan\nProgres Rencana Komulatif : 30,3%\nProgres Realisasi Komulatif : 42,6%', '2025-03-12', 'KL.702/III/9/DO.6-2025', '2538000000.00', NULL, '2025-06-01'),
(14, 14, 'Sertipikasi Tanah Daop 6 Yogyakarta', 'IBE.B0602501-05-01', 'Daop 6 Yk', '1', '9460989780.00', '9460989780.00', '9460989780.00', '1000760111.00', '439533578.00', '1440293689.00', '802096609.00', NULL, NULL, 'Desa Banyusari, Desa Kartoharjo, Desa Sidogede, Desa kalipucang Kec Grabag Kab Magelang - Sudah SPK dengan Notaris per 11/03/2025 nomor kontrak KL.702/III/6/DO.6-2025\n- Pendaftaran sertifikat di BPN tgl 17 Maret 2024,\n- Terbit peta bidang tanggal 21 Maret 2025\n- Pengurusan PKKPR dan Pertek', NULL, NULL, NULL, 'Koordinasi dengan Kanwil untuk percepatan', '2025-06-01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id` int(11) NOT NULL,
  `kategori` enum('pendapatan','beban','laba rugi usaha','pendapatan beban lain lain','laba rugi sebelum pajak penghasilan','pajak penghasilan','laba rugi bersih tahun berjalan','kepentingan non pengendali','laba yang dapat diatribusikan kepada pemilik entitas induk') NOT NULL DEFAULT 'pendapatan',
  `Uraian` varchar(255) NOT NULL,
  `REALISASI_TAHUN_LALU` decimal(20,2) DEFAULT 0.00,
  `ANGGARAN_TAHUN_INI` decimal(20,2) DEFAULT 0.00,
  `REALISASI_TAHUN_INI` decimal(20,2) DEFAULT 0.00,
  `ANGGARAN_TAHUN_2025` decimal(20,2) DEFAULT 0.00,
  `ACH_1` decimal(6,2) DEFAULT 0.00,
  `GRO` decimal(6,2) DEFAULT 0.00,
  `ACH_2` decimal(6,2) DEFAULT 0.00,
  `ANALISIS_VERTICAL` decimal(6,2) DEFAULT 0.00,
  `input_date` date DEFAULT curdate(),
  `parent_id` int(11) DEFAULT NULL,
  `nomor` varchar(50) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `laporan`
--

INSERT INTO `laporan` (`id`, `kategori`, `Uraian`, `REALISASI_TAHUN_LALU`, `ANGGARAN_TAHUN_INI`, `REALISASI_TAHUN_INI`, `ANGGARAN_TAHUN_2025`, `ACH_1`, `GRO`, `ACH_2`, `ANALISIS_VERTICAL`, `input_date`, `parent_id`, `nomor`, `sort_order`) VALUES
(107, 'pendapatan', 'Angkutan KA Penumpang', '507778782000.00', '584654885000.00', '572482886274.00', '2031365759.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '1', 0),
(108, 'pendapatan', 'Angkutan KA Barang', '18551268263.00', '20393654985.00', '22778918810.00', '66252182.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '2', 0),
(109, 'pendapatan', 'Pendapatan Pendukung Angkutan KA', '10872758521.00', '11611416486.00', '10508418000.00', '34889116.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '3', 0),
(110, 'pendapatan', 'Non Angkutan KA', '23305274726.00', '33131653075.00', '39483835312.00', '117060039.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '4', 0),
(111, 'pendapatan', 'Kompensasi Pemerintah (PSO-IMO-KA Perintis)', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '5', 0),
(115, 'beban', 'Pegawai', '124823650200.00', '138359996256.00', '134659640597.00', '410748043.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '1', 0),
(116, 'beban', 'BBM dan LAA', '47437445191.00', '57213192622.00', '57359824481.00', '172631108.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '2', 0),
(117, 'beban', 'Perawatan (Sarana dan Prasarana)', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '3', 0),
(121, 'beban', 'Penggunaan Prasarana (TAC)', '29585575420.00', '22739672480.00', '22739672480.00', '69475034.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '4', 0),
(122, 'beban', 'Penyusutan dan Amortisasi', '31106537292.00', '33338009362.00', '40611572808.00', '100014028.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '5', 0),
(123, 'beban', 'Umum dan Administrasi', '3452810365.00', '6903723926.00', '3993687404.00', '22700729.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '6', 0),
(124, 'beban', 'Operasi Lainnya', '27485008982.00', '29628145870.00', '29730226825.00', '124319489.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '7', 0),
(126, 'laba rugi usaha', 'LABA (RUGI) USAHA (III = I - II)', '314067939583.00', '364504734662.00', '359584032166.00', '1286958297.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '1', 0),
(127, 'pendapatan beban lain lain', 'PENDAPATAN (BEBAN) LAIN - LAIN', '89358401.00', '0.00', '350236416.00', '0.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '1', 0),
(128, 'laba rugi sebelum pajak penghasilan', 'LABA (RUGI) SEBELUM PAJAK PENGHASILAN (V = III + IV)', '314157297984.00', '364504734662.00', '359934268582.00', '1286958297.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '1', 0),
(129, 'pajak penghasilan', 'PAJAK PENGHASILAN', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '1', 0),
(130, 'laba rugi bersih tahun berjalan', 'LABA (RUGI) BERSIH TAHUN BERJALAN (VII = V + VI)', '314157297984.00', '364504734662.00', '359934268582.00', '1286958297.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '1', 0),
(131, 'kepentingan non pengendali', 'KEPENTINGAN NON PENGENDALI', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '1', 0),
(132, 'laba yang dapat diatribusikan kepada pemilik entitas induk', 'LABA YG DPT DIATRIBUSIKAN KPD PEMILIK ENTITAS INDUK (IX = VII + VIII)', '314157297984.00', '364504734662.00', '359934268582.00', '1286958297.00', '0.00', '0.00', '0.00', '0.00', '2025-06-05', NULL, '1', 0),
(137, 'pendapatan', 'Kontribusi Pemerintah sebagai Bentuk Kewajiban Pelayanan Publik (PSO)', '17073812904.00', '12590563303.00', '13759668152.00', '37579946.00', '0.00', '0.00', '0.00', '0.00', '2025-06-06', 111, '5A', 0),
(145, 'pendapatan', 'Kontribusi Pemerintah sebagai Bentuk Subsidi Angkutan Perintis', '1085432282.00', '2360241490.00', '4351359919.00', '0.00', '0.00', '0.00', '0.00', '0.00', '2025-06-06', 111, '5B', 0),
(146, 'pendapatan', 'Kontribusi Negara untuk Penyediaan Prasarana (IMO)', '33909982332.00', '32691070335.00', '19336503380.00', '98073211.00', '0.00', '0.00', '0.00', '0.00', '2025-06-06', 111, '5C', 0),
(159, 'beban', 'Sarana Perkeretaapian', '13465716249.00', '126917133085879.00', '12925379012.00', '384563207205882.00', '0.00', '0.00', '0.00', '0.00', '2025-06-07', 117, '3A', 0),
(160, 'beban', 'Bangunan (Stasiun & Bangunan Lainnya)', '623232649.00', '7947062883.00', '1246211107.00', '20884721.00', '0.00', '0.00', '0.00', '0.00', '2025-06-07', 117, '3B', 0),
(161, 'beban', 'Prasarana Perkeretaapian', '20529395097.00', '241072333062166.00', '19851342967.00', '139032481416049.00', '0.00', '0.00', '0.00', '0.00', '2025-06-07', 117, '3C', 0);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `laporan_preview`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `laporan_preview` (
`kategori` enum('pendapatan','beban','laba rugi usaha','pendapatan beban lain lain','laba rugi sebelum pajak penghasilan','pajak penghasilan','laba rugi bersih tahun berjalan','kepentingan non pengendali','laba yang dapat diatribusikan kepada pemilik entitas induk')
,`total_realisasi_tahun_lalu` decimal(42,2)
,`total_anggaran_tahun_ini` decimal(42,2)
,`total_realisasi_tahun_ini` decimal(42,2)
,`total_anggaran_tahun_2025` decimal(42,2)
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(2, '', 'user@example.com', '$2y$10$JQckvjxcxqDr1ti6CdLh8OUkbhccjAY69ZAPqYYuCh.5CKz/XNTsa', '2025-05-23 04:44:51'),
(5, 'admin', 'admin@example.com', '$2y$10$U4YQUchx/86fsJgLhfJfd.D90zIDPna8DMdXZbtligp/sDyux92aq', '2025-05-23 03:19:11'),
(6, 'ajeng', 'ajeng@gmail.com', '$2y$10$KpO0lYdgdgnfH3ep9BprtO/DdoGZ00dvceosK2enrT.tI5UtHT9De', '2025-05-31 20:08:16');

-- --------------------------------------------------------

--
-- Struktur untuk view `laporan_preview`
--
DROP TABLE IF EXISTS `laporan_preview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `laporan_preview`  AS SELECT `laporan`.`kategori` AS `kategori`, sum(`laporan`.`REALISASI_TAHUN_LALU`) AS `total_realisasi_tahun_lalu`, sum(`laporan`.`ANGGARAN_TAHUN_INI`) AS `total_anggaran_tahun_ini`, sum(`laporan`.`REALISASI_TAHUN_INI`) AS `total_realisasi_tahun_ini`, sum(`laporan`.`ANGGARAN_TAHUN_2025`) AS `total_anggaran_tahun_2025` FROM `laporan` GROUP BY `laporan`.`kategori``kategori`  ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `import_data`
--
ALTER TABLE `import_data`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `investasi`
--
ALTER TABLE `investasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_parent_id` (`parent_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `import_data`
--
ALTER TABLE `import_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT untuk tabel `investasi`
--
ALTER TABLE `investasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `fk_laporan_parent` FOREIGN KEY (`parent_id`) REFERENCES `laporan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
