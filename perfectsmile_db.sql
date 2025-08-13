-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Aug 09, 2025 at 11:38 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perfectsmile_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `dentist_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_datetime` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `appointment_type` enum('scheduled','walkin') DEFAULT 'scheduled',
  `approval_status` enum('pending','approved','declined','auto_approved') DEFAULT 'pending',
  `decline_reason` text,
  `remarks` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `checked_in_at` datetime DEFAULT NULL COMMENT 'When patient checked in',
  `checked_in_by` int(11) DEFAULT NULL COMMENT 'Staff who checked in the patient',
  `self_checkin` tinyint(1) DEFAULT '0' COMMENT 'Whether patient checked in themselves',
  `started_at` datetime DEFAULT NULL COMMENT 'When treatment started',
  `called_by` int(11) DEFAULT NULL COMMENT 'Dentist who called the patient',
  `treatment_status` varchar(50) DEFAULT NULL COMMENT 'Current treatment status',
  `treatment_notes` text COMMENT 'Treatment progress notes',
  `payment_status` enum('pending','paid','partial','waived') DEFAULT 'pending' COMMENT 'Payment status',
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'Payment method used',
  `payment_amount` decimal(10,2) DEFAULT NULL COMMENT 'Amount paid',
  `payment_date` datetime DEFAULT NULL COMMENT 'When payment was made',
  `payment_received_by` int(11) DEFAULT NULL COMMENT 'Staff who received payment',
  `payment_notes` text COMMENT 'Payment notes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `branch_id`, `dentist_id`, `user_id`, `appointment_datetime`, `status`, `appointment_type`, `approval_status`, `decline_reason`, `remarks`, `created_at`, `updated_at`, `checked_in_at`, `checked_in_by`, `self_checkin`, `started_at`, `called_by`, `treatment_status`, `treatment_notes`, `payment_status`, `payment_method`, `payment_amount`, `payment_date`, `payment_received_by`, `payment_notes`) VALUES
(45, 1, 16, 15, '2025-08-09 08:19:00', 'completed', 'scheduled', 'approved', NULL, 'nnnnnnnnnnnnnnnnn', '2025-08-09 03:14:39', '2025-08-09 03:19:20', NULL, NULL, 0, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL),
(46, 1, 16, 15, '2025-08-09 13:30:00', 'pending_approval', 'scheduled', 'pending', NULL, 'jjjjjjjjjj', '2025-08-09 05:29:30', '2025-08-09 05:29:30', NULL, NULL, 0, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL),
(47, 1, 16, 15, '2025-08-09 09:00:00', 'checked_in', 'scheduled', 'approved', NULL, 'mmmmmmmmmmmm', '2025-08-09 05:30:22', '2025-08-09 05:30:38', '2025-08-09 05:30:38', 1, 0, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `appointment_service`
--

CREATE TABLE `appointment_service` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text,
  `contact_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `contact_number`) VALUES
(1, 'Perfect Smile - Nabua Branch', 'Nabua,Camarines Sur', '+1 (555) 123-4567'),
(2, 'Perfect Smile - Iriga Branch', 'Iriga City,Camarines Sur', '+1 (555) 234-5678');

-- --------------------------------------------------------

--
-- Table structure for table `branch_user`
--

CREATE TABLE `branch_user` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `position` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `branch_user`
--

INSERT INTO `branch_user` (`id`, `user_id`, `branch_id`, `position`) VALUES
(1, 10, 1, 'Administrator'),
(4, 16, 1, 'nabua@perfectsmile.com');

-- --------------------------------------------------------

--
-- Table structure for table `dental_chart`
--

CREATE TABLE `dental_chart` (
  `id` int(11) UNSIGNED NOT NULL,
  `dental_record_id` int(11) UNSIGNED NOT NULL,
  `tooth_number` int(2) NOT NULL COMMENT 'Tooth number (1-32 for permanent teeth, 51-85 for primary teeth)',
  `tooth_type` enum('permanent','primary') NOT NULL DEFAULT 'permanent',
  `condition` varchar(255) DEFAULT NULL COMMENT 'Dental condition (cavity, filling, crown, etc.)',
  `status` enum('healthy','needs_treatment','treated','missing','none') NOT NULL DEFAULT 'healthy',
  `notes` text COMMENT 'Additional notes about the tooth',
  `recommended_service_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Recommended service for treatment',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `dental_chart`
--

INSERT INTO `dental_chart` (`id`, `dental_record_id`, `tooth_number`, `tooth_type`, `condition`, `status`, `notes`, `recommended_service_id`, `priority`, `estimated_cost`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'permanent', 'cavity', 'none', '', NULL, 'low', NULL, '2025-07-21 10:30:22', '2025-07-21 10:30:22'),
(2, 4, 1, 'permanent', 'healthy', '', 'wala naman', NULL, 'medium', NULL, '2025-07-21 11:09:28', '2025-07-21 11:09:28'),
(3, 4, 2, 'permanent', 'healthy', '', 'wala', NULL, 'low', NULL, '2025-07-21 11:09:28', '2025-07-21 11:09:28'),
(4, 4, 3, 'permanent', 'extraction_needed', '', 'non', NULL, 'low', NULL, '2025-07-21 11:09:28', '2025-07-21 11:09:28'),
(5, 5, 1, 'permanent', 'crown', '', '', NULL, 'low', NULL, '2025-07-21 11:37:10', '2025-07-21 11:37:10'),
(6, 5, 2, 'permanent', 'healthy', 'none', '', NULL, 'low', NULL, '2025-07-21 11:37:10', '2025-07-21 11:37:10'),
(7, 5, 3, 'permanent', 'healthy', '', '', NULL, 'medium', NULL, '2025-07-21 11:37:10', '2025-07-21 11:37:10'),
(8, 5, 17, 'permanent', 'filled', 'none', '', NULL, 'low', NULL, '2025-07-21 11:37:10', '2025-07-21 11:37:10'),
(9, 5, 18, 'permanent', 'cavity', '', '', NULL, 'medium', NULL, '2025-07-21 11:37:10', '2025-07-21 11:37:10'),
(10, 5, 20, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-21 11:37:10', '2025-07-21 11:37:10'),
(11, 6, 1, 'permanent', 'crown', '', '', NULL, 'low', NULL, '2025-07-22 07:15:32', '2025-07-22 07:15:32'),
(12, 6, 2, 'permanent', 'healthy', 'none', '', NULL, 'low', NULL, '2025-07-22 07:15:32', '2025-07-22 07:15:32'),
(13, 6, 3, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-22 07:15:32', '2025-07-22 07:15:32'),
(14, 6, 17, 'permanent', 'filled', 'none', '', NULL, 'low', NULL, '2025-07-22 07:15:32', '2025-07-22 07:15:32'),
(15, 6, 18, 'permanent', 'cavity', '', '', NULL, 'low', NULL, '2025-07-22 07:15:32', '2025-07-22 07:15:32'),
(16, 6, 20, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-22 07:15:32', '2025-07-22 07:15:32'),
(17, 7, 1, 'permanent', 'crown', '', '', NULL, 'low', NULL, '2025-07-22 07:15:40', '2025-07-22 07:15:40'),
(18, 7, 2, 'permanent', 'healthy', 'none', '', NULL, 'low', NULL, '2025-07-22 07:15:40', '2025-07-22 07:15:40'),
(19, 7, 3, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-22 07:15:40', '2025-07-22 07:15:40'),
(20, 7, 17, 'permanent', 'filled', 'none', '', NULL, 'low', NULL, '2025-07-22 07:15:40', '2025-07-22 07:15:40'),
(21, 7, 18, 'permanent', 'cavity', '', '', NULL, 'low', NULL, '2025-07-22 07:15:40', '2025-07-22 07:15:40'),
(22, 7, 20, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-22 07:15:40', '2025-07-22 07:15:40'),
(23, 8, 1, 'permanent', 'crown', '', '', NULL, 'low', NULL, '2025-07-22 07:16:09', '2025-07-22 07:16:09'),
(24, 8, 2, 'permanent', 'healthy', 'none', '', NULL, 'low', NULL, '2025-07-22 07:16:09', '2025-07-22 07:16:09'),
(25, 8, 3, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-22 07:16:09', '2025-07-22 07:16:09'),
(26, 8, 16, 'permanent', 'root_canal', '', '', NULL, 'low', NULL, '2025-07-22 07:16:09', '2025-07-22 07:16:09'),
(27, 8, 17, 'permanent', 'filled', 'none', '', NULL, 'low', NULL, '2025-07-22 07:16:09', '2025-07-22 07:16:09'),
(28, 8, 18, 'permanent', 'cavity', '', '', NULL, 'low', NULL, '2025-07-22 07:16:09', '2025-07-22 07:16:09'),
(29, 8, 20, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-22 07:16:09', '2025-07-22 07:16:09'),
(30, 8, 32, 'permanent', 'root_canal', '', '', NULL, 'low', NULL, '2025-07-22 07:16:09', '2025-07-22 07:16:09'),
(31, 9, 1, 'permanent', 'crown', '', '', NULL, 'low', NULL, '2025-07-22 07:25:30', '2025-07-22 07:25:30'),
(32, 9, 2, 'permanent', 'healthy', 'none', '', NULL, 'low', NULL, '2025-07-22 07:25:30', '2025-07-22 07:25:30'),
(33, 9, 3, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-22 07:25:30', '2025-07-22 07:25:30'),
(34, 9, 17, 'permanent', 'filled', 'none', '', NULL, 'low', NULL, '2025-07-22 07:25:30', '2025-07-22 07:25:30'),
(35, 9, 18, 'permanent', 'cavity', '', '', NULL, 'low', NULL, '2025-07-22 07:25:30', '2025-07-22 07:25:30'),
(36, 9, 20, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-22 07:25:30', '2025-07-22 07:25:30'),
(37, 10, 1, 'permanent', 'crown', '', '', NULL, 'low', NULL, '2025-07-23 05:15:10', '2025-07-23 05:15:10'),
(38, 10, 2, 'permanent', 'healthy', 'none', '', NULL, 'low', NULL, '2025-07-23 05:15:10', '2025-07-23 05:15:10'),
(39, 10, 3, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-23 05:15:10', '2025-07-23 05:15:10'),
(40, 10, 16, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-23 05:15:10', '2025-07-23 05:15:10'),
(41, 10, 17, 'permanent', 'filled', 'none', '', NULL, 'low', NULL, '2025-07-23 05:15:10', '2025-07-23 05:15:10'),
(42, 10, 18, 'permanent', 'cavity', '', '', NULL, 'low', NULL, '2025-07-23 05:15:10', '2025-07-23 05:15:10'),
(43, 10, 20, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-23 05:15:10', '2025-07-23 05:15:10'),
(44, 10, 32, 'permanent', 'cavity', 'none', '', NULL, 'low', NULL, '2025-07-23 05:15:10', '2025-07-23 05:15:10'),
(45, 11, 1, 'permanent', 'crown', '', '', NULL, 'low', NULL, '2025-07-27 03:15:17', '2025-07-27 03:15:17'),
(46, 11, 2, 'permanent', 'healthy', 'none', '', NULL, 'low', NULL, '2025-07-27 03:15:17', '2025-07-27 03:15:17'),
(47, 11, 3, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-27 03:15:17', '2025-07-27 03:15:17'),
(48, 11, 16, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-27 03:15:17', '2025-07-27 03:15:17'),
(49, 11, 17, 'permanent', 'filled', 'none', '', NULL, 'low', NULL, '2025-07-27 03:15:17', '2025-07-27 03:15:17'),
(50, 11, 18, 'permanent', 'cavity', '', '', NULL, 'low', NULL, '2025-07-27 03:15:17', '2025-07-27 03:15:17'),
(51, 11, 20, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-27 03:15:17', '2025-07-27 03:15:17'),
(52, 11, 32, 'permanent', 'cavity', 'none', '', NULL, 'low', NULL, '2025-07-27 03:15:17', '2025-07-27 03:15:17'),
(53, 12, 1, 'permanent', 'missing', 'none', 'idk', NULL, 'low', NULL, '2025-07-27 14:20:14', '2025-07-27 14:20:14'),
(54, 12, 2, 'permanent', 'healthy', 'none', '', NULL, 'low', NULL, '2025-07-27 14:20:14', '2025-07-27 14:20:14'),
(55, 12, 3, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-27 14:20:14', '2025-07-27 14:20:14'),
(56, 12, 16, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-27 14:20:14', '2025-07-27 14:20:14'),
(57, 12, 17, 'permanent', 'filled', 'none', '', NULL, 'low', NULL, '2025-07-27 14:20:14', '2025-07-27 14:20:14'),
(58, 12, 18, 'permanent', 'cavity', '', '', NULL, 'low', NULL, '2025-07-27 14:20:14', '2025-07-27 14:20:14'),
(59, 12, 20, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-27 14:20:14', '2025-07-27 14:20:14'),
(60, 12, 32, 'permanent', 'cavity', 'none', '', NULL, 'low', NULL, '2025-07-27 14:20:14', '2025-07-27 14:20:14'),
(61, 13, 1, 'permanent', 'crown', '', '', NULL, 'low', NULL, '2025-07-27 14:21:55', '2025-07-27 14:21:55'),
(62, 13, 2, 'permanent', 'healthy', 'none', '', NULL, 'low', NULL, '2025-07-27 14:21:55', '2025-07-27 14:21:55'),
(63, 13, 3, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-27 14:21:55', '2025-07-27 14:21:55'),
(64, 13, 5, 'permanent', 'missing', 'none', 'wala', NULL, 'low', NULL, '2025-07-27 14:21:55', '2025-07-27 14:21:55'),
(65, 13, 16, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-27 14:21:55', '2025-07-27 14:21:55'),
(66, 13, 17, 'permanent', 'filled', 'none', '', NULL, 'low', NULL, '2025-07-27 14:21:55', '2025-07-27 14:21:55'),
(67, 13, 18, 'permanent', 'cavity', '', '', NULL, 'low', NULL, '2025-07-27 14:21:55', '2025-07-27 14:21:55'),
(68, 13, 20, 'permanent', 'healthy', '', '', NULL, 'low', NULL, '2025-07-27 14:21:55', '2025-07-27 14:21:55'),
(69, 13, 32, 'permanent', 'cavity', 'none', '', NULL, 'low', NULL, '2025-07-27 14:21:55', '2025-07-27 14:21:55'),
(70, 14, 1, 'permanent', 'cavity', 'none', '', NULL, 'low', NULL, '2025-07-27 14:27:05', '2025-07-27 14:27:05'),
(71, 14, 2, 'permanent', 'missing', '', 'wala', NULL, 'low', NULL, '2025-07-27 14:27:05', '2025-07-27 14:27:05'),
(72, 15, 1, 'permanent', 'crown', 'none', '', NULL, 'low', NULL, '2025-07-27 14:54:43', '2025-07-27 14:54:43'),
(73, 15, 2, 'permanent', 'filled', '', 'wala', NULL, 'low', NULL, '2025-07-27 14:54:43', '2025-07-27 14:54:43'),
(74, 15, 3, 'permanent', 'cavity', 'none', '', NULL, 'low', NULL, '2025-07-27 14:54:43', '2025-07-27 14:54:43');

-- --------------------------------------------------------

--
-- Table structure for table `dental_record`
--

CREATE TABLE `dental_record` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `diagnosis` text,
  `treatment` text,
  `notes` text,
  `xray_image_url` varchar(255) DEFAULT NULL,
  `next_appointment_date` date DEFAULT NULL,
  `dentist_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dental_record`
--

INSERT INTO `dental_record` (`id`, `user_id`, `appointment_id`, `record_date`, `diagnosis`, `treatment`, `notes`, `xray_image_url`, `next_appointment_date`, `dentist_id`) VALUES
(1, 5, NULL, '2025-07-20', 'dddddddddddmdfoidvidfnoi', 'ddddddddwefmdfewflmkwefklmwfeklm', 'lllllm;mk;m;mkm;mk;', NULL, NULL, 1),
(2, 5, NULL, '2025-07-21', '2nd timeee2nd timeee2nd timeee2nd timeee2nd timeee', '2nd2nd timeee2nd timeee2nd timeee2nd timeee', 'time2nd timeee2nd timeee2nd timeee', NULL, NULL, 1),
(3, 3, NULL, '2025-07-21', 'ffwdeefffeknfeefn', 'ffwdeefffeknfeefn', 'ffwdeefffeknfeefn', NULL, NULL, 1),
(4, 3, NULL, '2025-07-21', 'ffwdeefffeknfeefn', 'ffwdeefffeknfeefn', 'ffwdeefffeknfeefn', NULL, NULL, 1),
(5, 5, NULL, '2025-07-21', 'SELECT * FROM dental_chart WHERE dental_record_id IN (SELECT id FROM dental_record WHERE user_id = 5);', 'SELECT * FROM dental_chart WHERE dental_record_id IN (SELECT id FROM dental_record WHERE user_id = 5);', 'SELECT * FROM dental_chart WHERE dental_record_id IN (SELECT id FROM dental_record WHERE user_id = 5);', NULL, NULL, 1),
(6, 5, NULL, '2025-07-22', 'balik ka mamaya 330pm', 'balik ka mamaya 330pm', 'balik ka mamaya 330pm', NULL, '2025-07-22', 1),
(7, 5, NULL, '2025-07-22', 'balik ka mamaya 330pm', 'balik ka mamaya 330pm', 'balik ka mamaya 330pm', NULL, '2025-07-22', 1),
(8, 5, NULL, '2025-07-22', 'balik ka mamaya 330pm', 'balik ka mamaya 330pm', 'balik ka mamaya 330pm', NULL, NULL, 1),
(9, 5, NULL, '2025-07-22', 'kookkokokookok', 'kokookookokok', 'kokookko', NULL, NULL, 1),
(10, 5, NULL, '2025-07-23', 'https://web.archive.org/web/20200220222607/http://www.meshmixer.com/download.htmlhttps://web.archive.org/web/20200220222607/http://www.meshmixer.com/download.html', 'https://web.archive.org/web/20200220222607/http://www.meshmixer.com/download.html', 'https://web.archive.org/web/20200220222607/http://www.meshmixer.com/download.html', NULL, NULL, 1),
(11, 5, NULL, '2025-07-27', 'kjljkllkjkl', 'kl;kl;kll;k', ';kl;kkl;kl;', NULL, NULL, 1),
(12, 5, NULL, '2025-07-27', 'testing 3d 101', '3d testing 101', 'wld naman 1411242412', NULL, NULL, 1),
(13, 5, NULL, '2025-07-27', '\r\nCondition: [dropdown]\r\nTreatment: [dropdown]  \r\nNotes: [textarea]', '\r\nCondition: [dropdown]\r\nTreatment: [dropdown]  \r\nNotes: [textarea]', '\r\nCondition: [dropdown]\r\nTreatment: [dropdown]  \r\nNotes: [textarea]', NULL, NULL, 1),
(14, 3, NULL, '2025-07-27', '\r\nCondition: [dropdown]\r\nTreatment: [dropdown]  \r\nNotes: [textarea]', '\r\nCondition: [dropdown]\r\nTreatment: [dropdown]  \r\nNotes: [textarea]', '\r\nCondition: [dropdown]\r\nTreatment: [dropdown]  \r\nNotes: [textarea]', NULL, NULL, 1),
(15, 3, NULL, '2025-07-27', 'mlmm;;mlmlm;', '\r\nCondition: [dropdown]\r\nTreatment: [dropdown]  \r\nNotes: [textarea]', '', NULL, NULL, 1),
(16, 15, NULL, '2025-08-09', 'missing tooth needs pustiso', 'missing tooth needs pustiso', 'wala namn', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `procedure_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2025-07-08-111501', 'App\\Database\\Migrations\\AddPatientFieldsToUsersTable', 'default', 'App', 1751973347, 1),
(2, '2025-07-08-162713', 'App\\Database\\Migrations\\AddStatusToUserTable', 'default', 'App', 1751992066, 2),
(3, '2025-07-11-195213', 'App\\Database\\Migrations\\RemoveSeparateNameFields', 'default', 'App', 1752263588, 3),
(4, '2025-07-18-022629', 'App\\Database\\Migrations\\AddDentistApprovalFieldsToAppointments', 'default', 'App', 1752805772, 4),
(5, '2025-07-18-201213', 'App\\Database\\Migrations\\MergeAppointmentDateTimeMigration', 'default', 'App', 1752869622, 5),
(6, '2025-07-20-065031', 'App\\Database\\Migrations\\AddWorkflowColumnsToAppointments', 'default', 'App', 1752994261, 6);

-- --------------------------------------------------------

--
-- Table structure for table `patient_guardian`
--

CREATE TABLE `patient_guardian` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `relationship` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `notes` text,
  `signature_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

CREATE TABLE `prescription_items` (
  `id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `frequency` varchar(50) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `instructions` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `procedures`
--

CREATE TABLE `procedures` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `procedure_name` varchar(255) DEFAULT NULL,
  `description` text,
  `procedure_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `procedure_service`
--

CREATE TABLE `procedure_service` (
  `id` int(11) NOT NULL,
  `procedure_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`) VALUES
(1, 'Dental Checkup', 'Comprehensive dental examination and cleaning', '75.00'),
(2, 'Teeth Whitening', 'Professional teeth whitening treatment', '150.00'),
(3, 'Cavity Filling', 'Dental filling for cavities', '120.00'),
(4, 'Root Canal', 'Root canal treatment', '800.00'),
(5, 'Dental Crown', 'Dental crown placement', '600.00'),
(6, 'Tooth Extraction', 'Simple tooth extraction', '200.00');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `user_type`, `name`, `address`, `email`, `gender`, `password`, `phone`, `created_at`, `updated_at`, `occupation`, `nationality`, `date_of_birth`, `age`, `status`) VALUES
(1, 'admin', 'Admin User', '123 Admin Street', 'admin@perfectsmile.com', 'male', '$2y$12$Tau0ciyH4Ny3A/P0I.qEbO5i63Ja4AtTPBd4OblwwAnoQLCYokErS', '1234567890', '2025-06-28 16:02:14', '2025-06-28 16:02:14', NULL, NULL, NULL, NULL, 'active'),
(3, 'patient', 'Patient Jane', '789 Patient Road', 'patient@perfectsmile.com', 'Female', '$2y$12$IT16i/hQXaqPB4bgvBy23.isY3vix20H5snsASckANyzP3HcwpJ0e', '1234567892', '2025-06-28 16:02:14', '2025-08-08 11:10:37', 'hh', 'oo', '2025-07-08', 9, 'active'),
(5, 'patient', 'Brandon Brandon Brandon', 'Brandon@gmail.com', 'Brandon@gmail.com', 'Male', '$2y$12$3225/eB6Cz2MGgN3eHsp6.26RK/q0nmDVEvBkvGubuBiyWpCNG3Sm', '89078007077', '2025-07-08 13:39:16', '2025-07-10 10:45:58', 'Brandon', 'Brandon@gmail.com', '2025-07-03', 18, 'active'),
(10, 'admin', 'Brandon Dentist', NULL, 'don@gmail.com', 'male', '$2y$12$9BzUNUBkE5idKzb1qdz78ePPo8HsVRgCMm9ZJnjaZDIvRIqcnYW8S', '09150540702', '2025-08-07 05:30:50', '2025-08-07 07:25:15', 'na', 'na', NULL, NULL, 'active'),
(15, 'patient', 'Eden Caritos', 'PIli Camsur', 'eden@gmail.com', 'Female', '$2y$12$iDQqqaHABtNBdRA3ZrC/EeqDZ/L5cPE4HzaKj84mufo/mLGS7qNgi', '099150540702', '2025-08-08 11:06:41', '2025-08-08 11:39:27', 'Nurse', 'Filipino', '2001-09-11', 23, 'active'),
(16, 'dentist', 'Nabua Dentist', NULL, 'nabua@perfectsmile.com', 'Male', '$2y$12$saFau/p7z3Tu6x/vMg6Q4OkN8o34aUQTMj3Do.zwIVU0l/ii1QVw2', '09150540702', '2025-08-09 03:14:00', '2025-08-09 03:14:00', 'na', 'na', '2000-09-23', 24, 'active'),
(17, 'patient', 'Johnbert', 'jjjjjj', 'jb2g@gmail.com', 'Male', NULL, '412812482148', '2025-08-09 05:43:01', '2025-08-09 05:43:01', 'jjj', 'jjj', '2003-08-07', 22, 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `fk_appointments_dentist` (`dentist_id`);

--
-- Indexes for table `appointment_service`
--
ALTER TABLE `appointment_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branch_user`
--
ALTER TABLE `branch_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `dental_chart`
--
ALTER TABLE `dental_chart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dental_record_id` (`dental_record_id`),
  ADD KEY `tooth_number` (`tooth_number`);

--
-- Indexes for table `dental_record`
--
ALTER TABLE `dental_record`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `dentist_id` (`dentist_id`),
  ADD KEY `fk_dental_record_appointment` (`appointment_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `procedure_id` (`procedure_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_guardian`
--
ALTER TABLE `patient_guardian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dentist_id` (`dentist_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prescription_id` (`prescription_id`);

--
-- Indexes for table `procedures`
--
ALTER TABLE `procedures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `procedure_service`
--
ALTER TABLE `procedure_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `procedure_id` (`procedure_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `appointment_service`
--
ALTER TABLE `appointment_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `branch_user`
--
ALTER TABLE `branch_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dental_chart`
--
ALTER TABLE `dental_chart`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `dental_record`
--
ALTER TABLE `dental_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patient_guardian`
--
ALTER TABLE `patient_guardian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `procedures`
--
ALTER TABLE `procedures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `procedure_service`
--
ALTER TABLE `procedure_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_appointments_dentist` FOREIGN KEY (`dentist_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `appointment_service`
--
ALTER TABLE `appointment_service`
  ADD CONSTRAINT `appointment_service_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `appointment_service_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`);

--
-- Constraints for table `availability`
--
ALTER TABLE `availability`
  ADD CONSTRAINT `availability_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `branch_user`
--
ALTER TABLE `branch_user`
  ADD CONSTRAINT `branch_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `branch_user_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `dental_record`
--
ALTER TABLE `dental_record`
  ADD CONSTRAINT `dental_record_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `dental_record_ibfk_2` FOREIGN KEY (`dentist_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_dental_record_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`procedure_id`) REFERENCES `procedures` (`id`);

--
-- Constraints for table `patient_guardian`
--
ALTER TABLE `patient_guardian`
  ADD CONSTRAINT `patient_guardian_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`dentist_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`);

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`);

--
-- Constraints for table `procedures`
--
ALTER TABLE `procedures`
  ADD CONSTRAINT `procedures_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `procedure_service`
--
ALTER TABLE `procedure_service`
  ADD CONSTRAINT `procedure_service_ibfk_1` FOREIGN KEY (`procedure_id`) REFERENCES `procedures` (`id`),
  ADD CONSTRAINT `procedure_service_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
