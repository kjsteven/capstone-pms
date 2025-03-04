-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2025 at 11:59 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `capstone`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `staff_id`, `user_role`, `action`, `details`, `ip_address`, `timestamp`) VALUES
(1, 1, NULL, 'Admin', 'Archive Reservation', 'Archived reservation ID: 18', '::1', '2025-02-23 22:00:26'),
(3, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-23 22:07:07'),
(4, 11, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-02-23 22:10:01'),
(9, 11, NULL, 'user', 'Login', 'User logged in successfully', '::1', '2025-02-23 22:24:49'),
(17, 11, NULL, 'user', 'Logout', 'User logged out', '::1', '2025-02-23 22:44:22'),
(20, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-23 22:51:22'),
(21, 11, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-02-23 22:51:38'),
(22, NULL, 17, 'Staff', 'Staff Login', 'Staff member logged in successfully', '::1', '2025-02-23 22:54:18'),
(23, NULL, 17, 'Staff', 'Staff Login', 'Staff member logged in successfully', '::1', '2025-02-23 22:56:18'),
(28, 1, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-02-23 23:37:53'),
(29, NULL, 15, 'Staff', 'Staff Login', 'Staff member logged in successfully', '::1', '2025-02-24 13:22:03'),
(30, 1, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-24 13:27:27'),
(31, 1, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-02-24 13:27:57'),
(32, NULL, 15, 'Staff', 'Staff Login', 'Staff member logged in successfully', '::1', '2025-02-24 13:28:46'),
(33, NULL, 15, 'Staff', 'Staff Login', 'Staff member logged in successfully', '::1', '2025-02-24 13:35:54'),
(34, NULL, 15, 'Staff', 'Logout', 'Staff logged out', '::1', '2025-02-24 14:02:07'),
(35, NULL, 15, 'Staff', 'Staff Login', 'Staff member logged in successfully', '::1', '2025-02-24 14:06:24'),
(36, NULL, 15, 'Staff', 'Staff Logout', 'Staff logged out', '::1', '2025-02-24 14:06:42'),
(37, 1, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-24 14:10:13'),
(38, 1, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-24 16:11:52'),
(46, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-24 16:42:28'),
(47, 11, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-02-24 16:45:58'),
(48, 1, NULL, 'Admin', 'Archived Unit', 'Archived unit 805 (Warehouse)', '::1', '2025-02-24 17:13:03'),
(49, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 805 to Maintenance', '::1', '2025-02-24 17:14:14'),
(50, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 805 to Available', '::1', '2025-02-24 17:14:18'),
(51, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-02-25 13:37:25'),
(52, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 1004 (Office)', '::1', '2025-02-25 13:40:08'),
(53, 24, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-25 13:47:39'),
(54, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 23 updated to confirmed by Jhon Bautista (Admin)', '::1', '2025-02-25 13:48:43'),
(55, 1, NULL, 'Admin', 'Added New Unit to Tenant', 'Added unit 1004 for tenant Anora Hidson', '::1', '2025-02-25 13:49:53'),
(56, 1, NULL, 'Admin', 'Archived Tenant', 'Archived tenant David Fuentes from unit 807', '::1', '2025-02-25 13:56:22'),
(57, 1, NULL, 'Admin', 'Archived Tenant', 'Archived tenant David Fuentes from unit 807', '::1', '2025-02-25 14:01:02'),
(58, 1, NULL, 'Admin', 'Update Staff Information', 'Updated staff (ID: 15) information: Status: Inactive → Available', '::1', '2025-02-25 14:11:46'),
(59, 1, NULL, 'Admin', 'Update Staff Information', 'Updated staff (ID: 15) information: Phone: 09510975884 → 09510975881', '::1', '2025-02-25 14:12:13'),
(60, 1, NULL, 'Admin', 'Update Staff Status', 'Changed staff (ID: 15) status from Available to Suspended', '::1', '2025-02-25 14:12:38'),
(61, 1, NULL, 'Admin', 'Update Staff Information', 'Updated staff (ID: 15) information: Status: Suspended → Available', '::1', '2025-02-25 14:12:54'),
(62, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 23) role from User to Admin', '::1', '2025-02-25 14:19:39'),
(63, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 23) role from Admin to User', '::1', '2025-02-25 14:25:14'),
(64, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 23) role from User to Admin', '::1', '2025-02-25 14:30:07'),
(65, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 23) role from Admin to User', '::1', '2025-02-25 14:30:19'),
(66, 1, NULL, 'Admin', 'Update Staff Information', 'Updated staff (ID: 15) information: Status: Available → Busy', '::1', '2025-02-25 14:33:03'),
(67, 1, NULL, 'Admin', 'Update Staff Information', 'Updated staff (ID: 15) information: Status: Busy → Available', '::1', '2025-02-25 14:33:14'),
(68, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 23) role from User to Admin', '::1', '2025-02-25 14:35:52'),
(69, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 23) role from Admin to User', '::1', '2025-02-25 14:35:58'),
(80, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-02-25 18:50:57'),
(82, 1, NULL, 'Admin', 'Upload Contract', 'Contract uploaded for tenant ID: 43 - File: 67bda2809aaee_43_Rental_Agreement.pdf', '::1', '2025-02-25 18:59:12'),
(83, 1, NULL, 'Admin', 'Delete Contract', 'Contract deleted for tenant ID: 43', '::1', '2025-02-25 18:59:29'),
(84, 1, NULL, 'Admin', 'Update Maintenance Status', 'Updated maintenance request #95 status to: In Progress', '::1', '2025-02-25 19:12:02'),
(88, 1, NULL, 'Admin', 'Assign Maintenance Staff', 'Assigned maintenance request #96 to staff ID: 17 with priority: medium', '::1', '2025-02-25 19:13:39'),
(90, 1, NULL, 'Admin', 'Archive Maintenance Request', 'Archived maintenance request #97', '::1', '2025-02-25 19:15:43'),
(104, 1, NULL, 'Admin', 'Download Report', 'Downloaded maintenance report: maintenance_report_89_20250217_152954.pdf', '::1', '2025-02-25 19:33:19'),
(105, 1, NULL, 'Admin', 'Archive Reservation', 'Archived reservation ID: 18', '::1', '2025-02-25 19:36:31'),
(106, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 11) role from User to Admin', '::1', '2025-02-25 19:36:56'),
(107, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 11) role from Admin to User', '::1', '2025-02-25 19:37:08'),
(108, 1, NULL, 'Admin', 'Update Staff Information', 'Updated staff (ID: 15) information: Status: Busy → Available', '::1', '2025-02-25 19:37:18'),
(109, 1, NULL, 'Admin', 'Archived Tenant', 'Archived tenant David Fuentes from unit 807', '::1', '2025-02-25 19:37:34'),
(110, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-25 19:38:36'),
(111, 11, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-02-25 19:59:46'),
(112, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-25 20:00:01'),
(113, 11, NULL, 'User', 'Profile Image Update', 'Updated profile image: 67bdb549d211a-145218582_429580515129022_8609296071987312843_n.jpg', '::1', '2025-02-25 20:19:21'),
(114, 11, NULL, 'User', 'Password Change', 'Password changed successfully', '::1', '2025-02-25 20:20:05'),
(115, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #1002 - Viewing scheduled for 2025-03-05 at 11:30 (Reservation ID: 24)', '::1', '2025-02-25 20:26:27'),
(116, 11, NULL, 'User', 'Password Change', 'Password changed successfully', '::1', '2025-02-25 20:28:10'),
(128, 11, NULL, 'User', 'Cancel Reservation', 'Cancelled reservation #21', '::1', '2025-02-25 21:02:59'),
(131, 11, NULL, 'User', 'Archive Reservation', 'Archived reservation #22', '::1', '2025-02-25 21:21:16'),
(132, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-25 22:51:38'),
(133, 11, NULL, 'User', 'Submit Maintenance Request', 'Submitted maintenance request for unit 807, Issue: Leaking Faucet', '::1', '2025-02-25 23:10:40'),
(134, 11, NULL, 'User', 'Archive Maintenance Request', 'Archived maintenance request #89', '::1', '2025-02-25 23:11:08'),
(135, 1, NULL, 'Admin', 'Upload Contract', 'Contract uploaded for tenant ID: 43 - File: 67bddec5560f6_43_Rental_Agreement.pdf', '::1', '2025-02-25 23:16:22'),
(136, 1, NULL, 'Admin', 'Delete Contract', 'Contract deleted for tenant ID: 43', '::1', '2025-02-25 23:16:55'),
(137, 1, NULL, 'Admin', 'Upload Contract', 'Contract uploaded for tenant ID: 43 - File: 67bddf58896e2_43_Rental_Agreement.pdf', '::1', '2025-02-25 23:18:48'),
(138, 1, NULL, 'Admin', 'Delete Contract', 'Contract deleted for tenant ID: 43', '::1', '2025-02-25 23:18:53'),
(139, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 23) role from User to Admin', '::1', '2025-02-25 23:24:34'),
(140, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 23) role from Admin to User', '::1', '2025-02-25 23:24:38'),
(141, 1, NULL, 'Admin', 'Archived Tenant', 'Archived tenant David Fuentes from unit 807', '::1', '2025-02-25 23:24:45'),
(142, 1, NULL, 'Admin', 'Archived Unit', 'Archived unit 1004 (Office)', '::1', '2025-02-25 23:25:07'),
(143, 1, NULL, 'Admin', 'Archived Unit', 'Archived unit 1004 (Office)', '::1', '2025-02-25 23:25:12'),
(144, 1, NULL, 'Admin', 'Archived Unit', 'Archived unit 702 (Warehouse)', '::1', '2025-02-25 23:25:41'),
(145, 1, NULL, 'Admin', 'Archived Unit', 'Archived unit 702 (Warehouse)', '::1', '2025-02-25 23:28:02'),
(146, 1, NULL, 'Admin', 'Update Staff Information', 'Updated staff (ID: 15) information: Status: Available → Busy', '::1', '2025-02-25 23:30:23'),
(147, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 24 updated to confirmed by Jhon Bautista (Admin)', '::1', '2025-02-25 23:30:36'),
(148, 1, NULL, 'Admin', 'Archive Reservation', 'Archived reservation ID: 18', '::1', '2025-02-25 23:30:53'),
(149, 1, NULL, 'Admin', 'Download Report', 'Downloaded maintenance report: maintenance_report_97_20250218_132338.pdf', '::1', '2025-02-25 23:31:04'),
(150, 1, NULL, 'Admin', 'Assign Maintenance Staff', 'Assigned maintenance request #98 to staff ID: 15 with priority: medium', '::1', '2025-02-25 23:31:39'),
(151, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 702 to Maintenance', '::1', '2025-02-25 23:33:02'),
(152, 1, NULL, 'Admin', 'Archived Unit', 'Archived unit 702 (Warehouse)', '::1', '2025-02-25 23:33:10'),
(153, 1, NULL, 'Admin', 'Archived Unit', 'Archived unit 702 (Warehouse)', '::1', '2025-02-25 23:34:05'),
(154, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 805 to Available', '::1', '2025-02-25 23:35:09'),
(155, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 805 to Maintenance', '::1', '2025-02-25 23:35:12'),
(156, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 703 (Warehouse)', '::1', '2025-02-26 00:01:48'),
(157, 1, NULL, 'Admin', 'Archived Unit', 'Archived unit 703 (Warehouse)', '::1', '2025-02-26 00:02:01'),
(161, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 31 updated to confirmed by Jhon Bautista (Admin)', '::1', '2025-02-26 00:10:21'),
(162, 1, NULL, 'Admin', 'Added New Tenant', 'Added new tenant for Unit 101', '::1', '2025-02-26 00:11:00'),
(163, 1, NULL, 'Admin', 'Archived Tenant', 'Archived tenant Genalyn Palma from unit 101', '::1', '2025-02-26 00:11:15'),
(165, 26, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-26 00:19:14'),
(166, 1, NULL, 'Admin', 'Added New Unit to Tenant', 'Added unit 1002 for tenant David Fuentes', '::1', '2025-02-26 00:20:51'),
(167, 26, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-02-26 00:22:00'),
(168, 1, NULL, 'Admin', 'Assign Maintenance Staff', 'Assigned maintenance request #96 to staff ID: 15 with priority: high', '::1', '2025-02-26 00:22:32'),
(169, 1, NULL, 'Admin', 'Archive Maintenance Request', 'Archived maintenance request #89', '::1', '2025-02-26 00:22:46'),
(170, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-02-26 21:08:37'),
(178, NULL, 15, 'Staff', 'Password Change', 'Staff member changed their password', '::1', '2025-02-28 02:06:16'),
(179, NULL, 15, 'Staff', 'Password Change', 'Staff member changed their password', '::1', '2025-02-28 02:07:03'),
(180, NULL, 15, 'Staff', 'Staff  Logout', 'Staff member logged out successfully', '::1', '2025-02-28 02:10:05'),
(181, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-02-28 02:11:02'),
(182, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #101 - Viewing scheduled for 2025-03-05 at 10:40 (Reservation ID: 32)', '::1', '2025-02-28 02:17:03'),
(183, 11, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-02-28 02:19:01'),
(184, NULL, 15, 'Staff', 'Staff Login', 'Staff member logged in successfully', '::1', '2025-02-28 02:19:08'),
(185, NULL, 15, 'Staff', 'Staff  Logout', 'Staff member logged out successfully', '::1', '2025-02-28 02:23:10'),
(186, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-02-28 02:24:58'),
(187, 1, NULL, 'Admin', 'Logout', 'User logged out', '::1', '2025-02-28 02:45:32'),
(188, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-01 23:05:00'),
(189, 23, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-03-01 23:22:58'),
(190, 23, NULL, 'User', 'Unit Reservation', 'Reserved Unit #801 - Viewing scheduled for 2025-03-05 at 14:00 (Reservation ID: 33)', '::1', '2025-03-01 23:24:03'),
(191, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 33 updated to confirmed by Jhon Bautista (Admin)', '::1', '2025-03-01 23:24:24'),
(192, 1, NULL, 'Admin', 'Added New Unit to Tenant', 'Added unit 801 for tenant Conrad Palma with receipt', '::1', '2025-03-01 23:25:07'),
(193, 1, NULL, 'Admin', 'Update Staff Information', 'Updated staff (ID: 15) information: Status: Inactive → Available', '::1', '2025-03-01 23:27:26'),
(194, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-03 21:32:27'),
(195, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-04 00:12:33'),
(196, 1, NULL, 'Admin', 'Logout', 'User logged out', '::1', '2025-03-04 00:17:40'),
(197, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-03-04 00:17:53'),
(198, 11, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-03-04 00:23:27'),
(199, NULL, 15, 'Staff', 'Staff Login', 'Staff member logged in successfully', '::1', '2025-03-04 00:23:39'),
(200, NULL, 15, 'Staff', 'Staff  Logout', 'Staff member logged out successfully', '::1', '2025-03-04 00:33:30');

-- --------------------------------------------------------

--
-- Table structure for table `generated_reports`
--

CREATE TABLE `generated_reports` (
  `report_id` int(11) NOT NULL,
  `report_type` enum('Unit Occupancy Report','Property Availability Report','Property Maintenance Report','Monthly Payments Report','Rental Balance Report') NOT NULL,
  `report_date` date NOT NULL,
  `report_period` varchar(100) NOT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`report_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `downloaded_count` int(11) DEFAULT 0,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `generated_reports`
--
DELIMITER $$
CREATE TRIGGER `update_download_count` BEFORE UPDATE ON `generated_reports` FOR EACH ROW BEGIN
    IF NEW.downloaded_count != OLD.downloaded_count THEN
        SET NEW.downloaded_count = OLD.downloaded_count + 1;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `invoice_number` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('paid','unpaid') NOT NULL DEFAULT 'unpaid',
  `invoice_type` enum('rent','utility','other') NOT NULL DEFAULT 'rent',
  `description` text DEFAULT NULL,
  `email_sent` tinyint(1) DEFAULT 0,
  `email_sent_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `issue` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `action_taken` text DEFAULT NULL,
  `maintenance_cost` decimal(10,2) DEFAULT NULL,
  `completion_date` datetime DEFAULT NULL,
  `service_date` date NOT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `report_pdf` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assigned_to` int(11) DEFAULT NULL,
  `priority` enum('high','medium','low') NOT NULL DEFAULT 'medium',
  `archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_requests`
--

INSERT INTO `maintenance_requests` (`id`, `user_id`, `unit`, `issue`, `description`, `action_taken`, `maintenance_cost`, `completion_date`, `service_date`, `status`, `report_pdf`, `image`, `created_at`, `updated_at`, `assigned_to`, `priority`, `archived`) VALUES
(89, 11, '105', 'Electrical Problem', 'Outage', 'I have fix the cables', 500.00, '2025-02-17 22:29:00', '2024-12-13', 'Completed', 'maintenance_report_89_20250217_152954.pdf', 'uploads/maintenance_requests/maintenance_675b0f44cce1d9.66352520.jpg', '2024-12-12 16:28:52', '2025-02-25 16:22:57', 15, 'medium', 0),
(94, 23, '205', 'Electrical Problem', 'Outage', NULL, NULL, NULL, '2024-12-14', 'In Progress', NULL, 'uploads/maintenance_requests/maintenance_675be5b19c7b59.82571621.jpg', '2024-12-13 07:43:45', '2025-02-17 04:21:58', 17, 'medium', 0),
(95, 11, '102', 'Electrical Problem', 'Overheating', NULL, NULL, NULL, '2025-01-09', 'In Progress', NULL, 'uploads/maintenance_requests/maintenance_6778c8547a2057.59459477.jpg', '2025-01-04 05:34:12', '2025-02-25 11:12:02', 17, 'high', 0),
(96, 11, '105', 'Heating Issue', 'AC is broken', 'After receiving a distress call from a customer reporting their broken air conditioning unit, our team quickly sprang into action. We promptly scheduled a visit to assess the situation and arrived at the location equipped with the necessary tools and replacement parts. Upon inspection, we identified a malfunctioning compressor as the root cause of the issue. Our skilled technician efficiently replaced the faulty component and performed a thorough system check to ensure optimal functionality. We tested the unit multiple times to verify that it was cooling effectively and met the customer’s expectations. Satisfied with our prompt and professional service, the customer expressed their gratitude for restoring comfort to their home just in time for the hot weather.', 1000.00, '2025-02-28 13:41:00', '2025-01-15', 'Pending', 'maintenance_report_96_20250227_182804.pdf', 'uploads/maintenance_requests/maintenance_6778c8f5505524.10953351.png', '2025-01-04 05:36:53', '2025-02-27 17:28:04', 15, 'high', 0),
(97, 11, '701', 'Leaking Faucet', 'Broken Faucet', 'Change Faucet', 500.00, '2025-03-18 20:23:00', '2025-02-28', 'Completed', 'maintenance_report_97_20250218_132338.pdf', 'uploads/maintenance_requests/maintenance_67b47b30da2a95.63959313.jpg', '2025-02-18 12:21:04', '2025-02-25 15:06:21', 15, 'medium', 0),
(98, 11, '807', 'Leaking Faucet', 'The faucet is leaking a water', NULL, NULL, NULL, '2025-02-28', 'Pending', NULL, 'uploads/maintenance_requests/maintenance_67bddd702336d1.04366914.jpg', '2025-02-25 15:10:40', '2025-02-25 15:31:39', 15, 'medium', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'General',
  `status` enum('Unread','Read') DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `unit_id` int(11) NOT NULL,
  `unit_no` varchar(50) NOT NULL,
  `unit_type` varchar(50) NOT NULL,
  `square_meter` decimal(10,2) NOT NULL,
  `monthly_rent` decimal(10,2) NOT NULL,
  `images` text DEFAULT NULL,
  `status` enum('Available','Occupied','Maintenance','Reserved') DEFAULT 'Available',
  `position` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property`
--

INSERT INTO `property` (`unit_id`, `unit_no`, `unit_type`, `square_meter`, `monthly_rent`, `images`, `status`, `position`) VALUES
(1, '101', 'Warehouse', 100.00, 45000.00, 'uploads/67447b19203d5_architecture-5339245_1280.jpg', 'Available', 'active'),
(3, '105', 'Office', 75.00, 33750.00, 'uploads/6744916776e67_bricks-2181920_1280.jpg', 'Occupied', 'active'),
(4, '102', 'Commercial', 50.00, 22500.00, 'uploads/674492f88e939_kitchen-8297678_1280.jpg', 'Occupied', 'active'),
(5, '106', 'Warehouse', 100.00, 45000.00, 'uploads/6744bfef4a3ce_architecture-5339245_1280.jpg', 'Occupied', 'active'),
(6, '107', 'Commercial', 150.00, 67500.00, 'uploads/6746d96160177_architecture-3383067_1280.jpg', 'Occupied', 'active'),
(7, '115', 'Warehouse', 110.00, 49500.00, 'uploads/674ee720cb5b1_warehouse-1026496_1280.jpg', 'Available', 'active'),
(8, '116', 'Office', 80.00, 36000.00, 'uploads/674ee99e0119e_classroom-4919804_1280.jpg', 'Available', 'active'),
(9, '201', 'Commercial', 50.00, 22500.00, 'uploads/6757aa7d0e2eb_kitchen-8714865_1280.jpg', 'Available', 'active'),
(10, '205', 'Office', 50.00, 22500.00, 'uploads/6757c2c52a4a1_kitchen-1336160_1280.jpg', 'Available', 'active'),
(11, '206', 'Warehouse', 150.00, 67500.00, 'uploads/67ab43a74ce60_ShockWatch-Warehouse-Efficiency.jpg', 'Available', 'active'),
(12, '207', 'Office', 200.00, 90000.00, 'uploads/67ab43e7572a9_pexels-seven11nash-380769.jpg', 'Available', 'active'),
(13, '208', 'Office', 180.00, 81000.00, 'uploads/67ab442d24775_pexels-pixabay-221537.jpg', 'Available', 'active'),
(14, '301', 'Commercial', 250.00, 112500.00, 'uploads/67b2ba7310b27_building-7005414_1280.jpg', 'Available', 'active'),
(15, '401', 'Warehouse', 160.00, 72000.00, 'uploads/67b2baaa4e1f6_lift-5201486_1280.jpg', 'Available', 'active'),
(16, 'G1', 'Commercial', 175.00, 78750.00, 'uploads/67b2bb6764f36_hall-621741_1280.jpg', 'Available', 'active'),
(17, '501', 'Office', 200.00, 90000.00, 'uploads/67b2bbc389c3f_bedroom-8275330_1280.jpg', 'Available', 'active'),
(18, '605', 'Office', 250.00, 112500.00, 'uploads/67b2bc10d4e5f_apartment-1851201_1280.jpg', 'Available', 'active'),
(19, '702', 'Warehouse', 200.00, 90000.00, 'uploads/67b2bc4d0a251_apartment-406901_1280.jpg', 'Maintenance', 'active'),
(20, '807', 'Warehouse', 200.00, 90000.00, 'uploads/67b2bc75b1f59_high-level-rack-408222_1280.jpg', 'Available', 'active'),
(21, '902', 'Office', 100.00, 45000.00, 'uploads/67b2bca4e0c7e_workplace-5517744_1280.jpg', 'Available', 'active'),
(22, '1006', 'Office', 200.00, 90000.00, 'uploads/67b2bcd813f98_office-2360063_1280.jpg', 'Available', 'active'),
(23, '701', 'Warehouse', 200.00, 90000.00, 'uploads/67b479e46a5ca_office-2360063_1280.jpg', 'Occupied', 'active'),
(24, '1001', 'Warehouse', 100.00, 45000.00, 'uploads/67bc2bab0425f_pexels-nc-farm-bureau-mark-27793715.jpg', 'Maintenance', 'active'),
(25, '1002', 'Commercial', 200.00, 90000.00, 'uploads/67bc2d93a5104_pexels-reneterp-20943.jpg', 'Occupied', 'active'),
(26, '1003', 'Commercial', 85.00, 38250.00, 'uploads/67bc2dda1f6ef_pexels-heyho-8089087.jpg', 'Available', 'active'),
(27, '801', 'Warehouse', 175.00, 78750.00, 'uploads/67bc2f1d62c12_pexels-david-slaager-3875806-5759147.jpg', 'Occupied', 'active'),
(28, '805', 'Warehouse', 200.00, 90000.00, 'uploads/67bc2f4d6d5ef_pexels-golimpio-8171863.jpg', 'Maintenance', 'active'),
(29, '1004', 'Office', 200.00, 90000.00, 'uploads/67bd57b893de4_pexels-kamo11235-667838.jpg', 'Occupied', 'archive'),
(30, '703', 'Warehouse', 150.00, 67500.00, 'uploads/67bde96c3927a_pexels-spacex-60130.jpg', 'Available', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `viewing_date` date NOT NULL,
  `viewing_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
  `archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `unit_id`, `viewing_date`, `viewing_time`, `created_at`, `status`, `archived`) VALUES
(18, 24, 6, '2024-12-27', '20:38:00', '2024-12-27 12:38:03', 'Completed', 1),
(19, 11, 4, '2025-01-01', '10:30:00', '2024-12-31 08:27:21', 'Completed', 0),
(20, 23, 5, '2025-01-06', '10:40:00', '2025-01-03 11:35:03', 'Completed', 0),
(21, 11, 23, '2025-02-19', '10:20:00', '2025-02-18 12:17:03', 'Completed', 0),
(22, 11, 20, '2025-02-25', '11:30:00', '2025-02-23 14:25:42', 'Completed', 1),
(23, 24, 29, '2025-02-28', '10:50:00', '2025-02-25 05:48:25', 'Completed', 0),
(24, 11, 25, '2025-03-05', '11:30:00', '2025-02-25 12:26:27', 'Completed', 0),
(32, 11, 1, '2025-03-05', '10:40:00', '2025-02-27 18:17:03', 'Pending', 0),
(33, 23, 27, '2025-03-05', '14:00:00', '2025-03-01 15:24:03', 'Completed', 0);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Specialty` varchar(100) NOT NULL,
  `Phone_Number` varchar(20) NOT NULL,
  `ResetToken` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token_expires` datetime DEFAULT NULL,
  `status` enum('Available','Busy','Active','Suspended','Inactive') NOT NULL DEFAULT 'Available',
  `OTP` varchar(6) DEFAULT NULL,
  `OTP_expiration` datetime DEFAULT NULL,
  `OTP_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `Email`, `Password`, `Name`, `Specialty`, `Phone_Number`, `ResetToken`, `created_at`, `reset_token_expires`, `status`, `OTP`, `OTP_expiration`, `OTP_used`) VALUES
(15, 'freshplayz18@gmail.com', '$2y$10$Zu8rBaxirZhlAmFE23kOtOXgd6EcD5GIV1dOx66x8o2AEe7blgP5K', 'CONRAD KANE', 'Hvac Technician', '09510975881', NULL, '2024-11-25 18:23:43', NULL, 'Inactive', '211931', '2025-03-03 17:33:39', 0),
(17, 'kjstevenpalma09@gmail.com', '$2y$10$9GpFnvY4vqvXcgUs1RrKDu3Sfyos0t1w3OhcV72vBvWQ85qVxlaHy', 'KJ STEVEN PALMA', 'General Maintenance', '09510975885', 'ac5809a81cd8c5da3043d58b235e95fcb80881216b0ade9d58d198a6adc096b7', '2024-12-10 04:30:55', NULL, 'Busy', '771030', '2025-02-23 16:06:40', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `tenant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `unit_rented` varchar(255) NOT NULL,
  `rent_from` date NOT NULL,
  `rent_until` date NOT NULL,
  `monthly_rate` decimal(10,2) NOT NULL,
  `outstanding_balance` decimal(10,2) NOT NULL,
  `registration_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `downpayment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payable_months` int(11) DEFAULT 0,
  `status` enum('active','archived') DEFAULT 'active',
  `contract_file` varchar(255) DEFAULT NULL,
  `contract_upload_date` datetime DEFAULT NULL,
  `downpayment_receipt` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`tenant_id`, `user_id`, `unit_rented`, `rent_from`, `rent_until`, `monthly_rate`, `outstanding_balance`, `registration_date`, `created_at`, `updated_at`, `downpayment_amount`, `payable_months`, `status`, `contract_file`, `contract_upload_date`, `downpayment_receipt`) VALUES
(20, 11, '3', '2024-12-29', '2026-05-20', 33750.00, 440000.00, '0000-00-00', '2024-12-09 13:52:32', '2025-02-16 12:37:46', 100000.00, 14, 'active', 'uploads/contracts/67b1dc1a23042_20_Rental_Agreement.pdf', '2025-02-16 20:37:46', NULL),
(21, 23, '10', '2024-12-10', '2026-12-20', 22500.00, 440000.00, '0000-00-00', '2024-12-10 04:27:46', '2025-02-23 14:04:34', 100000.00, 20, 'active', NULL, NULL, NULL),
(42, 24, '6', '2024-12-31', '2026-10-31', 67500.00, 1385000.00, '0000-00-00', '2024-12-31 08:43:13', '2024-12-31 08:43:13', 100000.00, 21, 'active', NULL, NULL, NULL),
(43, 11, '4', '2025-01-03', '2027-11-24', 22500.00, 665000.00, '0000-00-00', '2025-01-03 15:08:01', '2025-02-25 15:18:53', 100000.00, 30, 'active', NULL, NULL, NULL),
(44, 23, '5', '2025-01-23', '2027-10-03', 45000.00, 940000.00, '0000-00-00', '2025-01-03 15:12:16', '2025-01-03 15:12:16', 500000.00, 21, 'active', NULL, NULL, NULL),
(45, 11, '23', '2025-02-18', '2027-09-23', 90000.00, 2290000.00, '0000-00-00', '2025-02-18 12:20:19', '2025-02-18 12:24:28', 500000.00, 26, 'active', 'uploads/contracts/67b47bfcb7e70_45_Rental_Agreement.pdf', '2025-02-18 20:24:28', NULL),
(47, 11, '20', '2025-02-28', '2028-10-26', 90000.00, 3370000.00, '0000-00-00', '2025-02-23 14:37:52', '2025-02-25 15:24:56', 500000.00, 38, 'active', NULL, NULL, NULL),
(48, 24, '29', '2025-03-06', '2027-09-30', 90000.00, 2200000.00, '0000-00-00', '2025-02-25 05:49:53', '2025-02-25 05:49:53', 500000.00, 25, 'active', NULL, NULL, NULL),
(50, 11, '25', '2025-04-30', '2026-12-30', 90000.00, 1400000.00, '0000-00-00', '2025-02-25 16:20:51', '2025-02-25 16:20:51', 400000.00, 16, 'active', NULL, NULL, NULL),
(51, 23, '27', '2025-03-10', '2027-05-30', 78750.00, 1547500.00, '0000-00-00', '2025-03-01 15:25:07', '2025-03-01 15:25:07', 500000.00, 20, 'active', NULL, NULL, '../uploads/receipts/receipt_1740842707_6543.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('Admin','User') NOT NULL,
  `OTP` varchar(6) DEFAULT NULL,
  `OTP_used` tinyint(1) DEFAULT 0,
  `OTP_expiration` datetime DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone` varchar(15) DEFAULT NULL,
  `ResetToken` varchar(64) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `profile_image`, `role`, `OTP`, `OTP_used`, `OTP_expiration`, `token`, `is_verified`, `created_at`, `updated_at`, `phone`, `ResetToken`, `login_attempts`, `last_attempt`, `status`) VALUES
(1, 'Jhon Bautista', 'kjstevenpalma@gmail.com', '$2y$10$IbNu.ni4K.fxIYaIbyFqnekQgBR9RykaYNiTAbmUPa9obcBHlDxWG', NULL, 'Admin', '662690', 0, '2025-03-03 17:22:29', NULL, 1, '2024-10-22 08:59:15', '2025-03-03 16:17:40', '09510973323', '25974df077c253efd20cab82d9270e27fe1fd5238d4fea9ac2794aec23671076', 0, '2025-03-03 16:12:29', 'inactive'),
(11, 'David Fuentes', 'kjstevengaming@gmail.com', '$2y$10$vq8FbgmhOjIfSu5BBi5UA.Sk4wynt6P.s2FAbGG9DsuCmH3U6q0cq', 'uploads/67bdb549d211a-145218582_429580515129022_8609296071987312843_n.jpg', 'User', '224469', 0, '2025-03-03 17:27:49', NULL, 1, '2024-10-22 09:34:53', '2025-03-03 16:23:27', '09510973444', NULL, 0, '2025-03-03 16:17:49', 'inactive'),
(23, 'Conrad Palma', 'kjstevenpalma18@gmail.com', '$2y$10$ePCz3ES5ycuXMIvghHlbS.Tp7vI8DHmRz8VbXRiSwGmb9S5yz3p/a', NULL, 'User', '746820', 0, '2025-03-01 16:32:55', NULL, 1, '2024-12-10 04:21:37', '2025-03-01 15:22:55', '09616733509', NULL, 0, '2025-03-01 15:22:55', 'active'),
(24, 'Anora Hidson', 'freshplayz18@gmail.com', '$2y$10$mKVkrd7ZtB4yXrdmUPN.DO7.msB12SEcdbM1tp7MsZQtko5WOQctK', NULL, 'User', '160895', 0, '2025-02-25 06:57:36', NULL, 1, '2024-12-25 07:47:13', '2025-02-25 05:47:36', '09212973327', NULL, 0, '2025-02-25 05:47:36', 'active'),
(26, 'Genalyn Palma', 'palmagenalyn17@gmail.com', '$2y$10$5w8ADIL9AijXhs.JVBw1X.olYGpZT8HL7W6TIe4lad3IJSgNtX6Ve', NULL, 'User', '461341', 0, '2025-02-25 17:29:11', NULL, 1, '2025-02-25 16:18:33', '2025-02-25 16:22:00', '09516733408', NULL, 0, '2025-02-25 16:19:11', 'inactive');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `generated_reports`
--
ALTER TABLE `generated_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_report_date` (`report_date`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_assigned_staff` (`assigned_to`),
  ADD KEY `idx_maintenance_priority` (`priority`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`unit_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_unit_id` (`unit_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_reset_token` (`ResetToken`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`tenant_id`),
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
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT for table `generated_reports`
--
ALTER TABLE `generated_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `tenant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `fk_assigned_staff` FOREIGN KEY (`assigned_to`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_unit_id` FOREIGN KEY (`unit_id`) REFERENCES `property` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `property` (`unit_id`) ON DELETE CASCADE;

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
