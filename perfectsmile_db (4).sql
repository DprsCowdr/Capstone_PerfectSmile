-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2025 at 02:02 PM
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

INSERT INTO `appointments` (`id`, `branch_id`, `dentist_id`, `user_id`, `appointment_datetime`, `status`, `appointment_type`, `approval_status`, `decline_reason`, `remarks`, `created_at`, `updated_at`, `procedure_duration`, `time_taken`, `pending_change`) VALUES
(141, 2, 26, 35, '2025-08-31 08:00:00', 'completed', 'scheduled', 'approved', NULL, 'please prio me. i will arrive early', '2025-08-31 04:05:07', '2025-08-31 14:43:56', 30, NULL, 0),
(142, 2, 26, 3, '2025-08-31 16:41:00', 'pending_approval', 'scheduled', 'pending', NULL, '', '2025-08-31 07:41:12', '2025-08-31 07:41:12', NULL, NULL, 0),
(143, 2, 26, 15, '2025-08-31 08:30:00', 'no_show', 'scheduled', 'approved', NULL, 'first try ', '2025-08-31 09:22:19', '2025-08-31 14:43:56', NULL, NULL, 0),
(146, 2, 26, 31, '2025-08-31 09:00:00', 'no_show', 'scheduled', 'approved', NULL, 'testing selected time', '2025-08-31 10:28:26', '2025-08-31 14:43:56', NULL, NULL, 0),
(147, 2, 26, 5, '2025-08-31 09:30:00', 'no_show', 'scheduled', 'approved', NULL, 'testing no2', '2025-08-31 10:37:16', '2025-08-31 14:43:56', NULL, NULL, 0),
(148, 2, 26, 15, '2025-08-31 10:30:00', 'no_show', 'scheduled', 'approved', NULL, 'test', '2025-08-31 11:03:47', '2025-08-31 14:43:56', NULL, NULL, 0),
(149, 2, 26, 22, '2025-08-31 12:00:00', 'no_show', 'scheduled', 'approved', NULL, 'it should +30', '2025-08-31 11:26:33', '2025-08-31 14:43:56', NULL, NULL, 0),
(150, 2, 26, 5, '2025-08-31 13:00:00', 'no_show', 'scheduled', 'approved', NULL, 'it should add +30 ', '2025-08-31 12:32:27', '2025-08-31 14:43:56', 45, NULL, 0),
(151, 2, 26, 22, '2025-08-31 13:46:00', 'no_show', 'scheduled', 'approved', NULL, 'test if it add the manual time', '2025-08-31 12:37:48', '2025-08-31 14:43:56', 60, NULL, 0),
(152, 2, 26, 32, '2025-08-31 14:04:00', 'no_show', 'walkin', 'auto_approved', NULL, 'hindi gumagana pag may butal ', '2025-08-31 12:49:51', '2025-08-31 14:43:56', 45, NULL, 0),
(153, 2, 26, 30, '2025-08-31 14:50:00', 'confirmed', 'walkin', 'auto_approved', NULL, 'try kung ma occupy the remaining time', '2025-08-31 12:52:12', '2025-08-31 12:52:12', 60, NULL, 0),
(155, 2, 26, 22, '2025-08-31 15:51:00', 'confirmed', 'scheduled', 'approved', NULL, 'try kong gumana ', '2025-08-31 14:25:27', '2025-08-31 14:25:51', 60, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `appointment_service`
--

CREATE TABLE `appointment_service` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `appointment_service`
--

INSERT INTO `appointment_service` (`id`, `service_id`, `appointment_id`) VALUES
(12, 1, 141);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `contact_number`) VALUES
(1, 'Nabua Branch', 'Nabua,Camarines Sur', '+1 (555) 123-4567'),
(2, 'Iriga Branch', 'Iriga City,Camarines Sur', '+1 (555) 234-5678');

-- --------------------------------------------------------

--
-- Table structure for table `branch_notifications`
--

CREATE TABLE `branch_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED NOT NULL,
  `appointment_id` int(10) UNSIGNED DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_notifications`
--

INSERT INTO `branch_notifications` (`id`, `branch_id`, `appointment_id`, `payload`, `sent`, `sent_at`, `created_at`, `updated_at`) VALUES
(18, 2, 141, '{\"patient_name\":\"John Bert Manaog\",\"patient_email\":\"manaogjohnbert@gmail.com\",\"patient_phone\":\"09948804318\",\"appointment_date\":\"2025-08-31\",\"appointment_time\":\"08:00\",\"branch_id\":\"2\",\"procedure_duration\":\"30\",\"dentist_id\":26,\"remarks\":\"please prio me. i will arrive early\",\"user_id\":\"35\",\"status\":\"pending\",\"approval_status\":\"pending\"}', 0, NULL, '2025-08-31 04:05:07', '2025-08-31 04:05:07');

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
(9, 24, 2, 'receptionist'),
(13, 31, 2, 'Patient'),
(14, 32, 2, 'Patient'),
(15, 33, 2, 'receptionnist');

-- --------------------------------------------------------

--
-- Table structure for table `dental_chart`
--

CREATE TABLE `dental_chart` (
  `id` int(11) UNSIGNED NOT NULL,
  `dental_record_id` int(11) NOT NULL,
  `tooth_number` int(2) NOT NULL COMMENT 'Tooth number (1-32 for permanent teeth, 51-85 for primary teeth)',
  `tooth_type` enum('permanent','primary') NOT NULL DEFAULT 'permanent',
  `condition` varchar(255) DEFAULT NULL COMMENT 'Dental condition (cavity, filling, crown, etc.)',
  `status` enum('healthy','needs_treatment','treated','missing','none') NOT NULL DEFAULT 'healthy',
  `notes` text DEFAULT NULL COMMENT 'Additional notes about the tooth',
  `recommended_service_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Recommended service for treatment',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dental_record`
--

CREATE TABLE `dental_record` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `record_date` date DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `xray_image_url` varchar(255) DEFAULT NULL,
  `next_appointment_date` date DEFAULT NULL,
  `next_appointment_id` int(11) DEFAULT NULL,
  `dentist_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
(20, '2025-01-15-000001', 'App\\Database\\Migrations\\CreateInvoicesTable', 'default', 'App', 1756461403, 15);

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
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_checkins`
--

INSERT INTO `patient_checkins` (`id`, `appointment_id`, `checked_in_at`, `checked_in_by`, `self_checkin`, `checkin_method`, `notes`, `created_at`, `updated_at`) VALUES
(33, 141, '2025-08-31 04:05:50', 33, 0, 'staff', 'Patient checked in via admin interface', '2025-08-31 04:05:50', '2025-08-31 04:05:50');

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
(9, 1, 'Dr. Gonowon Minnie Arroyo', '1234', '12345', 3, NULL, '2025-09-05', '2025-09-11', 'draft', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', NULL, '2025-09-05 06:21:12', '2025-09-05 07:36:39'),
(10, 1, 'Dr. Gonowon Minnie Arroyo', '01122', '554', 22, NULL, '2025-09-05', '2025-09-12', 'draft', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', NULL, '2025-09-05 11:59:50', '2025-09-05 12:14:55');

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
(13, 9, 'Amoxicillin', '500 mg', '3 times daily', '7 days', 'Take after meals with water.'),
(15, 10, 'Amoxicillin', '100 kg', '3 times daily', 'everyday', 'wag inumin');

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
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`) VALUES
(1, 'Dental Checkup', 'Comprehensive dental examination and cleaning', 75.00),
(2, 'Teeth Whitening', 'Professional teeth whitening treatment', 150.00),
(3, 'Cavity Filling', 'Dental filling for cavities', 120.00),
(4, 'Root Canal', 'Root canal treatment', 800.00),
(5, 'Dental Crown', 'Dental crown placement', 600.00),
(6, 'Tooth Extraction', 'Simple tooth extraction', 200.00);

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
(21, 141, '2025-08-31 04:05:59', NULL, 33, 26, 'in_progress', NULL, 'normal', NULL, NULL, '2025-08-31 04:05:59', '2025-08-31 04:05:59');

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
(3, 'patient', 'Patient Jane', '789 Patient Road', 'patient@perfectsmile.com', 'Female', '$2y$12$IT16i/hQXaqPB4bgvBy23.isY3vix20H5snsASckANyzP3HcwpJ0e', '1234567892', '2025-06-28 16:02:14', '2025-09-05 07:36:39', 'hh', 'oo', '2025-07-08', 9, 'active'),
(5, 'patient', 'Brandon Brandon Brandon', 'san jose baybayon sugong', 'Brandon@gmail.com', 'Male', '$2y$10$Yxaynjci.9n395MxVkRpBeOGRRAaB3aWT5QCwSS9hCbCPuhvEYFj.', '89078007077', '2025-07-08 13:39:16', '2025-09-05 05:27:33', 'Brandon', 'Brandon@gmail.com', '2025-07-03', 18, 'active'),
(10, 'admin', 'Brandon Dentist', NULL, 'don@gmail.com', 'male', '$2y$12$9BzUNUBkE5idKzb1qdz78ePPo8HsVRgCMm9ZJnjaZDIvRIqcnYW8S', '09150540702', '2025-08-07 05:30:50', '2025-08-07 07:25:15', 'na', 'na', NULL, NULL, 'active'),
(15, 'patient', 'Eden Caritos', 'PIli Camsur', 'eden@gmail.com', 'Female', '$2y$12$UCxvaWFzAwxJSjK2LBqTGuRXUJaDYbc7rtU9culVbP2SqV/pV/DeS', '099150540702', '2025-08-08 11:06:41', '2025-08-22 20:00:49', 'Nurse', 'Filipino', '2001-09-11', 23, 'active'),
(16, 'dentist', 'Nabua Dentist', NULL, 'nabua@perfectsmile.com', 'Male', '$2y$12$saFau/p7z3Tu6x/vMg6Q4OkN8o34aUQTMj3Do.zwIVU0l/ii1QVw2', '09150540702', '2025-08-09 03:14:00', '2025-08-09 03:14:00', 'na', 'na', '2000-09-23', 24, 'active'),
(18, 'dentist', 'Iriga Dentist', NULL, 'iriga@gmail.com', '', '$2y$12$1X4TeOBfzyXYR8rjHrLU4.ifGgz3mJS.KtW4Hr9h5mI3s.wODqGfy', '3903983098809', '2025-08-09 11:43:06', '2025-08-09 11:43:06', 'nnn', 'nnn', '2000-08-23', 24, 'active'),
(20, 'staff', 'Brandon Dentist', NULL, 'adminn@perfectsmile.com', 'Male', '$2y$12$nw2ocoPA/fAScG/vvSSO..H0AmbVOV.K6WY2YULYbX9hMFuUCekkW', '09150540702', '2025-08-13 04:12:57', '2025-08-13 04:12:57', 'nnnn', 'nnnnn', '2007-05-11', 18, 'active'),
(22, 'patient', 'Testing 001 ', 'san jose baybayon sugong', 'testing@gmail.com', 'Male', NULL, '123456789', '2025-08-16 13:07:00', '2025-09-05 12:14:55', 'testing', 'testing', '2003-08-08', 22, 'active'),
(24, 'staff', 'diana ', NULL, 'd@gmail.com', 'Female', '$2y$12$WrH1I/NedwIb4JwJ3TE2Ou5hT0DHF1nJjqg5TlKUWzPSJHZWtb09S', '09150540702', '2025-08-22 17:40:48', '2025-08-22 17:40:48', 'd@gmail.com', 'd@gmail.com', '1992-01-08', 33, 'active'),
(26, 'dentist', 'Dr. Minnie Gonowon', NULL, 'perfectsmile-nabua@gmail.com', 'Female', '$2y$10$tBgRKEL3PjZoUCY2aJAtYezBfbAdLG67fZoKkE/PDqJjXx7dkK.T.', '09948804320', '2025-08-23 05:44:10', '2025-08-23 05:44:10', 'Doctor', 'filipino', '1987-03-22', 38, 'active'),
(30, 'patient', 'testuser', 'san jose baybayon sugong', 'testuser@gmail.com', 'Male', NULL, '09948804318', '2025-08-25 06:27:13', '2025-08-25 06:27:13', 'student', 'filipino', '2003-08-14', NULL, 'active'),
(31, 'patient', 'testuser-1', 'san jose baybayon sugong', 'testuser-1@gmail.com', 'Male', NULL, '09948804318', '2025-08-25 06:44:48', '2025-08-25 06:44:48', 'student', 'filipino', '2003-08-21', NULL, 'active'),
(32, 'patient', 'testuser-2', 'san jose baybayon sugong', 'testuser-2@gmail.com', 'Male', NULL, '09948804318', '2025-08-25 07:01:52', '2025-08-25 07:01:52', 'student', 'filipino', '2002-08-15', NULL, 'active'),
(33, 'staff', 'Staff-IrigaBranch', NULL, 'perfectsmile-staff/IrigaBranch@gmail.com', 'Female', '$2y$10$GVD9mPTnhgE.BgzUnBYcsOcHLA7tIr0evG3Dhts6e/BmptrwVgLCK', '09948804318', '2025-08-25 07:07:36', '2025-08-25 07:07:36', 'Doctor', 'filipino', '2000-03-31', 25, 'active'),
(35, 'patient', 'John Bert Manaog', 'san jose baybayon sugong', 'manaogjohnbert@gmail.com', 'Male', '$2y$10$YRI6d2VjBYBXfwIIrvrCuuBdafoHRKZAZDmSjA3lH.qVpCflNQ.NG', '09948804318', '2025-08-28 10:15:01', '2025-08-28 10:15:28', 'student', 'filipino', '2003-07-04', NULL, 'active');

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
-- Indexes for table `branch_notifications`
--
ALTER TABLE `branch_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `appointment_id` (`appointment_id`);

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
  ADD KEY `fk_dental_record_appointment` (`appointment_id`),
  ADD KEY `fk_dental_record_next_appointment` (`next_appointment_id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT for table `appointment_service`
--
ALTER TABLE `appointment_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
-- AUTO_INCREMENT for table `branch_notifications`
--
ALTER TABLE `branch_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `branch_user`
--
ALTER TABLE `branch_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `dental_chart`
--
ALTER TABLE `dental_chart`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT for table `dental_record`
--
ALTER TABLE `dental_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `patient_checkins`
--
ALTER TABLE `patient_checkins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `procedures`
--
ALTER TABLE `procedures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `treatment_sessions`
--
ALTER TABLE `treatment_sessions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

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
-- Constraints for table `dental_chart`
--
ALTER TABLE `dental_chart`
  ADD CONSTRAINT `fk_dental_chart_record` FOREIGN KEY (`dental_record_id`) REFERENCES `dental_record` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`procedure_id`) REFERENCES `procedures` (`id`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
