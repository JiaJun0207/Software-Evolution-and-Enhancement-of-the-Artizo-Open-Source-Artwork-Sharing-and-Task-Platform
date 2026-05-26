# Software Evolution and Enhancement of the Artizo Open-Source Artwork Sharing and Task Platform

This repository contains the Artizo PHP/MySQL project for the CSE6364 Software Evolution and Maintenance assignment.

## Local Environment

- XAMPP
- PHP
- MySQL/MariaDB
- phpMyAdmin
- Bootstrap 5 from CDN
- Vanilla JavaScript

The current database connection is configured in `config.php`:

- Host: `localhost`
- User: `root`
- Password: empty string
- Database: `web_assignment`

## Database Setup

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin.
3. Create a database named `web_assignment` if it does not already exist.
4. Import the base schema from:

   `assets/database/web_assignment.sql`

5. Apply the Phase 1 migration from:

   `assets/database/phase1_database_migration.sql`

## Phase 1 Database Migration

The Phase 1 migration prepares the database for the approved proposal features without changing frontend behavior yet.

It adds:

- `saved_tasks`
  - Stores tasks saved by users.
  - Uses `user_id` and `task_id` foreign keys.
  - Prevents duplicate saves with a unique key on `(user_id, task_id)`.

- `task_categories`
  - Stores task-specific category labels for the approved task categorization feature.
  - Seeded with:
    - Illustration
    - Graphic Design
    - Animation
    - Digital Painting
    - UI/UX Design
    - Photography
    - Other

- `artwork_likes`
  - Stores artwork likes by users.
  - Uses `user_id` and `artwork_id` foreign keys.
  - Prevents duplicate likes with a unique key on `(user_id, artwork_id)`.

It also checks whether `task.category_id` exists. In the current schema, this column already exists and references the shared `category` table, so no destructive change is needed. For compatibility with the existing code, the migration also inserts any missing approved task category names into the existing `category` table.

## Applying the Migration in phpMyAdmin

1. Open phpMyAdmin.
2. Select the `web_assignment` database.
3. Click the `Import` tab.
4. Choose `assets/database/phase1_database_migration.sql`.
5. Click `Import`.

Alternative:

1. Select the `web_assignment` database.
2. Click the `SQL` tab.
3. Paste the contents of `assets/database/phase1_database_migration.sql`.
4. Click `Go`.

## Verifying the Database Setup

After applying the migration, run these SQL checks in phpMyAdmin:

```sql
SHOW TABLES LIKE 'saved_tasks';
SHOW TABLES LIKE 'task_categories';
SHOW TABLES LIKE 'artwork_likes';
```

Confirm the new table structures:

```sql
DESCRIBE saved_tasks;
DESCRIBE task_categories;
DESCRIBE artwork_likes;
```

Confirm task category seed data:

```sql
SELECT * FROM task_categories ORDER BY task_category_id;
```

Confirm the existing task table has category support:

```sql
DESCRIBE task;
```

Look for the `category_id` column.

Confirm foreign keys:

```sql
SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'web_assignment'
  AND TABLE_NAME IN ('saved_tasks', 'artwork_likes', 'task');
```

## Phase 1 Assumptions

- Existing project tables use singular names: `user`, `task`, `artwork`, and `category`.
- The existing `task` table already includes `category_id`.
- Existing data must be preserved.
- No frontend or PHP feature behavior is implemented in Phase 1.
- Future implementation phases should use prepared statements for all new SQL queries.

## Save Task Feature

The Save Task feature uses the `saved_tasks` table from the Phase 1 migration.

Files:

- `task.php`: Shows Save/Saved bookmark buttons on task cards and toggles state with `fetch()`.
- `save_task_toggle.php`: JSON endpoint for saving and unsaving tasks.
- `saved_tasks.php`: Lists tasks saved by the current logged-in user.

Manual testing notes:

1. Log in as a user.
2. Open `task.php`.
3. Click `Save` on a task card and confirm the button changes to `Saved` without a page reload.
4. Refresh `task.php` and confirm the same task still shows `Saved`.
5. Open `saved_tasks.php` and confirm the saved task appears.
6. Click `Saved` on the saved task page and confirm the task is removed from the list.
7. Verify duplicate saves are prevented by the unique key on `(user_id, task_id)`.
8. Log in as a different user and confirm saved tasks are user-specific.
