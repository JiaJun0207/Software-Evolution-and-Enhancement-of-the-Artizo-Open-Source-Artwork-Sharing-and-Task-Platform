-- Task Categorization and Filtering migration.
-- Run this after the base schema and Phase 1 migration.
-- It adds a task-specific category column while preserving the existing
-- legacy `task.category_id` relationship used elsewhere in the project.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

START TRANSACTION;

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
    AND COLUMN_NAME = 'task_category_id'
);

SET @sql := IF(
  @task_table_exists > 0 AND @task_category_column_exists = 0,
  'ALTER TABLE `task` ADD COLUMN `task_category_id` int(11) NULL AFTER `category_id`',
  'SELECT ''task table missing or task.task_category_id already exists; no ALTER TABLE needed'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @task_category_index_exists := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND INDEX_NAME = 'idx_task_task_category'
);

SET @sql := IF(
  @task_table_exists > 0 AND @task_category_index_exists = 0,
  'ALTER TABLE `task` ADD KEY `idx_task_task_category` (`task_category_id`)',
  'SELECT ''task table missing or task.task_category_id index already exists; no index needed'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @task_category_fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND CONSTRAINT_NAME = 'fk_task_task_category'
);

SET @sql := IF(
  @task_table_exists > 0 AND @task_category_fk_exists = 0,
  'ALTER TABLE `task` ADD CONSTRAINT `fk_task_task_category` FOREIGN KEY (`task_category_id`) REFERENCES `task_categories` (`task_category_id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'SELECT ''task table missing or task.task_category_id foreign key already exists; no FK needed'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @category_table_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'category'
);

SET @sql := IF(
  @task_table_exists > 0 AND @category_table_exists > 0,
  'UPDATE `task` t
   JOIN `category` c ON t.category_id = c.category_id
   JOIN `task_categories` tc ON tc.category_name = c.category_name
   SET t.task_category_id = tc.task_category_id
   WHERE t.task_category_id IS NULL',
  'SELECT ''Task or shared category table not found; skipped legacy task category backfill.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

COMMIT;
