-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2026 at 04:19 PM
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
(4, 5, '2026-03-24 20:43:00', 'Barangay Residency Verification', 'scheduled'),
(5, 10, '2026-04-04 21:46:00', 'Barangay Residency Verification', 'scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `blotter_reports`
--

CREATE TABLE `blotter_reports` (
  `report_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `staff_user_id` int(11) DEFAULT NULL,
  `complainant_user_id` int(11) DEFAULT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `status` enum('awaiting_complainant_signature','signed_by_complainant','submitted_to_admin','approved','rejected') NOT NULL DEFAULT 'awaiting_complainant_signature',
  `report_path` varchar(255) NOT NULL,
  `report_original_name` varchar(255) NOT NULL,
  `report_data` longtext DEFAULT NULL,
  `staff_signature_image` varchar(255) DEFAULT NULL,
  `complainant_signature_image` varchar(255) DEFAULT NULL,
  `admin_signature_image` varchar(255) DEFAULT NULL,
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blotter_reports`
--

INSERT INTO `blotter_reports` (`report_id`, `complaint_id`, `staff_user_id`, `complainant_user_id`, `admin_user_id`, `status`, `report_path`, `report_original_name`, `report_data`, `staff_signature_image`, `complainant_signature_image`, `admin_signature_image`, `admin_remarks`, `created_at`, `updated_at`) VALUES
(1, 53, 4, 2, 1, 'approved', 'uploads/complaint_proofs/blotter_53_4_1779092759.pdf', 'Barangay Blotter Report - CMP-20260518-00053.pdf', NULL, '1779092371_4_arjay_e_signature.jpg', '1779094234_53_2_rj_complainant_e_signature.jpg', '1779092799_1_admin_e_signature.jpg', NULL, '2026-05-18 08:25:59', '2026-05-18 08:53:07'),
(2, 54, 4, 2, 1, 'approved', 'uploads/complaint_proofs/blotter_54_4_1779096515.pdf', 'Barangay Blotter Report - CMP-20260518-00054.pdf', NULL, '1779092371_4_arjay_e_signature.jpg', '1779096583_54_2_rj_complainant_e_signature.jpg', '1779092799_1_admin_e_signature.jpg', NULL, '2026-05-18 09:28:35', '2026-05-18 09:32:26'),
(3, 55, 4, 2, NULL, 'awaiting_complainant_signature', 'uploads/complaint_proofs/blotter_55_4_1779116553.pdf', 'Barangay Blotter Report - CMP-20260518-00055.pdf', '{\"province\":\"Misamis Occidental\",\"city\":\"Tangub\",\"barangay\":\"Labuyo\",\"blotter_no\":\"CMP-20260518-00055\",\"date_filed\":\"May 18, 2026\",\"time_filed\":\"10:58 PM\",\"complainant_name\":\"Rj Derubio\",\"complainant_age\":\"23\",\"complainant_gender\":\"Male\",\"complainant_civil_status\":\"Single\",\"complainant_address\":\"labuyo\",\"complainant_contact\":\"0985736475\",\"respondent_name\":\"Jay Zulieta\",\"respondent_age\":\"25\",\"respondent_gender\":\"Male\",\"respondent_civil_status\":\"Single\",\"respondent_address\":\"labuyo purok-4\",\"respondent_contact\":\"09675856474\",\"incident_date\":\"May 17, 2026\",\"incident_time\":\"9:01 AM\",\"incident_place\":\"100th street avenue, Corner recto st.\",\"complaint_types\":[],\"complaint_type_other\":\"Trespassing\",\"statement_details\":\"Trespassing my property many times, kag if ma paakan sa iro akoa pang sala, sag siya rang ga trespass sakong yuta\",\"requested_actions\":[\"Summon the respondent for mediation\",\"Assist both parties in settling the matter peacefully\"],\"other_action\":\"\",\"witness_name\":\"N\\/A\",\"witness_address\":\"N\\/A\",\"witness_contact\":\"N\\/A\",\"witness_statement\":\"N\\/A\",\"action_date\":\"May 18, 2026\",\"action_remarks\":\"\",\"recorded_by\":\"Arjay\",\"recorded_position\":\"Barangay Secretary \\/ Desk Officer\",\"issued_day\":\"18\",\"issued_month\":\"May\",\"issued_year_suffix\":\"26\",\"prepared_by\":\"Barangay Secretary \\/ Desk Officer\",\"approved_by\":\"Punong Barangay\"}', '1779092371_4_arjay_e_signature.jpg', NULL, NULL, NULL, '2026-05-18 15:02:33', '2026-05-18 15:02:33'),
(4, 56, 4, 2, 1, 'approved', 'uploads/complaint_proofs/blotter_56_4_1779117940.pdf', 'Barangay Blotter Report - CMP-20260518-00056.pdf', '{\"province\":\"Misamis Occidental\",\"city\":\"Tangub\",\"barangay\":\"Labuyo\",\"blotter_no\":\"CMP-20260518-00056\",\"date_filed\":\"May 18, 2026\",\"time_filed\":\"11:22 PM\",\"complainant_name\":\"Rj Derubio\",\"complainant_age\":\"23\",\"complainant_gender\":\"Male\",\"complainant_civil_status\":\"Single\",\"complainant_address\":\"labuyo\",\"complainant_contact\":\"0985736475\",\"respondent_name\":\"Taylor Swift\",\"respondent_age\":\"36\",\"respondent_gender\":\"Female\",\"respondent_civil_status\":\"Single\",\"respondent_address\":\"Labuyo purok-1\",\"respondent_contact\":\"09563452272\",\"incident_date\":\"May 15, 2026\",\"incident_time\":\"11:23 PM\",\"incident_place\":\"Block 5 Lot 50\",\"complaint_types\":[\"Neighborhood Conflict\"],\"complaint_type_other\":\"\",\"statement_details\":\"Mare banha kaayu imo tingog permi nlng patukar karaoke sag gabiing dako\",\"requested_actions\":[\"Summon the respondent for mediation\",\"Assist both parties in settling the matter peacefully\"],\"other_action\":\"\",\"witness_name\":\"Jason Derulo\",\"witness_address\":\"Block 5 Lot 49\",\"witness_contact\":\"08969678565\",\"witness_statement\":\"Silingan sab ko niya sir\\/ma\'am banha jd. Sag gabii na bah. 1 week nana sya permi ingani ka dugay sir\\/ma\'am mo kanta, ga relapse permi.\",\"action_date\":\"\",\"action_remarks\":\"\",\"recorded_by\":\"Arjay\",\"recorded_position\":\"Barangay Secretary \\/ Desk Officer\",\"issued_day\":\"18\",\"issued_month\":\"May\",\"issued_year_suffix\":\"26\",\"prepared_by\":\"Barangay Secretary \\/ Desk Officer\",\"approved_by\":\"Punong Barangay\"}', '1779092371_4_arjay_e_signature.jpg', '1779118017_56_2_rj_complainant_e_signature.jpg', '1779092799_1_admin_e_signature.jpg', NULL, '2026-05-18 15:25:40', '2026-05-18 15:28:50');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `tracking_number` varchar(30) DEFAULT NULL,
  `complainant_id` int(11) DEFAULT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `staff_comment` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Resolved','Cancelled') NOT NULL DEFAULT 'Pending',
  `resolution_confirmation` enum('pending','confirmed','reopened') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `tracking_number`, `complainant_id`, `assigned_staff_id`, `subject`, `description`, `staff_comment`, `status`, `resolution_confirmation`, `created_at`) VALUES
(1, 'CMP-20260307-00001', 2, 4, 'Ace Azcona sa Qith\'s Dorm', 'Banha kaayu sir, permig ungol kag lulu, bahog utot sir kay bulan na way libang2', 'Okay na sir ngayo daw sya pasensya.', 'Resolved', 'confirmed', '2026-03-07 08:26:30'),
(2, 'CMP-20260322-00002', 2, NULL, 'Rode', 'sigeg tagay banha kaayu rba sir tas wa nay limpyo iyang lote hugaw way panilhig', NULL, 'Pending', NULL, '2026-03-22 05:38:11'),
(3, 'CMP-20260322-00003', 2, 4, 'LJ Saavedra', 'Sag asa mo butang basiwa sa coke daghan nag case diri nanga tibulaag kay sag asa ra neya e butang, sahay sa dalan pana.', NULL, 'In Progress', NULL, '2026-03-22 15:35:56'),
(4, 'CMP-20260501-00004', 23, 11, 'Noise Complaint #04', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-01 00:00:00'),
(5, 'CMP-20260502-00005', 24, 12, 'Garbage Collection Delay #05', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-02 01:07:00'),
(6, 'CMP-20260503-00006', 25, 13, 'Blocked Drainage #06', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-03 02:14:00'),
(7, 'CMP-20260504-00007', 26, 14, 'Street Light Repair #07', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-04 03:21:00'),
(8, 'CMP-20260505-00008', 27, 15, 'Water Leakage #08', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-05 04:28:00'),
(9, 'CMP-20260506-00009', 28, 16, 'Animal Nuisance #09', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-06 05:35:00'),
(10, 'CMP-20260507-00010', 29, 17, 'Illegal Parking #10', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-07 06:42:00'),
(11, 'CMP-20260508-00011', 30, 18, 'Public Disturbance #11', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-08 07:49:00'),
(12, 'CMP-20260509-00012', 31, 19, 'Road Obstruction #12', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-09 08:56:00'),
(13, 'CMP-20260510-00013', 32, 20, 'Sanitation Concern #13', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-10 00:03:00'),
(14, 'CMP-20260511-00014', 33, 21, 'Noise Complaint #14', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-11 01:10:00'),
(15, 'CMP-20260512-00015', 34, 22, 'Garbage Collection Delay #15', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-12 02:17:00'),
(16, 'CMP-20260513-00016', 35, 11, 'Blocked Drainage #16', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-13 03:24:00'),
(17, 'CMP-20260514-00017', 36, 12, 'Street Light Repair #17', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-14 04:31:00'),
(18, 'CMP-20260515-00018', 37, 13, 'Water Leakage #18', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-15 05:38:00'),
(19, 'CMP-20260516-00019', 38, 14, 'Animal Nuisance #19', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-16 06:45:00'),
(20, 'CMP-20260517-00020', 39, 15, 'Illegal Parking #20', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-17 07:52:00'),
(21, 'CMP-20260518-00021', 40, 16, 'Public Disturbance #21', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-18 08:59:00'),
(22, 'CMP-20260519-00022', 41, 17, 'Road Obstruction #22', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-19 00:06:00'),
(23, 'CMP-20260520-00023', 42, 18, 'Sanitation Concern #23', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-20 01:13:00'),
(24, 'CMP-20260521-00024', 43, 19, 'Noise Complaint #24', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-21 02:20:00'),
(25, 'CMP-20260522-00025', 44, 20, 'Garbage Collection Delay #25', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-22 03:27:00'),
(26, 'CMP-20260523-00026', 45, 21, 'Blocked Drainage #26', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-23 04:34:00'),
(27, 'CMP-20260524-00027', 46, 22, 'Street Light Repair #27', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-24 05:41:00'),
(28, 'CMP-20260525-00028', 47, 11, 'Water Leakage #28', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-25 06:48:00'),
(29, 'CMP-20260501-00029', 48, 12, 'Animal Nuisance #29', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-01 07:55:00'),
(30, 'CMP-20260502-00030', 49, 13, 'Illegal Parking #30', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-02 08:02:00'),
(31, 'CMP-20260503-00031', 50, 14, 'Public Disturbance #31', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-03 00:09:00'),
(32, 'CMP-20260504-00032', 23, 15, 'Road Obstruction #32', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-04 01:16:00'),
(33, 'CMP-20260505-00033', 24, 16, 'Sanitation Concern #33', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-05 02:23:00'),
(34, 'CMP-20260506-00034', 25, 17, 'Noise Complaint #34', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-06 03:30:00'),
(35, 'CMP-20260507-00035', 26, 18, 'Garbage Collection Delay #35', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-07 04:37:00'),
(36, 'CMP-20260508-00036', 27, 19, 'Blocked Drainage #36', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-08 05:44:00'),
(37, 'CMP-20260509-00037', 28, 20, 'Street Light Repair #37', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-09 06:51:00'),
(38, 'CMP-20260510-00038', 29, 21, 'Water Leakage #38', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-10 07:58:00'),
(39, 'CMP-20260511-00039', 30, 22, 'Animal Nuisance #39', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-11 08:05:00'),
(40, 'CMP-20260512-00040', 31, 11, 'Illegal Parking #40', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-12 00:12:00'),
(41, 'CMP-20260513-00041', 32, 12, 'Public Disturbance #41', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-13 01:19:00'),
(42, 'CMP-20260514-00042', 33, 13, 'Road Obstruction #42', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-14 02:26:00'),
(43, 'CMP-20260515-00043', 34, 14, 'Sanitation Concern #43', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-15 03:33:00'),
(44, 'CMP-20260516-00044', 35, 15, 'Noise Complaint #44', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-16 04:40:00'),
(45, 'CMP-20260517-00045', 36, 16, 'Garbage Collection Delay #45', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-17 05:47:00'),
(46, 'CMP-20260518-00046', 37, 17, 'Blocked Drainage #46', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-18 06:54:00'),
(47, 'CMP-20260519-00047', 38, 18, 'Street Light Repair #47', 'Issue reported by the resident and followed through until closure.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-19 07:01:00'),
(48, 'CMP-20260520-00048', 39, 19, 'Water Leakage #48', 'Resident reported a community concern for documentation and barangay action.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-20 08:08:00'),
(49, 'CMP-20260521-00049', 40, 20, 'Animal Nuisance #49', 'Complaint filed for checking purposes with a realistic complaint timeline.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-21 00:15:00'),
(50, 'CMP-20260522-00050', 41, 21, 'Illegal Parking #50', 'Concern logged for barangay response and case resolution tracking.', 'Concern resolved after barangay validation and follow-up action.', 'Resolved', 'confirmed', '2026-05-22 01:22:00'),
(51, 'CMP-20260518-00051', 2, 4, 'Sagbot', 'Sir/Ma\'am good afternoon, concern lng ko bah kay na tabonan nag sagbot ang dalan didto katunga sa dalan gi sagbot sa corner street halo.', 'Mana sir gi limpyu na namo', 'Resolved', 'pending', '2026-05-17 23:28:45'),
(52, 'CMP-20260518-00052', 2, 4, 'lourence lopez', 'Nakitan sa CCTV sir nangawat, ni sud sa balay, nagkuha malungay pandesal. Ipa blotter nako sir.', NULL, 'In Progress', NULL, '2026-05-18 00:50:10'),
(53, 'CMP-20260518-00053', 2, 4, 'Neil Martin Molina', 'Pa blotter nako siya sir kay nangutang wako bayri', NULL, 'In Progress', NULL, '2026-05-18 08:21:16'),
(54, 'CMP-20260518-00054', 2, 4, 'Renato Capundag', 'Ni sulod balay nanguhag lamas', NULL, 'In Progress', NULL, '2026-05-18 09:25:19'),
(55, 'CMP-20260518-00055', 2, 4, 'Jay Zulieta', 'Trespassing my property many times, kag if ma paakan sa iro akoa pang sala, sag siya rang ga trespass sakong yuta', NULL, 'In Progress', NULL, '2026-05-18 14:30:48'),
(56, 'CMP-20260518-00056', 2, 4, 'Taylor Swift', 'Mare banha kaayu imo tingog permi nlng patukar karaoke sag gabiing dako', 'Already has a blotter report as proof, please be present in the said date for hearing', 'In Progress', NULL, '2026-05-18 15:21:21');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_updates`
--

CREATE TABLE `complaint_updates` (
  `update_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `actor_user_id` int(11) DEFAULT NULL,
  `actor_role` varchar(50) DEFAULT NULL,
  `update_type` varchar(50) DEFAULT NULL,
  `status_snapshot` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `proof_path` varchar(255) DEFAULT NULL,
  `proof_original_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint_updates`
--

INSERT INTO `complaint_updates` (`update_id`, `complaint_id`, `actor_user_id`, `actor_role`, `update_type`, `status_snapshot`, `message`, `proof_path`, `proof_original_name`, `created_at`) VALUES
(1, 1, 2, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-03-07 08:26:30'),
(2, 2, 2, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-03-22 05:38:11'),
(3, 3, 2, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-03-22 15:35:56'),
(4, 3, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned to Arjay Arjay.', NULL, NULL, '2026-04-10 16:54:40'),
(5, 4, 23, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-01 00:00:00'),
(6, 4, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-01 01:00:00'),
(7, 4, 11, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-01 02:00:00'),
(8, 5, 24, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-02 01:07:00'),
(9, 5, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-02 02:07:00'),
(10, 5, 12, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-02 03:07:00'),
(11, 6, 25, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-03 02:14:00'),
(12, 6, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-03 03:14:00'),
(13, 6, 13, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-03 04:14:00'),
(14, 7, 26, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-04 03:21:00'),
(15, 7, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-04 04:21:00'),
(16, 7, 14, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-04 05:21:00'),
(17, 8, 27, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-05 04:28:00'),
(18, 8, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-05 05:28:00'),
(19, 8, 15, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-05 06:28:00'),
(20, 9, 28, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-06 05:35:00'),
(21, 9, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-06 06:35:00'),
(22, 9, 16, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-06 07:35:00'),
(23, 10, 29, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-07 06:42:00'),
(24, 10, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-07 07:42:00'),
(25, 10, 17, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-07 08:42:00'),
(26, 11, 30, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-08 07:49:00'),
(27, 11, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-08 08:49:00'),
(28, 11, 18, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-08 09:49:00'),
(29, 12, 31, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-09 08:56:00'),
(30, 12, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-09 09:56:00'),
(31, 12, 19, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-09 10:56:00'),
(32, 13, 32, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-10 00:03:00'),
(33, 13, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-10 01:03:00'),
(34, 13, 20, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-10 02:03:00'),
(35, 14, 33, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-11 01:10:00'),
(36, 14, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-11 02:10:00'),
(37, 14, 21, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-11 03:10:00'),
(38, 15, 34, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-12 02:17:00'),
(39, 15, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-12 03:17:00'),
(40, 15, 22, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-12 04:17:00'),
(41, 16, 35, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-13 03:24:00'),
(42, 16, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-13 04:24:00'),
(43, 16, 11, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-13 05:24:00'),
(44, 17, 36, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-14 04:31:00'),
(45, 17, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-14 05:31:00'),
(46, 17, 12, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-14 06:31:00'),
(47, 18, 37, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-15 05:38:00'),
(48, 18, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-15 06:38:00'),
(49, 18, 13, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-15 07:38:00'),
(50, 19, 38, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-16 06:45:00'),
(51, 19, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-16 07:45:00'),
(52, 19, 14, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-16 08:45:00'),
(53, 20, 39, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-17 07:52:00'),
(54, 20, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-17 08:52:00'),
(55, 20, 15, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-17 09:52:00'),
(56, 21, 40, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-18 08:59:00'),
(57, 21, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-18 09:59:00'),
(58, 21, 16, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-18 10:59:00'),
(59, 22, 41, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-19 00:06:00'),
(60, 22, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-19 01:06:00'),
(61, 22, 17, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-19 02:06:00'),
(62, 23, 42, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-20 01:13:00'),
(63, 23, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-20 02:13:00'),
(64, 23, 18, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-20 03:13:00'),
(65, 24, 43, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-21 02:20:00'),
(66, 24, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-21 03:20:00'),
(67, 24, 19, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-21 04:20:00'),
(68, 25, 44, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-22 03:27:00'),
(69, 25, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-22 04:27:00'),
(70, 25, 20, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-22 05:27:00'),
(71, 26, 45, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-23 04:34:00'),
(72, 26, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-23 05:34:00'),
(73, 26, 21, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-23 06:34:00'),
(74, 27, 46, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-24 05:41:00'),
(75, 27, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-24 06:41:00'),
(76, 27, 22, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-24 07:41:00'),
(77, 28, 47, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-25 06:48:00'),
(78, 28, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-25 07:48:00'),
(79, 28, 11, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-25 08:48:00'),
(80, 29, 48, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-01 07:55:00'),
(81, 29, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-01 08:55:00'),
(82, 29, 12, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-01 09:55:00'),
(83, 30, 49, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-02 08:02:00'),
(84, 30, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-02 09:02:00'),
(85, 30, 13, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-02 10:02:00'),
(86, 31, 50, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-03 00:09:00'),
(87, 31, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-03 01:09:00'),
(88, 31, 14, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-03 02:09:00'),
(89, 32, 23, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-04 01:16:00'),
(90, 32, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-04 02:16:00'),
(91, 32, 15, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-04 03:16:00'),
(92, 33, 24, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-05 02:23:00'),
(93, 33, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-05 03:23:00'),
(94, 33, 16, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-05 04:23:00'),
(95, 34, 25, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-06 03:30:00'),
(96, 34, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-06 04:30:00'),
(97, 34, 17, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-06 05:30:00'),
(98, 35, 26, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-07 04:37:00'),
(99, 35, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-07 05:37:00'),
(100, 35, 18, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-07 06:37:00'),
(101, 36, 27, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-08 05:44:00'),
(102, 36, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-08 06:44:00'),
(103, 36, 19, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-08 07:44:00'),
(104, 37, 28, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-09 06:51:00'),
(105, 37, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-09 07:51:00'),
(106, 37, 20, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-09 08:51:00'),
(107, 38, 29, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-10 07:58:00'),
(108, 38, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-10 08:58:00'),
(109, 38, 21, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-10 09:58:00'),
(110, 39, 30, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-11 08:05:00'),
(111, 39, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-11 09:05:00'),
(112, 39, 22, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-11 10:05:00'),
(113, 40, 31, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-12 00:12:00'),
(114, 40, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-12 01:12:00'),
(115, 40, 11, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-12 02:12:00'),
(116, 41, 32, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-13 01:19:00'),
(117, 41, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-13 02:19:00'),
(118, 41, 12, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-13 03:19:00'),
(119, 42, 33, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-14 02:26:00'),
(120, 42, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-14 03:26:00'),
(121, 42, 13, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-14 04:26:00'),
(122, 43, 34, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-15 03:33:00'),
(123, 43, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-15 04:33:00'),
(124, 43, 14, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-15 05:33:00'),
(125, 44, 35, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-16 04:40:00'),
(126, 44, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-16 05:40:00'),
(127, 44, 15, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-16 06:40:00'),
(128, 45, 36, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-17 05:47:00'),
(129, 45, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-17 06:47:00'),
(130, 45, 16, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-17 07:47:00'),
(131, 46, 37, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-18 06:54:00'),
(132, 46, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-18 07:54:00'),
(133, 46, 17, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-18 08:54:00'),
(134, 47, 38, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-19 07:01:00'),
(135, 47, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-19 08:01:00'),
(136, 47, 18, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-19 09:01:00'),
(137, 48, 39, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-20 08:08:00'),
(138, 48, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-20 09:08:00'),
(139, 48, 19, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-20 10:08:00'),
(140, 49, 40, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-21 00:15:00'),
(141, 49, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-21 01:15:00'),
(142, 49, 20, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-21 02:15:00'),
(143, 50, 41, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-22 01:22:00'),
(144, 50, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned for staff action.', NULL, NULL, '2026-05-22 02:22:00'),
(145, 50, 21, 'staff', 'resolved', 'Resolved', 'Complaint marked as resolved by assigned staff.', NULL, NULL, '2026-05-22 03:22:00'),
(146, 51, 2, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-17 23:28:45'),
(147, 51, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned to Arjay Rubio.', NULL, NULL, '2026-05-17 23:30:04'),
(148, 51, 4, 'staff', 'resolved', 'Resolved', 'Mana sir gi limpyu na namo', 'uploads/complaint_proofs/1779061255_51_4_street.jpg', 'street.jpg', '2026-05-17 23:40:55'),
(149, 52, 2, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-18 00:50:10'),
(150, 52, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned to Arjay Rubio.', NULL, NULL, '2026-05-18 00:51:22'),
(151, 52, 4, 'staff', 'blotter_report', 'In Progress', 'Barangay blotter / complaint report generated and attached.', NULL, NULL, '2026-05-18 01:03:12'),
(152, 53, 2, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-18 08:21:16'),
(153, 53, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned to Arjay Rubio.', NULL, NULL, '2026-05-18 08:22:18'),
(154, 53, 4, 'staff', 'blotter_report', 'In Progress', 'Barangay blotter / complaint report generated and attached.', NULL, NULL, '2026-05-18 08:25:59'),
(155, 53, 2, 'complainant', 'blotter_signed', 'awaiting_complainant_signature', 'Complainant signed the barangay blotter / complaint report.', NULL, NULL, '2026-05-18 08:50:34'),
(156, 53, 4, 'staff', 'blotter_submitted_to_admin', 'For Admin Review', 'Assigned staff submitted the signed blotter report to admin for review.', NULL, NULL, '2026-05-18 08:52:16'),
(157, 53, 1, 'admin', 'blotter_approved', 'Approved', 'Admin approved the barangay blotter / complaint report.', NULL, NULL, '2026-05-18 08:53:07'),
(158, 54, 2, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-18 09:25:19'),
(159, 54, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned to Arjay Rubio.', NULL, NULL, '2026-05-18 09:25:43'),
(160, 54, 4, 'staff', 'blotter_report', 'In Progress', 'Barangay blotter / complaint report generated and attached.', NULL, NULL, '2026-05-18 09:28:35'),
(161, 54, 2, 'complainant', 'blotter_signed', 'awaiting_complainant_signature', 'Complainant signed the barangay blotter / complaint report.', NULL, NULL, '2026-05-18 09:29:43'),
(162, 54, 4, 'staff', 'blotter_submitted_to_admin', 'For Admin Review', 'Assigned staff submitted the signed blotter report to admin for review.', NULL, NULL, '2026-05-18 09:30:24'),
(163, 54, 1, 'admin', 'blotter_approved', 'Approved', 'Admin approved the barangay blotter / complaint report.', NULL, NULL, '2026-05-18 09:32:26'),
(164, 55, 2, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-18 14:30:48'),
(165, 55, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned to Arjay Rubio.', NULL, NULL, '2026-05-18 14:32:19'),
(174, 55, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned to Arjay Rubio.', NULL, NULL, '2026-05-18 14:33:03'),
(182, 55, 4, 'staff', 'blotter_report', 'In Progress', 'Barangay blotter / complaint report generated and attached.', NULL, NULL, '2026-05-18 15:02:33'),
(183, 56, 2, 'complainant', 'submitted', 'Pending', 'Complaint submitted by complainant.', NULL, NULL, '2026-05-18 15:21:21'),
(184, 56, 1, 'admin', 'assigned', 'In Progress', 'Complaint assigned to Arjay Rubio.', NULL, NULL, '2026-05-18 15:21:58'),
(185, 56, 4, 'staff', 'blotter_report', 'In Progress', 'Barangay blotter / complaint report generated and attached.', NULL, NULL, '2026-05-18 15:25:40'),
(186, 56, 2, 'complainant', 'blotter_signed', 'awaiting_complainant_signature', 'Complainant signed the barangay blotter / complaint report.', NULL, NULL, '2026-05-18 15:26:57'),
(187, 56, 4, 'staff', 'blotter_submitted_to_admin', 'For Admin Review', 'Assigned staff submitted the signed blotter report to admin for review.', NULL, NULL, '2026-05-18 15:27:55'),
(188, 56, 1, 'admin', 'blotter_approved', 'Approved', 'Admin approved the barangay blotter / complaint report.', NULL, NULL, '2026-05-18 15:28:50'),
(189, 56, 4, 'staff', 'progress_update', 'In Progress', 'Already has a blotter report as proof, please be present in the said date for hearing', NULL, NULL, '2026-05-18 17:09:09');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_update_attachments`
--

CREATE TABLE `complaint_update_attachments` (
  `attachment_id` int(11) NOT NULL,
  `update_id` int(11) NOT NULL,
  `stored_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint_update_attachments`
--

INSERT INTO `complaint_update_attachments` (`attachment_id`, `update_id`, `stored_path`, `original_name`, `file_type`, `file_size`, `created_at`) VALUES
(1, 148, 'uploads/complaint_proofs/1779061255_51_4_street.jpg', 'street.jpg', 'jpg', NULL, '2026-05-17 23:40:55'),
(2, 151, 'uploads/complaint_proofs/blotter_52_4_1779066192.pdf', 'Barangay Blotter Report - CMP-20260518-00052.pdf', 'pdf', 8237, '2026-05-18 01:03:12'),
(3, 154, 'uploads/complaint_proofs/blotter_53_4_1779092759.pdf', 'Barangay Blotter Report - CMP-20260518-00053.pdf', 'pdf', 109769, '2026-05-18 08:25:59'),
(4, 154, 'uploads/signatures/1779092371_4_arjay_e_signature.jpg', 'Staff E-Signature - Arjay Rubio', 'jpg', 82906, '2026-05-18 08:25:59'),
(5, 155, 'uploads/blotter_signatures/1779094234_53_2_rj_complainant_e_signature.jpg', 'Complainant E-Signature', 'jpg', 80385, '2026-05-18 08:50:34'),
(6, 157, 'uploads/signatures/1779092799_1_admin_e_signature.jpg', 'Admin E-Signature', 'jpg', 83926, '2026-05-18 08:53:07'),
(7, 160, 'uploads/complaint_proofs/blotter_54_4_1779096515.pdf', 'Barangay Blotter Report - CMP-20260518-00054.pdf', 'pdf', 127087, '2026-05-18 09:28:35'),
(8, 160, 'uploads/signatures/1779092371_4_arjay_e_signature.jpg', 'Staff E-Signature - Arjay Rubio', 'jpg', 82906, '2026-05-18 09:28:35'),
(9, 161, 'uploads/blotter_signatures/1779096583_54_2_rj_complainant_e_signature.jpg', 'Complainant E-Signature', 'jpg', 80385, '2026-05-18 09:29:43'),
(10, 163, 'uploads/signatures/1779092799_1_admin_e_signature.jpg', 'Admin E-Signature', 'jpg', 83926, '2026-05-18 09:32:26'),
(11, 163, 'uploads/complaint_proofs/blotter_54_4_1779096515.pdf', 'Barangay Blotter Report - CMP-20260518-00054.pdf', 'pdf', 127087, '2026-05-18 09:32:26'),
(12, 182, 'uploads/complaint_proofs/blotter_55_4_1779116553.pdf', 'Barangay Blotter Report - CMP-20260518-00055.pdf', 'pdf', 136854, '2026-05-18 15:02:33'),
(13, 182, 'uploads/signatures/1779092371_4_arjay_e_signature.jpg', 'Staff E-Signature - Arjay Rubio', 'jpg', 82906, '2026-05-18 15:02:33'),
(14, 185, 'uploads/complaint_proofs/blotter_56_4_1779117940.pdf', 'Barangay Blotter Report - CMP-20260518-00056.pdf', 'pdf', 136909, '2026-05-18 15:25:40'),
(15, 185, 'uploads/signatures/1779092371_4_arjay_e_signature.jpg', 'Staff E-Signature - Arjay Rubio', 'jpg', 82906, '2026-05-18 15:25:40'),
(16, 186, 'uploads/blotter_signatures/1779118017_56_2_rj_complainant_e_signature.jpg', 'Complainant E-Signature', 'jpg', 80385, '2026-05-18 15:26:57'),
(17, 188, 'uploads/signatures/1779092799_1_admin_e_signature.jpg', 'Admin E-Signature', 'jpg', 83926, '2026-05-18 15:28:50'),
(18, 188, 'uploads/complaint_proofs/blotter_56_4_1779117940.pdf', 'Barangay Blotter Report - CMP-20260518-00056.pdf', 'pdf', 301706, '2026-05-18 15:28:50');

-- --------------------------------------------------------

--
-- Table structure for table `developer_profile`
--

CREATE TABLE `developer_profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `developer_profile`
--

INSERT INTO `developer_profile` (`id`, `user_id`, `name`, `email`, `address`, `about`, `image`) VALUES
(1, 4, 'Johnie Niel Derubio', 'johniedy2003@gmail.com', 'Aguada, Recto St. Ozamiz City', 'Continue studying in BS information Technology at Northwestern Mindanao State College of Science and Technology', 'dev.png');

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
(71, 1, 'Logged in successfully with 2FA', '2026-03-30 09:58:43'),
(72, 1, 'Logged in successfully with 2FA', '2026-04-03 08:47:37'),
(73, 1, 'Logged in successfully with 2FA', '2026-04-04 03:08:37'),
(74, 1, 'Logged in successfully with 2FA', '2026-04-04 04:00:21'),
(75, 9, 'System created default superadmin account', '2026-04-04 05:31:19'),
(76, 9, 'Logged in successfully with 2FA', '2026-04-04 05:33:21'),
(77, 9, 'Logged in successfully with 2FA', '2026-04-04 06:02:36'),
(78, 1, 'Logged in successfully with 2FA', '2026-04-04 07:02:49'),
(79, 1, 'Logged in successfully with 2FA', '2026-04-04 10:41:52'),
(80, 1, 'Approved user ID 10', '2026-04-04 13:41:45'),
(81, 1, 'Updated user ID 10', '2026-04-04 13:44:50'),
(82, 1, 'Scheduled residency appointment for user ID 10', '2026-04-04 13:45:20'),
(83, 1, 'Rejected user ID 10', '2026-04-04 13:47:44'),
(84, 1, 'Updated user ID 10', '2026-04-04 13:48:11'),
(85, 10, 'Logged in successfully with 2FA', '2026-04-04 13:50:12'),
(86, 4, 'Logged in successfully with 2FA', '2026-04-04 13:51:01'),
(87, 4, 'Opened staff dashboard', '2026-04-04 13:51:01'),
(88, 4, 'Viewed assigned complaints', '2026-04-04 13:51:06'),
(89, 4, 'Opened staff dashboard', '2026-04-04 13:51:30'),
(90, 4, 'Viewed assigned complaints', '2026-04-04 13:51:42'),
(91, 3, 'Reset password via email', '2026-04-04 14:00:42'),
(92, 10, 'Logged in successfully with 2FA', '2026-04-04 14:01:39'),
(93, 10, 'Logged in successfully with 2FA', '2026-04-04 14:02:44'),
(94, 10, 'Reset password via email', '2026-04-04 14:03:34'),
(95, 10, 'Logged in successfully with 2FA', '2026-04-04 14:04:09'),
(96, 3, 'Reset password via email', '2026-04-04 14:05:07'),
(97, 10, 'Logged in successfully with 2FA', '2026-04-04 14:05:48'),
(98, 10, 'Logged in successfully with 2FA', '2026-04-04 14:12:39'),
(99, 10, 'Reset password via email', '2026-04-04 14:13:58'),
(100, 10, 'Logged in successfully with 2FA', '2026-04-04 14:14:53'),
(101, 4, 'Logged in successfully with 2FA', '2026-04-04 14:16:06'),
(102, 4, 'Opened staff dashboard', '2026-04-04 14:16:06'),
(103, 4, 'Viewed assigned complaints', '2026-04-04 14:16:10'),
(104, 4, 'Viewed assigned complaints', '2026-04-04 14:16:15'),
(105, 4, 'Opened staff dashboard', '2026-04-04 14:16:17'),
(106, 4, 'Opened staff dashboard', '2026-04-04 14:16:19'),
(107, 4, 'Viewed assigned complaints', '2026-04-04 14:16:20'),
(108, 4, 'Opened staff dashboard', '2026-04-04 14:16:21'),
(109, 10, 'Logged in successfully with 2FA', '2026-04-04 14:16:50'),
(110, 1, 'Logged in successfully with 2FA', '2026-04-04 14:17:33'),
(111, 1, 'Updated user ID 3', '2026-04-04 14:23:14'),
(112, 1, 'Updated user ID 3', '2026-04-04 14:23:31'),
(113, 1, 'Verified residency for user ID 3', '2026-04-04 14:50:42'),
(114, 1, 'Logged in successfully with 2FA', '2026-04-10 15:00:12'),
(115, 1, 'Logged in successfully with 2FA', '2026-04-10 15:11:02'),
(116, 1, 'Logged in successfully with 2FA', '2026-04-10 15:13:16'),
(117, 1, 'Logged in successfully with 2FA', '2026-04-10 15:16:05'),
(118, 2, 'Logged in successfully with 2FA', '2026-04-10 15:42:28'),
(119, 4, 'Logged in successfully with 2FA', '2026-04-10 16:51:34'),
(120, 4, 'Opened staff dashboard', '2026-04-10 16:51:34'),
(121, 4, 'Viewed assigned complaints', '2026-04-10 16:51:37'),
(122, 4, 'Opened staff dashboard', '2026-04-10 16:51:45'),
(123, 4, 'Viewed assigned complaints', '2026-04-10 16:51:48'),
(124, 4, 'Viewed assigned complaints', '2026-04-10 16:51:51'),
(125, 1, 'Logged in successfully with 2FA', '2026-04-10 16:53:30'),
(126, 1, 'Assigned staff to complaint ID 3', '2026-04-10 16:54:40'),
(127, 2, 'Logged in successfully with 2FA', '2026-04-10 16:58:30'),
(128, 4, 'Logged in successfully with 2FA', '2026-04-10 16:59:28'),
(129, 4, 'Opened staff dashboard', '2026-04-10 16:59:28'),
(130, 4, 'Viewed assigned complaints', '2026-04-10 16:59:31'),
(131, 4, 'Viewed assigned complaints', '2026-04-10 17:04:02'),
(132, 4, 'Opened staff dashboard', '2026-04-10 17:07:29'),
(133, 2, 'Logged in successfully with 2FA', '2026-04-10 17:21:01'),
(134, 4, 'Logged in successfully with 2FA', '2026-04-11 10:34:30'),
(135, 4, 'Opened staff dashboard', '2026-04-11 10:34:30'),
(136, 4, 'Viewed assigned complaints', '2026-04-11 10:34:32'),
(137, 4, 'Viewed assigned complaints', '2026-04-11 10:38:08'),
(138, 1, 'Logged in successfully with 2FA', '2026-04-11 10:43:59'),
(139, 2, 'Logged in successfully with 2FA', '2026-04-11 10:45:33'),
(140, 1, 'Logged in successfully', '2026-04-13 01:16:25'),
(141, 10, 'Logged in successfully', '2026-04-13 01:16:36'),
(142, 1, 'Logged in successfully after OTP verification', '2026-04-13 01:36:44'),
(143, 4, 'Reset password via email', '2026-04-13 01:42:31'),
(144, 4, 'Logged in successfully', '2026-04-13 01:43:32'),
(145, 4, 'Opened staff dashboard', '2026-04-13 01:43:32'),
(146, 4, 'Reset password via email', '2026-04-13 01:49:49'),
(147, 4, 'Logged in successfully', '2026-04-13 01:50:11'),
(148, 4, 'Opened staff dashboard', '2026-04-13 01:50:11'),
(149, 2, 'Logged in successfully', '2026-04-17 14:46:33'),
(150, 9, 'Logged in successfully', '2026-04-17 14:52:07'),
(151, 2, 'Logged in successfully', '2026-04-17 16:52:40'),
(152, 4, 'Logged in successfully', '2026-04-17 17:30:08'),
(153, 4, 'Opened staff dashboard', '2026-04-17 17:30:09'),
(154, 4, 'Viewed assigned complaints', '2026-04-17 17:30:11'),
(155, 4, 'Opened staff dashboard', '2026-04-17 17:30:20'),
(156, 4, 'Logged in successfully', '2026-04-17 17:40:30'),
(157, 4, 'Opened staff dashboard', '2026-04-17 17:40:31'),
(158, 4, 'Viewed assigned complaints', '2026-04-17 17:40:32'),
(159, 4, 'Generated printable complaint record for complaint ID 1', '2026-04-17 17:40:36'),
(160, 4, 'Viewed assigned complaints', '2026-04-17 17:41:14'),
(161, 4, 'Generated printable complaint record for complaint ID 3', '2026-04-17 17:41:19'),
(162, 4, 'Viewed assigned complaints', '2026-04-17 17:41:32'),
(163, 1, 'Logged in successfully', '2026-04-17 18:15:05'),
(164, 9, 'Logged in successfully', '2026-04-17 18:15:40'),
(165, 9, 'Logged in successfully', '2026-04-18 14:01:41'),
(166, 1, 'Logged in successfully', '2026-04-18 14:02:16'),
(167, 2, 'Logged in successfully', '2026-04-26 16:33:31'),
(168, 4, 'Reset password via email', '2026-04-26 16:34:52'),
(169, 4, 'Logged in successfully', '2026-04-26 16:35:10'),
(170, 4, 'Opened staff dashboard', '2026-04-26 16:35:11'),
(171, 4, 'Logged in successfully', '2026-04-26 16:35:31'),
(172, 4, 'Opened staff dashboard', '2026-04-26 16:35:31'),
(173, 4, 'Opened staff dashboard', '2026-04-26 16:35:32'),
(174, 4, 'Opened staff dashboard', '2026-04-26 16:35:36'),
(175, 4, 'Viewed assigned complaints', '2026-04-26 16:35:37'),
(176, 4, 'Generated printable complaint record for complaint ID 1', '2026-04-26 16:36:00'),
(177, 4, 'Viewed assigned complaints', '2026-04-26 16:36:17'),
(178, 4, 'Generated printable complaint record for complaint ID 3', '2026-04-26 16:36:20'),
(179, 4, 'Opened staff dashboard', '2026-04-26 16:36:49'),
(180, 4, 'Deleted profile image', '2026-04-26 16:36:53'),
(181, 4, 'Uploaded profile image', '2026-04-26 16:37:28'),
(182, 4, 'Updated profile information', '2026-04-26 16:37:33'),
(183, 4, 'Viewed assigned complaints', '2026-04-26 16:37:36'),
(184, 4, 'Opened staff dashboard', '2026-04-26 16:37:36'),
(185, 4, 'Viewed assigned complaints', '2026-04-26 16:37:37'),
(186, 1, 'Logged in successfully', '2026-04-26 16:37:45'),
(187, 9, 'Logged in successfully', '2026-04-26 16:39:47'),
(188, 1, 'Logged in successfully', '2026-04-26 16:40:24'),
(189, 4, 'Logged in successfully', '2026-04-28 11:50:32'),
(190, 4, 'Opened staff dashboard', '2026-04-28 11:50:33'),
(191, 1, 'Logged in successfully', '2026-04-29 05:19:40'),
(192, 1, 'Logged in successfully', '2026-05-03 03:02:37'),
(193, 1, 'Logged in successfully', '2026-05-03 03:09:45'),
(194, 1, 'Approved user ID 51', '2026-05-03 03:10:25'),
(195, 1, 'Verified residency for user ID 51', '2026-05-03 03:10:48'),
(196, 51, 'Logged in successfully', '2026-05-03 03:14:12'),
(197, 1, 'Logged in successfully', '2026-05-04 05:13:40'),
(198, 1, 'Logged in successfully', '2026-05-06 05:25:16'),
(199, 2, 'Logged in successfully', '2026-05-06 05:53:09'),
(200, 2, 'Logged in successfully', '2026-05-06 05:54:34'),
(201, 2, 'Logged in successfully', '2026-05-06 05:55:06'),
(202, 1, 'Logged in successfully after OTP verification', '2026-05-06 19:52:04'),
(203, 4, 'Logged in successfully', '2026-05-06 19:54:29'),
(204, 4, 'Opened staff dashboard', '2026-05-06 19:54:29'),
(205, 2, 'Logged in successfully', '2026-05-06 19:54:43'),
(206, 9, 'Logged in successfully', '2026-05-06 19:57:34'),
(207, 1, 'Logged in successfully after OTP verification', '2026-05-06 19:59:58'),
(208, 1, 'Logged in successfully', '2026-05-06 20:03:03'),
(209, 2, 'Logged in successfully', '2026-05-06 20:08:17'),
(210, 4, 'Logged in successfully', '2026-05-06 20:08:30'),
(211, 4, 'Opened staff dashboard', '2026-05-06 20:08:30'),
(212, 1, 'Logged in successfully', '2026-05-06 20:08:46'),
(213, 4, 'Logged in successfully', '2026-05-08 18:04:43'),
(214, 4, 'Opened staff dashboard', '2026-05-08 18:04:43'),
(215, 4, 'Logged in successfully', '2026-05-11 12:29:25'),
(216, 4, 'Opened staff dashboard', '2026-05-11 12:29:26'),
(217, 2, 'Logged in successfully', '2026-05-11 12:29:49'),
(218, 1, 'Logged in successfully', '2026-05-11 12:29:59'),
(219, 4, 'Logged in successfully', '2026-05-17 22:03:20'),
(220, 4, 'Opened staff dashboard', '2026-05-17 22:03:20'),
(221, 4, 'Logged in successfully', '2026-05-17 22:58:40'),
(222, 4, 'Opened staff dashboard', '2026-05-17 22:58:40'),
(223, 1, 'Logged in successfully', '2026-05-17 23:07:03'),
(224, 2, 'Logged in successfully', '2026-05-17 23:09:26'),
(225, 4, 'Logged in successfully', '2026-05-17 23:19:39'),
(226, 4, 'Opened staff dashboard', '2026-05-17 23:19:39'),
(227, 4, 'Opened staff dashboard', '2026-05-17 23:19:42'),
(228, 4, 'Opened staff dashboard', '2026-05-17 23:19:44'),
(229, 4, 'Viewed assigned complaints', '2026-05-17 23:19:46'),
(230, 4, 'Generated printable complaint record for complaint ID 3', '2026-05-17 23:20:20'),
(231, 4, 'Viewed assigned complaints', '2026-05-17 23:20:44'),
(232, 4, 'Viewed assigned complaints', '2026-05-17 23:20:55'),
(233, 4, 'Opened staff dashboard', '2026-05-17 23:21:02'),
(234, 2, 'Logged in successfully', '2026-05-17 23:23:41'),
(235, 2, 'Logged in successfully', '2026-05-17 23:26:07'),
(236, 2, 'Logged in successfully', '2026-05-17 23:26:51'),
(237, 2, 'Created complaint CMP-20260518-00051', '2026-05-17 23:28:45'),
(238, 4, 'Logged in successfully', '2026-05-17 23:29:17'),
(239, 4, 'Opened staff dashboard', '2026-05-17 23:29:17'),
(240, 4, 'Viewed assigned complaints', '2026-05-17 23:29:19'),
(241, 1, 'Logged in successfully', '2026-05-17 23:29:38'),
(242, 1, 'Assigned staff to complaint ID 51', '2026-05-17 23:30:04'),
(243, 1, 'Generated printable complaint record for complaint ID 51', '2026-05-17 23:30:14'),
(244, 4, 'Logged in successfully', '2026-05-17 23:32:00'),
(245, 4, 'Opened staff dashboard', '2026-05-17 23:32:00'),
(246, 4, 'Opened staff dashboard', '2026-05-17 23:32:02'),
(247, 4, 'Viewed assigned complaints', '2026-05-17 23:32:03'),
(248, 4, 'Resolved complaint ID 51 and added comment', '2026-05-17 23:40:55'),
(249, 4, 'Viewed assigned complaints', '2026-05-17 23:41:00'),
(250, 2, 'Logged in successfully', '2026-05-17 23:41:25'),
(251, 4, 'Logged in successfully', '2026-05-17 23:43:30'),
(252, 4, 'Opened staff dashboard', '2026-05-17 23:43:30'),
(253, 4, 'Viewed assigned complaints', '2026-05-17 23:43:32'),
(254, 4, 'Viewed assigned complaints', '2026-05-17 23:51:17'),
(255, 2, 'Logged in successfully', '2026-05-17 23:51:42'),
(256, 2, 'Logged in successfully', '2026-05-18 00:00:24'),
(257, 4, 'Logged in successfully', '2026-05-18 00:22:15'),
(258, 4, 'Opened staff dashboard', '2026-05-18 00:22:15'),
(259, 4, 'Viewed assigned complaints', '2026-05-18 00:22:26'),
(260, 4, 'Generated printable complaint record for complaint ID 51', '2026-05-18 00:22:36'),
(261, 4, 'Viewed assigned complaints', '2026-05-18 00:22:53'),
(262, 4, 'Opened staff dashboard', '2026-05-18 00:22:57'),
(263, 4, 'Opened staff dashboard', '2026-05-18 00:22:59'),
(264, 4, 'Viewed assigned complaints', '2026-05-18 00:23:00'),
(265, 4, 'Viewed assigned complaints', '2026-05-18 00:27:23'),
(266, 4, 'Viewed assigned complaints', '2026-05-18 00:27:25'),
(267, 4, 'Viewed assigned complaints', '2026-05-18 00:27:26'),
(268, 4, 'Viewed assigned complaints', '2026-05-18 00:27:32'),
(269, 4, 'Viewed assigned complaints', '2026-05-18 00:27:33'),
(270, 4, 'Viewed assigned complaints', '2026-05-18 00:27:38'),
(271, 4, 'Viewed assigned complaints', '2026-05-18 00:27:40'),
(272, 4, 'Viewed assigned complaints', '2026-05-18 00:27:41'),
(273, 4, 'Viewed assigned complaints', '2026-05-18 00:27:42'),
(274, 4, 'Viewed assigned complaints', '2026-05-18 00:27:43'),
(275, 4, 'Viewed assigned complaints', '2026-05-18 00:27:44'),
(276, 4, 'Viewed assigned complaints', '2026-05-18 00:27:45'),
(277, 4, 'Viewed assigned complaints', '2026-05-18 00:27:46'),
(278, 4, 'Generated printable complaint record for complaint ID 51', '2026-05-18 00:27:59'),
(279, 4, 'Viewed assigned complaints', '2026-05-18 00:28:02'),
(280, 4, 'Logged in successfully', '2026-05-18 00:28:25'),
(281, 4, 'Opened staff dashboard', '2026-05-18 00:28:25'),
(282, 4, 'Viewed assigned complaints', '2026-05-18 00:28:27'),
(283, 1, 'Logged in successfully', '2026-05-18 00:29:23'),
(284, 4, 'Logged in successfully', '2026-05-18 00:44:59'),
(285, 4, 'Opened staff dashboard', '2026-05-18 00:44:59'),
(286, 4, 'Viewed assigned complaints', '2026-05-18 00:45:01'),
(287, 4, 'Viewed assigned complaints', '2026-05-18 00:47:37'),
(288, 2, 'Logged in successfully', '2026-05-18 00:49:22'),
(289, 2, 'Created complaint CMP-20260518-00052', '2026-05-18 00:50:10'),
(290, 2, 'Logged in successfully', '2026-05-18 00:50:41'),
(291, 2, 'Updated complaint ID 52', '2026-05-18 00:50:56'),
(292, 1, 'Logged in successfully', '2026-05-18 00:51:10'),
(293, 1, 'Assigned staff to complaint ID 52', '2026-05-18 00:51:22'),
(294, 4, 'Logged in successfully', '2026-05-18 00:51:39'),
(295, 4, 'Opened staff dashboard', '2026-05-18 00:51:39'),
(296, 4, 'Viewed assigned complaints', '2026-05-18 00:51:50'),
(297, 4, 'Generated barangay blotter report for complaint ID 52', '2026-05-18 01:03:12'),
(298, 4, 'Viewed assigned complaints', '2026-05-18 01:03:12'),
(299, 2, 'Logged in successfully', '2026-05-18 01:03:44'),
(300, 2, 'Logged in successfully', '2026-05-18 01:19:35'),
(301, 4, 'Logged in successfully', '2026-05-18 01:21:06'),
(302, 4, 'Opened staff dashboard', '2026-05-18 01:21:06'),
(303, 4, 'Viewed assigned complaints', '2026-05-18 01:21:08'),
(304, 4, 'Viewed assigned complaints', '2026-05-18 01:23:07'),
(305, 1, 'Logged in successfully', '2026-05-18 01:24:11'),
(306, 4, 'Logged in successfully', '2026-05-18 06:44:49'),
(307, 4, 'Opened staff dashboard', '2026-05-18 06:44:49'),
(308, 4, 'Viewed assigned complaints', '2026-05-18 06:44:52'),
(309, 2, 'Logged in successfully', '2026-05-18 07:53:30'),
(310, 1, 'Logged in successfully', '2026-05-18 08:07:19'),
(311, 4, 'Logged in successfully', '2026-05-18 08:07:53'),
(312, 4, 'Opened staff dashboard', '2026-05-18 08:07:53'),
(313, 4, 'Viewed assigned complaints', '2026-05-18 08:07:55'),
(314, 4, 'Viewed assigned complaints', '2026-05-18 08:09:07'),
(315, 4, 'Logged in successfully', '2026-05-18 08:09:42'),
(316, 4, 'Opened staff dashboard', '2026-05-18 08:09:42'),
(317, 4, 'Viewed assigned complaints', '2026-05-18 08:09:45'),
(318, 4, 'Viewed assigned complaints', '2026-05-18 08:14:49'),
(319, 4, 'Viewed assigned complaints', '2026-05-18 08:14:50'),
(320, 4, 'Viewed assigned complaints', '2026-05-18 08:14:50'),
(321, 2, 'Logged in successfully', '2026-05-18 08:15:09'),
(322, 4, 'Logged in successfully', '2026-05-18 08:15:40'),
(323, 4, 'Opened staff dashboard', '2026-05-18 08:15:40'),
(324, 4, 'Viewed assigned complaints', '2026-05-18 08:15:43'),
(325, 4, 'Uploaded e-signature', '2026-05-18 08:19:31'),
(326, 4, 'Updated profile information', '2026-05-18 08:19:34'),
(327, 4, 'Viewed assigned complaints', '2026-05-18 08:19:38'),
(328, 2, 'Logged in successfully', '2026-05-18 08:19:54'),
(329, 2, 'Created complaint CMP-20260518-00053', '2026-05-18 08:21:16'),
(330, 4, 'Logged in successfully', '2026-05-18 08:21:42'),
(331, 4, 'Opened staff dashboard', '2026-05-18 08:21:42'),
(332, 4, 'Viewed assigned complaints', '2026-05-18 08:21:43'),
(333, 1, 'Logged in successfully', '2026-05-18 08:22:01'),
(334, 1, 'Assigned staff to complaint ID 53', '2026-05-18 08:22:18'),
(335, 4, 'Logged in successfully', '2026-05-18 08:22:49'),
(336, 4, 'Opened staff dashboard', '2026-05-18 08:22:50'),
(337, 4, 'Viewed assigned complaints', '2026-05-18 08:23:40'),
(338, 4, 'Generated barangay blotter report for complaint ID 53', '2026-05-18 08:25:59'),
(339, 4, 'Viewed assigned complaints', '2026-05-18 08:25:59'),
(340, 1, 'Logged in successfully', '2026-05-18 08:26:15'),
(341, 2, 'Logged in successfully', '2026-05-18 08:27:14'),
(342, 4, 'Logged in successfully', '2026-05-18 08:51:07'),
(343, 4, 'Opened staff dashboard', '2026-05-18 08:51:07'),
(344, 4, 'Viewed assigned complaints', '2026-05-18 08:51:11'),
(345, 4, 'Viewed assigned complaints', '2026-05-18 08:52:16'),
(346, 1, 'Logged in successfully', '2026-05-18 08:52:28'),
(347, 1, 'Approved blotter report ID 1 for complaint ID 53', '2026-05-18 08:53:07'),
(348, 4, 'Logged in successfully', '2026-05-18 08:53:23'),
(349, 4, 'Opened staff dashboard', '2026-05-18 08:53:23'),
(350, 4, 'Viewed assigned complaints', '2026-05-18 08:53:24'),
(351, 2, 'Logged in successfully', '2026-05-18 08:53:45'),
(352, 2, 'Logged in successfully', '2026-05-18 09:24:01'),
(353, 2, 'Created complaint CMP-20260518-00054', '2026-05-18 09:25:19'),
(354, 1, 'Logged in successfully', '2026-05-18 09:25:34'),
(355, 1, 'Assigned staff to complaint ID 54', '2026-05-18 09:25:43'),
(356, 4, 'Logged in successfully', '2026-05-18 09:26:03'),
(357, 4, 'Opened staff dashboard', '2026-05-18 09:26:03'),
(358, 4, 'Viewed assigned complaints', '2026-05-18 09:26:04'),
(359, 4, 'Generated barangay blotter report for complaint ID 54', '2026-05-18 09:28:35'),
(360, 4, 'Viewed assigned complaints', '2026-05-18 09:28:35'),
(361, 2, 'Logged in successfully', '2026-05-18 09:28:49'),
(362, 4, 'Logged in successfully', '2026-05-18 09:29:59'),
(363, 4, 'Opened staff dashboard', '2026-05-18 09:30:00'),
(364, 4, 'Viewed assigned complaints', '2026-05-18 09:30:01'),
(365, 4, 'Viewed assigned complaints', '2026-05-18 09:30:23'),
(366, 4, 'Viewed assigned complaints', '2026-05-18 09:30:24'),
(367, 1, 'Logged in successfully', '2026-05-18 09:30:42'),
(368, 1, 'Approved blotter report ID 2 for complaint ID 54', '2026-05-18 09:32:26'),
(369, 4, 'Logged in successfully', '2026-05-18 09:32:39'),
(370, 4, 'Opened staff dashboard', '2026-05-18 09:32:39'),
(371, 4, 'Viewed assigned complaints', '2026-05-18 09:32:40'),
(372, 4, 'Viewed assigned complaints', '2026-05-18 09:32:47'),
(373, 2, 'Logged in successfully', '2026-05-18 09:32:56'),
(374, 2, 'Logged in successfully', '2026-05-18 14:28:58'),
(375, 2, 'Created complaint CMP-20260518-00055', '2026-05-18 14:30:48'),
(376, 4, 'Logged in successfully', '2026-05-18 14:31:11'),
(377, 4, 'Opened staff dashboard', '2026-05-18 14:31:11'),
(378, 4, 'Viewed assigned complaints', '2026-05-18 14:31:14'),
(379, 1, 'Logged in successfully', '2026-05-18 14:32:07'),
(380, 1, 'Assigned staff to complaint ID 55', '2026-05-18 14:32:19'),
(381, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:32:25'),
(382, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:32:29'),
(383, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:32:34'),
(384, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:32:39'),
(385, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:32:44'),
(386, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:32:48'),
(387, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:32:53'),
(388, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:32:58'),
(389, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:33:03'),
(390, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:33:08'),
(391, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:33:13'),
(392, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:33:18'),
(393, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:33:23'),
(394, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:33:27'),
(395, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:33:32'),
(396, 1, 'Updated staff assignment for complaint ID 55', '2026-05-18 14:33:36'),
(397, 2, 'Logged in successfully', '2026-05-18 14:37:29'),
(398, 4, 'Logged in successfully', '2026-05-18 14:58:32'),
(399, 4, 'Opened staff dashboard', '2026-05-18 14:58:32'),
(400, 4, 'Viewed assigned complaints', '2026-05-18 14:58:37'),
(401, 4, 'Viewed assigned complaints', '2026-05-18 14:58:38'),
(402, 4, 'Generated barangay blotter report for complaint ID 55', '2026-05-18 15:02:33'),
(403, 4, 'Viewed assigned complaints', '2026-05-18 15:02:33'),
(404, 1, 'Logged in successfully', '2026-05-18 15:02:49'),
(405, 2, 'Logged in successfully', '2026-05-18 15:20:36'),
(406, 2, 'Created complaint CMP-20260518-00056', '2026-05-18 15:21:21'),
(407, 1, 'Logged in successfully', '2026-05-18 15:21:44'),
(408, 1, 'Generated printable complaint record for complaint ID 56', '2026-05-18 15:21:50'),
(409, 1, 'Assigned staff to complaint ID 56', '2026-05-18 15:21:58'),
(410, 4, 'Logged in successfully', '2026-05-18 15:22:24'),
(411, 4, 'Opened staff dashboard', '2026-05-18 15:22:24'),
(412, 4, 'Viewed assigned complaints', '2026-05-18 15:22:25'),
(413, 4, 'Generated barangay blotter report for complaint ID 56', '2026-05-18 15:25:40'),
(414, 4, 'Viewed assigned complaints', '2026-05-18 15:25:40'),
(415, 2, 'Logged in successfully', '2026-05-18 15:26:01'),
(416, 4, 'Logged in successfully', '2026-05-18 15:27:22'),
(417, 4, 'Opened staff dashboard', '2026-05-18 15:27:23'),
(418, 4, 'Viewed assigned complaints', '2026-05-18 15:27:31'),
(419, 4, 'Viewed assigned complaints', '2026-05-18 15:27:53'),
(420, 4, 'Viewed assigned complaints', '2026-05-18 15:27:55'),
(421, 1, 'Logged in successfully', '2026-05-18 15:28:10'),
(422, 1, 'Approved blotter report ID 4 for complaint ID 56', '2026-05-18 15:28:50'),
(423, 4, 'Logged in successfully', '2026-05-18 15:29:10'),
(424, 4, 'Opened staff dashboard', '2026-05-18 15:29:10'),
(425, 4, 'Viewed assigned complaints', '2026-05-18 15:29:14'),
(426, 4, 'Viewed assigned complaints', '2026-05-18 15:31:25'),
(427, 4, 'Viewed assigned complaints', '2026-05-18 15:31:51'),
(428, 4, 'Viewed assigned complaints', '2026-05-18 15:33:52'),
(429, 4, 'Viewed assigned complaints', '2026-05-18 15:35:34'),
(430, 4, 'Viewed assigned complaints', '2026-05-18 15:42:48'),
(431, 4, 'Viewed assigned complaints', '2026-05-18 15:43:18'),
(432, 4, 'Generated printable complaint record for complaint ID 56', '2026-05-18 15:43:32'),
(433, 4, 'Viewed assigned complaints', '2026-05-18 15:53:01'),
(434, 2, 'Logged in successfully', '2026-05-18 15:53:28'),
(435, 4, 'Logged in successfully', '2026-05-18 15:55:13'),
(436, 4, 'Opened staff dashboard', '2026-05-18 15:55:13'),
(437, 4, 'Viewed assigned complaints', '2026-05-18 15:55:17'),
(438, 4, 'Generated printable complaint record for complaint ID 56', '2026-05-18 15:55:23'),
(439, 4, 'Viewed assigned complaints', '2026-05-18 15:55:30'),
(440, 4, 'Viewed assigned complaints', '2026-05-18 15:56:13'),
(441, 4, 'Viewed assigned complaints', '2026-05-18 15:57:32'),
(442, 2, 'Logged in successfully', '2026-05-18 15:59:38'),
(443, 4, 'Logged in successfully', '2026-05-18 16:08:17'),
(444, 4, 'Opened staff dashboard', '2026-05-18 16:08:17'),
(445, 4, 'Opened staff dashboard', '2026-05-18 16:08:21'),
(446, 4, 'Viewed assigned complaints', '2026-05-18 16:08:22'),
(447, 4, 'Generated printable complaint record for complaint ID 56', '2026-05-18 16:08:50'),
(448, 4, 'Viewed assigned complaints', '2026-05-18 16:08:54'),
(449, 4, 'Generated printable complaint record for complaint ID 56', '2026-05-18 16:09:19'),
(450, 4, 'Viewed assigned complaints', '2026-05-18 16:09:22'),
(451, 2, 'Logged in successfully', '2026-05-18 16:09:47'),
(452, 1, 'Logged in successfully', '2026-05-18 16:10:28'),
(453, 1, 'Generated printable complaint record for complaint ID 56', '2026-05-18 16:10:36'),
(454, 1, 'Generated printable complaint record for complaint ID 56', '2026-05-18 16:10:47'),
(455, 4, 'Logged in successfully', '2026-05-18 16:12:39'),
(456, 4, 'Opened staff dashboard', '2026-05-18 16:12:39'),
(457, 4, 'Viewed assigned complaints', '2026-05-18 16:13:26'),
(458, 4, 'Viewed assigned complaints', '2026-05-18 16:24:44'),
(459, 4, 'Generated printable complaint record for complaint ID 56', '2026-05-18 16:24:48'),
(460, 4, 'Viewed assigned complaints', '2026-05-18 16:27:47'),
(461, 2, 'Logged in successfully', '2026-05-18 16:28:10'),
(462, 4, 'Logged in successfully', '2026-05-18 17:07:28'),
(463, 4, 'Opened staff dashboard', '2026-05-18 17:07:28'),
(464, 4, 'Viewed assigned complaints', '2026-05-18 17:07:30'),
(465, 4, 'Viewed assigned complaints', '2026-05-18 17:07:40'),
(466, 4, 'Opened staff dashboard', '2026-05-18 17:07:42'),
(467, 4, 'Viewed assigned complaints', '2026-05-18 17:08:01'),
(468, 4, 'Updated complaint ID 56 with progress remarks', '2026-05-18 17:09:09'),
(469, 4, 'Viewed assigned complaints', '2026-05-18 17:09:14'),
(470, 2, 'Logged in successfully', '2026-05-18 17:09:28'),
(471, 4, 'Logged in successfully', '2026-05-18 17:16:35'),
(472, 4, 'Opened staff dashboard', '2026-05-18 17:16:35'),
(473, 4, 'Viewed assigned complaints', '2026-05-18 17:16:37'),
(474, 4, 'Generated printable complaint record for complaint ID 56', '2026-05-18 17:17:26'),
(475, 4, 'Viewed assigned complaints', '2026-05-18 17:19:51'),
(476, 1, 'Logged in successfully', '2026-05-18 17:20:08'),
(477, 1, 'Generated printable complaint record for complaint ID 56', '2026-05-18 17:21:17'),
(478, 4, 'Logged in successfully', '2026-05-19 03:10:28'),
(479, 4, 'Opened staff dashboard', '2026-05-19 03:10:28'),
(480, 4, 'Viewed assigned complaints', '2026-05-19 03:10:30'),
(481, 4, 'Viewed assigned complaints', '2026-05-19 03:12:38'),
(482, 4, 'Viewed assigned complaints', '2026-05-19 03:12:39'),
(483, 4, 'Viewed assigned complaints', '2026-05-19 03:12:40'),
(484, 4, 'Viewed assigned complaints', '2026-05-19 03:12:42'),
(485, 4, 'Viewed assigned complaints', '2026-05-19 03:12:44'),
(486, 2, 'Logged in successfully', '2026-05-19 03:13:05'),
(487, 1, 'Logged in successfully', '2026-05-19 03:13:38'),
(488, 4, 'Logged in successfully', '2026-05-19 03:14:50'),
(489, 4, 'Opened staff dashboard', '2026-05-19 03:14:50'),
(490, 4, 'Viewed assigned complaints', '2026-05-19 03:14:54'),
(491, 4, 'Generated printable complaint record for complaint ID 56', '2026-05-19 03:14:56'),
(492, 4, 'Viewed assigned complaints', '2026-05-19 03:15:38'),
(493, 4, 'Viewed assigned complaints', '2026-05-19 03:19:01'),
(494, 4, 'Viewed assigned complaints', '2026-05-19 03:20:59'),
(495, 27, 'Logged in successfully', '2026-05-20 11:53:18'),
(496, 2, 'Logged in successfully', '2026-05-20 12:08:03'),
(497, 11, 'Logged in successfully', '2026-05-20 12:23:03'),
(498, 11, 'Opened staff dashboard', '2026-05-20 12:23:03'),
(499, 11, 'Viewed assigned complaints', '2026-05-20 12:23:07'),
(500, 11, 'Opened staff dashboard', '2026-05-20 12:23:10');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `reset_token`, `reset_expiry`) VALUES
(1, 1, NULL, NULL),
(2, 2, NULL, NULL),
(5, 5, NULL, NULL),
(6, 6, NULL, NULL),
(7, 7, NULL, NULL),
(8, 8, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `residency`
--

CREATE TABLE `residency` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','verified','none') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residency`
--

INSERT INTO `residency` (`id`, `user_id`, `status`) VALUES
(1, 1, 'verified'),
(2, 2, 'verified'),
(3, 3, 'verified'),
(4, 4, 'verified'),
(5, 5, 'pending'),
(6, 6, 'pending'),
(7, 7, 'pending'),
(8, 8, 'pending'),
(9, 9, 'verified'),
(10, 10, 'verified'),
(11, 11, 'verified'),
(12, 12, 'verified'),
(13, 13, 'verified'),
(14, 14, 'verified'),
(15, 15, 'verified'),
(16, 16, 'verified'),
(17, 17, 'verified'),
(18, 18, 'verified'),
(19, 19, 'verified'),
(20, 20, 'verified'),
(21, 21, 'verified'),
(22, 22, 'verified'),
(23, 23, 'verified'),
(24, 24, 'verified'),
(25, 25, 'verified'),
(26, 26, 'verified'),
(27, 27, 'verified'),
(28, 28, 'verified'),
(29, 29, 'verified'),
(30, 30, 'verified'),
(31, 31, 'verified'),
(32, 32, 'verified'),
(33, 33, 'verified'),
(34, 34, 'verified'),
(35, 35, 'verified'),
(36, 36, 'verified'),
(37, 37, 'verified'),
(38, 38, 'verified'),
(39, 39, 'verified'),
(40, 40, 'verified'),
(41, 41, 'verified'),
(42, 42, 'verified'),
(43, 43, 'verified'),
(44, 44, 'verified'),
(45, 45, 'verified'),
(46, 46, 'verified'),
(47, 47, 'verified'),
(48, 48, 'verified'),
(49, 49, 'verified'),
(50, 50, 'verified'),
(51, 51, 'verified');

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
  `role` enum('superadmin','admin','staff','complainant') NOT NULL,
  `account_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `lastname`, `email`, `password`, `role`, `account_status`, `created_at`) VALUES
(1, 'System', 'Administrator', 'admin@barangay.com', '$2y$10$KAMo90XDjDfAEszw8.6BAOZrFGgmH1vli0LZvHRmcyH.WZuDj2F0m', 'admin', 'approved', '2026-03-06 06:33:01'),
(2, 'Rj', 'Derubio', 'argydy2003@gmail.com', '$2y$10$2SZOth.0mHdCEyfBmXqUquczRAkso6QzhQCyBerMhyPlDdlqxJBEK', 'complainant', 'approved', '2026-03-07 06:42:51'),
(3, 'Venzoy', 'Teyol', 'rjdy2003@gmail.com', '$2y$10$ErfbD8D.jQBrQ5rDa6gpE./vo3lYrUqnGlUwzZDTNL428IBStCDdG', 'staff', 'approved', '2026-03-07 07:06:48'),
(4, 'Arjay', 'Rubio', 'johniedy2003@gmail.com', '$2y$10$eNhAUtVPVwzC9bnzMJ1Spu/8cBDTfvHayRUhnIclbX4ar4aCbEcby', 'staff', 'approved', '2026-03-07 07:28:26'),
(5, 'Jonah', 'Derubio', 'jonahdyderubio@gmail.com', '$2y$10$VNHK0YldHmZhc0Cl3DaeguLcFP2YRWZ89eozeFXU/d3VWg12s.qey', 'complainant', 'rejected', '2026-03-22 04:57:50'),
(6, 'Louie Jay', 'Fortuna', 'louiejay.fortuna@nmsc.edu.ph', '$2y$10$etUiq6u0iEJvjAfjqfPnduAgPa63UdNtIpITwMyVE9u4rsb5nZUVy', 'complainant', 'pending', '2026-03-24 12:02:20'),
(7, 'Neil Martin', 'Molina', 'neilmartin.molina@nmsc.edu.ph', '$2y$10$RwprlWHigUmRjXtwc1LKcu65BMU4EGxEqzI68RZPkO2F9/DmV37yC', 'complainant', 'pending', '2026-03-24 12:53:12'),
(8, 'Argy', 'Derubio', 'derubiojohnie@gmail.com', '$2y$10$QcySt4FZZScXMP0u8GBCZeT0zLd6iL.9eUYE6oV5dPsjN/B7nIo6W', 'staff', 'pending', '2026-03-24 13:17:41'),
(9, 'Super', 'Admin', 'superadmin@barangay.com', '$2y$10$nJNLeJ.dzBiH7DmgD/z6oe/eFjhffW1QPGn3kAwVsLS6ihjuMUUM2', 'superadmin', 'approved', '2026-04-04 05:31:19'),
(10, 'Kennard', 'Derubio', 'kennarddyderubio@gmail.com', '$2y$10$TXDJPXMR7N0QxZZlrsAYU.P6sQJFTeZt6EF0dsbC2OWsngLVmxTeS', 'complainant', 'approved', '2026-04-04 10:39:57'),
(11, 'Aira', 'Santos', 'staff04.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(12, 'John Paul', 'Reyes', 'staff05.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(13, 'Maricel', 'Flores', 'staff06.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(14, 'Kevin', 'Mendoza', 'staff07.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(15, 'Liza', 'Garcia', 'staff08.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(16, 'Carlo', 'Bautista', 'staff09.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(17, 'Rhea', 'Morales', 'staff10.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(18, 'Nathan', 'Villanueva', 'staff11.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(19, 'Joy', 'Ramirez', 'staff12.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(20, 'Mark', 'Aquino', 'staff13.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(21, 'Angela', 'Torres', 'staff14.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(22, 'Paolo', 'Castro', 'staff15.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'staff', 'approved', '2026-04-26 15:50:26'),
(23, 'Mae', 'Domingo', 'complainant06.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(24, 'Rico', 'Fernandez', 'complainant07.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(25, 'Alyssa', 'Navarro', 'complainant08.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(26, 'Bryan', 'Dela Cruz', 'complainant09.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(27, 'Shane', 'Salazar', 'complainant10.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(28, 'Jomar', 'Gonzales', 'complainant11.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(29, 'Hazel', 'Rivera', 'complainant12.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(30, 'Elijah', 'Pascual', 'complainant13.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(31, 'Diane', 'Lopez', 'complainant14.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(32, 'Kurt', 'Abad', 'complainant15.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(33, 'Rose', 'Natividad', 'complainant16.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(34, 'Jerome', 'Tolentino', 'complainant17.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(35, 'Celine', 'Mercado', 'complainant18.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(36, 'Joshua', 'Serrano', 'complainant19.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(37, 'Faith', 'Valencia', 'complainant20.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(38, 'Adrian', 'Rosales', 'complainant21.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(39, 'Mica', 'Alvarez', 'complainant22.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(40, 'Reynan', 'Cortez', 'complainant23.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(41, 'Nicole', 'Bernardo', 'complainant24.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(42, 'Ethan', 'Galang', 'complainant25.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(43, 'Patricia', 'Ocampo', 'complainant26.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(44, 'Lester', 'Manalo', 'complainant27.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(45, 'Trisha', 'Soriano', 'complainant28.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(46, 'Gian', 'Padilla', 'complainant29.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(47, 'Ella', 'Maranan', 'complainant30.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(48, 'Ralph', 'Estrella', 'complainant31.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(49, 'Kaye', 'Pineda', 'complainant32.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(50, 'Vincent', 'Malabanan', 'complainant33.may2026@barangay.demo', '$2y$10$YRwXnYeOaVE6cm9SV2EAbOBbkVXd9BNEqM75g87oLRSP9pD00aGne', 'complainant', 'approved', '2026-04-26 15:50:26'),
(51, 'Hanoj', 'Dy', 'hanojdy@gmail.com', '$2y$10$iWwNG7fVVJx6t/nFztXoQe.VnqcIDzEqC8BC49gjhoHvTmd1tft8K', 'complainant', 'approved', '2026-05-03 03:09:04');

-- --------------------------------------------------------

--
-- Table structure for table `user_auth`
--

CREATE TABLE `user_auth` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `require_otp_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_auth`
--

INSERT INTO `user_auth` (`id`, `user_id`, `email_verified`, `verification_token`, `otp_code`, `otp_expiry`, `failed_login_attempts`, `require_otp_until`) VALUES
(1, 1, 1, NULL, NULL, NULL, 0, NULL),
(2, 2, 1, NULL, NULL, NULL, 0, NULL),
(3, 3, 0, NULL, NULL, NULL, 0, NULL),
(4, 4, 1, NULL, NULL, NULL, 0, NULL),
(5, 5, 0, NULL, NULL, NULL, 0, NULL),
(6, 6, 0, NULL, NULL, NULL, 0, NULL),
(7, 7, 0, '416eb5ba3a87fb2469396648494e7064', NULL, NULL, 0, NULL),
(8, 8, 1, NULL, NULL, NULL, 0, NULL),
(9, 9, 1, NULL, NULL, NULL, 0, NULL),
(10, 10, 1, NULL, NULL, NULL, 0, NULL),
(11, 11, 1, NULL, NULL, NULL, 0, NULL),
(12, 12, 1, NULL, NULL, NULL, 0, NULL),
(13, 13, 1, NULL, NULL, NULL, 0, NULL),
(14, 14, 1, NULL, NULL, NULL, 0, NULL),
(15, 15, 1, NULL, NULL, NULL, 0, NULL),
(16, 16, 1, NULL, NULL, NULL, 0, NULL),
(17, 17, 1, NULL, NULL, NULL, 0, NULL),
(18, 18, 1, NULL, NULL, NULL, 0, NULL),
(19, 19, 1, NULL, NULL, NULL, 0, NULL),
(20, 20, 1, NULL, NULL, NULL, 0, NULL),
(21, 21, 1, NULL, NULL, NULL, 0, NULL),
(22, 22, 1, NULL, NULL, NULL, 0, NULL),
(23, 23, 1, NULL, NULL, NULL, 0, NULL),
(24, 24, 1, NULL, NULL, NULL, 0, NULL),
(25, 25, 1, NULL, NULL, NULL, 0, NULL),
(26, 26, 1, NULL, NULL, NULL, 0, NULL),
(27, 27, 1, NULL, NULL, NULL, 0, NULL),
(28, 28, 1, NULL, NULL, NULL, 0, NULL),
(29, 29, 1, NULL, NULL, NULL, 0, NULL),
(30, 30, 1, NULL, NULL, NULL, 0, NULL),
(31, 31, 1, NULL, NULL, NULL, 0, NULL),
(32, 32, 1, NULL, NULL, NULL, 0, NULL),
(33, 33, 1, NULL, NULL, NULL, 0, NULL),
(34, 34, 1, NULL, NULL, NULL, 0, NULL),
(35, 35, 1, NULL, NULL, NULL, 0, NULL),
(36, 36, 1, NULL, NULL, NULL, 0, NULL),
(37, 37, 1, NULL, NULL, NULL, 0, NULL),
(38, 38, 1, NULL, NULL, NULL, 0, NULL),
(39, 39, 1, NULL, NULL, NULL, 0, NULL),
(40, 40, 1, NULL, NULL, NULL, 0, NULL),
(41, 41, 1, NULL, NULL, NULL, 0, NULL),
(42, 42, 1, NULL, NULL, NULL, 0, NULL),
(43, 43, 1, NULL, NULL, NULL, 0, NULL),
(44, 44, 1, NULL, NULL, NULL, 0, NULL),
(45, 45, 1, NULL, NULL, NULL, 0, NULL),
(46, 46, 1, NULL, NULL, NULL, 0, NULL),
(47, 47, 1, NULL, NULL, NULL, 0, NULL),
(48, 48, 1, NULL, NULL, NULL, 0, NULL),
(49, 49, 1, NULL, NULL, NULL, 0, NULL),
(50, 50, 1, NULL, NULL, NULL, 0, NULL),
(51, 51, 1, NULL, NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `age` int(3) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `signature_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `address`, `phone`, `age`, `gender`, `civil_status`, `about`, `profile_image`, `signature_image`) VALUES
(1, 1, 'Aguada, Recto St. Ozamiz City', '9754629572', 44, 'Male', 'Married', 'kapitan 3 straight wins', '457481135_1600428444152250_4082727828674291578_n-ai-brush-removebg-b6mkcqom.png', '1779092799_1_admin_e_signature.jpg'),
(2, 2, 'labuyo', '0985736475', 23, 'Male', 'Single', 'Gwapo', 'aceWnoChain.jpg', NULL),
(3, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 4, 'Aguada, Recto St. Ozamiz City', '9754629572', NULL, '', '', 'Third year college student at Northwestern Mindanao State College of Science and Technology.', '1777221448_developer.png', '1779092371_4_arjay_e_signature.jpg'),
(5, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 11, 'Purok 5, Barangay Aguada, Ozamiz City', '09170000011', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(12, 12, 'Purok 6, Barangay Aguada, Ozamiz City', '09170000012', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(13, 13, 'Purok 7, Barangay Aguada, Ozamiz City', '09170000013', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(14, 14, 'Purok 1, Barangay Aguada, Ozamiz City', '09170000014', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(15, 15, 'Purok 2, Barangay Aguada, Ozamiz City', '09170000015', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(16, 16, 'Purok 3, Barangay Aguada, Ozamiz City', '09170000016', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(17, 17, 'Purok 4, Barangay Aguada, Ozamiz City', '09170000017', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(18, 18, 'Purok 5, Barangay Aguada, Ozamiz City', '09170000018', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(19, 19, 'Purok 6, Barangay Aguada, Ozamiz City', '09170000019', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(20, 20, 'Purok 7, Barangay Aguada, Ozamiz City', '09170000020', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(21, 21, 'Purok 1, Barangay Aguada, Ozamiz City', '09170000021', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(22, 22, 'Purok 2, Barangay Aguada, Ozamiz City', '09170000022', NULL, NULL, NULL, 'Staff demo account added to cloned checking database.', NULL, NULL),
(23, 23, 'Purok 3, Barangay Aguada, Ozamiz City', '09170000023', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(24, 24, 'Purok 4, Barangay Aguada, Ozamiz City', '09170000024', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(25, 25, 'Purok 5, Barangay Aguada, Ozamiz City', '09170000025', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(26, 26, 'Purok 6, Barangay Aguada, Ozamiz City', '09170000026', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(27, 27, 'Purok 7, Barangay Aguada, Ozamiz City', '09170000027', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(28, 28, 'Purok 1, Barangay Aguada, Ozamiz City', '09170000028', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(29, 29, 'Purok 2, Barangay Aguada, Ozamiz City', '09170000029', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(30, 30, 'Purok 3, Barangay Aguada, Ozamiz City', '09170000030', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(31, 31, 'Purok 4, Barangay Aguada, Ozamiz City', '09170000031', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(32, 32, 'Purok 5, Barangay Aguada, Ozamiz City', '09170000032', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(33, 33, 'Purok 6, Barangay Aguada, Ozamiz City', '09170000033', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(34, 34, 'Purok 7, Barangay Aguada, Ozamiz City', '09170000034', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(35, 35, 'Purok 1, Barangay Aguada, Ozamiz City', '09170000035', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(36, 36, 'Purok 2, Barangay Aguada, Ozamiz City', '09170000036', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(37, 37, 'Purok 3, Barangay Aguada, Ozamiz City', '09170000037', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(38, 38, 'Purok 4, Barangay Aguada, Ozamiz City', '09170000038', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(39, 39, 'Purok 5, Barangay Aguada, Ozamiz City', '09170000039', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(40, 40, 'Purok 6, Barangay Aguada, Ozamiz City', '09170000040', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(41, 41, 'Purok 7, Barangay Aguada, Ozamiz City', '09170000041', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(42, 42, 'Purok 1, Barangay Aguada, Ozamiz City', '09170000042', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(43, 43, 'Purok 2, Barangay Aguada, Ozamiz City', '09170000043', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(44, 44, 'Purok 3, Barangay Aguada, Ozamiz City', '09170000044', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(45, 45, 'Purok 4, Barangay Aguada, Ozamiz City', '09170000045', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(46, 46, 'Purok 5, Barangay Aguada, Ozamiz City', '09170000046', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(47, 47, 'Purok 6, Barangay Aguada, Ozamiz City', '09170000047', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(48, 48, 'Purok 7, Barangay Aguada, Ozamiz City', '09170000048', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(49, 49, 'Purok 1, Barangay Aguada, Ozamiz City', '09170000049', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(50, 50, 'Purok 2, Barangay Aguada, Ozamiz City', '09170000050', NULL, NULL, NULL, 'Complainant demo account added to cloned checking database.', NULL, NULL),
(51, 51, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
-- Indexes for table `blotter_reports`
--
ALTER TABLE `blotter_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `staff_user_id` (`staff_user_id`),
  ADD KEY `complainant_user_id` (`complainant_user_id`),
  ADD KEY `admin_user_id` (`admin_user_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`),
  ADD KEY `complainant_id` (`complainant_id`),
  ADD KEY `assigned_staff_id` (`assigned_staff_id`);

--
-- Indexes for table `complaint_updates`
--
ALTER TABLE `complaint_updates`
  ADD PRIMARY KEY (`update_id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `actor_user_id` (`actor_user_id`);

--
-- Indexes for table `complaint_update_attachments`
--
ALTER TABLE `complaint_update_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `update_id` (`update_id`);

--
-- Indexes for table `developer_profile`
--
ALTER TABLE `developer_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_password_resets_user` (`user_id`);

--
-- Indexes for table `residency`
--
ALTER TABLE `residency`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_residency_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_auth`
--
ALTER TABLE `user_auth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_auth_user` (`user_id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `unique_user_profiles_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `blotter_reports`
--
ALTER TABLE `blotter_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `complaint_updates`
--
ALTER TABLE `complaint_updates`
  MODIFY `update_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `complaint_update_attachments`
--
ALTER TABLE `complaint_update_attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `developer_profile`
--
ALTER TABLE `developer_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=501;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `residency`
--
ALTER TABLE `residency`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `user_auth`
--
ALTER TABLE `user_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

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
-- Constraints for table `complaint_updates`
--
ALTER TABLE `complaint_updates`
  ADD CONSTRAINT `fk_complaint_updates_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_complaint_updates_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`) ON DELETE CASCADE;

--
-- Constraints for table `complaint_update_attachments`
--
ALTER TABLE `complaint_update_attachments`
  ADD CONSTRAINT `fk_complaint_update_attachments_update` FOREIGN KEY (`update_id`) REFERENCES `complaint_updates` (`update_id`) ON DELETE CASCADE;

--
-- Constraints for table `developer_profile`
--
ALTER TABLE `developer_profile`
  ADD CONSTRAINT `developer_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `residency`
--
ALTER TABLE `residency`
  ADD CONSTRAINT `residency_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_auth`
--
ALTER TABLE `user_auth`
  ADD CONSTRAINT `user_auth_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
