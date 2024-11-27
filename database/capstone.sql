-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2024 at 09:08 AM
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
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `unit_id` int(11) NOT NULL,
  `unit_no` varchar(50) NOT NULL,
  `unit_type` varchar(50) NOT NULL,
  `square_meter` decimal(10,2) NOT NULL,
  `monthly_rent` decimal(10,2) NOT NULL,
  `images` text DEFAULT NULL,
  `status` enum('Available','Occupied','Maintenance') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property`
--

INSERT INTO `property` (`unit_id`, `unit_no`, `unit_type`, `square_meter`, `monthly_rent`, `images`, `status`) VALUES
(1, '101', 'Warehouse', 100.00, 45000.00, 'uploads/67447b19203d5_architecture-5339245_1280.jpg', 'Available'),
(3, '105', 'Office', 75.00, 33750.00, 'uploads/6744916776e67_bricks-2181920_1280.jpg', 'Available'),
(4, '102', 'Commercial', 50.00, 22500.00, 'uploads/674492f88e939_kitchen-8297678_1280.jpg', 'Available'),
(5, '106', 'Warehouse', 100.00, 45000.00, 'uploads/6744bfef4a3ce_architecture-5339245_1280.jpg', 'Available');

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
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `Email`, `Password`, `Name`, `Specialty`, `Phone_Number`, `ResetToken`, `created_at`, `reset_token_expires`) VALUES
(13, 'kjstevenpalma09@gmail.com', '$2y$10$YTW06KztLzpJDMntGdIlPulNKBa25wQvDrBdLpNW1W43ELkx54Bpq', 'KJ STEVEN PALMA', 'Electrical Specialist', '09510974883', NULL, '2024-11-24 16:03:29', NULL),
(15, 'freshplayz18@gmail.com', '$2y$10$Q2CDx//NHpKfT3H6pTKNAuuzzS6HIrAy0tOM4YrR97Ewzy0DyegAe', 'CONRAD KANE', 'Hvac Technician', '09510975884', NULL, '2024-11-25 18:23:43', NULL);

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
  `downpayment_amount` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`tenant_id`, `user_id`, `unit_rented`, `rent_from`, `rent_until`, `monthly_rate`, `outstanding_balance`, `registration_date`, `created_at`, `updated_at`, `downpayment_amount`) VALUES
(8, 11, '102', '2024-11-20', '2026-11-30', 5000.00, 70000.00, '2024-11-17', '2024-11-17 08:15:27', '2024-11-17 08:15:27', 50000.00),
(12, 21, '106', '2024-11-26', '2026-12-30', 5000.00, 75000.00, '2024-11-26', '2024-11-25 18:22:02', '2024-11-25 18:22:02', 50000.00);

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
(1, 'Jhon Bautista', 'kjstevenpalma@gmail.com', '$2y$10$IbNu.ni4K.fxIYaIbyFqnekQgBR9RykaYNiTAbmUPa9obcBHlDxWG', NULL, 'Admin', '294149', 1, '2024-11-22 21:19:10', NULL, 1, '2024-10-22 08:59:15', '2024-11-27 06:52:00', '09510973323', NULL, 0, '2024-11-27 06:52:00', 'active'),
(11, 'David Fuentes', 'kjstevengaming@gmail.com', '$2y$10$/8diyrvl1CUo9oPl5ZWP0eGttP1HmXcQUk0eqnRfnMVCex81lZVPS', 'uploads/6746c58b0b8fd-145218582_429580515129022_8609296071987312843_n.jpg', 'User', '227288', 1, '2024-11-22 21:11:38', NULL, 1, '2024-10-22 09:34:53', '2024-11-27 07:08:59', '09510973444', NULL, 0, '2024-11-27 06:53:04', 'active'),
(21, 'James Paloma', 'kjstevenpalma18@gmail.com', '$2y$10$2zG237giCUohYEjRtWRuoeyHIEz0z7Rs1renRDNxgV.5G/1kBjA5.', NULL, 'User', '794400', 1, '2024-11-25 19:16:48', NULL, 1, '2024-11-25 18:06:07', '2024-11-25 18:07:45', '09210953442', NULL, 0, '2024-11-25 18:06:48', 'inactive');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`unit_id`);

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
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `tenant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
