-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: ccsw-mysql1.mysql.database.azure.com
-- Generation Time: Mar 18, 2026 at 09:24 AM
-- Server version: 8.0.42-azure
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `0020041303_db1`
--

-- --------------------------------------------------------

--
-- Table structure for table `login_details`
--

CREATE TABLE `login_details` (
  `my_row_id` bigint UNSIGNED NOT NULL INVISIBLE,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `register_form`
--

CREATE TABLE `register_form` (
  `my_row_id` bigint UNSIGNED NOT NULL INVISIBLE,
  `username` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `mobile` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `register_form`
--

INSERT INTO `register_form` (`my_row_id`, `username`, `email`, `password`, `date_of_birth`, `mobile`) VALUES
(1, 'tom', '19BalogunT@gmail.com', '1234', '2025-12-17', '0292832838'),
(3, 'h', '19BalogunT@gmail.com', '$2y$10$Np2yqNKL2JcFsGKwBbxUUeSEnR/Ia4cP.PTi3A3Ga37WJ6K5sLo1G', '2025-12-17', '0292832838'),
(4, 'bal', '19BalogunT@gmail.com', '$2y$10$HceHZxa0zYlzUVtCLc.JfeQAJf.IX7kvXiK0VzraddclvXIEQGBNG', '2014-03-07', '0292832838'),
(5, 't', '19BalogunT@gmail.com', '$2y$10$ArLEjN1KHNIqVCwbThA7y.4fOHGTj3SSZyGtJg7YQW/bm8j.y1tO.', '2014-03-07', '0292832838');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_booking`
--

CREATE TABLE `ticket_booking` (
  `booking_id` int NOT NULL,
  `user_id` int NOT NULL,
  `slot_id` int NOT NULL,
  `booking_status` enum('PENDING','CONFIRMED','CANCELLED','') NOT NULL,
  `create_on` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_booking_items`
--

CREATE TABLE `ticket_booking_items` (
  `booking_item_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `ticket_type_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_types`
--

CREATE TABLE `ticket_types` (
  `ticket_types_id` int NOT NULL,
  `ticket_name` varchar(50) NOT NULL,
  `ticket_price` decimal(10,0) NOT NULL,
  `age_min` int NOT NULL,
  `age_max` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

CREATE TABLE `user_details` (
  `User_ID` int NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `mobile_number` varchar(20) NOT NULL,
  `date_of_birth` date NOT NULL,
  `Fullname` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visit_slots`
--

CREATE TABLE `visit_slots` (
  `slots_id` int NOT NULL,
  `visit_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `capacity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `login_details`
--
ALTER TABLE `login_details`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `register_form`
--
ALTER TABLE `register_form`
  ADD PRIMARY KEY (`my_row_id`);

--
-- Indexes for table `ticket_booking`
--
ALTER TABLE `ticket_booking`
  ADD PRIMARY KEY (`booking_id`);

--
-- Indexes for table `ticket_booking_items`
--
ALTER TABLE `ticket_booking_items`
  ADD PRIMARY KEY (`booking_item_id`);

--
-- Indexes for table `ticket_types`
--
ALTER TABLE `ticket_types`
  ADD PRIMARY KEY (`ticket_types_id`);

--
-- Indexes for table `user_details`
--
ALTER TABLE `user_details`
  ADD PRIMARY KEY (`User_ID`);

--
-- Indexes for table `visit_slots`
--
ALTER TABLE `visit_slots`
  ADD PRIMARY KEY (`slots_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `login_details`
--
ALTER TABLE `login_details`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT INVISIBLE;

--
-- AUTO_INCREMENT for table `register_form`
--
ALTER TABLE `register_form`
  MODIFY `my_row_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT INVISIBLE, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ticket_booking`
--
ALTER TABLE `ticket_booking`
  MODIFY `booking_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_booking_items`
--
ALTER TABLE `ticket_booking_items`
  MODIFY `booking_item_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_types`
--
ALTER TABLE `ticket_types`
  MODIFY `ticket_types_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_details`
--
ALTER TABLE `user_details`
  MODIFY `User_ID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visit_slots`
--
ALTER TABLE `visit_slots`
  MODIFY `slots_id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
