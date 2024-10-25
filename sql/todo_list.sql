-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2024 at 04:29 PM
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
-- Database: `todo_list`
--

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `list_id` int(11) DEFAULT NULL,
  `task_name` varchar(100) DEFAULT NULL,
  `status` enum('completed','incomplete') DEFAULT 'incomplete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `list_id`, `task_name`, `status`) VALUES
(1, 1, 'php', 'completed'),
(2, 1, 'den', 'incomplete'),
(3, 2, 'tidur', 'completed'),
(4, 2, 'makan', 'completed'),
(5, 2, 'maen', 'completed'),
(31, 16, 'asd', 'completed'),
(34, 17, 'asdw', 'completed'),
(35, 17, 'ca', 'completed'),
(36, 17, 'asc', 'completed'),
(37, 17, 'asc', 'completed'),
(38, 17, 'asc', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `todo_lists`
--

CREATE TABLE `todo_lists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `todo_lists`
--

INSERT INTO `todo_lists` (`id`, `user_id`, `title`, `created_at`) VALUES
(1, 1, 'Belajar ngoding', '2024-10-21 07:29:53'),
(2, 1, 'minggu', '2024-10-21 07:43:41'),
(10, 9, 'minggu', '2024-10-23 06:02:16'),
(16, 9, 'Asep', '2024-10-24 20:04:52'),
(17, 14, '123', '2024-10-24 21:06:03'),
(18, 18, 'asd', '2024-10-25 13:21:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `profile_picture`) VALUES
(9, 'admin', 'gartomjunior@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'uploads/Lugia PC.png'),
(10, 'reza', '', '$2y$10$QChuxbvcueea/FBO9a24qOBaGdpZlZvkRQHHDh8I8seWqhWzEiaz.', NULL),
(11, 'denito', '', '$2y$10$eGWiq3//FaTQsMrWJbnNw.et7uVLsEHD3DuZZZW06ndzNpwmQ5n7S', NULL),
(12, 'liantampan', '', '$2y$10$JusS25Lcu.w3t5Vbh19OMu54H2fIa5qhj7A1J9Qb8aiNIw7thpMj2', NULL),
(13, 'iwan', '', '$2y$10$BAIRFOYbNdSEyTsa/TI5JeI6rqgxejMT07K3ST3FBKL9.SIyKYKOa', NULL),
(14, 'mother father', '', '$2y$10$umgEnN4lNcKe7FUy76Q2NOr5ArmNWdE1Gv5hhnDWiG7gVkXcvfUAS', NULL),
(15, 'asepkopling', '', '$2y$10$2zBTYPvKWMWKKJTqcLxNLewsTGeuidlTVMqZqN4a496ZtFdpG1C6m', NULL),
(16, 'w', '', '$2y$10$C/nDIpdpC80s6SVlBWg5gefGGiebyTBlalAV.2runka7KdDlwFxee', NULL),
(17, 'gartomjunior@gmail.com', '', '$2y$10$Vsm1UCs2jzatY8pGi96zreirIfamLr4VCa4YA9N8qK210kCRPvy/u', NULL),
(18, 'asd', '', '$2y$10$piNdHNbj7FuOhoR1Wobs2OhLVmfFRD6gZIlrX.fzyQKFlotZNmdaO', NULL),
(19, '1234', '', '$2y$10$Rm5fJehEGfu7v6RmkmA8buS6Mr07ayJ2TBAVD4tcrqyMa9cc15wYe', NULL),
(20, 'qwer', '', '$2y$10$vfqThXBRf6I6r2I3yhScPuWgj1o8LH47cORHdmxqAycbERuhBLzWW', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `list_id` (`list_id`);

--
-- Indexes for table `todo_lists`
--
ALTER TABLE `todo_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `todo_lists`
--
ALTER TABLE `todo_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `todo_lists` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
