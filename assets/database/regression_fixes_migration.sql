-- Regression fixes migration for Artizo (admin support, task submissions,
-- forgot-password, ownership and category unification work).
--
-- Run this AFTER the base schema and the existing Phase 1 / regression
-- migrations. It is idempotent: every change is guarded so the file can be
-- imported more than once without errors and without removing existing data.
--
-- Confirmed table names (singular): `user`, `task`, `support_tickets`,
-- `category`. No `users` table exists in this project.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

START TRANSACTION;

-- --------------------------------------------------------
-- 1. Admin role flag on the user table.
-- --------------------------------------------------------

SET @user_table_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'user'
);

SET @is_admin_column_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'user'
    AND COLUMN_NAME = 'is_admin'
);

SET @sql := IF(
  @user_table_exists > 0 AND @is_admin_column_exists = 0,
  'ALTER TABLE `user` ADD COLUMN `is_admin` TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT ''user table missing or user.is_admin already exists; no ALTER TABLE needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 2. Seed one admin account.
--    Username : admin
--    Email    : admin@artizo.local
--    Password : Admin@123  (stored ONLY as a bcrypt hash, never plaintext)
--    The hash below was produced with PHP password_hash('Admin@123', PASSWORD_BCRYPT).
--    INSERT IGNORE + UNIQUE-safe update keeps this idempotent and guarantees
--    the account ends up flagged as admin.
-- --------------------------------------------------------

SET @sql := IF(
  @user_table_exists > 0,
  'INSERT INTO `user` (`user_name`, `user_description`, `email`, `password`, `profile_image`, `is_admin`)
   SELECT ''admin'', ''System administrator'', ''admin@artizo.local'',
          ''$2y$10$vBY5GhMN0MEJZj5.nj71H.tY3r3InXE9mBpJbgzRDO7k8p2C4CIZq'', '''', 1
   FROM DUAL
   WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE `user_name` = ''admin'')',
  'SELECT ''user table missing; skipped admin seed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure an existing `admin` user is flagged as administrator.
SET @sql := IF(
  @user_table_exists > 0,
  'UPDATE `user` SET `is_admin` = 1 WHERE `user_name` = ''admin''',
  'SELECT ''user table missing; skipped admin flag update.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 3. Support ticket response / status handling columns.
-- --------------------------------------------------------

SET @support_table_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'support_tickets'
);

SET @support_response_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'support_tickets'
    AND COLUMN_NAME = 'admin_response'
);

SET @sql := IF(
  @support_table_exists > 0 AND @support_response_exists = 0,
  'ALTER TABLE `support_tickets`
     ADD COLUMN `admin_response` TEXT NULL AFTER `status`,
     ADD COLUMN `responded_at` TIMESTAMP NULL DEFAULT NULL AFTER `admin_response`,
     ADD COLUMN `responded_by` INT(11) NULL DEFAULT NULL AFTER `responded_at`',
  'SELECT ''support_tickets table missing or response columns already exist; no ALTER TABLE needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 4. Task submissions table.
--    Stores each submission made by a user who accepted a task so the task
--    poster (and admin) can review who submitted what.
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `task_submissions` (
  `submission_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `submitter_user_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'submitted',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`submission_id`),
  KEY `idx_task_submissions_task` (`task_id`),
  KEY `idx_task_submissions_user` (`submitter_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @task_table_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
);

SET @task_submissions_task_fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task_submissions'
    AND CONSTRAINT_NAME = 'fk_task_submissions_task'
);

SET @sql := IF(
  @task_table_exists > 0 AND @task_submissions_task_fk_exists = 0,
  'ALTER TABLE `task_submissions` ADD CONSTRAINT `fk_task_submissions_task` FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT ''task table missing or task_submissions task foreign key already exists; no FK needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @task_submissions_user_fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task_submissions'
    AND CONSTRAINT_NAME = 'fk_task_submissions_user'
);

SET @sql := IF(
  @user_table_exists > 0 AND @task_submissions_user_fk_exists = 0,
  'ALTER TABLE `task_submissions` ADD CONSTRAINT `fk_task_submissions_user` FOREIGN KEY (`submitter_user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT ''user table missing or task_submissions user foreign key already exists; no FK needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 5. Remove unused legacy task category schema.
--    Task categories now use the shared `category` table via `task.category_id`.
--    No application code references `task.task_category_id` or `task_categories`.
--    Each step is guarded so the migration is idempotent and safe to re-run.
--    Order: drop FK -> drop index -> drop column -> drop table.
--    The shared `category` table and `task.category_id` are NOT touched.
-- --------------------------------------------------------

-- Drop the foreign key on task.task_category_id (if present).
SET @fk_task_task_category_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND CONSTRAINT_NAME = 'fk_task_task_category'
);

SET @sql := IF(
  @fk_task_task_category_exists > 0,
  'ALTER TABLE `task` DROP FOREIGN KEY `fk_task_task_category`',
  'SELECT ''fk_task_task_category not present; no FK drop needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop the index on task.task_category_id (if present).
SET @idx_task_task_category_exists := (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND INDEX_NAME = 'idx_task_task_category'
);

SET @sql := IF(
  @idx_task_task_category_exists > 0,
  'ALTER TABLE `task` DROP INDEX `idx_task_task_category`',
  'SELECT ''idx_task_task_category not present; no index drop needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop the column task.task_category_id (if present).
SET @task_category_id_column_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND COLUMN_NAME = 'task_category_id'
);

SET @sql := IF(
  @task_category_id_column_exists > 0,
  'ALTER TABLE `task` DROP COLUMN `task_category_id`',
  'SELECT ''task.task_category_id not present; no column drop needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop the legacy task_categories table (if present).
SET @task_categories_table_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task_categories'
);

SET @sql := IF(
  @task_categories_table_exists > 0,
  'DROP TABLE `task_categories`',
  'SELECT ''task_categories table not present; no table drop needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 6. Multi-user task workflow.
--    A posted task can be accepted and submitted to by many users.
--    Per-user accept/submit state lives in `task_acceptances`; the poster
--    controls board visibility via `task.task_state`. Legacy
--    `task.task_status` / `task.accepted_user_id` are kept for backward
--    compatibility but no longer gate the task board.
-- --------------------------------------------------------

-- 6a. Poster-level lifecycle column on `task`.
SET @task_state_column_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'task'
    AND COLUMN_NAME = 'task_state'
);

SET @sql := IF(
  @task_state_column_exists = 0,
  'ALTER TABLE `task` ADD COLUMN `task_state` ENUM(''open'',''closed'',''completed'') NOT NULL DEFAULT ''open'' AFTER `task_status`',
  'SELECT ''task.task_state already exists; no column add needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6b. Per-user acceptance state table.
CREATE TABLE IF NOT EXISTS `task_acceptances` (
  `acceptance_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` ENUM('accepted','cancelled','submitted') NOT NULL DEFAULT 'accepted',
  `accepted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`acceptance_id`),
  UNIQUE KEY `uniq_task_acceptances_task_user` (`task_id`, `user_id`),
  KEY `idx_task_acceptances_task` (`task_id`),
  KEY `idx_task_acceptances_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @task_table_exists := (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'task'
);
SET @user_table_exists := (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user'
);

SET @ta_task_fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'task_acceptances'
    AND CONSTRAINT_NAME = 'fk_task_acceptances_task'
);
SET @sql := IF(
  @task_table_exists > 0 AND @ta_task_fk_exists = 0,
  'ALTER TABLE `task_acceptances` ADD CONSTRAINT `fk_task_acceptances_task` FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT ''task table missing or task_acceptances task FK already exists.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @ta_user_fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'task_acceptances'
    AND CONSTRAINT_NAME = 'fk_task_acceptances_user'
);
SET @sql := IF(
  @user_table_exists > 0 AND @ta_user_fk_exists = 0,
  'ALTER TABLE `task_acceptances` ADD CONSTRAINT `fk_task_acceptances_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE',
  'SELECT ''user table missing or task_acceptances user FK already exists.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6c. One active submission per user per task.
SET @task_submissions_unique_exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'task_submissions'
    AND INDEX_NAME = 'uniq_task_submissions_task_user'
);
SET @sql := IF(
  @task_submissions_unique_exists = 0,
  'ALTER TABLE `task_submissions` ADD UNIQUE KEY `uniq_task_submissions_task_user` (`task_id`, `submitter_user_id`)',
  'SELECT ''task_submissions unique key already exists; no add needed.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6d. Backfill: every existing task is open unless a poster later closes it.
SET @sql := IF(
  @task_table_exists > 0,
  'UPDATE `task` SET `task_state` = ''open'' WHERE `task_state` IS NULL',
  'SELECT ''task table missing; skipped task_state backfill.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6e. Backfill task_acceptances from the legacy single-accepter columns.
--     Maps task_status submitted->submitted, otherwise accepted. Idempotent via
--     the unique (task_id, user_id) key + NOT EXISTS guard.
SET @sql := IF(
  @task_table_exists > 0,
  'INSERT INTO `task_acceptances` (`task_id`, `user_id`, `status`, `accepted_at`)
   SELECT t.task_id, t.accepted_user_id,
          IF(LOWER(t.task_status) = ''submitted'', ''submitted'', ''accepted''),
          t.release_at
   FROM `task` t
   WHERE t.accepted_user_id IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM `task_acceptances` ta
       WHERE ta.task_id = t.task_id AND ta.user_id = t.accepted_user_id
     )',
  'SELECT ''task table missing; skipped task_acceptances backfill.'' AS migration_note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

COMMIT;
