-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 20, 2025 at 12:47 AM
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
-- Database: `perfectsmile_db-v1`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `dentist_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'User ID (NULL for guest bookings)',
  `patient_email` varchar(255) DEFAULT NULL COMMENT 'Email for guest bookings',
  `patient_phone` varchar(20) DEFAULT NULL COMMENT 'Phone for guest bookings',
  `patient_name` varchar(255) DEFAULT NULL COMMENT 'Name for guest bookings',
  `appointment_datetime` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `appointment_type` enum('scheduled','walkin') DEFAULT 'scheduled',
  `approval_status` enum('pending','approved','declined','auto_approved') DEFAULT 'pending',
  `decline_reason` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `procedure_duration` int(11) DEFAULT NULL,
  `time_taken` int(11) DEFAULT NULL,
  `pending_change` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `branch_id`, `dentist_id`, `user_id`, `patient_email`, `patient_phone`, `patient_name`, `appointment_datetime`, `status`, `appointment_type`, `approval_status`, `decline_reason`, `remarks`, `created_at`, `updated_at`, `procedure_duration`, `time_taken`, `pending_change`) VALUES
(87, 2, 18, 15, NULL, NULL, NULL, '2025-08-23 08:30:00', 'completed', 'scheduled', 'approved', NULL, 'ooooooooooo', '2025-08-22 19:22:46', '2025-08-23 00:51:02', NULL, NULL, 0),
(88, 1, 16, 5, NULL, NULL, NULL, '2025-08-23 08:40:00', 'ongoing', 'scheduled', 'approved', NULL, 'kkkk', '2025-08-22 19:40:41', '2025-08-23 00:51:08', NULL, NULL, 0),
(89, 1, 16, 22, NULL, NULL, NULL, '2025-08-23 09:00:00', 'confirmed', 'walkin', 'auto_approved', NULL, '', '2025-08-22 19:52:42', '2025-08-22 19:52:42', NULL, NULL, 0),
(92, 2, 18, 15, NULL, NULL, NULL, '2025-08-23 09:30:00', 'confirmed', 'scheduled', 'approved', NULL, '2\r\n18\r\n15\r\n2025-08-23 09:00:00\r\npending\r\nscheduled\r\npending\r\nNULL\r\nbook po ako\r\n2025-08-23 00:26:53\r\n2025-08-23 00:26:53\r\n', '2025-08-23 00:30:17', '2025-08-23 00:30:37', NULL, NULL, 0),
(94, 1, 16, 5, NULL, NULL, NULL, '2025-08-24 15:40:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-08-24 07:40:28', '2025-08-24 12:56:55', NULL, NULL, 0),
(95, 1, 16, 3, NULL, NULL, NULL, '2025-08-24 17:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-08-24 13:44:18', '2025-08-24 16:39:45', NULL, NULL, 0),
(96, 1, 16, 15, NULL, NULL, NULL, '2025-08-25 17:55:00', 'confirmed', 'scheduled', 'approved', NULL, '', '2025-08-24 19:37:29', '2025-08-24 19:37:33', NULL, NULL, 0),
(97, 1, 16, 15, NULL, NULL, NULL, '2025-09-03 15:13:00', 'completed', 'scheduled', 'approved', NULL, 'k', '2025-09-03 07:13:56', '2025-09-03 07:34:34', NULL, NULL, 0),
(98, 1, 16, 15, NULL, NULL, NULL, '2025-09-03 15:34:00', 'completed', 'scheduled', 'approved', NULL, 'k', '2025-09-03 07:34:58', '2025-09-03 20:46:53', NULL, NULL, 0),
(99, 1, 16, 15, NULL, NULL, NULL, '2025-09-04 08:50:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-04 00:48:29', '2025-09-04 01:14:18', NULL, NULL, 0),
(101, 1, 16, 15, NULL, NULL, NULL, '2025-09-05 08:40:00', 'completed', 'scheduled', 'approved', NULL, 'k\r\n\r\n', '2025-09-05 00:39:34', '2025-09-05 00:41:43', NULL, NULL, 0),
(102, 1, 16, 15, NULL, NULL, NULL, '2025-09-05 08:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-05 00:47:56', '2025-09-05 10:10:49', NULL, NULL, 0),
(103, 1, 16, 15, NULL, NULL, NULL, '2025-09-05 11:47:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-05 01:47:14', '2025-09-05 01:48:48', NULL, NULL, 0),
(104, 2, 18, 15, NULL, NULL, NULL, '2025-09-05 10:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-05 02:04:20', '2025-09-05 13:47:38', NULL, NULL, 0),
(105, 1, 16, 15, NULL, NULL, NULL, '2025-09-06 08:40:00', 'ongoing', 'scheduled', 'approved', NULL, '99\r\n', '2025-09-06 00:04:14', '2025-09-06 00:04:33', NULL, NULL, 0),
(106, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 08:08:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 00:21:47', '2025-09-07 16:44:32', NULL, NULL, 0),
(107, 1, 16, 3, NULL, NULL, NULL, '2025-09-08 13:50:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 05:45:58', '2025-09-08 01:51:43', NULL, NULL, 0),
(108, 2, 18, 15, NULL, NULL, NULL, '2025-09-07 13:50:00', 'completed', 'scheduled', 'approved', NULL, 'kkkkk', '2025-09-07 05:46:35', '2025-09-07 06:18:27', NULL, NULL, 0),
(110, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 15:03:00', 'completed', 'scheduled', 'approved', NULL, 'kkkkk\r\n\r\n', '2025-09-07 07:10:32', '2025-09-07 07:30:57', NULL, NULL, 0),
(111, 2, 16, 15, NULL, NULL, NULL, '2025-09-07 15:36:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 07:35:39', '2025-09-07 07:36:25', NULL, NULL, 0),
(112, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 15:33:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 07:37:10', '2025-09-07 07:39:03', NULL, NULL, 0),
(113, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 15:44:00', 'completed', 'scheduled', 'approved', NULL, 'klklklkl', '2025-09-07 07:39:39', '2025-09-07 07:40:58', NULL, NULL, 0),
(114, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 16:04:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 07:41:19', '2025-09-07 07:50:38', NULL, NULL, 0),
(115, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 15:55:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 07:51:38', '2025-09-07 07:54:31', NULL, NULL, 0),
(116, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 15:55:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 07:54:53', '2025-09-07 08:06:43', NULL, NULL, 0),
(117, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 16:10:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 08:11:59', '2025-09-07 08:13:19', NULL, NULL, 0),
(118, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 16:16:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 08:12:22', '2025-09-07 08:13:59', NULL, NULL, 0),
(119, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 16:21:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 08:16:22', '2025-09-07 08:19:07', NULL, NULL, 0),
(121, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 16:23:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 08:24:02', '2025-09-07 08:24:35', NULL, NULL, 0),
(122, 1, 16, 5, NULL, NULL, NULL, '2025-09-07 15:03:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 07:05:08', '2025-09-07 08:08:49', NULL, NULL, 0),
(123, 1, 16, 5, NULL, NULL, NULL, '2025-09-07 16:44:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 08:17:09', '2025-09-07 08:33:18', NULL, NULL, 0),
(124, 2, 18, 3, NULL, NULL, NULL, '2025-09-07 09:09:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 13:09:01', '2025-09-07 15:18:48', NULL, NULL, 0),
(125, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 09:09:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 13:11:10', '2025-09-07 15:18:48', NULL, NULL, 0),
(126, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 11:11:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 15:19:22', '2025-09-07 15:32:22', NULL, NULL, 0),
(127, 1, 16, 15, NULL, NULL, NULL, '2025-09-07 11:32:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-07 15:32:49', '2025-09-07 20:58:14', NULL, NULL, 0),
(128, 1, 10, 15, NULL, NULL, NULL, '2025-09-08 14:22:00', 'confirmed', 'scheduled', 'approved', NULL, 'Follow-up appointment from checkup on Sep 7, 2025 - sdfsddsfsddssdfsdfsdfdsfsdsdfsdfsdfdfssdfsd', '2025-09-07 20:58:14', '2025-09-07 20:58:20', NULL, NULL, 0),
(129, 1, 16, 3, NULL, NULL, NULL, '2025-09-09 17:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-09 11:05:22', '2025-09-09 11:07:05', NULL, NULL, 0),
(130, 1, 16, 3, NULL, NULL, NULL, '2025-09-09 17:00:00', 'completed', 'scheduled', 'approved', NULL, 'kkk', '2025-09-09 11:14:10', '2025-09-09 11:14:38', NULL, NULL, 0),
(131, 1, 16, 15, NULL, NULL, NULL, '2025-09-09 17:00:00', 'completed', 'scheduled', 'approved', NULL, 'lklklkl', '2025-09-09 11:20:26', '2025-09-09 11:20:54', NULL, NULL, 0),
(132, 1, 16, 15, NULL, NULL, NULL, '2025-09-10 08:08:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-09 17:17:39', '2025-09-10 13:37:03', NULL, NULL, 0),
(133, 1, 16, 15, NULL, NULL, NULL, '2025-09-10 14:30:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-10 06:37:43', '2025-09-10 09:29:11', NULL, NULL, 0),
(134, 1, 16, 15, NULL, NULL, NULL, '2025-09-10 17:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-10 09:31:17', '2025-09-10 09:32:15', NULL, NULL, 0),
(135, 1, 16, 15, NULL, NULL, NULL, '2025-09-10 17:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-10 09:34:10', '2025-09-10 09:34:50', NULL, NULL, 0),
(137, 1, 16, 15, NULL, NULL, NULL, '2025-09-10 17:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-10 09:35:50', '2025-09-10 09:36:19', NULL, NULL, 0),
(138, 1, 16, 15, NULL, NULL, NULL, '2025-09-10 18:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-10 10:51:53', '2025-09-10 11:17:32', NULL, NULL, 0),
(139, 1, 16, 15, NULL, NULL, NULL, '2025-09-10 19:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-10 11:22:47', '2025-09-10 11:23:06', NULL, NULL, 0),
(140, 1, 16, 5, NULL, NULL, NULL, '2025-09-10 19:00:00', 'completed', 'scheduled', 'approved', NULL, 'mmmm', '2025-09-10 11:37:50', '2025-09-10 11:42:32', NULL, NULL, 0),
(141, 1, 16, 5, NULL, NULL, NULL, '2025-09-10 19:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-10 11:43:49', '2025-09-10 11:46:28', NULL, NULL, 0),
(142, 1, 16, 5, NULL, NULL, NULL, '2025-09-10 19:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-10 11:59:04', '2025-09-10 11:59:32', NULL, NULL, 0),
(143, 1, 16, 5, NULL, NULL, NULL, '2025-09-10 19:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-10 12:00:07', '2025-09-10 13:48:57', NULL, NULL, 0),
(144, 1, 16, 3, NULL, NULL, NULL, '2025-09-10 20:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-10 13:53:30', '2025-09-10 13:54:25', NULL, NULL, 0),
(145, 1, 16, 3, NULL, NULL, NULL, '2025-09-11 16:04:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-11 08:41:14', '2025-09-11 08:41:49', NULL, NULL, 0),
(146, 1, 16, 15, NULL, NULL, NULL, '2025-09-11 16:51:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-11 08:51:32', '2025-09-11 08:51:46', NULL, NULL, 0),
(147, 1, 16, 15, NULL, NULL, NULL, '2025-09-11 17:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-11 08:54:50', '2025-09-11 09:30:59', NULL, NULL, 0),
(148, 1, 16, 15, NULL, NULL, NULL, '2025-09-11 18:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-11 10:03:32', '2025-09-11 10:05:07', NULL, NULL, 0),
(149, 1, 16, 28, NULL, NULL, NULL, '2025-09-12 15:50:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-12 07:40:44', '2025-09-12 07:42:45', NULL, NULL, 0),
(150, 2, 18, 28, NULL, NULL, NULL, '2025-09-12 17:00:00', 'completed', 'scheduled', 'approved', NULL, '', '2025-09-12 08:53:58', '2025-09-12 08:54:30', NULL, NULL, 0),
(151, 2, 30, 28, NULL, NULL, NULL, '2025-09-16 10:20:00', 'completed', 'scheduled', 'approved', NULL, 'test', '2025-09-16 02:21:23', '2025-09-16 02:53:39', NULL, NULL, 0),
(153, 1, 30, 3, NULL, NULL, NULL, '2025-09-17 10:00:00', 'no_show', 'scheduled', 'approved', NULL, NULL, '2025-09-16 19:52:30', '2025-09-17 11:34:56', 30, NULL, 0),
(154, 2, 30, 3, NULL, NULL, NULL, '2025-09-17 08:00:00', 'no_show', 'scheduled', 'approved', NULL, 'test', '2025-09-17 08:40:09', '2025-09-17 11:34:56', NULL, NULL, 0),
(155, 2, 30, 28, NULL, NULL, NULL, '2025-09-17 08:20:00', 'no_show', 'scheduled', 'approved', NULL, 'test 2', '2025-09-17 08:40:52', '2025-09-17 11:34:56', NULL, NULL, 0),
(156, 2, 30, 17, NULL, NULL, NULL, '2025-09-17 08:40:00', 'no_show', 'scheduled', 'approved', NULL, 'testing 3', '2025-09-17 08:46:07', '2025-09-17 11:34:56', NULL, NULL, 0),
(162, 2, 30, 28, NULL, NULL, NULL, '2025-09-17 08:00:00', 'completed', 'scheduled', 'approved', NULL, 'test via patient side', '2025-09-17 09:06:42', '2025-09-17 11:35:44', NULL, NULL, 0),
(164, 2, 30, 19, NULL, NULL, NULL, '2025-09-17 09:00:00', 'no_show', 'scheduled', 'approved', NULL, 'testing 4', '2025-09-17 09:58:34', '2025-09-17 11:34:56', NULL, NULL, 0),
(169, 2, 30, 22, NULL, NULL, NULL, '2025-09-17 09:19:00', 'no_show', 'scheduled', 'approved', NULL, 'testing 5', '2025-09-17 10:42:21', '2025-09-17 11:34:56', NULL, NULL, 0),
(170, 2, 30, 28, NULL, NULL, NULL, '2025-09-17 09:35:00', 'no_show', 'scheduled', 'approved', NULL, 'testing via patient', '2025-09-17 10:43:29', '2025-09-17 11:34:56', NULL, NULL, 0),
(171, 2, 30, 28, NULL, NULL, NULL, '2025-09-17 09:35:00', 'no_show', 'scheduled', 'approved', NULL, 'testing via patient', '2025-09-17 10:43:30', '2025-09-17 11:34:56', NULL, NULL, 0),
(178, 2, 30, 28, NULL, NULL, NULL, '2025-09-17 10:20:00', 'no_show', 'scheduled', 'approved', NULL, 'testing via patient 2', '2025-09-17 10:53:38', '2025-09-17 11:34:56', NULL, NULL, 0),
(179, 2, 30, 28, NULL, NULL, NULL, '2025-09-17 10:20:00', 'no_show', 'scheduled', 'approved', NULL, 'testing via patient 2', '2025-09-17 10:53:38', '2025-09-17 11:34:56', NULL, NULL, 0),
(183, 2, 30, 28, NULL, NULL, NULL, '2025-09-17 10:40:00', 'no_show', 'scheduled', 'approved', NULL, 'testing via patient 6', '2025-09-17 11:05:18', '2025-09-17 11:34:56', NULL, NULL, 0),
(184, 2, 30, 38, NULL, NULL, NULL, '2025-09-17 11:00:00', 'no_show', 'scheduled', 'approved', NULL, 'testing', '2025-09-17 11:18:04', '2025-09-17 11:34:56', NULL, NULL, 0),
(186, 2, 30, 38, NULL, NULL, NULL, '2025-09-17 11:20:00', 'checked_in', 'scheduled', 'approved', NULL, 'test', '2025-09-17 11:28:16', '2025-09-17 11:50:35', NULL, NULL, 0),
(187, 2, 30, 38, NULL, NULL, NULL, '2025-09-17 10:00:00', 'confirmed', 'scheduled', 'approved', NULL, 'test', '2025-09-17 11:59:31', '2025-09-17 15:30:40', NULL, NULL, 0),
(188, 2, 30, 28, NULL, NULL, NULL, '2025-09-17 12:00:00', 'confirmed', 'scheduled', 'approved', NULL, 'test', '2025-09-17 15:02:28', '2025-09-17 15:30:29', NULL, NULL, 0),
(189, 2, 30, 28, NULL, NULL, NULL, '2025-09-17 11:30:00', 'confirmed', 'scheduled', 'approved', NULL, '', '2025-09-17 15:14:30', '2025-09-17 15:30:33', NULL, NULL, 0),
(190, 2, 30, 28, NULL, NULL, NULL, '2025-09-17 11:00:00', 'confirmed', 'scheduled', 'approved', NULL, '', '2025-09-17 15:25:17', '2025-09-17 15:30:36', NULL, NULL, 0),
(209, 1, 30, 40, NULL, NULL, NULL, '2025-09-18 09:00:00', 'scheduled', 'scheduled', 'approved', NULL, NULL, '2025-09-18 09:43:53', NULL, 30, NULL, 0),
(210, 1, 30, 41, NULL, NULL, NULL, '2025-09-18 09:00:00', 'ongoing', 'walkin', 'auto_approved', NULL, NULL, '2025-09-18 09:43:53', '2025-09-18 01:46:19', 30, NULL, 0),
(212, 1, 30, 43, NULL, NULL, NULL, '2025-09-18 09:00:00', 'ongoing', 'walkin', 'auto_approved', NULL, NULL, '2025-09-18 09:52:07', '2025-09-18 03:00:48', 30, NULL, 0),
(213, 1, 30, 44, NULL, NULL, NULL, '2025-09-18 09:20:00', 'scheduled', 'scheduled', 'approved', NULL, NULL, '2025-09-18 09:52:07', '2025-09-18 09:52:07', 30, NULL, 0),
(214, 2, 30, 31, NULL, NULL, NULL, '2025-09-18 13:00:00', 'scheduled', 'scheduled', 'approved', NULL, 'test', '2025-09-18 03:24:02', '2025-09-18 04:01:35', NULL, NULL, 0),
(232, 2, 30, 28, NULL, NULL, NULL, '2025-09-18 08:00:00', 'cancelled', 'scheduled', 'declined', 's', 'dddd', '2025-09-18 15:21:25', '2025-09-19 06:48:19', 15, NULL, 0),
(233, 2, 30, 28, NULL, NULL, NULL, '2025-09-18 08:30:00', 'cancelled', 'scheduled', 'declined', 's', '', '2025-09-18 15:45:44', '2025-09-19 06:48:23', 15, NULL, 0),
(234, 2, 30, 28, NULL, NULL, NULL, '2025-09-19 08:00:00', 'cancelled', 'scheduled', 'declined', 's', 'dddd', '2025-09-18 16:12:29', '2025-09-19 06:48:26', 15, NULL, 0),
(235, 2, 30, 28, NULL, NULL, NULL, '2025-09-19 08:30:00', 'cancelled', 'scheduled', 'declined', 's', 'xxx', '2025-09-18 16:13:26', '2025-09-19 06:48:29', 240, NULL, 0),
(236, 2, 30, 28, NULL, NULL, NULL, '2025-09-19 09:00:00', 'cancelled', 'scheduled', 'declined', 's', '', '2025-09-18 16:24:17', '2025-09-19 06:48:32', 240, NULL, 0),
(239, 2, 30, 28, NULL, NULL, NULL, '2025-09-20 10:50:00', 'cancelled', 'scheduled', 'declined', 's', 'ssss', '2025-09-19 06:33:06', '2025-09-19 06:48:13', 180, NULL, 0),
(240, 2, 30, 28, NULL, NULL, NULL, '2025-09-20 09:00:00', 'cancelled', 'scheduled', 'declined', 's', 'dd', '2025-09-19 06:45:42', '2025-09-19 06:48:35', 15, NULL, 0),
(241, 1, 30, 3, NULL, NULL, NULL, '2025-09-26 09:00:00', 'confirmed', 'scheduled', 'approved', NULL, NULL, '2025-09-19 17:48:08', NULL, 120, NULL, 0),
(242, 2, 30, 38, NULL, NULL, NULL, '2025-09-19 13:00:00', 'cancelled', 'scheduled', 'declined', 's', 'dddd', '2025-09-19 11:18:54', '2025-09-19 13:44:24', 240, NULL, 0),
(243, 2, NULL, 38, NULL, NULL, NULL, '2025-09-21 09:00:00', 'cancelled', 'scheduled', 'declined', 's', 'testy', '2025-09-19 13:43:16', '2025-09-19 13:44:20', 15, NULL, 0),
(244, 2, 30, 28, NULL, NULL, NULL, '2025-09-21 09:00:00', 'confirmed', 'scheduled', 'approved', NULL, 'sss', '2025-09-19 13:45:26', '2025-09-19 13:45:49', 180, NULL, 0),
(245, 2, 30, 28, NULL, NULL, NULL, '2025-09-20 08:00:00', 'confirmed', 'scheduled', 'approved', NULL, 'ggg', '2025-09-19 16:07:42', '2025-09-19 16:09:17', 15, NULL, 0),
(252, 2, 30, 35, NULL, NULL, NULL, '2025-09-20 08:35:00', 'confirmed', 'scheduled', 'approved', NULL, 'ggggg', '2025-09-19 16:37:39', '2025-09-19 16:38:09', 180, NULL, 0),
(263, 2, 30, 15, NULL, NULL, NULL, '2025-09-20 11:55:00', 'confirmed', 'scheduled', 'approved', NULL, 'gegegeege', '2025-09-19 17:08:03', '2025-09-19 17:08:19', 180, NULL, 0),
(269, 2, 30, 15, NULL, NULL, NULL, '2025-09-22 08:00:00', 'pending_approval', 'scheduled', 'pending', NULL, 'fff', '2025-09-19 22:38:27', '2025-09-19 22:38:27', 15, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `appointment_service`
--

CREATE TABLE `appointment_service` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `tooth_number` varchar(5) DEFAULT NULL,
  `surface` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `appointment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `appointment_service`
--

INSERT INTO `appointment_service` (`id`, `service_id`, `tooth_number`, `surface`, `notes`, `appointment_id`) VALUES
(9, 7, NULL, NULL, NULL, 106),
(10, 3, NULL, NULL, NULL, 106),
(11, 5, NULL, NULL, NULL, 106),
(12, 3, NULL, NULL, NULL, 106),
(13, 3, NULL, NULL, NULL, 108),
(14, 1, NULL, NULL, NULL, 108),
(19, 1, NULL, NULL, NULL, 110),
(20, 1, NULL, NULL, NULL, 110),
(21, 1, NULL, NULL, NULL, 110),
(22, 1, '4', NULL, NULL, 114),
(23, 3, '31', NULL, 'Service added for tooth #31 (FDI: 47)', 114),
(24, 1, '7', NULL, 'Service added for tooth #7 (3D)', 114),
(25, 6, '3', NULL, NULL, 119),
(26, 1, '8', NULL, NULL, 121),
(27, 1, '32', NULL, 'Service added for tooth #32 (FDI: 48)', 122),
(38, 1, NULL, NULL, NULL, 232),
(39, 1, NULL, NULL, NULL, 233),
(40, 1, NULL, NULL, NULL, 234),
(41, 2, NULL, NULL, NULL, 235),
(42, 2, NULL, NULL, NULL, 236),
(44, 4, NULL, NULL, NULL, 239),
(45, 1, NULL, NULL, NULL, 240),
(46, 2, NULL, NULL, NULL, 241),
(47, 2, NULL, NULL, NULL, 242),
(48, 1, NULL, NULL, NULL, 243),
(49, 4, NULL, NULL, NULL, 244),
(50, 1, NULL, NULL, NULL, 245),
(51, 4, NULL, NULL, NULL, 252),
(52, 4, NULL, NULL, NULL, 263),
(53, 1, NULL, NULL, NULL, 269);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `actor_id` int(10) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(150) DEFAULT NULL,
  `role_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `changes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_id`, `actor_name`, `role_id`, `action`, `changes`, `created_at`) VALUES
(1, 1, 'Admin User', 0, 'reschedule', '{\"appointment_id\":211,\"from\":\"2025-09-18 08:00:00\",\"to\":\"2025-09-18 12:00:00\"}', '2025-09-18 04:00:37'),
(2, 1, 'Admin User', 0, 'reschedule', '{\"appointment_id\":214,\"from\":\"2025-09-18 08:25:00\",\"to\":\"2025-09-18 13:00:00\"}', '2025-09-18 04:01:35'),
(3, 1, 'Admin User', 0, 'reschedule', '{\"appointment_id\":215,\"from\":\"2025-09-18 14:00:00\",\"to\":\"2025-09-19 08:00:00\"}', '2025-09-18 07:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT 'recurring',
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`id`, `user_id`, `type`, `day_of_week`, `start_time`, `end_time`, `start_datetime`, `end_datetime`, `is_recurring`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(24, 30, 'urgent', NULL, '13:00:00', '15:00:00', '2025-09-18 13:00:00', '2025-09-18 15:00:00', 0, 'testing', 30, '2025-09-17 03:47:27', '2025-09-17 07:36:41'),
(27, 30, 'kumakain kayo', NULL, '16:30:00', '20:00:00', '2025-09-17 16:30:00', '2025-09-17 20:00:00', 0, 'pwde pumunta pero maghintay muna kupal', 30, '2025-09-17 06:48:59', '2025-09-17 07:36:33'),
(28, 30, 'testing kung may start and end time', 'Wednesday', '14:28:00', '17:30:00', '2025-09-17 14:28:00', '2025-09-17 17:30:00', 0, 'tumisting ka', 30, '2025-09-17 07:29:01', '2025-09-17 07:42:56'),
(29, 30, 'testing part 2', 'Wednesday', '16:32:00', '17:33:00', '2025-09-17 16:32:00', '2025-09-17 17:33:00', 0, 'testing', 30, '2025-09-17 07:33:18', '2025-09-17 07:42:29');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `operating_hours` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `contact_number`, `email`, `status`, `created_at`, `updated_at`, `deleted_at`, `operating_hours`) VALUES
(1, 'Perfect Smile - Nabua Branch', 'Nabua,Camarines Sur', '+1 (555) 123-4567', NULL, 'active', NULL, NULL, NULL, '{\"monday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"tuesday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"wednesday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"thursday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"friday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"saturday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"sunday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"}}'),
(2, 'Perfect Smile - Iriga Branch', 'Iriga City,Camarines Sur', '+1 (555) 234-5678', '', 'active', NULL, '2025-09-18 14:49:30', NULL, '{\"monday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"tuesday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"wednesday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"thursday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"friday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"saturday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"},\"sunday\":{\"enabled\":true,\"open\":\"08:00\",\"close\":\"20:00\"}}'),
(5, 'Test OH Branch', 'Test Address', '123-456-7890', 'test@example.com', 'active', '2025-09-19 18:04:49', '2025-09-19 18:04:49', NULL, '{\"saturday\":{\"enabled\":true,\"open\":\"09:00\",\"close\":\"17:00\"}}');

-- --------------------------------------------------------

--
-- Table structure for table `branch_notifications`
--

CREATE TABLE `branch_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `appointment_id` int(10) UNSIGNED DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_notifications`
--

INSERT INTO `branch_notifications` (`id`, `branch_id`, `appointment_id`, `payload`, `sent`, `sent_at`, `created_at`, `updated_at`) VALUES
(1, 2, 162, '\"{\\\"patient_name\\\":\\\"Marc Aaron Gamban\\\",\\\"patient_email\\\":\\\"gamban@gmail.com\\\",\\\"patient_phone\\\":\\\"09150540703\\\",\\\"appointment_date\\\":\\\"2025-09-17\\\",\\\"appointment_time\\\":\\\"08:00\\\",\\\"branch_id\\\":\\\"2\\\",\\\"procedure_duration\\\":null,\\\"dentist_id\\\":30,\\\"remarks\\\":\\\"test via patient side\\\",\\\"user_id\\\":\\\"28\\\",\\\"status\\\":\\\"pending\\\",\\\"approval_status\\\":\\\"pending\\\"}\"', 0, NULL, '2025-09-17 09:06:42', '2025-09-17 09:06:42'),
(2, 1, 0, '\"{\\\"patient_name\\\":\\\"Test Guest\\\",\\\"patient_email\\\":\\\"guest@example.com\\\",\\\"patient_phone\\\":\\\"09123456789\\\",\\\"appointment_date\\\":\\\"2025-09-17\\\",\\\"appointment_time\\\":\\\"15:00\\\",\\\"branch_id\\\":\\\"1\\\",\\\"procedure_duration\\\":null,\\\"dentist_id\\\":null,\\\"remarks\\\":null,\\\"user_id\\\":null,\\\"status\\\":\\\"pending\\\",\\\"approval_status\\\":\\\"pending\\\"}\"', 0, NULL, '2025-09-17 09:21:16', '2025-09-17 09:21:16'),
(3, 1, NULL, '\"{\\\"patient_name\\\":null,\\\"patient_email\\\":null,\\\"patient_phone\\\":null,\\\"appointment_date\\\":\\\"2025-09-17\\\",\\\"appointment_time\\\":\\\"10:00\\\",\\\"branch_id\\\":\\\"1\\\",\\\"procedure_duration\\\":null,\\\"dentist_id\\\":null,\\\"remarks\\\":null,\\\"user_id\\\":null,\\\"status\\\":\\\"pending\\\",\\\"approval_status\\\":\\\"pending\\\"}\"', 0, NULL, '2025-09-17 09:48:16', '2025-09-17 09:48:16'),
(4, 2, 188, '\"{\\\"type\\\":\\\"appointment_approved\\\",\\\"appointment_id\\\":188,\\\"patient_id\\\":28,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 12:00 Please try to arrive on time \\\\u2014 we allow a 15-minute grace period. If you can\'t make it, please contact us to reschedule.\\\"}\"', 0, NULL, '2025-09-17 15:30:29', '2025-09-17 15:30:29'),
(5, 2, 189, '\"{\\\"type\\\":\\\"appointment_approved\\\",\\\"appointment_id\\\":189,\\\"patient_id\\\":28,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 11:30 Please try to arrive on time \\\\u2014 we allow a 15-minute grace period. If you can\'t make it, please contact us to reschedule.\\\"}\"', 0, NULL, '2025-09-17 15:30:33', '2025-09-17 15:30:33'),
(6, 2, 190, '\"{\\\"type\\\":\\\"appointment_approved\\\",\\\"appointment_id\\\":190,\\\"patient_id\\\":28,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 11:00 Please try to arrive on time \\\\u2014 we allow a 15-minute grace period. If you can\'t make it, please contact us to reschedule.\\\"}\"', 0, NULL, '2025-09-17 15:30:36', '2025-09-17 15:30:36'),
(7, 2, 187, '\"{\\\"type\\\":\\\"appointment_approved\\\",\\\"appointment_id\\\":187,\\\"patient_id\\\":38,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 10:00 Please try to arrive on time \\\\u2014 we allow a 15-minute grace period. If you can\'t make it, please contact us to reschedule.\\\"}\"', 0, NULL, '2025-09-17 15:30:40', '2025-09-17 15:30:40'),
(8, 1, 165, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":165,\\\"patient_id\\\":1,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 09:20 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:21:54', '2025-09-18 00:21:54'),
(9, 1, 167, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":167,\\\"patient_id\\\":1,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 09:20 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:21:57', '2025-09-18 00:21:57'),
(10, 1, 172, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":172,\\\"patient_id\\\":1,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 10:20 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:21:59', '2025-09-18 00:21:59'),
(11, 1, 174, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":174,\\\"patient_id\\\":31,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 10:20 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:02', '2025-09-18 00:22:02'),
(12, 1, 175, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":175,\\\"patient_id\\\":32,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 10:20 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:04', '2025-09-18 00:22:04'),
(13, 1, 176, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":176,\\\"patient_id\\\":33,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 10:20 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:07', '2025-09-18 00:22:07'),
(14, 1, 177, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":177,\\\"patient_id\\\":34,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 10:20 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:10', '2025-09-18 00:22:10'),
(15, 2, 191, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":191,\\\"patient_id\\\":28,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 10:30 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:13', '2025-09-18 00:22:13'),
(16, 1, 180, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":180,\\\"patient_id\\\":35,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 10:40 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:17', '2025-09-18 00:22:17'),
(17, 1, 181, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":181,\\\"patient_id\\\":36,\\\"message\\\":\\\"You\'re all set! on September 17, 2025 at 10:40 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:20', '2025-09-18 00:22:20'),
(18, 2, 192, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":192,\\\"patient_id\\\":28,\\\"message\\\":\\\"You\'re all set! on September 18, 2025 at 08:00 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:25', '2025-09-18 00:22:25'),
(19, 2, 196, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":196,\\\"patient_id\\\":38,\\\"message\\\":\\\"You\'re all set! on September 18, 2025 at 08:00 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:27', '2025-09-18 00:22:27'),
(20, 2, 193, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":193,\\\"patient_id\\\":28,\\\"message\\\":\\\"You\'re all set! on September 18, 2025 at 08:30 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:30', '2025-09-18 00:22:30'),
(21, 2, 194, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":194,\\\"patient_id\\\":28,\\\"message\\\":\\\"You\'re all set! on September 18, 2025 at 09:00 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: g\\\"}\"', 0, NULL, '2025-09-18 00:22:33', '2025-09-18 00:22:33'),
(22, 2, 195, '\"{\\\"type\\\":\\\"appointment_declined\\\",\\\"appointment_id\\\":195,\\\"patient_id\\\":28,\\\"message\\\":\\\"You\'re all set! on September 18, 2025 at 09:30 \\\\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: r\\\"}\"', 0, NULL, '2025-09-18 00:22:37', '2025-09-18 00:22:37'),
(23, 2, 211, '{\"type\":\"appointment_approved\",\"appointment_id\":211,\"patient_id\":28,\"message\":\"You\'re all set! on September 18, 2025 at 08:00 \\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana\"}', 0, NULL, '2025-09-18 01:45:34', '2025-09-18 01:45:34'),
(24, 2, 214, '{\"type\":\"appointment_approved\",\"appointment_id\":214,\"patient_id\":31,\"message\":\"You\'re all set! on September 18, 2025 at 08:25 \\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana\"}', 0, NULL, '2025-09-18 03:24:31', '2025-09-18 03:24:31'),
(25, 2, 215, '{\"type\":\"appointment_approved\",\"appointment_id\":215,\"patient_id\":28,\"message\":\"You\'re all set! on September 18, 2025 at 14:00 \\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana\"}', 0, NULL, '2025-09-18 06:20:30', '2025-09-18 06:20:30'),
(26, 2, 215, '{\"type\":\"reschedule\",\"appointment_id\":215,\"old_time\":\"2025-09-18 14:00:00\",\"new_time\":\"2025-09-19 08:00:00\",\"actor\":\"Admin User\"}', 0, NULL, '2025-09-18 07:22:19', '2025-09-18 07:22:19'),
(27, 2, 216, '{\"type\":\"appointment_approved\",\"appointment_id\":216,\"patient_id\":28,\"message\":\"You\'re all set! on September 18, 2025 at 08:00 \\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana\"}', 0, NULL, '2025-09-18 09:29:33', '2025-09-18 09:29:33'),
(28, 2, 218, '{\"type\":\"appointment_approved\",\"appointment_id\":218,\"patient_id\":28,\"message\":\"You\'re all set! on September 18, 2025 at 09:40 \\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana\"}', 0, NULL, '2025-09-18 11:59:28', '2025-09-18 11:59:28'),
(29, 2, 217, '{\"type\":\"appointment_declined\",\"appointment_id\":217,\"patient_id\":28,\"message\":\"You\'re all set! on September 18, 2025 at 08:30 \\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: d\"}', 0, NULL, '2025-09-18 12:57:34', '2025-09-18 12:57:34'),
(30, 2, 219, '{\"type\":\"appointment_declined\",\"appointment_id\":219,\"patient_id\":28,\"message\":\"You\'re all set! on September 18, 2025 at 10:00 \\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: d\"}', 0, NULL, '2025-09-18 12:57:38', '2025-09-18 12:57:38'),
(31, 2, 220, '{\"type\":\"appointment_declined\",\"appointment_id\":220,\"patient_id\":28,\"message\":\"You\'re all set! on September 18, 2025 at 10:30 \\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: d\"}', 0, NULL, '2025-09-18 12:57:44', '2025-09-18 12:57:44'),
(32, 2, 221, '{\"type\":\"appointment_declined\",\"appointment_id\":221,\"patient_id\":28,\"message\":\"You\'re all set! on September 18, 2025 at 08:30 \\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: d\"}', 0, NULL, '2025-09-18 13:30:45', '2025-09-18 13:30:45'),
(33, 2, 222, '{\"type\":\"appointment_declined\",\"appointment_id\":222,\"patient_id\":28,\"message\":\"You\'re all set! on September 18, 2025 at 13:00 \\u2014 we allow a 15-minute grace period. pag dika dumating ngayong out kana Reason: d\"}', 0, NULL, '2025-09-18 13:30:49', '2025-09-18 13:30:49'),
(34, 2, 239, '{\"type\":\"appointment_declined\",\"appointment_id\":239,\"patient_id\":28,\"message\":\"You\'re all set! September 20, 2025 at 10:50 AM \\u2014 2:05 PM \\u2014 we allow a 15-minute grace period.  Reason: s\"}', 0, NULL, '2025-09-19 06:48:13', '2025-09-19 06:48:13'),
(35, 2, 232, '{\"type\":\"appointment_declined\",\"appointment_id\":232,\"patient_id\":28,\"message\":\"You\'re all set! September 18, 2025 at 8:00 AM \\u2014 8:30 AM \\u2014 we allow a 15-minute grace period.  Reason: s\"}', 0, NULL, '2025-09-19 06:48:19', '2025-09-19 06:48:19'),
(36, 2, 233, '{\"type\":\"appointment_declined\",\"appointment_id\":233,\"patient_id\":28,\"message\":\"You\'re all set! September 18, 2025 at 8:30 AM \\u2014 9:00 AM \\u2014 we allow a 15-minute grace period.  Reason: s\"}', 0, NULL, '2025-09-19 06:48:23', '2025-09-19 06:48:23'),
(37, 2, 234, '{\"type\":\"appointment_declined\",\"appointment_id\":234,\"patient_id\":28,\"message\":\"You\'re all set! September 19, 2025 at 8:00 AM \\u2014 8:30 AM \\u2014 we allow a 15-minute grace period.  Reason: s\"}', 0, NULL, '2025-09-19 06:48:26', '2025-09-19 06:48:26'),
(38, 2, 235, '{\"type\":\"appointment_declined\",\"appointment_id\":235,\"patient_id\":28,\"message\":\"You\'re all set! September 19, 2025 at 8:30 AM \\u2014 12:45 PM \\u2014 we allow a 15-minute grace period.  Reason: s\"}', 0, NULL, '2025-09-19 06:48:29', '2025-09-19 06:48:29'),
(39, 2, 236, '{\"type\":\"appointment_declined\",\"appointment_id\":236,\"patient_id\":28,\"message\":\"You\'re all set! September 19, 2025 at 9:00 AM \\u2014 1:15 PM \\u2014 we allow a 15-minute grace period.  Reason: s\"}', 0, NULL, '2025-09-19 06:48:32', '2025-09-19 06:48:32'),
(40, 2, 240, '{\"type\":\"appointment_declined\",\"appointment_id\":240,\"patient_id\":28,\"message\":\"You\'re all set! September 20, 2025 at 9:00 AM \\u2014 9:30 AM \\u2014 we allow a 15-minute grace period.  Reason: s\"}', 0, NULL, '2025-09-19 06:48:35', '2025-09-19 06:48:35'),
(41, 2, 243, '{\"type\":\"appointment_declined\",\"appointment_id\":243,\"patient_id\":38,\"message\":\"You\'re all set! September 21, 2025 at 9:00 AM \\u2014 9:30 AM \\u2014 we allow a 15-minute grace period.  Reason: s\"}', 0, NULL, '2025-09-19 13:44:21', '2025-09-19 13:44:21'),
(42, 2, 242, '{\"type\":\"appointment_declined\",\"appointment_id\":242,\"patient_id\":38,\"message\":\"You\'re all set! September 19, 2025 at 1:00 PM \\u2014 5:15 PM \\u2014 we allow a 15-minute grace period.  Reason: s\"}', 0, NULL, '2025-09-19 13:44:24', '2025-09-19 13:44:24'),
(43, 2, 244, '{\"type\":\"appointment_approved\",\"appointment_id\":244,\"patient_id\":28,\"message\":\"You\'re all set! September 21, 2025 at 9:00 AM \\u2014 12:15 PM \\u2014 we allow a 15-minute grace period. \"}', 0, NULL, '2025-09-19 13:45:49', '2025-09-19 13:45:49'),
(44, 2, 245, '{\"type\":\"appointment_approved\",\"appointment_id\":245,\"patient_id\":28,\"message\":\"You\'re all set! September 20, 2025 at 8:00 AM \\u2014 8:30 AM \\u2014 we allow a 15-minute grace period. \"}', 0, NULL, '2025-09-19 16:09:17', '2025-09-19 16:09:17'),
(45, 2, 252, '{\"type\":\"appointment_approved\",\"appointment_id\":252,\"patient_id\":35,\"message\":\"You\'re all set! September 20, 2025 at 8:35 AM \\u2014 11:50 AM \\u2014 we allow a 15-minute grace period. \"}', 0, NULL, '2025-09-19 16:38:09', '2025-09-19 16:38:09'),
(46, 2, 263, '{\"type\":\"appointment_approved\",\"appointment_id\":263,\"patient_id\":15,\"message\":\"You\'re all set! September 20, 2025 at 11:55 AM \\u2014 3:10 PM \\u2014 we allow a 15-minute grace period. \"}', 0, NULL, '2025-09-19 17:08:19', '2025-09-19 17:08:19');

-- --------------------------------------------------------

--
-- Table structure for table `branch_staff`
--

CREATE TABLE `branch_staff` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `branch_staff`
--

INSERT INTO `branch_staff` (`id`, `user_id`, `branch_id`, `position`, `assigned_at`, `notes`) VALUES
(0, 29, 2, 'receptionnist', NULL, NULL),
(0, 30, 1, 'dentist', NULL, NULL),
(0, 30, 2, 'dentist', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branch_user`
--

CREATE TABLE `branch_user` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `position` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `branch_user`
--

INSERT INTO `branch_user` (`id`, `user_id`, `branch_id`, `position`) VALUES
(1, 10, 1, 'Administrator'),
(4, 16, 1, 'nabua@perfectsmile.com'),
(5, 18, 1, 'Dentist'),
(6, 20, 1, 'nnnnnn'),
(7, 21, 1, 'jogn@gmail.com'),
(8, 23, 1, 'receptionist'),
(9, 24, 2, 'receptionist'),
(12, 27, 1, 'Dentist');

-- --------------------------------------------------------

--
-- Table structure for table `dental_chart`
--

CREATE TABLE `dental_chart` (
  `id` int(11) UNSIGNED NOT NULL,
  `dental_record_id` int(11) NOT NULL,
  `tooth_number` int(2) NOT NULL COMMENT 'Tooth number (1-32 for permanent teeth, 51-85 for primary teeth)',
  `surface` varchar(20) DEFAULT NULL,
  `tooth_type` enum('permanent','primary') NOT NULL DEFAULT 'permanent',
  `condition` varchar(255) DEFAULT NULL COMMENT 'Dental condition (cavity, filling, crown, etc.)',
  `notes` text DEFAULT NULL COMMENT 'Additional notes about the tooth',
  `service_id` int(11) DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` enum('healthy','needs_treatment','treated','missing','none') NOT NULL DEFAULT 'healthy',
  `recommended_service_id` int(10) UNSIGNED DEFAULT NULL,
  `estimated_cost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dental_chart`
--

INSERT INTO `dental_chart` (`id`, `dental_record_id`, `tooth_number`, `surface`, `tooth_type`, `condition`, `notes`, `service_id`, `priority`, `created_at`, `updated_at`, `status`, `recommended_service_id`, `estimated_cost`) VALUES
(213, 66, 8, NULL, 'permanent', 'healthy', '', NULL, 'low', '2025-09-10 11:17:32', '2025-09-10 11:17:32', 'healthy', NULL, NULL),
(214, 66, 9, NULL, 'permanent', 'healthy', '', NULL, 'low', '2025-09-10 11:17:32', '2025-09-10 11:17:32', 'healthy', NULL, NULL),
(215, 68, 8, NULL, 'permanent', 'healthy', 'healthy', NULL, 'low', '2025-09-10 11:42:32', '2025-09-10 11:42:32', 'healthy', NULL, NULL),
(216, 69, 7, NULL, 'permanent', 'cavity', 'testing cavitiy', NULL, 'low', '2025-09-10 11:46:28', '2025-09-10 11:46:28', 'healthy', NULL, NULL),
(217, 70, 6, NULL, 'permanent', 'healthy', 'testing', NULL, 'low', '2025-09-10 11:59:32', '2025-09-10 11:59:32', 'healthy', NULL, NULL),
(218, 71, 25, 'Crown', 'permanent', 'healthy', 'none', 3, 'low', '2025-09-10 13:48:57', '2025-09-10 13:48:57', 'healthy', NULL, NULL),
(219, 71, 31, 'Crown', 'permanent', 'cavity', 'wala', 3, 'low', '2025-09-10 13:48:57', '2025-09-10 13:48:57', 'healthy', NULL, NULL),
(220, 72, 8, 'Crown', 'permanent', 'healthy', 'walalalalalaall', 3, 'low', '2025-09-10 13:54:25', '2025-09-10 13:54:25', 'healthy', NULL, NULL),
(221, 72, 9, 'Crown', 'permanent', 'missing', 'wala', 3, 'low', '2025-09-10 13:54:25', '2025-09-10 13:54:25', 'healthy', NULL, NULL),
(222, 73, 8, '', 'permanent', 'healthy', 'walalalalalaall', NULL, 'low', '2025-09-11 08:41:49', '2025-09-11 08:41:49', 'healthy', NULL, NULL),
(223, 73, 9, '', 'permanent', 'missing', 'wala', NULL, 'low', '2025-09-11 08:41:49', '2025-09-11 08:41:49', 'healthy', NULL, NULL),
(224, 74, 8, '', 'permanent', 'healthy', '', NULL, 'low', '2025-09-11 08:51:46', '2025-09-11 08:51:46', 'healthy', NULL, NULL),
(225, 74, 9, '', 'permanent', 'healthy', '', NULL, 'low', '2025-09-11 08:51:46', '2025-09-11 08:51:46', 'healthy', NULL, NULL),
(226, 75, 8, '', 'permanent', 'healthy', '', NULL, 'low', '2025-09-11 09:30:59', '2025-09-11 09:30:59', 'healthy', NULL, NULL),
(227, 75, 9, '', 'permanent', 'healthy', '', NULL, 'low', '2025-09-11 09:30:59', '2025-09-11 09:30:59', 'healthy', NULL, NULL),
(228, 76, 8, '', 'permanent', 'healthy', '', NULL, 'low', '2025-09-11 10:05:07', '2025-09-11 10:05:07', 'healthy', NULL, NULL),
(229, 76, 9, '', 'permanent', 'healthy', '', NULL, 'low', '2025-09-11 10:05:07', '2025-09-11 10:05:07', 'healthy', NULL, NULL),
(230, 76, 14, 'Crown', 'permanent', 'missing', 'idk', 4, 'low', '2025-09-11 10:05:07', '2025-09-11 10:05:07', 'healthy', NULL, NULL),
(231, 76, 15, 'Crown', 'permanent', 'healthy', 'none', 1, 'low', '2025-09-11 10:05:07', '2025-09-11 10:05:07', 'healthy', NULL, NULL),
(232, 76, 16, 'Crown', 'permanent', 'root_canal', 'none', 4, 'low', '2025-09-11 10:05:07', '2025-09-11 10:05:07', 'healthy', NULL, NULL),
(233, 77, 1, 'Middle', 'permanent', 'healthy', 'idkidk', 1, 'low', '2025-09-12 07:42:45', '2025-09-12 07:42:45', 'healthy', NULL, NULL),
(234, 77, 3, 'Middle', 'permanent', 'missing', 'missing', NULL, 'low', '2025-09-12 07:42:45', '2025-09-12 07:42:45', 'healthy', NULL, NULL),
(235, 77, 8, 'Crown', 'permanent', 'cavity', 'idk', NULL, 'low', '2025-09-12 07:42:45', '2025-09-12 07:42:45', 'healthy', NULL, NULL),
(236, 77, 9, 'Middle', 'permanent', 'healthy', '', NULL, 'low', '2025-09-12 07:42:45', '2025-09-12 07:42:45', 'healthy', NULL, NULL),
(237, 78, 1, '', 'permanent', 'healthy', 'idkidk', NULL, 'low', '2025-09-12 08:54:30', '2025-09-12 08:54:30', 'healthy', NULL, NULL),
(238, 78, 3, '', 'permanent', 'missing', 'missing', NULL, 'low', '2025-09-12 08:54:30', '2025-09-12 08:54:30', 'healthy', NULL, NULL),
(239, 78, 8, '', 'permanent', 'cavity', 'idk', NULL, 'low', '2025-09-12 08:54:30', '2025-09-12 08:54:30', 'healthy', NULL, NULL),
(240, 78, 9, '', 'permanent', 'healthy', '', NULL, 'low', '2025-09-12 08:54:30', '2025-09-12 08:54:30', 'healthy', NULL, NULL),
(241, 79, 1, '', 'permanent', 'healthy', 'idkidk', NULL, 'low', '2025-09-16 02:53:39', '2025-09-16 02:53:39', 'healthy', NULL, NULL),
(242, 79, 3, '', 'permanent', 'missing', 'missing', NULL, 'low', '2025-09-16 02:53:39', '2025-09-16 02:53:39', 'healthy', NULL, NULL),
(243, 79, 8, '', 'permanent', 'cavity', 'idk', NULL, 'low', '2025-09-16 02:53:39', '2025-09-16 02:53:39', 'healthy', NULL, NULL),
(244, 79, 9, '', 'permanent', 'healthy', '', NULL, 'low', '2025-09-16 02:53:39', '2025-09-16 02:53:39', 'healthy', NULL, NULL),
(245, 79, 32, '', 'permanent', 'cavity', '', NULL, 'low', '2025-09-16 02:53:39', '2025-09-16 02:53:39', 'healthy', NULL, NULL),
(246, 80, 1, NULL, 'permanent', 'healthy', 'idkidk', NULL, 'low', '2025-09-17 11:35:44', '2025-09-17 11:35:44', '', NULL, NULL),
(247, 80, 3, NULL, 'permanent', 'missing', 'missing', NULL, 'low', '2025-09-17 11:35:44', '2025-09-17 11:35:44', '', NULL, NULL),
(248, 80, 8, NULL, 'permanent', 'cavity', 'idk', NULL, 'low', '2025-09-17 11:35:44', '2025-09-17 11:35:44', '', NULL, NULL),
(249, 80, 9, NULL, 'permanent', 'healthy', '', NULL, 'low', '2025-09-17 11:35:44', '2025-09-17 11:35:44', '', NULL, NULL),
(250, 80, 31, NULL, 'permanent', 'cavity', 'e', NULL, 'low', '2025-09-17 11:35:44', '2025-09-17 11:35:44', '', NULL, NULL),
(251, 80, 32, NULL, 'permanent', 'cavity', '', NULL, 'low', '2025-09-17 11:35:44', '2025-09-17 11:35:44', '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dental_record`
--

CREATE TABLE `dental_record` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `xray_image_url` varchar(255) DEFAULT NULL,
  `next_appointment_date` date DEFAULT NULL,
  `next_appointment_id` int(11) DEFAULT NULL,
  `dentist_id` int(11) DEFAULT NULL,
  `visual_chart_data` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `dental_record`
--

INSERT INTO `dental_record` (`id`, `user_id`, `appointment_id`, `branch_id`, `record_date`, `treatment`, `notes`, `xray_image_url`, `next_appointment_date`, `next_appointment_id`, `dentist_id`, `visual_chart_data`) VALUES
(66, 15, 138, NULL, '2025-09-10', 'Routine dental cleaning and oral examination completed.', '', NULL, NULL, NULL, 1, NULL),
(67, 15, 139, NULL, '2025-09-10', 'Routine dental cleaning and oral examination completed.', '', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"http://localhost:8080/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":112.65132743362832,\"y\":258.88141592920357},{\"x\":112.65132743362832,\"y\":258.88141592920357},{\"x\":112.65132743362832,\"y\":259.9646017699115},{\"x\":120.23362831858408,\"y\":265.38053097345136},{\"x\":144.0637168141593,\"y\":283.79469026548674},{\"x\":198.22300884955752,\"y\":327.12212389380534},{\"x\":262.13097345132746,\"y\":378.03185840707965},{\"x\":318.4566371681416,\"y\":423.52566371681417},{\"x\":365.0336283185841,\"y\":462.5203539823009},{\"x\":392.1132743362832,\"y\":485.26725663716815},{\"x\":409.4442477876106,\"y\":498.2654867256637},{\"x\":418.10973451327436,\"y\":504.7646017699115}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":433.27433628318585,\"y\":252.38230088495575},{\"x\":432.1911504424779,\"y\":253.46548672566374},{\"x\":430.02477876106195,\"y\":255.63185840707965},{\"x\":419.19292035398234,\"y\":265.38053097345136},{\"x\":385.6141592920354,\"y\":296.7929203539823},{\"x\":317.37345132743366,\"y\":353.1185840707965},{\"x\":235.05132743362833,\"y\":415.9433628318584},{\"x\":155.9787610619469,\"y\":471.18584070796464},{\"x\":105.06902654867257,\"y\":505.8477876106195},{\"x\":84.48849557522124,\"y\":519.929203539823}]}]}'),
(68, 5, 140, NULL, '2025-09-10', 'Fluoride treatment applied for cavity prevention.', '', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":560,\"y\":273},{\"x\":560,\"y\":275},{\"x\":558,\"y\":300},{\"x\":555,\"y\":323},{\"x\":554,\"y\":342},{\"x\":554,\"y\":360},{\"x\":554,\"y\":373},{\"x\":556,\"y\":383},{\"x\":560,\"y\":394},{\"x\":563,\"y\":404},{\"x\":568,\"y\":411},{\"x\":571,\"y\":416},{\"x\":578,\"y\":422},{\"x\":589,\"y\":427},{\"x\":604,\"y\":429},{\"x\":617,\"y\":426},{\"x\":629,\"y\":413},{\"x\":638,\"y\":385},{\"x\":639,\"y\":346},{\"x\":638,\"y\":316},{\"x\":630,\"y\":295},{\"x\":617,\"y\":274},{\"x\":599,\"y\":256},{\"x\":584,\"y\":245},{\"x\":572,\"y\":243},{\"x\":568,\"y\":243}]}]}'),
(69, 5, 141, NULL, '2025-09-10', 'Routine dental cleaning and oral examination completed.', 'wala naman', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":560,\"y\":273},{\"x\":560,\"y\":275},{\"x\":558,\"y\":300},{\"x\":555,\"y\":323},{\"x\":554,\"y\":342},{\"x\":554,\"y\":360},{\"x\":554,\"y\":373},{\"x\":556,\"y\":383},{\"x\":560,\"y\":394},{\"x\":563,\"y\":404},{\"x\":568,\"y\":411},{\"x\":571,\"y\":416},{\"x\":578,\"y\":422},{\"x\":589,\"y\":427},{\"x\":604,\"y\":429},{\"x\":617,\"y\":426},{\"x\":629,\"y\":413},{\"x\":638,\"y\":385},{\"x\":639,\"y\":346},{\"x\":638,\"y\":316},{\"x\":630,\"y\":295},{\"x\":617,\"y\":274},{\"x\":599,\"y\":256},{\"x\":584,\"y\":245},{\"x\":572,\"y\":243},{\"x\":568,\"y\":243}]}]}'),
(70, 5, 142, NULL, '2025-09-10', 'Dental filling procedure completed on affected tooth.', '', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":560,\"y\":273},{\"x\":560,\"y\":275},{\"x\":558,\"y\":300},{\"x\":555,\"y\":323},{\"x\":554,\"y\":342},{\"x\":554,\"y\":360},{\"x\":554,\"y\":373},{\"x\":556,\"y\":383},{\"x\":560,\"y\":394},{\"x\":563,\"y\":404},{\"x\":568,\"y\":411},{\"x\":571,\"y\":416},{\"x\":578,\"y\":422},{\"x\":589,\"y\":427},{\"x\":604,\"y\":429},{\"x\":617,\"y\":426},{\"x\":629,\"y\":413},{\"x\":638,\"y\":385},{\"x\":639,\"y\":346},{\"x\":638,\"y\":316},{\"x\":630,\"y\":295},{\"x\":617,\"y\":274},{\"x\":599,\"y\":256},{\"x\":584,\"y\":245},{\"x\":572,\"y\":243},{\"x\":568,\"y\":243}]}]}'),
(71, 5, 143, NULL, '2025-09-10', 'Deep cleaning (scaling and root planing) performed.', '', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":560,\"y\":273},{\"x\":560,\"y\":275},{\"x\":558,\"y\":300},{\"x\":555,\"y\":323},{\"x\":554,\"y\":342},{\"x\":554,\"y\":360},{\"x\":554,\"y\":373},{\"x\":556,\"y\":383},{\"x\":560,\"y\":394},{\"x\":563,\"y\":404},{\"x\":568,\"y\":411},{\"x\":571,\"y\":416},{\"x\":578,\"y\":422},{\"x\":589,\"y\":427},{\"x\":604,\"y\":429},{\"x\":617,\"y\":426},{\"x\":629,\"y\":413},{\"x\":638,\"y\":385},{\"x\":639,\"y\":346},{\"x\":638,\"y\":316},{\"x\":630,\"y\":295},{\"x\":617,\"y\":274},{\"x\":599,\"y\":256},{\"x\":584,\"y\":245},{\"x\":572,\"y\":243},{\"x\":568,\"y\":243}]}]}'),
(72, 3, 144, NULL, '2025-09-10', 'Routine dental cleaning and oral examination completed.', '', NULL, NULL, NULL, 1, ''),
(73, 3, 145, NULL, '2025-09-11', 'Crown placement procedure completed.', '', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":308,\"y\":337},{\"x\":308,\"y\":336},{\"x\":311,\"y\":333},{\"x\":337,\"y\":315},{\"x\":386,\"y\":285},{\"x\":427,\"y\":273},{\"x\":452,\"y\":274},{\"x\":465,\"y\":296},{\"x\":468,\"y\":342},{\"x\":428,\"y\":411},{\"x\":333,\"y\":482},{\"x\":275,\"y\":516},{\"x\":256,\"y\":528},{\"x\":255,\"y\":530}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":307,\"y\":335},{\"x\":306,\"y\":335},{\"x\":301,\"y\":322},{\"x\":274,\"y\":287},{\"x\":231,\"y\":260},{\"x\":183,\"y\":256},{\"x\":153,\"y\":270},{\"x\":142,\"y\":294},{\"x\":141,\"y\":333},{\"x\":147,\"y\":381},{\"x\":201,\"y\":448},{\"x\":273,\"y\":494},{\"x\":295,\"y\":504}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":363,\"y\":328},{\"x\":364,\"y\":328}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":283,\"y\":312}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":284,\"y\":351},{\"x\":284,\"y\":352},{\"x\":284,\"y\":360},{\"x\":290,\"y\":371},{\"x\":299,\"y\":378},{\"x\":310,\"y\":381},{\"x\":329,\"y\":376},{\"x\":354,\"y\":355},{\"x\":364,\"y\":345},{\"x\":366,\"y\":343},{\"x\":367,\"y\":343}]}]}'),
(74, 15, 146, NULL, '2025-09-11', 'Crown placement procedure completed.', '', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":113,\"y\":259},{\"x\":113,\"y\":260},{\"x\":144,\"y\":284},{\"x\":262,\"y\":378},{\"x\":365,\"y\":463},{\"x\":409,\"y\":498},{\"x\":418,\"y\":505}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":433,\"y\":252},{\"x\":430,\"y\":256},{\"x\":386,\"y\":297},{\"x\":235,\"y\":416},{\"x\":105,\"y\":506},{\"x\":84,\"y\":520}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":854,\"y\":380},{\"x\":856,\"y\":377},{\"x\":858,\"y\":371},{\"x\":859,\"y\":368},{\"x\":855,\"y\":352},{\"x\":806,\"y\":322},{\"x\":682,\"y\":321},{\"x\":597,\"y\":377},{\"x\":589,\"y\":497},{\"x\":724,\"y\":619},{\"x\":853,\"y\":632},{\"x\":915,\"y\":573},{\"x\":913,\"y\":489},{\"x\":834,\"y\":416},{\"x\":662,\"y\":430},{\"x\":564,\"y\":525},{\"x\":556,\"y\":604},{\"x\":685,\"y\":614},{\"x\":766,\"y\":566},{\"x\":778,\"y\":523},{\"x\":627,\"y\":509},{\"x\":418,\"y\":556},{\"x\":335,\"y\":601}]}]}'),
(75, 15, 147, NULL, '2025-09-11', 'Dental filling procedure completed on affected tooth.', '', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":113,\"y\":259},{\"x\":144,\"y\":284},{\"x\":365,\"y\":463},{\"x\":418,\"y\":505}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":433,\"y\":252},{\"x\":386,\"y\":297},{\"x\":105,\"y\":506},{\"x\":84,\"y\":520}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":854,\"y\":380},{\"x\":858,\"y\":371},{\"x\":855,\"y\":352},{\"x\":682,\"y\":321},{\"x\":589,\"y\":497},{\"x\":853,\"y\":632},{\"x\":913,\"y\":489},{\"x\":662,\"y\":430},{\"x\":556,\"y\":604},{\"x\":766,\"y\":566},{\"x\":627,\"y\":509},{\"x\":335,\"y\":601}]},{\"tool\":\"pen\",\"color\":\"#2563eb\",\"size\":5,\"points\":[{\"x\":206,\"y\":463},{\"x\":210,\"y\":473},{\"x\":227,\"y\":501},{\"x\":249,\"y\":537},{\"x\":270,\"y\":569},{\"x\":282,\"y\":584},{\"x\":288,\"y\":584},{\"x\":287,\"y\":546},{\"x\":245,\"y\":492},{\"x\":156,\"y\":486},{\"x\":88,\"y\":541},{\"x\":73,\"y\":601},{\"x\":139,\"y\":631},{\"x\":248,\"y\":583},{\"x\":289,\"y\":532},{\"x\":286,\"y\":505},{\"x\":218,\"y\":526},{\"x\":153,\"y\":593},{\"x\":132,\"y\":651},{\"x\":188,\"y\":668},{\"x\":262,\"y\":628},{\"x\":287,\"y\":594},{\"x\":282,\"y\":583},{\"x\":237,\"y\":641},{\"x\":207,\"y\":704},{\"x\":207,\"y\":737},{\"x\":254,\"y\":703},{\"x\":288,\"y\":631},{\"x\":287,\"y\":583},{\"x\":268,\"y\":575},{\"x\":227,\"y\":623},{\"x\":214,\"y\":673},{\"x\":235,\"y\":703},{\"x\":285,\"y\":680},{\"x\":308,\"y\":628},{\"x\":304,\"y\":596},{\"x\":251,\"y\":603},{\"x\":185,\"y\":668},{\"x\":158,\"y\":723},{\"x\":171,\"y\":753},{\"x\":210,\"y\":753}]},{\"tool\":\"pen\",\"color\":\"#2563eb\",\"size\":5,\"points\":[{\"x\":322,\"y\":786},{\"x\":322,\"y\":785},{\"x\":315,\"y\":770},{\"x\":296,\"y\":738},{\"x\":271,\"y\":714},{\"x\":246,\"y\":710},{\"x\":229,\"y\":732},{\"x\":231,\"y\":776},{\"x\":263,\"y\":803},{\"x\":312,\"y\":782},{\"x\":331,\"y\":753},{\"x\":330,\"y\":735},{\"x\":296,\"y\":730},{\"x\":256,\"y\":749},{\"x\":245,\"y\":769},{\"x\":277,\"y\":766},{\"x\":352,\"y\":682},{\"x\":380,\"y\":617},{\"x\":383,\"y\":590},{\"x\":367,\"y\":590},{\"x\":349,\"y\":602}]}]}'),
(76, 15, 148, NULL, '2025-09-11', 'testing 5pm appoinmentstesting 5pm appoinmentstesting 5pm appoinments', '', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[]}'),
(77, 28, 149, NULL, '2025-09-12', 'Fluoride treatment applied for cavity prevention. on 18', 'none', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":250,\"y\":355},{\"x\":250,\"y\":357},{\"x\":250,\"y\":358},{\"x\":250,\"y\":362},{\"x\":251,\"y\":364},{\"x\":251,\"y\":363},{\"x\":251,\"y\":361},{\"x\":251,\"y\":360},{\"x\":251,\"y\":356},{\"x\":251,\"y\":354},{\"x\":251,\"y\":352},{\"x\":251,\"y\":351},{\"x\":251,\"y\":349},{\"x\":251,\"y\":347},{\"x\":251,\"y\":346},{\"x\":251,\"y\":345},{\"x\":251,\"y\":344},{\"x\":253,\"y\":343},{\"x\":253,\"y\":342},{\"x\":253,\"y\":343},{\"x\":253,\"y\":344},{\"x\":253,\"y\":345},{\"x\":251,\"y\":347},{\"x\":250,\"y\":350},{\"x\":250,\"y\":351},{\"x\":249,\"y\":352},{\"x\":249,\"y\":354},{\"x\":249,\"y\":355},{\"x\":249,\"y\":356},{\"x\":248,\"y\":358}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":503,\"y\":400},{\"x\":503,\"y\":401},{\"x\":503,\"y\":402},{\"x\":503,\"y\":403},{\"x\":503,\"y\":402},{\"x\":503,\"y\":400},{\"x\":503,\"y\":399},{\"x\":503,\"y\":402},{\"x\":502,\"y\":405},{\"x\":502,\"y\":407},{\"x\":502,\"y\":405},{\"x\":502,\"y\":400},{\"x\":502,\"y\":396},{\"x\":502,\"y\":395},{\"x\":502,\"y\":397},{\"x\":502,\"y\":399},{\"x\":502,\"y\":400},{\"x\":503,\"y\":402},{\"x\":503,\"y\":404},{\"x\":503,\"y\":405},{\"x\":505,\"y\":405},{\"x\":505,\"y\":407},{\"x\":506,\"y\":408},{\"x\":506,\"y\":410},{\"x\":507,\"y\":411},{\"x\":509,\"y\":412},{\"x\":511,\"y\":413},{\"x\":512,\"y\":413},{\"x\":514,\"y\":414},{\"x\":515,\"y\":415},{\"x\":513,\"y\":414},{\"x\":511,\"y\":412},{\"x\":510,\"y\":411},{\"x\":510,\"y\":410},{\"x\":509,\"y\":409},{\"x\":509,\"y\":408},{\"x\":509,\"y\":405},{\"x\":508,\"y\":403},{\"x\":508,\"y\":401}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":739,\"y\":387},{\"x\":739,\"y\":390},{\"x\":739,\"y\":392},{\"x\":740,\"y\":394},{\"x\":740,\"y\":395},{\"x\":743,\"y\":396},{\"x\":745,\"y\":398},{\"x\":746,\"y\":399},{\"x\":746,\"y\":400},{\"x\":745,\"y\":400},{\"x\":743,\"y\":399},{\"x\":740,\"y\":399},{\"x\":739,\"y\":397},{\"x\":738,\"y\":396},{\"x\":738,\"y\":395},{\"x\":738,\"y\":397},{\"x\":739,\"y\":401},{\"x\":743,\"y\":404},{\"x\":746,\"y\":407},{\"x\":747,\"y\":408},{\"x\":747,\"y\":407},{\"x\":746,\"y\":405},{\"x\":745,\"y\":403},{\"x\":744,\"y\":402},{\"x\":743,\"y\":401},{\"x\":743,\"y\":399},{\"x\":740,\"y\":397},{\"x\":740,\"y\":396},{\"x\":740,\"y\":395},{\"x\":740,\"y\":392},{\"x\":739,\"y\":390},{\"x\":739,\"y\":389}]}]}'),
(78, 28, 150, NULL, '2025-09-12', 'Routine dental cleaning and oral examination completed.', '', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":250,\"y\":355},{\"x\":250,\"y\":358},{\"x\":251,\"y\":364},{\"x\":251,\"y\":361},{\"x\":251,\"y\":356},{\"x\":251,\"y\":352},{\"x\":251,\"y\":349},{\"x\":251,\"y\":346},{\"x\":251,\"y\":344},{\"x\":253,\"y\":342},{\"x\":253,\"y\":344},{\"x\":251,\"y\":347},{\"x\":250,\"y\":351},{\"x\":249,\"y\":354},{\"x\":249,\"y\":356},{\"x\":248,\"y\":358}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":503,\"y\":400},{\"x\":503,\"y\":402},{\"x\":503,\"y\":399},{\"x\":502,\"y\":405},{\"x\":502,\"y\":396},{\"x\":502,\"y\":397},{\"x\":502,\"y\":400},{\"x\":503,\"y\":404},{\"x\":505,\"y\":405},{\"x\":506,\"y\":408},{\"x\":507,\"y\":411},{\"x\":511,\"y\":413},{\"x\":514,\"y\":414},{\"x\":513,\"y\":414},{\"x\":510,\"y\":411},{\"x\":509,\"y\":409},{\"x\":509,\"y\":405},{\"x\":508,\"y\":401}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":739,\"y\":387},{\"x\":739,\"y\":392},{\"x\":740,\"y\":395},{\"x\":745,\"y\":398},{\"x\":746,\"y\":400},{\"x\":743,\"y\":399},{\"x\":739,\"y\":397},{\"x\":738,\"y\":395},{\"x\":739,\"y\":401},{\"x\":746,\"y\":407},{\"x\":747,\"y\":407},{\"x\":745,\"y\":403},{\"x\":743,\"y\":401},{\"x\":740,\"y\":397},{\"x\":740,\"y\":395},{\"x\":739,\"y\":390},{\"x\":739,\"y\":389}]},{\"tool\":\"pen\",\"color\":\"#2563eb\",\"size\":5,\"points\":[{\"x\":101,\"y\":414},{\"x\":101,\"y\":415},{\"x\":101,\"y\":421},{\"x\":101,\"y\":422},{\"x\":101,\"y\":421},{\"x\":101,\"y\":419},{\"x\":101,\"y\":420},{\"x\":101,\"y\":425},{\"x\":101,\"y\":429},{\"x\":101,\"y\":430},{\"x\":101,\"y\":428},{\"x\":101,\"y\":421},{\"x\":101,\"y\":419},{\"x\":101,\"y\":417},{\"x\":102,\"y\":422},{\"x\":102,\"y\":424},{\"x\":102,\"y\":425},{\"x\":102,\"y\":424},{\"x\":102,\"y\":423},{\"x\":103,\"y\":423},{\"x\":103,\"y\":424}]},{\"tool\":\"pen\",\"color\":\"#2563eb\",\"size\":5,\"points\":[{\"x\":255,\"y\":419},{\"x\":256,\"y\":420},{\"x\":261,\"y\":420},{\"x\":270,\"y\":420},{\"x\":281,\"y\":420},{\"x\":285,\"y\":420},{\"x\":283,\"y\":420},{\"x\":267,\"y\":420},{\"x\":256,\"y\":422},{\"x\":253,\"y\":422},{\"x\":255,\"y\":422},{\"x\":261,\"y\":422},{\"x\":264,\"y\":421},{\"x\":262,\"y\":422},{\"x\":261,\"y\":424},{\"x\":263,\"y\":424},{\"x\":267,\"y\":424},{\"x\":269,\"y\":424},{\"x\":268,\"y\":426},{\"x\":263,\"y\":428},{\"x\":263,\"y\":429},{\"x\":266,\"y\":429},{\"x\":273,\"y\":428},{\"x\":275,\"y\":428},{\"x\":274,\"y\":428},{\"x\":273,\"y\":428},{\"x\":275,\"y\":427},{\"x\":284,\"y\":425}]}]}'),
(79, 28, 151, NULL, '2025-09-16', 'Fluoride treatment applied for cavity prevention.', '', NULL, NULL, NULL, 29, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":250,\"y\":355},{\"x\":250,\"y\":357},{\"x\":250,\"y\":358},{\"x\":250,\"y\":362},{\"x\":251,\"y\":364},{\"x\":251,\"y\":363},{\"x\":251,\"y\":361},{\"x\":251,\"y\":360},{\"x\":251,\"y\":356},{\"x\":251,\"y\":354},{\"x\":251,\"y\":352},{\"x\":251,\"y\":351},{\"x\":251,\"y\":349},{\"x\":251,\"y\":347},{\"x\":251,\"y\":346},{\"x\":251,\"y\":345},{\"x\":251,\"y\":344},{\"x\":253,\"y\":343},{\"x\":253,\"y\":342},{\"x\":253,\"y\":343},{\"x\":253,\"y\":344},{\"x\":253,\"y\":345},{\"x\":251,\"y\":347},{\"x\":250,\"y\":350},{\"x\":250,\"y\":351},{\"x\":249,\"y\":352},{\"x\":249,\"y\":354},{\"x\":249,\"y\":355},{\"x\":249,\"y\":356},{\"x\":248,\"y\":358}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":503,\"y\":400},{\"x\":503,\"y\":401},{\"x\":503,\"y\":402},{\"x\":503,\"y\":403},{\"x\":503,\"y\":402},{\"x\":503,\"y\":400},{\"x\":503,\"y\":399},{\"x\":503,\"y\":402},{\"x\":502,\"y\":405},{\"x\":502,\"y\":407},{\"x\":502,\"y\":405},{\"x\":502,\"y\":400},{\"x\":502,\"y\":396},{\"x\":502,\"y\":395},{\"x\":502,\"y\":397},{\"x\":502,\"y\":399},{\"x\":502,\"y\":400},{\"x\":503,\"y\":402},{\"x\":503,\"y\":404},{\"x\":503,\"y\":405},{\"x\":505,\"y\":405},{\"x\":505,\"y\":407},{\"x\":506,\"y\":408},{\"x\":506,\"y\":410},{\"x\":507,\"y\":411},{\"x\":509,\"y\":412},{\"x\":511,\"y\":413},{\"x\":512,\"y\":413},{\"x\":514,\"y\":414},{\"x\":515,\"y\":415},{\"x\":513,\"y\":414},{\"x\":511,\"y\":412},{\"x\":510,\"y\":411},{\"x\":510,\"y\":410},{\"x\":509,\"y\":409},{\"x\":509,\"y\":408},{\"x\":509,\"y\":405},{\"x\":508,\"y\":403},{\"x\":508,\"y\":401}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":739,\"y\":387},{\"x\":739,\"y\":390},{\"x\":739,\"y\":392},{\"x\":740,\"y\":394},{\"x\":740,\"y\":395},{\"x\":743,\"y\":396},{\"x\":745,\"y\":398},{\"x\":746,\"y\":399},{\"x\":746,\"y\":400},{\"x\":745,\"y\":400},{\"x\":743,\"y\":399},{\"x\":740,\"y\":399},{\"x\":739,\"y\":397},{\"x\":738,\"y\":396},{\"x\":738,\"y\":395},{\"x\":738,\"y\":397},{\"x\":739,\"y\":401},{\"x\":743,\"y\":404},{\"x\":746,\"y\":407},{\"x\":747,\"y\":408},{\"x\":747,\"y\":407},{\"x\":746,\"y\":405},{\"x\":745,\"y\":403},{\"x\":744,\"y\":402},{\"x\":743,\"y\":401},{\"x\":743,\"y\":399},{\"x\":740,\"y\":397},{\"x\":740,\"y\":396},{\"x\":740,\"y\":395},{\"x\":740,\"y\":392},{\"x\":739,\"y\":390},{\"x\":739,\"y\":389}]}]}'),
(80, 28, 162, NULL, '2025-09-17', 'Tooth extraction completed.', '', NULL, NULL, NULL, 1, '{\"version\":1,\"background\":\"/img/d.jpg\",\"width\":1224,\"height\":1224,\"strokes\":[{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":250,\"y\":355},{\"x\":250,\"y\":357},{\"x\":250,\"y\":358},{\"x\":250,\"y\":362},{\"x\":251,\"y\":364},{\"x\":251,\"y\":363},{\"x\":251,\"y\":361},{\"x\":251,\"y\":360},{\"x\":251,\"y\":356},{\"x\":251,\"y\":354},{\"x\":251,\"y\":352},{\"x\":251,\"y\":351},{\"x\":251,\"y\":349},{\"x\":251,\"y\":347},{\"x\":251,\"y\":346},{\"x\":251,\"y\":345},{\"x\":251,\"y\":344},{\"x\":253,\"y\":343},{\"x\":253,\"y\":342},{\"x\":253,\"y\":343},{\"x\":253,\"y\":344},{\"x\":253,\"y\":345},{\"x\":251,\"y\":347},{\"x\":250,\"y\":350},{\"x\":250,\"y\":351},{\"x\":249,\"y\":352},{\"x\":249,\"y\":354},{\"x\":249,\"y\":355},{\"x\":249,\"y\":356},{\"x\":248,\"y\":358}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":503,\"y\":400},{\"x\":503,\"y\":401},{\"x\":503,\"y\":402},{\"x\":503,\"y\":403},{\"x\":503,\"y\":402},{\"x\":503,\"y\":400},{\"x\":503,\"y\":399},{\"x\":503,\"y\":402},{\"x\":502,\"y\":405},{\"x\":502,\"y\":407},{\"x\":502,\"y\":405},{\"x\":502,\"y\":400},{\"x\":502,\"y\":396},{\"x\":502,\"y\":395},{\"x\":502,\"y\":397},{\"x\":502,\"y\":399},{\"x\":502,\"y\":400},{\"x\":503,\"y\":402},{\"x\":503,\"y\":404},{\"x\":503,\"y\":405},{\"x\":505,\"y\":405},{\"x\":505,\"y\":407},{\"x\":506,\"y\":408},{\"x\":506,\"y\":410},{\"x\":507,\"y\":411},{\"x\":509,\"y\":412},{\"x\":511,\"y\":413},{\"x\":512,\"y\":413},{\"x\":514,\"y\":414},{\"x\":515,\"y\":415},{\"x\":513,\"y\":414},{\"x\":511,\"y\":412},{\"x\":510,\"y\":411},{\"x\":510,\"y\":410},{\"x\":509,\"y\":409},{\"x\":509,\"y\":408},{\"x\":509,\"y\":405},{\"x\":508,\"y\":403},{\"x\":508,\"y\":401}]},{\"tool\":\"pen\",\"color\":\"#dc2626\",\"size\":5,\"points\":[{\"x\":739,\"y\":387},{\"x\":739,\"y\":390},{\"x\":739,\"y\":392},{\"x\":740,\"y\":394},{\"x\":740,\"y\":395},{\"x\":743,\"y\":396},{\"x\":745,\"y\":398},{\"x\":746,\"y\":399},{\"x\":746,\"y\":400},{\"x\":745,\"y\":400},{\"x\":743,\"y\":399},{\"x\":740,\"y\":399},{\"x\":739,\"y\":397},{\"x\":738,\"y\":396},{\"x\":738,\"y\":395},{\"x\":738,\"y\":397},{\"x\":739,\"y\":401},{\"x\":743,\"y\":404},{\"x\":746,\"y\":407},{\"x\":747,\"y\":408},{\"x\":747,\"y\":407},{\"x\":746,\"y\":405},{\"x\":745,\"y\":403},{\"x\":744,\"y\":402},{\"x\":743,\"y\":401},{\"x\":743,\"y\":399},{\"x\":740,\"y\":397},{\"x\":740,\"y\":396},{\"x\":740,\"y\":395},{\"x\":740,\"y\":392},{\"x\":739,\"y\":390},{\"x\":739,\"y\":389}]}]}');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `dental_chart_id` int(11) UNSIGNED DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `tooth_numbers` varchar(100) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'unpaid',
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `user_id`, `service_id`, `dental_chart_id`, `appointment_id`, `tooth_numbers`, `subtotal`, `discount_amount`, `total_amount`, `status`, `due_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 15, 1, NULL, 106, NULL, 1951.00, 0.00, 1951.00, 'unpaid', '2025-10-07', 'Auto invoice for appointment 106', 1, '2025-09-07 14:27:11', '2025-09-07 14:27:11'),
(2, 15, NULL, NULL, 121, NULL, 75.00, 0.00, 75.00, 'unpaid', '2025-10-07', 'Auto invoice for appointment 121', 10, '2025-09-07 08:24:35', '2025-09-07 08:24:35');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_id` int(11) UNSIGNED NOT NULL,
  `procedure_id` int(11) UNSIGNED DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2025-07-08-111501', 'App\\Database\\Migrations\\AddPatientFieldsToUsersTable', 'default', 'App', 1751973347, 1),
(2, '2025-07-08-162713', 'App\\Database\\Migrations\\AddStatusToUserTable', 'default', 'App', 1751992066, 2),
(3, '2025-07-11-195213', 'App\\Database\\Migrations\\RemoveSeparateNameFields', 'default', 'App', 1752263588, 3),
(4, '2025-07-18-022629', 'App\\Database\\Migrations\\AddDentistApprovalFieldsToAppointments', 'default', 'App', 1752805772, 4),
(5, '2025-07-18-201213', 'App\\Database\\Migrations\\MergeAppointmentDateTimeMigration', 'default', 'App', 1752869622, 5),
(6, '2025-07-20-065031', 'App\\Database\\Migrations\\AddWorkflowColumnsToAppointments', 'default', 'App', 1752994261, 6),
(7, '2025-07-20-063644', 'App\\Database\\Migrations\\CreateDentalChartTable', 'default', 'App', 1755398850, 7),
(8, '2025-08-12-000001', 'App\\Database\\Migrations\\AddMedicalHistoryFields', 'default', 'App', 1755398850, 7),
(9, '2025-08-16-153200', 'App\\Database\\Migrations\\AddNextAppointmentIdToDentalRecord', 'default', 'App', 1755398928, 8),
(10, '2025-08-17-000000', 'App\\Database\\Migrations\\SlimUserMedicalColumns', 'default', 'App', 1755398929, 8),
(11, '2025-08-17-010100', 'App\\Database\\Migrations\\CreatePatientMedicalHistory', 'default', 'App', 1755399602, 9),
(12, '2025-08-17-031500', 'App\\Database\\Migrations\\PatchPatientMedicalHistoryColumns', 'default', 'App', 1755399988, 10),
(13, '2025-08-17-003700', 'App\\Database\\Migrations\\ClearUserMedicalConditions', 'default', 'App', 1755391082, 11),
(14, '2025-08-17-070000', 'App\\Database\\Migrations\\CreatePatientCheckinsTable', 'default', 'App', 1755413553, 12),
(15, '2025-08-17-070100', 'App\\Database\\Migrations\\CreateTreatmentSessionsTable', 'default', 'App', 1755413553, 12),
(16, '2025-08-17-070200', 'App\\Database\\Migrations\\CreatePaymentsTable', 'default', 'App', 1755414334, 13),
(17, '2025-08-17-070300', 'App\\Database\\Migrations\\MigrateAppointmentDataToNewTables', 'default', 'App', 1755414334, 13),
(18, '2025-08-17-070400', 'App\\Database\\Migrations\\CleanupAppointmentsTable', 'default', 'App', 1755414335, 13),
(19, '2025-01-15-000000', 'App\\Database\\Migrations\\EnhanceProceduresTable', 'default', 'App', 1755719537, 14),
(20, '2025_09_10_000001', 'App\\Database\\Migrations\\CreateInvoiceItemsTable', 'default', 'App', 1758199649, 15),
(21, '20250911000100', 'App\\Database\\Migrations\\CreateRoles', 'default', 'App', 1758199649, 15),
(22, '20250911000200', 'App\\Database\\Migrations\\CreatePermissions', 'default', 'App', 1758199649, 15),
(23, '20250911000300', 'App\\Database\\Migrations\\CreateUserRoles', 'default', 'App', 1758199649, 15);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(10) UNSIGNED NOT NULL,
  `external_id` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('M','F','O') DEFAULT 'O',
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient_checkins`
--

CREATE TABLE `patient_checkins` (
  `id` int(11) UNSIGNED NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `checked_in_at` datetime NOT NULL,
  `checked_in_by` int(11) DEFAULT NULL COMMENT 'Staff who checked in the patient (null if self check-in)',
  `self_checkin` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether patient checked in themselves',
  `checkin_method` enum('staff','self','kiosk') NOT NULL DEFAULT 'staff',
  `notes` text DEFAULT NULL COMMENT 'Check-in notes or special instructions',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `removed_at` datetime DEFAULT NULL,
  `removed_by` int(10) UNSIGNED DEFAULT NULL,
  `removed_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_checkins`
--

INSERT INTO `patient_checkins` (`id`, `appointment_id`, `checked_in_at`, `checked_in_by`, `self_checkin`, `checkin_method`, `notes`, `created_at`, `updated_at`, `removed_at`, `removed_by`, `removed_reason`) VALUES
(12, 97, '2025-09-03 07:14:07', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-03 07:14:07', '2025-09-03 07:14:07', NULL, NULL, NULL),
(13, 98, '2025-09-03 07:35:10', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-03 07:35:10', '2025-09-03 07:35:10', NULL, NULL, NULL),
(14, 99, '2025-09-04 00:48:46', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-04 00:48:46', '2025-09-04 00:48:46', NULL, NULL, NULL),
(16, 101, '2025-09-05 00:39:45', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-05 00:39:45', '2025-09-05 00:39:45', NULL, NULL, NULL),
(17, 102, '2025-09-05 00:48:03', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-05 00:48:03', '2025-09-05 00:48:03', NULL, NULL, NULL),
(18, 103, '2025-09-05 01:47:21', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-05 01:47:21', '2025-09-05 01:47:21', NULL, NULL, NULL),
(19, 104, '2025-09-05 02:04:27', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-05 02:04:27', '2025-09-05 02:04:27', NULL, NULL, NULL),
(20, 105, '2025-09-06 00:04:31', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-06 00:04:31', '2025-09-06 00:04:31', NULL, NULL, NULL),
(21, 108, '2025-09-07 05:46:41', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 05:46:41', '2025-09-07 05:46:41', NULL, NULL, NULL),
(22, 110, '2025-09-07 07:10:41', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 07:10:41', '2025-09-07 07:10:41', NULL, NULL, NULL),
(23, 111, '2025-09-07 07:35:50', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 07:35:50', '2025-09-07 07:35:50', NULL, NULL, NULL),
(24, 112, '2025-09-07 07:37:15', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 07:37:15', '2025-09-07 07:37:15', NULL, NULL, NULL),
(25, 113, '2025-09-07 07:39:46', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 07:39:46', '2025-09-07 07:39:46', NULL, NULL, NULL),
(26, 114, '2025-09-07 07:41:24', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 07:41:24', '2025-09-07 07:41:24', NULL, NULL, NULL),
(27, 115, '2025-09-07 07:51:45', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 07:51:45', '2025-09-07 07:51:45', NULL, NULL, NULL),
(28, 116, '2025-09-07 07:55:00', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 07:55:00', '2025-09-07 07:55:00', NULL, NULL, NULL),
(29, 117, '2025-09-07 08:12:29', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 08:12:29', '2025-09-07 08:12:29', NULL, NULL, NULL),
(30, 119, '2025-09-07 08:16:44', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 08:16:44', '2025-09-07 08:16:44', NULL, NULL, NULL),
(31, 121, '2025-09-07 08:24:07', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 08:24:07', '2025-09-07 08:24:07', NULL, NULL, NULL),
(32, 122, '2025-09-07 07:05:41', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 07:05:41', '2025-09-07 07:05:41', NULL, NULL, NULL),
(33, 123, '2025-09-07 08:17:58', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 08:17:58', '2025-09-07 08:17:58', NULL, NULL, NULL),
(34, 124, '2025-09-07 13:09:09', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 13:09:09', '2025-09-07 13:09:09', NULL, NULL, NULL),
(35, 125, '2025-09-07 13:11:22', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 13:11:22', '2025-09-07 13:11:22', NULL, NULL, NULL),
(36, 126, '2025-09-07 15:19:29', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 15:19:29', '2025-09-07 15:19:29', NULL, NULL, NULL),
(37, 127, '2025-09-07 15:32:56', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-07 15:32:56', '2025-09-07 15:32:56', NULL, NULL, NULL),
(38, 107, '2025-09-08 01:50:16', 10, 0, 'staff', 'Patient checked in via admin interface', '2025-09-08 01:50:16', '2025-09-08 01:50:16', NULL, NULL, NULL),
(39, 129, '2025-09-09 11:05:30', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-09 11:05:30', '2025-09-09 11:05:30', NULL, NULL, NULL),
(40, 130, '2025-09-09 11:14:18', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-09 11:14:18', '2025-09-09 11:14:18', NULL, NULL, NULL),
(41, 131, '2025-09-09 11:20:33', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-09 11:20:33', '2025-09-09 11:20:33', NULL, NULL, NULL),
(42, 132, '2025-09-10 00:18:07', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 00:18:07', '2025-09-10 00:18:07', NULL, NULL, NULL),
(43, 133, '2025-09-10 06:37:56', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 06:37:56', '2025-09-10 06:37:56', NULL, NULL, NULL),
(44, 134, '2025-09-10 09:31:27', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 09:31:27', '2025-09-10 09:31:27', NULL, NULL, NULL),
(45, 135, '2025-09-10 09:34:17', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 09:34:17', '2025-09-10 09:34:17', NULL, NULL, NULL),
(46, 137, '2025-09-10 09:36:00', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 09:36:00', '2025-09-10 09:36:00', NULL, NULL, NULL),
(47, 138, '2025-09-10 10:52:03', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 10:52:03', '2025-09-10 10:52:03', NULL, NULL, NULL),
(48, 139, '2025-09-10 11:22:52', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 11:22:52', '2025-09-10 11:22:52', NULL, NULL, NULL),
(49, 140, '2025-09-10 11:37:56', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 11:37:56', '2025-09-10 11:37:56', NULL, NULL, NULL),
(50, 141, '2025-09-10 11:43:55', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 11:43:55', '2025-09-10 11:43:55', NULL, NULL, NULL),
(51, 143, '2025-09-10 12:00:16', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 12:00:16', '2025-09-10 12:00:16', NULL, NULL, NULL),
(52, 144, '2025-09-10 13:53:36', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-10 13:53:36', '2025-09-10 13:53:36', NULL, NULL, NULL),
(53, 147, '2025-09-11 08:55:00', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-11 08:55:00', '2025-09-11 08:55:00', NULL, NULL, NULL),
(54, 148, '2025-09-11 10:03:38', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-11 10:03:38', '2025-09-11 10:03:38', NULL, NULL, NULL),
(55, 149, '2025-09-12 07:40:51', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-12 07:40:51', '2025-09-12 07:40:51', NULL, NULL, NULL),
(56, 150, '2025-09-12 08:54:06', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-12 08:54:06', '2025-09-12 08:54:06', NULL, NULL, NULL),
(57, 151, '2025-09-16 02:21:37', 29, 0, 'staff', 'Patient checked in via admin interface', '2025-09-16 02:21:37', '2025-09-16 02:21:37', NULL, NULL, NULL),
(58, 162, '2025-09-17 11:34:50', 1, 0, 'staff', 'Patient checked in via admin interface', '2025-09-17 11:34:50', '2025-09-17 11:34:50', NULL, NULL, NULL),
(59, 186, '2025-09-17 11:50:35', 29, 0, 'staff', 'Patient checked in via admin interface', '2025-09-17 11:50:35', '2025-09-17 11:50:35', NULL, NULL, NULL),
(65, 210, '2025-09-18 08:55:00', NULL, 0, 'staff', NULL, '2025-09-18 09:43:53', NULL, NULL, NULL, NULL),
(66, 212, '2025-09-18 08:55:00', NULL, 0, 'staff', NULL, '2025-09-18 09:52:07', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patient_guardian`
--

CREATE TABLE `patient_guardian` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `relationship` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient_medical_history`
--

CREATE TABLE `patient_medical_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `good_health` enum('yes','no') DEFAULT NULL,
  `under_treatment` enum('yes','no') DEFAULT NULL,
  `treatment_condition` text DEFAULT NULL,
  `serious_illness` enum('yes','no') DEFAULT NULL,
  `illness_details` text DEFAULT NULL,
  `hospitalized` enum('yes','no') DEFAULT NULL,
  `hospitalization_where` varchar(255) DEFAULT NULL,
  `hospitalization_when` varchar(255) DEFAULT NULL,
  `hospitalization_why` varchar(255) DEFAULT NULL,
  `tobacco_use` enum('yes','no') DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `pregnant` enum('yes','no','na') DEFAULT NULL,
  `nursing` enum('yes','no','na') DEFAULT NULL,
  `birth_control` enum('yes','no','na') DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `other_conditions` text DEFAULT NULL,
  `previous_dentist` varchar(255) DEFAULT NULL,
  `last_dental_visit` date DEFAULT NULL,
  `physician_name` varchar(255) DEFAULT NULL,
  `physician_specialty` varchar(255) DEFAULT NULL,
  `physician_phone` varchar(20) DEFAULT NULL,
  `physician_address` text DEFAULT NULL,
  `current_treatment` text DEFAULT NULL,
  `hospitalization_details` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_medical_history`
--

INSERT INTO `patient_medical_history` (`id`, `user_id`, `good_health`, `under_treatment`, `treatment_condition`, `serious_illness`, `illness_details`, `hospitalized`, `hospitalization_where`, `hospitalization_when`, `hospitalization_why`, `tobacco_use`, `blood_pressure`, `allergies`, `pregnant`, `nursing`, `birth_control`, `medical_conditions`, `other_conditions`, `previous_dentist`, `last_dental_visit`, `physician_name`, `physician_specialty`, `physician_phone`, `physician_address`, `current_treatment`, `hospitalization_details`, `created_at`, `updated_at`) VALUES
(2, 3, 'yes', 'no', NULL, 'no', NULL, 'no', NULL, NULL, NULL, 'no', NULL, NULL, 'yes', NULL, NULL, '\"[\\\"aids_hiv\\\",\\\"fainting\\\"]\"', NULL, 'patient_medical_history', NULL, 'patient_medical_history', 'patient_medical_history', 'patient_medical_hist', 'patient_medical_history', NULL, NULL, '2025-08-17 00:19:06', '2025-08-17 00:19:06'),
(3, 17, 'yes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-18 06:53:57', '2025-08-18 06:53:57'),
(4, 15, 'no', 'no', NULL, 'no', NULL, 'no', NULL, NULL, NULL, 'no', NULL, NULL, 'no', 'yes', 'yes', '[\"anemia\",\"angina\",\"asthma\",\"emphysema\",\"bleeding_problem\",\"blood_disease\",\"head_injuries\",\"arthritis\"]', 'BRandonBRandonBRandonBRandonBRandon', 'nabuakkk', '2025-08-12', 'BRandon', 'BRandon', '908098089809809', 'BRandon', NULL, NULL, '2025-08-20 19:49:51', '2025-08-24 15:11:14');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `payment_status` enum('pending','paid','partial','waived','refunded') NOT NULL DEFAULT 'pending' COMMENT 'Payment status',
  `payment_method` enum('cash','card','bank_transfer','gcash','paymaya','insurance') DEFAULT NULL COMMENT 'Payment method used',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total amount due',
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount actually paid',
  `balance_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Remaining balance',
  `payment_date` datetime DEFAULT NULL COMMENT 'When payment was made',
  `payment_received_by` int(11) DEFAULT NULL COMMENT 'Staff who received payment',
  `payment_notes` text DEFAULT NULL COMMENT 'Payment notes',
  `invoice_number` varchar(50) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL COMMENT 'External payment reference (e.g., bank transaction ID)',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `module` varchar(150) NOT NULL,
  `action` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `dentist_name` varchar(255) DEFAULT NULL,
  `license_no` varchar(100) DEFAULT NULL,
  `ptr_no` varchar(100) DEFAULT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `next_appointment` date DEFAULT NULL,
  `status` enum('draft','final','cancelled') DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `signature_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `dentist_id`, `dentist_name`, `license_no`, `ptr_no`, `patient_id`, `appointment_id`, `issue_date`, `next_appointment`, `status`, `notes`, `signature_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'Admin User', '123213213', '3212321', 15, NULL, '2025-09-11', '0000-00-00', 'draft', 'wala', NULL, '2025-09-11 11:19:16', '2025-09-11 11:22:09');

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
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `prescription_items`
--

INSERT INTO `prescription_items` (`id`, `prescription_id`, `medicine_name`, `dosage`, `frequency`, `duration`, `instructions`) VALUES
(2, 1, 'iiii', 'iiii', 'iii', 'iii', 'iiii');

-- --------------------------------------------------------

--
-- Table structure for table `procedures`
--

CREATE TABLE `procedures` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `procedure_name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'none',
  `fee` decimal(10,2) DEFAULT NULL,
  `treatment_area` varchar(100) DEFAULT 'Surface',
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `procedure_date` date DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `procedure_service`
--

CREATE TABLE `procedure_service` (
  `id` int(11) NOT NULL,
  `procedure_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `duration_minutes` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `duration_max_minutes` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `duration_minutes`, `created_at`, `updated_at`, `duration_max_minutes`) VALUES
(1, 'Dental Checkup', 'Comprehensive dental examination and cleanings', 75.00, 15, '2025-09-05 09:58:03', '2025-09-18 06:18:32', NULL),
(2, 'Teeth Whitening', 'Professional teeth whitening treatment', 150.00, 120, '2025-09-05 09:58:03', '2025-09-18 00:57:25', 240),
(3, 'Cavity Filling', 'Dental filling for cavities', 120.00, NULL, '2025-09-05 09:58:03', '2025-09-05 09:58:03', NULL),
(4, 'Orthodontic Treatment (Braces)', 'Orthodontic treatment using brackets/wires to align teeth.', 50000.00, 120, '2025-09-05 09:58:03', '2025-09-18 17:56:33', 180),
(5, 'Cleaning Max', 'Professional deep cleaning of teeth and gums.', 2500.00, 30, '2025-09-05 09:58:03', '2025-09-18 17:55:23', NULL),
(6, 'Tooth Extraction', 'Simple tooth extraction', 200.00, NULL, '2025-09-05 09:58:03', '2025-09-05 09:58:03', NULL),
(7, 'Surgery Impacted', 'Surgical removal of an impacted tooth, often a wisdom tooth.', 1000.00, 120, '2025-09-06 16:29:23', '2025-09-18 17:56:12', NULL),
(8, 'Dental Filling(Pasta)', 'Repair tooth decay or damage using filling material.', 1000.00, 30, '2025-09-11 23:28:07', '2025-09-18 17:54:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `treatment_sessions`
--

CREATE TABLE `treatment_sessions` (
  `id` int(11) UNSIGNED NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `started_at` datetime NOT NULL COMMENT 'When treatment started',
  `ended_at` datetime DEFAULT NULL COMMENT 'When treatment ended',
  `called_by` int(11) DEFAULT NULL COMMENT 'Dentist who called the patient',
  `dentist_id` int(11) DEFAULT NULL COMMENT 'Primary dentist for this session',
  `treatment_status` enum('in_progress','completed','paused','cancelled') NOT NULL DEFAULT 'in_progress' COMMENT 'Current treatment status',
  `treatment_notes` text DEFAULT NULL COMMENT 'Treatment progress notes',
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `room_number` varchar(20) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL COMMENT 'Actual treatment duration in minutes',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `treatment_sessions`
--

INSERT INTO `treatment_sessions` (`id`, `appointment_id`, `started_at`, `ended_at`, `called_by`, `dentist_id`, `treatment_status`, `treatment_notes`, `priority`, `room_number`, `duration_minutes`, `created_at`, `updated_at`) VALUES
(12, 97, '2025-09-03 07:14:12', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-03 07:14:12', '2025-09-03 07:14:12'),
(13, 98, '2025-09-03 07:35:14', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-03 07:35:14', '2025-09-03 07:35:14'),
(14, 99, '2025-09-04 00:48:53', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-04 00:48:53', '2025-09-04 00:48:53'),
(16, 101, '2025-09-05 00:39:51', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-05 00:39:51', '2025-09-05 00:39:51'),
(17, 102, '2025-09-05 00:48:07', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-05 00:48:07', '2025-09-05 00:48:07'),
(18, 103, '2025-09-05 01:47:24', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-05 01:47:24', '2025-09-05 01:47:24'),
(19, 104, '2025-09-05 02:04:31', NULL, 1, 18, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-05 02:04:31', '2025-09-05 02:04:31'),
(20, 105, '2025-09-06 00:04:33', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-06 00:04:33', '2025-09-06 00:04:33'),
(21, 108, '2025-09-07 05:46:43', NULL, 10, 18, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 05:46:43', '2025-09-07 05:46:43'),
(22, 110, '2025-09-07 07:10:47', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 07:10:47', '2025-09-07 07:10:47'),
(23, 111, '2025-09-07 07:35:52', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 07:35:52', '2025-09-07 07:35:52'),
(24, 112, '2025-09-07 07:37:18', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 07:37:18', '2025-09-07 07:37:18'),
(25, 113, '2025-09-07 07:39:48', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 07:39:48', '2025-09-07 07:39:48'),
(26, 114, '2025-09-07 07:41:26', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 07:41:26', '2025-09-07 07:41:26'),
(27, 115, '2025-09-07 07:51:46', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 07:51:46', '2025-09-07 07:51:46'),
(28, 116, '2025-09-07 07:55:02', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 07:55:02', '2025-09-07 07:55:02'),
(29, 117, '2025-09-07 08:12:33', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 08:12:33', '2025-09-07 08:12:33'),
(30, 119, '2025-09-07 08:16:46', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 08:16:46', '2025-09-07 08:16:46'),
(31, 121, '2025-09-07 08:24:10', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 08:24:10', '2025-09-07 08:24:10'),
(32, 122, '2025-09-07 07:05:42', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 07:05:42', '2025-09-07 07:05:42'),
(33, 123, '2025-09-07 08:18:00', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 08:18:00', '2025-09-07 08:18:00'),
(34, 124, '2025-09-07 13:09:11', NULL, 10, 18, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 13:09:11', '2025-09-07 13:09:11'),
(35, 125, '2025-09-07 13:11:24', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 13:11:24', '2025-09-07 13:11:24'),
(36, 126, '2025-09-07 15:19:34', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 15:19:34', '2025-09-07 15:19:34'),
(37, 127, '2025-09-07 15:32:59', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-07 15:32:59', '2025-09-07 15:32:59'),
(38, 107, '2025-09-08 01:50:20', NULL, 10, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-08 01:50:20', '2025-09-08 01:50:20'),
(39, 129, '2025-09-09 11:05:32', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-09 11:05:32', '2025-09-09 11:05:32'),
(40, 130, '2025-09-09 11:14:20', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-09 11:14:20', '2025-09-09 11:14:20'),
(41, 131, '2025-09-09 11:20:36', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-09 11:20:36', '2025-09-09 11:20:36'),
(42, 132, '2025-09-10 00:18:10', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 00:18:10', '2025-09-10 00:18:10'),
(43, 133, '2025-09-10 06:37:58', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 06:37:58', '2025-09-10 06:37:58'),
(44, 134, '2025-09-10 09:31:29', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 09:31:29', '2025-09-10 09:31:29'),
(45, 135, '2025-09-10 09:34:20', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 09:34:20', '2025-09-10 09:34:20'),
(46, 137, '2025-09-10 09:36:03', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 09:36:03', '2025-09-10 09:36:03'),
(47, 138, '2025-09-10 10:52:05', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 10:52:05', '2025-09-10 10:52:05'),
(48, 139, '2025-09-10 11:22:54', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 11:22:54', '2025-09-10 11:22:54'),
(49, 140, '2025-09-10 11:37:58', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 11:37:58', '2025-09-10 11:37:58'),
(50, 141, '2025-09-10 11:43:57', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 11:43:57', '2025-09-10 11:43:57'),
(51, 143, '2025-09-10 12:00:20', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 12:00:20', '2025-09-10 12:00:20'),
(52, 144, '2025-09-10 13:53:38', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-10 13:53:38', '2025-09-10 13:53:38'),
(53, 147, '2025-09-11 08:55:03', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-11 08:55:03', '2025-09-11 08:55:03'),
(54, 148, '2025-09-11 10:03:41', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-11 10:03:41', '2025-09-11 10:03:41'),
(55, 149, '2025-09-12 07:40:53', NULL, 1, 16, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-12 07:40:53', '2025-09-12 07:40:53'),
(56, 150, '2025-09-12 08:54:11', NULL, 1, 18, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-12 08:54:11', '2025-09-12 08:54:11'),
(57, 151, '2025-09-16 02:21:43', NULL, 29, 30, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-16 02:21:43', '2025-09-16 02:21:43'),
(58, 162, '2025-09-17 11:35:01', NULL, 1, 30, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-17 11:35:01', '2025-09-17 11:35:01'),
(59, 210, '2025-09-18 01:46:19', NULL, 1, 1, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-18 01:46:19', '2025-09-18 01:46:19'),
(60, 212, '2025-09-18 03:00:48', NULL, 1, 1, 'in_progress', NULL, 'normal', NULL, NULL, '2025-09-18 03:00:48', '2025-09-18 03:00:48');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `user_type`, `name`, `address`, `email`, `gender`, `password`, `phone`, `created_at`, `updated_at`, `occupation`, `nationality`, `date_of_birth`, `age`, `status`) VALUES
(1, 'admin', 'Admin User', '123 Admin Street', 'admin@perfectsmile.com', 'male', '$2y$12$Tau0ciyH4Ny3A/P0I.qEbO5i63Ja4AtTPBd4OblwwAnoQLCYokErS', '1234567890', '2025-06-28 16:02:14', '2025-06-28 16:02:14', NULL, NULL, NULL, NULL, 'active'),
(3, 'patient', 'Patient Jane', '789 Patient Road', 'patient@perfectsmile.com', 'Female', '$2y$12$56jBwCaavme4yDUKOyaqzOTaogfs.qBZMe3.17Thz8OxkBT5SyRIW', '1234567892', '2025-06-28 16:02:14', '2025-09-09 14:00:28', 'hh', 'oo', '2025-07-08', 9, 'active'),
(5, 'patient', 'Brandon Brandon Brandon', 'Brandon@gmail.com', 'Brandon@gmail.com', 'Male', '$2y$12$WBK7ZzUAxGcqO2DFmKaqaOxwAGqIrMa3EXdY3kVvZtpAOB2Te8PbS', '89078007077', '2025-07-08 13:39:16', '2025-08-18 06:57:50', 'Brandon', 'Brandon@gmail.com', '2025-07-03', 18, 'inactive'),
(10, 'admin', 'Brandon Dentist', NULL, 'don@gmail.com', 'male', '$2y$12$9BzUNUBkE5idKzb1qdz78ePPo8HsVRgCMm9ZJnjaZDIvRIqcnYW8S', '09150540702', '2025-08-07 05:30:50', '2025-08-07 07:25:15', 'na', 'na', NULL, NULL, 'active'),
(15, 'patient', 'Eden Caritos', 'PIli Camsur', 'eden@gmail.com', 'Female', '$2y$12$UCxvaWFzAwxJSjK2LBqTGuRXUJaDYbc7rtU9culVbP2SqV/pV/DeS', '099150540702', '2025-08-08 11:06:41', '2025-09-11 11:22:09', 'Nurse', 'Filipino', '2001-09-11', 23, 'active'),
(16, 'dentist', 'Nabua Dentist', NULL, 'nabua@perfectsmile.com', 'Male', '$2y$12$Xype.o./nlBf/u8Ymshd8eL/XZXhblBA0FR.EgIuljKJQjA6XDsEq', '09150540702', '2025-08-09 03:14:00', '2025-08-09 03:14:00', 'na', 'na', '2000-09-23', 24, 'active'),
(17, 'patient', 'Johnbert', 'jjjjjj', 'jb2g@gmail.com', 'Female', NULL, '412812482148', '2025-08-09 05:43:01', '2025-08-22 15:32:02', 'jjj', 'jjj', '2003-08-07', 22, 'active'),
(18, 'dentist', 'Iriga Dentist', NULL, 'iriga@gmail.com', '', '$2y$12$Xrdzm.Nb7v.hkLdr//9AO.ZCN3MSx2wANm8gY47aYP7tKyQqIK6Qm', '3903983098809', '2025-08-09 11:43:06', '2025-08-09 11:43:06', 'nnn', 'nnn', '2000-08-23', 24, 'active'),
(19, 'patient', 'johnberto', 'na', 'jb@gmail.com', 'Male', '$2y$12$oIZqfK2HqB4WK1drwxyqjunhzz7MUnjtTKsugV/QHkiAhu90FA.iC', '789789789978', '2025-08-11 03:19:20', '2025-08-13 03:28:26', 'na', 'na', '2003-08-30', 21, 'active'),
(20, 'staff', 'Brandon Dentist', NULL, 'adminn@perfectsmile.com', 'Male', '$2y$12$nw2ocoPA/fAScG/vvSSO..H0AmbVOV.K6WY2YULYbX9hMFuUCekkW', '09150540702', '2025-08-13 04:12:57', '2025-08-13 04:12:57', 'nnnn', 'nnnnn', '2007-05-11', 18, 'active'),
(21, 'staff', 'werwewer', NULL, 'jogn@gmail.com', 'Male', '$2y$12$NttcnqQe/BNTOGOjx6o1auet5SBybDsHkE92EGcvj89LcUx963Cra', '2412124142124', '2025-08-13 04:29:26', '2025-08-13 04:29:26', 'jogn@gmail.com', 'jogn@gmail.com', '2000-07-31', NULL, 'active'),
(22, 'patient', 'Testing 001 ', 'testing', 'testing@gmail.com', 'Male', NULL, '123456789', '2025-08-16 13:07:00', '2025-08-16 13:07:00', 'testing', 'testing', '2003-08-08', 22, 'active'),
(23, 'staff', 'Johnbert', NULL, 'jbb@gmail.com', 'Female', '$2y$12$n7lJ7mnvO4GeV/REQQpzMOpSGRc.d0lW324pY13fc5twu2F5mq4iK', '09150540702', '2025-08-20 02:07:02', '2025-08-20 02:07:02', '', '', '2000-02-03', 25, 'active'),
(24, 'staff', 'diana ', NULL, 'd@gmail.com', 'Female', '$2y$12$WrH1I/NedwIb4JwJ3TE2Ou5hT0DHF1nJjqg5TlKUWzPSJHZWtb09S', '09150540702', '2025-08-22 17:40:48', '2025-08-22 17:40:48', 'd@gmail.com', 'd@gmail.com', '1992-01-08', 33, 'active'),
(27, 'dentist', 'Den', NULL, 'den@gmail.com', 'Female', '$2y$12$NTHf1vetcN/8vDmVh3o07uJ0n3fFfuqyUtryxx76P/Z7B3ZOd2iUK', '98008089098', '2025-08-24 08:31:18', '2025-09-10 14:30:11', '', '', '2000-05-24', 25, 'active'),
(28, 'patient', 'Marc Aaron Gamban', 'nnnq', 'gamban@gmail.com', 'Male', '$2y$10$tg9LdDxIC3E.y5Y482.1V.Lx93TNcc39trGWnxCNgmoUvJK5HT0Cq', '09150540703', '2025-09-12 07:40:18', '2025-09-16 22:57:40', 'testing', 'jogn@gmail.com', '2000-09-14', NULL, 'active'),
(29, 'staff', 'staff-PerfectSmile', NULL, 'staff-account@gmail.com', 'Female', '$2y$10$J9UG6uY5Y0GDbIzVwFOon.V6TAGmfCRaY2CzTFAV6WpSjiWsganYK', '09948804318', '2025-09-16 01:31:38', '2025-09-16 01:31:38', 'student', 'filipino', '1999-05-05', 26, 'active'),
(30, 'dentist', 'Minnie Arroyo Gonowon', NULL, 'perfectsmile-dentist@gmail.com', 'Female', '$2y$10$x.uQwujBWZ3qN4rFYAVUZ.o4ClMyMT84VnKLn/YSfGxq11K62VJ12', '09948804320', '2025-09-16 01:36:20', '2025-09-16 01:36:20', 'Doctor', 'filipino', '1980-03-03', 45, 'active'),
(31, 'patient', 'Smoke User 1', NULL, 'smoke.user1+1758106145@example.com', NULL, 'smokepass', '0000000000', '2025-09-17 12:49:05', '2025-09-17 12:49:05', NULL, NULL, NULL, NULL, 'active'),
(32, 'patient', 'Smoke User 2', NULL, 'smoke.user2+1758106145@example.com', NULL, 'smokepass', '0000000000', '2025-09-17 12:49:05', '2025-09-17 12:49:05', NULL, NULL, NULL, NULL, 'active'),
(33, 'patient', 'Smoke User 1', NULL, 'smoke.user1+1758106284@example.com', NULL, 'smokepass', '0000000000', '2025-09-17 12:51:24', '2025-09-17 12:51:24', NULL, NULL, NULL, NULL, 'active'),
(34, 'patient', 'Smoke User 2', NULL, 'smoke.user2+1758106284@example.com', NULL, 'smokepass', '0000000000', '2025-09-17 12:51:24', '2025-09-17 12:51:24', NULL, NULL, NULL, NULL, 'active'),
(35, 'patient', 'Smoke User 1', NULL, 'smoke.user1+1758106798@example.com', NULL, 'smokepass', '0000000000', '2025-09-17 12:59:58', '2025-09-17 12:59:58', NULL, NULL, NULL, NULL, 'active'),
(36, 'patient', 'Smoke User 2', NULL, 'smoke.user2+1758106798@example.com', NULL, 'smokepass', '0000000000', '2025-09-17 12:59:58', '2025-09-17 12:59:58', NULL, NULL, NULL, NULL, 'active'),
(38, 'patient', 'lazy', 'buhi', 'bert@gmail.com', 'Male', '$2y$10$0SbUTknLUUVJsXaU/1C0zu/It1gO4I8xLJBrSMI1usYAwUwcS3e0y', '09771169214', '2025-09-17 11:15:41', '2025-09-17 15:32:12', 'student', 'pinoy', '2000-09-06', NULL, 'active'),
(40, 'patient', 'Smoke Scheduled A', NULL, 'smoke_a@example.local', NULL, NULL, NULL, '2025-09-18 03:26:58', NULL, NULL, NULL, NULL, NULL, 'active'),
(41, 'patient', 'Smoke Walkin B', NULL, 'smoke_b@example.local', NULL, NULL, NULL, '2025-09-18 03:26:58', NULL, NULL, NULL, NULL, NULL, 'active'),
(42, 'staff', 'Smoke Staff', NULL, 'smoke_staff@example.local', NULL, '$2y$10$H1s4Qf6/AfQJAryRZt/H1OoUVIvp8meZ/ewEXT2wdisEKv16P2kMu', NULL, '2025-09-18 03:41:40', NULL, NULL, NULL, NULL, NULL, 'active'),
(43, 'patient', 'Walkin A', NULL, 'walkin_a@example.local', NULL, '$2y$10$iubYqLjSg4R.sxboFABmGuqI9t2VKSxqN/5.4FGJTcRZMsRdDaqsC', NULL, '2025-09-18 03:52:06', NULL, NULL, NULL, NULL, NULL, 'active'),
(44, 'patient', 'Online B', NULL, 'online_b@example.local', NULL, '$2y$10$uUQnCDisTv/utmPSD/HWh.c7Rjqwl3dyzjWC1z30KwZvb7OzROlQq', NULL, '2025-09-18 03:52:06', NULL, NULL, NULL, NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD KEY `fk_appointments_dentist` (`dentist_id`),
  ADD KEY `idx_appointments_guest_email` (`patient_email`),
  ADD KEY `idx_appointments_guest_phone` (`patient_phone`);

--
-- Indexes for table `appointment_service`
--
ALTER TABLE `appointment_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_availability_user` (`user_id`),
  ADD KEY `idx_availability_start_end` (`start_datetime`,`end_datetime`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branch_notifications`
--
ALTER TABLE `branch_notifications`
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
  ADD UNIQUE KEY `uq_dental_chart_record_tooth` (`dental_record_id`,`tooth_number`),
  ADD KEY `dental_record_id` (`dental_record_id`),
  ADD KEY `tooth_number` (`tooth_number`),
  ADD KEY `idx_dental_chart_service_id` (`service_id`);

--
-- Indexes for table `dental_record`
--
ALTER TABLE `dental_record`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `dentist_id` (`dentist_id`),
  ADD KEY `fk_dental_record_appointment` (`appointment_id`),
  ADD KEY `fk_dental_record_next_appointment` (`next_appointment_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_inv_appt_id` (`appointment_id`),
  ADD KEY `fk_inv_chart_id` (`dental_chart_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_patients_external` (`external_id`);

--
-- Indexes for table `patient_checkins`
--
ALTER TABLE `patient_checkins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `checked_in_by` (`checked_in_by`);

--
-- Indexes for table `patient_guardian`
--
ALTER TABLE `patient_guardian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `patient_medical_history`
--
ALTER TABLE `patient_medical_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_unique` (`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `payment_received_by` (`payment_received_by`),
  ADD KEY `payment_status` (`payment_status`),
  ADD KEY `invoice_number` (`invoice_number`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_procedure_date` (`procedure_date`);

--
-- Indexes for table `procedure_service`
--
ALTER TABLE `procedure_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `procedure_id` (`procedure_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `treatment_sessions`
--
ALTER TABLE `treatment_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `called_by` (`called_by`),
  ADD KEY `dentist_id` (`dentist_id`),
  ADD KEY `treatment_status` (`treatment_status`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_roles_role_id_foreign` (`role_id`),
  ADD KEY `user_id_role_id` (`user_id`,`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=270;

--
-- AUTO_INCREMENT for table `appointment_service`
--
ALTER TABLE `appointment_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branch_notifications`
--
ALTER TABLE `branch_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `branch_user`
--
ALTER TABLE `branch_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `dental_chart`
--
ALTER TABLE `dental_chart`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT for table `dental_record`
--
ALTER TABLE `dental_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_checkins`
--
ALTER TABLE `patient_checkins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `patient_guardian`
--
ALTER TABLE `patient_guardian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_medical_history`
--
ALTER TABLE `patient_medical_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `treatment_sessions`
--
ALTER TABLE `treatment_sessions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_appointments_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
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
-- Constraints for table `dental_chart`
--
ALTER TABLE `dental_chart`
  ADD CONSTRAINT `fk_dental_chart_record` FOREIGN KEY (`dental_record_id`) REFERENCES `dental_record` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dental_chart_service_id` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `dental_record`
--
ALTER TABLE `dental_record`
  ADD CONSTRAINT `dental_record_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `dental_record_ibfk_2` FOREIGN KEY (`dentist_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_dental_record_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dental_record_next_appointment` FOREIGN KEY (`next_appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_inv_appt_id` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_chart_id` FOREIGN KEY (`dental_chart_id`) REFERENCES `dental_chart` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_service_id` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `patient_checkins`
--
ALTER TABLE `patient_checkins`
  ADD CONSTRAINT `patient_checkins_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `patient_checkins_checked_in_by_foreign` FOREIGN KEY (`checked_in_by`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `patient_guardian`
--
ALTER TABLE `patient_guardian`
  ADD CONSTRAINT `patient_guardian_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `patient_medical_history`
--
ALTER TABLE `patient_medical_history`
  ADD CONSTRAINT `fk_pmh_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_payment_received_by_foreign` FOREIGN KEY (`payment_received_by`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

--
-- Constraints for table `treatment_sessions`
--
ALTER TABLE `treatment_sessions`
  ADD CONSTRAINT `treatment_sessions_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `treatment_sessions_called_by_foreign` FOREIGN KEY (`called_by`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `treatment_sessions_dentist_id_foreign` FOREIGN KEY (`dentist_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
