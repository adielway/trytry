-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 07, 2025 at 02:58 PM
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
-- Database: `grading_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `period` varchar(10) NOT NULL,
  `grade` decimal(5,2) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject_id`, `period`, `grade`, `teacher_id`, `created_at`) VALUES
(1, 1, 1, 'Q1', 88.50, 1, '2025-09-13 18:52:19'),
(2, 1, 2, 'Q1', 92.00, 1, '2025-09-13 18:52:19'),
(3, 1, 3, 'Q1', 85.25, 1, '2025-09-13 18:52:19'),
(4, 1, 5, '1', 73.00, 1, '2025-09-15 09:29:02'),
(5, 1, 3, '1', 87.00, 1, '2025-09-15 09:29:12'),
(6, 1, 4, '1', 85.00, 1, '2025-09-15 10:04:43'),
(7, 1, 6, '1', 95.00, 1, '2025-09-18 08:44:43'),
(8, 1, 1, '1', 98.00, 1, '2025-09-18 08:44:52'),
(9, 1, 2, '1', 95.00, 1, '2025-09-18 08:44:58'),
(10, 1, 5, '2', 74.00, 1, '2025-09-18 09:19:33'),
(11, 1, 3, '2', 80.00, 1, '2025-09-18 09:19:45'),
(12, 1, 4, '2', 80.00, 1, '2025-09-18 09:19:52'),
(13, 1, 6, '2', 85.00, 1, '2025-09-18 09:20:00'),
(15, 1, 1, '2', 80.00, 1, '2025-09-18 09:20:47'),
(16, 1, 2, '2', 87.00, 1, '2025-09-18 09:20:53'),
(17, 1, 5, '3', 95.00, 1, '2025-09-19 07:20:40'),
(18, 1, 3, '3', 97.00, 1, '2025-09-19 07:20:54'),
(19, 1, 4, '3', 95.00, 1, '2025-09-19 07:21:04'),
(20, 1, 6, '3', 99.00, 1, '2025-09-19 07:21:16'),
(21, 1, 1, '3', 96.00, 1, '2025-09-19 07:21:26'),
(22, 1, 2, '3', 98.00, 1, '2025-09-19 07:21:39');

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `child_student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `user_id`, `child_student_id`) VALUES
(1, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_no` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `class` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_no`, `name`, `class`, `user_id`) VALUES
(1, 'S-0001', 'Student Sam', 'Grade 10 - A', 2);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`) VALUES
(1, 'Mathematics'),
(2, 'Science'),
(3, 'English'),
(4, 'Filipino'),
(5, 'AP'),
(6, 'MAPEH');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('teacher','student','parent') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`) VALUES
(1, 'Mr. Guilbert Nicanor Atillo', 'Atillo@gmail.com', '$2y$10$Vt0KtwX95C3t5/pT1tbqc.GDbuvOp/fJCANpkPaukURTGVDCYo.r2', 'teacher'),
(2, 'Sam A. Millares', 'student@example.com', '$2y$10$cc9GihPb0OViRqFGdW4FBOOi0Hs1a2W6bNATuLIHSRpuaV9.brHgW', 'student'),
(3, 'Mrs. Perlie A. Millares', 'parent@example.com', '$2y$10$IrpgAHD9V2QBRrS971AmC.9gseGbs1DWDJ2SrgAZUzEMkk5m/RqAe', 'parent');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `child_student_id` (`child_student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `parents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parents_ibfk_2` FOREIGN KEY (`child_student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
