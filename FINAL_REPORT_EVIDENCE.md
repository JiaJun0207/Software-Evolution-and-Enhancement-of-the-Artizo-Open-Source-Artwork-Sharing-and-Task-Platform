# CSE6364 Part II Final Report Evidence Guide

Project: Software Evolution and Enhancement of the Artizo Open-Source Artwork Sharing and Task Platform

This document is a guide for preparing the Part II final report. It summarizes the implementation work and lists evidence that should be captured manually. It does not contain invented screenshots or test results.

## 1. Codebase Analysis Summary

The existing Artizo codebase is a PHP/MySQL web application designed for artwork sharing and creative task posting. It uses:

- PHP pages for routing and server-side rendering.
- MySQL/MariaDB database tables accessed through `config.php`.
- Bootstrap 5 and custom CSS in `style.css`.
- Vanilla JavaScript for page-level interactivity.
- XAMPP/phpMyAdmin as the expected local development environment.

Main original feature areas inspected:

- User authentication and profile pages.
- Artwork upload, gallery display, and artwork detail pages.
- Task creation, task board, accepted tasks, and task detail pages.
- Artwork comments.
- Admin pages for managing users, artworks, tasks, and comments.
- Database schema files under `assets/database`.

The implementation approach was additive. Existing features were preserved while new functionality was integrated through additional tables, endpoints, CSS, and JavaScript behavior.

## 2. Original Problems Found

Problems identified during analysis and implementation:

- Layout could stretch too widely on large 1920x1080 displays.
- Some text areas, labels, subtitles, captions, and interactive elements had weak contrast or readability issues.
- Users had no way to save tasks for later.
- Task creation and task browsing did not support task-specific categorization and filtering.
- Artwork cards and artwork detail pages did not support user likes.
- Artwork comment submission caused a full page refresh instead of updating dynamically.
- The original schema did not include budget fields, so budget filtering could not be safely implemented.
- The comment table stores artwork comments through `comment.artwork_id`; it does not support task comments.
- Some original code used direct SQL string construction. New feature work uses prepared statements.
- Local database names varied between `web_assignment` and `software_evo_assignment`, which could cause connection or import confusion.

## 3. Implemented Improvements

Implemented proposal improvements:

1. Responsive layout improvements for 1920x1080 displays.
2. Text contrast and readability improvements.
3. Save Task feature.
4. Task categorization and AJAX filtering.
5. Artwork Like feature.
6. AJAX comment submission and near-real-time refresh.

Additional integration/documentation work:

- Added safe database migration SQL.
- Added final testing documentation.
- Added user and developer documentation.
- Updated database connection handling for common local database names.

## 4. Changed Files Grouped by Feature

### Project Guidance and Planning

- `AGENTS.md`
- `implementation_plan.md`

### Database Preparation

- `assets/database/phase1_database_migration.sql`
- `assets/database/full_database_setup.sql`
- `assets/database/task_category_filter_migration.sql`
- `README.md`

### Responsive Layout and Text Contrast

- `style.css`

### Save Task Feature

- `task.php`
- `save_task_toggle.php`
- `saved_tasks.php`
- `user_profile.php`
- `style.css`
- `README.md`

### Task Categorization and Filtering

- `upload_task.php`
- `upload_task_form.php`
- `task.php`
- `task_filter.php`
- `style.css`
- `assets/database/task_category_filter_migration.sql`
- `assets/database/full_database_setup.sql`
- `README.md`

### Artwork Like Feature

- `explore.php`
- `artwork_detail.php`
- `user_profile.php`
- `artwork_like_toggle.php`
- `artwork_like.js`
- `style.css`
- `README.md`

### AJAX Comment Submission and Refresh

- `artwork_detail.php`
- `submit_comment.php`
- `fetch_comments.php`
- `style.css`
- `README.md`

### Final Documentation

- `README.md`
- `CHANGELOG.md`
- `TESTING.md`
- `docs/user-guide.md`
- `docs/developer-notes.md`
- `FINAL_REPORT_EVIDENCE.md`

### Integration and Security Fixes

- `config.php`
- `upload_artwork_form.php`
- `task_detail.php`

## 5. Database Changes

### New Tables

`saved_tasks`

- Stores saved task records by user.
- Uses `user_id` and `task_id`.
- Prevents duplicate saves with a unique user/task key.

`task_categories`

- Stores task-specific category labels.
- Seeded with:
  - Illustration
  - Graphic Design
  - Animation
  - Digital Painting
  - UI/UX Design
  - Photography
  - Other

`artwork_likes`

- Stores artwork likes by user.
- Uses `user_id` and `artwork_id`.
- Prevents duplicate likes with a unique user/artwork key.

`task_submissions`

- Stores each user's submitted work for a task.
- Fields: `submission_id`, `task_id`, `submitter_user_id`, `file_path`, `message`, `status`, `submitted_at`.
- Unique key `uniq_task_submissions_task_user` on `(task_id, submitter_user_id)` enforces one active submission per user per task.

`task_acceptances`

- Stores per-user acceptance/submission state so multiple users can independently accept and submit to the same posted task.
- Fields: `acceptance_id`, `task_id`, `user_id`, `status` (`accepted`/`cancelled`/`submitted`), `accepted_at`, `updated_at`.
- `UNIQUE (task_id, user_id)`; foreign keys to `task` and `user` with `ON DELETE CASCADE`.

### New/Updated Columns

`task.task_state`

- `ENUM('open','closed','completed') NOT NULL DEFAULT 'open'`.
- Poster-level task lifecycle and the single source of truth for task board visibility (board filters on `task_state='open'`).

`task.category_id` (unified category)

- The unified category source is the shared `category` table referenced by `task.category_id`.

Legacy (backward compatibility only):

- `task.task_status` and `task.accepted_user_id` — legacy single-accepter fields, no longer used for board gating (per-user state now lives in `task_acceptances`).
- `task_categories` and `task.task_category_id` — legacy and must not be reintroduced; superseded by the shared `category` table.

### Migration Files

- `assets/database/phase1_database_migration.sql`
- `assets/database/full_database_setup.sql` (fresh install — already includes `task_submissions`, `task_acceptances`, `task.task_state`, and the `task_submissions` unique key)
- `assets/database/regression_fixes_migration.sql` (idempotent upgrade for existing databases — adds `task.task_state`, `task_acceptances`, the `task_submissions` unique key, and backfills from legacy fields)
- `assets/database/task_category_filter_migration.sql` (legacy / superseded)

## 6. Testing Evidence Checklist

Fill in this section manually after testing.

| Evidence Item | Capture/Record Needed | Completed |
|---|---|---|
| PHP syntax check | Command output showing PHP files pass syntax check |  |
| Database migration import | phpMyAdmin import success screenshot or notes |  |
| Database table validation | Screenshots of `DESCRIBE saved_tasks`, `DESCRIBE task_categories`, `DESCRIBE artwork_likes` |  |
| Responsive layout | Screenshots at required screen sizes |  |
| Text contrast | Before/after or final screenshots showing readable labels/text |  |
| Save Task | Screenshot before saving, after saving, and saved tasks page |  |
| Task filtering | Screenshot of category dropdown and filtered task board |  |
| Artwork Like | Screenshot of Like/Unlike state and count update |  |
| AJAX comments | Screenshot before comment, after submit, and after refresh/polling |  |
| Regression testing | Completed rows in `TESTING.md` |  |

## 7. Screenshots to Capture

Do not mark these as completed until screenshots are actually captured.

### Responsive Layout

- Homepage at 1366x768.
- Homepage or explore page at 1440x900.
- Task board at 1600x900.
- Explore page or task board at 1920x1080.
- Artwork detail page at 1920x1080.

### Text Contrast

- Category labels after contrast improvement.
- Task card descriptions after contrast improvement.
- Artwork captions after contrast improvement.
- Buttons/links after contrast improvement.

### Save Task

- Task card before clicking Save.
- Task card after clicking Save.
- Saved Tasks page showing saved task.
- Saved task removed or unsaved state.

### Task Categorization and Filtering

- Task creation form showing category dropdown.
- Task board showing category labels.
- Task board filtered by one category.
- Task board search combined with category filter.

### Artwork Like

- Artwork card before Like.
- Artwork card after Like with updated count.
- Artwork detail page showing liked state.
- Artwork detail page after Unlike.

### AJAX Comments

- Artwork detail page before submitting a comment.
- Comment appearing immediately after submit.
- Second session/browser showing comment refresh after polling.
- No duplicate comment after polling.

### Database

- phpMyAdmin table list showing new tables.
- `saved_tasks` structure.
- `task_categories` structure and seed data.
- `artwork_likes` structure.
- `task` structure showing category fields.

## 8. Challenges Faced During Implementation

Challenges observed:

- Database imports could fail when local databases had different names or missing base tables.
- The original schema used singular table names such as `task`, while proposal wording sometimes referred to `tasks`.
- Task category support needed to preserve the existing `task.category_id` relationship.
- Budget filtering was requested only if budget data existed, but no budget field was present.
- The existing `comment` table supports artwork comments but not task comments.
- AJAX updates needed to avoid duplicate DOM entries.
- Some existing pages used legacy SQL style, so new work had to be carefully scoped and maintainable.

## 9. Solutions Applied

Solutions implemented:

- Added XAMPP-friendly migration SQL with conditional table/foreign-key logic.
- Preserved existing table and column relationships instead of rewriting the schema.
- Added `task_categories` and `task.task_category_id` while keeping `task.category_id`.
- Skipped budget filtering and documented the schema limitation.
- Implemented AJAX comments for artwork detail pages only, matching the current `comment.artwork_id` schema.
- Used `comment_id` tracking to prevent duplicate comments during polling.
- Added prepared statements for new SQL queries.
- Added session validation to AJAX endpoints.
- Updated `config.php` to support common local database names.
- Added testing and developer documentation for future maintainers.

## 10. Software Maintenance Concepts Demonstrated

### Corrective Maintenance

Corrective maintenance fixes defects or broken behavior.

Examples:

- Fixed database import mismatch issues in migration SQL.
- Removed broken task comment insertion that referenced a nonexistent `comment.task_id` column.
- Guarded task detail JavaScript so it does not run against missing DOM elements.
- Improved database connection behavior for common local database names.

### Adaptive Maintenance

Adaptive maintenance adjusts the system to new requirements or environments.

Examples:

- Added support for `web_assignment` and `software_evo_assignment` database names.
- Added XAMPP/phpMyAdmin-compatible migration files.
- Added AJAX-based interactions while preserving the PHP/MySQL application structure.

### Perfective Maintenance

Perfective maintenance improves usability, performance, or user experience.

Examples:

- Improved responsive layout at 1920x1080.
- Improved text contrast and readability.
- Added Save Task, task filtering, artwork likes, and AJAX comments to improve user workflows.

### Preventive Maintenance

Preventive maintenance reduces future risk and improves maintainability.

Examples:

- Added prepared statements for new SQL paths.
- Added unique constraints for saved tasks and artwork likes.
- Added documentation in `README.md`, `TESTING.md`, `docs/user-guide.md`, and `docs/developer-notes.md`.
- Added safer migrations that avoid destructive changes.

## 11. Proposal Item to Implementation Mapping

| Proposal Item | Implementation |
|---|---|
| Fix responsive layout issues for 1920x1080 resolution | Updated CSS layout constraints, card sizing, and large-screen behavior in `style.css`. |
| Improve low text contrast and accessibility | Updated text, label, caption, button, and link readability styles in `style.css`. |
| Add Save Task feature | Added `saved_tasks`, Save/Saved buttons, `save_task_toggle.php`, and `saved_tasks.php`. |
| Add task categorization and filtering | Added `task_categories`, `task.task_category_id`, category dropdown, task labels, and `task_filter.php`. |
| Add artwork Like feature | Added `artwork_likes`, Like/Unlike buttons, like counts, `artwork_like_toggle.php`, and `artwork_like.js`. |
| Add AJAX comment submission and near-real-time refresh | Added `submit_comment.php`, `fetch_comments.php`, fetch-based comment submission, and 5-second polling on artwork detail pages. |

## 12. Remaining Limitations

- Task comments are not implemented because the current `comment` table is tied to `artwork_id`.
- Budget filtering is not implemented because the task schema does not include budget, price, or amount fields.
- Some original admin/authentication pages still contain legacy SQL style outside the approved feature scope.
- Final browser validation still requires XAMPP Apache/MySQL running with a correctly imported database.
- Actual screenshots and test results must be collected manually for the final report.
