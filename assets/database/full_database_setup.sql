-- Full database setup for Artizo + Phase 1 CSE6364 enhancements.
-- Use this file when importing into an empty phpMyAdmin database.
-- It includes the base tables from web_assignment.sql plus the new Phase 1 tables.
--
-- If your database already has the base Artizo tables, use
-- assets/database/phase1_database_migration.sql instead.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `user_description` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) NOT NULL DEFAULT '',
  `reset_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `category` (`category_id`, `category_name`) VALUES
(1, 'Graphic Design'),
(2, 'Illustration'),
(3, 'Photography'),
(4, '3D Art'),
(5, 'Advertising');

CREATE TABLE IF NOT EXISTS `artwork` (
  `artwork_id` int(11) NOT NULL AUTO_INCREMENT,
  `artwork_title` varchar(255) NOT NULL,
  `artwork_description` varchar(255) NOT NULL,
  `artwork_image` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `release_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`artwork_id`),
  KEY `fk_artwork_user` (`user_id`),
  KEY `fk_artwork_category` (`category_id`),
  CONSTRAINT `fk_artwork_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_artwork_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `task` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_title` varchar(255) NOT NULL,
  `task_description` varchar(255) NOT NULL,
  `task_image` varchar(255) NOT NULL,
  `task_solution` varchar(255) NOT NULL DEFAULT '',
  `task_status` enum('accept','accepted','submitted','') DEFAULT 'accept',
  `post_user_id` int(11) NOT NULL,
  `accepted_user_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `release_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`task_id`),
  KEY `idx_task_user` (`accepted_user_id`),
  KEY `idx_task_category` (`category_id`),
  KEY `fk_post_user` (`post_user_id`),
  CONSTRAINT `fk_post_user` FOREIGN KEY (`post_user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `fk_task_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_task_user` FOREIGN KEY (`accepted_user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `artwork_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`comment_id`),
  KEY `idx_comment_artwork` (`artwork_id`),
  KEY `idx_comment_user` (`user_id`),
  CONSTRAINT `fk_comment_artwork` FOREIGN KEY (`artwork_id`) REFERENCES `artwork` (`artwork_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `task_categories` (
  `task_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`task_category_id`),
  UNIQUE KEY `uniq_task_categories_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `task_categories` (`category_name`) VALUES
('Illustration'),
('Graphic Design'),
('Animation'),
('Digital Painting'),
('UI/UX Design'),
('Photography'),
('Other');

CREATE TABLE IF NOT EXISTS `saved_tasks` (
  `saved_task_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`saved_task_id`),
  UNIQUE KEY `uniq_saved_tasks_user_task` (`user_id`, `task_id`),
  KEY `idx_saved_tasks_user` (`user_id`),
  KEY `idx_saved_tasks_task` (`task_id`),
  CONSTRAINT `fk_saved_tasks_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_saved_tasks_task` FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `artwork_likes` (
  `artwork_like_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `artwork_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`artwork_like_id`),
  UNIQUE KEY `uniq_artwork_likes_user_artwork` (`user_id`, `artwork_id`),
  KEY `idx_artwork_likes_user` (`user_id`),
  KEY `idx_artwork_likes_artwork` (`artwork_id`),
  CONSTRAINT `fk_artwork_likes_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_artwork_likes_artwork` FOREIGN KEY (`artwork_id`) REFERENCES `artwork` (`artwork_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
