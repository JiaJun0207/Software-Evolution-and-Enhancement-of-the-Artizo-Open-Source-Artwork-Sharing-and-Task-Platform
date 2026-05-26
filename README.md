# Artizo Software Evolution and Enhancement

This repository contains the CSE6364 Software Evolution and Maintenance assignment project: **Software Evolution and Enhancement of the Artizo Open-Source Artwork Sharing and Task Platform**.

Artizo is a PHP/MySQL web application for sharing artwork and posting creative tasks. The goal of this implementation branch is not to rewrite the system, but to evolve the existing codebase through focused, maintainable improvements that preserve current behavior.

## Original System

The original Artizo system provides:

- User registration, login, logout, and profile management.
- Artwork upload and gallery browsing by category.
- Artwork detail pages with descriptions and comments.
- Task posting and task board browsing.
- Task detail pages and task acceptance/submission flow.
- Admin pages for managing users, artworks, tasks, and comments.

The project uses a traditional XAMPP stack with PHP pages, MySQL tables, Bootstrap 5, shared CSS, and vanilla JavaScript.

## Technology Stack

- PHP
- MySQL/MariaDB
- HTML
- CSS
- Bootstrap 5
- Vanilla JavaScript
- XAMPP local environment
- phpMyAdmin for database import and inspection

## Enhancement Summary

Six approved proposal improvements were implemented:

1. **Responsive layout fix for 1920x1080**
   - Large-screen container widths, card sizing, and gallery/task spacing were adjusted so the interface does not stretch too widely on 1920x1080 displays.

2. **Text contrast and readability improvement**
   - Weak text contrast was improved for labels, descriptions, captions, buttons, links, and placeholder-style content while preserving the original visual style.

3. **Save Task feature**
   - Logged-in users can save and unsave tasks from task cards.
   - Saved tasks are stored in the `saved_tasks` table.
   - Duplicate saves are prevented by a unique `(user_id, task_id)` key.
   - Users can view saved tasks on `saved_tasks.php`.

4. **Task categorization and filtering**
   - Task creation now includes a category dropdown loaded from `task_categories`.
   - The selected category is stored on the task record.
   - Task cards display category labels.
   - Task board filtering is performed with AJAX through `task_filter.php`.

5. **Artwork Like feature**
   - Users can like and unlike artwork from gallery/profile cards and artwork detail pages.
   - Likes are stored in `artwork_likes`.
   - Like counts and active states update immediately through AJAX.

6. **AJAX comment submission and near-real-time refresh**
   - Artwork comments can be submitted without a full page reload.
   - New comments are appended immediately.
   - The artwork detail page polls every 5 seconds for newer comments.
   - Duplicate comments are avoided by tracking `comment_id` values in the DOM.

## Installation with XAMPP

1. Install XAMPP.
2. Copy or clone this repository into the XAMPP `htdocs` directory.
   - Example path: `C:\xampp\htdocs\Software-Evolution-and-Enhancement-of-the-Artizo-Open-Source-Artwork-Sharing-and-Task-Platform`
3. Start **Apache** and **MySQL** from the XAMPP Control Panel.
4. Open phpMyAdmin at `http://localhost/phpmyadmin`.
5. Create a database for the project.
   - Recommended name: `web_assignment`
   - Existing local assignment name also supported: `software_evo_assignment`

The database connection is configured in `config.php`.

Default connection values:

- Host: `localhost`
- User: `root`
- Password: empty string
- Database: `web_assignment`, with fallback to `software_evo_assignment`

To force a specific database name, set the `ARTIZO_DB_NAME` environment variable before running the project.

## Database Setup

Use one of the following setup paths.

### Option A: Empty Database

If the selected database is empty, import:

```text
assets/database/full_database_setup.sql
```

This file creates the original base tables and the enhancement tables.

### Option B: Existing Artizo Database

If the original Artizo schema is already present, import:

```text
assets/database/phase1_database_migration.sql
```

This migration adds the new enhancement tables and safely adds foreign keys when the referenced base tables exist.

### Option C: Base SQL Then Migration

If the database does not have the original tables yet, first import:

```text
assets/database/web_assignment.sql
```

Then import:

```text
assets/database/phase1_database_migration.sql
```

For task categorization on an existing database, also apply:

```text
assets/database/task_category_filter_migration.sql
```

## Database Objects Added

- `saved_tasks`
  - Stores saved task records.
  - Uses `user_id` and `task_id`.
  - Prevents duplicates with `UNIQUE (user_id, task_id)`.

- `task_categories`
  - Stores task category labels.
  - Seed values:
    - Illustration
    - Graphic Design
    - Animation
    - Digital Painting
    - UI/UX Design
    - Photography
    - Other

- `artwork_likes`
  - Stores artwork likes.
  - Uses `user_id` and `artwork_id`.
  - Prevents duplicates with `UNIQUE (user_id, artwork_id)`.

- `task.task_category_id`
  - Stores the selected task-specific category when the task category migration is applied.
  - The existing `task.category_id` field is preserved for compatibility with the original system.

## Verifying the Database

Run these SQL checks in phpMyAdmin:

```sql
SHOW TABLES LIKE 'saved_tasks';
SHOW TABLES LIKE 'task_categories';
SHOW TABLES LIKE 'artwork_likes';
```

Check table structures:

```sql
DESCRIBE saved_tasks;
DESCRIBE task_categories;
DESCRIBE artwork_likes;
DESCRIBE task;
```

Check task category seed data:

```sql
SELECT * FROM task_categories ORDER BY task_category_id;
```

Check foreign keys:

```sql
SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('saved_tasks', 'artwork_likes', 'task');
```

## Running Locally

1. Start Apache and MySQL in XAMPP.
2. Confirm the database has been imported.
3. Open the project in a browser:

```text
http://localhost/Software-Evolution-and-Enhancement-of-the-Artizo-Open-Source-Artwork-Sharing-and-Task-Platform/
```

4. Register a user or log in with an existing user from the imported database.
5. Use the navigation bar to access artwork, task, profile, and support pages.

## Testing the New Features

Detailed test cases are provided in `TESTING.md`. The summary below can be used for quick manual verification.

### Responsive Layout

1. Open the homepage, explore page, task board, artwork detail page, and profile page.
2. Test at 1366x768, 1440x900, 1600x900, and 1920x1080.
3. Confirm cards and containers do not stretch too wide and text does not overlap.

### Text Contrast

1. Review category labels, artwork captions, task descriptions, card subtitles, buttons, links, and placeholders.
2. Confirm text is readable against its background.
3. Confirm hover/focus states remain visible.

### Save Task

1. Log in.
2. Open `task.php`.
3. Click `Save` on a task card.
4. Confirm the button changes to `Saved` without a page reload.
5. Refresh the page and confirm the saved state remains.
6. Open `saved_tasks.php` and confirm the saved task is listed.

### Task Categorization and Filtering

1. Open `upload_task.php`.
2. Confirm the category dropdown is populated.
3. Create a task with a selected category.
4. Open `task.php`.
5. Confirm the category label appears on the task card.
6. Click a category filter and confirm the task list updates without a full page reload.

### Artwork Like

1. Open `explore.php`.
2. Click `Like` on an artwork.
3. Confirm the button state and count update immediately.
4. Open the same artwork detail page.
5. Confirm the liked state and count are still correct.
6. Click `Unlike` and confirm the count decreases.

### AJAX Comments

1. Open `artwork_detail.php?id=<existing artwork id>`.
2. Enter a comment and submit it.
3. Confirm the comment appears immediately without a full page reload.
4. Open the same page in another browser or session.
5. Confirm new comments appear within approximately 5 seconds.

## Related Documentation

- `TESTING.md`: Final report testing plan and checklists.
- `CHANGELOG.md`: Summary of implemented changes.
- `docs/user-guide.md`: User-facing guide for the new features.
- `docs/developer-notes.md`: Technical notes for maintainers.

## Known Limitations

- AJAX comments are implemented for artwork comments because the current database schema stores comments with `comment.artwork_id`. Task comments are not part of the current schema.
- Budget filtering was not implemented because the task table does not include budget, price, or amount fields.
- Some older legacy/admin code still follows the original project style. The approved feature work uses prepared statements for new SQL paths.
