# Developer Notes

These notes summarize the technical changes made for the CSE6364 Software Evolution and Maintenance assignment.

## New Database Tables and Columns

### `saved_tasks`

Purpose: stores tasks saved by users.

Important fields:

- `saved_task_id`
- `user_id`
- `task_id`
- `created_at`
- `updated_at`

Constraint:

- Unique key on `(user_id, task_id)` prevents duplicate saves.

### `task_categories`

Purpose: stores task-specific category labels for task creation and filtering.

Seed data:

- Illustration
- Graphic Design
- Animation
- Digital Painting
- UI/UX Design
- Photography
- Other

### `artwork_likes`

Purpose: stores artwork likes by user.

Important fields:

- `artwork_like_id`
- `user_id`
- `artwork_id`
- `created_at`

Constraint:

- Unique key on `(user_id, artwork_id)` prevents duplicate likes.

### `task.task_category_id`

Purpose: stores the selected task-specific category.

The existing `task.category_id` field is preserved because the original system already uses the shared `category` table.

### `pending_user_otps`

Purpose: stores account registration data until the user verifies the emailed OTP.

Important fields:

- `user_name`
- `email`
- `password_hash`
- `otp_code`
- `expires_at`
- `attempt_count`

### `support_tickets`

Purpose: stores user-created support tickets and tracking status. Ticket details can be tracked by matching ticket code and email.

Important fields:

- `ticket_code`
- `user_id`
- `email`
- `phone`
- `subject`
- `message`
- `status`

### `task_submissions`

Purpose: stores each user's submitted work for a task. Supports multiple users submitting to the same task (one active submission per user per task).

Important fields:

- `submission_id`
- `task_id`
- `submitter_user_id`
- `file_path`
- `message`
- `status` (`submitted`)
- `submitted_at`

Constraint:

- Unique key `uniq_task_submissions_task_user` on `(task_id, submitter_user_id)` — enforces one active submission per user per task. Deleting a submission frees the slot so the user can submit again.

### `task_acceptances`

Purpose: stores per-user acceptance/submission state for a task. This replaces the old single-accepter model so that multiple users can independently accept and submit to the same posted task.

Important fields:

- `acceptance_id`
- `task_id`
- `user_id`
- `status` (`accepted`, `cancelled`, `submitted`)
- `accepted_at`
- `updated_at`

Constraint:

- Unique key on `(task_id, user_id)` — one acceptance row per user per task (re-accepting updates the existing row).
- Foreign keys to `task(task_id)` and `user(user_id)` with `ON DELETE CASCADE`, so deleting a task removes its acceptances automatically.

### `task.task_state`

Purpose: poster-level task lifecycle column, `ENUM('open','closed','completed') NOT NULL DEFAULT 'open'`.

- This is the single source of truth for whether a task is shown on the open task board. The board filters on `task.task_state = 'open'`.
- The poster (or an admin) closes a task (`closed`, soft — submissions kept) or completes it (`completed`); only the poster/admin can change it.

### Legacy / backward-compatibility fields (do NOT use for board gating)

- `task.task_status` and `task.accepted_user_id` are **legacy** columns from the original single-accepter design. They are retained for backward compatibility only and are **no longer used** for task board visibility or per-user state. Per-user state now lives in `task_acceptances`; board visibility now lives in `task.task_state`.
- `task_categories` and `task.task_category_id` are **legacy** and **must not be reintroduced**. The unified category source is the shared `category` table referenced by `task.category_id` (the same table used by Explore, Task, Post Task, and Post Artwork).

## New and Updated PHP Endpoints

- `save_task_toggle.php`
  - Toggles saved task state.
  - Returns JSON.
  - Requires a logged-in user.

- `saved_tasks.php`
  - Displays the logged-in user's saved tasks.

- `submit_ticket.php`
  - Inserts a support ticket for the logged-in user.
  - Generates a unique ticket tracking code.

- `verify_otp.php`
  - Verifies account creation OTPs.
  - Creates the final `user` record only after successful verification.

- `task_filter.php`
  - Filters task cards by category and search term.
  - Returns JSON containing rendered card HTML.

- `artwork_like_toggle.php`
  - Toggles artwork like state.
  - Returns JSON with liked state and updated count.
  - Requires a logged-in user.

- `submit_comment.php`
  - Inserts a new artwork comment.
  - Returns JSON with the inserted comment data.
  - Requires a logged-in user.

- `fetch_comments.php`
  - Fetches comments newer than a supplied `since_id`.
  - Returns JSON with comment records and the latest comment ID.
  - Requires a logged-in user.

- `submission_task.php`
  - Saves a per-user submission for an accepted, open task.
  - Requires the user to hold an `accepted` `task_acceptances` row; blocks duplicate submissions; marks only that user's acceptance as `submitted`.

- `edit_task.php`
  - Poster/admin edits an open task (title, description, category, optional image replace).
  - Ownership-checked; only `task_state='open'` tasks are editable.

- `close_task.php`
  - Poster/admin soft-closes a task (`task_state='closed'`); submissions are kept.

- `delete_task.php`
  - Poster/admin hard-deletes a task; unlinks solution/image files and relies on FK cascade to remove acceptances/submissions.

- `edit_submission.php`
  - Submitter edits their own submission (message + optional file replace). Ownership-checked.

- `delete_submission.php`
  - Submitter deletes their own submission; removes the file, reverts the user's `task_acceptances` row to `accepted`, and does NOT delete the task.

Updated pages:

- `task.php`
- `task_detail.php`
- `task_filter.php`
- `accepted_task.php`
- `my_task.php`
- `submission_detail.php`
- `index.php`
- `upload_task.php`
- `upload_task_form.php`
- `saved_tasks.php`
- `support.php`
- `login_form.php`
- `signup_form.php`
- `forgot_password.php`
- `send_reset_link.php`
- `explore.php`
- `artwork_detail.php`
- `user_profile.php`
- `upload_artwork_form.php`
- `config.php`

## JavaScript Changes

- `artwork_like.js`
  - Shared handler for Like/Unlike buttons.
  - Sends fetch requests to `artwork_like_toggle.php`.
  - Updates liked state and counts without page reload.

Inline JavaScript updates:

- `task.php`
  - Handles Save Task button clicks.
  - Handles AJAX task filtering.

- `saved_tasks.php`
  - Handles unsave behavior on the saved tasks page.

- `artwork_detail.php`
  - Handles AJAX comment submission.
  - Polls `fetch_comments.php` every 5 seconds.
  - Tracks `comment_id` values to avoid duplicate DOM comments.

## CSS Changes

Main CSS file:

- `style.css`

Key additions:

- CSS variables for maintainable colors and sizing.
- Large-screen constraints for containers/cards.
- Improved contrast styles.
- Save Task button states.
- Task filter button styles.
- Task category chip styles.
- Artwork Like button states.
- Disabled comment submit button styling.

## Security and Maintainability Measures

- New feature SQL queries use prepared statements.
- Login accepts either email or username.
- Username login is case-sensitive through `BINARY user_name = ?`.
- Registration passwords must be at least 8 characters and include letters and numbers.
- Account creation requires OTP verification before inserting into `user`.
- AJAX endpoints validate session state before allowing protected actions.
- Save Task and Artwork Like endpoints validate target IDs before writing.
- Comment endpoints validate artwork IDs and reject empty comments.
- Duplicate saved tasks and artwork likes are prevented at the database level.
- JSON endpoints consistently return `success` and `message` or data payloads.
- Existing data is preserved by additive migrations.
- `config.php` supports common local database names used during the assignment.

## Known Limitations

- AJAX comments are implemented for artwork comments only because the current `comment` table stores `artwork_id` and does not support task comments.
- Budget filtering was not implemented because the task schema does not include budget, price, or amount fields.
- Some original admin and authentication pages still contain legacy SQL style from the base project. The approved enhancement work uses prepared statements for new SQL paths.
- Browser-level validation still requires XAMPP Apache and MySQL to be running with the database imported locally.
- PHPMailer SMTP credentials must be configured before OTP or reset emails can be delivered.
