-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 01:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `beach_resort`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `room` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `checkout_date` date DEFAULT NULL,
  `guests` int(11) NOT NULL,
  `children` int(11) DEFAULT NULL,
  `nights` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `payment_method` enum('pay-now','face-to-face') DEFAULT NULL,
  `gcash_name` varchar(100) DEFAULT NULL,
  `gcash_number` varchar(20) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted` tinyint(1) DEFAULT 0,
  `photo_filename` varchar(255) DEFAULT NULL,
  `receipt_filename` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_id`, `name`, `email`, `phone`, `room`, `date`, `checkout_date`, `guests`, `children`, `nights`, `total_price`, `status`, `payment_method`, `gcash_name`, `gcash_number`, `payment_reference`, `payment_date`, `timestamp`, `deleted`, `photo_filename`, `receipt_filename`) VALUES
(1, 'RESORT1763658268585', 'Marjhon', 'marjhonmatalog278@gmail.com', '098145289729', 'Premium 838 — ₱7,800', '2025-11-26', '2025-11-26', 3, NULL, NULL, NULL, 'cancelled', NULL, NULL, NULL, NULL, NULL, '2025-11-20 17:04:28', 0, NULL, NULL),
(2, 'RESORT1763658941422', 'Marjhon', 'marjhonmatalog278@gmail.com', '098145289729', 'Heartsuite — ₱11,800', '2025-11-11', '2025-11-11', 2, NULL, NULL, NULL, 'cancelled', NULL, NULL, NULL, NULL, NULL, '2025-11-20 17:15:41', 0, NULL, NULL),
(3, 'RESORT1763659910979', 'Marjhon', 'marjhonmatalog278@gmail.com', '098145289729', 'White House — ₱30,000', '2025-11-10', '2025-11-10', 2, NULL, NULL, NULL, 'cancelled', NULL, NULL, NULL, NULL, NULL, '2025-11-20 17:31:50', 0, NULL, NULL),
(4, 'RESORT1763660941663', 'Marjhon', 'marjhonmatalog278@gmail.com', '098145289729', 'White House — ₱30,000', '2025-11-02', '2025-11-02', 2, NULL, NULL, NULL, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-20 17:49:01', 0, NULL, NULL),
(5, 'RESORT1763663948413', 'Marjhon', 'marjhonmatalog278@gmail.com', '098145289729', 'Beatrice B — ₱6,800', '2025-12-23', '2025-12-23', 4, NULL, NULL, NULL, 'cancelled', NULL, NULL, NULL, NULL, NULL, '2025-11-20 18:39:08', 0, NULL, NULL),
(6, 'RESORT1763664052972', 'Marjhon', 'marjhonmatalog278@gmail.com', '098145289729', 'Giant Kubo — ₱6,800', '2025-12-23', '2025-12-23', 2, NULL, NULL, NULL, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-20 18:40:52', 0, NULL, NULL),
(7, 'RESORT1763709839651', 'Marjhon', 'marjhonmatalog278@gmail.com', '098145289729', 'Beatrice B — ₱6,800', '2025-11-26', '2025-11-26', 2, NULL, NULL, NULL, 'cancelled', NULL, NULL, NULL, NULL, NULL, '2025-11-21 07:23:59', 1, NULL, NULL),
(8, 'RESORT1763739328753', 'KalabawLAmawLAmaw', 'mcpebrinehq@gmail.com', '09789786764565', 'Seaside (Whole) — ₱6,800', '2025-11-25', '2025-11-25', 2, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, '2025-11-21 15:35:28', 1, NULL, NULL),
(9, 'RESORT1763739617807', 'MAHIYAIN AKO123', 'marjhonmatalog@gmail.com', '09789786764565', 'Premium 838 — ₱7,800', '2025-11-26', '2025-11-26', 3, NULL, 1, 7800.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-21 15:40:17', 0, NULL, NULL),
(10, 'RESORT1763739665481', 'MAHIYAIN AKO123', 'marjhonmatalog@yahoo.com', '09789786764565', 'Seaside (Half) — ₱3,400', '2026-01-23', '2026-01-23', 3, NULL, 1, 3400.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-21 15:41:05', 0, NULL, NULL),
(11, 'RESORT1763742333153', 'marjan', 'mcpebrinehq@gmail.com', '0988876', 'Seaside (Half) — ₱3,400', '2026-01-23', '2026-01-23', 2, NULL, 1, 3400.00, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-21 16:25:33', 1, NULL, NULL),
(12, 'RESORT1763745146343', 'marjan', 'MarjanOwie@gmail.com', '09789786764565', 'Seaside (Half) — ₱3,400', '2026-01-24', '2026-01-24', 2, NULL, 1, 3400.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-21 17:12:26', 0, NULL, NULL),
(13, 'RESORT1763746240471', 'Mar Jhon Lowie Matalog', '24-07711@g.batstate-u.edu.ph', '13293137', 'Seaside (Half) — ₱3,400', '2026-01-29', '2026-01-29', 2, NULL, 1, 3400.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-21 17:30:40', 0, NULL, NULL),
(14, 'RESORT1763797971528', 'marjan', 'marjhonmatalog278@gmail.com', '09814529729', 'Beatrice A — ₱7,800', '2025-11-28', '2025-11-28', 2, NULL, 1, 7800.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-22 07:52:51', 0, NULL, NULL),
(15, 'RESORT1763798227682', 'marjan', 'marjhonmatalog278@gmail.com', '09814529729', 'Premium 838 — ₱7,800', '2025-11-27', '2025-11-27', 2, NULL, 1, 7800.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-22 07:57:07', 0, NULL, NULL),
(16, 'RESORT1763798474868', 'marjan', 'marjhonmatalog278@gmail.com', '09814529729', 'Giant Kubo — ₱6,800', '2025-11-26', '2025-11-26', 2, NULL, 1, 6800.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-22 08:01:14', 0, NULL, NULL),
(17, 'RESORT1763798617422', 'ralph', 'bralphlorenz13@gmail.com', '09814529729', 'Premium 838 — ₱7,800', '2025-11-28', '2025-11-28', 2, NULL, 1, 7800.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-22 08:03:37', 0, NULL, NULL),
(18, 'RESORT1763799297748', 'Jhen', 'jenielynmcasas@gmail.com', '09814529729', 'Beatrice B — ₱6,800', '2025-11-30', '2025-11-30', 2, NULL, 1, 6800.00, 'cancelled', NULL, NULL, NULL, NULL, NULL, '2025-11-22 08:14:57', 1, NULL, NULL),
(19, 'RESORT1763802455592', 'Lance Kelly Salcedo', '23-50990@g.batstate-u.edu.ph', '987986656', 'Beatrice A — ₱7,800', '2025-11-23', '2025-11-23', 2, NULL, 1, 7800.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-22 09:07:35', 0, NULL, NULL),
(20, 'RESORT1763813387342', 'LArz', 'marjhonmatalog@yahoo.com', '09789786764565', 'Premium 838 — ₱7,800', '2025-12-12', '2025-12-12', 4, NULL, 3, 23400.00, 'pending', NULL, NULL, NULL, NULL, NULL, '2025-11-22 12:09:47', 1, NULL, NULL),
(21, 'RESORT1763813444816', 'marjan', 'marjhonmatalog278@gmail.com', '09789786764565', 'Beatrice B — ₱6,800', '2025-12-25', '2025-12-25', 2, NULL, 2, 13600.00, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-22 12:10:44', 1, NULL, NULL),
(22, 'RESORT1764088085974', 'Mar Jhon Lowie Matalog_IT-2102_ER', '24-07711@g.batstate-u.edu.ph', '13293137', 'Premium 840 — ₱8,800', '2025-11-25', '2025-11-25', 2, NULL, 1, 8800.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-25 16:28:05', 1, NULL, NULL),
(23, 'RESORT1764089053330', 'Marjhon', 'marjhonmatalog278@gmail.com', '098145289729', 'Seaside (Whole) — ₱6,800', '2025-12-02', '2025-12-02', 2, NULL, 1, 6800.00, 'confirmed', NULL, NULL, NULL, NULL, NULL, '2025-11-25 16:44:13', 1, NULL, NULL),
(24, 'RESORT1764100606862', 'marjan', 'marjhonmatalog278@gmail.com', '09814529729', 'Aqua Class — ₱11,800', '2025-11-28', '2025-11-29', 4, 3, 1, 11800.00, 'confirmed', 'face-to-face', '', '', '', '0000-00-00', '2025-11-25 19:56:46', 1, NULL, NULL),
(25, 'RESORT1764104039151', 'MARJHON', 'marjhonmatalog278@gmail.com', '09814529729', 'Premium 840 — ₱8,800', '2025-11-27', '2025-11-28', 3, 1, 1, 8800.00, 'confirmed', 'face-to-face', '', '', '', '0000-00-00', '2025-11-25 20:53:59', 1, NULL, NULL),
(26, 'RESORT1764140545479', 'Mar Jhon', 'marjhonmatalog278@yahoo.com', '13293137', 'Beatrice A — ₱7,800', '2025-11-29', '2025-11-30', 2, 1, 1, 7800.00, 'confirmed', 'face-to-face', '', '', '', '0000-00-00', '2025-11-26 07:02:25', 1, NULL, NULL),
(27, 'RESORT1764140831644', 'larz', 'ingcolarzbyron15@gmail.com', '763472567428', 'Premium 838 — ₱7,800', '2026-05-14', '2026-05-15', 2, 2, 1, 7800.00, 'confirmed', 'face-to-face', '', '', '', '0000-00-00', '2025-11-26 07:07:11', 0, NULL, NULL),
(28, 'RESORT1764213914804', 'MARJHON', 'marjhonmatalog278@gmail.com', '09814529729', 'stephs-848', '2025-11-28', '2025-11-29', 2, 1, 1, 10800.00, 'confirmed', 'face-to-face', '', '', '', '0000-00-00', '2025-11-27 03:25:14', 0, NULL, NULL),
(29, 'RESORT1764217704600', 'MARJHON', 'marjhonmatalog278@gmail.com', '09814529729', 'stephs-846', '2025-11-28', '2025-11-29', 2, 1, 1, 10000.00, 'pending', 'face-to-face', '', '', '', '0000-00-00', '2025-11-27 04:28:24', 0, NULL, NULL),
(30, 'RESORT1764230476332', 'KalabawLAmawLAmaw', 'mcpebrinehq@gmail.com', '09789786764565', 'premium-838', '2025-11-28', '2025-11-29', 2, 0, 1, 7800.00, 'confirmed', 'pay-now', 'sefesfe', '13123343', '223423423', '2025-11-28', '2025-11-27 08:01:16', 0, NULL, NULL),
(31, 'RESORT1764234344849', 'MARJHON', 'marjhonmatalog278@gmail.com', '09814529729', 'concierge-815-819', '2025-11-28', '2025-11-29', 2, 0, 1, 8800.00, 'pending', 'pay-now', 'mar jhon', '12324345', '131232343543', '2025-11-27', '2025-11-27 09:05:44', 0, NULL, 'RESORT1764234344849_receipt.png'),
(32, 'RESORT1764235796839', 'KalabawLAmawLAmaw', 'marjhonmatalog278@gmail.com', '09789786764565', 'premium-840', '2025-11-28', '2025-11-29', 2, 0, 1, 8800.00, 'pending', 'pay-now', 'sefesfe', '13123343', '1123243245345', '2025-11-27', '2025-11-27 09:29:56', 0, NULL, 'RESORT1764235796839_receipt.png'),
(33, 'RESORT1764240079110', 'marjan', 'marjhonmatalog278@gmail.com', '09814529729', 'heartsuite', '2025-11-28', '2025-11-29', 2, 0, 1, 11800.00, 'pending', 'pay-now', 'mar jhon', '1323123', '321231321', '2025-11-27', '2025-11-27 10:41:19', 0, NULL, 'RESORT1764240079110_receipt.png'),
(34, 'RESORT1764521400976', 'Mar Jan', 'marjhonmatalog278@gmail.com', '09789786764565', 'beatrice-a', '2025-12-02', '2025-12-03', 6, 1, 1, 7800.00, 'confirmed', 'pay-now', 'sefesfe', '13123343', '1123243245345', '2025-12-01', '2025-11-30 16:50:00', 0, NULL, 'RESORT1764521400976_receipt.png'),
(35, 'RESORT1764521595361', 'Lowie', 'marjhonmatalog278@gmail.com', '09789786764565', 'beatrice-a', '2025-12-11', '2025-12-12', 2, 1, 1, 7800.00, 'confirmed', 'pay-now', 'sefesfe', '13123343', '1123243245345', '2025-12-01', '2025-11-30 16:53:15', 0, NULL, 'RESORT1764521595361_receipt.png');

-- --------------------------------------------------------

--
-- Table structure for table `cottage_availability`
--

CREATE TABLE `cottage_availability` (
  `id` int(11) NOT NULL,
  `cottage_name` varchar(255) NOT NULL,
  `booked_date` date NOT NULL,
  `booking_id` varchar(50) NOT NULL,
  `status` enum('confirmed','cancelled') DEFAULT 'confirmed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cottage_availability`
--

INSERT INTO `cottage_availability` (`id`, `cottage_name`, `booked_date`, `booking_id`, `status`, `created_at`) VALUES
(1, 'Seaside (Half) — ₱3,400', '2026-01-23', 'RESORT1763739665481', 'confirmed', '2025-11-21 16:24:49'),
(3, 'Premium 838 — ₱7,800', '2025-11-26', 'RESORT1763739617807', 'confirmed', '2025-11-21 16:35:30'),
(5, 'Seaside (Half) — ₱3,400', '2026-01-24', 'RESORT1763745146343', 'confirmed', '2025-11-21 17:21:09'),
(6, 'Seaside (Half) — ₱3,400', '2026-01-29', 'RESORT1763746240471', 'confirmed', '2025-11-21 17:30:50'),
(7, 'Beatrice A — ₱7,800', '2025-11-28', 'RESORT1763797971528', 'confirmed', '2025-11-22 07:54:37'),
(8, 'Premium 838 — ₱7,800', '2025-11-27', 'RESORT1763798227682', 'confirmed', '2025-11-22 08:00:13'),
(9, 'Premium 838 — ₱7,800', '2025-11-28', 'RESORT1763798617422', 'confirmed', '2025-11-22 08:04:17'),
(11, 'Beatrice A — ₱7,800', '2025-11-23', 'RESORT1763802455592', 'confirmed', '2025-11-22 09:07:45'),
(15, 'Giant Kubo — ₱6,800', '2025-11-26', 'RESORT1763798474868', 'confirmed', '2025-11-22 10:13:17'),
(28, 'White House — ₱30,000', '2025-11-02', 'RESORT1763660941663', 'confirmed', '2025-11-25 20:26:42'),
(32, 'Giant Kubo — ₱6,800', '2025-12-23', 'RESORT1763664052972', 'confirmed', '2025-11-26 07:00:57'),
(35, 'Premium 838 — ₱7,800', '2026-05-14', 'RESORT1764140831644', 'confirmed', '2025-11-26 07:07:28'),
(36, 'stephs-848', '2025-11-28', 'RESORT1764213914804', 'confirmed', '2025-11-27 03:25:31'),
(37, 'premium-838', '2025-11-28', 'RESORT1764230476332', 'confirmed', '2025-11-27 08:57:18'),
(38, 'beatrice-a', '2025-12-02', 'RESORT1764521400976', 'confirmed', '2025-11-30 16:50:40'),
(39, 'beatrice-a', '2025-12-11', 'RESORT1764521595361', 'confirmed', '2025-11-30 16:54:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`);

--
-- Indexes for table `cottage_availability`
--
ALTER TABLE `cottage_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cottage_date` (`cottage_name`,`booked_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `cottage_availability`
--
ALTER TABLE `cottage_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
