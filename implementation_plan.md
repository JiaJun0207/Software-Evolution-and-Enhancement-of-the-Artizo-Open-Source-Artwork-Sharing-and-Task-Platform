# Implementation Plan

Project: Software Evolution and Enhancement of the Artizo Open-Source Artwork Sharing and Task Platform  
Course context: CSE6364 Software Evolution and Maintenance  
Branch context: `Part-ii-Implementation`

This plan is based on static inspection only. No functional code has been changed.

## 1. Current System Overview

Artizo is a PHP/MySQL artwork sharing and task platform designed for a local XAMPP environment. The application uses page-level PHP scripts, a shared `config.php` database connection, Bootstrap 5 from CDN, Google Fonts, a single root stylesheet, and vanilla JavaScript embedded directly inside PHP pages.

The application currently supports:

- User signup, login, logout, password reset, and profile editing.
- Artwork upload, artwork browsing, category filtering, artwork detail viewing, and artwork comments.
- Task upload, task browsing, category filtering, accepting tasks, viewing accepted tasks, and task solution submission.
- Admin-style listing, editing, and deleting for artwork, tasks, users, and artwork comments.
- Support email form using EmailJS.

There are no standalone project JavaScript files. Existing JavaScript is inline in PHP files for search field styling, drag-and-drop upload controls, profile image auto-submit, support email submission, Bootstrap navigation/carousel behavior, and an early artwork comment AJAX submit flow.

## 2. Existing Relevant Files

Core configuration and shared layout:

- `config.php`: MySQLi connection to local `web_assignment` database using XAMPP-style `root` user and empty password.
- `navbar.php`: Authenticated user navbar with profile image lookup.
- `admin_navbar.php`: Admin navbar variant.
- `footer.php`: Shared footer.
- `style.css`: Main stylesheet for typography, cards, buttons, responsive rules, artwork cards, category controls, detail pages, and comments.
- `README.md`: Minimal project description.
- `AGENTS.md`: Future Codex guidance for this assignment.

Authentication and account files:

- `login.php`, `login_form.php`: Login UI and form handler.
- `signup.php`, `signup_form.php`: Signup UI and form handler.
- `logout.php`: Session logout.
- `forgot_password.php`, `send_reset_link.php`, `reset_password.php`, `reset_password_form.php`, `update_password.php`: Password reset flow.

Profile files:

- `user_profile.php`: Current user profile and artwork list.
- `edit_profile.php`, `edit_profile_form.php`: Profile description and profile image updates.
- `admin_user_profile.php`: Admin-side user profile page.

Artwork files:

- `explore.php`: Artwork listing, search, and category tabs.
- `artwork_detail.php`: Artwork detail view and current artwork comment display/submission.
- `upload_artwork.php`, `upload_artwork_form.php`: Artwork upload UI and handler.
- `admin_index.php`: Admin artwork listing.
- `admin_edit_artwork.php`, `admin_edit_artwork_form.php`: Admin artwork detail/edit page and update handler.

Task files:

- `index.php`: Homepage with latest task cards.
- `task.php`: Task listing, search, category tabs, and available task cards.
- `task_detail.php`: Task detail page, accept-task action, and task solution upload UI.
- `accepted_task.php`: Tasks accepted by the logged-in user.
- `upload_task.php`, `upload_task_form.php`: Task upload UI and handler.
- `submission_task.php`: Task solution upload handler.
- `admin_task.php`: Admin task listing.
- `admin_edit_task.php`, `admin_edit_task_form.php`: Admin task detail/edit page and update handler.

Admin and delete files:

- `admin_login.php`, `admin_login_form.php`: Admin login UI and handler. The handler uses the same `user` table.
- `admin_user_preview.php`: Admin user listing.
- `admin_edit_user.php`, `admin_edit_user_form.php`: Admin user edit page and handler.
- `delete_form.php`: Prepared delete handler for tasks, artwork, users, and comments.

Support and assets:

- `support.php`: Support contact form using EmailJS.
- `assets/database/web_assignment.sql`: Current database dump.
- `assets/homepage/`, `assets/profile/`, `assets/Icons/`, `assets/logo/`, `assets/uploads/`: Static and uploaded assets.
- `PHPMailer/`: Bundled PHPMailer dependency used by password reset email.

## 3. Existing Database Tables or Assumptions

The schema file inspected is `assets/database/web_assignment.sql`. It defines:

- `user`
  - Columns: `user_id`, `user_name`, `user_description`, `email`, `password`, `profile_image`, `reset_token`.
  - Used by login, signup, profile, upload ownership, comments, and admin views.
- `category`
  - Columns: `category_id`, `category_name`.
  - Seed values: Graphic Design, Illustration, Photography, 3D Art, Advertising.
  - Already linked to artwork and task.
- `artwork`
  - Columns: `artwork_id`, `artwork_title`, `artwork_description`, `artwork_image`, `user_id`, `category_id`, `release_at`.
  - Foreign keys to `user` and `category`.
- `task`
  - Columns: `task_id`, `task_title`, `task_description`, `task_image`, `task_solution`, `task_status`, `post_user_id`, `accepted_user_id`, `category_id`, `release_at`.
  - Foreign keys to posting user, accepted user, and category.
  - `task_status` enum currently includes `accept`, `accepted`, `submitted`, and empty string.
- `comment`
  - Columns: `comment_id`, `artwork_id`, `user_id`, `comment_text`, `created_at`.
  - Foreign keys to `artwork` and `user`.
  - Current schema supports artwork comments only.

Important assumptions and findings:

- The database name is assumed to remain `web_assignment`.
- The project is expected to run under XAMPP with MariaDB/MySQL and PHP 8.x.
- Existing task category support is already present through `task.category_id` and `task.php` category tabs, but it needs hardening and clearer filtering behavior.
- Existing artwork category filtering is present in `explore.php`.
- `artwork_detail.php` already has an AJAX-like comment submission path, but it posts to the same page and then reloads the page. It is not near-real-time.
- `task_detail.php` contains a prepared insert into `comment (task_id, user_id, comment_text, created_at)`, but the inspected `comment` table does not have a `task_id` column and no task comment UI is rendered. This should be treated as an existing inconsistency, not as scope for inventing a task comment feature.
- Several existing legacy queries use string interpolation or `$conn->query()` with escaped strings. New work must use prepared statements, and touched queries should be converted where practical.
- `style.css` contains repeated responsive blocks and appears to include a malformed stray CSS block near the comment row rules. Responsive work should start with careful CSS validation.

## 4. Feature-by-Feature Implementation Plan

### Improvement 1: Fix Responsive Layout for 1920x1080

Goal: Improve desktop layout at 1920x1080 without rewriting the interface.

Current state:

- Many pages use Bootstrap containers and rows, but also rely on fixed inline padding, fixed card heights, and large typography classes.
- `style.css` focuses heavily on mobile breakpoints. There is limited explicit handling for large desktop widths.
- Likely affected pages: `index.php`, `explore.php`, `task.php`, `accepted_task.php`, `artwork_detail.php`, `task_detail.php`, `user_profile.php`, admin listing pages, and upload forms.

Plan:

- Audit primary pages at 1920x1080.
- Add targeted large-screen CSS rules in `style.css` rather than editing every page.
- Reduce layout pressure from fixed-width buttons, huge text classes, fixed card heights, and wide gaps where they cause awkward spacing or overflow.
- Keep Bootstrap grid behavior intact.
- Avoid broad redesigns and avoid changing page content.

Likely files:

- `style.css`
- Possibly small class additions in affected PHP templates only where CSS cannot reliably target existing markup.

Testing notes to collect:

- Screenshots at 1920x1080 for homepage, Explore, Task, artwork detail, task detail, profile, and one admin listing.
- Check no horizontal scrolling on main pages unless expected for category controls.

### Improvement 2: Improve Low Text Contrast

Goal: Improve readability and accessibility while preserving the Artizo visual style.

Current state:

- Several pages use `font-weight: 200` for body-size text.
- Some colored category backgrounds use white text and may not meet contrast needs, especially light green `#8DE45B` and orange `#F7822A`.
- Placeholder text uses `#b2b2b2`.
- Overlay text relies on image brightness and shadow.

Plan:

- Audit text contrast in `style.css` and major inline styles.
- Prefer CSS-level fixes for typography weight, text colors, and category label colors.
- Use darker text on light category colors when needed.
- Preserve brand/category colors where practical by changing foreground color, font weight, or background shade only where needed.
- Ensure buttons and search inputs remain readable in normal, hover, focus, and active states.

Likely files:

- `style.css`
- `explore.php`, `task.php`, `artwork_detail.php`, `task_detail.php` if inline category colors need accessible foreground logic.

Testing notes to collect:

- Before/after screenshots of low contrast examples.
- Manual contrast notes for category tags, card text, placeholders, and overlay text.

### Improvement 3: Add Save Task Feature

Goal: Allow logged-in users to save tasks for later without accepting them.

Current state:

- Users can browse tasks in `task.php`.
- Users can accept a task from `task_detail.php`.
- Users can view accepted tasks in `accepted_task.php`.
- No saved-task table or UI exists.

Plan:

- Add a new many-to-many table between `user` and `task`.
- Add a prepared-statement endpoint to toggle saved state.
- Add save/unsave controls to task cards and task detail.
- Add a saved tasks view, either as a new `saved_task.php` page or as a clearly separated filter in the existing task area.
- Keep accepting a task separate from saving a task.
- Do not allow duplicate saves because of a unique database constraint.

Likely files:

- `assets/database/web_assignment.sql`
- `task.php`
- `task_detail.php`
- New `save_task_toggle.php`
- New `saved_task.php` if a dedicated saved-task list is chosen.
- `navbar.php` or `task.php` only if a navigation/link entry is needed.
- `style.css`
- Documentation/testing notes file or README update.

### Improvement 4: Add Task Categorization and Filtering

Goal: Complete and harden task category filtering as an approved proposal improvement.

Current state:

- The `task` table already has `category_id`.
- `upload_task.php` already requires category radio buttons.
- `task.php` already has category tabs and filters by `category_name`.
- Search and category filtering are built through string concatenation and `$conn->real_escape_string()`, not prepared statements.
- `accepted_task.php` has search only, no category filtering.
- Admin task listing search exists, no category filtering.

Plan:

- Keep the existing `category` table and `task.category_id`.
- Convert touched task filtering queries to prepared statements.
- Preserve selected category and search query together when switching category tabs.
- Add category display to task cards where useful and not visually disruptive.
- Consider adding category filtering to `accepted_task.php` only if it fits the approved proposal without expanding scope too far.
- Avoid creating duplicate category systems.

Likely files:

- `task.php`
- `accepted_task.php`
- `upload_task.php` only if category UI needs accessibility improvements.
- `upload_task_form.php` if validation/prepared insert is updated while touching task category handling.
- `admin_task.php` if admin filtering is included.
- `style.css`

### Improvement 5: Add Artwork Like Feature

Goal: Allow logged-in users to like/unlike artwork and show like counts.

Current state:

- Artwork browsing and detail views exist.
- No artwork-like table, count, or like UI exists.
- Artwork comments already require login because `artwork_detail.php` redirects unauthenticated users.

Plan:

- Add a new many-to-many table between `user` and `artwork`.
- Add a prepared-statement endpoint to toggle like state.
- Show like count and liked/unliked state on `artwork_detail.php`.
- Consider showing counts on `explore.php` cards if it does not clutter the page.
- Use a unique key to prevent duplicate likes.
- Keep likes independent of comments and uploads.

Likely files:

- `assets/database/web_assignment.sql`
- `artwork_detail.php`
- `explore.php` if card counts are included.
- New `artwork_like_toggle.php`
- `style.css`
- Documentation/testing notes file or README update.

### Improvement 6: Add AJAX Comment Submission and Near-Real-Time Refresh

Goal: Improve artwork comments so submission does not require full page reload and comments refresh periodically.

Current state:

- `artwork_detail.php` displays comments from the `comment` table.
- It currently handles POST submission in the same page and returns `success`, but the JavaScript immediately calls `location.reload()`.
- There is no periodic refresh endpoint.
- `task_detail.php` contains a task comment insert path that does not match the current schema and has no rendered comment UI.

Plan:

- Scope this improvement to artwork comments, because the approved proposal says comment submission and the existing valid comment model is artwork-based.
- Add a comment-list endpoint returning JSON or rendered HTML for one artwork.
- Add a comment-submit endpoint using prepared statements.
- Update `artwork_detail.php` JavaScript to submit comments with `fetch()` or `XMLHttpRequest`, append/render the returned comment, clear the textarea, and avoid full page reload.
- Add a `setInterval` refresh using a timestamp or latest `comment_id` to reduce unnecessary redraws.
- Keep server-side escaping and client-side text rendering safe.
- Do not add task comments unless the assignment scope is explicitly updated.

Likely files:

- `artwork_detail.php`
- New `comment_submit.php`
- New `comments_fetch.php`
- `style.css` for comment layout/accessibility
- Documentation/testing notes file or README update.

## 5. Required New Database Tables or Columns

Required new tables:

```sql
CREATE TABLE saved_task (
  saved_task_id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  task_id INT(11) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (saved_task_id),
  UNIQUE KEY uniq_saved_task_user_task (user_id, task_id),
  KEY idx_saved_task_user (user_id),
  KEY idx_saved_task_task (task_id),
  CONSTRAINT fk_saved_task_user FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_saved_task_task FOREIGN KEY (task_id) REFERENCES task(task_id) ON DELETE CASCADE ON UPDATE CASCADE
);
```

```sql
CREATE TABLE artwork_like (
  artwork_like_id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  artwork_id INT(11) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (artwork_like_id),
  UNIQUE KEY uniq_artwork_like_user_artwork (user_id, artwork_id),
  KEY idx_artwork_like_user (user_id),
  KEY idx_artwork_like_artwork (artwork_id),
  CONSTRAINT fk_artwork_like_user FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_artwork_like_artwork FOREIGN KEY (artwork_id) REFERENCES artwork(artwork_id) ON DELETE CASCADE ON UPDATE CASCADE
);
```

Possible optional indexes:

```sql
CREATE INDEX idx_comment_artwork_created ON comment(artwork_id, created_at);
CREATE INDEX idx_task_status_category ON task(task_status, category_id);
```

No new column is required for task categorization because `task.category_id` already exists.

No new column is required for artwork comments if AJAX remains scoped to artwork comments.

## 6. Required New PHP Endpoints

Planned new endpoints:

- `save_task_toggle.php`
  - POST only.
  - Requires authenticated `$_SESSION['UID']`.
  - Inputs: `task_id`.
  - Uses prepared statements.
  - Toggles row in `saved_task`.
  - Returns JSON with `saved: true/false`.

- `artwork_like_toggle.php`
  - POST only.
  - Requires authenticated `$_SESSION['UID']`.
  - Inputs: `artwork_id`.
  - Uses prepared statements.
  - Toggles row in `artwork_like`.
  - Returns JSON with `liked: true/false` and `like_count`.

- `comment_submit.php`
  - POST only.
  - Requires authenticated `$_SESSION['UID']`.
  - Inputs: `artwork_id`, `comment_text`.
  - Uses prepared statements.
  - Inserts into `comment`.
  - Returns JSON with the new comment data or rendered safe HTML.

- `comments_fetch.php`
  - GET only.
  - Requires authenticated `$_SESSION['UID']` unless the detail page is later made public.
  - Inputs: `artwork_id`, optional `after_id` or `since`.
  - Uses prepared statements.
  - Returns JSON or rendered safe HTML for comments.

Possible new page:

- `saved_task.php`
  - Lists saved tasks for the current user.
  - Uses prepared statements and joins `saved_task`, `task`, `user`, and `category`.

## 7. Required Frontend Changes

Responsive layout:

- Add large desktop rules to `style.css`.
- Reduce reliance on brittle selectors based on inline style fragments where possible.
- Avoid page-wide redesign.
- Verify major pages at 1920x1080.

Contrast:

- Adjust text colors, weights, and category foreground colors.
- Preserve the existing brand direction.
- Improve placeholder and supporting text readability.

Save Task:

- Add save/unsave button state on task cards and task detail.
- Add saved tasks link or tab in the task area.
- Use vanilla JavaScript to call `save_task_toggle.php`.
- Ensure no page break if JavaScript fails; a normal POST fallback can be considered.

Task categorization/filtering:

- Preserve search query when changing categories.
- Preserve category query when searching.
- Show empty-state messaging when no tasks match.
- Add accessible active state for selected category.

Artwork Like:

- Add like button and count to artwork detail.
- Optionally add count to artwork cards.
- Use vanilla JavaScript to call `artwork_like_toggle.php`.
- Include loading/disabled state to prevent duplicate rapid clicks.

AJAX Comments:

- Replace reload-based comment submission in `artwork_detail.php`.
- Add JS for submit, render, clear, error message, and periodic refresh.
- Keep the textarea keyboard behavior usable: Enter-to-submit can remain if clearly handled, but Shift+Enter should still add a newline.
- Avoid duplicating comments during refresh by tracking latest `comment_id`.

## 8. Testing Plan

General setup:

- Import/update `assets/database/web_assignment.sql` in phpMyAdmin.
- Confirm `config.php` points to `localhost`, `root`, empty password, and `web_assignment`.
- Use at least two test users to verify user-specific features.
- Test in browser through XAMPP, not by opening PHP files directly.

Responsive layout:

- Test `index.php`, `explore.php`, `task.php`, `artwork_detail.php`, `task_detail.php`, `user_profile.php`, and an admin listing at 1920x1080.
- Confirm no unintended horizontal scroll.
- Confirm cards, buttons, category tabs, comments, and detail image columns align cleanly.

Contrast:

- Check category labels, card text, placeholder text, overlay text, navbar, footer, and form controls.
- Collect before/after screenshots of representative low-contrast areas.

Save Task:

- Save a task from the listing.
- Unsave the same task.
- Confirm saved task appears in saved task view.
- Confirm duplicate saves are prevented.
- Confirm one user saving a task does not save it for another user.
- Confirm deleting a task removes related saved-task rows through cascade.

Task categorization/filtering:

- Filter tasks by each category.
- Combine search and category filters.
- Test no-result state.
- Confirm newly posted tasks appear under the selected category.
- Confirm SQL uses prepared statements in touched query paths.

Artwork Like:

- Like and unlike artwork from detail page.
- Confirm like count changes immediately.
- Confirm refresh preserves liked state.
- Confirm duplicate likes are prevented.
- Confirm one user liking artwork does not mark it liked for another user.
- Confirm deleting artwork removes related likes through cascade.

AJAX comments:

- Submit a valid comment without full page reload.
- Submit an empty comment and confirm it is rejected.
- Open the same artwork in two browser sessions and confirm near-real-time refresh shows new comments.
- Confirm HTML/script text in comments is escaped and rendered as text.
- Confirm comments remain ordered by creation time.

Regression tests:

- Login, signup, logout.
- Upload artwork.
- Upload task.
- Accept task.
- Submit task solution.
- Edit profile.
- Admin delete for artwork, task, user, and comment.

## 9. Risks and Mitigation

- Risk: Existing non-prepared SQL remains in several legacy paths.
  - Mitigation: Use prepared statements for all new endpoints and convert touched queries when implementing related features.

- Risk: Task comments code in `task_detail.php` does not match the current `comment` schema.
  - Mitigation: Do not build on that path for the approved AJAX artwork-comment feature. Document it as an existing inconsistency.

- Risk: `style.css` has repeated responsive rules and an apparent malformed block near comment-row styling.
  - Mitigation: Validate CSS before changing responsive behavior, then make targeted edits.

- Risk: Upload folder naming is inconsistent between task list and upload paths.
  - Mitigation: Avoid changing upload behavior during planning. When task UI is touched, verify `assets/uploads/task/` versus `assets/uploads/tasks/` references.

- Risk: CDN dependencies require internet access.
  - Mitigation: Keep CDN use as existing project behavior for the assignment unless local fallback is explicitly required.

- Risk: New tables require manual database import/update in XAMPP.
  - Mitigation: Document exact SQL migration steps and include them in final evidence.

- Risk: AJAX refresh can create duplicate comment rendering.
  - Mitigation: Track latest `comment_id` and render only new comments.

- Risk: Feature creep beyond approved proposal.
  - Mitigation: Keep implementation limited to the six approved improvements and do not add unrelated social, notification, or admin features.

## 10. Final Report Evidence to Collect

- Screenshot of `implementation_plan.md`.
- Screenshot of imported/updated database tables in phpMyAdmin.
- Screenshot or SQL snippet for `saved_task` table.
- Screenshot or SQL snippet for `artwork_like` table.
- Before/after screenshots for 1920x1080 responsive layout fixes.
- Before/after screenshots for contrast improvements.
- Save Task evidence:
  - Unsaved state.
  - Saved state.
  - Saved task list.
  - Database row in `saved_task`.
- Task categorization/filtering evidence:
  - Category filter selected.
  - Search plus category result.
  - No-result state if implemented.
- Artwork Like evidence:
  - Before like.
  - After like count update.
  - Database row in `artwork_like`.
- AJAX comments evidence:
  - Comment submitted without page reload.
  - Near-real-time refresh from another browser/session.
  - Database row in `comment`.
- Testing notes for each feature.
- Short risk/mitigation section for the final assignment report.

## Files Inspected

Root and documentation:

- `AGENTS.md`
- `README.md`

Configuration, schema, and shared files:

- `config.php`
- `assets/database/web_assignment.sql`
- `style.css`
- `navbar.php`
- `admin_navbar.php`
- `footer.php`

Authentication and account:

- `login.php`
- `login_form.php`
- `signup.php`
- `signup_form.php`
- `logout.php`
- `forgot_password.php`
- `send_reset_link.php`
- `reset_password.php`
- `reset_password_form.php`
- `update_password.php`

Artwork:

- `explore.php`
- `artwork_detail.php`
- `upload_artwork.php`
- `upload_artwork_form.php`
- `admin_index.php`
- `admin_edit_artwork.php`
- `admin_edit_artwork_form.php`

Task:

- `index.php`
- `task.php`
- `task_detail.php`
- `accepted_task.php`
- `upload_task.php`
- `upload_task_form.php`
- `submission_task.php`
- `admin_task.php`
- `admin_edit_task.php`
- `admin_edit_task_form.php`

Profile and admin:

- `user_profile.php`
- `edit_profile.php`
- `edit_profile_form.php`
- `admin_user_profile.php`
- `admin_user_preview.php`
- `admin_edit_user.php`
- `admin_edit_user_form.php`
- `admin_login.php`
- `admin_login_form.php`
- `delete_form.php`

Support:

- `support.php`

Bundled dependency:

- `PHPMailer/` file list was inspected as a bundled dependency; detailed implementation review was not needed for the approved proposal features.

## Existing System Structure

```text
/
├── PHP page scripts and form handlers
├── config.php
├── style.css
├── README.md
├── AGENTS.md
├── implementation_plan.md
├── assets/
│   ├── database/web_assignment.sql
│   ├── homepage/
│   ├── Icons/
│   ├── logo/
│   ├── profile/
│   └── uploads/
└── PHPMailer/
```

The codebase is a flat PHP application rather than an MVC framework. Most behavior is implemented directly inside page scripts. Shared layout is handled through PHP includes. Database access uses MySQLi.

## Database Assumptions

- The local database is named `web_assignment`.
- The schema dump in `assets/database/web_assignment.sql` is the source of truth for current tables.
- Existing category values remain fixed for the assignment unless explicitly changed.
- New SQL should be compatible with MariaDB/MySQL in XAMPP.
- New tables should use `InnoDB`, `utf8mb4`, foreign keys, and unique constraints where needed.

## Implementation Plan by Phase

Phase 1: Baseline cleanup and documentation support

- Validate current schema import.
- Document current setup steps.
- Identify pages for 1920x1080 screenshots.

Phase 2: Responsive and contrast fixes

- Update `style.css` with targeted large-screen and contrast improvements.
- Touch PHP templates only when inline styles block a CSS-only fix.
- Capture before/after evidence.

Phase 3: Task categorization/filtering hardening

- Convert touched task filtering queries to prepared statements.
- Preserve search/category query combinations.
- Add empty-state behavior and category display improvements where appropriate.

Phase 4: Save Task feature

- Add `saved_task` table.
- Add save toggle endpoint.
- Add save controls and saved task view.
- Add tests and documentation notes.

Phase 5: Artwork Like feature

- Add `artwork_like` table.
- Add like toggle endpoint.
- Add like UI and count.
- Add tests and documentation notes.

Phase 6: AJAX comments and near-real-time refresh

- Add comment submit/fetch endpoints.
- Replace reload-based artwork comment submission.
- Add periodic refresh.
- Add tests and documentation notes.

Phase 7: Final verification and report evidence

- Run manual regression tests.
- Collect screenshots and SQL evidence.
- Update documentation with feature notes, schema changes, and testing notes.

## Risks Before Implementation

- The codebase contains legacy non-prepared queries, especially in login/signup/upload/search paths.
- Existing responsive CSS has repeated rules and one suspicious malformed section.
- Some file upload paths appear inconsistent for task images.
- The comment schema supports artwork comments only, while one task detail code path refers to `task_id`.
- New tables must be manually applied in XAMPP/phpMyAdmin unless an import script is added.
- CDN-based Bootstrap, fonts, Font Awesome, and EmailJS may not load offline.
- Admin pages do not appear to have a separate role/permission model; they only check login state.
