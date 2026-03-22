-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2026 at 04:56 PM
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
-- Database: `barangay_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `appointment_date` datetime DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','completed') DEFAULT 'scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `user_id`, `appointment_date`, `purpose`, `status`) VALUES
(1, 3, '2026-03-07 15:10:00', 'Barangay Residency Verification', 'scheduled'),
(2, 4, '2026-03-07 15:30:00', 'Barangay Residency Verification', 'scheduled'),
(3, 4, '2026-03-07 15:30:00', 'Barangay Residency Verification', 'scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `complainant_id` int(11) DEFAULT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `staff_comment` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Resolved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `complainant_id`, `assigned_staff_id`, `subject`, `description`, `staff_comment`, `status`, `created_at`) VALUES
(1, 2, 4, 'Ace Azcona sa Qith\'s Dorm', 'Banha kaayu sir, permig ungol kag lulu, bahog utot sir kay bulan na way libang2', 'Okay sir, anhaon namo sha later and discuss the matters, thank you sa pag submit.', 'Resolved', '2026-03-07 08:26:30'),
(2, 2, NULL, 'Rode', 'sigeg tagay banha kaayu rba sir tas wa nay limpyo iyang lote hugaw way panilhig', NULL, 'Pending', '2026-03-22 05:38:11'),
(3, 2, NULL, 'LJ Saavedra', 'Sag asa mo butang basiwa sa coke daghan nag case diri nanga tibulaag kay sag asa ra neya e butang, sahay sa dalan pana.', NULL, 'Pending', '2026-03-22 15:35:56');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `user_id`, `action`, `log_time`) VALUES
(1, 1, 'Logged in successfully with 2FA', '2026-03-06 06:53:07'),
(2, 1, 'Logged in successfully with 2FA', '2026-03-06 07:29:40'),
(3, 1, 'Logged in successfully with 2FA', '2026-03-06 07:30:37'),
(4, 1, 'Logged in successfully with 2FA', '2026-03-07 06:38:37'),
(5, 1, 'Logged in successfully with 2FA', '2026-03-07 06:43:41'),
(6, 1, 'Approved user ID 2', '2026-03-07 06:43:52'),
(7, 2, 'Logged in successfully with 2FA', '2026-03-07 06:59:03'),
(8, 1, 'Logged in successfully with 2FA', '2026-03-07 07:07:35'),
(9, 1, 'Scheduled residency appointment for user ID 3', '2026-03-07 07:08:42'),
(10, 1, 'Logged in successfully with 2FA', '2026-03-07 07:12:45'),
(11, 1, 'Rejected user ID 3', '2026-03-07 07:25:03'),
(12, 1, 'Logged in successfully with 2FA', '2026-03-07 07:26:03'),
(13, 1, 'Logged in successfully with 2FA', '2026-03-07 07:28:55'),
(14, 1, 'Scheduled residency appointment for user ID 4', '2026-03-07 07:29:54'),
(15, 1, 'Scheduled residency appointment for user ID 4', '2026-03-07 07:29:58'),
(16, 1, 'Approved user ID 4', '2026-03-07 07:31:27'),
(17, 2, 'Logged in successfully with 2FA', '2026-03-07 08:05:17'),
(18, 2, 'Created a complaint', '2026-03-07 08:26:30'),
(19, 4, 'Logged in successfully with 2FA', '2026-03-07 08:27:40'),
(20, 1, 'Logged in successfully with 2FA', '2026-03-07 08:29:09'),
(21, 1, 'Assigned staff to complaint ID 1', '2026-03-07 08:30:05'),
(22, 1, 'Assigned staff to complaint ID 1', '2026-03-07 08:30:10'),
(23, 1, 'Assigned staff to complaint ID 1', '2026-03-07 08:30:51'),
(24, 4, 'Logged in successfully with 2FA', '2026-03-07 08:32:40'),
(25, 2, 'Logged in successfully with 2FA', '2026-03-07 08:34:24'),
(26, 1, 'Logged in successfully with 2FA', '2026-03-21 04:36:17'),
(27, 1, 'Logged in successfully with 2FA', '2026-03-21 05:15:30'),
(28, 4, 'Logged in successfully with 2FA', '2026-03-21 05:17:22'),
(29, 2, 'Logged in successfully with 2FA', '2026-03-21 05:20:00'),
(30, 1, 'Logged in successfully with 2FA', '2026-03-21 05:31:27'),
(31, 1, 'Assigned staff to complaint ID 1', '2026-03-21 05:31:39'),
(32, 4, 'Logged in successfully with 2FA', '2026-03-21 06:04:04'),
(33, 4, 'Resolved complaint ID 1 with comment', '2026-03-21 06:12:06'),
(34, 2, 'Logged in successfully with 2FA', '2026-03-21 06:12:51'),
(35, 4, 'Logged in successfully with 2FA', '2026-03-21 06:18:24'),
(36, 2, 'Logged in successfully with 2FA', '2026-03-21 06:19:19'),
(37, 1, 'Logged in successfully with 2FA', '2026-03-21 06:25:23'),
(38, 1, 'Logged in successfully with 2FA', '2026-03-22 04:32:25'),
(39, 2, 'Logged in successfully with 2FA', '2026-03-22 04:59:51'),
(40, 2, 'Logged in successfully with 2FA', '2026-03-22 05:01:12'),
(41, 1, 'Logged in successfully with 2FA', '2026-03-22 05:05:57'),
(42, 1, 'Logged in successfully with 2FA', '2026-03-22 05:35:12'),
(43, 2, 'Logged in successfully with 2FA', '2026-03-22 05:37:13'),
(44, 2, 'Created a complaint', '2026-03-22 05:38:11'),
(45, 2, 'Logged in successfully with 2FA', '2026-03-22 15:34:37'),
(46, 2, 'Created a complaint', '2026-03-22 15:35:56'),
(47, 1, 'Logged in successfully with 2FA', '2026-03-22 15:41:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','complainant') NOT NULL,
  `residency_status` enum('pending','verified','none') DEFAULT 'pending',
  `account_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `lastname`, `email`, `password`, `role`, `residency_status`, `account_status`, `otp_code`, `otp_expiry`, `created_at`) VALUES
(1, 'System', 'Administrator', 'admin@barangay.com', '$2y$10$KAMo90XDjDfAEszw8.6BAOZrFGgmH1vli0LZvHRmcyH.WZuDj2F0m', 'admin', 'verified', 'approved', '246481', '2026-03-22 16:46:03', '2026-03-06 06:33:01'),
(2, 'Rj', 'Rj', 'argydy2003@gmail.com', '$2y$10$2SZOth.0mHdCEyfBmXqUquczRAkso6QzhQCyBerMhyPlDdlqxJBEK', 'complainant', 'verified', 'approved', '570021', '2026-03-22 16:39:19', '2026-03-07 06:42:51'),
(3, 'Venzoy', 'Venzoy', 'rjdy2003@gmail.com', '$2y$10$d78SDT.KXvVGq70bcfzZL.sWZktYcaKsIB7Kn09zE2jPEs31zvurO', 'staff', 'pending', 'rejected', NULL, NULL, '2026-03-07 07:06:48'),
(4, 'Arjay', 'Arjay', 'johniedy2003@gmail.com', '$2y$10$mYGv6VLR9RNtwPQ1skPU4OrS.X/rusYksyQGbxqIPMNs7t8zaaCqy', 'staff', 'verified', 'approved', '961051', '2026-03-21 07:23:03', '2026-03-07 07:28:26'),
(5, 'Jonah', 'Derubio', 'jonahdyderubio@gmail.com', '$2y$10$VNHK0YldHmZhc0Cl3DaeguLcFP2YRWZ89eozeFXU/d3VWg12s.qey', 'complainant', 'pending', 'pending', NULL, NULL, '2026-03-22 04:57:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `complainant_id` (`complainant_id`),
  ADD KEY `assigned_staff_id` (`assigned_staff_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`complainant_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
