-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2025 at 10:29 AM
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
-- Database: `web_assignment`
--

-- --------------------------------------------------------

--
-- Table structure for table `artwork`
--

CREATE TABLE `artwork` (
  `artwork_id` int(11) NOT NULL,
  `artwork_title` varchar(255) NOT NULL,
  `artwork_description` varchar(255) NOT NULL,
  `artwork_image` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `release_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artwork`
--

INSERT INTO `artwork` (`artwork_id`, `artwork_title`, `artwork_description`, `artwork_image`, `user_id`, `category_id`, `release_at`) VALUES
(4, 'Testing', 'gendaiiwdf', '1755504481_1200x630bb.jpg', 2, 1, '2025-08-18 08:08:01');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1, 'Graphic Design'),
(2, 'Illustration'),
(3, 'Photography'),
(4, '3D Art'),
(5, 'Advertising');

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `comment_id` int(11) NOT NULL,
  `artwork_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE `task` (
  `task_id` int(11) NOT NULL,
  `task_title` varchar(255) NOT NULL,
  `task_description` varchar(255) NOT NULL,
  `task_image` varchar(255) NOT NULL,
  `task_status` enum('pending','accepted','submitted') DEFAULT 'pending',
  `post_user_id` int(11) NOT NULL,
  `accepted_user_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `release_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task`
--

INSERT INTO `task` (`task_id`, `task_title`, `task_description`, `task_image`, `task_status`, `post_user_id`, `accepted_user_id`, `category_id`, `release_at`) VALUES
(3, 'testing', 'hello', '1755505585_03cdsaHx_400x400.jpg', 'pending', 2, NULL, 2, '2025-08-18 08:26:25');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_description` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) NOT NULL,
  `reset_token` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `user_description`, `email`, `password`, `profile_image`, `reset_token`) VALUES
(2, 'jiajun1', '', 'jiajun1@gmail.com', '$2y$10$ztKMhvw4Yg90VnB32CixaO9fSa2xL5fRX755yr91tizeWDTa/0ZF2', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `artwork`
--
ALTER TABLE `artwork`
  ADD PRIMARY KEY (`artwork_id`),
  ADD KEY `fk_artwork_user` (`user_id`),
  ADD KEY `fk_artwork_category` (`category_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `idx_comment_artwork` (`artwork_id`),
  ADD KEY `idx_comment_user` (`user_id`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `idx_task_user` (`accepted_user_id`),
  ADD KEY `idx_task_category` (`category_id`),
  ADD KEY `fk_post_user` (`post_user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `artwork`
--
ALTER TABLE `artwork`
  MODIFY `artwork_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `artwork`
--
ALTER TABLE `artwork`
  ADD CONSTRAINT `fk_artwork_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_artwork_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `fk_comment_artwork` FOREIGN KEY (`artwork_id`) REFERENCES `artwork` (`artwork_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `fk_post_user` FOREIGN KEY (`post_user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `fk_task_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_user` FOREIGN KEY (`accepted_user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
