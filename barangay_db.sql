-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2026 at 12:59 PM
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
(3, 4, '2026-03-07 15:30:00', 'Barangay Residency Verification', 'scheduled'),
(4, 5, '2026-03-24 20:43:00', 'Barangay Residency Verification', 'scheduled');

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
(1, 2, 4, 'Ace Azcona sa Qith\'s Dorm', 'Banha kaayu sir, permig ungol kag lulu, bahog utot sir kay bulan na way libang2', 'Okay na sir ngayo daw sya pasensya.', 'Resolved', '2026-03-07 08:26:30'),
(2, 2, NULL, 'Rode', 'sigeg tagay banha kaayu rba sir tas wa nay limpyo iyang lote hugaw way panilhig', NULL, 'Pending', '2026-03-22 05:38:11'),
(3, 2, NULL, 'LJ Saavedra', 'Sag asa mo butang basiwa sa coke daghan nag case diri nanga tibulaag kay sag asa ra neya e butang, sahay sa dalan pana.', NULL, 'Pending', '2026-03-22 15:35:56');

-- --------------------------------------------------------

--
-- Table structure for table `developer_profile`
--

CREATE TABLE `developer_profile` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `developer_profile`
--

INSERT INTO `developer_profile` (`id`, `name`, `email`, `address`, `about`, `image`) VALUES
(1, 'Johnie Niel Derubio', 'johniedy2003@gmail.com', 'Aguada, Recto St. Ozamiz City', 'Continue studying in BS information Technology at Northwestern Mindanao State College of Science and Technology', 'dev.png'),
(2, 'Johnie Niel Derubio', 'johniedy2003@gmail.com', 'Aguada, Recto St. Ozamiz City', 'Continue studying in BS information Technology at Northwestern Mindanao State College of Science and Technology', 'dev.png');

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
(47, 1, 'Logged in successfully with 2FA', '2026-03-22 15:41:34'),
(48, 1, 'Logged in successfully with 2FA', '2026-03-24 12:39:33'),
(49, 1, 'Logged in successfully with 2FA', '2026-03-24 12:42:18'),
(50, 1, 'Scheduled residency appointment for user ID 5', '2026-03-24 12:42:53'),
(51, 1, 'Rejected user ID 5', '2026-03-24 12:43:19'),
(52, 1, 'Logged in successfully with 2FA', '2026-03-24 13:56:14'),
(53, 2, 'Logged in successfully with 2FA', '2026-03-24 14:49:24'),
(54, 4, 'Logged in successfully with 2FA', '2026-03-24 14:50:31'),
(55, 2, 'Logged in successfully with 2FA', '2026-03-24 14:52:31'),
(56, 4, 'Logged in successfully with 2FA', '2026-03-27 13:53:41'),
(57, 1, 'Logged in successfully with 2FA', '2026-03-27 13:55:29'),
(58, 4, 'Logged in successfully with 2FA', '2026-03-27 13:58:16'),
(59, 4, 'Logged in successfully with 2FA', '2026-03-27 14:46:56'),
(60, 4, 'Logged in successfully with 2FA', '2026-03-27 14:48:17'),
(61, 4, 'Logged in successfully with 2FA', '2026-03-28 07:59:46'),
(62, 4, 'Resolved complaint ID 1 with comment', '2026-03-28 08:00:13'),
(63, 4, 'Logged in successfully with 2FA', '2026-03-28 08:32:30'),
(64, 4, 'Logged in successfully with 2FA', '2026-03-28 09:50:18'),
(65, 1, 'Logged in successfully with 2FA', '2026-03-28 10:14:48'),
(66, 4, 'Logged in successfully with 2FA', '2026-03-30 09:50:14'),
(67, 4, 'Opened staff dashboard', '2026-03-30 09:50:14'),
(68, 4, 'Opened staff dashboard', '2026-03-30 09:50:31'),
(69, 4, 'Viewed assigned complaints', '2026-03-30 09:50:33'),
(70, 4, 'Opened staff dashboard', '2026-03-30 09:50:35'),
(71, 1, 'Logged in successfully with 2FA', '2026-03-30 09:58:43');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `about` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `lastname`, `email`, `password`, `role`, `residency_status`, `account_status`, `otp_code`, `otp_expiry`, `created_at`, `email_verified`, `verification_token`, `reset_token`, `reset_expiry`, `profile_image`, `address`, `phone`, `about`) VALUES
(1, 'System', 'Administrator', 'admin@barangay.com', '$2y$10$KAMo90XDjDfAEszw8.6BAOZrFGgmH1vli0LZvHRmcyH.WZuDj2F0m', 'admin', 'verified', 'approved', '320008', '2026-03-30 12:02:55', '2026-03-06 06:33:01', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Rj', 'Rj', 'argydy2003@gmail.com', '$2y$10$2SZOth.0mHdCEyfBmXqUquczRAkso6QzhQCyBerMhyPlDdlqxJBEK', 'complainant', 'verified', 'approved', '377311', '2026-03-24 15:57:13', '2026-03-07 06:42:51', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Venzoy', 'Venzoy', 'rjdy2003@gmail.com', '$2y$10$d78SDT.KXvVGq70bcfzZL.sWZktYcaKsIB7Kn09zE2jPEs31zvurO', 'staff', 'pending', 'rejected', NULL, NULL, '2026-03-07 07:06:48', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Arjay', 'Arjay', 'johniedy2003@gmail.com', '$2y$10$Mfw08cTjdFm7vINIhCFxIuMEH4ZxndAZEA.hdW.4nYZQzVOKy43Ta', 'staff', 'verified', 'approved', '550027', '2026-03-30 11:53:57', '2026-03-07 07:28:26', 1, NULL, 'a2ca502c274c3838d5108e93b3d80c7f5448decc04f500d8c0179508fe480a12b4e8e2870b51a98a2bb2818911161f7af1ad', '2026-03-28 11:02:54', 'dev.png', 'Aguada, Recto St. Ozamiz City', '9754629572', 'Third year college student at Northwestern Mindanao State College of Science and Technology.'),
(5, 'Jonah', 'Derubio', 'jonahdyderubio@gmail.com', '$2y$10$VNHK0YldHmZhc0Cl3DaeguLcFP2YRWZ89eozeFXU/d3VWg12s.qey', 'complainant', 'pending', 'rejected', NULL, NULL, '2026-03-22 04:57:50', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'Louie Jay', 'Fortuna', 'louiejay.fortuna@nmsc.edu.ph', '$2y$10$etUiq6u0iEJvjAfjqfPnduAgPa63UdNtIpITwMyVE9u4rsb5nZUVy', 'complainant', 'pending', 'pending', NULL, NULL, '2026-03-24 12:02:20', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'Neil Martin', 'Molina', 'neilmartin.molina@nmsc.edu.ph', '$2y$10$RwprlWHigUmRjXtwc1LKcu65BMU4EGxEqzI68RZPkO2F9/DmV37yC', 'complainant', 'pending', 'pending', NULL, NULL, '2026-03-24 12:53:12', 0, '416eb5ba3a87fb2469396648494e7064', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Argy', 'Derubio', 'derubiojohnie@gmail.com', '$2y$10$QcySt4FZZScXMP0u8GBCZeT0zLd6iL.9eUYE6oV5dPsjN/B7nIo6W', 'staff', 'pending', 'pending', NULL, NULL, '2026-03-24 13:17:41', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
-- Indexes for table `developer_profile`
--
ALTER TABLE `developer_profile`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `developer_profile`
--
ALTER TABLE `developer_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
