-- =====================================================================
-- SUPERSEDED NOTICE
-- This historical migration creates the legacy `task_categories` table.
-- The project now uses the shared `category` table for Explore, Task,
-- Post Task, and Post Artwork (via `task.category_id`). The legacy
-- `task_categories` table and `task.task_category_id` column were removed
-- in `assets/database/regression_fixes_migration.sql`, and fresh installs
-- via `full_database_setup.sql` no longer create them.
-- This file is kept only as a historical record; do not run it after the
-- regression fixes migration, as it would re-create the removed legacy items.
-- =====================================================================
--
-- Phase 1 database migration for CSE6364 Artizo enhancements.
-- Compatible with MySQL/MariaDB in XAMPP/phpMyAdmin.
--
-- Use this after the base Artizo schema exists. If a local database is
-- partially imported, this migration avoids hard failures by creating the new
-- feature tables first and adding compatibility data/foreign keys only when
-- the referenced base tables are present.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

START TRANSACTION;

-- --------------------------------------------------------
-- Task-specific category lookup.
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
-- Optional compatibility seed for the shared artwork/category table.
-- --------------------------------------------------------

SET @category_table_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'category'
);

SET @sql := IF(
  @category_table_exists > 0,
  'INSERT INTO `category` (`category_name`)
   SELECT seed.category_name
   FROM (
     SELECT ''Illustration'' AS category_name
     UNION SELECT ''Graphic Design''
     UNION SELECT ''Animation''
     UNION SELECT ''Digital Painting''
     UNION SELECT ''UI/UX Design''
     UNION SELECT ''Photography''
     UNION SELECT ''Other''
   ) seed
   WHERE NOT EXISTS (
     SELECT 1 FROM `category` c WHERE c.category_name = seed.category_name
   )',
  'SELECT ''category table not found; skipped shared category seed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- Optional legacy task.category_id compatibility check.
-- --------------------------------------------------------

SET @task_table_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
);

SET @task_category_column_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND COLUMN_NAME = 'category_id'
);

SET @sql := IF(
  @task_table_exists > 0 AND @task_category_column_exists = 0,
  'ALTER TABLE `task` ADD COLUMN `category_id` int(11) NULL AFTER `accepted_user_id`',
  'SELECT ''task table missing or task.category_id already exists; no ALTER TABLE needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @task_category_index_exists := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND INDEX_NAME = 'idx_task_category'
);

SET @sql := IF(
  @task_table_exists > 0 AND @task_category_index_exists = 0,
  'ALTER TABLE `task` ADD KEY `idx_task_category` (`category_id`)',
  'SELECT ''task table missing or task.category_id index already exists; no index needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @task_category_fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND CONSTRAINT_NAME = 'fk_task_category'
);

SET @sql := IF(
  @task_table_exists > 0 AND @category_table_exists > 0 AND @task_category_fk_exists = 0,
  'ALTER TABLE `task` ADD CONSTRAINT `fk_task_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT ''task/category table missing or task.category_id foreign key already exists; no FK needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- Saved tasks.
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
  KEY `idx_saved_tasks_task` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @user_table_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'user'
);

SET @saved_tasks_user_fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'saved_tasks'
    AND CONSTRAINT_NAME = 'fk_saved_tasks_user'
);

SET @sql := IF(
  @user_table_exists > 0 AND @saved_tasks_user_fk_exists = 0,
  'ALTER TABLE `saved_tasks` ADD CONSTRAINT `fk_saved_tasks_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT ''user table missing or saved_tasks user foreign key already exists; no FK needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @saved_tasks_task_fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'saved_tasks'
    AND CONSTRAINT_NAME = 'fk_saved_tasks_task'
);

SET @sql := IF(
  @task_table_exists > 0 AND @saved_tasks_task_fk_exists = 0,
  'ALTER TABLE `saved_tasks` ADD CONSTRAINT `fk_saved_tasks_task` FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT ''task table missing or saved_tasks task foreign key already exists; no FK needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- Artwork likes.
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `artwork_likes` (
  `artwork_like_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `artwork_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`artwork_like_id`),
  UNIQUE KEY `uniq_artwork_likes_user_artwork` (`user_id`, `artwork_id`),
  KEY `idx_artwork_likes_user` (`user_id`),
  KEY `idx_artwork_likes_artwork` (`artwork_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @artwork_table_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'artwork'
);

SET @artwork_likes_user_fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'artwork_likes'
    AND CONSTRAINT_NAME = 'fk_artwork_likes_user'
);

SET @sql := IF(
  @user_table_exists > 0 AND @artwork_likes_user_fk_exists = 0,
  'ALTER TABLE `artwork_likes` ADD CONSTRAINT `fk_artwork_likes_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT ''user table missing or artwork_likes user foreign key already exists; no FK needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @artwork_likes_artwork_fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'artwork_likes'
    AND CONSTRAINT_NAME = 'fk_artwork_likes_artwork'
);

SET @sql := IF(
  @artwork_table_exists > 0 AND @artwork_likes_artwork_fk_exists = 0,
  'ALTER TABLE `artwork_likes` ADD CONSTRAINT `fk_artwork_likes_artwork` FOREIGN KEY (`artwork_id`) REFERENCES `artwork` (`artwork_id`) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT ''artwork table missing or artwork_likes artwork foreign key already exists; no FK needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

COMMIT;
