# Changelog

This changelog summarizes the approved CSE6364 Software Evolution and Maintenance enhancements implemented on the `Part-ii-Implementation` branch.

## Multi-User Task Workflow Refactor

- Reworked the task workflow so multiple users can independently accept and submit to the same posted task. A task no longer disappears for everyone after one user submits.
- Added `task_acceptances` (per-user `accepted`/`cancelled`/`submitted` state, `UNIQUE (task_id, user_id)`, FKs to `task`/`user` with `ON DELETE CASCADE`).
- Added `task.task_state` (`ENUM('open','closed','completed')`) as the single source of truth for task board visibility (board filters on `task_state='open'`).
- Added `UNIQUE (task_id, submitter_user_id)` on `task_submissions` (one active submission per user per task; deleting frees the slot).
- Posters/admins can edit (`edit_task.php`), soft-close (`close_task.php`), and hard-delete (`delete_task.php`) their own tasks; hard delete unlinks solution/image files and cascades acceptances/submissions.
- Submitters can edit (`edit_submission.php`) and delete (`delete_submission.php`) their own submission; deleting reverts their acceptance to `accepted` and keeps the task.
- Submission summary cards hide the submitter email; `submission_detail.php` shows the email only to the poster and admin.
- User-friendly status labels (Open / Accepted by you / Submitted by you / Closed / Completed) while DB values stay stable.
- Legacy `task.task_status`/`task.accepted_user_id` retained for backward compatibility only (not used for board gating). `task_categories`/`task.task_category_id` remain removed and must not be reintroduced; the shared `category` table is the unified category source.
- All new queries use prepared statements. Both `assets/database/full_database_setup.sql` (fresh install) and `assets/database/regression_fixes_migration.sql` (idempotent upgrade with backfill) updated.

## Final Assignment Implementation

### Responsive Layout Fix

- Added large-screen layout constraints for gallery cards, task cards, and main content containers.
- Improved spacing and sizing for 1920x1080 displays.
- Preserved the original Artizo visual style while reducing excessive stretching on wide screens.

### Text Contrast Improvement

- Improved readability for category labels, artwork captions, task descriptions, card subtitles, placeholders, buttons, and links.
- Added reusable CSS variables and more consistent text styling.
- Improved hover/focus readability for interactive controls.

### Save Task Feature

- Added Save/Saved buttons to task cards.
- Added AJAX save/unsave behavior through `save_task_toggle.php`.
- Added `saved_tasks.php` so users can view saved tasks.
- Used the `saved_tasks` table with a unique user/task constraint to prevent duplicate saves.
- Added session validation before saving or unsaving.

### Task Categorization and Filtering

- Added task category selection to the task creation form.
- Added task category storage through `task.task_category_id`.
- Added category labels to task cards.
- Added AJAX task filtering through `task_filter.php`.
- Added `task_categories` migration and seed data.
- Preserved existing task creation and task board behavior.

### Artwork Like Feature

- Added Like/Unlike buttons to artwork cards and artwork detail pages.
- Added like count display.
- Added AJAX like toggling through `artwork_like_toggle.php`.
- Used the `artwork_likes` table with a unique user/artwork constraint.
- Added shared frontend behavior in `artwork_like.js`.

### AJAX Comment Update

- Replaced full-page refresh comment submission on artwork detail pages.
- Added `submit_comment.php` for JSON comment submission.
- Added `fetch_comments.php` for polling newer comments.
- Added 5-second polling on artwork detail pages.
- Prevented duplicate DOM comments by tracking `comment_id`.
