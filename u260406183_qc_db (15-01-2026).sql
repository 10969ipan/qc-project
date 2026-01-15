-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 15 Jan 2026 pada 13.10
-- Versi server: 11.8.3-MariaDB-log
-- Versi PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u260406183_qc_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Sub Assy', '2026-01-09 11:10:57', '2026-01-09 11:10:57'),
(2, 'Inprosess', '2026-01-09 11:11:06', '2026-01-09 11:21:17'),
(3, 'Cross Cut Plating', '2026-01-09 11:11:19', '2026-01-09 11:11:19'),
(4, 'Cross Cut Painting', '2026-01-09 16:19:48', '2026-01-09 16:19:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheets`
--

CREATE TABLE `checksheets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `shift` varchar(255) NOT NULL,
  `line` varchar(255) DEFAULT NULL,
  `total_qty` int(11) NOT NULL,
  `sampling_qty` int(11) NOT NULL,
  `total_ok` int(11) NOT NULL,
  `total_ng` int(11) NOT NULL,
  `judgment` varchar(255) NOT NULL,
  `operator_initials` varchar(255) DEFAULT NULL,
  `kashift_qc` varchar(255) DEFAULT NULL,
  `kashift_approved_at` timestamp NULL DEFAULT NULL,
  `supervisor_qc` varchar(255) DEFAULT NULL,
  `supervisor_approved_at` timestamp NULL DEFAULT NULL,
  `asst_manager_qc` varchar(255) DEFAULT NULL,
  `manager_qc` varchar(255) DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `asst_manager_approved_at` timestamp NULL DEFAULT NULL,
  `approval_status` varchar(255) DEFAULT NULL,
  `rejection_remarks` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `next_proses` varchar(50) DEFAULT NULL,
  `defects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`defects`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cycle_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `checksheets`
--

INSERT INTO `checksheets` (`id`, `item_id`, `date`, `shift`, `line`, `total_qty`, `sampling_qty`, `total_ok`, `total_ng`, `judgment`, `operator_initials`, `kashift_qc`, `kashift_approved_at`, `supervisor_qc`, `supervisor_approved_at`, `asst_manager_qc`, `manager_qc`, `manager_approved_at`, `asst_manager_approved_at`, `approval_status`, `rejection_remarks`, `remarks`, `next_proses`, `defects`, `created_at`, `updated_at`, `cycle_time`) VALUES
(1, 1, '2025-12-22', '1', NULL, 300, 50, 50, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Ubah packing dari (20) pcs ke (50) pcs', NULL, '[]', '2025-12-22 14:03:24', '2025-12-24 07:07:22', 1563),
(2, 3, '2025-12-22', '2', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-22 15:14:41', '2025-12-24 07:06:40', 473),
(3, 1, '2025-12-22', '2', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-22 15:25:21', '2025-12-24 07:08:25', 467),
(4, 2, '2025-12-22', '2', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-22 16:25:27', '2025-12-24 07:07:04', 577),
(5, 2, '2025-12-19', '1', NULL, 100, 20, 20, 0, 'OK', 'AP', 'Administrator', NULL, 'Administrator', '2025-12-25 20:20:11', 'Administrator', 'Administrator', '2025-12-25 20:20:18', '2025-12-25 20:20:15', 'Approved', NULL, NULL, NULL, '[]', '2025-12-19 08:20:45', '2025-12-25 20:20:18', 914),
(8, 2, '2025-12-19', '1', NULL, 200, 32, 32, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 10:22:50', '2025-12-24 07:19:22', 1400),
(9, 1, '2025-12-19', '1', NULL, 300, 50, 50, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 10:48:28', '2025-12-24 07:19:01', 1173),
(10, 1, '2025-12-19', '1', NULL, 150, 20, 20, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 11:11:04', '2025-12-24 07:18:37', 563),
(11, 1, '2025-12-19', '1', NULL, 300, 50, 50, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 14:06:30', '2025-12-24 07:17:58', 933),
(12, 3, '2025-12-19', '1', NULL, 300, 50, 50, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 14:22:37', '2025-12-24 07:17:19', 835),
(13, 1, '2025-12-19', '1', NULL, 250, 32, 32, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 14:37:52', '2025-12-24 07:18:11', 769),
(14, 3, '2025-12-19', '1', NULL, 100, 20, 20, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 15:10:45', '2025-12-24 07:17:39', 31),
(15, 3, '2025-12-19', '2', NULL, 100, 20, 20, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 15:19:07', '2025-12-24 07:18:21', 242),
(16, 2, '2025-12-19', '2', NULL, 80, 20, 20, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 16:35:23', '2025-12-24 07:17:29', 594),
(17, 2, '2025-12-19', '2', NULL, 80, 20, 20, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 17:17:16', '2025-12-24 07:15:49', 506),
(18, 2, '2025-12-19', '2', NULL, 140, 20, 20, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 19:12:53', '2025-12-24 07:15:42', 632),
(19, 3, '2025-12-19', '2', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 19:35:00', '2025-12-24 07:15:31', 693),
(20, 1, '2025-12-19', '2', NULL, 350, 50, 50, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 20:03:17', '2025-12-24 07:13:12', 1506),
(22, 3, '2025-12-19', '2', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 22:31:50', '2025-12-24 07:13:03', 497),
(23, 1, '2025-12-19', '2', NULL, 50, 20, 20, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 22:45:07', '2025-12-24 07:13:40', 407),
(24, 1, '2025-12-19', '3', NULL, 150, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-19 23:06:37', '2025-12-24 07:13:21', 619),
(25, 2, '2025-12-20', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 01:07:28', '2025-12-24 07:12:52', 449),
(26, 1, '2025-12-20', '3', NULL, 150, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 03:21:55', '2025-12-24 07:14:14', 320),
(27, 2, '2025-12-20', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 03:33:44', '2025-12-24 07:12:41', 475),
(28, 2, '2025-12-20', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 03:42:03', '2025-12-24 07:13:57', 413),
(29, 1, '2025-12-20', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 05:18:24', '2025-12-24 07:13:29', 748),
(30, 3, '2025-12-20', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 05:34:42', '2025-12-24 07:13:48', 487),
(31, 1, '2025-12-20', '3', NULL, 50, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 05:48:59', '2025-12-24 07:11:43', 219),
(32, 3, '2025-12-20', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 06:06:03', '2025-12-24 07:11:59', 671),
(33, 1, '2025-12-20', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 06:22:57', '2025-12-24 07:12:11', 329),
(35, 1, '2025-12-20', '1', NULL, 200, 32, 32, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Packing 20 pcs label hijau untuk packing', NULL, '[]', '2025-12-20 10:44:03', '2025-12-24 07:12:20', 549),
(36, 1, '2025-12-20', '1', NULL, 300, 50, 50, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Packing 20 pcs label hijau untuk packing', NULL, '[]', '2025-12-20 10:54:51', '2025-12-24 07:11:07', 745),
(37, 3, '2025-12-20', '1', NULL, 100, 20, 20, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 11:09:00', '2025-12-24 07:11:16', 329),
(39, 1, '2025-12-20', '1', NULL, 300, 50, 50, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Packing 20 pcs label hijau untuk packing', NULL, '[]', '2025-12-20 11:16:04', '2025-12-24 07:11:34', 739),
(40, 2, '2025-12-20', '1', NULL, 300, 50, 50, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 11:29:25', '2025-12-24 07:11:25', 745),
(41, 1, '2025-12-20', '1', NULL, 200, 32, 32, 0, 'OK', 'AP', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 11:42:44', '2025-12-24 07:11:49', 464),
(43, 3, '2025-12-20', '2', NULL, 100, 20, 20, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 14:57:41', '2025-12-24 07:10:58', 363),
(44, 1, '2025-12-20', '2', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Part isi 20 pcs ,untuk packing', NULL, '[]', '2025-12-20 15:14:39', '2025-12-24 07:09:56', 774),
(45, 1, '2025-12-20', '2', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 15:34:49', '2025-12-24 07:09:40', 713),
(46, 2, '2025-12-20', '2', NULL, 300, 50, 50, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 16:14:00', '2025-12-24 07:10:14', 1174),
(47, 1, '2025-12-20', '2', NULL, 100, 20, 20, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 16:42:59', '2025-12-24 07:10:05', 375),
(48, 2, '2025-12-20', '3', NULL, 80, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 18:35:05', '2025-12-24 07:09:47', 616),
(49, 1, '2025-12-20', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 18:46:23', '2025-12-24 07:10:24', 501),
(50, 1, '2025-12-20', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 18:56:04', '2025-12-24 07:09:22', 501),
(51, 3, '2025-12-20', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 19:37:37', '2025-12-24 07:10:34', 663),
(52, 2, '2025-12-20', '3', NULL, 120, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 20:11:39', '2025-12-24 07:09:30', 619),
(53, 1, '2025-12-20', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 20:22:44', '2025-12-24 07:08:54', 509),
(54, 1, '2025-12-20', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 20:33:56', '2025-12-24 07:08:09', 491),
(55, 2, '2025-12-20', '3', NULL, 80, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 21:18:31', '2025-12-24 07:08:37', 354),
(56, 1, '2025-12-20', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 21:25:35', '2025-12-24 07:07:55', 471),
(57, 2, '2025-12-20', '3', NULL, 20, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-20 21:33:54', '2025-12-24 07:05:08', 313),
(58, 3, '2025-12-22', '2', NULL, 188, 32, 32, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-22 20:47:45', '2025-12-24 07:07:42', 1144),
(59, 2, '2025-12-22', '2', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-22 21:01:38', '2025-12-24 07:06:55', 756),
(60, 1, '2025-12-22', '2', NULL, 300, 50, 50, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-22 21:21:42', '2025-12-24 07:05:53', 1149),
(61, 1, '2025-12-22', '2', NULL, 150, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-22 21:28:34', '2025-12-24 07:06:01', 381),
(62, 1, '2025-12-22', '2', NULL, 150, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-22 22:12:56', '2025-12-24 07:05:44', 377),
(63, 1, '2025-12-22', '2', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-22 22:29:22', '2025-12-24 07:05:35', 377),
(64, 2, '2025-12-22', '3', NULL, 100, 20, 20, 0, 'OK', 'AY', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 01:25:20', '2025-12-24 07:05:28', 446),
(65, 1, '2025-12-22', '3', NULL, 150, 20, 20, 0, 'OK', 'AY', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 01:52:52', '2025-12-24 07:04:54', 589),
(67, 2, '2025-12-22', '3', NULL, 100, 20, 20, 0, 'OK', 'AY', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 03:07:41', '2025-12-24 07:05:19', 417),
(68, 1, '2025-12-22', '3', NULL, 100, 20, 20, 0, 'OK', 'AY', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 03:36:58', '2025-12-24 07:04:59', 366),
(69, 1, '2025-12-22', '3', NULL, 150, 20, 20, 0, 'OK', 'AY', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 05:35:40', '2025-12-24 07:06:10', 409),
(70, 3, '2025-12-22', '3', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 06:03:53', '2025-12-24 07:06:23', 496),
(71, 1, '2025-12-22', '3', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 06:14:15', '2025-12-24 07:04:38', 549),
(72, 1, '2025-12-22', '3', NULL, 100, 20, 20, 0, 'OK', 'AY', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 06:23:19', '2025-12-24 07:04:32', 219),
(73, 2, '2025-12-22', '3', NULL, 100, 20, 20, 0, 'OK', 'AY', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 06:42:21', '2025-12-24 07:04:23', 563),
(74, 3, '2025-12-23', '1', NULL, 150, 20, 20, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 10:03:30', '2025-12-24 07:03:59', 318),
(75, 1, '2025-12-23', '1', NULL, 300, 50, 50, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 10:28:39', '2025-12-24 07:04:10', 1198),
(76, 2, '2025-12-23', '1', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 10:52:04', '2025-12-24 07:04:17', 787),
(77, 1, '2025-12-23', '1', NULL, 150, 20, 20, 0, 'OK', 'DS', 'Administrator', NULL, 'Administrator', NULL, 'Administrator', 'Administrator', '2025-12-25 19:50:34', NULL, 'Approved', NULL, NULL, NULL, '[]', '2025-12-23 11:01:17', '2025-12-25 19:50:34', 325),
(78, 1, '2025-12-23', '1', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 13:49:09', '2025-12-24 07:04:04', 800),
(79, 1, '2025-12-23', '1', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 14:45:27', '2025-12-24 07:03:55', 657),
(80, 2, '2025-12-23', '2', NULL, 80, 20, 20, 0, 'OK', 'GK', 'Mr Ahmad Jaeni', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-23 16:41:10', '2025-12-24 07:03:46', 446),
(81, 1, '2025-12-24', '1', NULL, 300, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:55:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 11:04:25', '2026-01-06 09:55:52', 1213),
(83, 3, '2025-12-24', '1', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:55:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 12:40:03', '2026-01-06 09:55:47', 525),
(85, 2, '2025-12-24', '1', NULL, 300, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:55:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 13:03:35', '2026-01-06 09:55:42', 1193),
(86, 1, '2025-12-24', '1', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:55:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 13:50:55', '2026-01-06 09:55:38', 813),
(87, 3, '2025-12-24', '1', NULL, 100, 20, 20, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:55:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 14:10:04', '2026-01-06 09:55:34', 263),
(88, 2, '2025-12-24', '1', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:55:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 14:51:07', '2026-01-06 09:55:28', 773),
(89, 1, '2025-12-24', '1', NULL, 150, 20, 20, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:55:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 14:59:08', '2026-01-06 09:55:16', 409),
(90, 2, '2025-12-24', '1', NULL, 160, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:55:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 16:54:44', '2026-01-06 09:55:12', 629),
(91, 1, '2025-12-24', '2', NULL, 450, 50, 50, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:55:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 21:51:10', '2026-01-06 09:55:09', 668),
(92, 3, '2025-12-24', '2', NULL, 300, 50, 50, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:55:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 22:02:17', '2026-01-06 09:55:05', 625),
(93, 1, '2025-12-24', '2', NULL, 250, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:54:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 22:14:51', '2026-01-06 09:54:59', 303),
(94, 2, '2025-12-24', '2', NULL, 240, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:54:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-24 22:23:29', '2026-01-06 09:54:55', 452),
(95, 2, '2025-12-24', '3', NULL, 100, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:54:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 01:20:14', '2026-01-06 09:54:50', 483),
(96, 1, '2025-12-24', '3', NULL, 60, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:54:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Box belum standar', NULL, '[]', '2025-12-25 01:29:46', '2026-01-06 09:54:44', 415),
(97, 1, '2025-12-24', '3', NULL, 150, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:54:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 02:30:13', '2026-01-06 09:54:40', 512),
(98, 3, '2025-12-24', '3', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:54:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 02:40:02', '2026-01-06 09:54:36', 476),
(99, 2, '2025-12-24', '3', NULL, 300, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:54:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 05:55:17', '2026-01-06 09:54:32', 971),
(100, 1, '2025-12-24', '3', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:54:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 06:02:37', '2026-01-06 09:54:28', 352),
(101, 1, '2025-12-24', '3', NULL, 240, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:54:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Box belum standar', NULL, '[]', '2025-12-25 06:28:18', '2026-01-06 09:54:23', 559),
(102, 1, '2025-12-24', '3', NULL, 50, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:54:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 06:42:41', '2026-01-06 09:54:13', 316),
(103, 2, '2025-12-24', '3', NULL, 100, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:54:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 06:48:26', '2026-01-06 09:54:08', 280),
(104, 1, '2025-12-25', '1', NULL, 300, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:54:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 10:17:45', '2026-01-06 09:54:04', 1013),
(105, 2, '2025-12-25', '1', NULL, 300, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:53:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 13:21:43', '2026-01-06 09:53:59', 1171),
(106, 3, '2025-12-25', '1', NULL, 300, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:53:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 13:33:43', '2026-01-06 09:53:54', 541),
(107, 1, '2025-12-25', '1', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:53:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 14:27:48', '2026-01-06 09:53:49', 690),
(108, 2, '2025-12-25', '1', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:53:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 14:39:21', '2026-01-06 09:53:44', 652),
(109, 1, '2025-12-25', '1', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:53:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 14:52:37', '2026-01-06 09:53:39', 625),
(110, 2, '2025-12-25', '2', NULL, 80, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:53:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 16:20:33', '2026-01-06 09:53:36', 1),
(111, 2, '2025-12-25', '2', NULL, 80, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:53:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 16:27:51', '2026-01-06 09:53:32', 422),
(112, 1, '2025-12-25', '2', NULL, 180, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:53:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 17:19:16', '2026-01-06 09:53:25', 446),
(113, 3, '2025-12-25', '2', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:53:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 20:10:31', '2026-01-06 09:53:03', 914),
(114, 2, '2025-12-25', '2', NULL, 240, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:52:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 20:20:18', '2026-01-06 09:52:59', 489),
(115, 2, '2025-12-25', '2', NULL, 140, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:52:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 20:26:17', '2026-01-06 09:52:54', 324),
(116, 3, '2025-12-25', '2', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:52:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 21:47:11', '2026-01-06 09:52:50', 458),
(117, 1, '2025-12-25', '1', NULL, 240, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:52:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 21:57:28', '2026-01-06 09:52:46', 499),
(118, 2, '2025-12-25', '2', NULL, 40, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:52:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 22:05:24', '2026-01-06 09:52:42', 368),
(119, 1, '2025-12-25', '2', NULL, 20, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:52:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-25 22:28:01', '2026-01-06 09:52:36', 382),
(120, 2, '2025-12-25', '3', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:52:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 03:09:36', '2026-01-06 09:52:32', 456),
(121, 3, '2025-12-25', '3', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:52:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 03:19:07', '2026-01-06 09:52:27', 395),
(122, 2, '2025-12-25', '3', NULL, 300, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:52:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 05:44:04', '2026-01-06 09:52:21', 644),
(123, 1, '2025-12-25', '3', NULL, 240, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:52:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Box belum standar', NULL, '[]', '2025-12-26 06:13:40', '2026-01-06 09:52:15', 629),
(124, 1, '2025-12-25', '3', NULL, 160, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:52:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Box belum standar', NULL, '[]', '2025-12-26 06:27:14', '2026-01-06 09:52:07', 481),
(127, 1, '2025-12-26', '1', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Mr Ahmad Jaeni', '2025-12-26 09:39:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 09:36:13', '2025-12-26 09:39:28', 10),
(129, 1, '2025-12-26', '1', NULL, 106, 20, 20, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:52:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 10:45:44', '2026-01-06 09:52:04', 423),
(130, 2, '2025-12-26', '1', NULL, 140, 20, 20, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-06 09:52:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 11:41:02', '2026-01-06 09:52:02', 437),
(131, 2, '2025-12-26', '2', NULL, 80, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:52:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 13:54:30', '2026-01-06 09:52:00', 412),
(132, 3, '2025-12-26', '2', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:51:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 15:11:55', '2026-01-06 09:51:57', 754),
(133, 2, '2025-12-26', '2', NULL, 50, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:51:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 15:23:02', '2026-01-06 09:51:55', 597),
(134, 3, '2025-12-26', '2', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-06 09:51:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 15:45:48', '2026-01-06 09:51:53', 1162),
(135, 2, '2025-12-26', '3', NULL, 100, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 09:51:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2025-12-26 19:47:45', '2026-01-06 09:51:51', 219),
(137, 10, '2026-01-06', '1', NULL, 250, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 13:59:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-06 11:05:47', '2026-01-06 13:59:04', 204),
(138, 1, '2026-01-06', '1', NULL, 50, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 13:58:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-06 13:33:30', '2026-01-06 13:58:56', 145),
(139, 1, '2026-01-06', '1', NULL, 22, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-06 13:58:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Blm standar packing', NULL, '[]', '2026-01-06 13:36:32', '2026-01-06 13:58:52', 142),
(140, 10, '2026-01-06', '1', NULL, 250, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-08 07:14:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-06 14:55:26', '2026-01-08 07:14:12', 248),
(141, 10, '2026-01-06', '2', NULL, 400, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-08 07:14:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-06 20:51:24', '2026-01-08 07:14:17', 413),
(142, 3, '2026-01-06', '2', NULL, 150, 20, 20, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-08 07:14:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-06 22:25:50', '2026-01-08 07:14:21', 259),
(143, 10, '2026-01-06', '2', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-08 07:14:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-06 22:32:58', '2026-01-08 07:14:25', 235),
(144, 10, '2026-01-06', '2', NULL, 400, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-08 07:14:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-06 22:43:21', '2026-01-08 07:14:29', 433),
(145, 10, '2026-01-06', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-08 07:14:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-07 00:38:15', '2026-01-08 07:14:33', 371),
(146, 10, '2026-01-06', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-08 07:14:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-07 00:45:43', '2026-01-08 07:14:36', 426),
(147, 3, '2026-01-06', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-08 07:14:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-07 00:53:59', '2026-01-08 07:14:41', 385),
(148, 10, '2026-01-06', '3', NULL, 100, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-08 07:14:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-07 01:10:42', '2026-01-08 07:14:45', 462),
(149, 10, '2026-01-06', '3', NULL, 500, 50, 50, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-08 07:14:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-07 01:32:52', '2026-01-08 07:14:49', 1105),
(150, 10, '2026-01-07', '1', NULL, 400, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-08 07:14:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-07 11:21:30', '2026-01-08 07:14:04', 489),
(151, 10, '2026-01-07', '1', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-08 07:14:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-07 11:26:47', '2026-01-08 07:14:00', 170),
(152, 10, '2026-01-07', '2', NULL, 500, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-08 07:13:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-07 20:58:24', '2026-01-08 07:13:56', 497),
(153, 3, '2026-01-07', '2', NULL, 150, 20, 20, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-08 07:13:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-07 21:13:01', '2026-01-08 07:13:52', 244),
(154, 10, '2026-01-07', '2', NULL, 500, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-08 07:13:48', 'Arief Hidayat', '2026-01-09 13:09:59', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-07 22:52:34', '2026-01-09 13:09:59', 537),
(155, 10, '2026-01-07', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-08 07:13:44', 'Arief Hidayat', '2026-01-09 13:09:53', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 00:37:19', '2026-01-09 13:09:53', 394),
(156, 10, '2026-01-07', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-08 07:13:40', 'Arief Hidayat', '2026-01-09 13:09:45', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 00:43:45', '2026-01-09 13:09:45', 364),
(157, 10, '2026-01-07', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-08 07:13:37', 'Arief Hidayat', '2026-01-09 13:09:39', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 00:56:42', '2026-01-09 13:09:39', 501),
(158, 3, '2026-01-07', '3', NULL, 150, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-08 07:13:33', 'Arief Hidayat', '2026-01-09 13:09:09', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 01:07:17', '2026-01-09 13:09:09', 514),
(159, 10, '2026-01-07', '3', NULL, 400, 50, 50, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-08 07:13:29', 'Arief Hidayat', '2026-01-09 13:09:04', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 06:03:41', '2026-01-09 13:09:04', 934),
(160, 10, '2026-01-08', '1', NULL, 400, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 09:06:58', 'Arief Hidayat', '2026-01-09 13:08:58', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 11:03:07', '2026-01-09 13:08:58', 447),
(161, 10, '2026-01-08', '1', NULL, 400, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 09:07:07', 'Arief Hidayat', '2026-01-09 13:08:54', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 11:09:48', '2026-01-09 13:08:54', 382),
(162, 10, '2026-01-08', '1', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 09:07:24', 'Arief Hidayat', '2026-01-09 13:08:48', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 14:27:46', '2026-01-09 13:08:48', 242),
(163, 10, '2026-01-08', '1', NULL, 500, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 09:07:16', 'Arief Hidayat', '2026-01-09 13:08:43', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 14:44:29', '2026-01-09 13:08:43', 444),
(164, 10, '2026-01-08', '2', NULL, 400, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-09 09:07:11', 'Arief Hidayat', '2026-01-09 13:08:10', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 17:42:07', '2026-01-09 13:08:10', 463),
(165, 10, '2026-01-08', '2', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-09 09:05:51', 'Arief Hidayat', '2026-01-09 13:08:05', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 20:06:32', '2026-01-09 13:08:05', 504),
(166, 10, '2026-01-08', '2', NULL, 400, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-09 09:05:41', 'Arief Hidayat', '2026-01-09 13:07:59', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-08 22:32:10', '2026-01-09 13:07:59', 500),
(167, 2, '2026-01-08', '3', NULL, 160, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-09 09:05:36', 'Arief Hidayat', '2026-01-09 13:07:53', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-09 00:01:25', '2026-01-09 13:07:53', 537),
(168, 10, '2026-01-08', '3', NULL, 400, 50, 50, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-09 09:06:23', 'Arief Hidayat', '2026-01-09 13:07:31', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-09 01:00:35', '2026-01-09 13:07:31', 1103),
(169, 10, '2026-01-08', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-09 09:06:09', 'Arief Hidayat', '2026-01-09 13:07:26', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-09 01:10:15', '2026-01-09 13:07:26', 558),
(170, 2, '2026-01-08', '3', NULL, 160, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-09 09:05:45', 'Arief Hidayat', '2026-01-09 13:07:20', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-09 01:21:29', '2026-01-09 13:07:20', 633),
(171, 3, '2026-01-08', '3', NULL, 150, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-09 09:05:57', 'Arief Hidayat', '2026-01-09 13:07:11', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-09 01:36:29', '2026-01-09 13:07:11', 804),
(172, 2, '2026-01-08', '3', NULL, 80, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-09 09:06:30', 'Arief Hidayat', '2026-01-09 13:07:05', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-09 01:46:44', '2026-01-09 13:07:05', 363),
(173, 10, '2026-01-08', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-09 09:06:18', 'Arief Hidayat', '2026-01-09 13:06:57', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-09 05:40:02', '2026-01-09 13:06:57', 495),
(174, 10, '2026-01-08', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-09 09:05:24', 'Arief Hidayat', '2026-01-09 13:06:27', NULL, NULL, NULL, NULL, 'Approved', NULL, NULL, NULL, '[]', '2026-01-09 06:20:51', '2026-01-09 13:06:27', 577),
(175, 10, '2026-01-09', '1', NULL, 400, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 15:30:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 10:05:16', '2026-01-09 15:30:33', 526),
(176, 2, '2026-01-09', '1', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 15:30:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 10:13:54', '2026-01-09 15:30:23', 382),
(177, 10, '2026-01-09', '1', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 15:30:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 10:18:29', '2026-01-09 15:30:16', 195),
(178, 2, '2026-01-09', '1', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 15:30:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 13:50:56', '2026-01-09 15:30:08', 445),
(179, 10, '2026-01-09', '1', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 15:29:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 13:55:41', '2026-01-09 15:29:58', 189),
(180, 10, '2026-01-09', '1', NULL, 400, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 15:29:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 14:45:04', '2026-01-09 15:29:27', 372),
(181, 10, '2026-01-09', '1', NULL, 300, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-09 15:29:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 14:54:42', '2026-01-09 15:29:03', 272),
(184, 10, '2026-01-09', '2', NULL, 400, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-10 09:55:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 19:50:43', '2026-01-10 09:55:41', 429),
(185, 10, '2026-01-09', '2', NULL, 400, 50, 50, 0, 'OK', 'DS', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 19:59:45', '2026-01-09 19:59:45', 429),
(191, 10, '2026-01-09', '2', NULL, 500, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-10 09:55:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 22:33:10', '2026-01-10 09:55:30', 450),
(192, 10, '2026-01-09', '2', NULL, 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-10 09:54:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-09 22:53:21', '2026-01-10 09:54:10', 285),
(193, 2, '2026-01-09', '3', NULL, 160, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-10 09:53:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 00:12:53', '2026-01-10 09:53:25', 547),
(194, 2, '2026-01-09', '3', NULL, 160, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-10 09:53:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 00:42:18', '2026-01-10 09:53:53', 553),
(195, 10, '2026-01-09', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-10 09:54:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 01:07:59', '2026-01-10 09:54:17', 518),
(196, 10, '2026-01-09', '3', NULL, 400, 50, 50, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-10 09:53:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 01:27:47', '2026-01-10 09:53:59', 1162),
(197, 2, '2026-01-09', '3', NULL, 80, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-10 09:53:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 01:34:51', '2026-01-10 09:53:20', 390),
(198, 1, '2026-01-09', '3', NULL, 150, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-10 09:54:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 05:28:55', '2026-01-10 09:54:36', 423),
(199, 10, '2026-01-09', '3', NULL, 400, 50, 50, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-10 09:53:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 06:44:52', '2026-01-10 09:53:13', 1308),
(200, 2, '2026-01-10', '1', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-10 09:54:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 09:40:05', '2026-01-10 09:54:27', 488),
(201, 10, '2026-01-10', '1', NULL, 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-10 09:52:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 09:44:54', '2026-01-10 09:52:52', 262),
(202, 10, '2026-01-10', '1', NULL, 300, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-12 08:15:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 10:21:54', '2026-01-12 08:15:51', 482),
(203, 10, '2026-01-10', '1', NULL, 450, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-12 08:15:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 11:00:37', '2026-01-12 08:15:56', 694),
(204, 10, '2026-01-10', '1', NULL, 300, 50, 50, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-12 08:16:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 11:35:30', '2026-01-12 08:16:00', 481),
(205, 10, '2026-01-10', '1', NULL, 250, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-12 08:15:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 11:45:02', '2026-01-12 08:15:41', 211),
(206, 2, '2026-01-10', '1', NULL, 100, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-12 08:15:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 11:52:18', '2026-01-12 08:15:37', 255),
(207, 10, '2026-01-10', '2', NULL, 500, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 08:15:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 15:43:08', '2026-01-12 08:15:27', 594),
(208, 10, '2026-01-10', '2', NULL, 500, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 08:15:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 15:56:17', '2026-01-12 08:15:23', 599),
(209, 2, '2026-01-10', '2', NULL, 300, 50, 50, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 08:15:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 16:29:13', '2026-01-12 08:15:19', 1002),
(210, 2, '2026-01-10', '3', NULL, 160, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-12 08:15:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 18:14:42', '2026-01-12 08:15:14', 617),
(211, 10, '2026-01-10', '3', NULL, 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-12 08:15:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 18:25:54', '2026-01-12 08:15:09', 626),
(212, 10, '2026-01-10', '3', NULL, 500, 50, 50, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-12 08:15:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 20:15:18', '2026-01-12 08:15:04', 937),
(213, 2, '2026-01-10', '3', NULL, 140, 20, 20, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-12 08:14:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 20:21:30', '2026-01-12 08:14:59', 310),
(214, 10, '2026-01-10', '3', NULL, 300, 50, 50, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-12 08:14:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-10 21:32:13', '2026-01-12 08:14:54', 1399),
(217, 2, '2026-01-12', '1', '9', 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:07:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 09:20:58', '2026-01-12 15:07:49', 776),
(218, 10, '2026-01-12', '1', '10', 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:07:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 10:06:49', '2026-01-12 15:07:42', 417),
(219, 10, '2026-01-12', '1', '4', 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:07:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 10:15:18', '2026-01-12 15:07:37', 407),
(220, 10, '2026-01-12', '1', '8', 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:07:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 10:24:06', '2026-01-12 15:07:33', 417),
(221, 10, '2026-01-12', '1', '7', 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:07:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 13:35:03', '2026-01-12 15:07:26', 391),
(222, 3, '2026-01-12', '1', '1', 250, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:07:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 13:44:53', '2026-01-12 15:07:22', 453),
(223, 3, '2026-01-12', '1', '5', 250, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:07:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 13:55:12', '2026-01-12 15:07:16', 363),
(224, 10, '2026-01-12', '1', '8', 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:07:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 14:50:56', '2026-01-12 15:07:12', 263),
(225, 10, '2026-01-12', '1', '10', 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:07:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 14:55:37', '2026-01-12 15:07:09', 228),
(226, 10, '2026-01-12', '1', '4', 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:05:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 15:00:59', '2026-01-12 15:05:39', 261),
(227, 10, '2026-01-12', '1', '9', 200, 32, 32, 0, 'OK', 'DS', 'Ahmad Jaeni', '2026-01-12 15:07:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-12 15:05:44', '2026-01-12 15:07:04', 249),
(228, 10, '2026-01-12', '2', '7', 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-13 08:18:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SISAAN PART SHIFT 1', NULL, '[]', '2026-01-12 15:23:49', '2026-01-13 08:18:54', 266),
(229, 10, '2026-01-12', '2', '1', 200, 32, 32, 0, 'OK', 'GK', 'Ahmad Jaeni', '2026-01-13 08:19:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SISAAN PART SHIFT 1', NULL, '[]', '2026-01-12 15:36:53', '2026-01-13 08:19:07', 285),
(230, 2, '2026-01-12', '3', '7', 100, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:19:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 01:24:54', '2026-01-13 08:19:11', 376),
(231, 2, '2026-01-12', '3', '7', 100, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:19:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 01:32:54', '2026-01-13 08:19:15', 305),
(232, 10, '2026-01-12', '3', '12', 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:19:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 03:05:51', '2026-01-13 08:19:21', 295),
(233, 10, '2026-01-12', '3', '8', 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:19:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 03:11:44', '2026-01-13 08:19:27', 314),
(234, 10, '2026-01-12', '3', '2', 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:18:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 03:17:04', '2026-01-13 08:18:45', 254),
(235, 2, '2026-01-12', '3', '7', 100, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:18:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 05:45:24', '2026-01-13 08:18:40', 508),
(236, 10, '2026-01-12', '3', '4', 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:18:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 05:53:44', '2026-01-13 08:18:36', 408),
(237, 10, '2026-01-12', '3', '5', 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:18:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 05:59:33', '2026-01-13 08:18:31', 311),
(238, 10, '2026-01-12', '3', '9', 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:18:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 06:17:14', '2026-01-13 08:18:26', 325),
(239, 10, '2026-01-12', '3', '2', 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:18:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 06:39:41', '2026-01-13 08:18:22', 302),
(240, 2, '2026-01-12', '3', '7', 100, 20, 20, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:18:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 06:44:07', '2026-01-13 08:18:18', 231),
(241, 10, '2026-01-12', '3', '9', 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:18:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 06:48:48', '2026-01-13 08:18:15', 216),
(242, 10, '2026-01-12', '3', '4', 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:18:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 06:52:43', '2026-01-13 08:18:09', 194),
(243, 10, '2026-01-12', '3', '5', 200, 32, 32, 0, 'OK', 'AY', 'Ahmad Jaeni', '2026-01-13 08:18:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', '2026-01-13 06:56:18', '2026-01-13 08:18:04', 183);

-- --------------------------------------------------------

--
-- Struktur dari tabel `cross_cut_checksheets`
--

CREATE TABLE `cross_cut_checksheets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `operator_initials` varchar(255) DEFAULT NULL,
  `karu_qc` varchar(255) DEFAULT NULL,
  `karu_qc_approved_at` timestamp NULL DEFAULT NULL,
  `kashift_plating` varchar(255) DEFAULT NULL,
  `kashift_plating_approved_at` timestamp NULL DEFAULT NULL,
  `production_shift` varchar(255) NOT NULL,
  `qc_shift` varchar(255) NOT NULL,
  `production_datetime` datetime NOT NULL,
  `qc_datetime` datetime NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `chemical_copper` varchar(255) DEFAULT NULL,
  `chemical_nikel` varchar(255) DEFAULT NULL,
  `chemical_eching` varchar(255) DEFAULT NULL,
  `chemical_abu` varchar(255) DEFAULT NULL,
  `position_remark_judgment` enum('OK','NG') NOT NULL,
  `defects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`defects`)),
  `total_ng` int(11) NOT NULL DEFAULT 0,
  `sampling_qty` int(11) NOT NULL DEFAULT 0,
  `position_remark_no_lot` varchar(255) NOT NULL,
  `result_remark` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `next_proses` varchar(50) DEFAULT NULL,
  `cycle_time` int(11) DEFAULT NULL,
  `approval_status` varchar(255) DEFAULT NULL,
  `rejection_remarks` text DEFAULT NULL,
  `kashift_qc` varchar(255) DEFAULT NULL,
  `kashift_approved_at` timestamp NULL DEFAULT NULL,
  `supervisor_qc` varchar(255) DEFAULT NULL,
  `supervisor_approved_at` timestamp NULL DEFAULT NULL,
  `supervisor_plating` varchar(255) DEFAULT NULL,
  `supervisor_plating_approved_at` timestamp NULL DEFAULT NULL,
  `asst_manager_qc` varchar(255) DEFAULT NULL,
  `asst_manager_approved_at` timestamp NULL DEFAULT NULL,
  `manager_qc` varchar(255) DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `manager_plating` varchar(255) DEFAULT NULL,
  `manager_plating_approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cross_cut_checksheets`
--

INSERT INTO `cross_cut_checksheets` (`id`, `item_id`, `operator_initials`, `karu_qc`, `karu_qc_approved_at`, `kashift_plating`, `kashift_plating_approved_at`, `production_shift`, `qc_shift`, `production_datetime`, `qc_datetime`, `image_path`, `chemical_copper`, `chemical_nikel`, `chemical_eching`, `chemical_abu`, `position_remark_judgment`, `defects`, `total_ng`, `sampling_qty`, `position_remark_no_lot`, `result_remark`, `keterangan`, `next_proses`, `cycle_time`, `approval_status`, `rejection_remarks`, `kashift_qc`, `kashift_approved_at`, `supervisor_qc`, `supervisor_approved_at`, `supervisor_plating`, `supervisor_plating_approved_at`, `asst_manager_qc`, `asst_manager_approved_at`, `manager_qc`, `manager_approved_at`, `manager_plating`, `manager_plating_approved_at`, `created_at`, `updated_at`) VALUES
(3, 16, 'TK', NULL, NULL, NULL, NULL, '1', '1', '2026-01-06 13:18:00', '2026-01-07 09:50:00', 'cross_cut_images/Yl95B7adgSobGWXxRmcqq8lUVRE6qJN3GZqf2rCz.jpg', '2', '5', '2', '1', 'OK', NULL, 0, 0, 'A06TK26E', 'TK2', NULL, NULL, 332, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 09:54:05', '2026-01-07 09:54:05'),
(4, 17, 'MI', NULL, NULL, NULL, NULL, '3', '1', '2026-01-06 09:52:00', '2026-01-07 11:33:00', 'cross_cut_images/aGfqfhQ9a4Q8yYRTy7TpwH6vbqjhoLmYTEVTahRf.jpg', '7', '3', '2', '2', 'OK', NULL, 0, 0, 'A06MI26A', 'MI2', NULL, NULL, 219, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 11:36:36', '2026-01-07 11:36:36'),
(5, 18, 'AE', NULL, NULL, NULL, NULL, '1', '1', '2026-01-07 11:02:00', '2026-01-07 11:47:00', 'cross_cut_images/5nZtLmBY7AsWmmZWcd8t1J2Ci6euGQSCvCYv7KFF.jpg', '8', '4', '1', '1', 'OK', NULL, 0, 0, 'A07AE26A', 'AE1', NULL, NULL, 231, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 11:49:08', '2026-01-08 09:06:20'),
(6, 19, 'BA', 'Parlinah', '2026-01-08 09:43:59', NULL, NULL, '3', '1', '2026-01-06 02:43:00', '2026-01-08 09:38:00', 'cross_cut_images/C39YJCqR1I1lX8ORoKQYEtp89z7iGoYhfcy7DV30.jpg', '6', '4', '1', '1', 'OK', NULL, 0, 0, 'A06BA26B', 'BA1', NULL, NULL, 228, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 09:40:00', '2026-01-08 09:43:59'),
(7, 4, 'SW', NULL, NULL, NULL, NULL, '3', '1', '2026-01-07 07:59:00', '2026-01-08 09:50:00', 'cross_cut_images/b1i9MWOBHb8V1EMObKr2Cak21xEKm167ezavmOBU.jpg', '6', '4', '1', '1', 'OK', NULL, 0, 0, 'A07SW26A', 'SW2', NULL, NULL, 254, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 09:52:19', '2026-01-08 09:52:19'),
(9, 21, 'ND', NULL, NULL, NULL, NULL, '3', '1', '2026-01-07 10:09:00', '2026-01-08 10:09:00', 'cross_cut_images/GV7YtONy60xywnjg9DebCZV0wl4Ox8jPKjQjpYyJ.jpg', '8', '4', '1', '1', 'OK', NULL, 0, 0, 'A07ND26C', 'ND2', NULL, NULL, 338, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 10:12:09', '2026-01-08 10:12:09'),
(12, 22, 'AE', NULL, NULL, NULL, NULL, '3', '1', '2026-01-07 03:05:00', '2026-01-08 10:28:00', 'cross_cut_images/hahwqQAEvSLUfMKdZsF8GnACCmhYlxdwlmetPu00.jpg', '8', '4', '1', '1', 'OK', NULL, 0, 0, 'A07AE26A', 'AE', NULL, NULL, 254, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 10:34:12', '2026-01-09 20:30:35'),
(14, 52, 'IA', NULL, NULL, NULL, NULL, '1', '1', '2026-01-06 10:30:00', '2026-01-13 10:30:00', 'cross_cut_images/umVOJg0Hu3FX6Ll32cbKFstWXrTcpE3YfMyvqiF8.jpg', 'COPPER', 'NIKEL', 'ECHING', 'ABU', 'OK', NULL, 0, 0, '123', NULL, NULL, NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 10:31:52', '2026-01-13 10:31:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `in_process_checksheets`
--

CREATE TABLE `in_process_checksheets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `shift` varchar(255) NOT NULL,
  `code_machine` varchar(255) DEFAULT NULL,
  `total_qty` int(11) NOT NULL,
  `sampling_qty` int(11) NOT NULL,
  `total_ok` int(11) NOT NULL,
  `total_ng` int(11) NOT NULL,
  `judgment` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `next_proses` varchar(50) DEFAULT NULL,
  `dimension_check` text DEFAULT NULL,
  `defects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`defects`)),
  `operator_initials` varchar(255) DEFAULT NULL,
  `cycle_time` int(11) DEFAULT NULL,
  `approval_status` varchar(255) DEFAULT NULL,
  `rejection_remarks` text DEFAULT NULL,
  `kashift_qc` varchar(255) DEFAULT NULL,
  `kashift_approved_at` timestamp NULL DEFAULT NULL,
  `supervisor_qc` varchar(255) DEFAULT NULL,
  `supervisor_approved_at` timestamp NULL DEFAULT NULL,
  `asst_manager_qc` varchar(255) DEFAULT NULL,
  `manager_qc` varchar(255) DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `asst_manager_approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `in_process_checksheets`
--

INSERT INTO `in_process_checksheets` (`id`, `item_id`, `date`, `shift`, `code_machine`, `total_qty`, `sampling_qty`, `total_ok`, `total_ng`, `judgment`, `remarks`, `next_proses`, `dimension_check`, `defects`, `operator_initials`, `cycle_time`, `approval_status`, `rejection_remarks`, `kashift_qc`, `kashift_approved_at`, `supervisor_qc`, `supervisor_approved_at`, `asst_manager_qc`, `manager_qc`, `manager_approved_at`, `asst_manager_approved_at`, `created_at`, `updated_at`) VALUES
(7, 4, '2025-12-25', '1', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"24.97\",\"2\":\"21\",\"3\":\"3.18\"}}', '[]', 'YS', 407, NULL, NULL, 'Ahmad Jaeni', '2026-01-06 10:01:09', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-25 07:50:29', '2026-01-06 10:01:09'),
(8, 6, '2025-12-26', '1', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.88\",\"3\":\"9.80\",\"4\":\"9.85\"}}', '[]', 'DA', 555, NULL, NULL, 'Ahmad Jaeni', '2026-01-06 10:01:04', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-26 06:19:39', '2026-01-06 10:01:04'),
(9, 6, '2025-12-26', '2', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.89\",\"3\":\"9.90\",\"4\":\"9.89\"}}', '[]', 'SH', 115, 'Pending', NULL, 'Ahmad Jaeni', '2026-01-06 10:01:00', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-26 15:29:52', '2026-01-06 10:01:00'),
(10, 6, '2026-01-05', '1', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.95\",\"4\":\"9.90\"}}', '[]', 'DA', 220, NULL, NULL, 'Ahmad Jaeni', '2026-01-06 10:00:56', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 10:38:29', '2026-01-06 10:00:56'),
(11, 4, '2026-01-05', '1', NULL, 880, 80, 80, 0, 'NG', NULL, NULL, '{\"1\":{\"1\":\"25.10\",\"2\":\"3.21\",\"3\":\"24.31\"}}', '[]', 'DA', 267, NULL, NULL, 'Ahmad Jaeni', '2026-01-06 10:00:49', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 13:46:00', '2026-01-06 10:00:49'),
(12, 6, '2026-01-05', '1', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.95\",\"4\":\"9.89\"}}', '[]', 'YS', 104, NULL, NULL, 'Ahmad Jaeni', '2026-01-06 10:00:36', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 21:16:34', '2026-01-06 10:00:36'),
(13, 4, '2026-01-05', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.01\",\"3\":\"3.16\",\"4\":\"24.01\"},\"2\":{\"1\":\"25.06\",\"3\":\"3.17\",\"4\":\"24.09\"}}', '[]', 'YS', 53, NULL, NULL, 'Ahmad Jaeni', '2026-01-06 10:00:32', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-05 21:22:47', '2026-01-06 10:00:32'),
(14, 6, '2026-01-05', '3', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.90\",\"3\":\"9.89\",\"4\":\"9.89\"}}', '[]', 'SH', 480, NULL, NULL, 'Ahmad Jaeni', '2026-01-06 10:00:30', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 03:03:00', '2026-01-06 10:00:30'),
(15, 4, '2026-01-06', '1', NULL, 880, 80, 80, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.12\",\"2\":\"21.00\",\"3\":\"3.18\",\"4\":\"24.11\"}}', '[]', 'SH', 168, 'Pending', NULL, 'Ahmad Jaeni', '2026-01-06 10:00:28', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 03:08:40', '2026-01-06 10:00:28'),
(17, 6, '2026-01-06', '1', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.96\",\"4\":\"9.93\"}}', '[]', 'DA', 242, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:16:21', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 14:04:06', '2026-01-08 07:16:21'),
(18, 4, '2026-01-06', '1', NULL, 880, 80, 80, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"21.02\",\"3\":\"3.19\",\"4\":\"24.11\"}}', '[]', 'DA', 210, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:16:25', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 14:15:40', '2026-01-08 07:16:25'),
(20, 10, '2026-01-06', '2', NULL, 4, 4, 4, 0, 'OK', NULL, NULL, '[]', '[]', 'YS', 92, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:16:29', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 19:07:04', '2026-01-08 07:16:29'),
(21, 6, '2026-01-06', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.83\",\"2\":\"9.84\",\"3\":\"9.82\",\"4\":\"9.86\"}}', '[]', 'YS', 100, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:16:40', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 19:10:42', '2026-01-08 07:16:40'),
(22, 4, '2026-01-06', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"24.97\",\"3\":\"3.12\",\"4\":\"24.01\"},\"2\":{\"1\":\"25.00\",\"3\":\"3.10\",\"4\":\"24.09\"}}', '[]', 'YS', 86, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:16:45', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 19:21:02', '2026-01-08 07:16:45'),
(23, 6, '2026-01-06', '3', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.90\",\"3\":\"9.89\",\"4\":\"9.89\"}}', '[]', 'SH', 488, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:16:50', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 01:29:20', '2026-01-08 07:16:50'),
(24, 4, '2026-01-06', '3', NULL, 1600, 125, 125, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.16\",\"3\":\"3.18\",\"4\":\"24.36\"}}', '[]', 'SH', 393, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:15:27', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 03:00:33', '2026-01-08 07:15:27'),
(25, 6, '2026-01-06', '3', NULL, 300, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.89\",\"2\":\"9.91\",\"3\":\"9.90\",\"4\":\"9.89\"}}', '[]', 'SH', 391, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:15:31', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 06:23:49', '2026-01-08 07:15:31'),
(26, 4, '2026-01-07', '1', NULL, 720, 80, 80, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.05\",\"3\":\"3.18\",\"4\":\"24.12\"}}', '[]', 'DA', 461, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:15:36', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 10:17:07', '2026-01-08 07:15:36'),
(27, 6, '2026-01-07', '1', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.89\",\"3\":\"9.95\",\"4\":\"9.90\"}}', '[]', 'DA', 219, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:15:40', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 10:26:48', '2026-01-08 07:15:40'),
(28, 10, '2026-01-07', '1', NULL, 400, 50, 50, 0, 'OK', NULL, NULL, '[]', '[]', 'DA', 207, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:15:44', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 13:47:38', '2026-01-08 07:15:44'),
(29, 6, '2026-01-07', '1', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.92\",\"3\":\"9.91\",\"4\":\"9.90\"}}', '[]', 'DA', 276, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:15:48', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 13:54:44', '2026-01-08 07:15:48'),
(30, 6, '2026-01-07', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.90\",\"3\":\"9.95\",\"4\":\"9.90\"}}', '[]', 'YS', 76, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:15:51', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 21:08:47', '2026-01-08 07:15:51'),
(31, 6, '2026-01-07', '3', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.89\",\"3\":\"9.89\",\"4\":\"9.86\"}}', '[]', 'SH', 85, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:15:57', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 23:47:01', '2026-01-08 07:15:57'),
(32, 6, '2026-01-07', '3', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.89\",\"3\":\"9.89\",\"4\":\"9.86\"}}', '[]', 'SH', 276, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:16:01', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 02:35:33', '2026-01-08 07:16:01'),
(33, 6, '2026-01-07', '3', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.88\",\"3\":\"9.90\",\"4\":\"9.86\"}}', '[]', 'SH', 394, NULL, NULL, 'Ahmad Jaeni', '2026-01-08 07:15:23', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 06:02:37', '2026-01-08 07:15:23'),
(34, 6, '2026-01-08', '1', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.95\",\"4\":\"9.90\"}}', '[]', 'DA', 325, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:12:11', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 10:48:10', '2026-01-09 09:12:11'),
(35, 10, '2026-01-08', '1', NULL, 500, 50, 50, 0, 'OK', NULL, NULL, '[]', '[]', 'DA', 198, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:12:21', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 10:55:45', '2026-01-09 09:12:21'),
(36, 2, '2026-01-08', '1', NULL, 100, 20, 10, 10, 'NG', 'Finishing ulang', NULL, '[]', '[{\"type\":\"FLASH\",\"qty\":5},{\"type\":\"FLASH\",\"qty\":5}]', 'DA', 211, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:08:55', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 12:56:45', '2026-01-09 09:08:55'),
(37, 10, '2026-01-08', '2', NULL, 4, 4, 4, 0, 'OK', NULL, NULL, '[]', '[]', 'YS', 88, 'Pending', NULL, 'Ahmad Jaeni', '2026-01-09 09:09:27', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 19:04:10', '2026-01-09 14:08:28'),
(38, 6, '2026-01-08', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.89\",\"4\":\"9.90\"}}', '[]', 'YS', 140, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:10:28', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 19:06:55', '2026-01-09 09:10:28'),
(39, 2, '2026-01-08', '2', NULL, 2, 2, 2, 0, 'OK', NULL, NULL, '[]', '[]', 'YS', 123, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:08:49', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 19:09:54', '2026-01-09 09:08:49'),
(40, 13, '2026-01-08', '2', NULL, 2, 2, 2, 0, 'OK', 'Poin Check dimensi tolong di revisi', NULL, '{\"1\":{\"1\":\"8.1\",\"2\":\"8.6\",\"3\":\"8.1\",\"4\":\"5.6\",\"5\":\"6.6\",\"6\":\"6.1\"}}', '[]', 'YS', 269, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:08:42', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 19:16:45', '2026-01-09 09:08:42'),
(41, 12, '2026-01-08', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2\",\"5\":\"4..3\",\"6\":\"3\"}}', '[]', 'YS', 181, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:09:04', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 19:24:24', '2026-01-09 09:09:04'),
(42, 19, '2026-01-08', '3', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.89\",\"3\":\"9.89\",\"4\":\"9.88\"}}', '[]', 'SH', 252, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:09:12', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 01:21:07', '2026-01-09 09:09:12'),
(43, 2, '2026-01-08', '3', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '[]', '[]', 'SH', 72, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:11:40', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 01:26:25', '2026-01-09 09:11:40'),
(44, 6, '2026-01-08', '3', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.95\",\"4\":\"9.89\"}}', '[]', 'SH', 250, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:09:20', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 06:18:46', '2026-01-09 09:09:20'),
(45, 2, '2026-01-09', '1', NULL, 100, 20, 20, 0, 'OK', NULL, NULL, '[]', '[]', 'DA', 196, NULL, NULL, 'Ahmad Jaeni', '2026-01-09 09:08:36', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 08:43:39', '2026-01-09 09:08:36'),
(46, 2, '2026-01-09', '1', NULL, 100, 20, 20, 0, 'OK', NULL, NULL, '[]', '[]', 'DA', 210, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:17:35', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 10:16:00', '2026-01-12 08:17:35'),
(47, 6, '2026-01-09', '1', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.95\",\"4\":\"9.90\"}}', '[]', 'DA', 198, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:17:40', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 13:38:50', '2026-01-12 08:17:40'),
(50, 6, '2026-01-09', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.89\",\"4\":\"9.90\"}}', '[]', 'YS', 143, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:17:45', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 16:33:53', '2026-01-12 08:17:45'),
(51, 12, '2026-01-09', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.2\",\"3\":\"4.1\",\"4\":\"2.1\",\"5\":\"4.2\",\"6\":\"3.1\"}}', '[]', 'YS', 201, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:17:50', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 17:00:22', '2026-01-12 08:17:50'),
(52, 12, '2026-01-09', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2.1\",\"5\":\"4.1\",\"6\":\"3.1\"}}', '[]', 'YS', 203, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:17:57', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 19:54:28', '2026-01-12 08:17:57'),
(53, 6, '2026-01-09', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.90\",\"4\":\"9.90\"}}', '[]', 'YS', 161, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:18:02', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-09 20:05:41', '2026-01-12 08:18:02'),
(54, 12, '2026-01-09', '3', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.11\",\"4\":\"2.08\",\"5\":\"4.10\",\"6\":\"3.10\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.13\",\"4\":\"2.09\",\"5\":\"4.11\",\"6\":\"3.11\"}}', '[]', 'SH', 540, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:16:18', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 01:36:00', '2026-01-12 08:16:18'),
(55, 6, '2026-01-09', '3', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.90\",\"4\":\"9.89\"}}', '[]', 'SH', 312, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:16:27', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 02:43:30', '2026-01-12 08:16:27'),
(56, 6, '2026-01-09', '3', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.89\",\"3\":\"9.90\",\"4\":\"9.89\"}}', '[]', 'SH', 306, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:16:35', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 05:53:24', '2026-01-12 08:16:35'),
(57, 12, '2026-01-10', '1', NULL, 100, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.08\",\"2\":\"7.11\",\"3\":\"4.13\",\"4\":\"2.08\",\"5\":\"4.10\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.10\",\"3\":\"4.11\",\"4\":\"2.08\",\"5\":\"4.10\",\"6\":\"3.08\"}}', '[]', 'DA', 294, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:16:42', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 08:47:04', '2026-01-12 08:16:42'),
(58, 6, '2026-01-10', '1', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.95\",\"3\":\"9.93\",\"4\":\"9.90\"}}', '[]', 'DA', 235, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:16:47', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 09:45:34', '2026-01-12 08:16:47'),
(59, 6, '2026-01-10', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.89\",\"4\":\"9.90\"}}', '[]', 'YS', 97, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:16:56', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 13:21:19', '2026-01-12 08:16:56'),
(60, 12, '2026-01-10', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.2\",\"3\":\"4.1\",\"4\":\"2\",\"5\":\"4..2\",\"6\":\"3.1\"}}', '[]', 'YS', 123, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:17:02', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 13:29:34', '2026-01-12 08:17:02'),
(61, 4, '2026-01-10', '2', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25\",\"2\":\"21\",\"3\":\"3.2\",\"4\":\"24.1\"},\"2\":{\"1\":\"25\",\"2\":\"21.1\",\"3\":\"3.3\",\"4\":\"24.1\"}}', '[]', 'YS', 111, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:17:11', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 13:36:30', '2026-01-12 08:17:11'),
(62, 4, '2026-01-10', '3', NULL, 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"21.11\",\"3\":\"3.15\",\"4\":\"24.11\"},\"2\":{\"1\":\"25.06\",\"2\":\"21.1p\",\"3\":\"3.10\",\"4\":\"24.09\"}}', '[]', 'SH', 192, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:17:18', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 18:59:27', '2026-01-12 08:17:18'),
(63, 6, '2026-01-10', '3', NULL, 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.89\",\"3\":\"9.90\",\"4\":\"9.89\"}}', '[]', 'SH', 336, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 08:17:25', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 20:11:07', '2026-01-12 08:17:25'),
(65, 6, '2026-01-12', '1', '15', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.89\",\"4\":\"9.90\"}}', '[]', 'YS', 95, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 15:06:11', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 08:46:05', '2026-01-12 15:06:11'),
(66, 12, '2026-01-12', '1', '1', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2\",\"5\":\"4.2\",\"6\":\"3.1\"}}', '[]', 'YS', 138, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 15:06:16', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 09:02:16', '2026-01-12 15:06:16'),
(67, 4, '2026-01-12', '1', '18', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25\",\"2\":\"21\",\"3\":\"3.12\",\"4\":\"24.01\"},\"2\":{\"1\":\"25.06\",\"2\":\"21.1\",\"3\":\"3.10\",\"4\":\"24.1\"}}', '[]', 'YS', 124, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 15:06:27', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 09:17:23', '2026-01-12 15:06:27'),
(68, 6, '2026-01-12', '1', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.90\",\"4\":\"9.90\"}}', '[]', 'YS', 227, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 15:06:22', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 12:56:02', '2026-01-12 15:06:22'),
(69, 12, '2026-01-12', '1', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2\",\"5\":\"4.2\",\"6\":\"3\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.13\",\"3\":\"4.15\",\"4\":\"2.09\",\"5\":\"4.1\",\"6\":\"3.11\"}}', '[]', 'YS', 320, NULL, NULL, 'Ahmad Jaeni', '2026-01-12 15:06:06', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 14:30:08', '2026-01-12 15:06:06'),
(70, 6, '2026-01-12', '2', '15', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.89\",\"3\":\"9.90\",\"4\":\"9.89\"}}', '[]', 'SH', 84, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:23:17', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 16:58:34', '2026-01-13 08:23:17'),
(71, 4, '2026-01-12', '2', '18', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.09\",\"3\":\"3.13\",\"4\":\"24.11\"},\"2\":{\"1\":\"25.06\",\"3\":\"3.17\",\"4\":\"24.09\"}}', '[]', 'SH', 166, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:23:27', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 17:05:43', '2026-01-13 08:23:27'),
(72, 12, '2026-01-12', '2', '1', 300, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.13\",\"3\":\"4.12\",\"4\":\"2.10\",\"5\":\"4.10\",\"6\":\"3.10\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.10\",\"3\":\"4.11\",\"4\":\"2.09\",\"5\":\"4.11\",\"6\":\"3.08\"}}', '[]', 'SH', 536, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:23:23', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 19:48:02', '2026-01-13 08:23:23'),
(73, 6, '2026-01-12', '2', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.90\",\"3\":\"9.89\",\"4\":\"9.90\"}}', '[]', 'SH', 378, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:21:47', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 20:43:13', '2026-01-13 08:21:47'),
(74, 12, '2026-01-12', '2', '1', 300, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.10\",\"3\":\"4.11\",\"4\":\"2.09\",\"5\":\"4.10\",\"6\":\"3.11\"}}', '[]', 'SH', 589, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:21:52', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 21:42:58', '2026-01-13 08:21:52'),
(75, 4, '2026-01-12', '2', '18', 880, 80, 80, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"3\":\"3.18\",\"4\":\"24.01\"}}', '[]', 'SH', 643, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:22:09', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 22:20:43', '2026-01-13 08:22:09'),
(76, 12, '2026-01-12', '3', '1', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.11\",\"3\":\"4.10\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.12\",\"3\":\"4.10\",\"4\":\"2.09\",\"5\":\"4.20\",\"6\":\"3.10\"}}', '[]', 'DA', 74, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:22:15', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-12 23:58:49', '2026-01-13 08:22:15'),
(77, 4, '2026-01-12', '3', '18', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"3\":{\"1\":\"25.13\",\"3\":\"3.18\",\"4\":\"24.00\"},\"4\":{\"1\":\"25.11\",\"3\":\"3.16\",\"4\":\"24.02\"}}', '[]', 'DA', 163, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:22:20', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 00:27:33', '2026-01-13 08:22:20'),
(78, 12, '2026-01-12', '3', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.09\",\"5\":\"4.20\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.10\",\"3\":\"4.12\",\"4\":\"2.10\",\"5\":\"4.18\"}}', '[]', 'DA', 707, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:22:37', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 00:48:18', '2026-01-13 08:22:37'),
(79, 6, '2026-01-12', '3', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.95\",\"3\":\"9.98\",\"4\":\"9.93\"}}', '[]', 'DA', 280, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:22:44', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 01:54:31', '2026-01-13 08:22:44'),
(80, 23, '2026-01-12', '3', '7', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"5\":\"4.0\",\"6\":\"4.05\",\"7\":\"7.05\",\"8\":\"8.02\"}}', '[]', 'DA', 245, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:22:50', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 02:12:28', '2026-01-13 08:22:50'),
(81, 12, '2026-01-12', '3', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.12\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.13\",\"3\":\"4.11\",\"4\":\"2.10\",\"5\":\"4.20\",\"6\":\"3.09\"}}', '[]', 'DA', 562, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:22:56', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 02:25:03', '2026-01-13 08:22:56'),
(82, 12, '2026-01-12', '3', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.09\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.13\",\"3\":\"4.11\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.10\"}}', '[]', 'DA', 521, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:23:03', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 05:15:49', '2026-01-13 08:23:03'),
(83, 6, '2026-01-12', '3', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.95\",\"4\":\"9.90\"}}', '[]', 'DA', 350, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:21:15', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 05:53:59', '2026-01-13 08:21:15'),
(84, 26, '2026-01-12', '3', '6', 500, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"2\":\"2.10\",\"3\":\"2.08\",\"4\":\"2.09\"}}', '[]', 'DA', 308, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:20:46', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 06:20:35', '2026-01-13 08:20:46'),
(85, 12, '2026-01-12', '3', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.11\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.11\",\"2\":\"7.13\",\"3\":\"4.11\",\"4\":\"2.10\",\"5\":\"4.20\",\"6\":\"3.09\"}}', '[]', 'DA', 470, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:20:36', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 06:44:15', '2026-01-13 08:20:36'),
(86, 12, '2026-01-13', '1', '1', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\"},\"2\":{\"1\":\"2.10\"}}', '[]', 'DA', 3, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:20:01', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 06:57:56', '2026-01-13 08:20:01'),
(87, 12, '2026-01-13', '1', '1', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2.08\",\"5\":\"4.2\",\"6\":\"3.1\"},\"2\":{\"1\":\"2.11\",\"2\":\"7.12\",\"3\":\"4.11\",\"4\":\"2.10\",\"5\":\"4.20\",\"6\":\"3.08\"}}', '[]', 'YS', 237, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:20:06', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 07:29:05', '2026-01-13 08:20:06'),
(88, 26, '2026-01-13', '1', '6', 4, 4, 4, 0, 'OK', 'UKUR DIMENSI DI PERBARUI', NULL, '{\"1\":{\"2\":\"2.1\"},\"2\":{\"2\":\"2.1\"}}', '[]', 'YS', 131, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:20:11', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 07:35:22', '2026-01-13 08:20:11'),
(89, 23, '2026-01-13', '1', '7', 4, 4, 4, 0, 'OK', 'POINT CHECK DIMENSI DI PERBARUI', NULL, '{\"1\":{\"1\":\"162\"}}', '[]', 'YS', 128, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:20:17', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 07:39:31', '2026-01-13 08:20:17'),
(90, 23, '2026-01-13', '1', '7', 4, 4, 4, 0, 'OK', 'POINT CHECK DIMENSI DI PERBARUI', NULL, '{\"1\":{\"1\":\"162\"}}', '[]', 'YS', 128, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:19:57', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 07:39:32', '2026-01-13 08:19:57'),
(91, 6, '2026-01-13', '1', '15', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.89\",\"4\":\"9.90\"}}', '[]', 'YS', 168, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:19:51', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 07:47:09', '2026-01-13 08:19:51'),
(92, 4, '2026-01-13', '1', '18', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"21\",\"3\":\"3.18\",\"4\":\"24.11\"},\"2\":{\"1\":\"25.06\",\"2\":\"21.1\",\"3\":\"3.17\",\"4\":\"24.09\"}}', '[]', 'YS', 185, NULL, NULL, 'Ahmad Jaeni', '2026-01-13 08:19:44', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 08:02:52', '2026-01-13 08:19:44'),
(93, 12, '2026-01-13', '1', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2.08\",\"5\":\"4.2\",\"6\":\"3\"},\"2\":{\"1\":\"2.11\",\"2\":\"7.12\",\"3\":\"4.10\",\"4\":\"2.08\",\"5\":\"4.18\",\"6\":\"3.10\"}}', '[]', 'YS', 210, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 09:29:35', '2026-01-13 09:29:35'),
(95, 12, '2026-01-13', '1', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2.08\",\"5\":\"4.2\",\"6\":\"3\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.10\",\"3\":\"4.10\",\"4\":\"2.10\",\"5\":\"4.18\",\"6\":\"3.10\"}}', '[]', 'YS', 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 10:47:49', '2026-01-13 10:47:49'),
(96, 26, '2026-01-13', '1', '6', 4, 4, 4, 0, 'OK', NULL, NULL, '{\"1\":{\"2\":\"2\"},\"2\":{\"2\":\"2.1\"}}', '[]', 'YS', 98, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 12:22:56', '2026-01-13 12:22:56'),
(97, 6, '2026-01-13', '1', '14', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.90\",\"4\":\"9.90\"}}', '[]', 'YS', 186, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 12:33:36', '2026-01-13 12:33:36'),
(99, 23, '2026-01-13', '1', '7', 4, 4, 4, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"7.1\",\"2\":\"4.O7\",\"3\":\"8.02\",\"4\":\"2.10\"},\"2\":{\"1\":\"7.1\",\"2\":\"4.1\",\"3\":\"8.07\",\"4\":\"2.10\"}}', '[]', 'YS', 113, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 13:02:13', '2026-01-13 13:02:13'),
(100, 12, '2026-01-13', '1', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2.08\",\"5\":\"4.10\",\"6\":\"3.10\"},\"2\":{\"1\":\"2.11\",\"2\":\"7.12\",\"3\":\"4.10\",\"4\":\"2.09\",\"5\":\"4.10\",\"6\":\"3.11\"}}', '[]', 'YS', 324, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 13:11:55', '2026-01-13 13:11:55'),
(101, 4, '2026-01-13', '1', '18', 880, 80, 80, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25\",\"4\":\"24.11\"},\"2\":{\"1\":\"25.06\",\"4\":\"24.09\"}}', '[]', 'YS', 441, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 13:50:38', '2026-01-13 13:50:38'),
(102, 12, '2026-01-13', '1', '1', 140, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2.08\",\"5\":\"4.10\",\"6\":\"3.10\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.10\",\"3\":\"4.10\",\"4\":\"2.08\",\"5\":\"4.10\",\"6\":\"3.10\"}}', '[]', 'YS', 187, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 14:30:12', '2026-01-13 14:30:12'),
(103, 6, '2026-01-13', '1', '15', 50, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.90\",\"4\":\"9.90\"}}', '[]', 'YS', 189, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 14:33:43', '2026-01-13 14:33:43'),
(104, 4, '2026-01-13', '2', '18', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"3.22\",\"3\":\"24.12\"},\"2\":{\"1\":\"25.00\",\"2\":\"3.20\",\"3\":\"24.10\"}}', '[]', 'SH', 178, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 17:25:16', '2026-01-13 17:25:16'),
(105, 6, '2026-01-13', '2', '15', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.89\",\"3\":\"9.90\",\"4\":\"9.89\"}}', '[]', 'SH', 113, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 17:30:15', '2026-01-13 17:30:15'),
(106, 26, '2026-01-13', '2', '6', 4, 4, 4, 0, 'OK', NULL, NULL, '{\"1\":{\"3\":\"2.08\"},\"2\":{\"3\":\"2.10\"}}', '[]', 'SH', 103, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 17:34:08', '2026-01-13 17:34:08'),
(107, 12, '2026-01-13', '2', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.12\",\"2\":\"7.12\",\"3\":\"4.12\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.10\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.13\",\"3\":\"4.10\",\"4\":\"2.09\",\"5\":\"4.10\",\"6\":\"3.08\"}}', '[]', 'SH', 481, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 17:48:11', '2026-01-13 17:48:11'),
(108, 6, '2026-01-13', '2', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.90\",\"3\":\"9.89\",\"4\":\"9.89\"}}', '[]', 'SH', 468, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 19:29:49', '2026-01-13 19:29:49'),
(109, 4, '2026-01-13', '2', '18', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"3.22\",\"3\":\"24.30\"},\"2\":{\"1\":\"25.06\",\"2\":\"3.20\",\"3\":\"24.28\"}}', '[]', 'SH', 267, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 19:49:34', '2026-01-13 19:49:34'),
(110, 23, '2026-01-13', '2', '7', 4, 4, 4, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"7.10\",\"2\":\"4.05\",\"3\":\"8.05\",\"4\":\"2.08\"},\"2\":{\"1\":\"7.08\",\"2\":\"4.08\",\"3\":\"8.08\",\"4\":\"2.10\"}}', '[]', 'SH', 46, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 20:06:58', '2026-01-13 20:06:58'),
(111, 12, '2026-01-13', '2', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.20\",\"3\":\"4.13\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.10\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.12\",\"3\":\"4.15\",\"4\":\"2.10\",\"5\":\"4.18\",\"6\":\"3.11\"}}', '[]', 'SH', 482, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 20:27:00', '2026-01-13 20:27:00'),
(112, 26, '2026-01-13', '2', '6', 4, 4, 4, 0, 'OK', NULL, NULL, '{\"1\":{\"3\":\"2.1\"},\"2\":{\"3\":\"2.1\"}}', '[]', 'SH', 62, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 20:40:06', '2026-01-13 20:40:06'),
(113, 12, '2026-01-13', '2', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.20\",\"3\":\"4.12\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.10\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.12\",\"3\":\"4.10\",\"4\":\"2.09\",\"5\":\"4.18\",\"6\":\"3.11\"}}', '[]', 'SH', 547, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 22:02:09', '2026-01-13 22:02:09'),
(114, 6, '2026-01-13', '2', '15', 250, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.91\",\"2\":\"9.90\",\"3\":\"9.90\",\"4\":\"9.89\"}}', '[]', 'SH', 377, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 22:14:18', '2026-01-13 22:14:18'),
(115, 4, '2026-01-13', '3', '18', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"3\":{\"1\":\"25.09\",\"2\":\"3.15\",\"3\":\"24.13\"},\"4\":{\"1\":\"25.10\",\"2\":\"3.12\",\"3\":\"24.15\"}}', '[]', 'DA', 373, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 23:50:20', '2026-01-13 23:50:20'),
(116, 6, '2026-01-13', '3', '15', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.89\",\"2\":\"9.92\",\"3\":\"9.86\",\"4\":\"9.90\"}}', '[]', 'DA', 279, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-13 23:56:15', '2026-01-13 23:56:15'),
(117, 23, '2026-01-13', '3', '7', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"7.05\",\"2\":\"4.03\",\"3\":\"8.04\",\"4\":\"2.08\"},\"2\":{\"1\":\"7.08\",\"2\":\"4.04\",\"3\":\"8.05\",\"4\":\"2.08\"}}', '[]', 'DA', 188, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 00:09:34', '2026-01-14 00:09:34'),
(118, 12, '2026-01-13', '3', '1', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.09\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.08\",\"2\":\"7.10\",\"3\":\"4.12\",\"4\":\"2.09\",\"5\":\"4.20\",\"6\":\"3.08\"}}', '[]', 'DA', 314, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 00:15:20', '2026-01-14 00:15:20'),
(119, 26, '2026-01-13', '3', '6', 4, 4, 4, 0, 'OK', NULL, NULL, '{\"1\":{\"4\":\"2.1\"},\"2\":{\"4\":\"2.1\"}}', '[]', 'DA', 171, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 00:20:42', '2026-01-14 00:20:42'),
(121, 23, '2026-01-13', '3', '7', 160, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"7.05\",\"2\":\"4.03\",\"3\":\"8.04\",\"4\":\"2.08\"},\"2\":{\"1\":\"7.07\",\"2\":\"4.02\",\"3\":\"8.04\",\"4\":\"2.09\"}}', '[]', 'DA', 348, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 00:30:44', '2026-01-14 00:30:44'),
(122, 12, '2026-01-13', '3', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.10\",\"3\":\"4.12\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.12\",\"4\":\"2.09\",\"5\":\"4.20\",\"6\":\"3.08\"}}', '[]', 'DA', 627, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 00:44:34', '2026-01-14 00:44:34'),
(123, 23, '2026-01-13', '3', '7', 160, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"7.05\",\"2\":\"4.03\",\"3\":\"8.04\",\"4\":\"2.08\"},\"2\":{\"1\":\"7.07\",\"2\":\"4.04\",\"3\":\"8.05\",\"4\":\"2.08\"}}', '[]', 'DA', 278, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 01:23:58', '2026-01-14 01:23:58'),
(124, 6, '2026-01-13', '3', '15', 200, 32, 30, 2, 'OK', 'NG BERMINYAK', NULL, '{\"1\":{\"1\":\"9.89\",\"2\":\"9.93\",\"3\":\"9.86\",\"4\":\"9.90\"}}', '[]', 'DA', 168, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 01:33:01', '2026-01-14 01:33:01'),
(125, 12, '2026-01-13', '3', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.11\",\"2\":\"7.13\",\"3\":\"4.12\",\"4\":\"2.09\",\"5\":\"4.20\",\"6\":\"3.09\"}}', '[]', 'DA', 655, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 02:14:25', '2026-01-14 02:14:25'),
(126, 26, '2026-01-13', '3', '6', 500, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"4\":\"2.1\"}}', '[]', 'DA', 367, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 02:25:15', '2026-01-14 02:25:15'),
(127, 23, '2026-01-13', '3', '7', 160, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"7.05\",\"2\":\"4.03\",\"3\":\"8.04\",\"4\":\"2.08\"},\"2\":{\"1\":\"7.07\",\"2\":\"4.03\",\"3\":\"8.05\",\"4\":\"2.08\"}}', '[]', 'DA', 291, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 02:33:26', '2026-01-14 02:33:26'),
(128, 12, '2026-01-13', '3', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.09\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.11\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.09\",\"5\":\"4.20\",\"6\":\"3.09\"}}', '[]', 'DA', 604, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 05:34:36', '2026-01-14 05:34:36'),
(129, 23, '2026-01-13', '3', '7', 320, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"7.05\",\"2\":\"4.03\",\"3\":\"8.04\",\"4\":\"2.08\"},\"2\":{\"1\":\"7.07\",\"2\":\"4.04\",\"3\":\"8.04\",\"4\":\"2.09\"}}', '[]', 'DA', 358, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 05:47:47', '2026-01-14 05:47:47'),
(130, 6, '2026-01-13', '3', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.89\",\"2\":\"9.94\",\"3\":\"9.98\",\"4\":\"9.90\"}}', '[]', 'DA', 408, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 05:57:46', '2026-01-14 05:57:46'),
(131, 12, '2026-01-13', '3', '1', 150, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.09\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.13\",\"3\":\"4.13\",\"4\":\"2.09\",\"5\":\"4.20\",\"6\":\"3.09\"}}', '[]', 'DA', 303, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 06:40:04', '2026-01-14 06:40:04'),
(132, 26, '2026-01-13', '3', '6', 500, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"4\":\"2.1\"}}', '[]', 'DA', 318, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 06:45:35', '2026-01-14 06:45:35'),
(135, 12, '2026-01-14', '1', '1', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2.08\",\"5\":\"4.2\",\"6\":\"3\"},\"2\":{\"1\":\"2.11\",\"2\":\"7.12\",\"3\":\"4.12\",\"4\":\"2.10\",\"5\":\"4.11\",\"6\":\"3.10\"}}', '[]', 'YS', 372, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 07:34:42', '2026-01-14 07:34:42'),
(136, 26, '2026-01-14', '1', '6', 4, 4, 4, 0, 'OK', NULL, NULL, '{\"1\":{\"2\":\"2.1\",\"3\":\"2.1\",\"4\":\"2.1\"},\"2\":{\"2\":\"2.1\",\"3\":\"2.1\",\"4\":\"2.1\"}}', '[]', 'YS', 124, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 07:41:29', '2026-01-14 07:41:29'),
(137, 23, '2026-01-14', '1', '7', 4, 4, 4, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"7\",\"2\":\"4.1\",\"3\":\"8.06\",\"4\":\"2.1\"},\"2\":{\"1\":\"7\",\"2\":\"4.04\",\"3\":\"8.1\",\"4\":\"2.1\"}}', '[]', 'YS', 202, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 07:48:06', '2026-01-14 07:48:06'),
(138, 6, '2026-01-14', '1', '15', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.90\",\"4\":\"9.90\"}}', '[]', 'YS', 353, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 07:54:53', '2026-01-14 07:54:53'),
(139, 13, '2026-01-14', '1', '4', 20, 20, 20, 0, 'NG', 'SETING AWAL FPA ERAT PART SESUAI  STANDAR 263. \r\nNOTE : CHECK DIMENSI PERBARUI', NULL, '{\"1\":{\"1\":\"5.1\"}}', '[]', 'YS', 656, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 08:13:22', '2026-01-14 08:13:22'),
(141, 4, '2026-01-14', '1', '18', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"3.2\",\"3\":\"24\"},\"2\":{\"1\":\"25.06\",\"2\":\"3.20\",\"3\":\"24\"}}', '[]', 'YS', 382, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 08:29:40', '2026-01-14 08:29:40'),
(142, 13, '2026-01-14', '1', '4', 20, 20, 20, 0, 'OK', 'SETING AWAL FPA.BERAT PART SESUAI STANDAR 263', NULL, '{\"1\":{\"1\":\"8.1\"}}', '[]', 'YS', 80, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 08:36:15', '2026-01-14 08:36:15'),
(143, 12, '2026-01-14', '1', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.12\",\"3\":\"4.1\",\"4\":\"2.1\",\"5\":\"4.2\",\"6\":\"3\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.10\",\"3\":\"4.12\",\"4\":\"2.08\",\"5\":\"4.18\",\"6\":\"3.08\"}}', '[]', 'YS', 255, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 08:50:34', '2026-01-14 08:50:34'),
(144, 12, '2026-01-14', '1', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2.1\",\"5\":\"4.10\",\"6\":\"3\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.10\",\"3\":\"4.12\",\"4\":\"2.09\",\"5\":\"4.10\",\"6\":\"3.11\"}}', '[]', 'YS', 364, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 10:32:13', '2026-01-14 10:32:13'),
(145, 6, '2026-01-14', '1', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.90\",\"4\":\"9.90\"}}', '[]', 'YS', 338, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 12:11:11', '2026-01-14 12:11:11'),
(146, 13, '2026-01-14', '1', '4', 2, 2, 2, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"8\"}}', '[]', 'YS', 126, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 12:23:32', '2026-01-14 12:23:32'),
(147, 26, '2026-01-14', '1', '6', 4, 4, 4, 0, 'OK', NULL, NULL, '{\"1\":{\"2\":\"2\"}}', '[]', 'YS', 124, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 12:25:54', '2026-01-14 12:25:54'),
(148, 23, '2026-01-14', '1', '7', 4, 4, 4, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"7.10\",\"2\":\"4\",\"3\":\"8.1\",\"4\":\"2.1\"},\"2\":{\"1\":\"7.07\",\"2\":\"4.04\",\"3\":\"8.05\",\"4\":\"2.08\"}}', '[]', 'YS', 139, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 12:30:55', '2026-01-14 12:30:55'),
(149, 4, '2026-01-14', '1', '18', 440, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"3.2\",\"3\":\"24\"},\"2\":{\"1\":\"25.10\",\"2\":\"3.2\",\"3\":\"24\"}}', '[]', 'YS', 314, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 12:42:25', '2026-01-14 12:42:25'),
(150, 12, '2026-01-14', '1', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2.08\",\"5\":\"4.10\",\"6\":\"3\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.10\",\"3\":\"4.11\",\"4\":\"2.09\",\"5\":\"4.10\",\"6\":\"3.11\"}}', '[]', 'YS', 313, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 12:55:24', '2026-01-14 12:55:24'),
(151, 12, '2026-01-14', '1', '1', 140, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.1\",\"2\":\"7.1\",\"3\":\"4.1\",\"4\":\"2.1\",\"5\":\"4.2\",\"6\":\"3.1\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.12\",\"3\":\"4.12\",\"4\":\"2.09\",\"5\":\"4.11\",\"6\":\"3.10\"}}', '[]', 'YS', 277, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 14:08:46', '2026-01-14 14:08:46'),
(152, 4, '2026-01-14', '1', '18', 620, 80, 80, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25\",\"2\":\"3.2\",\"3\":\"24.1\"},\"2\":{\"1\":\"25.06\",\"2\":\"3.2\",\"3\":\"24\"}}', '[]', 'YS', 373, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 14:16:41', '2026-01-14 14:16:41'),
(153, 6, '2026-01-14', '1', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.90\",\"3\":\"9.89\",\"4\":\"9.90\"}}', '[]', 'YS', 285, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 14:22:01', '2026-01-14 14:22:01'),
(154, 12, '2026-01-14', '2', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.10\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.10\",\"3\":\"4.10\",\"4\":\"2.10\",\"5\":\"4.18\",\"6\":\"3.08\"}}', '[]', 'SH', 492, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 17:14:38', '2026-01-14 17:14:38'),
(155, 4, '2026-01-14', '2', '18', 380, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"3.25\",\"3\":\"24.12\"},\"2\":{\"1\":\"25.06\",\"2\":\"3.20\",\"3\":\"24.28\"}}', '[]', 'SH', 373, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 19:12:32', '2026-01-14 19:12:32'),
(156, 6, '2026-01-14', '2', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.90\",\"4\":\"9.89\"}}', '[]', 'SH', 421, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 19:28:08', '2026-01-14 19:28:08'),
(157, 13, '2026-01-14', '2', '4', 2, 2, 2, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"8.1\"}}', '[]', 'SH', 81, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 20:03:18', '2026-01-14 20:03:18'),
(158, 12, '2026-01-14', '2', '1', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.05\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.1\",\"5\":\"4.20\",\"6\":\"3.08\"},\"2\":{\"1\":\"2.10\",\"2\":\"7.13\",\"3\":\"4.12\",\"4\":\"2.10\",\"5\":\"4.18\",\"6\":\"3.10\"}}', '[]', 'SH', 538, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 20:19:30', '2026-01-14 20:19:30'),
(159, 12, '2026-01-14', '2', '1', 300, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"2.10\",\"2\":\"7.12\",\"3\":\"4.13\",\"4\":\"2.08\",\"5\":\"4.20\",\"6\":\"3.10\"},\"2\":{\"1\":\"2.12\",\"2\":\"7.13\",\"3\":\"4.12\",\"4\":\"2.10\",\"5\":\"4.18\",\"6\":\"3.11\"}}', '[]', 'SH', 591, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 22:20:53', '2026-01-14 22:20:53'),
(160, 6, '2026-01-14', '2', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.89\",\"3\":\"9.89\",\"4\":\"9.90\"}}', '[]', 'SH', 335, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 22:27:06', '2026-01-14 22:27:06'),
(161, 4, '2026-01-14', '3', '18', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"3.15\",\"3\":\"24.18\"}}', '[]', 'DA', 251, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 23:49:56', '2026-01-14 23:49:56'),
(162, 6, '2026-01-14', '3', '15', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.89\",\"2\":\"9.92\",\"3\":\"9.86\",\"4\":\"9.90\"}}', '[]', 'DA', 209, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 23:56:09', '2026-01-14 23:56:09'),
(163, 13, '2026-01-14', '3', '4', 2, 2, 2, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"8.15\"}}', '[]', 'DA', 255, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 00:00:42', '2026-01-15 00:00:42'),
(164, 26, '2026-01-14', '3', '6', 4, 4, 2, 2, 'OK', 'NG GOMPAL ( PART LANGSUNG SORTIR ULANG )', 'REPAIR', '{\"1\":{\"4\":\"2.1\"}}', '[]', 'DA', 267, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 00:06:38', '2026-01-15 00:06:38'),
(165, 13, '2026-01-14', '3', '4', 150, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"8.15\"}}', '[]', 'DA', 461, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 00:41:01', '2026-01-15 00:41:01'),
(166, 6, '2026-01-14', '3', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.89\",\"2\":\"9.93\",\"3\":\"9.86\",\"4\":\"9.90\"}}', '[]', 'DA', 385, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 01:43:46', '2026-01-15 01:43:46'),
(167, 13, '2026-01-14', '3', '4', 50, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"8.15\"}}', '[]', 'DA', 249, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 02:19:55', '2026-01-15 02:19:55'),
(168, 13, '2026-01-14', '3', '4', 150, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"8.15\"}}', '[]', 'DA', 497, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 05:36:06', '2026-01-15 05:36:06'),
(169, 26, '2026-01-14', '3', '6', 500, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"4\":\"2.1\"}}', '[]', 'DA', 433, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 05:43:56', '2026-01-15 05:43:56'),
(170, 6, '2026-01-14', '3', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.89\",\"2\":\"9.93\",\"3\":\"9.86\",\"4\":\"9.90\"}}', '[]', 'DA', 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 05:51:37', '2026-01-15 05:51:37'),
(171, 13, '2026-01-14', '3', '4', 100, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"8.15\"}}', '[]', 'DA', 247, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 06:38:08', '2026-01-15 06:38:08'),
(172, 26, '2026-01-14', '3', '6', 400, 50, 50, 0, 'OK', NULL, NULL, '{\"1\":{\"4\":\"2.1\"}}', '[]', 'DA', 319, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 06:44:07', '2026-01-15 06:44:07'),
(173, 6, '2026-01-14', '3', '1', 10, 10, 10, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"10\"}}', '[]', 'DA', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 06:56:43', '2026-01-15 06:56:43'),
(174, 6, '2026-01-15', '1', '15', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.98\",\"2\":\"9.93\",\"3\":\"9.90\",\"4\":\"9.90\"}}', '[]', 'YS', 239, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 07:42:38', '2026-01-15 07:42:38'),
(175, 4, '2026-01-15', '1', '18', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"3.2\",\"3\":\"24\"}}', '[]', 'YS', 320, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 07:56:53', '2026-01-15 07:56:53'),
(176, 6, '2026-01-15', '1', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.90\",\"4\":\"9.90\"}}', '[]', 'YS', 360, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 12:53:03', '2026-01-15 12:53:03'),
(177, 4, '2026-01-15', '1', '18', 880, 80, 80, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"3.2\",\"3\":\"24\"},\"2\":{\"1\":\"25.00\",\"2\":\"3.20\",\"3\":\"24.1\"}}', '[]', 'YS', 406, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 13:34:32', '2026-01-15 13:34:32'),
(178, 6, '2026-01-15', '1', '15', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.90\",\"3\":\"9.89\",\"4\":\"9.89\"}}', '[]', 'SH', 99, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 15:12:04', '2026-01-15 15:12:04'),
(179, 4, '2026-01-15', '2', '18', 20, 20, 20, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"25.11\",\"2\":\"3.22\",\"3\":\"24.20\"},\"2\":{\"1\":\"25.12\",\"2\":\"3.20\",\"3\":\"24.18\"}}', '[]', 'SH', 107, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 16:34:25', '2026-01-15 16:34:25'),
(180, 6, '2026-01-15', '2', '15', 200, 32, 32, 0, 'OK', NULL, NULL, '{\"1\":{\"1\":\"9.90\",\"2\":\"9.93\",\"3\":\"9.89\",\"4\":\"9.90\"}}', '[]', 'SH', 355, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-15 19:55:03', '2026-01-15 19:55:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `items`
--

CREATE TABLE `items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `customer` text DEFAULT NULL,
  `part_number` varchar(255) DEFAULT NULL,
  `sap_code` varchar(100) DEFAULT NULL,
  `dimension_standards` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dimension_standards`)),
  `defects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`defects`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `items`
--

INSERT INTO `items` (`id`, `name`, `category_id`, `file_path`, `customer`, `part_number`, `sap_code`, `dimension_standards`, `defects`, `created_at`, `updated_at`) VALUES
(1, 'COVER, FR. TOP SET', 1, 'master item/ahm/1767671665_0101. PCCP Cover, Front Top Set (6430B-K2V -N800) (Outgoing Subassy).pdf', 'PT. ASTRA HONDA MOTOR', '6430B-K2V -N800', NULL, '[]', '[\"SINK MARK\", \"FLASH PARTING LINE\", \"FLOW\", \"ALL LOCKING SHORT MOULD\", \"BARET\", \"BEDA WARNA / SHINING\", \"HOLE FLASH\", \"NUT CLIP TERBALIK\", \"RIB SHORT\"]', '2025-12-28 22:22:33', '2026-01-09 11:12:18'),
(2, 'COVER, HANDLE RR ASSY', 1, 'master item/ahm/1767671689_098. PCCP Cover, Handle RR Assy (53206-K3V-N000) (Outgoing Subassy).pdf', 'PT. ASTRA HONDA MOTOR', '5320C-K3V -N000-DL', NULL, '[]', '[\"NUT SPRING 4MM TIDAK TERPASANG\", \"FLASH\", \"SINKMARK\", \"KASAR\", \"KONTAMINASI\", \"BEDA WARNA\", \"UNDER CUT\", \"GOMPAL\", \"WELD LINE\", \"FLOW\"]', '2025-12-28 22:22:33', '2026-01-09 11:12:48'),
(3, 'COVER, ASSY FUEL TANK', 1, 'master item/ahm/1767671708_0103. PCCP Cover, Assy Fuel Tank (1757A-K0JJ-NA00) (Outgoing Subassy).pdf', 'PT. ASTRA HONDA MOTOR', '1757A-K0JJ-NA00', NULL, '[]', '[\"FLASH\", \"RUBER FUEL SEAL TIDAK TERPASANG\", \"TUBE DRAIN TIDAK TERPASANG\", \"LABEL FUEL TIDAK TERPASANG\", \"BEDA WARNA\", \"FLASH HOLE\"]', '2025-12-28 22:22:33', '2026-01-09 11:11:59'),
(4, 'TUNING FORK MARK, 3D', 2, 'master item/yimm/1768278272_1PA-F836B-00.pdf', 'PT. YAMAHA INDONESIA MOTOR MFG', '1PA - F836B - 00', NULL, '[{\"point\":\"1\",\"size\":\"25\",\"tolerance\":\"0.2\"},{\"point\":\"2\",\"size\":\"3.2\",\"tolerance\":\"0.2\"},{\"point\":\"3\",\"size\":\"24\",\"tolerance\":\"0.4\"}]', '[\"SINMARK\",\"BARET\",\"SILVER\",\"FLASH\",\"DUSPIN\",\"FLOW\",\"KAKI PATAH\",\"BERMINYAK\",\"DIMENSI\"]', '2025-12-28 22:22:33', '2026-01-13 16:54:07'),
(5, 'COVER, HNDL END K3VA', 2, 'master item/ahm/1767671773_083. PCCP Cover, Hndl End (53102-K0L-D002) (Inproses Injection).pdf', 'PT. ASTRA HONDA MOTOR', '53102-K0L -D002', NULL, '[]', '[\"SILVER\",\"BARET\",\"SHORT MOLD\",\"BEDA WARNA\",\"FLASH\",\"SINMARK\",\"CUTTING GATE OVER ATAU UNDER\",\"DIMENSI\"]', '2025-12-28 22:22:33', '2026-01-09 14:48:16'),
(6, 'COVER, HEAD LIGHT  (NATURAL)', 2, 'master item/ahm/1768290795_COVER HEAD LIGHT NATURAL + DIMENSI.pdf', 'PT. ASTRA HONDA MOTOR', '53209-K3V-N100', NULL, '[{\"point\":\"1\",\"size\":\"10\",\"tolerance\":\"0.2\"},{\"point\":\"2\",\"size\":\"10\",\"tolerance\":\"0.2\"},{\"point\":\"3\",\"size\":\"10\",\"tolerance\":\"0.2\"},{\"point\":\"4\",\"size\":\"10\",\"tolerance\":\"0.2\"}]', '[\"WIDELINE\",\"SILVER\",\"GATE RUNNER TIDAK RATA\\/RAPIH\",\"FLASH\",\"BINTIK\",\"SINMARK\",\"GOMPAL\",\"BARET\",\"DIMENSI\",\"KETRIK BULAN DAN TAHUN HARUS UPDATE\",\"CHECK CODE PART NO.53209-K3V -N100\"]', '2025-12-28 22:22:33', '2026-01-13 14:53:15'),
(10, 'COVER, ASSY FUEL TANK', 1, 'master item/ahm/1767666793_084. COVER FUEL TANK (17575-K1AL-N800)(Outgoing Subassy).pdf', 'PT.  ASTRA HONDA MOTOR', '17575-K1AL-N800', NULL, '[]', '[\"FLASH\", \"RUBBER FUEL SEAL TERBALIK\", \"UNDER CUT\", \"WELDLINE\", \"SHORT MOULD\", \"BARET\", \"BEDA WARNA\", \"POTONG GATE TIDAK RAPI\"]', '2026-01-06 02:33:13', '2026-01-09 11:12:10'),
(11, 'TRAY, ASSY FUEL', 1, 'master item/ahm/1767669655_75. Tray, Assy Fuel 1750A-K2S-N000-DL(Outgoing Subassy).pdf', 'PT. ASTRA HONDA MOTOR', '1750A-K2S-N000-DL', NULL, '[]', '[\"RUBBER FUEL SEAL TIDAK TERPASANG\",\"V-TUBE 8x12X810 TIDAK TERPASANG\",\"FLASH\",\"SHORT MOLD\"]', '2026-01-06 10:20:55', '2026-01-09 11:20:04'),
(12, 'COVER, HANDLE UPPER', 2, 'master item/ahm/1767673464_0095. COVER HANDLE UPPER (53250-K1Y-DC00)(Inproses Injection).pdf', 'PT.  ASTRA HONDA MOTOR', '53250-K1Y-DC00', NULL, '[{\"point\":\"1\",\"size\":\"2\",\"tolerance\":\"0.2\"},{\"point\":\"2\",\"size\":\"7,2\",\"tolerance\":\"0.2\"},{\"point\":\"3\",\"size\":\"4,1\",\"tolerance\":\"0.2\"},{\"point\":\"4\",\"size\":\"2\",\"tolerance\":\"0.1\"},{\"point\":\"5\",\"size\":\"4,5\",\"tolerance\":\"0.2\"},{\"point\":\"3\",\"size\":\"3\",\"tolerance\":\"0.2\"}]', '[\"UNDER CUT\",\"FLASH\",\"WHITE MARK\",\"SHINNING\",\"BEDA WARNA\",\"SINKMARK\",\"SILVER\",\"FLOW\",\"DIMENSI\",\"WINKMARK TIDAK TERPASANG DAN TERBALIK\",\"TIDAK MEMAKAI POKAYOKE BY OPERATOR\",\"SHORT MOULD MARKING AREA (BY OPERATOR)\"]', '2026-01-06 11:24:24', '2026-01-09 14:47:26'),
(13, 'PANEL, RR METER', 2, 'master item/ahm/1767674657_0097. PANEL RR METER (64337-K1Y-DF02)(Inproses Injection).pdf', 'PT.  ASTRA HONDA MOTOR', '64337-K1Y-DF02', NULL, '[{\"point\":\"1\",\"size\":\"8.1\",\"tolerance\":\"0.2\"},{\"point\":\"2\",\"size\":\"8.5\",\"tolerance\":\"0.2\"},{\"point\":\"3\",\"size\":\"8.1\",\"tolerance\":\"0.2\"},{\"point\":\"4\",\"size\":\"5.5\",\"tolerance\":\"0.2\"},{\"point\":\"5\",\"size\":\"6.5\",\"tolerance\":\"0.2\"},{\"point\":\"6\",\"size\":\"6\",\"tolerance\":\"0.1\"}]', '[\"UNDER CUT\",\"FLASH\",\"KASAR\",\"SHINNING\",\"SCRATCH\",\"SINKMARK\",\"WELDLINE\",\"FLOW\",\"DIMENSI\"]', '2026-01-06 11:44:17', '2026-01-09 14:58:50'),
(14, 'PANEL, RR METER', 2, 'master item/ahm/1767675168_0096. PANEL RR METER (64337-K1Y-DC02))(Inproses Injection).pdf', 'PT.  ASTRA HONDA MOTOR', '64337-K1Y-DC02', NULL, '[{\"point\":\"1\",\"size\":\"2.5\",\"tolerance\":\"0.1\"},{\"point\":\"2\",\"size\":\"6\",\"tolerance\":\"0.1\"},{\"point\":\"3\",\"size\":\"26\",\"tolerance\":\"0.1\"},{\"point\":\"4\",\"size\":\"4.2\",\"tolerance\":\"0.2\"},{\"point\":\"5\",\"size\":\"3.1\",\"tolerance\":\"0.1\"},{\"point\":\"6\",\"size\":\"7\",\"tolerance\":\"0.2\"}]', '[\"UNDER CUT\",\"FLASH\",\"KASAR\",\"SHINNING\",\"SCRATCH\",\"SINKMARK\",\"WELDLINE\",\"FLOW\",\"DIMENSI\"]', '2026-01-06 11:52:48', '2026-01-09 14:59:19'),
(15, 'COVER, HANDLE LOWER', 2, 'master item/ahm/1767675692_0094. COVER HANDLE LOWER (53206-K1Y-DC00)(Inproses Injection).pdf', 'PT.  ASTRA HONDA MOTOR', '53206-K1Y-DC00', NULL, '[{\"point\":\"1\",\"size\":\"2\",\"tolerance\":\"0.2\"},{\"point\":\"2\",\"size\":\"7.2\",\"tolerance\":\"0.2\"},{\"point\":\"3\",\"size\":\"2.5\",\"tolerance\":\"0.2\"},{\"point\":\"4\",\"size\":\"7\",\"tolerance\":\"0.2\"},{\"point\":\"5\",\"size\":\"3.1\",\"tolerance\":\"0.1\"},{\"point\":\"6\",\"size\":\"3.2\",\"tolerance\":\"0.2\"}]', '[\"UNDER CUT\",\"FLASH\",\"WHITE MARK\",\"SHINNING\",\"BEDA WARNA\",\"SINKMARK\",\"SILVER\",\"FLOW\",\"DIMENSI\"]', '2026-01-06 12:01:32', '2026-01-09 14:41:49'),
(16, 'EMBLEM 3D', 3, 'master item/yimm/1768200524_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BED-F4786-00', '7-03-0094', '[]', NULL, '2026-01-07 09:44:50', '2026-01-13 15:24:16'),
(17, 'CAP', 3, 'master item/yimm/1768200287_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BEJ-F8349-10', '7-03-0091', '[]', NULL, '2026-01-07 11:27:29', '2026-01-13 14:09:24'),
(18, 'BASE GR SPORT', 3, 'master item/others/1768201905_SK Cross Cut TSH.pdf', 'PT. WANG SARI MULTI UTAMA', 'P5188-BZA04 (D55L-876B)', '7-38-0028', '[]', NULL, '2026-01-07 11:44:19', '2026-01-13 17:06:37'),
(19, 'COVER HEAD LIGHT', 3, 'master item/ahm/1768200469_SK Cross Cut JIS H.pdf', 'PT.  ASTRA HONDA MOTOR', '53209-K3V -N101', '7-02-0348', '[]', NULL, '2026-01-08 09:23:27', '2026-01-13 11:46:26'),
(21, 'BOTTOM COVER HANDLE EV X05', 3, 'master item/others/1768200366_SK Cross Cut JIS H.pdf', 'PT. HIT (HARTONO ISTANA TEKNOLOGI)', '-', NULL, '[]', NULL, '2026-01-08 10:05:11', '2026-01-12 14:28:58'),
(22, 'KNOB VENT ORNAMENT', 3, 'master item/others/1768200643_SK Cross Cut TSH.pdf', 'PT. YUJU INDONESIA', 'TPST12020', '7-44-0001', '[]', NULL, '2026-01-08 10:26:36', '2026-01-13 15:54:46'),
(23, 'COVER, A ECU', 2, 'master item/ahm/1768276649_COVER, A ECU.pdf', 'PT.  ASTRA HONDA MOTOR', '83780-K2FP-NC00', NULL, '[{\"point\":\"1\",\"size\":\"7\",\"tolerance\":\"0.1\"},{\"point\":\"2\",\"size\":\"4\",\"tolerance\":\"0.1\"},{\"point\":\"3\",\"size\":\"8\",\"tolerance\":\"0.1\"},{\"point\":\"4\",\"size\":\"2.1\",\"tolerance\":\"0.1\"}]', '[\"FLASH\",\"LOCKING PATAH\",\"UNDER CUT\",\"KONTAMINASI\",\"BEDA WARNA\",\"GOMPAL\",\"WELD LINE\",\"FLOW\",\"DIMENSI\"]', '2026-01-12 11:25:26', '2026-01-13 10:57:29'),
(24, 'COVER, B ECU', 2, 'master item/ahm/1768290824_COVER, B ECU + DIMENSI.pdf', 'PT.  ASTRA HONDA MOTOR', '83781-K2FP-NC00', NULL, '[{\"point\":\"1\",\"size\":\"3\",\"tolerance\":\"0.1\"},{\"point\":\"2\",\"size\":\"4.4\",\"tolerance\":\"0.1\"},{\"point\":\"3\",\"size\":\"2\",\"tolerance\":\"0.1\"}]', '[\"FLASH\",\"LOCKING PATAH\",\"UNDER CUT\",\"KONTAMINASI\",\"BEDA WARNA\",\"GOMPAL\",\"WELD LINE\",\"FLOW\",\"DIMENSI\"]', '2026-01-12 11:38:46', '2026-01-13 14:53:44'),
(25, 'COVER, ECU ASSY', 2, 'master item/ahm/1768193300_092. COVER ECU ASSY (8378A-K2FP-NC00-DL).pdf', 'PT.  ASTRA HONDA MOTOR', '8378A-K2FP-NC00-DL', NULL, '[{\"point\":\"1\",\"size\":\"162\",\"tolerance\":\"0.5\"},{\"point\":\"2\",\"size\":\"203\",\"tolerance\":\"0.5\"},{\"point\":\"3\",\"size\":\"2\",\"tolerance\":\"0.2\"},{\"point\":\"4\",\"size\":\"3\",\"tolerance\":\"0.1\"},{\"point\":\"5\",\"size\":\"4.4\",\"tolerance\":\"0.1\"},{\"point\":\"6\",\"size\":\"2.1\",\"tolerance\":\"0.1\"}]', '[\"FLASH\",\"SINMARK\",\"SURFACE KASAR\",\"KONTAMINASI\",\"BEDA WARNA\",\"UNDERCUT\",\"GOMPAL\",\"WELDLINE\",\"FLOW\",\"DIMENSI\"]', '2026-01-12 11:48:20', '2026-01-12 11:48:57'),
(26, 'COVER FUEL TANK', 2, 'master item/ahm/1768193753_084. COVER FUEL TANK (17575-K1AL-N800).pdf', 'PT.  ASTRA HONDA MOTOR', '17575-K1AL-N800', NULL, '[{\"point\":\"1\",\"size\":\"84.9\",\"tolerance\":\"0.5\"},{\"point\":\"2\",\"size\":\"2\",\"tolerance\":\"0.1\"},{\"point\":\"3\",\"size\":\"2\",\"tolerance\":\"0.1\"},{\"point\":\"4\",\"size\":\"2\",\"tolerance\":\"0.1\"},{\"point\":\"5\",\"size\":\"1.8\",\"tolerance\":\"0.1\"}]', '[\"SINKMARK\",\"FLASH PARTING LINE\",\"UNDERCUT\",\"WELDLINE\",\"SHORTMOULD\",\"BARET\",\"BEDA WARNA\",\"POTONG GATE RUNNER TIDAK RAPIH\"]', '2026-01-12 11:55:53', '2026-01-12 11:55:53'),
(27, 'COVER BATTERY', 1, 'master item/ahm/1768201443_66. Cover Battery (8011A-K2F-N000-IN) ASSY.pdf', 'PT.  ASTRA HONDA MOTOR', '8011A-K2F -N000-IN', NULL, '[]', '[\"FLASH\",\"CUSHION A, BATTERY TIDAK TERPASANG\",\"CUSHION C, BATTERY TIDAK TERPASANG\"]', '2026-01-12 14:04:03', '2026-01-12 14:04:03'),
(28, 'CVR F HANDLE BAR KIT', 1, 'master item/others/1768202689_PCCP Cover Bar Kit.pdf', 'PT. HIT', 'EC-160093-AAA00A', NULL, '[]', '[\"FLASH\",\"BINTIK\",\"BARET\",\"KUNING CHROME\",\"BAKAR CHROME\",\"BOTAK CAT\",\"CAT MELER\",\"CAT BERBULU\",\"KULIT JERUK\",\"CAT TIPIS\"]', '2026-01-12 14:08:01', '2026-01-12 14:24:49'),
(29, 'LEVEL FUEL COCK', 3, 'master item/ahm/1768202099_SK Cross Cut JIS H.pdf', 'PT. ASTRA HONDA MOTOR', '-', '6-02-0002-02', '[]', NULL, '2026-01-12 14:14:59', '2026-01-13 17:02:02'),
(30, 'COVER L CARBURATOR', 3, 'master item/ahm/1768202145_SK Cross Cut JIS H.pdf', 'PT. ASTRA HONDA MOTOR', '83711-KCJ-6600-C1', '7-02-0015', '[]', NULL, '2026-01-12 14:15:45', '2026-01-14 09:50:01'),
(31, 'EMBLEM RH BODY COVER (VARIO)', 3, 'master item/ahm/1768202190_SK Cross Cut JIS H.pdf', 'PT. ASTRA HONDA MOTOR', '86832-K59A-A300', '6-02-0019-02', '[]', NULL, '2026-01-12 14:16:30', '2026-01-14 09:52:20'),
(32, 'EMBLEM LH BODY COVER (VARIO)', 3, 'master item/ahm/1768202252_SK Cross Cut JIS H.pdf', 'PT. ASTRA HONDA MOTOR', '86833-K59A-A300', '6-02-0020-02', '[]', NULL, '2026-01-12 14:17:32', '2026-01-14 09:52:38'),
(33, 'EMBLEM LH BODY COVER (SCOOPY)', 3, 'master item/ahm/1768202274_SK Cross Cut JIS H.pdf', 'PT. ASTRA HONDA MOTOR', '87124-K16-A000', '6-02-0100-02', '[]', NULL, '2026-01-12 14:17:54', '2026-01-14 09:51:52'),
(34, 'EMBLEM RH BODY COVER (SCOOPY)', 3, 'master item/ahm/1768202300_SK Cross Cut JIS H.pdf', 'PT. ASTRA HONDA MOTOR', '87123-K16-A000', '6-02-0099-02', '[]', NULL, '2026-01-12 14:18:20', '2026-01-14 09:51:32'),
(35, 'CAP SOC BOLT', 3, 'master item/ahm/1768202335_SK Cross Cut JIS H.pdf', 'PT. ASTRA HONDA MOTOR', '8MM 91455-KEA-0003', '7-02-0147', '[]', NULL, '2026-01-12 14:18:55', '2026-01-13 14:35:11'),
(36, 'COVER HANDLE FR', 3, 'master item/ahm/1768202356_SK Cross Cut JIS H.pdf', 'PT. ASTRA HONDA MOTOR', 'K1ZA', '7-02-0221', '[]', NULL, '2026-01-12 14:19:16', '2026-01-13 14:35:49'),
(38, 'COVER HANDLE RR', 3, 'master item/ahm/1768202452_SK Cross Cut JIS H.pdf', 'PT. ASTRA HONDA MOTOR', 'K1ZA', '7-02-0222', '[]', NULL, '2026-01-12 14:20:52', '2026-01-14 09:49:28'),
(39, 'COVER HANDLE FR', 3, 'master item/ahm/1768202474_SK Cross Cut JIS H.pdf', 'PT. ASTRA HONDA MOTOR', 'K97', NULL, '[]', NULL, '2026-01-12 14:21:14', '2026-01-12 14:21:14'),
(40, 'EMBLEM 3D LH', 3, 'master item/yimm/1768202534_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BJ8-F173B-00', '6-03-0029-02', '[]', NULL, '2026-01-12 14:22:14', '2026-01-14 09:50:31'),
(41, 'EMBLEM 3D RH', 3, 'master item/yimm/1768202569_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BJ8-F174B-00', '6-03-0030-02', '[]', NULL, '2026-01-12 14:22:49', '2026-01-14 09:50:48'),
(42, 'TUNING FORK MARK, 3D', 3, 'master item/yimm/1768202648_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BJM-F836B-00', '6-03-0099-02', '[]', NULL, '2026-01-12 14:24:08', '2026-01-13 16:56:24'),
(43, 'TUNING FORK MARK, 3D', 3, 'master item/yimm/1768202706_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'B74-F414B-00', '6-03-0032-02', '[]', NULL, '2026-01-12 14:25:06', '2026-01-13 16:56:11'),
(45, 'TUNING FORK MARK, 3D', 3, 'master item/yimm/1768203214_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', '1WD-F413B-01', '6-03-0092-02', '[]', NULL, '2026-01-12 14:33:34', '2026-01-13 16:56:31'),
(46, 'TUNING FORK MARK, 3D', 3, 'master item/yimm/1768203305_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', '1PA-F836B-01', '6-03-0093-02', '[]', NULL, '2026-01-12 14:35:05', '2026-01-13 16:53:56'),
(47, 'PLATE RH', 3, 'master item/yimm/1768203344_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BJM', '7-30-0009', '[]', NULL, '2026-01-12 14:35:44', '2026-01-13 11:37:46'),
(48, 'PLATE LH', 3, 'master item/yimm/1768203378_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BJM', '7-30-0010', '[]', NULL, '2026-01-12 14:36:18', '2026-01-13 11:38:05'),
(49, 'MOULDING RADIATOR GRILLE UPPER', 3, 'master item/others/1768203458_SK Cross Cut TSH.pdf', 'PT. TAKAGI SARI MULTI UTAMA', '53121-BZ370-PLATING', '7-12-0079', '[]', NULL, '2026-01-12 14:37:38', '2026-01-13 11:41:01'),
(50, 'M0ULDING FR BUMPER', 3, 'master item/others/1768203506_SK Cross Cut TSH.pdf', 'PT. TAKAGI SARI MULTI UTAMA', '52711-BZ080 (D16)', '7-12-0094', '[]', NULL, '2026-01-12 14:38:26', '2026-01-13 11:41:33'),
(51, 'GRILLE RADIATOR', 3, 'master item/others/1768203565_SK Cross Cut TSH.pdf', 'PT. TAKAGI SARI MULTI UTAMA', '53111-BZ130 (MATERIAL) ADM-D99-GR00-TAPL', '7-12-0054', '[]', NULL, '2026-01-12 14:39:25', '2026-01-13 17:05:43'),
(52, 'BEZEL SHIFTING HOLE AT', 3, 'master item/others/1768203813_SK Cross Cut TSH.pdf', 'PT. SANKO GOSEI TECHNOLOGY INDONESIA', '58843-BZ350', '7-32-0006', '[]', NULL, '2026-01-12 14:43:33', '2026-01-13 11:47:36'),
(53, 'BEZEL SHIFTING HOLE MT', 3, 'master item/others/1768203907_SK Cross Cut TSH.pdf', 'PT. SANKO GOSEI TECHNOLOGY INDONESIA', '58843-BZ320', '7-32-0007', '[]', NULL, '2026-01-12 14:45:07', '2026-01-13 11:47:59'),
(54, 'CENTER UPPER', 3, 'master item/others/1768204051_SK Cross Cut TSH.pdf', 'PT. BS INDONESIA', '-', '7-64-0003', '[]', NULL, '2026-01-12 14:47:31', '2026-01-13 11:46:58'),
(55, 'CAP DOOR ARMREST FRT RH', 3, 'master item/others/1768204160_SK Cross Cut JIS H.pdf', 'PT. MAH SING INDONESIA', '74223-BZ030', '7-20-0005', '[]', NULL, '2026-01-12 14:49:20', '2026-01-13 14:34:06'),
(56, 'CAP DOOR ARMREST FRT LH', 3, 'master item/others/1768204181_SK Cross Cut TSH.pdf', 'PT. MAH SING INDONESIA', '74224-BZ030', '7-20-0006', '[]', NULL, '2026-01-12 14:49:41', '2026-01-13 14:33:26'),
(57, 'CAP DOOR ARMREST RR RH', 3, 'master item/others/1768204235_SK Cross Cut TSH.pdf', 'PT. MAH SING INDONESIA', '74225-BZ020', '7-20-0007', '[]', NULL, '2026-01-12 14:50:35', '2026-01-13 14:34:53'),
(58, 'CAP DOOR ARMREST RR LH', 3, 'master item/others/1768204258_SK Cross Cut TSH.pdf', 'PT. MAH SING INDONESIA', '74226-BZ020', '7-20-0008', '[]', NULL, '2026-01-12 14:50:58', '2026-01-13 14:34:30'),
(59, 'HOOD RH CHROME PLATING', 3, 'master item/others/1768204291_SK Cross Cut TSH.pdf', 'PT. PROGRESS TOYO INDONESIA', 'D001-115-01', '7-30-0003', '[]', NULL, '2026-01-12 14:51:31', '2026-01-13 17:03:43'),
(60, 'HOOD LH CHROME PLATING', 3, 'master item/others/1768204312_SK Cross Cut TSH.pdf', 'PT. PROGRESS TOYO INDONESIA', 'D001-115-02', '7-30-0004', '[]', NULL, '2026-01-12 14:51:52', '2026-01-13 17:04:28'),
(61, 'HOOD PLATING RH CHROME', 3, 'master item/others/1768204338_SK Cross Cut TSH.pdf', 'PT. PROGRESS TOYO INDONESIA', '8534-215-001', '7-30-0001', '[]', NULL, '2026-01-12 14:52:18', '2026-01-13 15:48:57'),
(62, 'HOOD PLATING LH CHROME', 3, 'master item/others/1768204360_SK Cross Cut TSH.pdf', 'PT. PROGRESS TOYO INDONESIA', '8534-215-002', '7-30-0002', '[]', NULL, '2026-01-12 14:52:40', '2026-01-13 17:04:11'),
(63, 'COVER, FRONT (CAV 1,2)', 3, 'master item/others/1768279974_SK Cross Cut JIS H.pdf', 'PT. MITSUBA INDONESIA', 'A2304-445-00-000 MH14146A', '7-08-0002', '[]', NULL, '2026-01-13 11:52:54', '2026-01-13 11:52:54'),
(64, 'CHROME LIST AVANZA LH 1', 3, 'master item/others/1768280037_SK Cross Cut TSH.pdf', 'PT. OPSINDO ELOK MANDIRI', '-', '6-21-0003-02', '[]', NULL, '2026-01-13 11:53:57', '2026-01-13 11:53:57'),
(65, 'CHROME LIST AVANZA LH 2', 3, 'master item/others/1768280079_SK Cross Cut TSH.pdf', 'PT. OPSINDO ELOK MANDIRI', '-', '6-21-0004-02', '[]', NULL, '2026-01-13 11:54:39', '2026-01-13 11:54:39'),
(66, 'CHROME LIST AVANZA LH 3', 3, 'master item/others/1768285747_SK Cross Cut TSH.pdf', 'PT. OPSINDO ELOK MANDIRI', NULL, '6-21-0005-02', '[]', NULL, '2026-01-13 13:29:07', '2026-01-13 13:29:25'),
(67, 'CHROME LIST AVANZA RH', 3, 'master item/others/1768285967_SK Cross Cut TSH.pdf', 'PT. OPSINDO ELOK MANDIRI', '-', '6-21-0002-02', '[]', NULL, '2026-01-13 13:32:47', '2026-01-13 13:32:47'),
(68, 'EMBLEM O', 3, 'master item/others/1768286781_SK Cross Cut TSH.pdf', 'PT. OPSINDO ELOK MANDIRI', 'MT08-EMB0204-CH-0', '7-21-0041', '[]', NULL, '2026-01-13 13:46:21', '2026-01-13 13:46:21'),
(69, 'EMBLEM F', 3, 'master item/others/1768286826_SK Cross Cut TSH.pdf', 'PT. OPSINDO ELOK MANDIRI', 'MT08-EMB0201', '7-21-0038', '[]', NULL, '2026-01-13 13:47:06', '2026-01-13 13:47:06'),
(70, 'EMBLEM S', 3, 'master item/others/1768286899_SK Cross Cut TSH.pdf', 'PT. OPSINDO ELOK MANDIRI', 'MT08-EMB0203-CH-S', '7-21-0040', '[]', NULL, '2026-01-13 13:48:19', '2026-01-13 13:48:19'),
(71, 'EMBLEM U', 3, 'master item/others/1768286948_SK Cross Cut TSH.pdf', 'PT. OPSINDO ELOK MANDIRI', 'MT08-EMB0202', '7-21-0039', '[]', NULL, '2026-01-13 13:49:08', '2026-01-13 13:49:08'),
(72, 'GARNISH RADIATOR GRILE STD (LOWER)', 3, 'master item/others/1768287010_SK Cross Cut TSH.pdf', 'PT. USRA TAMPI INDONESIA', '-', '7-17-0025', '[]', NULL, '2026-01-13 13:50:10', '2026-01-13 15:43:25'),
(74, 'BASE EMBLEM GR', 3, 'master item/others/1768287609_SK Cross Cut TSH.pdf', 'PT. WANG SARI MULTI UTAMA', '(D26B) P5188-BYA01', '7-38-0011', '[]', NULL, '2026-01-13 14:00:09', '2026-01-13 14:00:09'),
(75, 'BASE EMBLEM GR', 3, 'master item/others/1768287727_SK Cross Cut TSH.pdf', 'PT. WANG SARI MULTI UTAMA', '(D26B) TN (SILVER)', '7-38-0027', '[]', NULL, '2026-01-13 14:02:07', '2026-01-13 14:02:07'),
(76, 'BASE EMBLEM GR', 3, 'master item/others/1768287805_SK Cross Cut TSH.pdf', 'PT. WANG SARI MULTI UTAMA', '(RAIZE) 75441-52160', '7-38-0021', '[]', NULL, '2026-01-13 14:03:25', '2026-01-13 14:03:25'),
(77, 'BEZEL PLATE', 3, 'master item/others/1768287850_SK Cross Cut TSH.pdf', 'PT. YUJU INDONESIA', '5566-58032', '7-28-0001', '[]', NULL, '2026-01-13 14:04:10', '2026-01-13 14:04:10'),
(79, 'COVER KNOB SHIFT LEVER', 3, 'master item/others/1768289938_SK Cross Cut TSH.pdf', 'PT. ARTHA UTAMA PLASINDO', 'BZ 120', '7-09-0003', '[]', NULL, '2026-01-13 14:38:58', '2026-01-13 14:38:58'),
(80, 'DRESING PANEL', 3, 'master item/others/1768290086_SK Cross Cut TSH.pdf', 'PT. PRESHION ENGPLAS', 'A2B-0170-20', '7-105-0005', '[]', NULL, '2026-01-13 14:41:26', '2026-01-13 14:41:26'),
(81, 'DRESING PANEL', 3, 'master item/others/1768290134_SK Cross Cut TSH.pdf', 'PT. PRESHION ENGPLAS', 'A2B-0171-20', '7-105-0007', '[]', NULL, '2026-01-13 14:42:14', '2026-01-13 14:42:14'),
(82, 'DRESING PANEL', 3, 'master item/others/1768290172_SK Cross Cut TSH.pdf', 'PT. PRESHION ENGPLAS', 'A2B-0191', '7-105-0009', '[]', NULL, '2026-01-13 14:42:52', '2026-01-13 14:42:52'),
(83, 'EMBLEM 3D', 3, 'master item/yimm/1768290217_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BJM-F839B-20', '6-03-0121-02', '[]', NULL, '2026-01-13 14:43:37', '2026-01-13 14:43:37'),
(84, 'EMBLEM 3D', 3, 'master item/yimm/1768290868_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BJM-F839B-00', '6-03-0101-02', '[]', NULL, '2026-01-13 14:54:28', '2026-01-13 14:54:28'),
(85, 'EMBLEM 3D LH', 3, 'master item/yimm/1768291203_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', '3KA-F416B-00', '6-03-0002-02', '[]', NULL, '2026-01-13 15:00:03', '2026-01-13 15:00:03'),
(86, 'EMBLEM 3D', 3, 'master item/yimm/1768291388_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', '3KA-F416B-10', '6-03-0005-02', '[]', NULL, '2026-01-13 15:03:08', '2026-01-13 15:03:08'),
(87, 'EMBLEM 3D RH', 3, 'master item/yimm/1768291502_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', '3KA-F417B-00', '6-03-0004-02', '[]', NULL, '2026-01-13 15:05:02', '2026-01-13 15:05:02'),
(88, 'EMBLEM 3D LH', 3, 'master item/yimm/1768291956_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BJM-F173B-00', '6-03-0095-02', '[]', NULL, '2026-01-13 15:12:36', '2026-01-13 15:12:36'),
(89, 'EMBLEM 3D RH', 3, 'master item/yimm/1768292472_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BJM-F174B-00', '6-03-0096-02', '[]', NULL, '2026-01-13 15:21:12', '2026-01-13 15:21:12'),
(90, 'EMBLEM,1', 3, 'master item/yimm/1768292619_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', '5MX-F3108-00', '6-03-0009-02', '[]', NULL, '2026-01-13 15:23:39', '2026-01-13 15:23:39'),
(91, 'EMBLEM CANTER', 3, 'master item/others/1768292690_SK Cross Cut TSH.pdf', 'PT. USRA TAMPI INDONESIA', '-', '6-17-0010-02', '[]', NULL, '2026-01-13 15:24:50', '2026-01-13 15:24:50'),
(92, 'EMBLEM COLT DIESEL', 3, 'master item/others/1768292747_SK Cross Cut TSH.pdf', 'PT. USRA TAMPI INDONESIA', 'KM004885', '7-17-0002', '[]', NULL, '2026-01-13 15:25:47', '2026-01-13 15:25:47'),
(93, 'EMBLEM DAKAR', 3, 'master item/others/1768292942_SK Cross Cut TSH.pdf', 'PT. OPSINDO ELOK MANDIRI', '900123', '7-21-0035', '[]', NULL, '2026-01-13 15:29:02', '2026-01-13 15:29:02'),
(94, 'EMBLEM I BESAR I+P', 3, 'master item/others/1768293001_SK Cross Cut TSH.pdf', 'PT. BS INDONESIA', '-', '6-64-0005-02', '[]', NULL, '2026-01-13 15:30:01', '2026-01-13 15:30:01'),
(95, 'EMBLEM MARK S BESAR (YR 8)', 3, 'master item/others/1768293099_SK Cross Cut TSH.pdf', 'PT SUZUKI INDOMOBIL MOTOR', '77811-58J00-OPG', '6-10-0014-02', '[]', NULL, '2026-01-13 15:31:39', '2026-01-13 15:31:39'),
(96, 'EMBLEM U BESAR I+P (U3)', 3, 'master item/others/1768293137_SK Cross Cut TSH.pdf', 'PT. BS INDONESIA', '-', '6-64-0007-02', '[]', NULL, '2026-01-13 15:32:17', '2026-01-13 15:32:33'),
(97, 'EMBLEM U BESAR I+P (U5)', 3, 'master item/others/1768293267_SK Cross Cut TSH.pdf', 'PT. BS INDONESIA', '-', '6-64-0009-02', '[]', NULL, '2026-01-13 15:34:27', '2026-01-13 15:34:27'),
(98, 'EMBLEM VVT', 3, 'master item/others/1768293350_SK Cross Cut TSH.pdf', 'PT. SUZUKI INDOMOBIL MOTOR', '77851-54G00-OPG', '6-10-0016-02', '[]', NULL, '2026-01-13 15:35:50', '2026-01-13 15:35:50'),
(99, 'END CUP (22.5 X 13 MM)', 3, 'master item/others/1768293593_SK Cross Cut TSH.pdf', 'PT. INDOSAFETY SENTOSA INDUSTRY', 'MC561-N', '7-00-0027', '[]', NULL, '2026-01-13 15:39:53', '2026-01-13 15:39:53'),
(100, 'END, GRIP', 3, 'master item/yimm/1768293647_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BJM-F6246-00', '7-03-0098', '[]', NULL, '2026-01-13 15:40:47', '2026-01-13 15:40:47'),
(101, 'FACE PLATE', 3, 'master item/others/1768293691_SK Cross Cut TSH.pdf', 'PT. WANG SARI MULTI UTAMA', '-', '7-38-0034', '[]', NULL, '2026-01-13 15:41:31', '2026-01-13 15:41:31'),
(102, 'GARNISH FRONT LH', 3, 'master item/others/1768293770_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '7-13-0130', '[]', NULL, '2026-01-13 15:42:50', '2026-01-13 15:42:50'),
(103, 'GARNISH FRONT RH', 3, 'master item/others/1768293962_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '7-13-0129', '[]', NULL, '2026-01-13 15:46:02', '2026-01-13 15:46:02'),
(104, 'GARNISH RADIATOR GRILLE WIDE (UPPER)', 3, 'master item/others/1768294074_SK Cross Cut TSH.pdf', 'PT. USRA TAMPI INDONESIA', '-', '7-17-0024', '[]', NULL, '2026-01-13 15:47:54', '2026-01-13 15:47:54'),
(105, 'KNOB INSTRUMENT', 3, 'master item/others/1768294206_SK Cross Cut TSH.pdf', 'PT. YUJU INDONESIA', '55696-KK050', '7-32-0004', '[]', NULL, '2026-01-13 15:50:06', '2026-01-13 15:50:06'),
(106, 'KNOB K2A', 3, 'master item/others/1768294419_SK Cross Cut TSH.pdf', 'PT. PRESHION ENGPLAS', '-', '7-105-0001', '[]', NULL, '2026-01-13 15:53:39', '2026-01-13 15:55:26'),
(107, 'KNOB K2K', 3, 'master item/others/1768294455_SK Cross Cut TSH.pdf', 'PT. PRESHION ENGPLAS', '-', '7-105-0003', '[]', NULL, '2026-01-13 15:54:15', '2026-01-13 15:55:38'),
(108, 'LEG BASE MOULD', 3, 'master item/yimm/1768294614_SK Cross Cut JIS H.pdf', 'PT. YAMAHA MUSIC MANUFACTURING ASIA', 'VGJ-3100', '7-65-0020', '[]', NULL, '2026-01-13 15:56:54', '2026-01-13 15:58:04'),
(109, 'LEG CAP PLATE', 3, 'master item/yimm/1768294670_SK Cross Cut JIS H.pdf', 'PT. YAMAHA MUSIC MANUFACTURING ASIA', '#3253 ZT71260', '7-65-0002', '[]', NULL, '2026-01-13 15:57:50', '2026-01-13 15:57:50'),
(110, 'LEG CAP PLATED (CHROME)', 3, 'master item/yimm/1768294728_SK Cross Cut JIS H.pdf', 'PT. YAMAHA MOTOR MANUFACTURING ASIA', 'VCK 3330', '7-65-0012', '[]', NULL, '2026-01-13 15:58:48', '2026-01-13 15:58:48'),
(111, 'LEG CAP PLATED', 3, 'master item/yimm/1768294770_SK Cross Cut JIS H.pdf', 'PT. YAMAHA MUSIC MANUFACTURING ASIA', 'VAP 5670', '7-65-0011', '[]', NULL, '2026-01-13 15:59:30', '2026-01-13 15:59:30'),
(112, 'MOULDING RADIATOR GRILLE UPPER', 3, 'master item/others/1768294856_SK Cross Cut TSH.pdf', 'PT. TAKAGI SARI MULTI UTAMA', 'DO2A', '6-12-0099-02', '[]', NULL, '2026-01-13 16:00:56', '2026-01-13 16:00:56'),
(113, 'ORNAMENT GRILLE FRONT L1', 3, 'master item/others/1768295001_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0092-02', '[]', NULL, '2026-01-13 16:03:21', '2026-01-13 16:03:36'),
(114, 'ORNAMENT GRILLE FRONT L10', 3, 'master item/others/1768295052_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0101-02', '[]', NULL, '2026-01-13 16:04:12', '2026-01-13 16:04:12'),
(115, 'ORNAMENT GRILLE FRONT L11', 3, 'master item/others/1768295087_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0102-02', '[]', NULL, '2026-01-13 16:04:47', '2026-01-13 16:04:47'),
(116, 'ORNAMENT GRILLE FRONT L2', 3, 'master item/others/1768295118_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0093-02', '[]', NULL, '2026-01-13 16:05:18', '2026-01-13 16:05:18'),
(117, 'ORNAMENT GRILLE FRONT L3', 3, 'master item/others/1768295282_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0094-02', '[]', NULL, '2026-01-13 16:08:02', '2026-01-13 16:08:02'),
(118, 'ORNAMENT GRILLE FRONT L4', 3, 'master item/others/1768295366_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0095-02', '[]', NULL, '2026-01-13 16:09:26', '2026-01-13 16:09:26'),
(119, 'ORNAMENT GRILLE FRONT L5', 3, 'master item/others/1768295542_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0096-02', '[]', NULL, '2026-01-13 16:12:22', '2026-01-13 16:12:22'),
(120, 'ORNAMENT GRILLE FRONT L6', 3, 'master item/others/1768295764_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0097-02', '[]', NULL, '2026-01-13 16:16:04', '2026-01-13 16:16:04'),
(121, 'ORNAMENT GRILLE FRONT L7', 3, 'master item/others/1768296072_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0098-02', '[]', NULL, '2026-01-13 16:21:12', '2026-01-13 16:21:12'),
(122, 'ORNAMENT GRILLE FRONT L8', 3, 'master item/others/1768296125_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0099-02', '[]', NULL, '2026-01-13 16:22:05', '2026-01-13 16:22:05'),
(123, 'ORNAMENT GRILLE FRONT L9', 3, 'master item/others/1768296174_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0100-02', '[]', NULL, '2026-01-13 16:22:54', '2026-01-13 16:22:54'),
(124, 'ORNAMENT GRILLE FRONT R1', 3, 'master item/others/1768296324_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0081-02', '[]', NULL, '2026-01-13 16:25:24', '2026-01-13 16:25:24'),
(125, 'ORNAMENT GRILLE FRONT R10', 3, 'master item/others/1768296375_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0090-02', '[]', NULL, '2026-01-13 16:26:15', '2026-01-13 16:26:15'),
(126, 'ORNAMENT GRILLE FRONT R11', 3, 'master item/others/1768296480_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0091-02', '[]', NULL, '2026-01-13 16:28:00', '2026-01-13 16:28:00'),
(127, 'ORNAMENT GRILLE FRONT R2', 3, 'master item/others/1768296779_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0082-02', '[]', NULL, '2026-01-13 16:32:59', '2026-01-13 16:32:59'),
(128, 'ORNAMENT GRILLE FRONT R3', 3, 'master item/others/1768296855_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0083-02', '[]', NULL, '2026-01-13 16:34:15', '2026-01-13 16:34:15'),
(129, 'ORNAMENT GRILLE FRONT R4', 3, 'master item/others/1768296932_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0084-02', '[]', NULL, '2026-01-13 16:35:32', '2026-01-13 16:35:32'),
(130, 'ORNAMENT GRILLE FRONT R5', 3, 'master item/others/1768296974_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0085-02', '[]', NULL, '2026-01-13 16:36:14', '2026-01-13 16:36:14'),
(131, 'ORNAMENT GRILLE FRONT R6', 3, 'master item/others/1768297010_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0086-02', '[]', NULL, '2026-01-13 16:36:50', '2026-01-13 16:36:50'),
(132, 'ORNAMENT GRILLE FRONT R7', 3, 'master item/others/1768297092_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0087-02', '[]', NULL, '2026-01-13 16:38:12', '2026-01-13 16:38:12'),
(133, 'ORNAMENT GRILLE FRONT R8', 3, 'master item/others/1768297143_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0088-02', '[]', NULL, '2026-01-13 16:39:03', '2026-01-13 16:39:03'),
(134, 'ORNAMENT GRILLE FRONT R9', 3, 'master item/others/1768297178_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '6-13-0089-02', '[]', NULL, '2026-01-13 16:39:38', '2026-01-13 16:39:38'),
(135, 'ORNAMENT REAR BUMPER L', 3, 'master item/others/1768297264_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '7-13-0079', '[]', NULL, '2026-01-13 16:41:04', '2026-01-13 16:41:04'),
(136, 'ORNAMENT REAR BUMPER R', 3, 'master item/others/1768297300_SK Cross Cut TSH.pdf', 'PT. PRIMA KOMPONEN INDONESIA', '-', '7-13-0080', '[]', NULL, '2026-01-13 16:41:40', '2026-01-13 16:41:40'),
(137, 'PANEL, 1', 3, 'master item/yimm/1768297372_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BEJ-F172A-10', '7-03-0090', '[]', NULL, '2026-01-13 16:42:52', '2026-01-13 16:42:52'),
(138, 'RING METER COVER', 3, 'master item/others/1768297482_SK Cross Cut TSH.pdf', 'PT. INDONESIA NIPPON SEIKI', '5BP', '7-06-0006', '[]', NULL, '2026-01-13 16:44:42', '2026-01-13 16:44:42'),
(139, 'SP RING TW', 3, 'master item/yimm/1768297874_SK Cross Cut JIS H.pdf', 'PT. YAMAHA MUSIC MANUFACTURING INDONESIA', '000106 ZZ01880', '7-65-0008', '[]', NULL, '2026-01-13 16:51:14', '2026-01-13 16:51:14'),
(140, 'SP RING WO', 3, 'master item/yimm/1768297917_SK Cross Cut JIS H.pdf', 'PT. YAMAHA MUSIC MANUFACTURING', '000106 ZZ01850', '7-65-0007', '[]', NULL, '2026-01-13 16:51:57', '2026-01-13 16:51:57'),
(141, 'TUNING FORK MARK, 3D', 3, 'master item/yimm/1768297970_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', '1PA-F836B-10', '6-03-0048-02', '[]', NULL, '2026-01-13 16:52:50', '2026-01-13 16:52:50'),
(142, 'TUNING FORK MARK, 3D', 3, 'master item/yimm/1768298020_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', '1PA-F413B-00', '6-03-0019-02', '[]', NULL, '2026-01-13 16:53:40', '2026-01-13 16:53:40'),
(143, 'TUNING FORK MARK, 3D', 3, 'master item/yimm/1768298124_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', '1WD-F413B-10', '6-03-0025-02', '[]', NULL, '2026-01-13 16:55:24', '2026-01-13 16:55:24'),
(144, 'TUNING FORK MARK, 3D', 3, 'master item/yimm/1768298269_SK Cross Cut JIS H.pdf', 'PT. YAMAHA INDONESIA MOTOR MANUFACTURING', 'BK8-F414B-00', '6-03-0037-02', '[]', NULL, '2026-01-13 16:57:49', '2026-01-13 16:57:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `machine_statuses`
--

CREATE TABLE `machine_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('line','machine') NOT NULL,
  `number` int(11) NOT NULL,
  `status` enum('normal','maintenance','stopped','trouble') DEFAULT 'normal',
  `description` varchar(255) DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `machine_statuses`
--

INSERT INTO `machine_statuses` (`id`, `type`, `number`, `status`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'line', 1, 'normal', NULL, 'Irfan Arfian Kusnadi', '2026-01-12 07:42:55', '2026-01-13 13:23:27'),
(2, 'line', 3, 'normal', NULL, 'Administrator', '2026-01-12 07:43:17', '2026-01-14 07:50:59'),
(3, 'line', 2, 'normal', NULL, 'Arga Yudistira', '2026-01-12 07:44:15', '2026-01-13 03:12:32'),
(4, 'line', 6, 'normal', NULL, 'Administrator', '2026-01-12 07:49:19', '2026-01-14 07:51:04'),
(5, 'line', 12, 'normal', NULL, 'Arga Yudistira', '2026-01-12 07:50:24', '2026-01-13 03:00:34'),
(6, 'line', 14, 'normal', NULL, 'Administrator', '2026-01-12 07:50:40', '2026-01-14 07:51:16'),
(7, 'line', 15, 'normal', NULL, 'Administrator', '2026-01-12 07:50:52', '2026-01-14 07:51:20'),
(8, 'machine', 2, 'trouble', NULL, 'Yono Supriatno', '2026-01-12 08:28:46', '2026-01-14 07:18:56'),
(9, 'machine', 3, 'normal', NULL, 'Yono Supriatno', '2026-01-12 08:28:55', '2026-01-15 07:18:09'),
(10, 'machine', 4, 'stopped', NULL, 'Yono Supriatno', '2026-01-12 08:29:03', '2026-01-15 07:57:32'),
(11, 'machine', 5, 'normal', 'PROD CHR K3V', 'Yono Supriatno', '2026-01-12 08:29:36', '2026-01-12 08:29:36'),
(12, 'machine', 6, 'stopped', NULL, 'Yono Supriatno', '2026-01-12 08:30:01', '2026-01-15 07:57:46'),
(13, 'machine', 7, 'normal', 'PROD ECU A', 'Yono Supriatno', '2026-01-12 08:30:13', '2026-01-13 07:36:09'),
(14, 'machine', 8, 'normal', 'PRODUKSI HANDLE BAR KIT EV', 'Dinar Ashobar', '2026-01-12 08:30:40', '2026-01-14 23:52:06'),
(15, 'machine', 9, 'stopped', NULL, 'Yono Supriatno', '2026-01-12 08:31:09', '2026-01-13 08:03:47'),
(16, 'machine', 10, 'normal', 'PROD PLATE R/L BJM', 'Yono Supriatno', '2026-01-12 08:31:33', '2026-01-12 08:31:33'),
(17, 'machine', 11, 'stopped', 'MESIN NO 11 TIDAK ADA.', 'Yono Supriatno', '2026-01-12 08:32:16', '2026-01-12 12:45:39'),
(18, 'machine', 12, 'normal', NULL, 'Sopian Handani', '2026-01-12 08:33:04', '2026-01-12 17:51:52'),
(19, 'machine', 14, 'normal', NULL, 'Sopian Handani', '2026-01-12 08:33:32', '2026-01-12 17:52:00'),
(20, 'machine', 13, 'stopped', 'TIDAK ADA MESIN NO 13', 'Yono Supriatno', '2026-01-12 08:34:06', '2026-01-12 08:34:06'),
(21, 'machine', 15, 'normal', NULL, 'Dinar Ashobar', '2026-01-12 08:34:28', '2026-01-13 01:49:25'),
(22, 'machine', 16, 'normal', NULL, 'Sopian Handani', '2026-01-12 08:34:46', '2026-01-12 17:52:11'),
(23, 'machine', 17, 'normal', NULL, 'Yono Supriatno', '2026-01-12 08:35:25', '2026-01-13 11:59:58'),
(24, 'machine', 18, 'normal', NULL, 'Dinar Ashobar', '2026-01-12 08:35:48', '2026-01-13 00:21:22'),
(25, 'machine', 19, 'normal', 'PRODUKSI RING TW', 'Dinar Ashobar', '2026-01-12 12:44:37', '2026-01-14 23:51:35'),
(26, 'machine', 1, 'normal', NULL, 'Dinar Ashobar', '2026-01-12 23:50:20', '2026-01-14 01:38:15'),
(27, 'line', 7, 'normal', NULL, 'Arga Yudistira', '2026-01-13 01:17:22', '2026-01-13 01:17:22'),
(28, 'line', 8, 'normal', NULL, 'Arga Yudistira', '2026-01-13 03:06:07', '2026-01-13 03:06:07'),
(29, 'line', 4, 'normal', NULL, 'Arga Yudistira', '2026-01-13 05:46:38', '2026-01-13 05:46:38'),
(30, 'line', 5, 'normal', NULL, 'Arga Yudistira', '2026-01-13 05:53:59', '2026-01-13 05:53:59'),
(31, 'line', 9, 'normal', NULL, 'Arga Yudistira', '2026-01-13 06:11:34', '2026-01-13 06:11:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(14, '2025_12_15_000000_update_role_enum_in_users_table', 1),
(23, '2025_12_30_000001_add_cycle_time_to_cross_cut_checksheets_table', 3),
(24, '2025_12_30_000002_add_approval_columns_to_cross_cut_checksheets_table', 4),
(63, '0001_01_01_000000_create_users_table', 5),
(64, '0001_01_01_000001_create_cache_table', 5),
(65, '0001_01_01_000002_create_jobs_table', 5),
(66, '2023_10_27_000001_create_items_table', 5),
(67, '2023_10_27_000002_create_checksheets_table', 5),
(68, '2025_02_20_000000_add_operator_initials_to_checksheets_table', 5),
(69, '2025_02_24_000000_add_cycle_time_to_checksheets_table', 5),
(70, '2025_05_01_000000_create_in_process_checksheets_table', 5),
(71, '2025_12_12_191808_create_production_reports_table', 5),
(72, '2025_12_12_194441_add_role_to_users_table', 5),
(73, '2025_12_14_105834_rename_description_to_customer_in_items_table', 5),
(74, '2025_12_14_123000_add_approval_status_to_checksheets_table', 5),
(75, '2025_12_14_130000_add_part_number_to_items_table', 5),
(76, '2025_12_20_000000_add_approval_timestamps_to_checksheets_table', 5),
(77, '2025_12_22_000000_drop_image_path_from_items_table', 5),
(78, '2025_12_23_000000_add_file_path_to_items_table', 5),
(79, '2025_12_26_000000_add_defects_to_items_table', 5),
(80, '2025_12_27_000000_update_dimension_check_format_in_in_process_checksheets_table', 5),
(81, '2025_12_28_000000_add_manager_approval_to_checksheets_tables', 5),
(82, '2025_12_29_000000_create_cross_cut_checksheets_table', 5),
(83, '2025_12_29_054325_add_rejection_remarks_to_checksheets_tables', 5),
(84, '2026_01_04_151843_add_dimension_standards_to_items_table', 5),
(85, '2026_01_05_113340_add_defects_and_fields_to_cross_cut_checksheets_table', 5),
(86, '2026_01_07_000001_add_new_roles_to_users_table', 6),
(87, '2026_01_07_000002_add_plating_approval_to_cross_cut_checksheets', 6),
(88, '2026_01_09_101433_add_category_to_items_table', 7),
(89, '2026_01_09_104236_create_categories_table', 7),
(90, '2026_01_09_104316_modify_items_category_to_foreign_key', 7),
(91, '2026_01_09_203350_add_next_proses_to_checksheet_tables', 8),
(92, '2026_01_10_085257_add_line_and_machine_columns', 9),
(93, '2026_01_10_095619_create_machine_statuses_table', 9),
(94, '2026_01_12_000000_create_monthly_reports_table', 10),
(95, '2026_01_12_104035_add_trouble_status_to_machine_statuses_table', 11),
(96, '2026_01_13_082754_add_sap_code_to_items_table', 12);

-- --------------------------------------------------------

--
-- Struktur dari tabel `monthly_reports`
--

CREATE TABLE `monthly_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `monthly_reports`
--

INSERT INTO `monthly_reports` (`id`, `month`, `year`, `title`, `file_path`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 11, 2025, 'PENCAPAIAN PRODUKTIFITAS QUALITY DEPARTEMENT', 'monthly_reports/1768354177_11. Hoshin Kanri Kecepatan Kerja + Overtime November 2025.pdf', 1, 1, '2026-01-14 08:29:37', '2026-01-14 08:29:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `production_reports`
--

CREATE TABLE `production_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_date` date NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `batch_no` varchar(255) NOT NULL,
  `total_produced` int(11) NOT NULL,
  `accepted_qty` int(11) NOT NULL,
  `rejected_qty` int(11) NOT NULL,
  `inspector_name` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3Vr2Zq1cmSXgGWxC9fDoexQ4OXmgHwnBk1rYgjbc', NULL, '2a02:4780:6:c0de::8', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibW9rUlF5SVdIdmxFUklScnI4QjRvQUMxVld6ZDhkWHIyNjZQOEs1TCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vcWMtaW5kb3BsYXQub25saW5lIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1768479704),
('79RCdQqW1AoiC1OLsEevevqXgEJeXiZYIUPPwUQe', 9, '180.244.162.240', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYUlTdWJDbjRZSjFxdnlBUExTb2JkOXgxQXNtNW9hZFpYbUpCcnRPeCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vcWMtaW5kb3BsYXQub25saW5lL2l0ZW1zLzEzL3BkZiI7czo1OiJyb3V0ZSI7czo5OiJpdGVtcy5wZGYiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo5O30=', 1768481723),
('9TEuXwza6E30iGn7SOKhG39IGvLq5QwM8ncSMqHP', NULL, '223.233.70.98', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Vivaldi/5.3.2679.68', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaGpsb2x6YkVLdUgzVWpxWkFqNkt2czBnNUdTOHh4WjJJcElzckJzcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHBzOi8vcWMtaW5kb3BsYXQub25saW5lL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768466521),
('DRKgSxiV7o71Z7GnjqPG16SqbCyIKb4WZ7Vkh1UR', NULL, '180.153.236.131', 'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0; 360Spider', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ3BFcE9yQ1M1NGd4MU1leTBlVGxOUVMxNlJ6M1JmdWpzWjVOWkRtYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vcWMtaW5kb3BsYXQub25saW5lIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1768468344),
('kSnsK1fk5vZuBVFPYHxKXiLM6yBz8y6g4VITqUTK', NULL, '180.153.236.115', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0; 360Spider', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVUJHMnU5TnBHUklSM3hsaERvT1dNR00zMXhzb1ptUDc1NHd2TThtNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnFjLWluZG9wbGF0Lm9ubGluZSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768471637),
('R2oH2CncbaY3lBDb6IKXT2nKBpP1sf7u2QkpQ8F6', 1, '114.4.213.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaDZ2RkE3YjNvTXBURWxZOFlnRG9uNnJIQ2t4aWVlMUxaSXJOaVFqTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTY6Imh0dHBzOi8vcWMtaW5kb3BsYXQub25saW5lL3JlcG9ydC9pbi1wcm9jZXNzLWNoZWNrc2hlZXRzIjtzOjU6InJvdXRlIjtzOjE2OiJpbl9wcm9jZXNzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1768467858);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','supervisor','inspector','kashift','asst_manager','manager','karu_qc','kashift_plating','supervisor_plating','manager_plating') NOT NULL DEFAULT 'inspector',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@qc.com', 'admin', NULL, '$2y$12$20HF9lJUsu0iSPHnqUhnqetF3v6cmbvd3IicDQMwWXu.h4SnEM4Ku', NULL, '2026-01-06 07:31:22', '2026-01-08 07:20:23'),
(2, 'Mida Herdiyani', 'spvqa@qc.com', 'supervisor', NULL, '$2y$12$8WR2VN3nRVmkIi6WmlbFvunHmD4Yo2EHX3xZ7Qdbvz0vsHjwcBqG6', NULL, '2026-01-06 07:31:22', '2026-01-08 07:20:23'),
(3, 'Arief Hidayat', 'spvqc@qc.com', 'supervisor', NULL, '$2y$12$tV02eurWhrDXxb8IF1ZP5.rYcNTxr048K4oSpQNaqXVHwfzGRtpbq', NULL, '2026-01-06 07:31:22', '2026-01-08 07:20:23'),
(4, 'Irfan Arfian Kusnadi', 'irfan@qc.com', 'inspector', NULL, '$2y$12$p/fd5ByQ1/vGfMhm4OM3DuKNh/aT/C3XxKjUDPvDQFxktmZZsn4UG', NULL, '2026-01-06 07:31:22', '2026-01-08 07:20:23'),
(5, 'Anggi Purnama', 'anggi@qc.com', 'inspector', NULL, '$2y$12$AHbkwXJoYowSvoROTr55EO/XEeftcgUejvdEFO8YrS8eefMAcejVO', NULL, '2026-01-06 07:31:23', '2026-01-08 07:20:23'),
(6, 'Gugun Kurniadi', 'gugun@qc.com', 'inspector', NULL, '$2y$12$hmxg4zZPRokEld3.vRFUGuOlawUN8aJ.ME1MqAq6rYA8hgmlWJYGW', NULL, '2026-01-06 07:31:23', '2026-01-08 07:20:24'),
(7, 'Dede Supriyadi', 'dede@qc.com', 'inspector', NULL, '$2y$12$vhuDxYte/KicyBAiFcPaTe8mZBPAhvB9.MObZkKxeNvy8Dxx9bCWS', NULL, '2026-01-06 07:31:23', '2026-01-08 07:20:24'),
(8, 'Arga Yudistira', 'arga@qc.com', 'inspector', NULL, '$2y$12$dXiW5BZh4wwVWRkApGg5VOCCF4LqAh3x3KgT/Wk6iKBhyGTEgBLqe', NULL, '2026-01-06 07:31:23', '2026-01-08 07:20:24'),
(9, 'Sopian Handani', 'sopian@qc.com', 'inspector', NULL, '$2y$12$hlI8F5AM1x5RA8cGCmDf2Oa9f9cxd13nQn43rYvF3dzcIWQskCxz2', NULL, '2026-01-06 07:31:23', '2026-01-08 07:20:24'),
(10, 'Yono Supriatno', 'yono@qc.com', 'inspector', NULL, '$2y$12$.VtZldsZKrsM0aU5ZtNTtuX5xui.yPWoji1oM7WKUSTKyW8jtMds6', NULL, '2026-01-06 07:31:24', '2026-01-08 07:20:24'),
(11, 'Dinar Ashobar', 'dinar@qc.com', 'inspector', NULL, '$2y$12$D6NVia.XfiNv/JOhq60AhO2ApVmrAi8LAmX6RPmKgP.w64/N2GoNW', NULL, '2026-01-06 07:31:24', '2026-01-08 07:20:25'),
(12, 'Ahmad Jaeni', 'kashift@qc.com', 'kashift', NULL, '$2y$12$jc4.vQGSFOocbVtkbVjAGOoWH2USIemy5HVQ9E4AOavgXLVRE.gZi', NULL, '2026-01-06 07:31:24', '2026-01-08 07:20:25'),
(13, 'Iwan Setiawan', 'manager@qc.com', 'asst_manager', NULL, '$2y$12$h6lQ0uUO10iYgzklGVP22uWPUP6TIl8/WHic4etfnmPqQB7TPud5K', NULL, '2026-01-06 07:31:24', '2026-01-08 07:20:25'),
(14, 'Desti Kurniasari', 'generalmanager@qc.com', 'manager', NULL, '$2y$12$z9X6TH1UJX8sd.940LI2oOUntvfSjaujI3eF/AVPsLbTumRJyKKj.', NULL, '2026-01-06 07:31:24', '2026-01-08 07:20:25'),
(15, 'Fitri', 'fitri@qc.com', 'karu_qc', NULL, '$2y$12$n..DG1VgCw4tjaArQifyWutOdYlyPPMfm6WqrDje0QZcceT.r4D9i', NULL, '2026-01-08 07:20:25', '2026-01-08 07:20:25'),
(16, 'Pipit', 'pipit@qc.com', 'karu_qc', NULL, '$2y$12$s7tR70zSIIByac1krevULeoOhHyvGmHoRJf6NUIyuj23GlYueaBoS', NULL, '2026-01-08 07:20:26', '2026-01-08 07:20:26'),
(17, 'Parlinah', 'parlinah@qc.com', 'karu_qc', NULL, '$2y$12$YTFcBHqzST6xvF99bDjVieNcLrIez3GH.FLgJ5mUHlmYX0ZmPWU6y', NULL, '2026-01-08 07:20:26', '2026-01-08 07:20:26'),
(18, 'Kashift Plating', 'kashiftplating@qc.com', 'kashift_plating', NULL, '$2y$12$y3zMubyyuW6mpCCUiiyIb.UID0Z5i6.oC5u5G4UY5NHn8Tw/IS4nW', NULL, '2026-01-08 07:20:26', '2026-01-08 07:20:26'),
(19, 'SPV Plating', 'spvplating@qc.com', 'supervisor_plating', NULL, '$2y$12$MkqSGu3SfuR.sfyi2F8nAeOttq5cgH2pZlgVneF.q5e/WEy47qoz6', NULL, '2026-01-08 07:20:26', '2026-01-08 07:20:26'),
(20, 'Manager Plating', 'managerplating@qc.com', 'manager_plating', NULL, '$2y$12$fvSC4USElELGr6aXt4ZqCedInDgeHIArmAE4yAbY8SN.kCU7qG1tm', NULL, '2026-01-08 07:20:26', '2026-01-08 07:20:26');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_name_unique` (`name`);

--
-- Indeks untuk tabel `checksheets`
--
ALTER TABLE `checksheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checksheets_item_id_foreign` (`item_id`);

--
-- Indeks untuk tabel `cross_cut_checksheets`
--
ALTER TABLE `cross_cut_checksheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cross_cut_checksheets_item_id_foreign` (`item_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `in_process_checksheets`
--
ALTER TABLE `in_process_checksheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `in_process_checksheets_item_id_foreign` (`item_id`);

--
-- Indeks untuk tabel `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `items_sap_code_unique` (`sap_code`),
  ADD KEY `items_category_id_foreign` (`category_id`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `machine_statuses`
--
ALTER TABLE `machine_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `machine_statuses_type_number_unique` (`type`,`number`),
  ADD KEY `machine_statuses_type_index` (`type`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `monthly_reports`
--
ALTER TABLE `monthly_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `monthly_reports_created_by_foreign` (`created_by`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `production_reports`
--
ALTER TABLE `production_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `checksheets`
--
ALTER TABLE `checksheets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT untuk tabel `cross_cut_checksheets`
--
ALTER TABLE `cross_cut_checksheets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `in_process_checksheets`
--
ALTER TABLE `in_process_checksheets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT untuk tabel `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `machine_statuses`
--
ALTER TABLE `machine_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT untuk tabel `monthly_reports`
--
ALTER TABLE `monthly_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `production_reports`
--
ALTER TABLE `production_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `checksheets`
--
ALTER TABLE `checksheets`
  ADD CONSTRAINT `checksheets_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `cross_cut_checksheets`
--
ALTER TABLE `cross_cut_checksheets`
  ADD CONSTRAINT `cross_cut_checksheets_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `in_process_checksheets`
--
ALTER TABLE `in_process_checksheets`
  ADD CONSTRAINT `in_process_checksheets_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `monthly_reports`
--
ALTER TABLE `monthly_reports`
  ADD CONSTRAINT `monthly_reports_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
