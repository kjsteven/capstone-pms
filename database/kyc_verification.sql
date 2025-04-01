CREATE TABLE IF NOT EXISTS `kyc_verification` (
  `kyc_id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`kyc_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `kyc_verification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
