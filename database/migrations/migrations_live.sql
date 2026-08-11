-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 11, 2026 at 07:44 AM
-- Server version: 8.0.46-0ubuntu0.24.04.3
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_portal_students`
--

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_05_10_144003_create_permission_tables', 1),
(5, '2024_05_10_144032_create_products_table', 1),
(6, '2024_11_15_024221_create_table_program_studi', 2),
(7, '2024_11_15_024234_create_table_mahasiswa', 3),
(8, '2024_11_18_020128_create_mata_kuliah_table', 3),
(9, '2024_11_18_024530_create_jadwal_table', 4),
(10, '2024_11_18_024902_create_jadwals_table', 5),
(11, '2024_11_18_064240_create_absensi_table', 6),
(12, '2024_11_18_072114_add_status_absensi_to_jadwal_table', 7),
(13, '2024_11_19_085046_add_user_id_and_program_studi_id_to_jadwal_table', 8),
(14, '2024_11_24_053009_add_tanggal_to_jadwal_table', 9),
(15, '2024_11_24_062905_add_total_pertemuan_to_matakuliah_table', 9),
(16, '2024_11_24_110828_create_settings_table', 9),
(17, '2024_12_03_092817_create_pertemuans_table', 10),
(18, '2024_12_03_092938_add_pertemuan_id_to_absensi_table', 10),
(19, '2024_12_04_083336_add_status_to_pertemuan_table', 10),
(20, '2025_01_03_082932_table_ruangan', 11),
(22, '2025_01_02_040612_create_dosens_table', 12),
(23, '2025_01_06_062611_create_dosen_mata_kuliah_table', 13),
(24, '2025_01_06_081349_create_dosen_matakuliahs_table', 14),
(25, '2025_01_07_070514_create_jadwal_uts_table', 14),
(26, '2025_01_07_070515_create_jadwal_uap_table', 14),
(27, '2025_01_07_070515_create_jadwal_uas_table', 14),
(28, '2025_01_22_032346_create_penilaian_table', 15),
(29, '2025_01_23_075317_create_penilaians_table', 16),
(30, '2025_01_23_081849_create_saran_table', 16),
(31, '2025_01_31_072424_add_kategori_to_evaluasi_table', 17),
(32, '2025_01_31_075253_add_jenis_dosen_to_dosen_mata_kuliah_table', 18),
(33, '2025_01_31_085218_add_jenis_dosen_to_penilaian_table', 19),
(34, '2025_01_31_092829_add_jenis_dosen_to_saran_table', 20),
(35, '2025_02_07_024125_create_tarif_persemester_table', 21),
(36, '2025_02_07_024125_create_tenor_pembayaran_table', 21),
(37, '2025_02_07_024126_create_tagihan_mahasiswa_table', 21),
(38, '2025_02_11_074824_gelombang', 22),
(39, '2025_02_11_080433_add_gelombang_id_to_mahasiswa_and_tarif_persemester', 23),
(40, '2025_02_24_061449_create_calender_akademik_table', 24),
(41, '2025_02_24_084319_create_jadwal_table', 25),
(42, '2025_02_26_040538_add_remember_token_to_dosens_table', 26),
(52, '2025_05_16_020055_create_sertifikasis_table', 27),
(53, '2025_05_16_020419_create_ppsms_table', 28),
(54, '2025_05_16_020540_create_p2mw_programs_table', 29),
(55, '2025_05_16_020603_create_pkm_programs_table', 30),
(56, '2025_05_16_021617_create_kegiatan_tambahans_table', 31),
(57, '2025_05_19_044636_create_penguasaan_bahasas_table', 32),
(58, '2025_06_20_063734_jadwal_uap', 33),
(59, '2025_06_25_031226_create_uap_nilai_table', 34),
(60, '2025_08_06_070422_create_bobot_nilai_table', 34),
(61, '2026_04_13_045300_add_performance_indexes_to_jadwal_tables', 34),
(62, '2026_04_14_071100_add_komponen_nilai_to_krs_table', 34),
(63, '2026_04_14_072000_ensure_bobot_nilai_table_creation', 34),
(64, '2026_04_14_073000_fix_matakuliah_id_schema_in_bobot_nilai', 34),
(65, '2026_04_16_025631_rebuild_pembayaran_system_tables', 34),
(66, '2026_04_22_090000_create_activity_logs_table', 35),
(67, '2026_04_28_092400_add_unique_constraints_and_fix_dosen_id_type', 36),
(68, '2026_06_05_000000_add_mahasiswa_impersonate_permission', 37);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
