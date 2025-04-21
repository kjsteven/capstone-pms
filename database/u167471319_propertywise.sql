-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 03:33 PM
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
-- Database: `u167471319_propertywise`
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
(200, NULL, 15, 'Staff', 'Staff  Logout', 'Staff member logged out successfully', '::1', '2025-03-04 00:33:30'),
(201, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-04 19:00:38'),
(202, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250304-1713 for tenant #42', '::1', '2025-03-04 19:11:13'),
(203, 1, NULL, 'Admin', 'Sent Invoice Email', 'Sent invoice #INV-20250304-1713 to Anora Hidson (freshplayz18@gmail.com)', '::1', '2025-03-04 19:11:38'),
(204, 1, NULL, 'Admin', 'Sent Invoice Email', 'Sent invoice #INV-20250304-1713 to Anora Hidson (freshplayz18@gmail.com)', '::1', '2025-03-04 19:22:27'),
(205, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250304-3030 for tenant #42', '::1', '2025-03-04 19:23:21'),
(206, 1, NULL, 'Admin', 'Sent Invoice Email', 'Sent invoice #INV-20250304-3030 to Anora Hidson (freshplayz18@gmail.com)', '::1', '2025-03-04 19:24:09'),
(207, 1, NULL, 'Admin', 'Logout', 'User logged out', '::1', '2025-03-04 19:32:06'),
(208, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-03-04 19:32:20'),
(209, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-04 19:40:20'),
(210, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250304-9270 for tenant #44', '::1', '2025-03-04 19:42:14'),
(211, 1, NULL, 'Admin', 'Sent Invoice Email', 'Sent invoice #INV-20250304-9270 to Conrad Palma (kjstevenpalma18@gmail.com)', '::1', '2025-03-04 19:42:51'),
(212, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-03-04 21:53:06'),
(213, 11, NULL, 'User', 'Payment Submission', 'Payment of PHP 46,000.00 submitted for review (Reference: 2012120513868)', '::1', '2025-03-04 22:01:47'),
(214, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-05 17:32:20'),
(215, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-05 21:38:44'),
(216, 1, NULL, 'Admin', 'Logout', 'User logged out', '::1', '2025-03-05 22:29:14'),
(217, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-05 22:29:28'),
(218, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱46,000.00 for David Fuentes (Unit 701)', '::1', '2025-03-05 23:08:19'),
(219, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash payment of ₱45,000.00 for Conrad Palma (Unit 106)', '::1', '2025-03-05 23:18:27'),
(220, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded GCash payment of ₱78,750.00 for Conrad Palma (Unit 801)', '::1', '2025-03-05 23:41:32'),
(221, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash payment of ₱90,000.00 for David Fuentes (Unit 807)', '::1', '2025-03-05 23:59:45'),
(222, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250304-1713 status to paid', '::1', '2025-03-06 00:15:27'),
(223, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250304-3030 status to paid', '::1', '2025-03-06 00:15:41'),
(224, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250304-9270 status to paid', '::1', '2025-03-06 00:15:42'),
(225, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash payment of ₱1,000.00 for Maintenance Charges - Anora Hidson (Unit 107)', '::1', '2025-03-06 00:27:48'),
(226, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded GCash payment of ₱5,000.00 for Utilities - Anora Hidson (Unit 107)', '::1', '2025-03-06 01:12:47'),
(227, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250304-9270 status to unpaid', '::1', '2025-03-06 01:18:44'),
(228, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250304-9270 status to paid', '::1', '2025-03-06 01:18:46'),
(229, 1, NULL, 'Admin', 'Logout', 'User logged out', '::1', '2025-03-06 01:19:10'),
(230, 23, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-03-06 01:19:39'),
(231, 23, NULL, 'User', 'Payment Submission', 'Payment of PHP 22,500.00 submitted for review (Reference: 2012120513868)', '::1', '2025-03-06 01:21:26'),
(232, 23, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-03-06 01:23:55'),
(233, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-06 01:24:10'),
(234, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱22,500.00 for Conrad Palma (Unit 205)', '::1', '2025-03-06 02:16:14'),
(235, 1, NULL, 'Admin', 'Logout', 'User logged out', '::1', '2025-03-06 02:19:12'),
(236, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-09 12:00:05'),
(237, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded GCash payment of ₱9,999.98 for Maintenance Charges - David Fuentes (Unit 102)', '::1', '2025-03-09 12:09:53'),
(238, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash payment of ₱5,000.00 for Utilities - David Fuentes (Unit 102)', '::1', '2025-03-09 12:11:37'),
(239, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash rent payment of ₱22,500.00 for David Fuentes (Unit 102)', '::1', '2025-03-09 12:23:22'),
(240, 26, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-03-09 12:32:10'),
(241, 26, NULL, 'User', 'Unit Reservation', 'Reserved Unit #115 - Viewing scheduled for 2025-03-10 at 10:30 (Reservation ID: 34)', '::1', '2025-03-09 12:33:42'),
(242, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 34 updated to confirmed by Jhon Bautista (Admin)', '::1', '2025-03-09 12:34:09'),
(243, 1, NULL, 'Admin', 'Added New Tenant', 'Added new tenant for Unit 115', '::1', '2025-03-09 12:34:49'),
(244, 26, NULL, 'User', 'Unit Reservation', 'Reserved Unit #116 - Viewing scheduled for 2025-03-20 at 10:40 (Reservation ID: 35)', '::1', '2025-03-09 12:43:43'),
(245, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 35 updated to confirmed by Jhon Bautista (Admin)', '::1', '2025-03-09 12:44:03'),
(246, 1, NULL, 'Admin', 'Added New Unit to Tenant', 'Added unit 116 for tenant Genalyn Palma with receipt', '::1', '2025-03-09 12:45:17'),
(247, 26, NULL, 'User', 'Unit Reservation', 'Reserved Unit #807 - Viewing scheduled for 2025-03-30 at 13:30 (Reservation ID: 36)', '::1', '2025-03-09 12:56:30'),
(248, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 36 updated to confirmed by Jhon Bautista (Admin)', '::1', '2025-03-09 12:56:46'),
(249, 1, NULL, 'Admin', 'Added New Unit to Tenant', 'Added unit 807 for tenant Genalyn Palma with receipt', '::1', '2025-03-09 12:57:33'),
(250, 26, NULL, 'User', 'Archive Reservation', 'Archived reservation #36', '::1', '2025-03-09 13:02:29'),
(251, 26, NULL, 'User', 'Unit Reservation', 'Reserved Unit #807 - Viewing scheduled for 2025-03-30 at 10:31 (Reservation ID: 37)', '::1', '2025-03-09 13:03:02'),
(252, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 37 updated to confirmed by Jhon Bautista (Admin)', '::1', '2025-03-09 13:03:15'),
(253, 1, NULL, 'Admin', 'Added New Unit to Tenant', 'Added unit 807 for tenant Genalyn Palma with receipt', '::1', '2025-03-09 13:04:08'),
(254, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded GCash rent payment of ₱90,000.00 for Genalyn Palma (Unit 807)', '::1', '2025-03-09 13:05:39'),
(255, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash rent payment of ₱36,000.00 for Genalyn Palma (Unit 116)', '::1', '2025-03-09 13:15:30'),
(256, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash rent payment of ₱90,000.00 for Genalyn Palma (Unit 807)', '::1', '2025-03-09 13:16:44'),
(257, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash rent payment of ₱90,000.00 for Genalyn Palma (Unit 807)', '::1', '2025-03-09 13:21:01'),
(258, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash rent payment of ₱36,000.00 for Genalyn Palma (Unit 116)', '::1', '2025-03-09 13:33:03'),
(259, 26, NULL, 'User', 'Payment Submission', 'Payment of PHP 49,500.00 submitted for review (Reference: 2012120513868)', '::1', '2025-03-09 13:34:35'),
(260, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱49,500.00 for Genalyn Palma (Unit 115)', '::1', '2025-03-09 13:34:57'),
(261, 26, NULL, 'User', 'Payment Submission', 'Payment of PHP 1,000.00 submitted for review (Reference: 2012120513868)', '::1', '2025-03-09 13:44:41'),
(262, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱1,000.00 for Genalyn Palma (Unit 115)', '::1', '2025-03-09 13:45:17'),
(264, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱1,500.00 for Genalyn Palma (Unit 116)', '::1', '2025-03-09 13:55:01'),
(266, 26, NULL, 'User', 'Payment Submission', 'Payment of PHP 800.00 submitted for review (Reference: 2012120513868)', '::1', '2025-03-09 14:17:50'),
(267, 26, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-03-09 14:29:53'),
(268, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-10 22:58:33'),
(269, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-12 20:22:46'),
(270, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash rent payment of ₱22,500.00 for Conrad Palma (Unit 205)', '::1', '2025-03-12 21:59:45'),
(271, 1, NULL, 'Admin', 'Rejected Payment', 'Rejected payment of ₱800.00 for Genalyn Palma (Unit 807)', '::1', '2025-03-12 22:01:03'),
(272, 1, NULL, 'Admin', 'Rejected Payment', 'Rejected payment of ₱5,000.00 for Genalyn Palma (Unit 807)', '::1', '2025-03-12 22:01:16'),
(273, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash rent payment of ₱67,500.00 for Anora Hidson (Unit 107)', '::1', '2025-03-12 22:49:01'),
(274, 26, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-03-12 22:50:23'),
(275, 26, NULL, 'User', 'Payment Submission', 'Payment of PHP 36,000.00 submitted for review (Reference: 201923210312)', '::1', '2025-03-12 22:56:37'),
(276, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded GCash rent payment of ₱90,000.00 for Anora Hidson (Unit 1004)', '::1', '2025-03-12 23:28:32'),
(277, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash payment of ₱10,000.00 for Utilities - Anora Hidson (Unit 1004)', '::1', '2025-03-12 23:29:04'),
(278, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded GCash payment of ₱5,000.00 for Utilities - Anora Hidson (Unit 1004)', '::1', '2025-03-12 23:29:43'),
(279, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded GCash rent payment of ₱67,500.00 for Anora Hidson (Unit 107)', '::1', '2025-03-12 23:32:07'),
(280, 1, NULL, 'Admin', 'Rejected Payment', 'Rejected payment of ₱36,000.00 for Genalyn Palma (Unit 116)', '::1', '2025-03-12 23:34:40'),
(281, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded GCash rent payment of ₱49,500.00 for Genalyn Palma (Unit 115)', '::1', '2025-03-12 23:43:43'),
(282, 26, NULL, 'User', 'Payment Submission', 'Payment of PHP 500.00 submitted for review (Reference: 201923210312)', '::1', '2025-03-12 23:44:53'),
(283, 1, NULL, 'Admin', 'Rejected Payment', 'Rejected payment of ₱500.00 for Genalyn Palma (Unit 807)', '::1', '2025-03-12 23:45:14'),
(284, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-13 20:23:20'),
(285, 1, NULL, 'Admin', 'Generate Report', 'Generated Unit Occupancy Report for January 2025', '::1', '2025-03-13 22:02:53'),
(286, 1, NULL, 'Admin', 'Delete Report', 'Deleted Unit Occupancy Report (ID: 191) for period 2025-01', '::1', '2025-03-13 22:03:02'),
(287, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-13 23:14:01'),
(288, 1, NULL, 'Admin', 'Sent Invoice Email', 'Sent invoice #INV-20250304-9270 to Conrad Palma (kjstevenpalma18@gmail.com)', '::1', '2025-03-13 23:26:56'),
(289, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-13 16:51:11'),
(290, 1, NULL, 'Admin', 'Upload Contract', 'Contract uploaded for tenant ID: 43 - File: 67d30db6ee0b7_43_Rental_Agreement.pdf', '136.158.16.124', '2025-03-13 16:54:14'),
(291, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 32 updated to confirmed by Jhon Bautista (Admin)', '136.158.16.124', '2025-03-13 16:54:32'),
(292, 11, NULL, 'User', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-13 16:58:27'),
(293, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-15 11:32:50'),
(294, 1, NULL, 'Admin', 'Generate Report', 'Generated Unit Occupancy Report for February 2025', '136.158.16.124', '2025-03-15 11:35:14'),
(295, 1, NULL, 'Admin', 'Generate Report', 'Generated Property Maintenance Report for January 2025', '136.158.16.124', '2025-03-15 11:35:24'),
(296, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.16.124', '2025-03-15 11:44:50'),
(297, 27, NULL, 'User', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-15 11:58:23'),
(298, 27, NULL, 'User', 'Logout', 'User logged out', '136.158.16.124', '2025-03-15 12:04:21'),
(299, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-15 12:32:43'),
(300, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 703 to Occupied', '136.158.16.124', '2025-03-15 12:35:06'),
(301, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 703 to Available', '136.158.16.124', '2025-03-15 12:35:08'),
(302, 1, NULL, 'Admin', 'Delete Report', 'Deleted Property Maintenance Report (ID: 193) for period 2025-01', '136.158.16.124', '2025-03-15 12:55:32'),
(303, 1, NULL, 'Admin', 'Delete Report', 'Deleted Unit Occupancy Report (ID: 192) for period 2025-02', '136.158.16.124', '2025-03-15 12:55:33'),
(304, 1, NULL, 'Admin', 'Generate Report', 'Generated Unit Occupancy Report for January 2025', '136.158.16.124', '2025-03-15 12:55:36'),
(305, 1, NULL, 'Admin', 'Generate Report', 'Generated Property Maintenance Report for January 2025', '136.158.16.124', '2025-03-15 12:55:38'),
(306, 28, NULL, 'User', 'Login', 'User logged in successfully', '175.176.41.209', '2025-03-15 13:15:03'),
(307, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-15 13:33:50'),
(308, 28, NULL, 'User', 'Profile Image Update', 'Updated profile image: 67d58684ce24e-IMG_1661.png', '175.176.40.142', '2025-03-15 13:54:12'),
(309, 28, NULL, 'User', 'Unit Reservation', 'Reserved Unit #201 - Viewing scheduled for 2025-03-16 at 09:00 (Reservation ID: 38)', '175.176.40.142', '2025-03-15 13:56:32'),
(310, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 38 updated to confirmed by Jhon Bautista (Admin)', '136.158.16.124', '2025-03-15 13:57:34'),
(311, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.16.124', '2025-03-15 14:05:32'),
(312, 28, NULL, 'User', 'Logout', 'User logged out', '175.176.40.142', '2025-03-15 14:06:37'),
(313, 28, NULL, 'User', 'Login', 'User logged in successfully', '175.176.40.142', '2025-03-15 14:09:18'),
(314, 28, NULL, 'User', 'Logout', 'User logged out', '175.176.40.142', '2025-03-15 14:09:48'),
(315, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-16 15:10:03'),
(316, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.16.124', '2025-03-16 15:11:47'),
(317, NULL, 15, 'Staff', 'Staff Login', 'Staff member logged in successfully', '136.158.16.124', '2025-03-16 15:11:58'),
(318, NULL, 15, 'Staff', 'Submit Maintenance Report', 'Maintenance Report submitted for Request #96. Status: Completed', '136.158.16.124', '2025-03-16 15:32:46'),
(319, NULL, 15, 'Staff', 'Generate PDF', 'Generated maintenance report PDF: maintenance_report_96_20250316_153246.pdf', '136.158.16.124', '2025-03-16 15:32:46'),
(320, NULL, 15, 'Staff', 'Staff  Logout', 'Staff member logged out successfully', '136.158.16.124', '2025-03-16 15:33:25'),
(321, 11, NULL, 'User', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-16 15:33:54'),
(322, 11, NULL, 'User', 'Payment Submission', 'Payment of PHP 1,000.00 submitted for review (Reference: 2012120513868)', '136.158.16.124', '2025-03-16 15:36:30'),
(323, 11, NULL, 'User', 'Payment Submission', 'Payment of PHP 22,500.00 submitted for review (Reference: 2012120513868)', '136.158.16.124', '2025-03-16 15:38:00'),
(324, 11, NULL, 'User', 'Logout', 'User logged out', '136.158.16.124', '2025-03-16 15:38:53'),
(325, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-16 15:39:10'),
(326, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱22,500.00 for David Fuentes (Unit 1002)', '136.158.16.124', '2025-03-16 15:40:08'),
(327, 11, NULL, 'User', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-16 15:42:06'),
(328, 11, NULL, 'User', 'Payment Submission', 'Payment of PHP 33,750.00 submitted for review (Reference: 1001543610110)', '136.158.16.124', '2025-03-16 15:43:19'),
(329, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱33,750.00 for David Fuentes (Unit 105)', '136.158.16.124', '2025-03-16 15:43:59'),
(330, 11, NULL, 'User', 'Payment Submission', 'Payment of PHP 90,000.00 submitted for review (Reference: 1001543610110)', '136.158.16.124', '2025-03-16 15:45:02'),
(331, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱90,000.00 for David Fuentes (Unit 807)', '136.158.16.124', '2025-03-16 15:46:40'),
(332, 1, NULL, 'Admin', 'Rejected Payment', 'Rejected payment of ₱1,000.00 for David Fuentes (Unit 1002)', '136.158.16.124', '2025-03-16 15:54:48'),
(333, 11, NULL, 'User', 'Payment Submission', 'Payment of PHP 500.00 submitted for review (Reference: 1001543610110)', '136.158.16.124', '2025-03-16 16:03:35'),
(334, 1, NULL, 'Admin', 'Rejected Payment', 'Rejected payment of ₱500.00 for David Fuentes (Unit 102) - Reason: Insufficient amount, Please try again with valid amount. Thank you!\n\n- PropertyWise Team', '136.158.16.124', '2025-03-16 16:12:12'),
(335, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250316-7617 for tenant #43', '136.158.16.124', '2025-03-16 16:16:52'),
(336, 1, NULL, 'Admin', 'Sent Invoice Email', 'Sent invoice #INV-20250316-7617 to David Fuentes (kjstevengaming@gmail.com)', '136.158.16.124', '2025-03-16 16:17:23'),
(337, 1, NULL, 'Admin', 'Update Maintenance Status', 'Updated maintenance request #98 status to: In Progress', '136.158.16.124', '2025-03-16 16:24:48'),
(338, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.16.124', '2025-03-16 16:25:41'),
(339, 11, NULL, 'User', 'Logout', 'User logged out', '136.158.16.124', '2025-03-16 16:25:52'),
(340, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-17 14:06:37'),
(341, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 103 (Warehouse)', '136.158.16.124', '2025-03-17 14:08:00'),
(342, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 302 (Warehouse)', '136.158.16.124', '2025-03-17 14:09:23'),
(343, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 303 (Warehouse)', '136.158.16.124', '2025-03-17 14:10:16'),
(344, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 304 (Warehouse)', '136.158.16.124', '2025-03-17 14:10:43'),
(345, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 402 (Warehouse)', '136.158.16.124', '2025-03-17 14:11:16'),
(346, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 403 (Warehouse)', '136.158.16.124', '2025-03-17 14:12:01'),
(347, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 11) role from User to Admin', '136.158.16.124', '2025-03-17 15:03:10'),
(348, 1, NULL, 'Admin', 'Update User Role', 'Changed user (ID: 11) role from Admin to User', '136.158.16.124', '2025-03-17 15:03:14'),
(349, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.16.124', '2025-03-17 15:15:40'),
(350, 11, NULL, 'User', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-18 06:58:54'),
(351, 11, NULL, 'User', 'Logout', 'User logged out', '136.158.16.124', '2025-03-18 07:01:56'),
(352, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.16.124', '2025-03-18 07:38:40'),
(353, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '136.158.16.124', '2025-03-18 07:40:14'),
(354, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '136.158.16.124', '2025-03-18 07:40:15'),
(355, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '136.158.16.124', '2025-03-18 07:40:15'),
(356, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '136.158.16.124', '2025-03-18 07:40:15'),
(357, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '136.158.16.124', '2025-03-18 07:40:15'),
(358, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '136.158.16.124', '2025-03-18 07:40:16'),
(359, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '136.158.16.124', '2025-03-18 07:40:16'),
(360, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Available', '136.158.16.124', '2025-03-18 07:40:17'),
(361, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '136.158.16.124', '2025-03-18 07:40:17'),
(362, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Available', '136.158.16.124', '2025-03-18 07:40:18'),
(363, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250316-7617 status to paid', '136.158.16.124', '2025-03-18 07:41:14'),
(364, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.16.124', '2025-03-18 07:50:19'),
(365, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '175.176.43.4', '2025-03-18 10:31:58'),
(366, 1, NULL, 'Admin', 'Logout', 'User logged out', '175.176.43.4', '2025-03-18 10:43:17'),
(367, 29, NULL, 'User', 'Login', 'User logged in successfully', '130.105.53.117', '2025-03-19 14:31:14'),
(368, 29, NULL, 'User', 'Logout', 'User logged out', '130.105.53.117', '2025-03-19 14:33:50'),
(369, 11, NULL, 'User', 'Login', 'User logged in successfully', '136.158.30.16', '2025-03-20 05:33:32'),
(370, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #103 - Viewing scheduled for 2025-03-21 at 09:30 (Reservation ID: 39)', '136.158.30.16', '2025-03-20 05:37:29'),
(371, 11, NULL, 'User', 'Submit Maintenance Request', 'Submitted maintenance request for unit 1002, Issue: Other', '136.158.30.16', '2025-03-20 05:40:49'),
(372, 11, NULL, 'User', 'Payment Submission', 'Payment of PHP 90,000.00 submitted for review (Reference: 2012120513868)', '136.158.30.16', '2025-03-20 05:42:27'),
(373, 11, NULL, 'User', 'Logout', 'User logged out', '136.158.30.16', '2025-03-20 05:42:58'),
(374, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.30.16', '2025-03-20 05:43:13'),
(375, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '136.158.30.16', '2025-03-20 05:45:58'),
(376, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '136.158.30.16', '2025-03-20 05:45:58'),
(377, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Available', '136.158.30.16', '2025-03-20 05:46:01'),
(378, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Available', '136.158.30.16', '2025-03-20 05:46:01'),
(379, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 39 updated to confirmed by Jhon Bautista (Admin)', '136.158.30.16', '2025-03-20 05:47:45'),
(380, 1, NULL, 'Admin', 'Assign Maintenance Staff', 'Assigned maintenance request #99 to staff ID: 15 with priority: high', '136.158.30.16', '2025-03-20 05:59:03'),
(381, 1, NULL, 'Admin', 'Assign Maintenance Staff', 'Assigned maintenance request #99 to staff ID: 15 with priority: high', '136.158.30.16', '2025-03-20 05:59:03'),
(382, 1, NULL, 'Admin', 'Update Maintenance Status', 'Updated maintenance request #99 status to: In Progress', '136.158.30.16', '2025-03-20 05:59:17'),
(383, 1, NULL, 'Admin', 'Update Maintenance Status', 'Updated maintenance request #99 status to: In Progress', '136.158.30.16', '2025-03-20 05:59:17'),
(384, 1, NULL, 'Admin', 'Update Staff Information', 'Updated staff (ID: 15) information: Status: Busy → Available', '136.158.30.16', '2025-03-20 06:00:06'),
(385, NULL, 15, 'Staff', 'Staff Login', 'Staff member logged in successfully', '136.158.30.16', '2025-03-20 06:00:23'),
(386, NULL, 15, 'Staff', 'Submit Maintenance Report', 'Maintenance Report submitted for Request #99. Status: Completed', '136.158.30.16', '2025-03-20 06:03:24'),
(387, NULL, 15, 'Staff', 'Generate PDF', 'Generated maintenance report PDF: maintenance_report_99_20250320_060324.pdf', '136.158.30.16', '2025-03-20 06:03:24'),
(388, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250320-6629 for tenant #50', '136.158.30.16', '2025-03-20 06:05:50'),
(389, 1, NULL, 'Admin', 'Sent Invoice Email', 'Sent invoice #INV-20250320-6629 to David Fuentes (kjstevengaming@gmail.com)', '136.158.30.16', '2025-03-20 06:06:17'),
(390, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash payment of ₱1,500.00 for Maintenance Fee - David Fuentes (Unit 1002)', '136.158.30.16', '2025-03-20 06:07:28'),
(391, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱90,000.00 for David Fuentes (Unit 1002)', '136.158.30.16', '2025-03-20 06:10:00'),
(392, 1, NULL, 'Admin', 'Generate Report', 'Generated Property Maintenance Report for March 2025', '136.158.30.16', '2025-03-20 06:11:09'),
(393, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.30.16', '2025-03-20 06:15:16'),
(394, NULL, 15, 'Staff', 'Staff  Logout', 'Staff member logged out successfully', '136.158.30.16', '2025-03-20 06:15:29'),
(395, 29, NULL, 'User', 'Login', 'User logged in successfully', '130.105.53.117', '2025-03-20 09:26:55'),
(396, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.30.16', '2025-03-20 09:28:31'),
(397, 29, NULL, 'User', 'Unit Reservation', 'Reserved Unit #205 - Viewing scheduled for 2025-03-21 at 10:30 (Reservation ID: 40)', '130.105.53.117', '2025-03-20 09:28:47'),
(398, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 40 updated to confirmed by Jhon Bautista (Admin)', '136.158.30.16', '2025-03-20 09:29:48'),
(399, 1, NULL, 'Admin', 'Added New Tenant', 'Added new tenant for Unit 205', '136.158.30.16', '2025-03-20 09:30:48'),
(400, 29, NULL, 'User', 'Submit Maintenance Request', 'Submitted maintenance request for unit 205, Issue: Leaking Faucet', '130.105.53.117', '2025-03-20 09:32:14'),
(401, 1, NULL, 'Admin', 'Assign Maintenance Staff', 'Assigned maintenance request #100 to staff ID: 17 with priority: high', '136.158.30.16', '2025-03-20 09:32:47'),
(402, 1, NULL, 'Admin', 'Upload Contract', 'Contract uploaded for tenant ID: 56 - File: 67dbe0e0d8523_56_Rental_Agreement.pdf', '136.158.30.16', '2025-03-20 09:33:20'),
(403, 29, NULL, 'User', 'Logout', 'User logged out', '130.105.53.117', '2025-03-20 09:34:22'),
(404, 30, NULL, 'User', 'Login', 'User logged in successfully', '112.207.159.102', '2025-03-20 09:34:51'),
(405, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.30.16', '2025-03-20 09:34:53'),
(406, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '130.105.53.117', '2025-03-20 09:35:35'),
(407, 30, NULL, 'User', 'Unit Reservation', 'Reserved Unit #1003 - Viewing scheduled for 2025-03-21 at 09:01 (Reservation ID: 41)', '112.207.159.102', '2025-03-20 09:37:19'),
(408, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Maintenance', '130.105.53.117', '2025-03-20 09:38:01'),
(409, 1, NULL, 'Admin', 'Updated Unit Status', 'Updated status of unit 403 to Occupied', '130.105.53.117', '2025-03-20 09:38:09'),
(410, 30, NULL, 'User', 'Logout', 'User logged out', '112.207.159.102', '2025-03-20 09:38:09'),
(411, 1, NULL, 'Admin', 'Assign Maintenance Staff', 'Assigned maintenance request #100 to staff ID: 15 with priority: medium', '130.105.53.117', '2025-03-20 09:42:06'),
(412, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250320-1030 for tenant #56', '130.105.53.117', '2025-03-20 09:44:22'),
(413, 1, NULL, 'Admin', 'Sent Invoice Email', 'Sent invoice #INV-20250320-1030 to PJ ETESAM (etesam90@gmail.com)', '130.105.53.117', '2025-03-20 09:44:46'),
(414, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash rent payment of ₱22,500.00 for PJ ETESAM (Unit 205)', '130.105.53.117', '2025-03-20 09:46:22'),
(415, 1, NULL, 'Admin', 'Generate Report', 'Generated Unit Occupancy Report for January 2025', '130.105.53.117', '2025-03-20 09:47:14'),
(416, 1, NULL, 'Admin', 'Logout', 'User logged out', '130.105.53.117', '2025-03-20 09:48:44'),
(417, NULL, 15, 'Staff', 'Staff Login', 'Staff member logged in successfully', '130.105.53.117', '2025-03-20 09:49:23'),
(418, NULL, 15, 'Staff', 'Submit Maintenance Report', 'Maintenance Report submitted for Request #100. Status: Completed', '130.105.53.117', '2025-03-20 09:51:28'),
(419, NULL, 15, 'Staff', 'Generate PDF', 'Generated maintenance report PDF: maintenance_report_100_20250320_095128.pdf', '130.105.53.117', '2025-03-20 09:51:28'),
(420, NULL, 15, 'Staff', 'Staff  Logout', 'Staff member logged out successfully', '130.105.53.117', '2025-03-20 09:57:57'),
(421, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '130.105.53.117', '2025-03-20 09:58:10'),
(422, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '175.176.44.144', '2025-03-21 12:02:14'),
(423, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '175.176.44.144', '2025-03-21 12:02:17'),
(424, 1, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash rent payment of ₱22,500.00 for PJ ETESAM (Unit 205)', '175.176.44.144', '2025-03-21 12:06:01'),
(425, 29, NULL, 'User', 'Login', 'User logged in successfully', '175.176.44.144', '2025-03-21 12:08:54'),
(426, 29, NULL, 'User', 'Payment Submission', 'Payment of PHP 22,500.00 submitted for review (Reference: 212234182329)', '175.176.44.144', '2025-03-21 12:11:45'),
(427, 1, NULL, 'Admin', 'Rejected Payment', 'Rejected payment of ₱22,500.00 for PJ ETESAM (Unit 205) - Reason: invalid payment', '175.176.44.144', '2025-03-21 12:13:21'),
(428, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.16.218', '2025-03-21 15:59:20'),
(429, 27, NULL, 'User', 'Login', 'User logged in successfully', '136.158.16.218', '2025-03-21 16:01:01'),
(430, 27, NULL, 'User', 'Unit Reservation', 'Reserved Unit #703 - Viewing scheduled for 2025-03-24 at 09:30 (Reservation ID: 42)', '136.158.16.218', '2025-03-21 16:01:53'),
(431, 27, NULL, 'User', 'Unit Reservation', 'Reserved Unit #303 - Viewing scheduled for 2025-03-24 at 12:00 (Reservation ID: 43)', '136.158.16.218', '2025-03-21 16:02:30'),
(432, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 43 updated to confirmed by Jhon Bautista (Admin)', '136.158.16.218', '2025-03-21 16:02:54'),
(433, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 42 updated to confirmed by Jhon Bautista (Admin)', '136.158.16.218', '2025-03-21 16:03:04'),
(434, 1, NULL, 'Admin', 'Added New Tenant', 'Added new tenant for Unit 303', '136.158.16.218', '2025-03-21 16:04:22'),
(435, 1, NULL, 'Admin', 'Archived Tenant', 'Archived tenant David Fuentes from unit 105', '136.158.16.218', '2025-03-21 16:13:54'),
(436, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250316-7617 status to unpaid', '136.158.16.218', '2025-03-21 16:19:12'),
(437, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250316-7617 status to paid', '136.158.16.218', '2025-03-21 16:19:15'),
(438, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250321-2977 for tenant #42', '136.158.16.218', '2025-03-21 16:23:31'),
(439, 1, NULL, 'Admin', 'Deleted Invoice', 'Deleted invoice #INV-20250321-2977 for tenant #42', '136.158.16.218', '2025-03-21 16:27:05'),
(440, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250321-6690 for tenant #42', '136.158.16.218', '2025-03-21 16:27:24'),
(441, 1, NULL, 'Admin', 'Deleted Invoice', 'Deleted invoice #INV-20250321-6690 for tenant #42', '136.158.16.218', '2025-03-21 16:50:36'),
(442, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250320-6629 status to paid', '136.158.16.218', '2025-03-21 16:50:45'),
(443, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250320-6629 status to unpaid', '136.158.16.218', '2025-03-21 16:50:47'),
(444, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250321-3310 for tenant #42', '136.158.16.218', '2025-03-21 16:51:06'),
(445, 1, NULL, 'Admin', 'Deleted Invoice', 'Deleted invoice #INV-20250321-3310 for tenant #42', '136.158.16.218', '2025-03-21 17:10:41'),
(446, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250321-8948 for tenant #42', '136.158.16.218', '2025-03-21 17:12:26'),
(447, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250321-8948 status to overdue', '136.158.16.218', '2025-03-21 17:31:11'),
(448, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250321-8948 status to paid', '136.158.16.218', '2025-03-21 17:31:22'),
(449, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250320-1030 status to overdue', '136.158.16.218', '2025-03-21 17:32:22'),
(450, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250321-8948 status to overdue', '136.158.16.218', '2025-03-21 17:32:25'),
(451, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250321-8948 status to paid', '136.158.16.218', '2025-03-21 17:32:32'),
(452, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250320-1030 status to paid', '136.158.16.218', '2025-03-21 17:32:33'),
(453, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250320-1030 status to unpaid', '136.158.16.218', '2025-03-21 17:32:35'),
(454, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250321-8948 status to overdue', '136.158.16.218', '2025-03-21 17:32:38'),
(455, 1, NULL, 'Admin', 'Updated Invoice Status', 'Updated invoice #INV-20250320-6629 status to paid', '136.158.16.218', '2025-03-21 17:32:44'),
(456, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.16.218', '2025-03-21 17:42:06'),
(457, 27, NULL, 'User', 'Logout', 'User logged out', '136.158.16.218', '2025-03-21 17:42:12'),
(458, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.31.97', '2025-03-22 16:31:13'),
(466, NULL, 19, 'Staff', 'Staff Login', 'Staff member logged in successfully', '136.158.31.97', '2025-03-22 16:48:37'),
(467, NULL, 19, 'Staff', 'Password Change', 'Staff member changed their password', '136.158.31.97', '2025-03-22 16:49:14'),
(468, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.31.97', '2025-03-22 16:52:12'),
(470, 29, NULL, 'User', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:87b:f05d:4747:4b07', '2025-03-23 10:29:32'),
(471, 29, NULL, 'User', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:87b:f05d:4747:4b07', '2025-03-23 10:40:46'),
(472, 29, NULL, 'User', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 14:26:16'),
(473, 29, NULL, 'User', 'Logout', 'User logged out', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 14:51:59'),
(474, 29, NULL, 'User', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 14:53:10'),
(475, 29, NULL, 'User', 'Logout', 'User logged out', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 14:53:35'),
(476, 31, NULL, 'User', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 14:55:18'),
(477, 31, NULL, 'User', 'Logout', 'User logged out', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 14:56:14'),
(478, 29, NULL, 'User', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 14:56:23'),
(479, 31, NULL, 'Admin', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 15:00:26'),
(480, NULL, NULL, 'User', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 15:01:47'),
(481, NULL, NULL, 'User', 'Logout', 'User logged out', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 15:02:10'),
(482, 31, NULL, 'Admin', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 15:02:21'),
(483, 31, NULL, 'Admin', 'Logout', 'User logged out', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 15:03:49'),
(484, 31, NULL, 'Admin', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 15:05:19'),
(485, NULL, 21, 'Staff', 'Staff Login', 'Staff member logged in successfully', '2404:3c00:330d:7fb0:e51a:2df:e7ec:6b99', '2025-03-23 15:07:08');
INSERT INTO `activity_logs` (`log_id`, `user_id`, `staff_id`, `user_role`, `action`, `details`, `ip_address`, `timestamp`) VALUES
(486, 29, NULL, 'User', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:d5d5:b398:4dd7:bf4', '2025-03-24 02:05:29'),
(487, 29, NULL, 'User', 'Unit Reservation', 'Reserved Unit #402 - Viewing scheduled for 2025-03-26 at 10:38 (Reservation ID: 44)', '2404:3c00:330d:7fb0:d5d5:b398:4dd7:bf4', '2025-03-24 02:37:55'),
(488, 11, NULL, 'User', 'Login', 'User logged in successfully', '136.158.31.97', '2025-03-24 05:46:51'),
(489, 11, NULL, 'User', 'Logout', 'User logged out', '136.158.31.97', '2025-03-24 06:05:43'),
(490, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '136.158.31.97', '2025-03-24 06:06:00'),
(491, 31, NULL, 'Admin', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:d5d5:b398:4dd7:bf4', '2025-03-24 06:06:02'),
(492, 31, NULL, 'Admin', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:d5d5:b398:4dd7:bf4', '2025-03-24 06:06:51'),
(493, 31, NULL, 'Admin', 'Logout', 'User logged out', '2404:3c00:330d:7fb0:d5d5:b398:4dd7:bf4', '2025-03-24 06:07:07'),
(494, 29, NULL, 'User', 'Login', 'User logged in successfully', '2404:3c00:330d:7fb0:d5d5:b398:4dd7:bf4', '2025-03-24 06:07:34'),
(495, 1, NULL, 'Admin', 'Logout', 'User logged out', '136.158.31.97', '2025-03-24 06:11:00'),
(496, NULL, 15, 'Staff', 'Staff Login', 'Staff member logged in successfully', '136.158.31.97', '2025-03-24 06:11:16'),
(497, NULL, 15, 'Staff', 'Staff  Logout', 'Staff member logged out successfully', '136.158.31.97', '2025-03-24 06:15:03'),
(498, 29, NULL, 'User', 'Unit Reservation', 'Reserved Unit #402 - Viewing scheduled for 2025-03-25 at 09:40 (Reservation ID: 45)', '2404:3c00:330d:7fb0:d5d5:b398:4dd7:bf4', '2025-03-24 06:37:07'),
(499, 29, NULL, 'User', 'Unit Reservation', 'Reserved Unit #402 - Viewing scheduled for 2025-03-25 at 10:50 (Reservation ID: 46)', '2404:3c00:330d:7fb0:9dd4:ae98:f82b:9258', '2025-03-24 06:51:01'),
(500, 29, NULL, 'User', 'Cancel Reservation', 'Cancelled reservation #46', '2404:3c00:330d:7fb0:9dd4:ae98:f82b:9258', '2025-03-24 06:52:54'),
(501, 29, NULL, 'User', 'Cancel Reservation', 'Cancelled reservation #45', '2404:3c00:330d:7fb0:9dd4:ae98:f82b:9258', '2025-03-24 06:52:56'),
(502, 29, NULL, 'User', 'Cancel Reservation', 'Cancelled reservation #44', '2404:3c00:330d:7fb0:9dd4:ae98:f82b:9258', '2025-03-24 06:52:58'),
(503, 33, NULL, 'User', 'Login', 'User logged in successfully', '175.176.40.208', '2025-03-24 09:32:46'),
(504, 33, NULL, 'User', 'Unit Reservation', 'Reserved Unit #302 - Viewing scheduled for 2025-03-25 at 10:34 (Reservation ID: 47)', '175.176.40.208', '2025-03-24 09:34:50'),
(505, 31, NULL, 'Admin', 'Login', 'User logged in successfully', '175.176.40.208', '2025-03-24 09:36:02'),
(506, 31, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 47 updated to confirmed by PJ ETESAM (Admin)', '175.176.40.208', '2025-03-24 09:37:48'),
(507, 31, NULL, 'Admin', 'Added New Tenant', 'Added new tenant for Unit 302', '175.176.40.208', '2025-03-24 09:39:24'),
(508, 33, NULL, 'User', 'Submit Maintenance Request', 'Submitted maintenance request for unit 302, Issue: Leaking Faucet', '175.176.40.208', '2025-03-24 09:40:43'),
(509, NULL, 22, 'Staff', 'Staff Login', 'Staff member logged in successfully', '175.176.40.208', '2025-03-24 09:45:36'),
(510, 31, NULL, 'Admin', 'Assign Maintenance Staff', 'Assigned maintenance request #101 to staff ID: 22 with priority: high', '175.176.40.208', '2025-03-24 09:46:25'),
(511, NULL, 22, 'Staff', 'Submit Maintenance Report', 'Maintenance Report submitted for Request #101. Status: Completed', '175.176.40.208', '2025-03-24 09:50:59'),
(512, NULL, 22, 'Staff', 'Generate PDF', 'Generated maintenance report PDF: maintenance_report_101_20250324_095059.pdf', '175.176.40.208', '2025-03-24 09:50:59'),
(513, 31, NULL, 'Admin', 'Upload Contract', 'Contract uploaded for tenant ID: 58 - File: 67e12b6a55bcc_58_CONTRACT.pdf', '175.176.40.208', '2025-03-24 09:52:42'),
(514, 33, NULL, 'User', 'Payment Submission', 'Payment of PHP 500.00 submitted for review (Reference: 12345678910111)', '175.176.40.208', '2025-03-24 09:56:49'),
(515, 31, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱500.00 for Paolo James Etesam (Unit 302)', '175.176.40.208', '2025-03-24 09:58:16'),
(516, 31, NULL, 'Admin', 'Added New Unit', 'Added new unit: 209 (Warehouse)', '175.176.40.208', '2025-03-24 10:05:34'),
(517, 33, NULL, 'User', 'Unit Reservation', 'Reserved Unit #209 - Viewing scheduled for 2025-03-30 at 10:10 (Reservation ID: 48)', '175.176.40.208', '2025-03-24 10:07:52'),
(518, 31, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 48 updated to confirmed by PJ ETESAM (Admin)', '175.176.40.208', '2025-03-24 10:08:29'),
(519, 31, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250324-4421 for tenant #58', '175.176.40.208', '2025-03-24 10:32:13'),
(520, 31, NULL, 'Admin', 'Sent Invoice Email', 'Sent invoice #INV-20250324-4421 to Paolo James Etesam (etesam099@gmail.com)', '175.176.40.208', '2025-03-24 10:32:16'),
(521, 31, NULL, 'Admin', 'Sent Invoice Email', 'Sent invoice #INV-20250324-4421 to Paolo James Etesam (etesam099@gmail.com)', '175.176.40.208', '2025-03-24 10:35:54'),
(522, 31, NULL, 'Admin', 'Recorded Manual Payment', 'Recorded Cash rent payment of ₱81,000.00 for Paolo James Etesam (Unit 302)', '175.176.40.208', '2025-03-24 10:39:42'),
(523, 31, NULL, 'Admin', 'Generate Report', 'Generated Unit Occupancy Report for January 2025', '175.176.40.208', '2025-03-24 10:42:31'),
(524, 31, NULL, 'Admin', 'Generate Report', 'Generated Property Maintenance Report for January 2025', '175.176.40.208', '2025-03-24 10:43:19'),
(525, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-29 21:35:16'),
(526, 1, NULL, 'Admin', 'Logout', 'User logged out', '::1', '2025-03-29 21:35:47'),
(527, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-03-29 21:35:58'),
(528, 11, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-03-29 22:03:07'),
(529, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-03-30 21:46:02'),
(530, 11, NULL, 'User', 'KYC Submission', 'User submitted KYC verification', '::1', '2025-03-30 21:55:01'),
(531, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-03-30 22:23:08'),
(532, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-04-01 14:15:46'),
(533, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-04-01 14:23:42'),
(534, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-04-01 14:23:46'),
(535, 11, NULL, 'User', 'KYC Submission', 'User submitted KYC verification', '::1', '2025-04-01 14:36:31'),
(536, 1, NULL, 'Admin', 'KYC Approval', 'Approved KYC verification #3', '::1', '2025-04-01 15:10:42'),
(537, 1, NULL, 'Admin', 'KYC Rejection', 'Rejected KYC verification #3', '::1', '2025-04-01 15:30:52'),
(538, 1, NULL, 'Admin', 'KYC Approval', 'Approved KYC verification #3', '::1', '2025-04-01 15:31:46'),
(539, 11, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-04-01 15:34:55'),
(540, 27, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-04-01 15:35:41'),
(541, 1, NULL, 'Admin', 'Archive KYC', 'Archived KYC verification #3', '::1', '2025-04-01 15:50:31'),
(542, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 210 (Warehouse)', '::1', '2025-04-01 16:00:33'),
(543, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 402 (Warehouse)', '::1', '2025-04-01 16:03:14'),
(544, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 304 (Warehouse)', '::1', '2025-04-01 16:03:43'),
(545, 1, NULL, 'Admin', 'Logout', 'User logged out', '::1', '2025-04-01 16:30:34'),
(546, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-04-02 21:58:22'),
(547, 1, NULL, 'Admin', 'Archive Maintenance Request', 'Archived maintenance request #101', '::1', '2025-04-02 22:00:05'),
(548, 1, NULL, 'Admin', 'Archive Maintenance Request', 'Archived maintenance request #100', '::1', '2025-04-02 22:00:08'),
(549, 1, NULL, 'Admin', 'Archive Maintenance Request', 'Archived maintenance request #99', '::1', '2025-04-02 22:00:11'),
(550, 1, NULL, 'Admin', 'Archive Maintenance Request', 'Archived maintenance request #96', '::1', '2025-04-02 22:00:22'),
(551, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-04-08 21:19:52'),
(552, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-04-09 12:45:51'),
(553, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-04-09 12:45:56'),
(554, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-04-09 12:46:00'),
(555, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-04-09 13:51:18'),
(556, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to David Fuentes for unit 105', '::1', '2025-04-09 14:06:54'),
(557, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to David Fuentes for unit 105', '::1', '2025-04-09 14:06:58'),
(558, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to David Fuentes for unit 105', '::1', '2025-04-09 14:07:01'),
(559, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to David Fuentes for unit 105', '::1', '2025-04-09 14:16:25'),
(560, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to David Fuentes for unit 105', '::1', '2025-04-09 14:26:58'),
(561, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 105 on 2025-04-25 14:49 with CONRAD KANE', '::1', '2025-04-09 14:49:58'),
(562, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 105 on 2025-04-30 15:08 with CONRAD KANE', '::1', '2025-04-09 15:08:49'),
(563, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 105 on 2025-04-25 15:17 with CONRAD KANE', '::1', '2025-04-09 15:17:24'),
(564, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 105 on 2025-04-24 15:55 with CONRAD KANE', '::1', '2025-04-09 15:55:26'),
(565, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 105 on 2025-04-25 16:05 with CONRAD KANE', '::1', '2025-04-09 16:05:53'),
(566, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to David Fuentes for unit 102', '::1', '2025-04-09 16:06:22'),
(567, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 102 on 2025-04-21 16:06 with JAMES ALFONSO', '::1', '2025-04-09 16:06:33'),
(568, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 105 on 2025-04-23 16:17 with CONRAD KANE', '::1', '2025-04-09 16:18:02'),
(569, 1, NULL, 'Admin', 'Renewed Tenant Contract', 'Renewed contract for Renante Colaste on Unit 303', '::1', '2025-04-09 16:39:35'),
(570, 1, NULL, 'Admin', 'Renewed Tenant Contract', 'Renewed contract for Anora Hidson on Unit 107', '::1', '2025-04-09 16:41:51'),
(571, 1, NULL, 'Admin', 'Renewed Tenant Contract', 'Renewed contract for Anora Hidson on Unit 107', '::1', '2025-04-09 17:05:24'),
(572, 1, NULL, 'Admin', 'Renewed Tenant Contract', 'Renewed contract for Anora Hidson on Unit 107', '::1', '2025-04-09 17:08:34'),
(573, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-04-09 18:46:58'),
(574, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to Renante Colaste for unit 303', '::1', '2025-04-09 19:08:32'),
(575, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 303 on 2025-04-25 19:08 with KJ STEVEN PALMA', '::1', '2025-04-09 19:08:43'),
(576, 1, NULL, 'Admin', 'Turnover Inspection Completed', 'Completed inspection for 105, Cleanliness: excellent, Damages: none', '::1', '2025-04-09 19:35:44'),
(577, 1, NULL, 'Admin', 'Turnover Completed', 'Completed turnover for David Fuentes from unit 105', '::1', '2025-04-09 19:36:53'),
(578, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-04-09 19:43:25'),
(579, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #105 - Viewing scheduled for 2025-04-19 at 11:47 (Reservation ID: 49)', '::1', '2025-04-09 19:44:30'),
(580, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 49 updated to confirmed by Jhon Bautista (Admin)', '::1', '2025-04-09 19:45:16'),
(581, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to Conrad Palma for unit 205', '::1', '2025-04-09 20:32:53'),
(582, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 205 on 2025-04-27 11:38 with CONRAD KANE', '::1', '2025-04-09 20:35:44'),
(583, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to Conrad Palma for unit 205', '::1', '2025-04-09 20:46:07'),
(584, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 205 on 2025-04-27 10:48 with CONRAD KANE', '::1', '2025-04-09 20:46:26'),
(585, 1, NULL, 'Admin', 'Turnover Inspection Completed', 'Completed inspection for 205, Cleanliness: fair, Damages: minor', '::1', '2025-04-09 20:52:19'),
(586, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to Anora Hidson for unit 107', '::1', '2025-04-09 21:19:20'),
(587, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to Conrad Palma for unit 205', '::1', '2025-04-09 21:32:37'),
(588, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 205 on 2025-04-24 21:32 with CONRAD KANE', '::1', '2025-04-09 21:32:57'),
(589, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to Conrad Palma for unit 205', '::1', '2025-04-09 21:34:03'),
(590, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 205 on 2025-04-22 21:34 with CONRAD KANE', '::1', '2025-04-09 21:34:17'),
(591, 1, NULL, 'Admin', 'Turnover Step Reopened', 'Reopened turnover process to notified stage for unit 205', '::1', '2025-04-09 21:44:10'),
(592, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-04-09 22:34:49'),
(593, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to Conrad Palma for unit 205', '::1', '2025-04-09 22:47:06'),
(594, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 205 on 2025-04-20 14:51 with CONRAD KANE', '::1', '2025-04-09 22:47:27'),
(595, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 205 on 2025-04-26 23:00 with CONRAD KANE', '::1', '2025-04-09 23:00:51'),
(596, 1, NULL, 'Admin', 'Turnover Inspection Completed', 'Completed inspection for 205, Cleanliness: excellent, Damages: none', '::1', '2025-04-09 23:02:17'),
(597, 1, NULL, 'Admin', 'Turnover Step Reopened', 'Reopened turnover process to scheduled stage for unit 205', '::1', '2025-04-09 23:02:39'),
(598, 1, NULL, 'Admin', 'Turnover Step Reopened', 'Reopened turnover process to scheduled stage for unit 205', '::1', '2025-04-09 23:02:49'),
(599, 1, NULL, 'Admin', 'Turnover Step Reopened', 'Reopened turnover process to notified stage for unit 205', '::1', '2025-04-09 23:03:00'),
(600, 1, NULL, 'Admin', 'Turnover Notification', 'Sent turnover notification to Conrad Palma for unit 205', '::1', '2025-04-09 23:13:58'),
(601, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 205 on 2025-04-16 23:14 with CONRAD KANE', '::1', '2025-04-09 23:14:13'),
(602, 1, NULL, 'Admin', 'Turnover Inspection Completed', 'Completed inspection for 205, Cleanliness: excellent, Damages: none', '::1', '2025-04-09 23:15:58'),
(603, 1, NULL, 'Admin', 'Turnover Step Reopened', 'Reopened turnover process to scheduled stage for unit 205', '::1', '2025-04-09 23:16:12'),
(604, 1, NULL, 'Admin', 'Turnover Step Reopened', 'Reopened turnover process to notified stage for unit 205', '::1', '2025-04-09 23:16:32'),
(605, 1, NULL, 'Admin', 'Turnover Inspection Scheduled', 'Scheduled inspection for 205 on 2025-04-19 23:42 with CONRAD KANE', '::1', '2025-04-09 23:43:06'),
(606, 1, NULL, 'Admin', 'Turnover Inspection Completed', 'Completed inspection for 205, Cleanliness: excellent, Damages: none', '::1', '2025-04-09 23:43:39'),
(607, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-04-09 23:57:43'),
(608, 11, NULL, 'User', 'Password Change', 'Password changed successfully', '::1', '2025-04-10 00:04:39'),
(609, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 13:40 (Reservation ID: 50)', '::1', '2025-04-10 00:21:43'),
(610, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 13:40 (Reservation ID: 51)', '::1', '2025-04-10 00:21:55'),
(611, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 13:40 (Reservation ID: 52)', '::1', '2025-04-10 00:22:02'),
(612, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 13:40 (Reservation ID: 53)', '::1', '2025-04-10 00:22:02'),
(613, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 13:40 (Reservation ID: 54)', '::1', '2025-04-10 00:22:05'),
(614, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 13:40 (Reservation ID: 55)', '::1', '2025-04-10 00:22:06'),
(615, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 13:40 (Reservation ID: 56)', '::1', '2025-04-10 00:22:07'),
(616, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 13:40 (Reservation ID: 57)', '::1', '2025-04-10 00:22:08'),
(617, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 13:40 (Reservation ID: 58)', '::1', '2025-04-10 00:22:59'),
(618, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 13:40 (Reservation ID: 59)', '::1', '2025-04-10 00:23:00'),
(619, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #207 - Viewing scheduled for 2025-04-19 at 13:24 (Reservation ID: 60)', '::1', '2025-04-10 00:23:38'),
(620, 11, NULL, 'User', 'Cancel Reservation', 'Cancelled reservation #50', '::1', '2025-04-10 00:27:44'),
(621, 11, NULL, 'User', 'Cancel Reservation', 'Cancelled reservation #51', '::1', '2025-04-10 00:27:49'),
(622, 11, NULL, 'User', 'Cancel Reservation', 'Cancelled reservation #60', '::1', '2025-04-10 00:30:50'),
(623, 11, NULL, 'User', 'Unit Reservation', 'Reserved Unit #206 - Viewing scheduled for 2025-04-14 at 15:32 (Reservation ID: 61)', '::1', '2025-04-10 00:32:18'),
(624, 11, NULL, 'User', 'Submit Maintenance Request', 'Submitted maintenance request for unit 701, Issue: Heating Issue', '::1', '2025-04-10 00:36:03'),
(625, 11, NULL, 'User', 'Payment Submission', 'Payment of PHP 22,500.00 submitted for review (Reference: 1001543610110)', '::1', '2025-04-10 00:43:01'),
(626, 11, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-04-10 00:43:46'),
(627, 26, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-04-10 00:43:59'),
(628, 26, NULL, 'User', 'KYC Submission', 'User submitted KYC verification', '::1', '2025-04-10 00:46:03'),
(629, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 707 (Warehouse)', '::1', '2025-04-10 00:54:24'),
(630, 1, NULL, 'Admin', 'Added New Unit', 'Added new unit: 609 (Office)', '::1', '2025-04-10 00:55:34'),
(634, 1, NULL, 'Admin', 'KYC Approval', 'Approved KYC verification #4', '::1', '2025-04-10 01:02:47'),
(635, 26, NULL, 'User', 'Unit Reservation', 'Reserved Unit #609 - Viewing scheduled for 2025-04-14 at 10:40 (Reservation ID: 62)', '::1', '2025-04-10 01:03:54'),
(637, 1, NULL, 'Admin', 'Update Reservation', 'Reservation ID: 62 updated to confirmed by Jhon Bautista (Admin)', '::1', '2025-04-10 01:09:31'),
(638, 26, NULL, 'User', 'Submit Maintenance Request', 'Submitted maintenance request for unit 115, Issue: Heating Issue', '::1', '2025-04-10 01:12:42'),
(639, 1, NULL, 'Admin', 'Assign Maintenance Staff', 'Assigned maintenance request #103 to staff ID: 19 with priority: high', '::1', '2025-04-10 01:16:49'),
(640, 1, NULL, 'Admin', 'Update Maintenance Status', 'Updated maintenance request #103 status to: In Progress', '::1', '2025-04-10 01:16:56'),
(641, 1, NULL, 'Admin', 'Upload Contract', 'Contract uploaded for tenant ID: 52 - File: 67f6ac40ef932_52_Rental_Agreement.pdf', '::1', '2025-04-10 01:20:00'),
(642, 1, NULL, 'Admin', 'Contract Upload', 'Uploaded contract for tenant ID: 52', '::1', '2025-04-10 01:20:00'),
(643, 1, NULL, 'Admin', 'Created Invoice', 'Created invoice #INV-20250409-5638 for tenant #53', '::1', '2025-04-10 01:24:18'),
(644, 26, NULL, 'User', 'Payment Submission', 'Payment of PHP 36,000.00 submitted for review (Reference: 1001543610110)', '::1', '2025-04-10 01:39:21'),
(645, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱36,000.00 for Genalyn Palma (Unit 116)', '::1', '2025-04-10 01:42:20'),
(646, 26, NULL, 'User', 'Payment Submission', 'Payment of PHP 49,500.00 submitted for review (Reference: 1001543610110)', '::1', '2025-04-10 01:43:09'),
(647, 1, NULL, 'Admin', 'Rejected Payment', 'Rejected payment of ₱49,500.00 for Genalyn Palma (Unit 115) - Reason: Insufficient payment amount', '::1', '2025-04-10 01:43:48'),
(648, 1, NULL, 'Admin', 'Archived Tenant', 'Archived tenant David Fuentes from unit 807', '::1', '2025-04-10 01:52:44'),
(649, 26, NULL, 'User', 'Payment Submission', 'Payment of PHP 500.00 submitted for review (Reference: 1001543610110)', '::1', '2025-04-10 01:56:54'),
(650, 1, NULL, 'Admin', 'Approved Payment', 'Approved payment of ₱500.00 for Genalyn Palma (Unit 807)', '::1', '2025-04-10 01:57:16'),
(651, 1, NULL, 'Admin', 'Added New Tenant', 'Added new tenant for Unit 201', '::1', '2025-04-10 02:35:15'),
(652, 1, NULL, 'Admin', 'Added New Tenant', 'Added new tenant for Unit 201', '::1', '2025-04-10 02:37:46'),
(653, 26, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-04-10 02:59:07'),
(654, 1, NULL, 'Admin', 'Logout', 'User logged out', '::1', '2025-04-10 03:05:45'),
(655, 26, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-04-10 03:15:00'),
(656, 26, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-04-10 03:25:51'),
(657, 1, NULL, 'Admin', 'Login', 'User logged in successfully', '::1', '2025-04-12 22:46:10'),
(658, 24, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-04-17 15:52:08'),
(659, 24, NULL, 'User', 'KYC Submission', 'User submitted KYC verification', '::1', '2025-04-17 15:55:17'),
(660, 11, NULL, 'User', 'Login', 'User logged in successfully', '::1', '2025-04-21 19:56:05'),
(661, 11, NULL, 'User', 'Logout', 'User logged out', '::1', '2025-04-21 20:17:35');

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
-- Dumping data for table `generated_reports`
--

INSERT INTO `generated_reports` (`report_id`, `report_type`, `report_date`, `report_period`, `report_data`, `created_at`, `downloaded_count`, `file_path`) VALUES
(194, 'Unit Occupancy Report', '2025-03-15', '2025-01', '{\"title\":\"Unit Occupancy Report\",\"overview\":{\"report_date\":\"2025-03-15 20:55:36\",\"report_period\":\"2025-01\",\"generated_by\":\"Jhon Bautista\"},\"units\":[{\"unit_number\":\"1001\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Maintenance\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"1002\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-04-30\",\"rent_end_date\":\"2026-12-30\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"1400000.00\",\"payable_months\":\"16\",\"downpayment_amount\":\"400000.00\",\"registration_date\":\"2025-02-25 16:20:51\"},{\"unit_number\":\"1003\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"38250.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"1004\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Anora Hidson\",\"rent_start_date\":\"2025-03-06\",\"rent_end_date\":\"2027-09-30\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"2110000.00\",\"payable_months\":\"24\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-02-25 05:49:53\"},{\"unit_number\":\"1006\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"101\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Reserved\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"102\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-01-03\",\"rent_end_date\":\"2027-11-24\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":\"642500.00\",\"payable_months\":\"30\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2025-01-03 15:08:01\"},{\"unit_number\":\"105\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2024-12-29\",\"rent_end_date\":\"2026-05-20\",\"monthly_rent\":\"33750.00\",\"outstanding_balance\":\"440000.00\",\"payable_months\":\"14\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-09 13:52:32\"},{\"unit_number\":\"106\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2025-01-23\",\"rent_end_date\":\"2027-10-03\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":\"895000.00\",\"payable_months\":\"21\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-01-03 15:12:16\"},{\"unit_number\":\"107\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Anora Hidson\",\"rent_start_date\":\"2024-12-31\",\"rent_end_date\":\"2026-10-31\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":\"1250000.00\",\"payable_months\":\"19\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-31 08:43:13\"},{\"unit_number\":\"115\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Genalyn Palma\",\"rent_start_date\":\"2025-03-14\",\"rent_end_date\":\"2027-07-20\",\"monthly_rent\":\"49500.00\",\"outstanding_balance\":\"586500.00\",\"payable_months\":\"12\",\"downpayment_amount\":\"750000.00\",\"registration_date\":\"2025-03-09 04:34:49\"},{\"unit_number\":\"116\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Genalyn Palma\",\"rent_start_date\":\"2025-04-15\",\"rent_end_date\":\"2027-12-27\",\"monthly_rent\":\"36000.00\",\"outstanding_balance\":\"630000.00\",\"payable_months\":\"18\",\"downpayment_amount\":\"450000.00\",\"registration_date\":\"2025-03-09 04:45:17\"},{\"unit_number\":\"201\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"205\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2024-12-10\",\"rent_end_date\":\"2026-12-20\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":\"395000.00\",\"payable_months\":\"18\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-10 04:27:46\"},{\"unit_number\":\"206\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"207\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"208\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"81000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"301\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"112500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"401\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"72000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"501\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"605\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"112500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"701\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-02-18\",\"rent_end_date\":\"2027-09-23\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"2244000.00\",\"payable_months\":\"26\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-02-18 12:20:19\"},{\"unit_number\":\"702\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Maintenance\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"703\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"801\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2025-03-10\",\"rent_end_date\":\"2027-05-30\",\"monthly_rent\":\"78750.00\",\"outstanding_balance\":\"1468750.00\",\"payable_months\":\"20\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-03-01 15:25:07\"},{\"unit_number\":\"805\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Maintenance\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"807\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-02-28\",\"rent_end_date\":\"2028-10-26\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"3280000.00\",\"payable_months\":\"38\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-02-23 14:37:52\"},{\"unit_number\":\"807\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Genalyn Palma\",\"rent_start_date\":\"2025-03-30\",\"rent_end_date\":\"2027-10-19\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"1710000.00\",\"payable_months\":\"19\",\"downpayment_amount\":\"800000.00\",\"registration_date\":\"2025-03-09 05:04:08\"},{\"unit_number\":\"902\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"G1\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"78750.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"}],\"summary\":{\"total_units\":30,\"occupied_units\":12,\"available_units\":18,\"occupancy_rate\":40}}', '2025-03-15 12:55:36', 0, '../reports/unit_occupancy_report_2025-03-15_20-55-36.csv'),
(195, 'Property Maintenance Report', '2025-03-15', '2025-01', '{\"title\":\"Property Maintenance Report\",\"overview\":{\"report_date\":\"2025-03-15 20:55:38\",\"report_period\":\"2025-01\",\"generated_by\":\"Jhon Bautista\"},\"maintenance_requests\":[{\"id\":96,\"tenant_name\":\"David Fuentes\",\"unit\":\"105\",\"issue\":\"Heating Issue\",\"description\":\"AC is broken\",\"service_date\":\"2025-01-15\",\"submitted_on\":\"2025-01-04 05:36:53\",\"assigned_to\":\"CONRAD KANE\",\"staff_specialty\":\"Hvac Technician\",\"status\":\"Pending\"},{\"id\":95,\"tenant_name\":\"David Fuentes\",\"unit\":\"102\",\"issue\":\"Electrical Problem\",\"description\":\"Overheating\",\"service_date\":\"2025-01-09\",\"submitted_on\":\"2025-01-04 05:34:12\",\"assigned_to\":\"KJ STEVEN PALMA\",\"staff_specialty\":\"General Maintenance\",\"status\":\"In Progress\"}],\"summary\":{\"total_requests\":2,\"pending_requests\":1,\"in_progress_requests\":1,\"completed_requests\":0,\"completion_rate\":0,\"issue_categories\":{\"Heating Issue\":1,\"Electrical Problem\":1}}}', '2025-03-15 12:55:38', 0, '../reports/property_maintenance_report_2025-03-15_20-55-38.csv'),
(196, 'Property Maintenance Report', '2025-03-20', '2025-03', '{\"title\":\"Property Maintenance Report\",\"overview\":{\"report_date\":\"2025-03-20 14:11:09\",\"report_period\":\"2025-03\",\"generated_by\":\"Jhon Bautista\"},\"maintenance_requests\":[{\"id\":99,\"tenant_name\":\"David Fuentes\",\"unit\":\"1002\",\"issue\":\"Other\",\"description\":\"Broken AC\",\"service_date\":\"2025-03-23\",\"submitted_on\":\"2025-03-20 05:40:49\",\"assigned_to\":\"CONRAD KANE\",\"staff_specialty\":\"Hvac Technician\",\"status\":\"Completed\"}],\"summary\":{\"total_requests\":1,\"pending_requests\":0,\"in_progress_requests\":0,\"completed_requests\":1,\"completion_rate\":100,\"issue_categories\":{\"Other\":1}}}', '2025-03-20 06:11:09', 0, '../reports/property_maintenance_report_2025-03-20_14-11-09.csv'),
(197, 'Unit Occupancy Report', '2025-03-20', '2025-01', '{\"title\":\"Unit Occupancy Report\",\"overview\":{\"report_date\":\"2025-03-20 17:47:14\",\"report_period\":\"2025-01\",\"generated_by\":\"Jhon Bautista\"},\"units\":[{\"unit_number\":\"1001\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Maintenance\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"1002\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-04-30\",\"rent_end_date\":\"2026-12-30\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"1287500.00\",\"payable_months\":\"15\",\"downpayment_amount\":\"400000.00\",\"registration_date\":\"2025-02-25 16:20:51\"},{\"unit_number\":\"1003\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"38250.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"1004\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Anora Hidson\",\"rent_start_date\":\"2025-03-06\",\"rent_end_date\":\"2027-09-30\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"2110000.00\",\"payable_months\":\"24\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-02-25 05:49:53\"},{\"unit_number\":\"1006\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"101\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Reserved\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"102\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-01-03\",\"rent_end_date\":\"2027-11-24\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":\"642500.00\",\"payable_months\":\"30\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2025-01-03 15:08:01\"},{\"unit_number\":\"103\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Reserved\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"105\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2024-12-29\",\"rent_end_date\":\"2026-05-20\",\"monthly_rent\":\"33750.00\",\"outstanding_balance\":\"406250.00\",\"payable_months\":\"13\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-09 13:52:32\"},{\"unit_number\":\"106\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2025-01-23\",\"rent_end_date\":\"2027-10-03\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":\"895000.00\",\"payable_months\":\"21\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-01-03 15:12:16\"},{\"unit_number\":\"107\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Anora Hidson\",\"rent_start_date\":\"2024-12-31\",\"rent_end_date\":\"2026-10-31\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":\"1250000.00\",\"payable_months\":\"19\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-31 08:43:13\"},{\"unit_number\":\"115\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Genalyn Palma\",\"rent_start_date\":\"2025-03-14\",\"rent_end_date\":\"2027-07-20\",\"monthly_rent\":\"49500.00\",\"outstanding_balance\":\"586500.00\",\"payable_months\":\"12\",\"downpayment_amount\":\"750000.00\",\"registration_date\":\"2025-03-09 04:34:49\"},{\"unit_number\":\"116\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Genalyn Palma\",\"rent_start_date\":\"2025-04-15\",\"rent_end_date\":\"2027-12-27\",\"monthly_rent\":\"36000.00\",\"outstanding_balance\":\"630000.00\",\"payable_months\":\"18\",\"downpayment_amount\":\"450000.00\",\"registration_date\":\"2025-03-09 04:45:17\"},{\"unit_number\":\"201\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Reserved\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"205\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"PJ ETESAM\",\"rent_start_date\":\"2025-03-30\",\"rent_end_date\":\"2027-09-30\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":\"157500.00\",\"payable_months\":\"7\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-03-20 09:30:48\"},{\"unit_number\":\"205\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2024-12-10\",\"rent_end_date\":\"2026-12-20\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":\"395000.00\",\"payable_months\":\"18\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-10 04:27:46\"},{\"unit_number\":\"206\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"207\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"208\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"81000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"301\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"112500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"302\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"81000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"303\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"54000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"304\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"58500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"401\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"72000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"402\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"63000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"403\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"501\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"605\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"112500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"701\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-02-18\",\"rent_end_date\":\"2027-09-23\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"2244000.00\",\"payable_months\":\"26\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-02-18 12:20:19\"},{\"unit_number\":\"702\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Maintenance\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"703\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"801\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2025-03-10\",\"rent_end_date\":\"2027-05-30\",\"monthly_rent\":\"78750.00\",\"outstanding_balance\":\"1468750.00\",\"payable_months\":\"20\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-03-01 15:25:07\"},{\"unit_number\":\"805\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Maintenance\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"807\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-02-28\",\"rent_end_date\":\"2028-10-26\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"3190000.00\",\"payable_months\":\"36\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-02-23 14:37:52\"},{\"unit_number\":\"807\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Genalyn Palma\",\"rent_start_date\":\"2025-03-30\",\"rent_end_date\":\"2027-10-19\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"1710000.00\",\"payable_months\":\"19\",\"downpayment_amount\":\"800000.00\",\"registration_date\":\"2025-03-09 05:04:08\"},{\"unit_number\":\"902\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"G1\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"78750.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"}],\"summary\":{\"total_units\":37,\"occupied_units\":15,\"available_units\":22,\"occupancy_rate\":40.54}}', '2025-03-20 09:47:14', 0, '../reports/unit_occupancy_report_2025-03-20_17-47-14.csv'),
(198, 'Unit Occupancy Report', '2025-03-24', '2025-01', '{\"title\":\"Unit Occupancy Report\",\"overview\":{\"report_date\":\"2025-03-24 18:42:31\",\"report_period\":\"2025-01\",\"generated_by\":\"PJ ETESAM\"},\"units\":[{\"unit_number\":\"1001\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Maintenance\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"1002\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-04-30\",\"rent_end_date\":\"2026-12-30\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"1287500.00\",\"payable_months\":\"15\",\"downpayment_amount\":\"400000.00\",\"registration_date\":\"2025-02-25 16:20:51\"},{\"unit_number\":\"1003\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"38250.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"1004\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Anora Hidson\",\"rent_start_date\":\"2025-03-06\",\"rent_end_date\":\"2027-09-30\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"2110000.00\",\"payable_months\":\"24\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-02-25 05:49:53\"},{\"unit_number\":\"1006\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"101\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Reserved\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"102\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-01-03\",\"rent_end_date\":\"2027-11-24\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":\"642500.00\",\"payable_months\":\"30\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2025-01-03 15:08:01\"},{\"unit_number\":\"103\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Reserved\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"105\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2024-12-29\",\"rent_end_date\":\"2026-05-20\",\"monthly_rent\":\"33750.00\",\"outstanding_balance\":\"406250.00\",\"payable_months\":\"13\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-09 13:52:32\"},{\"unit_number\":\"106\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2025-01-23\",\"rent_end_date\":\"2027-10-03\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":\"895000.00\",\"payable_months\":\"21\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-01-03 15:12:16\"},{\"unit_number\":\"107\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Anora Hidson\",\"rent_start_date\":\"2024-12-31\",\"rent_end_date\":\"2026-10-31\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":\"1250000.00\",\"payable_months\":\"19\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-31 08:43:13\"},{\"unit_number\":\"115\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Genalyn Palma\",\"rent_start_date\":\"2025-03-14\",\"rent_end_date\":\"2027-07-20\",\"monthly_rent\":\"49500.00\",\"outstanding_balance\":\"586500.00\",\"payable_months\":\"12\",\"downpayment_amount\":\"750000.00\",\"registration_date\":\"2025-03-09 04:34:49\"},{\"unit_number\":\"116\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Genalyn Palma\",\"rent_start_date\":\"2025-04-15\",\"rent_end_date\":\"2027-12-27\",\"monthly_rent\":\"36000.00\",\"outstanding_balance\":\"630000.00\",\"payable_months\":\"18\",\"downpayment_amount\":\"450000.00\",\"registration_date\":\"2025-03-09 04:45:17\"},{\"unit_number\":\"201\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Reserved\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"205\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2024-12-10\",\"rent_end_date\":\"2026-12-20\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":\"395000.00\",\"payable_months\":\"18\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-10 04:27:46\"},{\"unit_number\":\"205\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"PJ ETESAM\",\"rent_start_date\":\"2025-03-30\",\"rent_end_date\":\"2027-09-30\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":\"135000.00\",\"payable_months\":\"6\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-03-20 09:30:48\"},{\"unit_number\":\"206\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"207\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"208\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"81000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"209\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Reserved\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"301\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"112500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"302\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Paolo James Etesam\",\"rent_start_date\":\"2025-03-27\",\"rent_end_date\":\"2027-12-30\",\"monthly_rent\":\"81000.00\",\"outstanding_balance\":\"2106000.00\",\"payable_months\":\"26\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-03-24 09:39:23\"},{\"unit_number\":\"303\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Renante Colaste\",\"rent_start_date\":\"2025-03-28\",\"rent_end_date\":\"2026-12-30\",\"monthly_rent\":\"54000.00\",\"outstanding_balance\":\"864000.00\",\"payable_months\":\"16\",\"downpayment_amount\":\"300000.00\",\"registration_date\":\"2025-03-21 16:04:22\"},{\"unit_number\":\"304\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"58500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"401\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"72000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"402\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"63000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"403\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"501\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"605\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"112500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"701\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-02-18\",\"rent_end_date\":\"2027-09-23\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"2244000.00\",\"payable_months\":\"26\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-02-18 12:20:19\"},{\"unit_number\":\"702\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Maintenance\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"703\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Reserved\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"801\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2025-03-10\",\"rent_end_date\":\"2027-05-30\",\"monthly_rent\":\"78750.00\",\"outstanding_balance\":\"1468750.00\",\"payable_months\":\"20\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-03-01 15:25:07\"},{\"unit_number\":\"805\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Maintenance\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"807\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-02-28\",\"rent_end_date\":\"2028-10-26\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"3190000.00\",\"payable_months\":\"36\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-02-23 14:37:52\"},{\"unit_number\":\"807\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Genalyn Palma\",\"rent_start_date\":\"2025-03-30\",\"rent_end_date\":\"2027-10-19\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":\"1710000.00\",\"payable_months\":\"19\",\"downpayment_amount\":\"800000.00\",\"registration_date\":\"2025-03-09 05:04:08\"},{\"unit_number\":\"902\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"G1\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"78750.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"}],\"summary\":{\"total_units\":38,\"occupied_units\":16,\"available_units\":22,\"occupancy_rate\":42.11}}', '2025-03-24 10:42:31', 0, '../reports/unit_occupancy_report_2025-03-24_18-42-31.csv'),
(199, 'Property Maintenance Report', '2025-03-24', '2025-01', '{\"title\":\"Property Maintenance Report\",\"overview\":{\"report_date\":\"2025-03-24 18:43:19\",\"report_period\":\"2025-01\",\"generated_by\":\"PJ ETESAM\"},\"maintenance_requests\":[{\"id\":96,\"tenant_name\":\"David Fuentes\",\"unit\":\"105\",\"issue\":\"Heating Issue\",\"description\":\"AC is broken\",\"service_date\":\"2025-01-15\",\"submitted_on\":\"2025-01-04 05:36:53\",\"assigned_to\":\"CONRAD KANE\",\"staff_specialty\":\"Hvac Technician\",\"status\":\"Completed\"},{\"id\":95,\"tenant_name\":\"David Fuentes\",\"unit\":\"102\",\"issue\":\"Electrical Problem\",\"description\":\"Overheating\",\"service_date\":\"2025-01-09\",\"submitted_on\":\"2025-01-04 05:34:12\",\"assigned_to\":\"KJ STEVEN PALMA\",\"staff_specialty\":\"General Maintenance\",\"status\":\"In Progress\"}],\"summary\":{\"total_requests\":2,\"pending_requests\":0,\"in_progress_requests\":1,\"completed_requests\":1,\"completion_rate\":50,\"issue_categories\":{\"Heating Issue\":1,\"Electrical Problem\":1}}}', '2025-03-24 10:43:19', 0, '../reports/property_maintenance_report_2025-03-24_18-43-19.csv');

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
  `status` enum('paid','unpaid','overdue') DEFAULT 'unpaid',
  `invoice_type` enum('rent','utility','other') NOT NULL DEFAULT 'rent',
  `description` text DEFAULT NULL,
  `email_sent` tinyint(1) DEFAULT 0,
  `email_sent_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `tenant_id`, `invoice_number`, `amount`, `issue_date`, `due_date`, `status`, `invoice_type`, `description`, `email_sent`, `email_sent_date`, `created_at`, `updated_at`) VALUES
(1, 42, 'INV-20250304-1713', 67500.00, '2025-03-15', '2025-03-30', 'paid', 'rent', 'test', 1, '2025-03-04 19:22:27', '2025-03-04 11:11:13', '2025-03-05 16:15:27'),
(2, 42, 'INV-20250304-3030', 2000.00, '2025-03-04', '2025-03-19', 'paid', 'utility', 'Water and Electric Bill', 1, '2025-03-04 19:24:09', '2025-03-04 11:23:20', '2025-03-05 16:15:41'),
(3, 44, 'INV-20250304-9270', 45500.00, '2025-03-15', '2025-03-30', 'paid', 'rent', 'Monthly rent and additional expenses', 1, '2025-03-13 23:26:56', '2025-03-04 11:42:14', '2025-03-13 15:26:56'),
(4, 43, 'INV-20250316-7617', 28000.00, '2025-03-20', '2025-04-30', 'paid', 'rent', '', 1, '2025-03-16 16:17:23', '2025-03-16 16:16:52', '2025-03-21 16:19:15'),
(5, 50, 'INV-20250320-6629', 1500.00, '2025-03-24', '2025-03-30', 'paid', 'other', 'Maintenance Fee', 1, '2025-03-20 06:06:17', '2025-03-20 06:05:50', '2025-03-21 17:32:44'),
(6, 56, 'INV-20250320-1030', 23722.00, '2025-04-20', '2025-05-30', 'unpaid', 'rent', '', 1, '2025-03-20 09:44:46', '2025-03-20 09:44:22', '2025-03-21 17:32:35'),
(10, 42, 'INV-20250321-8948', 67500.00, '2025-03-05', '2025-03-22', 'overdue', 'rent', '', 0, NULL, '2025-03-21 17:12:26', '2025-03-21 17:32:38'),
(11, 58, 'INV-20250324-4421', 83000.00, '2025-03-24', '2025-03-30', 'overdue', 'rent', '', 1, '2025-03-24 10:35:54', '2025-03-24 10:32:13', '2025-04-01 08:30:18'),
(12, 53, 'INV-20250409-5638', 36000.00, '2025-04-14', '2025-04-30', 'paid', 'rent', '', 1, '2025-04-10 01:33:47', '2025-04-09 17:24:18', '2025-04-09 17:34:03');

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

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_name`, `amount`) VALUES
(1, 1, 'Monthly Rent', 67500.00),
(2, 2, 'PVC Pipe', 500.00),
(3, 3, 'Maintenance Charges', 500.00),
(4, 3, 'Monthly Rent', 45000.00),
(5, 4, 'Maintenance Charges', 500.00),
(6, 4, 'Utilities (Water and Electric Bill)', 5000.00),
(7, 4, 'Monthly Rent', 22500.00),
(8, 5, 'Other Charges', 1500.00),
(9, 6, 'maintenance', 1222.00),
(10, 6, 'Monthly Rent', 22500.00),
(14, 10, 'Monthly Rent', 67500.00),
(15, 11, 'Utilities ( Water and Electric)', 1500.00),
(16, 11, 'Maintenance Charge', 500.00),
(17, 11, 'Monthly Rent', 81000.00),
(18, 12, 'Monthly Rent', 36000.00);

-- --------------------------------------------------------

--
-- Table structure for table `kyc_verification`
--

CREATE TABLE `kyc_verification` (
  `kyc_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` varchar(20) NOT NULL,
  `nationality` varchar(100) NOT NULL,
  `other_nationality` varchar(100) DEFAULT NULL,
  `civil_status` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_number` varchar(20) NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `id_type` varchar(50) NOT NULL,
  `other_id_type` varchar(100) DEFAULT NULL,
  `id_number` varchar(100) NOT NULL,
  `id_front_path` varchar(255) NOT NULL,
  `id_back_path` varchar(255) NOT NULL,
  `funds_source` varchar(50) NOT NULL,
  `other_funds_source` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) NOT NULL,
  `employer` varchar(100) DEFAULT NULL,
  `verification_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_date` timestamp NULL DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kyc_verification`
--

INSERT INTO `kyc_verification` (`kyc_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `gender`, `nationality`, `other_nationality`, `civil_status`, `email`, `mobile_number`, `street_address`, `barangay`, `city`, `province`, `zip_code`, `id_type`, `other_id_type`, `id_number`, `id_front_path`, `id_back_path`, `funds_source`, `other_funds_source`, `occupation`, `employer`, `verification_status`, `admin_remarks`, `submission_date`, `verification_date`, `archived`) VALUES
(3, 11, 'David', 'Colaste', 'Fuentes', '2003-09-12', 'male', 'Filipino', NULL, 'single', 'kjstevengaming@gmail.com', '09510974884', '672 Centurion', 'Post Proper Southside', 'City of Makati', 'Metro Manila (NCR)', '1200', 'philsys', NULL, '2323-4323-2531-2315', 'uploads/kyc/user_11_front_1743489391_national_id_front.jpg', 'uploads/kyc/user_11_back_1743489391_national_id_back.jpg', 'salary', NULL, 'IT Specialist', 'Facebook', 'approved', 'Approved by Jhon Bautista', '2025-04-01 06:36:31', '2025-04-01 07:31:46', 0),
(4, 26, 'Genalyn', 'Colaste', 'Palma', '1978-09-17', 'female', 'Filipino', NULL, 'married', 'palmagenalyn17@gmail.com', '09512975385', '672 Centurion', 'Post Proper Southside', 'City of Makati', 'Metro Manila (NCR)', '1200', 'philsys', NULL, '2323-4323-2531-2315', 'uploads/kyc/user_26_front_1744217163_national_id_front.jpg', 'uploads/kyc/user_26_back_1744217163_national_id_back.jpg', 'salary', NULL, 'Network Engineer', 'Cisco', 'approved', 'Approved by Jhon Bautista', '2025-04-09 16:46:03', '2025-04-09 17:02:47', 0),
(5, 24, 'Anora', 'Fley', 'Hidson', '1999-02-27', 'female', 'other', 'American', 'single', 'freshplayz18@gmail.com', '09210954392', 'BLK 20 LOT 8 MILKWEED', 'Rizal', 'City of Makati', 'Metro Manila (NCR)', '1683', 'philsys', NULL, '5443-4231-3214-1132', 'uploads/kyc/user_24_front_1744876517_national_id_front.jpg', 'uploads/kyc/user_24_back_1744876517_national_id_back.jpg', 'salary', NULL, 'Social Media Manager', 'Tiktok', 'pending', NULL, '2025-04-17 07:55:17', NULL, 0);

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
(96, 11, '105', 'Heating Issue', 'AC is broken', 'Change cables and fix motor problems', 400.00, '2025-03-16 13:30:00', '2025-01-15', 'Completed', 'maintenance_report_96_20250316_153246.pdf', 'uploads/maintenance_requests/maintenance_6778c8f5505524.10953351.png', '2025-01-04 05:36:53', '2025-04-02 14:00:22', 15, 'high', 1),
(97, 11, '701', 'Leaking Faucet', 'Broken Faucet', 'Change Faucet', 500.00, '2025-03-18 20:23:00', '2025-02-28', 'Completed', 'maintenance_report_97_20250218_132338.pdf', 'uploads/maintenance_requests/maintenance_67b47b30da2a95.63959313.jpg', '2025-02-18 12:21:04', '2025-02-25 15:06:21', 15, 'medium', 0),
(98, 11, '807', 'Leaking Faucet', 'The faucet is leaking a water', NULL, NULL, NULL, '2025-02-28', 'In Progress', NULL, 'uploads/maintenance_requests/maintenance_67bddd702336d1.04366914.jpg', '2025-02-25 15:10:40', '2025-03-16 16:24:48', 15, 'medium', 0),
(99, 11, '1002', 'Other', 'Broken AC', 'Change AC motor', 1500.00, '2025-03-24 14:03:00', '2025-03-23', 'Completed', 'maintenance_report_99_20250320_060324.pdf', 'uploads/maintenance_requests/maintenance_67dbaa6199ac13.25942518.jpg', '2025-03-20 05:40:49', '2025-04-02 14:00:11', 15, 'high', 1),
(100, 29, '205', 'Leaking Faucet', 'word word word ', 'asdasdasdasd', 22500.00, '2025-03-27 17:51:00', '2025-03-22', 'Completed', 'maintenance_report_100_20250320_095128.pdf', 'uploads/maintenance_requests/maintenance_67dbe09eb9a544.86421677.jpg', '2025-03-20 09:32:14', '2025-04-02 14:00:08', 15, 'medium', 1),
(101, 33, '302', 'Leaking Faucet', 'word word word ', 'change the faucet', 500.00, '2025-03-26 17:50:00', '2025-03-26', 'Completed', 'maintenance_report_101_20250324_095059.pdf', 'uploads/maintenance_requests/maintenance_67e1289b9c9cd5.46428865.jpg', '2025-03-24 09:40:43', '2025-04-02 14:00:05', 22, 'high', 1),
(102, 11, '701', 'Heating Issue', 'AC is not working', NULL, NULL, NULL, '2025-04-11', 'Pending', NULL, 'uploads/maintenance_requests/maintenance_67f6a1f3dfc655.05131062.jpg', '2025-04-09 16:36:03', '2025-04-09 16:36:03', NULL, 'medium', 0),
(103, 26, '115', 'Heating Issue', 'AC is not working', NULL, NULL, NULL, '2025-04-14', 'In Progress', NULL, 'uploads/maintenance_requests/maintenance_67f6aa8a38d341.69615667.jpg', '2025-04-09 17:12:42', '2025-04-09 17:16:56', 19, 'high', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(50) DEFAULT 'general',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `notification_type`, `is_read`, `created_at`) VALUES
(1, 11, 'Your account password was changed successfully. If you didn\'t make this change, please contact support immediately.', 'security', 1, '2025-04-09 16:04:39'),
(2, 11, 'You have successfully reserved Unit 206 for viewing on April 14, 2025 at 1:40 PM. Please wait for confirmation.', 'reservation', 1, '2025-04-09 16:21:43'),
(23, 1, 'New reservation request for Unit 207 from . Scheduled for April 19, 2025 at 1:24 PM', 'admin_reservation', 0, '2025-04-09 16:23:38'),
(24, 11, 'You have successfully reserved Unit 206 for viewing on April 14, 2025 at 3:32 PM. Please wait for confirmation.', 'reservation', 0, '2025-04-09 16:32:18'),
(25, 1, 'New reservation request for Unit 206 from David Fuentes scheduled for April 14, 2025 at 3:32 PM', 'admin_reservation', 0, '2025-04-09 16:32:18'),
(26, 11, 'Your maintenance request for Unit 701 has been submitted successfully. Our team will review it shortly.', 'maintenance', 0, '2025-04-09 16:36:03'),
(27, 1, 'New maintenance request from Unit 701 - Issue: Heating Issue', 'admin_maintenance', 0, '2025-04-09 16:36:03'),
(28, 11, 'You have downloaded the contract for Unit 102. Please keep it in a safe location.', 'contract', 0, '2025-04-09 16:37:59'),
(29, 1, 'Tenant has downloaded the contract for Unit 102.', 'admin_contract', 0, '2025-04-09 16:37:59'),
(30, 11, 'You have downloaded the contract for Unit 701. Please keep it in a safe location.', 'contract', 0, '2025-04-09 16:38:17'),
(31, 1, 'Tenant has downloaded the contract for Unit 701.', 'admin_contract', 0, '2025-04-09 16:38:17'),
(32, 11, 'You have downloaded the contract for Unit 701. Please keep it in a safe location.', 'contract', 0, '2025-04-09 16:39:38'),
(33, 1, 'Tenant has downloaded the contract for Unit 701.', 'admin_contract', 0, '2025-04-09 16:39:38'),
(34, 11, 'Your payment of PHP 22,500.00 has been submitted and is pending review.', 'payment', 0, '2025-04-09 16:43:01'),
(35, 1, 'New payment of PHP 22,500.00 submitted for Unit 4 (Reference: 1001543610110)', 'admin_payment', 0, '2025-04-09 16:43:01'),
(36, 26, 'Your KYC verification request has been submitted successfully. We will review your information shortly.', 'kyc', 0, '2025-04-09 16:46:03'),
(37, 1, 'New KYC verification request from Genalyn Palma', 'kyc_admin', 0, '2025-04-09 16:46:03'),
(38, 1, 'New unit added: Unit 707 (Warehouse) has been successfully added to the system.', 'property', 0, '2025-04-09 16:54:24'),
(39, 11, 'New unit available: Unit 707 (Warehouse) is now available for rent.', 'new_unit', 1, '2025-04-09 16:54:24'),
(40, 1, 'New unit added: Unit 609 (Office) has been successfully added to the system.', 'property', 0, '2025-04-09 16:55:34'),
(41, 26, 'Your KYC verification has been approved. You can now access all features.', 'kyc_approved', 0, '2025-04-09 17:02:47'),
(42, 1, 'KYC verification #4 has been approved successfully.', 'admin_kyc', 0, '2025-04-09 17:02:47'),
(43, 26, 'You have successfully reserved Unit 609 for viewing on April 14, 2025 at 10:40 AM. Please wait for confirmation.', 'reservation', 0, '2025-04-09 17:03:54'),
(44, 1, 'New reservation request for Unit 609 from Genalyn Palma scheduled for April 14, 2025 at 10:40 AM', 'admin_reservation', 0, '2025-04-09 17:03:54'),
(45, 26, 'Your reservation for Unit 609 has been confirmed.', 'reservation_confirmed', 0, '2025-04-09 17:09:34'),
(46, 1, 'Reservation #62 for Unit 609 has been confirmed.', 'admin_reservation_confirmed', 0, '2025-04-09 17:09:34'),
(47, 26, 'Your maintenance request for Unit 115 has been submitted successfully. Our team will review it shortly.', 'maintenance', 0, '2025-04-09 17:12:42'),
(48, 1, 'New maintenance request from Unit 115 - Issue: Heating Issue', 'admin_maintenance', 0, '2025-04-09 17:12:42'),
(49, 26, 'Your maintenance request for Unit 115 (Heating Issue) has been marked as In Progress.', 'maintenance', 0, '2025-04-09 17:16:56'),
(50, 1, 'Maintenance request #103 for Unit 115 updated to In Progress.', 'admin_maintenance', 0, '2025-04-09 17:16:56'),
(51, 26, 'Your rental contract for Unit 115 has been uploaded.', 'contract', 0, '2025-04-09 17:20:00'),
(52, 1, 'Contract uploaded for Unit 115', 'admin_contract', 0, '2025-04-09 17:20:00'),
(53, 26, 'A new invoice #INV-20250409-5638 has been sent to your email.', 'invoice_sent', 0, '2025-04-09 17:33:47'),
(54, 1, 'Invoice #INV-20250409-5638 was sent to Genalyn Palma.', 'admin_invoice', 0, '2025-04-09 17:33:47'),
(55, 26, 'Your invoice #INV-20250409-5638 for Unit 116 has been marked as paid.', 'invoice_paid', 0, '2025-04-09 17:34:03'),
(56, 1, 'Invoice #INV-20250409-5638 for Genalyn Palma has been marked as paid.', 'admin_invoice', 0, '2025-04-09 17:34:03'),
(57, 26, 'Your payment of PHP 36,000.00 has been submitted and is pending review.', 'payment', 0, '2025-04-09 17:39:21'),
(58, 1, 'New payment of PHP 36,000.00 submitted for Unit 8 (Reference: 1001543610110)', 'admin_payment', 0, '2025-04-09 17:39:21'),
(59, 26, 'Your payment of PHP 36,000.00 has been approved.', 'payment_approved', 0, '2025-04-09 17:42:20'),
(60, 1, 'Payment of PHP 36,000.00 from Genalyn Palma (Unit 116) has been approved', 'admin_payment', 0, '2025-04-09 17:42:20'),
(61, 26, 'Your payment of PHP 49,500.00 has been submitted and is pending review.', 'payment', 0, '2025-04-09 17:43:09'),
(62, 1, 'New payment of PHP 49,500.00 submitted for Unit 7 (Reference: 1001543610110)', 'admin_payment', 0, '2025-04-09 17:43:09'),
(63, 26, 'Your payment of PHP 49,500.00 has been rejected. Reason: Insufficient payment amount', 'payment_rejected', 0, '2025-04-09 17:43:51'),
(64, 1, 'Payment of PHP 49,500.00 from Genalyn Palma (Unit 115) has been rejected', 'admin_payment', 0, '2025-04-09 17:43:51'),
(65, 26, 'Your payment of PHP 500.00 has been submitted and is pending review.', 'payment', 0, '2025-04-09 17:56:54'),
(66, 1, 'New payment of PHP 500.00 submitted for Unit 20 (Reference: 1001543610110)', 'admin_payment', 1, '2025-04-09 17:56:54'),
(67, 26, 'Your payment of PHP 500.00 has been approved.', 'payment_approved', 0, '2025-04-09 17:57:12'),
(68, 1, 'Payment of PHP 500.00 from Genalyn Palma (Unit 807) has been approved', 'admin_payment', 1, '2025-04-09 17:57:12'),
(69, 26, 'Your payment of PHP 1,500.00 has been recorded successfully.', 'payment_recorded', 0, '2025-04-09 18:00:20'),
(70, 1, 'Manual payment of PHP 1,500.00 recorded for Genalyn Palma (Unit 116).', 'admin_payment', 1, '2025-04-09 18:00:20'),
(71, 24, 'Your KYC verification request has been submitted successfully. We will review your information shortly.', 'kyc', 1, '2025-04-17 07:55:17'),
(72, 1, 'New KYC verification request from Anora Hidson', 'kyc_admin', 0, '2025-04-17 07:55:17');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `reference_number` varchar(100) NOT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `gcash_number` varchar(20) NOT NULL,
  `status` enum('Pending','Received','Rejected') NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `processed_by` varchar(255) NOT NULL,
  `payment_type` varchar(50) NOT NULL,
  `bill_item` varchar(100) NOT NULL,
  `bill_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `tenant_id`, `amount`, `payment_date`, `reference_number`, `receipt_image`, `gcash_number`, `status`, `notes`, `created_at`, `updated_at`, `processed_by`, `payment_type`, `bill_item`, `bill_description`) VALUES
(16, 45, 46000.00, '2025-03-04 22:01:47', '2012120513868', 'uploads/receipts/receipt_67c707cb7c38e_1741096907.jpg', '09510974884', 'Received', NULL, '2025-03-04 22:01:47', '2025-03-06 01:11:16', '', 'rent', '', NULL),
(17, 44, 45000.00, '2025-03-05 00:00:00', '', NULL, '', 'Received', '', '2025-03-05 23:18:27', '2025-03-06 01:11:11', '', 'rent', '', NULL),
(18, 51, 78750.00, '2025-03-05 00:00:00', '2012120513868', 'uploads/receipts/receipt_1741189292_4485.jpg', '09210929331', 'Received', '', '2025-03-05 23:41:32', '2025-03-06 01:11:07', '', 'rent', '', NULL),
(19, 47, 90000.00, '2025-03-05 00:00:00', '', NULL, '', 'Received', '', '2025-03-05 23:59:45', '2025-03-06 01:10:57', '', 'rent', '', NULL),
(20, 42, 1000.00, '2025-03-05 00:00:00', '', NULL, '', 'Received', '', '2025-03-06 00:27:48', '2025-03-06 00:27:48', '', 'other', 'Maintenance Charges', 'Maintenance fee for fixing broken AC'),
(21, 42, 5000.00, '2025-03-05 00:00:00', '2012120513868', 'uploads/receipts/receipt_1741194767_5948.jpg', '09310973342', 'Received', '', '2025-03-06 01:12:47', '2025-03-06 01:12:47', '', 'other', 'Utilities', 'Water and Electric Bill'),
(22, 21, 22500.00, '2025-03-06 01:21:26', '2012120513868', 'uploads/receipts/receipt_67c888167fc27_1741195286.jpg', '09610974334', 'Received', NULL, '2025-03-06 01:21:26', '2025-03-06 02:16:14', 'Jhon Bautista', 'rent', '', NULL),
(23, 43, 9999.98, '2025-03-09 00:00:00', '2012120513868', 'uploads/receipts/receipt_1741493393_9274.jpg', '09510974884', 'Received', '', '2025-03-09 12:09:53', '2025-03-09 12:09:53', '', 'other', 'Maintenance Charges', ''),
(24, 43, 5000.00, '2025-03-09 00:00:00', '', 'uploads/receipts/receipt_1741493497_9345.jpg', '', 'Received', '', '2025-03-09 12:11:37', '2025-03-09 12:11:37', '', 'other', 'Utilities', ''),
(25, 43, 22500.00, '2025-03-09 00:00:00', '', '', '', 'Received', '', '2025-03-09 12:23:22', '2025-03-09 12:23:22', '1', 'rent', '', ''),
(26, 55, 90000.00, '2025-03-09 00:00:00', '2012120513868', 'uploads/receipts/receipt_1741496739_8566.jpg', '09510974884', 'Received', '', '2025-03-09 13:05:39', '2025-03-09 13:05:39', '1', 'rent', '', ''),
(27, 53, 36000.00, '2025-03-09 00:00:00', '', '', '', 'Received', '', '2025-03-09 13:15:30', '2025-03-09 13:15:30', '1', 'rent', '', ''),
(28, 55, 90000.00, '2025-03-09 00:00:00', '', '', '', 'Received', '', '2025-03-09 13:16:44', '2025-03-09 13:16:44', '1', 'rent', '', ''),
(29, 55, 90000.00, '2025-04-30 00:00:00', '', '', '', 'Received', '', '2025-03-09 13:21:01', '2025-03-09 13:21:01', '1', 'rent', '', ''),
(30, 53, 36000.00, '2025-03-09 00:00:00', '', '', '', 'Received', '', '2025-03-09 13:32:59', '2025-03-09 13:32:59', '1', 'rent', '', ''),
(31, 52, 49500.00, '2025-03-09 13:34:35', '2012120513868', 'uploads/receipts/receipt_67cd286b96f91_1741498475.jpg', '09510974884', 'Received', NULL, '2025-03-09 13:34:35', '2025-03-09 13:34:53', 'Jhon Bautista', '', '', NULL),
(32, 52, 1000.00, '2025-03-09 13:44:41', '2012120513868', 'uploads/receipts/receipt_67cd2ac954129_1741499081.jpg', '09510974884', 'Received', NULL, '2025-03-09 13:44:41', '2025-03-09 13:45:13', 'Jhon Bautista', '', '', NULL),
(33, 53, 1500.00, '2025-03-09 13:54:27', '2012120513868', 'uploads/receipts/receipt_67cd2d13892c3_1741499667.jpg', '09510974884', 'Received', NULL, '2025-03-09 13:54:27', '2025-03-09 13:54:58', 'Jhon Bautista', 'maintenance', 'AC Repair', NULL),
(34, 55, 5000.00, '2025-03-09 14:14:57', '2012120513868', 'uploads/receipts/receipt_67cd31e1ab094_1741500897.jpg', '09510974884', 'Rejected', NULL, '2025-03-09 14:14:57', '2025-03-12 23:06:54', 'Jhon Bautista', 'utilities', 'Electric Bill', NULL),
(35, 55, 800.00, '2025-03-09 14:17:50', '2012120513868', 'uploads/receipts/receipt_67cd328eeaab5_1741501070.jpg', '09510974884', 'Rejected', NULL, '2025-03-09 14:17:50', '2025-03-12 23:06:57', 'Jhon Bautista', 'utilities', 'Water Bil', NULL),
(36, 21, 22500.00, '2025-05-30 00:00:00', '', '', '', 'Received', '', '2025-03-12 21:59:42', '2025-03-12 21:59:42', '1', 'rent', '', ''),
(37, 42, 67500.00, '2025-03-12 00:00:00', '', '', '', 'Received', '', '2025-03-12 22:48:58', '2025-03-12 22:48:58', '1', 'rent', '', ''),
(38, 53, 36000.00, '2025-03-12 22:56:37', '201923210312', 'uploads/receipts/receipt_67d1a0a55127c_1741791397.jpg', '09510974884', 'Rejected', ' [Payment rejected by admin]', '2025-03-12 22:56:37', '2025-03-12 23:34:40', 'Jhon Bautista', 'rent', 'Monthly Rent', NULL),
(39, 48, 90000.00, '2025-05-30 00:00:00', '2012120513868', 'uploads/receipts/receipt_1741793309_4456.jpg', '09510974884', 'Received', '', '2025-03-12 23:28:29', '2025-03-12 23:28:29', '1', 'rent', '', ''),
(40, 48, 10000.00, '2025-03-25 00:00:00', '', '', '', 'Received', '', '2025-03-12 23:29:01', '2025-03-12 23:29:01', '1', 'other', 'Utilities', 'Water Bill'),
(41, 48, 5000.00, '2025-03-15 00:00:00', '2012120513868', 'uploads/receipts/receipt_1741793380_6499.jpg', '09510974884', 'Received', '', '2025-03-12 23:29:40', '2025-03-12 23:29:40', '1', 'other', 'Utilities', 'Electric Bill'),
(42, 42, 67500.00, '2025-05-30 00:00:00', '2012120513868', 'uploads/receipts/receipt_1741793523_4348.jpg', '09510974884', 'Received', '', '2025-03-12 23:32:03', '2025-03-12 23:32:03', '1', 'rent', '', ''),
(43, 52, 49500.00, '2025-04-30 00:00:00', '2012120513868', 'uploads/receipts/receipt_1741794220_5481.jpg', '09510974884', 'Received', '', '2025-03-12 23:43:40', '2025-03-12 23:43:40', '1', 'rent', '', ''),
(44, 55, 500.00, '2025-03-12 23:44:53', '201923210312', 'uploads/receipts/receipt_67d1abf549cf0_1741794293.jpg', '09510974884', 'Rejected', ' [Payment rejected by admin]', '2025-03-12 23:44:53', '2025-03-12 23:45:14', 'Jhon Bautista', 'maintenance', 'Broken Faucet', NULL),
(45, 50, 1000.00, '2025-03-16 15:36:30', '2012120513868', 'uploads/receipts/receipt_67d6effeb4802_1742139390.jpg', '09510974884', 'Rejected', ' [Payment rejected by admin]', '2025-03-16 15:36:30', '2025-03-16 15:54:48', 'Jhon Bautista', 'maintenance', 'Maintenance Charges', NULL),
(46, 50, 22500.00, '2025-03-16 15:38:00', '2012120513868', 'uploads/receipts/receipt_67d6f05815d18_1742139480.jpg', '09510974884', 'Received', NULL, '2025-03-16 15:38:00', '2025-03-16 15:40:05', 'Jhon Bautista', 'rent', 'Monthly Rent', NULL),
(47, 20, 33750.00, '2025-03-16 15:43:19', '1001543610110', 'uploads/receipts/receipt_67d6f197df05c_1742139799.jpg', '09510974884', 'Received', NULL, '2025-03-16 15:43:19', '2025-03-16 15:43:56', 'Jhon Bautista', 'rent', 'Monthly Rent', NULL),
(48, 47, 90000.00, '2025-03-16 15:45:02', '1001543610110', 'uploads/receipts/receipt_67d6f1fe9fa77_1742139902.jpg', '09510974884', 'Received', NULL, '2025-03-16 15:45:02', '2025-03-16 15:46:37', 'Jhon Bautista', 'rent', 'Monthly Rent', NULL),
(49, 43, 500.00, '2025-03-16 16:03:35', '1001543610110', 'uploads/receipts/receipt_67d6f6575ed1c_1742141015.jpg', '09510974884', 'Rejected', ' [Rejection Reason: Insufficient amount, Please try again with valid amount. Thank you!\n\n- PropertyWise Team]', '2025-03-16 16:03:35', '2025-03-16 16:12:12', 'Jhon Bautista', 'utilities', 'Water Bill', NULL),
(50, 50, 90000.00, '2025-03-20 05:42:27', '2012120513868', 'uploads/receipts/receipt_67dbaac37f020_1742449347.jpg', '09510974884', 'Received', NULL, '2025-03-20 05:42:27', '2025-03-20 06:09:57', 'Jhon Bautista', 'rent', 'Monthly Rent', NULL),
(51, 50, 1500.00, '2025-03-26 00:00:00', '', '', '', 'Received', '', '2025-03-20 06:07:25', '2025-03-20 06:07:25', '1', 'other', 'Maintenance Fee', ''),
(52, 56, 22500.00, '2025-03-30 00:00:00', '', '', '', 'Received', '', '2025-03-20 09:46:19', '2025-03-20 09:46:19', '1', 'rent', '', ''),
(53, 56, 22500.00, '2025-04-30 00:00:00', '', '', '', 'Received', '', '2025-03-21 12:05:59', '2025-03-21 12:05:59', '1', 'rent', '', ''),
(54, 56, 22500.00, '2025-03-21 12:11:45', '212234182329', 'uploads/receipts/receipt_67dd57814dc4b_1742559105.jpg', '09510974884', 'Rejected', ' [Rejection Reason: invalid payment]', '2025-03-21 12:11:45', '2025-03-21 12:13:21', 'Jhon Bautista', 'rent', 'Monthly Rent', NULL),
(55, 58, 500.00, '2025-03-24 09:56:49', '12345678910111', 'uploads/receipts/receipt_67e12c615d3b9_1742810209.jpg', '09208383913', 'Received', NULL, '2025-03-24 09:56:49', '2025-03-24 09:58:13', 'PJ ETESAM', 'utilities', 'water', NULL),
(56, 58, 81000.00, '2025-03-28 00:00:00', '', '', '', 'Received', '', '2025-03-24 10:39:39', '2025-03-24 10:39:39', '31', 'rent', '', ''),
(57, 43, 22500.00, '2025-04-10 00:43:01', '1001543610110', 'uploads/receipts/receipt_67f6a395df620_1744216981.jpg', '09510974884', 'Pending', NULL, '2025-04-10 00:43:01', '2025-04-10 00:43:01', '', 'rent', 'Monthly Rent', NULL),
(58, 53, 36000.00, '2025-04-10 01:39:21', '1001543610110', 'uploads/receipts/receipt_67f6b0c98c5e9_1744220361.jpg', '09510974884', 'Received', NULL, '2025-04-10 01:39:21', '2025-04-10 01:42:17', 'Jhon Bautista', 'rent', 'Monthly Rent', NULL),
(59, 52, 49500.00, '2025-04-10 01:43:09', '1001543610110', 'uploads/receipts/receipt_67f6b1ad57eac_1744220589.jpg', '09510974884', 'Rejected', ' [Rejection Reason: Insufficient payment amount]', '2025-04-10 01:43:09', '2025-04-10 01:43:48', 'Jhon Bautista', 'rent', 'Monthly Rent', NULL),
(61, 47, 90000.00, '2025-04-11 00:00:00', '', '', '', 'Received', '', '2025-04-10 01:50:21', '2025-04-10 01:50:21', '1', 'rent', '', ''),
(62, 55, 90000.00, '2025-04-14 00:00:00', '', '', '', 'Received', '', '2025-04-10 01:51:33', '2025-04-10 01:51:33', '1', 'rent', '', ''),
(63, 55, 90000.00, '2025-04-14 00:00:00', '', '', '', 'Received', '', '2025-04-10 01:56:05', '2025-04-10 01:56:05', '1', 'rent', '', ''),
(64, 55, 500.00, '2025-04-10 01:56:54', '1001543610110', 'uploads/receipts/receipt_67f6b4e674e74_1744221414.jpg', '09510974884', 'Received', NULL, '2025-04-10 01:56:54', '2025-04-10 01:57:12', 'Jhon Bautista', 'utilities', 'Water Bill', NULL),
(65, 53, 1500.00, '2025-04-09 00:00:00', '', '', '', 'Received', '', '2025-04-10 02:00:20', '2025-04-10 02:00:20', '1', 'other', 'Maintenance Charges', '');

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
(1, '101', 'Warehouse', 100.00, 45000.00, 'uploads/67447b19203d5_architecture-5339245_1280.jpg', 'Reserved', 'active'),
(3, '105', 'Office', 75.00, 33750.00, 'uploads/6744916776e67_bricks-2181920_1280.jpg', 'Reserved', 'active'),
(4, '102', 'Commercial', 50.00, 22500.00, 'uploads/674492f88e939_kitchen-8297678_1280.jpg', 'Occupied', 'active'),
(5, '106', 'Warehouse', 100.00, 45000.00, 'uploads/6744bfef4a3ce_architecture-5339245_1280.jpg', 'Occupied', 'active'),
(6, '107', 'Commercial', 150.00, 67500.00, 'uploads/6746d96160177_architecture-3383067_1280.jpg', 'Occupied', 'active'),
(7, '115', 'Warehouse', 110.00, 49500.00, 'uploads/674ee720cb5b1_warehouse-1026496_1280.jpg', 'Occupied', 'active'),
(8, '116', 'Office', 80.00, 36000.00, 'uploads/674ee99e0119e_classroom-4919804_1280.jpg', 'Occupied', 'active'),
(9, '201', 'Commercial', 50.00, 22500.00, 'uploads/6757aa7d0e2eb_kitchen-8714865_1280.jpg', 'Reserved', 'active'),
(10, '205', 'Office', 50.00, 22500.00, 'uploads/6757c2c52a4a1_kitchen-1336160_1280.jpg', 'Occupied', 'active'),
(11, '206', 'Warehouse', 150.00, 67500.00, 'uploads/67ab43a74ce60_ShockWatch-Warehouse-Efficiency.jpg', 'Reserved', 'active'),
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
(30, '703', 'Warehouse', 150.00, 67500.00, 'uploads/67bde96c3927a_pexels-spacex-60130.jpg', 'Reserved', 'active'),
(31, '103', 'Warehouse', 150.00, 67500.00, 'uploads/67d82cc027e23_pic6.jpg', 'Reserved', 'active'),
(32, '302', 'Warehouse', 180.00, 81000.00, 'uploads/67d82d13e42f2_pic5.jpg', 'Occupied', 'active'),
(33, '303', 'Warehouse', 120.00, 54000.00, 'uploads/67d82d484dc56_pic4.jpg', 'Occupied', 'active'),
(36, '403', 'Warehouse', 100.00, 45000.00, 'uploads/67d82db104e23_pic1.jpg', 'Occupied', 'active'),
(37, '209', 'Warehouse', 150.00, 67500.00, 'uploads/67e12e6ea4ef5_EMPTY PROPERTY2.PNG', 'Reserved', 'active'),
(38, '210', 'Warehouse', 180.00, 90000.00, 'uploads/67eb9d210f7c3_pic6.jpg', 'Available', 'active'),
(39, '402', 'Warehouse', 200.00, 100000.00, 'uploads/67eb9dc1b9b2e_pic5.jpg', 'Available', 'active'),
(40, '304', 'Warehouse', 200.00, 100000.00, 'uploads/67eb9ddeefa23_pic4.jpg', 'Available', 'active'),
(41, '707', 'Warehouse', 160.00, 72000.00, 'uploads/67f6a63fd1cfc_pic3.jpg', 'Available', 'active'),
(42, '609', 'Office', 170.00, 76500.00, 'uploads/67f6a686bd313_pic5.jpg', 'Reserved', 'active');

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
(32, 11, 1, '2025-03-05', '10:40:00', '2025-02-27 18:17:03', 'Confirmed', 0),
(33, 23, 27, '2025-03-05', '14:00:00', '2025-03-01 15:24:03', 'Completed', 0),
(34, 26, 7, '2025-03-10', '10:30:00', '2025-03-09 04:33:42', 'Completed', 0),
(35, 26, 8, '2025-03-20', '10:40:00', '2025-03-09 04:43:43', 'Completed', 0),
(36, 26, 20, '2025-03-30', '13:30:00', '2025-03-09 04:56:30', 'Completed', 1),
(37, 26, 20, '2025-03-30', '10:31:00', '2025-03-09 05:03:02', 'Completed', 0),
(38, 28, 9, '2025-03-16', '09:00:00', '2025-03-15 13:56:32', 'Confirmed', 0),
(39, 11, 31, '2025-03-21', '09:30:00', '2025-03-20 05:37:29', 'Confirmed', 0),
(40, 29, 10, '2025-03-21', '10:30:00', '2025-03-20 09:28:47', 'Completed', 0),
(41, 30, 26, '2025-03-21', '09:01:00', '2025-03-20 09:37:19', 'Pending', 0),
(42, 27, 30, '2025-03-24', '09:30:00', '2025-03-21 16:01:53', 'Confirmed', 0),
(43, 27, 33, '2025-03-24', '12:00:00', '2025-03-21 16:02:30', 'Completed', 0),
(47, 33, 32, '2025-03-25', '10:34:00', '2025-03-24 09:34:50', 'Completed', 0),
(48, 33, 37, '2025-03-30', '10:10:00', '2025-03-24 10:07:52', 'Confirmed', 0),
(49, 11, 3, '2025-04-19', '11:47:00', '2025-04-09 11:44:30', 'Confirmed', 0),
(50, 11, 11, '2025-04-14', '13:40:00', '2025-04-09 16:21:43', 'Cancelled', 0),
(51, 11, 11, '2025-04-14', '13:40:00', '2025-04-09 16:21:55', 'Cancelled', 0),
(60, 11, 12, '2025-04-19', '13:24:00', '2025-04-09 16:23:38', 'Cancelled', 0),
(61, 11, 11, '2025-04-14', '15:32:00', '2025-04-09 16:32:18', 'Pending', 0),
(62, 26, 42, '2025-04-14', '10:40:00', '2025-04-09 17:03:54', 'Confirmed', 0);

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
(15, 'freshplayz18@gmail.com', '$2y$10$Zu8rBaxirZhlAmFE23kOtOXgd6EcD5GIV1dOx66x8o2AEe7blgP5K', 'CONRAD KANE', 'Hvac Technician', '09510975881', NULL, '2024-11-25 18:23:43', NULL, 'Inactive', '128016', '2025-03-24 06:21:16', 0),
(17, 'kjstevenpalma09@gmail.com', '$2y$10$9GpFnvY4vqvXcgUs1RrKDu3Sfyos0t1w3OhcV72vBvWQ85qVxlaHy', 'KJ STEVEN PALMA', 'General Maintenance', '09510975885', 'ac5809a81cd8c5da3043d58b235e95fcb80881216b0ade9d58d198a6adc096b7', '2024-12-10 04:30:55', NULL, 'Busy', '771030', '2025-02-23 16:06:40', 0),
(19, 'jamesalfonso212@gmail.com', '$2y$10$Sh7wTzJpbC.kwfDWHLS.AO.krZvNFMyH9FvjfiHabuHXNU1Bphgq.', 'JAMES ALFONSO', 'Electrical Specialist', '09410524332', NULL, '2025-03-22 16:47:24', NULL, 'Busy', '279261', '2025-03-22 16:58:37', 0),
(21, 'etesamp69@gmail.com', '$2y$10$zi0IUI29/O9PvNDEMpym5.gAYsNfsDyykfjhNMbxB1DB0myh9WcRG', 'PJ XXX', 'General Maintenance', '09208383913', NULL, '2025-03-23 15:06:35', NULL, 'Available', '240371', '2025-03-23 15:17:08', 0),
(22, 'cedricpm22@gmail.com', '$2y$10$pRH1f/zIBM6kc4ZdSmqNqu8aKkm7MJwbH3g1Quu.fcc8HB7NUraRy', 'ROM CASTRO', 'Plumbing Specialist', '09615013119', NULL, '2025-03-24 09:42:47', NULL, 'Busy', '798138', '2025-03-24 09:55:36', 0);

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
  `status` enum('active','inactive','archived','turnover') DEFAULT NULL COMMENT 'active: currently renting, inactive: no longer renting, archived: historical record, turnover: unit turned over',
  `contract_file` varchar(255) DEFAULT NULL,
  `contract_upload_date` datetime DEFAULT NULL,
  `downpayment_receipt` varchar(255) DEFAULT NULL,
  `last_payment_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`tenant_id`, `user_id`, `unit_rented`, `rent_from`, `rent_until`, `monthly_rate`, `outstanding_balance`, `registration_date`, `created_at`, `updated_at`, `downpayment_amount`, `payable_months`, `status`, `contract_file`, `contract_upload_date`, `downpayment_receipt`, `last_payment_date`) VALUES
(20, 11, '3', '2024-12-29', '2026-05-20', 33750.00, 406250.00, '0000-00-00', '2024-12-09 13:52:32', '2025-04-09 11:36:53', 100000.00, 13, 'turnover', 'uploads/contracts/67b1dc1a23042_20_Rental_Agreement.pdf', '2025-02-16 20:37:46', NULL, '2025-03-16'),
(21, 23, '10', '2024-12-10', '2026-12-20', 22500.00, 395000.00, '0000-00-00', '2024-12-10 04:27:46', '2025-03-12 13:59:42', 100000.00, 18, 'active', NULL, NULL, NULL, '2025-05-30'),
(42, 24, '6', '2025-04-09', '2026-11-09', 67500.00, 1215000.00, '0000-00-00', '2024-12-31 08:43:13', '2025-04-09 09:08:34', 120000.00, 18, 'active', NULL, NULL, '../uploads/downpayment/receipt_1744189714_7779.jpg', '2025-05-30'),
(43, 11, '4', '2025-01-03', '2027-11-24', 22500.00, 642500.00, '0000-00-00', '2025-01-03 15:08:01', '2025-03-13 16:54:14', 100000.00, 30, 'active', 'uploads/contracts/67d30db6ee0b7_43_Rental_Agreement.pdf', '2025-03-13 16:54:14', NULL, NULL),
(44, 23, '5', '2025-01-23', '2027-10-03', 45000.00, 895000.00, '0000-00-00', '2025-01-03 15:12:16', '2025-03-05 15:18:27', 500000.00, 21, 'active', NULL, NULL, NULL, NULL),
(45, 11, '23', '2025-02-18', '2027-09-23', 90000.00, 2244000.00, '0000-00-00', '2025-02-18 12:20:19', '2025-03-05 15:08:19', 500000.00, 26, 'active', 'uploads/contracts/67b47bfcb7e70_45_Rental_Agreement.pdf', '2025-02-18 20:24:28', NULL, NULL),
(47, 11, '20', '2025-02-28', '2028-10-26', 90000.00, 3100000.00, '0000-00-00', '2025-02-23 14:37:52', '2025-04-09 17:52:44', 500000.00, 35, 'archived', NULL, NULL, NULL, '2025-04-11'),
(48, 24, '29', '2025-03-06', '2027-09-30', 90000.00, 2110000.00, '0000-00-00', '2025-02-25 05:49:53', '2025-03-12 15:28:29', 500000.00, 24, 'active', NULL, NULL, NULL, '2025-05-30'),
(50, 11, '25', '2025-04-30', '2026-12-30', 90000.00, 1287500.00, '0000-00-00', '2025-02-25 16:20:51', '2025-03-20 06:09:57', 400000.00, 15, 'active', NULL, NULL, NULL, '2025-03-20'),
(51, 23, '27', '2025-03-10', '2027-05-30', 78750.00, 1468750.00, '0000-00-00', '2025-03-01 15:25:07', '2025-03-09 04:30:41', 500000.00, 20, 'active', NULL, NULL, NULL, NULL),
(52, 26, '7', '2025-03-14', '2027-07-20', 49500.00, 586500.00, '0000-00-00', '2025-03-09 04:34:49', '2025-04-09 17:20:00', 750000.00, 12, 'active', 'uploads/contracts/67f6ac40ef932_52_Rental_Agreement.pdf', '2025-04-10 01:20:00', '../uploads/downpayment/receipt_1741494889_1955.jpg', '2025-04-30'),
(53, 26, '8', '2025-04-15', '2027-12-27', 36000.00, 594000.00, '0000-00-00', '2025-03-09 04:45:17', '2025-04-09 17:42:17', 450000.00, 17, 'active', NULL, NULL, NULL, '2025-04-10'),
(55, 26, '20', '2025-03-30', '2027-10-19', 90000.00, 1530000.00, '0000-00-00', '2025-03-09 05:04:08', '2025-04-09 17:56:05', 800000.00, 17, 'active', NULL, NULL, '../uploads/downpayment/receipt_1741496648_8709.jpg', '2025-04-14'),
(56, 29, '10', '2025-03-30', '2027-09-30', 22500.00, 135000.00, '0000-00-00', '2025-03-20 09:30:48', '2025-03-21 12:05:59', 500000.00, 6, 'active', 'uploads/contracts/67dbe0e0d8523_56_Rental_Agreement.pdf', '2025-03-20 09:33:20', '../uploads/downpayment/receipt_1742463048_9861.jpg', '2025-04-30'),
(57, 27, '33', '2025-04-09', '2027-11-29', 54000.00, 864000.00, '0000-00-00', '2025-03-21 16:04:22', '2025-04-09 08:39:35', 900000.00, 16, 'active', NULL, NULL, '../uploads/downpayment/receipt_1744187975_6129.jpg', NULL),
(58, 33, '32', '2025-03-27', '2027-12-30', 81000.00, 2106000.00, '0000-00-00', '2025-03-24 09:39:23', '2025-03-24 10:39:39', 500000.00, 26, 'active', 'uploads/contracts/67e12b6a55bcc_58_CONTRACT.pdf', '2025-03-24 09:52:42', '../uploads/downpayment/receipt_1742809163_2905.jpg', '2025-03-28'),
(59, 28, '9', '2025-04-14', '2027-04-12', 22500.00, 45000.00, '0000-00-00', '2025-04-09 18:35:15', '2025-04-09 18:35:15', 500000.00, 2, NULL, NULL, NULL, '../uploads/downpayment/receipt_1744223715_3000.jpg', NULL),
(60, 28, '9', '2025-04-14', '2027-04-12', 22500.00, 45000.00, '0000-00-00', '2025-04-09 18:37:46', '2025-04-09 18:37:46', 500000.00, 2, NULL, NULL, NULL, '../uploads/downpayment/receipt_1744223866_6137.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tenant_turnovers`
--

CREATE TABLE `tenant_turnovers` (
  `turnover_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `status` enum('notified','scheduled','inspected','completed') NOT NULL DEFAULT 'notified',
  `notification_date` datetime DEFAULT NULL,
  `notification_message` text DEFAULT NULL,
  `inspection_date` datetime DEFAULT NULL,
  `staff_assigned` int(11) DEFAULT NULL,
  `inspection_notes` text DEFAULT NULL,
  `cleanliness_rating` enum('excellent','good','fair','poor') DEFAULT NULL,
  `damage_rating` enum('none','minor','moderate','major') DEFAULT NULL,
  `equipment_rating` enum('excellent','good','fair','poor') DEFAULT NULL,
  `inspection_report` text DEFAULT NULL,
  `inspection_photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`inspection_photos`)),
  `inspection_completed_date` datetime DEFAULT NULL,
  `completion_notes` text DEFAULT NULL,
  `completion_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_turnovers`
--

INSERT INTO `tenant_turnovers` (`turnover_id`, `tenant_id`, `status`, `notification_date`, `notification_message`, `inspection_date`, `staff_assigned`, `inspection_notes`, `cleanliness_rating`, `damage_rating`, `equipment_rating`, `inspection_report`, `inspection_photos`, `inspection_completed_date`, `completion_notes`, `completion_date`, `created_at`, `updated_at`) VALUES
(1, 20, 'completed', '2025-04-09 14:26:54', 'Dear tenant,\n\nWe would like to inform you of your upcoming move-out and need to schedule a unit inspection. Please prepare the unit according to our turnover guidelines.\n\nThank you,\nBuilding Management', '2025-04-23 16:17:00', 15, '', 'excellent', 'none', 'good', 'The unit is in excellent condition, ready for occupancy.', '[\"..\\/uploads\\/inspections\\/inspection_20_1744198544_0.png\",\"..\\/uploads\\/inspections\\/inspection_20_1744198544_1.png\",\"..\\/uploads\\/inspections\\/inspection_20_1744198544_2.png\"]', '2025-04-09 19:35:44', 'Tenants have returned the keys the unit is in good condition.', '2025-04-09 19:36:49', '2025-04-09 06:06:50', '2025-04-09 11:36:49'),
(2, 43, 'scheduled', '2025-04-09 16:06:19', 'Dear tenant,\n\nWe would like to inform you of your upcoming move-out and need to schedule a unit inspection. Please prepare the unit according to our turnover guidelines.\n\nThank you,\nBuilding Management', '2025-04-21 16:06:00', 19, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-09 08:06:19', '2025-04-09 08:06:30'),
(3, 57, 'scheduled', '2025-04-09 19:08:28', 'Dear tenant,\n\nWe would like to inform you of your upcoming move-out and need to schedule a unit inspection. Please prepare the unit according to our turnover guidelines.\n\nThank you,\nBuilding Management', '2025-04-25 19:08:00', 17, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-09 11:08:28', '2025-04-09 11:08:40'),
(5, 21, 'inspected', '2025-04-09 23:13:55', 'Dear tenant,\n\nWe would like to inform you of your upcoming move-out and need to schedule a unit inspection. Please prepare the unit according to our turnover guidelines.\n\nThank you,\nBuilding Management', '2025-04-19 23:42:00', 15, '', 'excellent', 'none', 'excellent', 'Unit is in well condition, ready for occupancy.', '[\"..\\/uploads\\/inspections\\/inspection_21_1744213419_0.png\",\"..\\/uploads\\/inspections\\/inspection_21_1744213419_1.png\",\"..\\/uploads\\/inspections\\/inspection_21_1744213419_2.png\"]', '2025-04-09 23:43:39', NULL, NULL, '2025-04-09 12:46:03', '2025-04-09 15:43:39'),
(6, 42, 'notified', '2025-04-09 21:19:16', 'Dear tenant,\n\nWe would like to inform you of your upcoming move-out and need to schedule a unit inspection. Please prepare the unit according to our turnover guidelines.\n\nThank you,\nBuilding Management', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-09 13:19:16', '2025-04-09 13:19:16');

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
  `address` varchar(255) DEFAULT NULL,
  `ResetToken` varchar(64) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `profile_image`, `role`, `OTP`, `OTP_used`, `OTP_expiration`, `token`, `is_verified`, `created_at`, `updated_at`, `phone`, `address`, `ResetToken`, `login_attempts`, `last_attempt`, `status`) VALUES
(1, 'Jhon Bautista', 'kjstevenpalma@gmail.com', '$2y$10$IbNu.ni4K.fxIYaIbyFqnekQgBR9RykaYNiTAbmUPa9obcBHlDxWG', NULL, 'Admin', '948495', 0, '2025-04-12 16:56:01', NULL, 1, '2024-10-22 08:59:15', '2025-04-12 14:46:01', '09510973323', NULL, '1179af3b0a0d0b153ea57a9b086e875ccef5e2fd676344d429f5f644d341c463', 0, '2025-04-12 14:46:01', 'active'),
(11, 'David Fuentes', 'kjstevengaming@gmail.com', '$2y$10$mkzTsoYyKhsxpq2BjhP0JOIum9VtvVl/JDDtAyA66TFVqelULzqaO', 'uploads/67bdb549d211a-145218582_429580515129022_8609296071987312843_n.jpg', 'User', '356794', 0, '2025-04-21 14:06:01', NULL, 1, '2024-10-22 09:34:53', '2025-04-21 12:17:36', '09510973444', NULL, '263a0e758c6da920c78e7a40e8fde64484421d136a4121d31a1c249ddfc68187', 0, '2025-04-21 11:56:01', 'inactive'),
(23, 'Conrad Palma', 'kjstevenpalma18@gmail.com', '$2y$10$ePCz3ES5ycuXMIvghHlbS.Tp7vI8DHmRz8VbXRiSwGmb9S5yz3p/a', NULL, 'User', '489455', 0, '2025-03-05 18:29:34', NULL, 1, '2024-12-10 04:21:37', '2025-03-19 15:14:20', '09616733509', NULL, NULL, 1, '2025-03-19 15:14:20', 'inactive'),
(24, 'Anora Hidson', 'freshplayz18@gmail.com', '$2y$10$mKVkrd7ZtB4yXrdmUPN.DO7.msB12SEcdbM1tp7MsZQtko5WOQctK', NULL, 'User', '248043', 0, '2025-04-17 10:01:58', NULL, 1, '2024-12-25 07:47:13', '2025-04-17 07:51:58', '09212973327', NULL, NULL, 0, '2025-04-17 07:51:58', 'active'),
(26, 'Genalyn Palma', 'palmagenalyn17@gmail.com', '$2y$10$5w8ADIL9AijXhs.JVBw1X.olYGpZT8HL7W6TIe4lad3IJSgNtX6Ve', NULL, 'User', '302167', 0, '2025-04-09 21:24:50', NULL, 1, '2025-02-25 16:18:33', '2025-04-09 19:25:51', '09516733408', NULL, NULL, 0, '2025-04-09 19:14:50', 'inactive'),
(27, 'Renante Colaste', 'renantepalma27@gmail.com', '$2y$10$QKOUkfTEMMBA9DEv4pKhFu1zzJ6X28oQ10VSqnlaBXE/WGy19OChW', NULL, 'User', '121509', 0, '2025-04-01 09:45:37', NULL, 1, '2025-03-15 11:55:24', '2025-04-01 07:35:37', '09510973323', NULL, NULL, 0, '2025-04-01 07:35:37', 'active'),
(28, 'Magilenne', 'psychmagilenne@gmail.com', '$2y$10$X9527TlCKNEWBwqFCTxj2uPG5UbSCU6zepEacC1j4ZXyTpXEZ6tIW', 'uploads/67d58684ce24e-IMG_1661.png', 'User', '630747', 0, '2025-03-15 14:19:15', NULL, 1, '2025-03-15 12:56:53', '2025-03-15 14:09:48', '09477256092', NULL, NULL, 0, '2025-03-15 14:09:15', 'inactive'),
(29, 'PJ ETESAM', 'etesam90@gmail.com', '$2y$10$6szGwDv0kln6gdHIAXSCg.Vep0aVpnDPvM2f5v24i.OY9Zjwr6D7O', NULL, 'User', '858170', 0, '2025-03-24 06:17:31', NULL, 1, '2025-03-19 14:30:39', '2025-03-24 06:07:31', '09208383913', NULL, NULL, 0, '2025-03-24 06:07:31', 'active'),
(30, 'Angelo Belen', 'angelobelen012@gmail.com', '$2y$10$9/xdqoZKsri8bnAOzz6j3OucDwajxLu4QpgE3MaR.tRq/gAjHQ1Cm', NULL, 'User', '425480', 0, '2025-03-20 09:44:48', NULL, 1, '2025-03-20 09:31:02', '2025-03-20 09:38:09', '09283480447', NULL, NULL, 0, '2025-03-20 09:34:48', 'inactive'),
(31, 'PJ ETESAM', 'etesampaolojames@gmail.com', '$2y$10$JVyneZ6q4QjBxHZBZWCwnOKQp8oB2wON8MQbzXh8mNzC.ag2ok6Iq', NULL, 'Admin', '123323', 0, '2025-03-24 09:45:59', NULL, 1, '2025-03-23 14:54:42', '2025-03-24 09:35:59', '09208383913', NULL, NULL, 0, '2025-03-24 09:35:59', 'active'),
(33, 'Paolo James Etesam', 'etesam099@gmail.com', '$2y$10$e3CDub6S94t/feRgEI2mP.DKpY2ELFWBGEjovvcy55ailfKEf9Y7K', NULL, 'User', '225317', 0, '2025-03-24 09:42:43', NULL, 1, '2025-03-24 09:31:47', '2025-03-24 09:32:43', '09208383913', NULL, NULL, 0, '2025-03-24 09:32:43', 'active');

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
-- Indexes for table `kyc_verification`
--
ALTER TABLE `kyc_verification`
  ADD PRIMARY KEY (`kyc_id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_date` (`payment_date`);

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
-- Indexes for table `tenant_turnovers`
--
ALTER TABLE `tenant_turnovers`
  ADD PRIMARY KEY (`turnover_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `staff_assigned` (`staff_assigned`);

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
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=662;

--
-- AUTO_INCREMENT for table `generated_reports`
--
ALTER TABLE `generated_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `kyc_verification`
--
ALTER TABLE `kyc_verification`
  MODIFY `kyc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `tenant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `tenant_turnovers`
--
ALTER TABLE `tenant_turnovers`
  MODIFY `turnover_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

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
-- Constraints for table `kyc_verification`
--
ALTER TABLE `kyc_verification`
  ADD CONSTRAINT `kyc_verification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

--
-- Constraints for table `tenant_turnovers`
--
ALTER TABLE `tenant_turnovers`
  ADD CONSTRAINT `tenant_turnovers_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  ADD CONSTRAINT `tenant_turnovers_ibfk_2` FOREIGN KEY (`staff_assigned`) REFERENCES `staff` (`staff_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
