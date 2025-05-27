-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Bulan Mei 2025 pada 10.43
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
-- Database: `elearning_devi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `pelajaran_id` int(11) DEFAULT NULL,
  `id_guru` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `deadline` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru_kelas_pelajaran`
--

CREATE TABLE `guru_kelas_pelajaran` (
  `id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `pelajaran_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guru_kelas_pelajaran`
--

INSERT INTO `guru_kelas_pelajaran` (`id`, `guru_id`, `kelas_id`, `pelajaran_id`) VALUES
(1, 4, 12, 13),
(2, 5, 13, 1),
(3, 25, 1, 2),
(4, 27, 1, 1),
(5, 4, 1, 7),
(6, 4, 2, 9);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `wali_kelas_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `wali_kelas_id`, `created_at`) VALUES
(1, 'XI TKJ 1', NULL, '2025-04-29 20:06:17'),
(2, 'XI TKJ 2', NULL, '2025-04-29 20:06:25'),
(3, 'XI PROFI 1', NULL, '2025-04-29 20:06:36'),
(4, 'XI PROFI 2', NULL, '2025-04-29 20:06:47'),
(5, 'XI TKR 1', NULL, '2025-04-29 20:07:02'),
(6, 'XI TKR 2', NULL, '2025-04-29 20:07:09'),
(7, 'XI TKR 3', NULL, '2025-04-29 20:07:20'),
(8, 'XI TBKR 1', NULL, '2025-04-29 20:07:32'),
(9, 'XI TBKR 2', NULL, '2025-04-29 20:07:40'),
(10, 'XI TPL 1', NULL, '2025-04-29 20:07:47'),
(11, 'XI TPL 2', NULL, '2025-04-29 20:07:59'),
(12, 'XI APAT 1', 4, '2025-04-29 20:08:13'),
(13, 'XI APAT 2', NULL, '2025-04-29 20:08:21'),
(14, 'XI APAT 3', NULL, '2025-04-29 20:08:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas_siswa`
--

CREATE TABLE `kelas_siswa` (
  `id_kelas_siswa` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kelas_siswa`
--

INSERT INTO `kelas_siswa` (`id_kelas_siswa`, `id_siswa`, `id_kelas`) VALUES
(4, 35, 1),
(5, 9, 1),
(14, 31, 1),
(15, 32, 1),
(19, 11, 1),
(25, 16, 1),
(29, 18, 12);

-- --------------------------------------------------------

--
-- Struktur dari tabel `materi`
--

CREATE TABLE `materi` (
  `id_materi` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `id_pelajaran` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `deadline` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `materi`
--

INSERT INTO `materi` (`id_materi`, `judul`, `deskripsi`, `file_path`, `id_pelajaran`, `id_kelas`, `id_guru`, `created_at`, `deadline`) VALUES
(2, 'jkklsk', 'skksk', '../uploads/materi_6810e371118c89.48729627.pptx', 1, 13, 5, '2025-04-29 21:34:25', NULL),
(3, 'Materi Matematika Dasar', NULL, '/elearning/uploads/materi_1.pdf', 1, 1, 2, '2025-04-29 21:58:48', NULL),
(4, 'ddd', '', '../uploads/materi_681890b25e3761.27464284.pptx', 13, 1, 4, '2025-05-01 10:11:01', NULL),
(26, 'contoh', 'ksksk', '../uploads/materi_681efeb5550767.04710406.docx', 7, 1, 4, '2025-05-10 14:22:29', NULL),
(27, 'sore ini', 'segera dikerjakan', '../uploads/materi_681f1eada20678.21221376.docx', 7, 1, 4, '2025-05-10 16:38:53', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notif` int(11) NOT NULL,
  `id_penerima` int(11) NOT NULL,
  `isi` text NOT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `dari_role` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id_notif`, `id_penerima`, `isi`, `tipe`, `dari_role`, `link`, `is_read`, `created_at`) VALUES
(1, 35, 'Ada tugas baru dari guru: \'akksk\'', 'tugas', 'guru', 'siswa/detail_tugas.php?id=3', 0, '2025-05-01 13:47:56'),
(2, 9, 'Ada tugas baru dari guru: \'akksk\'', 'tugas', 'guru', 'siswa/detail_tugas.php?id=3', 0, '2025-05-01 13:47:56'),
(3, 35, 'Ada tugas baru dari guru: \'kskssk\'', 'tugas', 'guru', 'siswa/detail_tugas.php?id=4', 0, '2025-05-01 14:08:29'),
(4, 9, 'Ada tugas baru dari guru: \'kskssk\'', 'tugas', 'guru', 'siswa/detail_tugas.php?id=4', 0, '2025-05-01 14:08:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `nama_pelajaran` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `subjects`
--

INSERT INTO `subjects` (`id`, `nama_pelajaran`, `deskripsi`, `created_at`) VALUES
(1, 'Bahasa Inggris', '', '2025-04-29 19:56:12'),
(2, 'Bahasa Indonesia', '', '2025-04-29 19:56:22'),
(3, 'Pendidikan Agama', '', '2025-04-29 19:56:32'),
(4, 'Pendidikan Pancasila', '', '2025-04-29 19:56:48'),
(5, 'Sejarah Indonesia', '', '2025-04-29 19:57:12'),
(6, 'Seni Budaya', '', '2025-04-29 19:58:01'),
(7, 'Informatika', '', '2025-04-29 19:58:11'),
(8, 'Bahasa Jawa', '', '2025-04-29 19:58:24'),
(9, 'Project IPAS', '', '2025-04-29 19:58:35'),
(10, 'Matematika', '', '2025-04-29 19:58:48'),
(11, 'Pendidikan Jasmani & Kesehatan', '', '2025-04-29 19:59:28'),
(12, 'Mapel Pilihan', '', '2025-04-29 19:59:50'),
(13, 'Projek Kreatif dan Kewirausahaan', '', '2025-04-29 20:00:20'),
(14, 'Kejuruan', '', '2025-04-29 20:00:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `submitted_assignments`
--

CREATE TABLE `submitted_assignments` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) DEFAULT NULL,
  `siswa_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `is_checked` tinyint(1) DEFAULT 0,
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas`
--

CREATE TABLE `tugas` (
  `id_tugas` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `id_pelajaran` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `id_materi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tugas`
--

INSERT INTO `tugas` (`id_tugas`, `judul`, `deskripsi`, `file_path`, `deadline`, `id_pelajaran`, `id_kelas`, `id_guru`, `created_at`, `id_materi`) VALUES
(6, 'bab3', 'sksk', '../uploads/tugas/tugas_681f0305b97672.20282111.docx', '2025-05-10 20:00:00', 7, 1, 4, '2025-05-10 14:40:53', NULL),
(7, 'sore semuanya', 'harus dikerjakan ya', '../uploads/tugas/tugas_681f1ff0cf91d6.28872407.pptx', '2025-05-10 22:00:00', 7, 1, 4, '2025-05-10 16:44:16', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas_siswa`
--

CREATE TABLE `tugas_siswa` (
  `id` int(11) NOT NULL,
  `id_tugas` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `status` enum('belum_dikumpulkan','sudah_dikumpulkan') DEFAULT 'belum_dikumpulkan' COMMENT 'Status pengumpulan tugas',
  `nilai` decimal(5,2) DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tugas_siswa`
--

INSERT INTO `tugas_siswa` (`id`, `id_tugas`, `id_siswa`, `file_path`, `submitted_at`, `status`, `nilai`, `catatan`) VALUES
(1, 6, 9, '../uploads/tugas/jawaban_681f0e03580f30.91420589.docx', '2025-05-10 15:27:47', 'belum_dikumpulkan', NULL, NULL),
(2, 6, 35, '../uploads/tugas/jawaban_681f1ddacf4fa6.91628524.docx', '2025-05-10 16:35:22', 'belum_dikumpulkan', NULL, NULL),
(3, 6, 31, '../uploads/tugas/jawaban_681f1e562390f3.44653947.docx', '2025-05-10 16:37:26', 'belum_dikumpulkan', NULL, NULL),
(4, 6, 11, '../uploads/tugas/jawaban_681f1e8047d078.24993530.docx', '2025-05-10 16:38:08', 'belum_dikumpulkan', NULL, NULL),
(5, 7, 9, '../uploads/tugas/jawaban_681f201ed50b35.39379711.docx', '2025-05-10 16:45:02', 'belum_dikumpulkan', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin1', '$2y$12$88ItgdY2WcysV5i8tnmkr.QnWiyqBAGuZDfnxnjfk8iga98J.rX9S', 'admin', '2025-04-29 18:50:42'),
(2, 'admin2', '$2y$12$88ItgdY2WcysV5i8tnmkr.QnWiyqBAGuZDfnxnjfk8iga98J.rX9S', 'admin', '2025-04-29 18:50:42'),
(3, 'admin3', '$2y$12$88ItgdY2WcysV5i8tnmkr.QnWiyqBAGuZDfnxnjfk8iga98J.rX9S', 'admin', '2025-04-29 18:50:42'),
(4, 'guru1', '$2y$12$A9L3lqAYUoEyCzoXXRociekt.4AQWArbbJ3wfSHxEemK3ezN2HcZu', 'guru', '2025-04-29 18:50:42'),
(5, 'guru2', '$2y$12$A9L3lqAYUoEyCzoXXRociekt.4AQWArbbJ3wfSHxEemK3ezN2HcZu', 'guru', '2025-04-29 18:50:42'),
(6, 'guru3', '$2y$12$A9L3lqAYUoEyCzoXXRociekt.4AQWArbbJ3wfSHxEemK3ezN2HcZu', 'guru', '2025-04-29 18:50:42'),
(7, 'guru4', '$2y$12$A9L3lqAYUoEyCzoXXRociekt.4AQWArbbJ3wfSHxEemK3ezN2HcZu', 'guru', '2025-04-29 18:50:42'),
(8, 'guru5', '$2y$12$A9L3lqAYUoEyCzoXXRociekt.4AQWArbbJ3wfSHxEemK3ezN2HcZu', 'guru', '2025-04-29 18:50:42'),
(9, 'siswa1', '$2y$12$iSHuWmTMvNLfpAYbQfq.outW932jV/rSBbjfTaaLv/av9TW9eie3O', 'siswa', '2025-04-29 18:50:42'),
(10, 'siswa2', '$2y$12$iSHuWmTMvNLfpAYbQfq.outW932jV/rSBbjfTaaLv/av9TW9eie3O', 'siswa', '2025-04-29 18:50:42'),
(11, 'siswa3', '$2y$12$iSHuWmTMvNLfpAYbQfq.outW932jV/rSBbjfTaaLv/av9TW9eie3O', 'siswa', '2025-04-29 18:50:42'),
(12, 'siswa4', '$2y$12$iSHuWmTMvNLfpAYbQfq.outW932jV/rSBbjfTaaLv/av9TW9eie3O', 'siswa', '2025-04-29 18:50:42'),
(13, 'siswa5', '$2y$12$iSHuWmTMvNLfpAYbQfq.outW932jV/rSBbjfTaaLv/av9TW9eie3O', 'siswa', '2025-04-29 18:50:42'),
(14, 'siswa6', '$2y$12$iSHuWmTMvNLfpAYbQfq.outW932jV/rSBbjfTaaLv/av9TW9eie3O', 'siswa', '2025-04-29 18:50:42'),
(15, 'siswa7', '$2y$12$iSHuWmTMvNLfpAYbQfq.outW932jV/rSBbjfTaaLv/av9TW9eie3O', 'siswa', '2025-04-29 18:50:42'),
(16, 'siswa8', '$2y$12$iSHuWmTMvNLfpAYbQfq.outW932jV/rSBbjfTaaLv/av9TW9eie3O', 'siswa', '2025-04-29 18:50:42'),
(17, 'siswa9', '$2y$12$iSHuWmTMvNLfpAYbQfq.outW932jV/rSBbjfTaaLv/av9TW9eie3O', 'siswa', '2025-04-29 18:50:42'),
(18, 'siswa10', '$2y$12$iSHuWmTMvNLfpAYbQfq.outW932jV/rSBbjfTaaLv/av9TW9eie3O', 'siswa', '2025-04-29 18:50:42'),
(21, 'guru15', '$2y$10$8XOv1v132h7SosVFyWBcpOKAT0Ytm5N7qee.H2C..QYO/MLdn/0.W', 'guru', '2025-04-29 19:29:43'),
(22, 'guru6', '$2y$10$Mvn89XkzINnsrp8YpfeVruC/RvaYm0lBLVf2Ytd0NGCBR7G5x6.im', 'guru', '2025-04-29 20:24:49'),
(23, 'guru7', '$2y$10$IiEpkXjcxUSo1s0mdX5CI.pL693bvjGKi8BNCgBcO2JAN1X0hlYhu', 'guru', '2025-04-29 20:24:59'),
(24, 'guru8', '$2y$10$XhGGb12Vw4whP9yaSH9nHeuzyYS0QAfaEZLSM6hhWlo.msKH9p1i6', 'guru', '2025-04-29 20:25:15'),
(25, 'guru9', '$2y$10$DHNK5yVACFUzLWuLqnBtRe5A.VxIldqBoCtfC2LM62EWAJvnJPtWe', 'guru', '2025-04-29 20:25:23'),
(26, 'guru10', '$2y$10$bBjvK9nxX5EvRKhpHvsu4u/evmP.EdwbwlLfnLS93/ebyPHeArivq', 'guru', '2025-04-29 20:25:31'),
(27, 'guru11', '$2y$10$qtg0nUjLNEfdz1a3Gk4dTu7kWdj9f/JFAgqetA3SRE/fatC0AZqXW', 'guru', '2025-04-29 20:25:40'),
(28, 'guru12', '$2y$10$TYzdXpvTc/ewOrCmDgxE9OSrQU0IhIiBppF/h5BvVkHbSyrOivt/S', 'guru', '2025-04-29 20:25:50'),
(29, 'guru13', '$2y$10$3NSPcZqEzHXPLM2k.fnzg.p5DUISBE3gg.RTgLkh32Ar7vXHZFv7q', 'guru', '2025-04-29 20:25:57'),
(30, 'guru14', '$2y$10$xmgZT6Tzs7rNHoU4ZMSFFuuyjoiatZ3HITTbZd24joXrgRMsfpoVS', 'guru', '2025-04-29 20:26:09'),
(31, 'siswa11', '$2y$10$c2kZkhHOO1GlxgPaQdc1iejrQjvfHU0K9ospTNaJOQ4Q1RMLgzxni', 'siswa', '2025-04-29 21:35:42'),
(32, 'siswa12', '$2y$10$kUqE0du8OovVmQucItpOkO82./G3ID0gDkKRm0jS/rrBg5HCYsh0a', 'siswa', '2025-04-29 21:35:53'),
(33, 'siswa13', '$2y$10$QIdDF0q7m8Tdgwwb/QV0DeyFsNF9uS8tKLXN5bvvAwqewm.H4591O', 'siswa', '2025-04-29 21:36:07'),
(34, 'siswa14', '$2y$10$LtVVFSPwhZwer.Wj.B3KhOZWchuPOVs7MxjBTehKJEYw/rl6A70QK', 'siswa', '2025-04-29 21:36:18'),
(35, 'siswa15', '$2y$10$E93O0XSdSyxM6ijnV2/V5OeAYWPt8yPNaH5T3nVIMwAy7pXNF3eMe', 'siswa', '2025-04-29 21:36:29'),
(36, 'devi', '$2y$10$OiqKxXPj3TA65K0zzxvBlO6iIv2I0VgNiTx9PrqEHJq1wOWhzDRBK', 'admin', '2025-05-06 11:58:13');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `guru_kelas_pelajaran`
--
ALTER TABLE `guru_kelas_pelajaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `pelajaran_id` (`pelajaran_id`);

--
-- Indeks untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_kelas` (`nama_kelas`),
  ADD KEY `wali_kelas_id` (`wali_kelas_id`);

--
-- Indeks untuk tabel `kelas_siswa`
--
ALTER TABLE `kelas_siswa`
  ADD PRIMARY KEY (`id_kelas_siswa`),
  ADD KEY `id_siswa` (`id_siswa`),
  ADD KEY `id_kelas` (`id_kelas`);

--
-- Indeks untuk tabel `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`id_materi`),
  ADD KEY `id_pelajaran` (`id_pelajaran`),
  ADD KEY `id_kelas` (`id_kelas`),
  ADD KEY `id_guru` (`id_guru`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notif`);

--
-- Indeks untuk tabel `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_pelajaran` (`nama_pelajaran`);

--
-- Indeks untuk tabel `submitted_assignments`
--
ALTER TABLE `submitted_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indeks untuk tabel `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id_tugas`),
  ADD KEY `id_pelajaran` (`id_pelajaran`),
  ADD KEY `id_kelas` (`id_kelas`),
  ADD KEY `id_guru` (`id_guru`);

--
-- Indeks untuk tabel `tugas_siswa`
--
ALTER TABLE `tugas_siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_unicity` (`id_tugas`,`id_siswa`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `guru_kelas_pelajaran`
--
ALTER TABLE `guru_kelas_pelajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `kelas_siswa`
--
ALTER TABLE `kelas_siswa`
  MODIFY `id_kelas_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `materi`
--
ALTER TABLE `materi`
  MODIFY `id_materi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `submitted_assignments`
--
ALTER TABLE `submitted_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id_tugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `tugas_siswa`
--
ALTER TABLE `tugas_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `guru_kelas_pelajaran`
--
ALTER TABLE `guru_kelas_pelajaran`
  ADD CONSTRAINT `guru_kelas_pelajaran_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guru_kelas_pelajaran_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guru_kelas_pelajaran_ibfk_3` FOREIGN KEY (`pelajaran_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`wali_kelas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `kelas_siswa`
--
ALTER TABLE `kelas_siswa`
  ADD CONSTRAINT `kelas_siswa_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `kelas_siswa_ibfk_2` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`);

--
-- Ketidakleluasaan untuk tabel `materi`
--
ALTER TABLE `materi`
  ADD CONSTRAINT `materi_ibfk_1` FOREIGN KEY (`id_pelajaran`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `materi_ibfk_2` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`),
  ADD CONSTRAINT `materi_ibfk_3` FOREIGN KEY (`id_guru`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `submitted_assignments`
--
ALTER TABLE `submitted_assignments`
  ADD CONSTRAINT `submitted_assignments_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`),
  ADD CONSTRAINT `submitted_assignments_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`id_pelajaran`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `tugas_ibfk_2` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`),
  ADD CONSTRAINT `tugas_ibfk_3` FOREIGN KEY (`id_guru`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `tugas_siswa`
--
ALTER TABLE `tugas_siswa`
  ADD CONSTRAINT `tugas_siswa_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
