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

For an empty database, import this all-in-one setup file:

   `assets/database/full_database_setup.sql`

This file includes the original base tables and the Phase 1 enhancement tables.

For a database that already has the original Artizo tables, import the base schema first if needed:

   `assets/database/web_assignment.sql`

Then apply the Phase 1 migration from:

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

It also checks whether `task.category_id` exists. In the repository schema, this column already exists and references the shared `category` table, so no destructive change is needed. Some local databases may not have a shared `category` table; in that case, the migration skips the compatibility seed safely and keeps the new `task_categories` table as the task-specific category source.

## Applying the Migration in phpMyAdmin

Use `assets/database/full_database_setup.sql` if your selected database is empty or missing base tables such as `user`, `task`, `artwork`, or `category`.

Use `assets/database/phase1_database_migration.sql` only when the base Artizo schema already exists.

1. Open phpMyAdmin.
2. Select the `web_assignment` database.
3. Click the `Import` tab.
4. Choose the correct SQL file:
   - Empty database: `assets/database/full_database_setup.sql`
   - Existing base database: `assets/database/phase1_database_migration.sql`
5. Click `Import`.

If an earlier import stopped with an error after creating `task_categories` or `saved_tasks`, import the same migration again after pulling the fixed file. The migration uses `CREATE TABLE IF NOT EXISTS`, duplicate-safe seed statements, and conditional foreign-key creation.

If your selected database does not yet contain the base Artizo tables (`user`, `task`, `artwork`, or `category`), the migration now creates the new feature tables and skips unavailable foreign keys. Import the base schema first, then rerun this migration to add the foreign keys.

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

## Task Categorization and Filtering Feature

The task categorization feature uses `task_categories` and the task-specific `task.task_category_id` column. This keeps the older shared `task.category_id` field available for existing project behavior.

Before testing this feature on an existing database, apply:

`assets/database/task_category_filter_migration.sql`

Files:

- `upload_task.php`: Loads task categories from `task_categories` and displays them in a dropdown.
- `upload_task_form.php`: Validates the selected task category and stores it with the new task using prepared statements.
- `task.php`: Shows task category labels and category filter buttons.
- `task_filter.php`: AJAX endpoint for category/search filtering.

Budget filtering is not implemented because the current schema does not contain budget, price, or amount fields for tasks.

Manual testing notes:

1. Apply `assets/database/task_category_filter_migration.sql`.
2. Log in and open `upload_task.php`.
3. Confirm the category dropdown shows task categories.
4. Create a task with a selected category.
5. Open `task.php` and confirm the task card shows the category label.
6. Click a category filter button and confirm the task board updates without a full page reload.
7. Combine search text with a category filter.
8. Confirm existing task card links, Save buttons, and Accepted Task navigation still work.

## Artwork Like Feature

The artwork like feature uses the existing `artwork_likes` table from the Phase 1 migration. Each row stores one user/artwork like, and the unique `(user_id, artwork_id)` key prevents duplicate likes.

Files:

- `artwork_like_toggle.php`: JSON endpoint for liking and unliking artworks.
- `artwork_like.js`: Shared AJAX handler for artwork like buttons.
- `explore.php`: Shows Like/Unlike buttons and like counts on artwork cards.
- `artwork_detail.php`: Shows Like/Unlike button and count on the artwork detail page.
- `user_profile.php`: Shows Like/Unlike buttons and counts on profile artwork cards.

Manual testing notes:

1. Confirm `artwork_likes` exists by running `SHOW TABLES LIKE 'artwork_likes';`.
2. Log in and open `explore.php`.
3. Click `Like` on an artwork and confirm the button changes to `Unlike` and the count updates without a page reload.
4. Open the same artwork in `artwork_detail.php` and confirm the liked state is active.
5. Click `Unlike` and confirm the count decreases by one.
6. Refresh the page and confirm the liked/unliked state persists.
7. Open `user_profile.php` and confirm artwork card like buttons behave consistently.

## AJAX Comment Submission and Refresh

The AJAX comment feature uses the existing `comment` table. The current schema stores comments for artworks through `comment.artwork_id`, so this implementation supports artwork comments only.

Files:

- `submit_comment.php`: JSON endpoint for validating and inserting a new artwork comment.
- `fetch_comments.php`: JSON endpoint for fetching comments newer than a provided comment ID.
- `artwork_detail.php`: Submits comments with `fetch()`, appends new comments without a page reload, and polls every 5 seconds.

JSON response examples:

- Submit success: `{ "success": true, "message": "Comment submitted.", "comment": { ... } }`
- Fetch success: `{ "success": true, "comments": [ ... ], "latest_comment_id": 12 }`
- Error: `{ "success": false, "message": "..." }`

Manual testing notes:

1. Log in and open `artwork_detail.php?id=<existing artwork id>`.
2. Type a comment and press Enter or click `Submit Comment`.
3. Confirm the new comment appears immediately without a full page reload.
4. Refresh the page and confirm the comment was preserved in the database.
5. Open the same artwork in another browser/session, add a comment, and confirm the first page shows it within 5 seconds.
6. Confirm duplicate comments are not appended when polling runs after a local submission.
7. Try an invalid artwork ID and confirm the endpoint returns an error response.
