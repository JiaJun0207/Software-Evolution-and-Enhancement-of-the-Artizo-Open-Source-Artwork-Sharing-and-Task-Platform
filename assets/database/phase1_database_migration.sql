-- Phase 1 database migration for CSE6364 Artizo enhancements
-- Compatible with MySQL/MariaDB in XAMPP/phpMyAdmin.
--
-- Assumptions from the existing schema:
-- 1. The project database is `web_assignment`.
-- 2. Existing core tables use singular names: `user`, `task`, `artwork`, `category`.
-- 3. The existing `task` table already has `category_id` referencing `category(category_id)`.
-- 4. This migration keeps existing tables and data intact.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

START TRANSACTION;

-- --------------------------------------------------------
-- Task-specific category lookup for the approved task filtering work.
-- The current application already uses `category` for both artwork and task.
-- This table is additive and allows future task UI to use task-specific labels
-- without removing the existing `task.category_id` relationship.
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Compatibility seed for the existing shared `category` table.
-- Because the current `task.category_id` foreign key points to `category`,
-- add missing approved task category names there too. Existing rows are kept.
-- --------------------------------------------------------

INSERT INTO `category` (`category_name`)
SELECT 'Illustration'
WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `category_name` = 'Illustration');

INSERT INTO `category` (`category_name`)
SELECT 'Graphic Design'
WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `category_name` = 'Graphic Design');

INSERT INTO `category` (`category_name`)
SELECT 'Animation'
WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `category_name` = 'Animation');

INSERT INTO `category` (`category_name`)
SELECT 'Digital Painting'
WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `category_name` = 'Digital Painting');

INSERT INTO `category` (`category_name`)
SELECT 'UI/UX Design'
WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `category_name` = 'UI/UX Design');

INSERT INTO `category` (`category_name`)
SELECT 'Photography'
WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `category_name` = 'Photography');

INSERT INTO `category` (`category_name`)
SELECT 'Other'
WHERE NOT EXISTS (SELECT 1 FROM `category` WHERE `category_name` = 'Other');

-- --------------------------------------------------------
-- Ensure `task.category_id` exists for task categorization.
-- The current repository schema already has this column, so this normally
-- reports that no change was needed. It is nullable only when added to an
-- older database to avoid breaking existing task rows during migration.
-- --------------------------------------------------------

SET @task_category_column_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND COLUMN_NAME = 'category_id'
);

SET @sql := IF(
  @task_category_column_exists = 0,
  'ALTER TABLE `task` ADD COLUMN `category_id` int(11) NULL AFTER `accepted_user_id`',
  'SELECT ''task.category_id already exists; no ALTER TABLE needed'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @task_category_index_exists := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND COLUMN_NAME = 'category_id'
);

SET @sql := IF(
  @task_category_index_exists = 0,
  'ALTER TABLE `task` ADD KEY `idx_task_category` (`category_id`)',
  'SELECT ''task.category_id is already indexed; no index needed'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @task_category_fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND COLUMN_NAME = 'category_id'
    AND REFERENCED_TABLE_NAME = 'category'
    AND REFERENCED_COLUMN_NAME = 'category_id'
);

SET @sql := IF(
  @task_category_fk_exists = 0,
  'ALTER TABLE `task` ADD CONSTRAINT `fk_task_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT ''task.category_id already has a category foreign key; no FK needed'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- Saved tasks: one user can save many tasks, and a task can be saved by many users.
-- Duplicate saves are prevented by the unique user/task key.
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Artwork likes: one user can like many artworks, and an artwork can be liked
-- by many users. Duplicate likes are prevented by the unique user/artwork key.
-- --------------------------------------------------------

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
