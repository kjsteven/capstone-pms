-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2025 at 01:57 PM
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
(132, 'Unit Occupancy Report', '2025-02-16', '2025-01', '{\"title\":\"Unit Occupancy Report\",\"overview\":{\"report_date\":\"2025-02-16 19:31:24\",\"report_period\":\"2025-01\",\"generated_by\":\"Jhon Bautista\"},\"units\":[{\"unit_number\":\"101\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Maintenance\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"102\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2025-01-03\",\"rent_end_date\":\"2027-11-24\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":\"665000.00\",\"payable_months\":\"30\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2025-01-03 23:08:01\"},{\"unit_number\":\"105\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"David Fuentes\",\"rent_start_date\":\"2024-12-29\",\"rent_end_date\":\"2026-05-20\",\"monthly_rent\":\"33750.00\",\"outstanding_balance\":\"440000.00\",\"payable_months\":\"14\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-09 21:52:32\"},{\"unit_number\":\"106\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2025-01-23\",\"rent_end_date\":\"2027-10-03\",\"monthly_rent\":\"45000.00\",\"outstanding_balance\":\"940000.00\",\"payable_months\":\"21\",\"downpayment_amount\":\"500000.00\",\"registration_date\":\"2025-01-03 23:12:16\"},{\"unit_number\":\"107\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Anora Hidson\",\"rent_start_date\":\"2024-12-31\",\"rent_end_date\":\"2026-10-31\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":\"1385000.00\",\"payable_months\":\"21\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-31 16:43:13\"},{\"unit_number\":\"115\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"49500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"116\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"36000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"201\",\"unit_type\":\"Commercial\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"205\",\"unit_type\":\"Office\",\"occupancy_status\":\"Occupied\",\"tenant_name\":\"Conrad Palma\",\"rent_start_date\":\"2024-12-10\",\"rent_end_date\":\"2026-12-20\",\"monthly_rent\":\"22500.00\",\"outstanding_balance\":\"440000.00\",\"payable_months\":\"20\",\"downpayment_amount\":\"100000.00\",\"registration_date\":\"2024-12-10 12:27:46\"},{\"unit_number\":\"206\",\"unit_type\":\"Warehouse\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"67500.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"207\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"90000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"},{\"unit_number\":\"208\",\"unit_type\":\"Office\",\"occupancy_status\":\"Available\",\"tenant_name\":\"N\\/A\",\"rent_start_date\":\"N\\/A\",\"rent_end_date\":\"N\\/A\",\"monthly_rent\":\"81000.00\",\"outstanding_balance\":0,\"payable_months\":0,\"downpayment_amount\":0,\"registration_date\":\"N\\/A\"}],\"summary\":{\"total_units\":12,\"occupied_units\":5,\"available_units\":7,\"occupancy_rate\":41.67}}', '2025-02-16 11:31:24', 0, '../reports/unit_occupancy_report_2025-02-16_19-31-24.csv');

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
(89, 11, '105', 'Electrical Problem', 'Outage', 'First, I turned off the power at the circuit breaker to safely inspect the electrical panel and identified a blown fuse. Then i fix the cables.', 200.00, '2025-02-16 20:23:00', '2024-12-13', 'Completed', 'maintenance_report_89_20250216_132313.pdf', 'uploads/maintenance_requests/maintenance_675b0f44cce1d9.66352520.jpg', '2024-12-12 16:28:52', '2025-02-16 12:23:13', 15, 'medium', 0),
(94, 23, '205', 'Electrical Problem', 'Outage', NULL, NULL, NULL, '2024-12-14', 'Pending', NULL, 'uploads/maintenance_requests/maintenance_675be5b19c7b59.82571621.jpg', '2024-12-13 07:43:45', '2025-02-16 12:30:30', 17, 'medium', 0),
(95, 11, '102', 'Electrical Problem', 'Overheating', NULL, NULL, NULL, '2025-01-09', 'Pending', NULL, 'uploads/maintenance_requests/maintenance_6778c8547a2057.59459477.jpg', '2025-01-04 05:34:12', '2025-01-11 06:48:52', 17, 'high', 0),
(96, 11, '105', 'Heating Issue', 'AC is broken', NULL, NULL, NULL, '2025-01-15', 'Pending', NULL, 'uploads/maintenance_requests/maintenance_6778c8f5505524.10953351.png', '2025-01-04 05:36:53', '2025-01-04 05:36:53', NULL, 'medium', 0);

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
(1, '101', 'Warehouse', 100.00, 45000.00, 'uploads/67447b19203d5_architecture-5339245_1280.jpg', 'Maintenance', 'active'),
(3, '105', 'Office', 75.00, 33750.00, 'uploads/6744916776e67_bricks-2181920_1280.jpg', 'Occupied', 'active'),
(4, '102', 'Commercial', 50.00, 22500.00, 'uploads/674492f88e939_kitchen-8297678_1280.jpg', 'Occupied', 'active'),
(5, '106', 'Warehouse', 100.00, 45000.00, 'uploads/6744bfef4a3ce_architecture-5339245_1280.jpg', 'Occupied', 'active'),
(6, '107', 'Commercial', 150.00, 67500.00, 'uploads/6746d96160177_architecture-3383067_1280.jpg', 'Occupied', 'active'),
(7, '115', 'Warehouse', 110.00, 49500.00, 'uploads/674ee720cb5b1_warehouse-1026496_1280.jpg', 'Available', 'active'),
(8, '116', 'Office', 80.00, 36000.00, 'uploads/674ee99e0119e_classroom-4919804_1280.jpg', 'Available', 'active'),
(9, '201', 'Commercial', 50.00, 22500.00, 'uploads/6757aa7d0e2eb_kitchen-8714865_1280.jpg', 'Available', 'active'),
(10, '205', 'Office', 50.00, 22500.00, 'uploads/6757c2c52a4a1_kitchen-1336160_1280.jpg', 'Occupied', 'active'),
(11, '206', 'Warehouse', 150.00, 67500.00, 'uploads/67ab43a74ce60_ShockWatch-Warehouse-Efficiency.jpg', 'Available', 'active'),
(12, '207', 'Office', 200.00, 90000.00, 'uploads/67ab43e7572a9_pexels-seven11nash-380769.jpg', 'Available', 'active'),
(13, '208', 'Office', 180.00, 81000.00, 'uploads/67ab442d24775_pexels-pixabay-221537.jpg', 'Available', 'active');

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
(18, 24, 6, '2024-12-27', '20:38:00', '2024-12-27 12:38:03', 'Completed', 0),
(19, 11, 4, '2025-01-01', '10:30:00', '2024-12-31 08:27:21', 'Completed', 0),
(20, 23, 5, '2025-01-06', '10:40:00', '2025-01-03 11:35:03', 'Completed', 0);

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
  `status` enum('Available','Busy','Unavailable') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `Email`, `Password`, `Name`, `Specialty`, `Phone_Number`, `ResetToken`, `created_at`, `reset_token_expires`, `status`) VALUES
(15, 'freshplayz18@gmail.com', '$2y$10$cMki.nt/d2iZEOGmNgtGt.cnZWiSpefRq0L26r2bnFQFWBKP59xY.', 'CONRAD KANE', 'Hvac Technician', '09510975884', NULL, '2024-11-25 18:23:43', NULL, 'Busy'),
(17, 'kjstevenpalma09@gmail.com', '$2y$10$QDPFoWWFQtzD2LSsSteRgeYl7cTjSdgROjeaJG20FuSLK.f5QSxj6', 'KJ STEVEN PALMA', 'General Maintenance', '09510975884', NULL, '2024-12-10 04:30:55', NULL, 'Busy');

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
  `contract_upload_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`tenant_id`, `user_id`, `unit_rented`, `rent_from`, `rent_until`, `monthly_rate`, `outstanding_balance`, `registration_date`, `created_at`, `updated_at`, `downpayment_amount`, `payable_months`, `status`, `contract_file`, `contract_upload_date`) VALUES
(20, 11, '3', '2024-12-29', '2026-05-20', 33750.00, 440000.00, '0000-00-00', '2024-12-09 13:52:32', '2025-02-16 12:37:46', 100000.00, 14, 'active', 'uploads/contracts/67b1dc1a23042_20_Rental_Agreement.pdf', '2025-02-16 20:37:46'),
(21, 23, '10', '2024-12-10', '2026-12-20', 22500.00, 440000.00, '0000-00-00', '2024-12-10 04:27:46', '2024-12-11 13:15:01', 100000.00, 20, 'active', NULL, NULL),
(42, 24, '6', '2024-12-31', '2026-10-31', 67500.00, 1385000.00, '0000-00-00', '2024-12-31 08:43:13', '2024-12-31 08:43:13', 100000.00, 21, 'active', NULL, NULL),
(43, 11, '4', '2025-01-03', '2027-11-24', 22500.00, 665000.00, '0000-00-00', '2025-01-03 15:08:01', '2025-01-03 15:08:01', 100000.00, 30, 'active', NULL, NULL),
(44, 23, '5', '2025-01-23', '2027-10-03', 45000.00, 940000.00, '0000-00-00', '2025-01-03 15:12:16', '2025-01-03 15:12:16', 500000.00, 21, 'active', NULL, NULL);

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
(1, 'Jhon Bautista', 'kjstevenpalma@gmail.com', '$2y$10$IbNu.ni4K.fxIYaIbyFqnekQgBR9RykaYNiTAbmUPa9obcBHlDxWG', NULL, 'Admin', '294149', 1, '2024-11-22 21:19:10', NULL, 1, '2024-10-22 08:59:15', '2025-02-16 12:24:08', '09510973323', NULL, 0, '2025-02-16 12:24:08', 'active'),
(11, 'David Fuentes', 'kjstevengaming@gmail.com', '$2y$10$8hnIlf5d4ugwVNVJFQzNzeOwelk1XTkhghCQDEHi053WvsBCyFUrq', 'uploads/6746c58b0b8fd-145218582_429580515129022_8609296071987312843_n.jpg', 'User', '227288', 1, '2024-11-22 21:11:38', NULL, 1, '2024-10-22 09:34:53', '2025-02-12 11:18:20', '09510973444', NULL, 0, '2025-02-12 10:57:33', 'inactive'),
(23, 'Conrad Palma', 'kjstevenpalma18@gmail.com', '$2y$10$ePCz3ES5ycuXMIvghHlbS.Tp7vI8DHmRz8VbXRiSwGmb9S5yz3p/a', NULL, 'User', '226093', 1, '2024-12-10 05:32:45', NULL, 1, '2024-12-10 04:21:37', '2025-01-03 11:35:16', '09616733509', NULL, 0, '2025-01-03 11:34:04', 'inactive'),
(24, 'Anora Hidson', 'freshplayz18@gmail.com', '$2y$10$mKVkrd7ZtB4yXrdmUPN.DO7.msB12SEcdbM1tp7MsZQtko5WOQctK', NULL, 'User', '864635', 1, '2024-12-25 08:58:18', NULL, 1, '2024-12-25 07:47:13', '2024-12-27 12:29:04', '09212973327', NULL, 0, '2024-12-27 12:29:04', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `generated_reports`
--
ALTER TABLE `generated_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_report_date` (`report_date`),
  ADD KEY `idx_created_at` (`created_at`);

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
-- AUTO_INCREMENT for table `generated_reports`
--
ALTER TABLE `generated_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `tenant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

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
