-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 20, 2025 at 06:03 AM
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
-- Database: `event_management_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_earned` datetime DEFAULT current_timestamp(),
  `points` int(11) DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`id`, `user_id`, `title`, `description`, `date_earned`, `points`) VALUES
(1, 1, 'First Event Participation', 'Successfully participated in your first event', '2025-03-28 08:07:01', 100),
(2, 1, 'Event Winner', 'Won first place in an event', '2025-03-28 08:07:01', 100),
(3, 1, 'Regular Participant', 'Participated in 5 events', '2025-03-28 08:07:01', 100),
(4, 2, 'First Event Participation', 'Successfully participated in your first event', '2025-03-28 08:07:01', 100),
(5, 2, 'Event Winner', 'Won first place in an event', '2025-03-28 08:07:01', 100),
(6, 3, 'First Event Participation', 'Successfully participated in your first event', '2025-03-28 08:07:01', 100);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `venue` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `category`, `date`, `time`, `venue`, `description`, `image`, `created_by`) VALUES
(1, 'Athleema', 'Sports', '2025-03-27', '11:11:00', 'Sports grounds', 'come play', NULL, 1),
(2, 'Athleema', 'Sports', '2025-03-29', '09:00:00', 'Sports grounds', 'Come show case your sports skills', NULL, 3),
(3, 'test 1', '', '2025-03-25', '22:18:00', 'test venue', 'test', NULL, 3),
(4, 'Technofusion 1.0', 'Tech', '2025-03-26', '09:00:00', 'Computer lab', 'create a website and win cash prizes', NULL, 3),
(6, 'SPORTIFY 1.O', 'Sports', '2025-03-29', '09:09:00', 'Sports Ground', 'Welcome to the first ever edition of SPORTIFY', NULL, 3),
(7, 'BUDGET CONCLAVE', '', '2025-03-30', '10:00:00', 'AUDITORIUM', 'where experts, academicians, and industry leaders come together to analyze and discuss the implications of a budget', NULL, 3),
(8, 'ANNUAL FUNCTION', '', '2025-04-01', '10:00:00', 'GROUND', 'COME JOIN THE ANNUAL FUNCTION', NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `event_attendance`
--

CREATE TABLE `event_attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `status` enum('Present','Absent') DEFAULT 'Absent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `comment`, `created_at`) VALUES
(1, 2, 'excited for athleema\r\n', '2025-03-25 16:45:17');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `user_id`, `event_id`, `status`) VALUES
(1, 2, 1, 'Approved'),
(2, 2, 4, 'Approved'),
(3, 4, 3, 'Approved'),
(4, 4, 4, 'Approved'),
(5, 4, 1, 'Approved'),
(6, 4, 2, 'Approved'),
(7, 4, 6, 'Approved'),
(8, 4, 7, 'Approved'),
(9, 4, 8, 'Approved'),
(10, 8, 3, 'Approved'),
(11, 8, 4, 'Approved'),
(12, 8, 1, 'Approved'),
(13, 8, 2, 'Approved'),
(14, 8, 6, 'Approved'),
(15, 8, 7, 'Approved'),
(16, 8, 8, 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `signup_requests`
--

CREATE TABLE `signup_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Student','Faculty') NOT NULL,
  `college` varchar(50) NOT NULL,
  `course` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `section` varchar(10) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `signup_requests`
--

INSERT INTO `signup_requests` (`id`, `name`, `email`, `password`, `role`, `college`, `course`, `year`, `section`, `status`, `created_at`) VALUES
(1, 'Naman', 'naman@123', '$2y$10$9AnCCyeds40PclcYhIdhu.lJDT7Fs5.bkOhMUcU1uewxd.dbdvcSW', 'Student', 'ASB', 'BCA', 2, 'A', 'Approved', '2025-03-25 15:14:49'),
(2, 'Faculty1', 'testfaculty@yopmail.com', '$2y$10$hgAt/VVEgqoqAsJ0zbpqBOXJLH9ei3DXFIhVwS8z9BrFDGKG/vjMa', 'Faculty', 'ASB', 'BCA', 1, 'A', 'Approved', '2025-03-25 16:42:33'),
(3, 'NEERAJ', 'neeraj@123', '$2y$10$ZHTDOZFA4LRYa2yicGhvuegB3FISHKgBH8N4gl/v8bLV15Bmlpf8u', 'Student', 'ASB', 'BCA', 2, 'A', 'Approved', '2025-03-20 04:45:58'),
(4, 'sonam', 'sonam@123', '$2y$10$eEdjGS2XNt7Z2tsFHquCD..rCUBBEV2PucS/6XPDV9qeFGoJeDukm', 'Student', 'ASB', 'BCA', 2, 'A', 'Approved', '2025-03-20 04:47:32'),
(5, 'samara', 'samara@123', '$2y$10$PvMzDJPENvK.IrdzWUKM0.ReL0K1jZW8GOkcjtQC1QoqqO3wRjYhy', 'Student', 'ASB', 'BCA', 3, 'A', 'Approved', '2025-03-20 04:47:57'),
(6, 'adesh', 'adesh@123', '$2y$10$adredzWW8qR.xuozHprAAOyEvFF/twu6fb3JCpyPvBbTKJMdumpiu', 'Student', 'ASB', 'BBA', 1, 'A', 'Approved', '2025-03-20 04:48:17'),
(7, 'aakash', 'aakash@123', '$2y$10$Y8ZstpH8icHU1hw06sX7Qu9DumerrLDIfmnrT9SggH.BqIm9sBgp2', 'Student', 'ASB', 'BCom', 1, 'A', 'Approved', '2025-03-20 04:48:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Student','Faculty') NOT NULL,
  `college` varchar(50) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `college`, `course`, `year`, `section`, `created_at`) VALUES
(1, 'Admin User', 'admin@123', '$2y$10$rFjeWrid8TOw/Hg/a3wuju21uzsk/bi/c4/hxk9DTPESSl1LmO2N.', 'Admin', 'ASB', 'BBA', 1, 'A', '2025-03-25 14:37:36'),
(2, 'Naman', 'naman@123', '$2y$10$9AnCCyeds40PclcYhIdhu.lJDT7Fs5.bkOhMUcU1uewxd.dbdvcSW', 'Student', 'ASB', 'BCA', 2, 'A', '2025-03-25 15:14:57'),
(3, 'Faculty1', 'testfaculty@yopmail.com', '$2y$10$hgAt/VVEgqoqAsJ0zbpqBOXJLH9ei3DXFIhVwS8z9BrFDGKG/vjMa', 'Faculty', 'ASB', 'BCA', 1, 'A', '2025-03-25 16:42:51'),
(4, 'NEERAJ', 'neeraj@123', '$2y$10$ZHTDOZFA4LRYa2yicGhvuegB3FISHKgBH8N4gl/v8bLV15Bmlpf8u', 'Student', 'ASB', 'BCA', 2, 'A', '2025-03-20 04:48:50'),
(5, 'sonam', 'sonam@123', '$2y$10$eEdjGS2XNt7Z2tsFHquCD..rCUBBEV2PucS/6XPDV9qeFGoJeDukm', 'Student', 'ASB', 'BCA', 2, 'A', '2025-03-20 04:48:52'),
(6, 'samara', 'samara@123', '$2y$10$PvMzDJPENvK.IrdzWUKM0.ReL0K1jZW8GOkcjtQC1QoqqO3wRjYhy', 'Student', 'ASB', 'BCA', 3, 'A', '2025-03-20 04:48:53'),
(7, 'adesh', 'adesh@123', '$2y$10$adredzWW8qR.xuozHprAAOyEvFF/twu6fb3JCpyPvBbTKJMdumpiu', 'Student', 'ASB', 'BBA', 1, 'A', '2025-03-20 04:48:54'),
(8, 'aakash', 'aakash@123', '$2y$10$Y8ZstpH8icHU1hw06sX7Qu9DumerrLDIfmnrT9SggH.BqIm9sBgp2', 'Student', 'ASB', 'BCom', 1, 'A', '2025-03-20 04:48:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_wins`
--

CREATE TABLE `user_wins` (
  `user_id` int(11) NOT NULL,
  `wins` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `signup_requests`
--
ALTER TABLE `signup_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_wins`
--
ALTER TABLE `user_wins`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `event_attendance`
--
ALTER TABLE `event_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `signup_requests`
--
ALTER TABLE `signup_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD CONSTRAINT `event_attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `event_attendance_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `user_wins`
--
ALTER TABLE `user_wins`
  ADD CONSTRAINT `user_wins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
