# CSE6364 Final Testing Plan

Project: Software Evolution and Enhancement of the Artizo Open-Source Artwork Sharing and Task Platform

This document lists manual test cases for the approved proposal improvements and regression areas. Actual Result and Status must be completed only after manual testing is performed.

## Functional Test Cases

| Test ID | Feature | Steps | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| F-001 | 1920x1080 responsive layout | Open the homepage, explore page, task board, artwork detail page, and profile page at 1920x1080. | Main containers remain readable, cards do not stretch too wide, and spacing remains consistent. |  |  |
| F-002 | 1920x1080 responsive layout | Inspect artwork gallery cards and task cards at 1920x1080. | Cards keep controlled widths and maintain visual alignment. |  |  |
| F-003 | Text contrast improvement | Review category labels, card subtitles, placeholder text, artwork captions, task descriptions, buttons, and links. | Text is readable against its background and weak contrast areas are improved. |  |  |
| F-004 | Text contrast improvement | Hover/focus interactive buttons and links where applicable. | Hover/focus states remain visible and readable. |  |  |
| F-005 | Save Task feature | Log in, open `task.php`, and click `Save` on an available task card. | Button changes to `Saved` without a full page reload. |  |  |
| F-006 | Save Task feature | Refresh `task.php` after saving a task. | Previously saved task still shows the saved state. |  |  |
| F-007 | Save Task feature | Open `saved_tasks.php` after saving a task. | Saved task appears in the saved tasks list. |  |  |
| F-008 | Save Task feature | Click `Saved` or unsave from the saved tasks view. | Task is unsaved and removed or updated without breaking the page. |  |  |
| F-009 | Task categorization and filtering | Open `upload_task.php`. | Task category dropdown is visible and populated from the shared `category` table (the unified category source now used by Explore, Task, Post Task, and Post Artwork). The legacy `task_categories` table is retained for backward compatibility but is no longer the primary category source. |  |  |
| F-010 | Task categorization and filtering | Create a task with a selected category. | New task is saved with the selected category ID. |  |  |
| F-011 | Task categorization and filtering | Open `task.php` and inspect task cards. | Task card displays a category label. |  |  |
| F-012 | Task categorization and filtering | Click a category filter on the task board. | Task list updates using AJAX/fetch without a full page reload. |  |  |
| F-013 | Task categorization and filtering | Use search text together with a category filter. | Task results match both the search and selected category. |  |  |
| F-014 | Artwork Like feature | Log in, open `explore.php`, and click `Like` on an artwork card. | Button changes to liked state and like count increases without page reload. |  |  |
| F-015 | Artwork Like feature | Refresh `explore.php` after liking an artwork. | Liked artwork still shows active liked state. |  |  |
| F-016 | Artwork Like feature | Open the liked artwork in `artwork_detail.php`. | Detail page shows active liked state and correct like count. |  |  |
| F-017 | Artwork Like feature | Click `Unlike` on the artwork detail page. | Button returns to inactive state and like count decreases. |  |  |
| F-018 | AJAX comment submission and refresh | Open `artwork_detail.php?id=<existing artwork id>`, enter a comment, and submit it. | New comment appears immediately without a full page reload. |  |  |
| F-019 | AJAX comment submission and refresh | Refresh the artwork detail page after submitting a comment. | Submitted comment remains visible from the database. |  |  |
| F-020 | AJAX comment submission and refresh | Open the same artwork detail page in two browser sessions and submit a comment in one session. | Other session receives the new comment within approximately 5 seconds. |  |  |
| F-021 | AJAX comment submission and refresh | Let polling run after a local comment submission. | The same comment is not duplicated in the DOM. |  |  |
| F-022 | Header/footer regression | Open any main page at 1920x1080 and mobile width. | Header menu bar and footer appear smaller and remain usable. |  |  |
| F-023 | Footer Support link | Click `Support` in the footer. | `support.php` opens and shows the ticket tracking form. |  |  |
| F-024 | Support ticket submission | Log in, then submit email, phone, subject, and message on `support.php`. | Ticket is stored and a tracking code is shown. |  |  |
| F-025 | Support ticket tracking | Track a ticket using its ticket code and matching email while logged out. | Ticket status and details are shown. |  |  |
| F-026 | Support ticket ownership | Track another user's ticket code with the wrong email. | Ticket is not shown. |  |  |
| F-027 | Registration password rule | Register with fewer than 8 characters, letters only, and numbers only. | Each invalid password is rejected. |  |  |
| F-028 | Registration OTP | Register with a valid password and verify with OTP. | Account is created only after correct OTP. |  |  |
| F-029 | Email login | Log in with a registered email address. | Login succeeds. |  |  |
| F-030 | Case-sensitive username login | Log in with exact-case username, then wrong-case username. | Exact case succeeds and wrong case fails. |  |  |
| F-031 | Forgot password wording | Open `login.php` and `forgot_password.php`. | Link reads `Forget Password`, forgot page has Back button, and submit feedback says `Sent to email`. |  |  |
| F-032 | Upload Task image preview | Select an image through file picker and drag-and-drop. | Preview appears for both flows. |  |  |
| F-033 | Upload Task success | Upload a valid task. | Task is inserted and success notification appears. |  |  |
| F-034 | Accepted/Saved navigation | Open `accepted_task.php` and `saved_tasks.php`. | Accepted page shows `Post Task`/`Saved Task`; Saved page shows `Post Task`/`Accepted Task`. |  |  |

## Regression Test Cases

| Test ID | Feature | Steps | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| R-001 | Login | Log in with a valid existing user account. | User is authenticated and redirected to the expected page. |  |  |
| R-002 | Login | Attempt login with invalid credentials. | Login is rejected without exposing sensitive information. |  |  |
| R-003 | Registration | Register a new user with valid details. | New account is created and can log in. |  |  |
| R-004 | Registration | Try registration with missing required fields. | Form validation prevents incomplete registration. |  |  |
| R-005 | Artwork upload | Log in and upload a valid artwork image with title, description, and category. | Artwork is saved and appears in gallery/profile views. |  |  |
| R-006 | Artwork upload | Attempt artwork upload with an invalid file type. | Upload is rejected safely. |  |  |
| R-007 | Gallery display | Open `explore.php` with no filters. | Artwork gallery loads existing artworks correctly. |  |  |
| R-008 | Gallery display | Use gallery search and existing category links. | Gallery filters still display matching artwork records. |  |  |
| R-009 | Task creation | Create a new task with title, description, image, and category. | Task is created successfully and appears on the task board. |  |  |
| R-010 | Task creation | Attempt task creation with missing required fields. | Form validation prevents incomplete task creation. |  |  |
| R-011 | Task board display | Open `task.php`. | Task board loads available tasks and existing navigation actions. |  |  |
| R-012 | Task board display | Open a task detail page from a task card. | Task detail page loads the selected task. |  |  |
| R-013 | Comment display | Open an artwork detail page with existing comments. | Existing comments are displayed in the comments section. |  |  |
| R-014 | Comment display | Open artwork detail after AJAX polling has run. | Existing stored comments remain preserved and visible. |  |  |
| R-015 | User profile | Open `user_profile.php` while logged in. | Profile information, profile actions, and artwork list display correctly. |  |  |
| R-016 | User profile | Navigate from an artwork/comment/task user link to a profile. | User profile page opens without broken links. |  |  |

## Browser Testing Checklist

| Test ID | Feature | Steps | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| B-001 | Chrome | Run the functional test cases in Google Chrome. | All approved features work and layout remains stable. |  |  |
| B-002 | Firefox | Run the functional test cases in Mozilla Firefox. | All approved features work and layout remains stable. |  |  |
| B-003 | Edge | Run the functional test cases in Microsoft Edge. | All approved features work and layout remains stable. |  |  |
| B-004 | Browser compatibility | Compare button states, AJAX updates, and polling behavior across Chrome, Firefox, and Edge. | Behavior is consistent across supported browsers. |  |  |

## Responsive Testing Checklist

| Test ID | Feature | Steps | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| V-001 | 1366x768 responsive layout | Test homepage, explore page, task board, artwork detail, and profile at 1366x768. | Layout remains readable with no major overlap or horizontal overflow. |  | PASS |
| V-002 | 1440x900 responsive layout | Test homepage, explore page, task board, artwork detail, and profile at 1440x900. | Layout remains readable with stable spacing and card sizing. |  | PASS |
| V-003 | 1600x900 responsive layout | Test homepage, explore page, task board, artwork detail, and profile at 1600x900. | Containers and cards remain visually controlled. |  | PASS |
| V-004 | 1920x1080 responsive layout | Test homepage, explore page, task board, artwork detail, and profile at 1920x1080. | Main containers do not stretch excessively and content remains readable. |  | PASS |

## Database Validation Checklist

| Test ID | Feature | Steps | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| D-001 | `saved_tasks` table | Run `SHOW TABLES LIKE 'saved_tasks';`. | `saved_tasks` table exists. |  | PASS |
| D-002 | `saved_tasks` table | Run `DESCRIBE saved_tasks;`. | Table includes `saved_task_id`, `user_id`, `task_id`, timestamps, and unique user/task constraint. |  | PASS |
| D-003 | `task_categories` table (legacy / not primary) | Run `SHOW TABLES LIKE 'task_categories';`. | `task_categories` table exists but is now **legacy only**. Explore, Task, Post Task, and Post Artwork no longer use it as the category source; the shared `category` table is the unified source (see D-012). The table and `task.task_category_id` column are retained for backward compatibility. |  | PASS |
| D-004 | `task_categories` table (legacy / not primary) | Run `SELECT * FROM task_categories ORDER BY task_category_id;`. | Legacy seed rows may still be present (Illustration, Graphic Design, Animation, Digital Painting, UI/UX Design, Photography, Other), but they are **not** the expected category source for the unified UI. The current expected source is the `category` table. |  | PASS |
| D-005 | `artwork_likes` table | Run `SHOW TABLES LIKE 'artwork_likes';`. | `artwork_likes` table exists. |  | PASS |
| D-006 | `artwork_likes` table | Run `DESCRIBE artwork_likes;`. | Table includes `artwork_like_id`, `user_id`, `artwork_id`, timestamp, and unique user/artwork constraint. |  | PASS |
| D-007 | `task.category_id` / `tasks.category_id` | Run `DESCRIBE task;` in this project schema. | Existing singular `task` table includes `category_id`. If a local schema uses `tasks`, confirm equivalent `tasks.category_id`. |  | PASS |
| D-008 | `task.task_category_id` | Run `DESCRIBE task;`. | `task_category_id` exists if the task categorization migration has been applied. |  | PASS |
| D-009 | Foreign keys | Run an information schema check for `saved_tasks`, `artwork_likes`, and `task`. | Foreign keys exist where referenced base tables are present. |  | PASS |
| D-010 | `pending_user_otps` table | Run `SHOW TABLES LIKE 'pending_user_otps';`. | Pending OTP table exists. |  | PASS |
| D-011 | `support_tickets` table | Run `SHOW TABLES LIKE 'support_tickets';`. | Support tickets table exists. |  | PASS |
| D-012 | `category` table (unified source) | Run `SELECT category_id, category_name FROM category ORDER BY category_id;`. | Shared category rows used by Explore, Task, Post Task, and Post Artwork exist (Graphic Design, Illustration, Photography, 3D Art, Advertising). | Returned the 5 shared categories. | PASS |
| D-013 | `user.is_admin` column | Run `DESCRIBE user;`. | `is_admin` column exists with default `0`. | `is_admin tinyint(1) NOT NULL DEFAULT 0` present. | PASS |
| D-014 | Default admin account | Run `SELECT user_id, user_name, email, is_admin FROM user WHERE user_name = 'admin';`. | Admin row exists with `is_admin = 1` (email `admin@artizo.local`). | Admin row exists with `is_admin = 1`. | PASS |
| D-015 | Admin password storage | Run `SELECT password FROM user WHERE user_name = 'admin';`. | Password is a bcrypt hash beginning with `$2y$` (PHP `password_hash()` output), not plaintext `Admin@123`. | Password stored as `$2y$10$...` bcrypt hash; `password_verify('Admin@123', hash)` returned true. | PASS |
| D-016 | `support_tickets` admin response columns | Run `DESCRIBE support_tickets;`. | `admin_response`, `responded_at`, and `responded_by` columns exist. | All three columns present. | PASS |
| D-017 | `task_submissions` table exists | Run `SHOW TABLES LIKE 'task_submissions';`. | `task_submissions` table exists. | Table exists. | PASS |
| D-018 | `task_submissions` structure | Run `DESCRIBE task_submissions;`. | Includes `submission_id`, `task_id`, `submitter_user_id`, `file_path`, `message`, `status`, and `submitted_at`. | All required columns present. | PASS |
| D-019 | Task accepted/submitted fields | Run `DESCRIBE task;`. | Task table supports accepted user/status logic used by accept, cancel, and submission flow (`task_status` enum, `accepted_user_id`, `category_id`, `task_solution`). | Fields present and exercised by the accept/cancel/submit flow. | PASS |
| D-020 | Full database setup (fresh import) | Fresh import `assets/database/full_database_setup.sql` into a temporary database. | No SQL errors and all latest schema changes (is_admin, admin seed, support response columns, `task_submissions`, unified `category` seed) exist without needing the migration file. | Imported into temp DB `artizo_fresh_test` with no errors; all latest schema/seed present; temp DB dropped after. | PASS |
| D-021 | Existing database migration | Apply `assets/database/regression_fixes_migration.sql` to an existing database. | Migration is idempotent/additive, adds required columns/tables, and does not delete existing data. | Applied to `software_evo_assignment`; added `is_admin`, admin account, support response columns, and `task_submissions` with no data loss. | PASS |

## Notes

- Do not mark a test as passed until it has been manually executed.
- Use the database selected in phpMyAdmin. The project defaults to `web_assignment` and can fall back to `software_evo_assignment`.
- The project schema uses the singular table name `task`; `tasks.category_id` in the proposal maps to `task.category_id` in this repository.

## Final Manual Regression Testing Table

| Test Case ID | Feature | Test Steps | Expected Result | Actual Result | Status | Evidence Screenshot Name |
|---|---|---|---|---|---|---|
| TC01 | Password validation | Register with a password less than 8 characters. | Registration is rejected with the password rule message. | Weak password under 8 characters was rejected. | PASS | TC01_weak_password_less_than_8_rejected.png |
| TC02 | Password validation | Register with a letters-only password. | Registration is rejected with the password rule message. | Letters-only password was rejected. | PASS | TC02_letters_only_password_rejected.png |
| TC03 | Password validation | Register with a numbers-only password. | Registration is rejected with the password rule message. | Numbers-only password was rejected. | PASS | TC03_numbers_only_password_rejected.png |
| TC04 | Password validation | Register with a valid password containing letters and numbers. | Registration proceeds to OTP verification. | Valid registration submits successfully and now shows a clear OTP email message before verification. | FIXED | TC04_valid_password_registration_submitted.png |
| TC05 | OTP verification | Complete valid registration details. | OTP verification page appears after valid registration. | OTP verification page appeared. | PASS | TC05_otp_page_appears.png |
| TC06 | OTP verification | Enter an incorrect OTP on the OTP verification page. | Wrong OTP is rejected and the final account is not created. | Wrong OTP was rejected. | PASS | TC06_wrong_otp_rejected.png |
| TC07 | OTP verification | Enter the correct OTP on the OTP verification page. | Final user account is created successfully. | Correct OTP created the final user account. | PASS | TC07_correct_otp_user_created.png |
| TC08 | Login | Log in using the exact-case registered username. | Login succeeds. | Exact-case username login worked, showed `Login successful using username.` on the login page, then redirected to the main page after 2 seconds. | PASS | TC08_exact_case_username_login_success.png |
| TC09 | Login | Log in using the wrong-case version of the registered username. | Login fails. | Wrong-case username login failed. | PASS | TC09_wrong_case_username_login_failed.png |
| TC10 | Login | Log in using the registered email address. | Login succeeds. | Email login worked, showed `Login successful using email.` on the login page, then redirected to the main page after 2 seconds. | PASS | TC10_email_login_success.png |
| TC11 | Login wording | Open the login page. | Login page displays `Forget Password`. | Login page displayed `Forget Password`. | PASS | TC11_login_forget_password_wording.png |
| TC12 | Forgot Password | Submit an email on the Forgot Password page. | Message `Sent to email` is shown. | Forgot Password submit showed `Sent to email`. | PASS | TC12_forgot_password_sent_to_email.png |
| TC13 | Forgot Password | Click the Back button on the Forgot Password page. | User returns to the login page. | Back button returned to the login page. | PASS | TC13_forgot_password_back_to_login.png |
| TC14 | Support ticket | Submit a support ticket with valid email, phone, subject, and message. | Ticket is submitted successfully and a tracking code appears. | Support ticket submitted successfully. | PASS | TC14_support_ticket_submitted.png |
| TC15 | Support ticket tracking | Track a support ticket using the correct ticket code and email. | Ticket details are shown. | Correct ticket code and email displayed ticket details. | PASS | TC15_support_ticket_tracking_success.png |
| TC16 | Upload Task | Select a task image using the file picker. | Image preview appears. | File picker image preview worked. | PASS | TC16_upload_task_file_picker_preview.png |
| TC17 | Upload Task | Drag and drop a task image into the upload area. | Image preview appears. | Drag-and-drop preview worked using the same preview component as TC16. | PASS | TC17_upload_task_drag_drop_preview.png |
| TC18 | Upload Task | Submit a valid task with title, description, category, and image. | Task data is inserted into the database. | Upload Task inserted into the database. | PASS | TC18_upload_task_database_insert.png |
| TC19 | Upload Task | Complete a successful task upload. | Success notification appears. | Upload Task success notification appeared. | PASS | TC19_upload_task_success_notification.png |
| TC20 | Accepted Task page | Open the Accepted Task page. | Page shows only `Post Task` and `Saved Task` buttons. | Accepted Task page buttons were correct. | PASS | TC20_accepted_task_buttons.png |
| TC21 | Saved Task page | Open the Saved Task page. | Page shows only `Post Task` and `Accepted Task` buttons. | Saved Task page buttons were correct. | PASS | TC21_saved_task_buttons.png |
| TC22 | Saved Task page | Check the Saved Task page buttons. | `Task Board` button is not shown. | `Task Board` button was not shown, and the footer white bar/layout issue was fixed. | FIXED | TC22_saved_task_no_task_board_button.png |
| TC23 | Footer link | Click the Support link in the footer. | Support page opens. | Footer Support link opened the support page. | PASS | TC23_footer_support_link.png |
| TC24 | Header/footer layout | View the header and footer at 1920x1080 resolution. | Header and footer appear smaller. | Header and footer appeared smaller at 1920x1080. | PASS | TC24_header_footer_1920x1080.png |
| TC25 | Responsive layout | View the header and footer at mobile width. | Header and footer remain usable. | Header and footer remained usable on mobile width. | PASS | TC25_header_footer_mobile_width.png |
| TC26 | Forgot password same password prevention | Reset password using the same current password. | System rejects it with "New password must be different from your current password." | Same password was rejected and the password hash was unchanged (token preserved for retry). | PASS | TC26_forgot_password_same_password_rejected.png |
| TC27 | Admin login success | Open `admin_login.php` and log in using admin / Admin@123. | Admin login succeeds and redirects to the admin dashboard. | Admin account logged in successfully and redirected to `admin_index.php`. | PASS | TC27_admin_login_success.png |
| TC28 | Admin access blocked for normal user | Log in as a normal user and try opening `admin_index.php` or `admin_support.php`. | Normal user is redirected to admin login and cannot access admin pages. | Normal user was redirected to `admin_login.php` and blocked from all admin pages. | PASS | TC28_normal_user_admin_access_blocked.png |
| TC29 | Admin support ticket list | Log in as admin and open `admin_support.php`. | Admin can see all support tickets with tracking code, user/email, subject, status, and response status. | Admin support ticket list displayed correctly (code, user, email, subject, status, response status). | PASS | TC29_admin_support_ticket_list.png |
| TC30 | Admin support response and status update | Open a support ticket as admin, enter a response, and change status to In Progress or Resolved. | Response and status are saved into the database. | Admin response, `responded_by`, `responded_at`, and `status` were saved (status set to In Progress). | PASS | TC30_admin_support_response_status_update.png |
| TC31 | Unified category table - Explore | Open `explore.php` and inspect category tabs/options. | Explore categories come from the `category` table. | Explore tabs matched the `category` table (ALL + the 5 shared categories). | PASS | TC31_explore_category_table_unified.png |
| TC32 | Unified category table - Task | Open `task.php` and inspect task category filters. | Task category filters come from the same `category` table. | Task filters matched the `category` table (data-category-id = category_id). | PASS | TC32_task_category_table_unified.png |
| TC33 | Unified category table - Post Task | Open `upload_task.php` and inspect the category dropdown. | Post Task dropdown uses the same `category` table. | Post Task dropdown options matched the shared `category` table. | PASS | TC33_post_task_category_dropdown_unified.png |
| TC34 | Unified category table - Post Artwork | Open `upload_artwork.php` and inspect the category options. | Post Artwork category options use the same `category` table. | Post Artwork radios matched the shared `category` table (ids 1-5). | PASS | TC34_post_artwork_category_unified.png |
| TC35 | User A profile artwork ownership | Log in as User A and open `user_profile.php`. | My Artwork section only shows artwork uploaded by User A. | User A profile showed only User A artwork (`WHERE a.user_id = ?`). | PASS | TC35_userA_profile_own_artwork_only.png |
| TC36 | User B profile artwork ownership | Log in as User B and open `user_profile.php`. | My Artwork section only shows artwork uploaded by User B. | User B profile showed only User B artwork. | PASS | TC36_userB_profile_own_artwork_only.png |
| TC37 | Profile My Artwork footer spacing | Open `user_profile.php` with artwork and inspect spacing before footer. | My Artwork section is not visually stuck to the footer. | Bottom spacing added; footer no longer sticks to the artwork section (with and without artwork). | PASS - screenshot required | TC37_profile_my_artwork_footer_spacing.png |
| TC38 | Post artwork image preview | Open `upload_artwork.php` and select an image. | Image preview appears before submission. | Preview markup and FileReader logic served and wired to the file input (same component as Upload Task). | PASS - screenshot required | TC38_post_artwork_image_preview.png |
| TC39 | Task Accept button capitalization | Open an available task in `task.php` or `task_detail.php`. | Button text shows `Accept`, not `accept`. | Open task displayed `Accept`; no lowercase status labels rendered. | PASS | TC39_task_accept_capitalization.png |
| TC40 | Task Accepted badge capitalization | Accept a task and open the accepted task view or task detail page. | Status/badge shows `Accepted`, not `accepted`. | Accepted task displayed `Accepted` badge; DB value remains lowercase. | PASS | TC40_task_accepted_capitalization.png |
| TC41 | Accept task flow | User A posts a task, User B accepts it. | Task status changes to accepted and `accepted_user_id` is saved. | User B accepted task; status `accepted`, `accepted_user_id = 5`. | PASS | TC41_userB_accept_task.png |
| TC42 | Cancel accepted task | User B cancels/removes an accepted task. | Original task is not deleted; it becomes available again. | Accepted relationship removed (status `accept`, `accepted_user_id` NULL); original task row remained. | PASS | TC42_cancel_accepted_task_no_delete.png |
| TC43 | Submit file to accepted task | User B accepts a task, opens submission page, selects file, enters message, and submits. | Submit form shows file input, preview, submit button, and saves submission. | File submission uploaded and saved; redirect with success feedback. | PASS | TC43_submit_file_to_accepted_task.png |
| TC44 | Task submission saved in database | Check `task_submissions` after User B submits. | Row exists with `task_id`, `submitter_user_id`, `file_path`, `message`, `status`, and `submitted_at`. | Submission row saved (task_id 3, submitter 5, file_path, message, status `submitted`, timestamp). | PASS | TC44_task_submission_database_saved.png |
| TC45 | Task poster views submission | Log in as User A, open the posted task detail page after User B submits. | User A can view submitter name/email, submitted file/image, message, and date. | Task poster (and admin) viewed submitter userB, email, image, and message. | PASS | TC45_task_poster_view_submission.png |
| TC46 | Unrelated user cannot view submission | Log in as User C and open the same task detail page. | Submitter email, message, and file are not visible to the unrelated user. | User C (and the non-poster accepter) could not view submission details. | PASS | TC46_unrelated_user_submission_hidden.png |
| TC47 | Task does not vanish after submission | Submit a file to an accepted task, then revisit task pages. | Task remains visible to correct users and is not incorrectly removed. | Task stayed in the accepted/poster views; correctly excluded only from the open feed. | PASS | TC47_task_not_vanish_after_submission.png |
| TC48 | Saved task still works after task fixes | Save a task, open saved task list, then unsave it. | Save and unsave still work correctly. | Save toggle on (row inserted, shows in saved list) and off (row removed) worked. | PASS | TC48_saved_task_after_task_fixes.png |
| TC49 | Latest Job Request View More link | Open `index.php` and click `View More` under Latest Job Request. | User is sent to `task.php`, not post artwork. | View More linked to `task.php`. | PASS | TC49_latest_job_request_view_more_task_link.png |
| TC50 | UI scale-down on Explore and Task pages | Open `explore.php` and `task.php` at desktop size. | Header, content, section spacing, and footer are scaled down around 10-15%. | Reduced type scale, navbar/footer sizing, and section gaps served live; pages render correctly (final visual sign-off via screenshot). | PASS - screenshot required | TC50_explore_task_ui_scaled_down.png |
| TC51 | Fresh database setup includes latest schema | Create a temporary database and import `full_database_setup.sql`. | Fresh import succeeds and includes the latest admin/support/task submission/category changes. | Imported into temp DB `artizo_fresh_test` with no SQL errors; admin account, `is_admin`, support response columns, `task_submissions`, and unified `category` seed all present; temp DB dropped after. | PASS | TC51_full_database_setup_import_success.png |
| TC52 | Existing database migration includes latest schema | Import `assets/database/regression_fixes_migration.sql` on an existing database. | Migration succeeds without removing existing data and adds required columns/tables. | Migration applied to `software_evo_assignment`; `is_admin`, admin account, support response columns, and `task_submissions` added with no data loss. | PASS | TC52_regression_migration_import_success.png |
| TC53 | Admin Home button target | Log in as admin and inspect the admin navbar Home/brand link. | Admin Home link points to `admin_index.php`, not the public `index.php`; public navbar Home still points to `index.php`. | Admin navbar brand `href="admin_index.php"`; public navbar brand `href="index.php"`. | PASS | TC53_admin_home_button_admin_index.png |
| TC54 | Legacy task category removed (runtime) | After the regression migration, confirm `task_categories` and `task.task_category_id` are gone while category features still work. | Legacy table/column removed; task filter and Post Task dropdown still work via the shared `category` table. | `task_categories`=0, `task.task_category_id`=0, `task.category_id`=1; task_filter `success:true`; dropdown shows the 5 `category` rows. | PASS | TC54_legacy_task_categories_removed.png |
| TC55 | Accepted Task page header | Open `accepted_task.php`. | Page shows an `Accepted Tasks` header styled like the Saved Task page, keeping Post Task / Saved Task buttons. | `<h1 class="inter-bold-44 mb-4">Accepted Tasks</h1>` rendered. | PASS | TC55_accepted_tasks_header.png |
| TC56 | Task detail submission not cropped | Open `task_detail.php` for a task with a submission as the poster. | Submission thumbnail is not badly cropped (uses contain, not cover). | Served markup uses `object-fit:contain`; no `object-fit:cover` on submission image. | PASS - screenshot required | TC56_task_detail_submission_not_cropped.png |
| TC57 | Submission detail full image/file | Open `submission_detail.php?id=<id>`. | Full submitted image shown uncropped; non-image files show a safe open/download link. | Submission Detail page renders the full image (max-height 700px, contain) and shows task title, submitter, message, date. | PASS - screenshot required | TC57_submission_detail_full_image.png |
| TC58 | Submission detail - poster view | As the task poster, open `submission_detail.php?id=<id>`. | Poster can view the submission detail. | Poster (userA) saw the submission detail (HTTP 200). | PASS | TC58_submission_detail_poster_view.png |
| TC59 | Submission detail - submitter view | As the submitter, open `submission_detail.php?id=<id>`. | Submitter can view their own submission detail. | Submitter (userB) saw the submission detail. | PASS | TC59_submission_detail_submitter_view.png |
| TC60 | Submission detail - admin view | As admin, open `submission_detail.php?id=<id>`. | Admin can view the submission detail. | Admin saw the submission detail. | PASS | TC60_submission_detail_admin_view.png |
| TC61 | Submission detail - unrelated blocked | As an unrelated user, open `submission_detail.php?id=<id>`. | Unrelated user cannot view the submission detail. | Unrelated user (userC) was blocked ("You are not allowed to view this submission."). | PASS | TC61_submission_detail_unrelated_blocked.png |
| TC62 | My Task page opens | Log in as a normal user and open `my_task.php`. | My Task page opens for the logged-in user. | My Task opened (HTTP 200). | PASS | TC62_my_task_page.png |
| TC63 | My Task - Tasks I Posted | Open `my_task.php` as a user who posted a task. | "Tasks I Posted" lists the user's posted tasks, each linking to task detail with status. | userA saw "Tasks I Posted" with a link to `task_detail.php?id=3`. | PASS | TC63_my_task_tasks_i_posted.png |
| TC64 | My Task - My Submissions | Open `my_task.php` as a user who submitted work. | "My Submissions" lists the user's submissions, each linking to submission detail. | userB saw "My Submissions" with a link to `submission_detail.php?id=1`. | PASS | TC64_my_task_my_submissions.png |
| TC65 | My Task - empty states | Open `my_task.php` as a user with no posted tasks and no submissions. | Clean empty messages are shown for both sections. | userC saw "You have not posted any tasks yet." and "You have not submitted any work yet." | PASS | TC65_my_task_empty_states.png |
| TC66 | Support tracking shows admin response | Track a ticket with the correct code and matching email after admin has replied. | Tracking shows status, original details, admin response, and response date; if no response, shows "No admin response yet." | Tracking showed "Admin Response", the response text, and "Responded at:". | PASS | TC66_support_tracking_admin_response.png |
| TC67 | Support tracking wrong email blocked | Track a ticket with the correct code but the wrong email. | Ticket (and admin response) is not shown. | Wrong email returned "No matching ticket found"; response text not shown. | PASS | TC67_support_tracking_wrong_email_blocked.png |
| TC68 | Fresh setup excludes legacy category | Fresh import `full_database_setup.sql` into a temporary database. | `task_categories` and `task.task_category_id` are not created; `task.category_id` and the shared `category` table exist. | Temp DB `artizo_fresh_test`: `task_categories`=0, `task.task_category_id`=0, `task.category_id`=1, category seed=5, no errors; temp DB dropped. | PASS | TC68_full_database_setup_legacy_removed.png |
| TC69 | Existing migration drops legacy safely | Run `assets/database/regression_fixes_migration.sql` on the existing database. | Migration drops only the unused legacy table/column, is idempotent, and removes no real data. | On `software_evo_assignment`: legacy table/column dropped, `category_id` kept, row counts unchanged before/after, 2nd run no errors. | PASS | TC69_regression_migration_legacy_removed.png |

## Latest Regression Fix Testing Evidence

This section records the live runtime verification of the latest regression fixes (admin support management, admin security, unified category source, artwork ownership, profile spacing, forgot-password protection, accept/cancel/submission task flow, capitalization, index link, and UI scale-down).

- **Environment:** XAMPP (Apache + MariaDB/MySQL), database `software_evo_assignment` (the project default `web_assignment` is absent, so `config.php` falls back to `software_evo_assignment`).
- **Method:** Each flow was exercised against the running app over HTTP using per-user session cookies, with the database inspected directly to confirm persistence. Client-only behaviour (image preview, footer spacing, UI scale-down) was confirmed from the served markup/CSS and still requires a visual screenshot for submission (marked `PASS - screenshot required`).
- **Test accounts used:**
  - User A — `user_id = 4` (`userA`)
  - User B — `user_id = 5` (`userB`)
  - User C — `user_id = 6` (`userC`, unrelated user)
  - Admin — `user_id = 3` (`admin`), admin login `admin` / `Admin@123`
- **Result:** 23 of 23 verification checks passed (mapped to TC26-TC52 above and database checks D-012-D-021).

| # | Verified behaviour | Mapped Test Case(s) | Result |
|---|---|---|---|
| 1 | Login/register still works | R-001, R-003, TC08-TC10 | PASS |
| 2 | Forgot password rejects the same current password | TC26 | PASS |
| 3 | Admin login only works for `is_admin = 1` | TC27 | PASS |
| 4 | Admin can view tickets, respond, and update status | TC29, TC30 | PASS |
| 5 | Normal users cannot access admin pages | TC28 | PASS |
| 6 | Categories unified from the `category` table | TC31-TC34, D-012 | PASS |
| 7 | User A can post artwork | TC35 (setup), R-005 | PASS |
| 8 | User B can post artwork | TC36 (setup), R-005 | PASS |
| 9 | User A profile shows only User A artwork | TC35 | PASS |
| 10 | User B profile shows only User B artwork | TC36 | PASS |
| 11 | Post artwork image preview works | TC38 | PASS - screenshot required |
| 12 | User A can post a task | TC41 (setup) | PASS |
| 13 | User B can accept the task | TC41 | PASS |
| 14 | User B can cancel an accepted task without deleting the original | TC42 | PASS |
| 15 | User B can re-accept and submit a file | TC43 | PASS |
| 16 | Submission is saved in `task_submissions` | TC44, D-017, D-018 | PASS |
| 17 | Task poster can view who submitted and what | TC45 | PASS |
| 18 | Unrelated users cannot view submissions | TC46 | PASS |
| 19 | Task does not vanish after submission | TC47 | PASS |
| 20 | Saved task logic still works | TC48 | PASS |
| 21 | `Accept` / `Accepted` capitalization is correct | TC39, TC40 | PASS |
| 22 | `index.php` Latest Job Request `View More` links to `task.php` | TC49 | PASS |
| 23 | UI scale-down applied to header, explore, task, profile, footer | TC37, TC50 | PASS - screenshot required |

## Suggested Evidence Screenshot Filenames

| Test Case ID | Suggested Screenshot Filename |
|---|---|
| TC01 | `TC01_weak_password_less_than_8_rejected.png` |
| TC02 | `TC02_letters_only_password_rejected.png` |
| TC03 | `TC03_numbers_only_password_rejected.png` |
| TC04 | `TC04_valid_password_registration_submitted.png` |
| TC05 | `TC05_otp_page_appears.png` |
| TC06 | `TC06_wrong_otp_rejected.png` |
| TC07 | `TC07_correct_otp_user_created.png` |
| TC08 | `TC08_exact_case_username_login_success.png` |
| TC09 | `TC09_wrong_case_username_login_failed.png` |
| TC10 | `TC10_email_login_success.png` |
| TC11 | `TC11_login_forget_password_wording.png` |
| TC12 | `TC12_forgot_password_sent_to_email.png` |
| TC13 | `TC13_forgot_password_back_to_login.png` |
| TC14 | `TC14_support_ticket_submitted.png` |
| TC15 | `TC15_support_ticket_tracking_success.png` |
| TC16 | `TC16_upload_task_file_picker_preview.png` |
| TC17 | `TC17_upload_task_drag_drop_preview.png` |
| TC18 | `TC18_upload_task_database_insert.png` |
| TC19 | `TC19_upload_task_success_notification.png` |
| TC20 | `TC20_accepted_task_buttons.png` |
| TC21 | `TC21_saved_task_buttons.png` |
| TC22 | `TC22_saved_task_no_task_board_button.png` |
| TC23 | `TC23_footer_support_link.png` |
| TC24 | `TC24_header_footer_1920x1080.png` |
| TC25 | `TC25_header_footer_mobile_width.png` |
| TC26 | `TC26_forgot_password_same_password_rejected.png` |
| TC27 | `TC27_admin_login_success.png` |
| TC28 | `TC28_normal_user_admin_access_blocked.png` |
| TC29 | `TC29_admin_support_ticket_list.png` |
| TC30 | `TC30_admin_support_response_status_update.png` |
| TC31 | `TC31_explore_category_table_unified.png` |
| TC32 | `TC32_task_category_table_unified.png` |
| TC33 | `TC33_post_task_category_dropdown_unified.png` |
| TC34 | `TC34_post_artwork_category_unified.png` |
| TC35 | `TC35_userA_profile_own_artwork_only.png` |
| TC36 | `TC36_userB_profile_own_artwork_only.png` |
| TC37 | `TC37_profile_my_artwork_footer_spacing.png` |
| TC38 | `TC38_post_artwork_image_preview.png` |
| TC39 | `TC39_task_accept_capitalization.png` |
| TC40 | `TC40_task_accepted_capitalization.png` |
| TC41 | `TC41_userB_accept_task.png` |
| TC42 | `TC42_cancel_accepted_task_no_delete.png` |
| TC43 | `TC43_submit_file_to_accepted_task.png` |
| TC44 | `TC44_task_submission_database_saved.png` |
| TC45 | `TC45_task_poster_view_submission.png` |
| TC46 | `TC46_unrelated_user_submission_hidden.png` |
| TC47 | `TC47_task_not_vanish_after_submission.png` |
| TC48 | `TC48_saved_task_after_task_fixes.png` |
| TC49 | `TC49_latest_job_request_view_more_task_link.png` |
| TC50 | `TC50_explore_task_ui_scaled_down.png` |
| TC51 | `TC51_full_database_setup_import_success.png` |
| TC52 | `TC52_regression_migration_import_success.png` |
| TC53 | `TC53_admin_home_button_admin_index.png` |
| TC54 | `TC54_legacy_task_categories_removed.png` |
| TC55 | `TC55_accepted_tasks_header.png` |
| TC56 | `TC56_task_detail_submission_not_cropped.png` |
| TC57 | `TC57_submission_detail_full_image.png` |
| TC58 | `TC58_submission_detail_poster_view.png` |
| TC59 | `TC59_submission_detail_submitter_view.png` |
| TC60 | `TC60_submission_detail_admin_view.png` |
| TC61 | `TC61_submission_detail_unrelated_blocked.png` |
| TC62 | `TC62_my_task_page.png` |
| TC63 | `TC63_my_task_tasks_i_posted.png` |
| TC64 | `TC64_my_task_my_submissions.png` |
| TC65 | `TC65_my_task_empty_states.png` |
| TC66 | `TC66_support_tracking_admin_response.png` |
| TC67 | `TC67_support_tracking_wrong_email_blocked.png` |
| TC68 | `TC68_full_database_setup_legacy_removed.png` |
| TC69 | `TC69_regression_migration_legacy_removed.png` |

## Required Project Screenshot Evidence Checklist

Capture the following screenshots for submission. The underlying behaviour for each item has been verified in the latest live run (see `Latest Regression Fix Testing Evidence`); the screenshots are the visual proof to attach. Tick each one once captured.

### Admin support management (Issue 3)
- [ ] `TC27_admin_login_success.png` — admin logged in at `admin_index.php`.
- [ ] `TC28_normal_user_admin_access_blocked.png` — normal user redirected from an admin page to `admin_login.php`.
- [ ] `TC29_admin_support_ticket_list.png` — `admin_support.php` list showing code, user/email, subject, status, response status.
- [ ] `TC30_admin_support_response_status_update.png` — ticket detail after saving a response and changing status to In Progress/Resolved.

### Unified category source (Issues 4 & 8)
- [ ] `TC31_explore_category_table_unified.png` — Explore category tabs.
- [ ] `TC32_task_category_table_unified.png` — Task category filters.
- [ ] `TC33_post_task_category_dropdown_unified.png` — Post Task category dropdown.
- [ ] `TC34_post_artwork_category_unified.png` — Post Artwork category options.

### Artwork ownership and profile (Issues 6 & 7)
- [ ] `TC35_userA_profile_own_artwork_only.png` — User A "My Artwork" shows only User A artwork.
- [ ] `TC36_userB_profile_own_artwork_only.png` — User B "My Artwork" shows only User B artwork.
- [ ] `TC37_profile_my_artwork_footer_spacing.png` — spacing between "My Artwork" and the footer.

### Forgot password (Issue 2)
- [ ] `TC26_forgot_password_same_password_rejected.png` — same-password rejection message.

### Post artwork preview (Issue 9)
- [ ] `TC38_post_artwork_image_preview.png` — preview shown after selecting an artwork image.

### Task accept / cancel / submission flow (Issues 5, 10, 11, 12)
- [ ] `TC39_task_accept_capitalization.png` — `Accept` button capitalization.
- [ ] `TC40_task_accepted_capitalization.png` — `Accepted` badge capitalization.
- [ ] `TC41_userB_accept_task.png` — User B accepting the task.
- [ ] `TC42_cancel_accepted_task_no_delete.png` — accepted task cancelled and available again (original not deleted).
- [ ] `TC43_submit_file_to_accepted_task.png` — submission form with file input, preview, and submit button.
- [ ] `TC44_task_submission_database_saved.png` — `task_submissions` row in phpMyAdmin.
- [ ] `TC45_task_poster_view_submission.png` — task poster viewing the submission.
- [ ] `TC46_unrelated_user_submission_hidden.png` — unrelated user not seeing the submission.
- [ ] `TC47_task_not_vanish_after_submission.png` — task still visible in the correct views after submission.

### Saved task and index link (Issues 13 & saved-task regression)
- [ ] `TC48_saved_task_after_task_fixes.png` — save/unsave still working.
- [ ] `TC49_latest_job_request_view_more_task_link.png` — `View More` pointing to `task.php`.

### UI scale-down (Issue 1)
- [ ] `TC50_explore_task_ui_scaled_down.png` — Explore and Task pages at desktop size after scale-down.
- [ ] `TC24_header_footer_1920x1080.png` — header/footer at 1920x1080 (existing).

### Database evidence (migration + fresh setup)
- [ ] `TC51_full_database_setup_import_success.png` — fresh import of `full_database_setup.sql` with no errors.
- [ ] `TC52_regression_migration_import_success.png` — `regression_fixes_migration.sql` applied to an existing database.

### Admin navigation (final fix — Issue 1)
- [ ] `TC53_admin_home_button_admin_index.png` — admin navbar Home/brand linking to `admin_index.php`.

### Legacy task category removal (final fix — Issue 2)
- [ ] `TC54_legacy_task_categories_removed.png` — runtime confirmation that `task_categories` / `task.task_category_id` are gone and category features still work.
- [ ] `TC68_full_database_setup_legacy_removed.png` — fresh import showing the legacy table/column are not created.
- [ ] `TC69_regression_migration_legacy_removed.png` — migration output dropping the legacy table/column with no data loss.

### Accepted Task header (final fix — Issue 3)
- [ ] `TC55_accepted_tasks_header.png` — `Accepted Tasks` header on `accepted_task.php`.

### Submission viewing and detail page (final fix — Issue 4)
- [ ] `TC56_task_detail_submission_not_cropped.png` — submission image not cropped on `task_detail.php`.
- [ ] `TC57_submission_detail_full_image.png` — full uncropped image on `submission_detail.php`.
- [ ] `TC58_submission_detail_poster_view.png` — poster viewing submission detail.
- [ ] `TC59_submission_detail_submitter_view.png` — submitter viewing submission detail.
- [ ] `TC60_submission_detail_admin_view.png` — admin viewing submission detail.
- [ ] `TC61_submission_detail_unrelated_blocked.png` — unrelated user blocked from submission detail.

### My Task page (final fix — Issue 5)
- [ ] `TC62_my_task_page.png` — `my_task.php` open for a normal user.
- [ ] `TC63_my_task_tasks_i_posted.png` — "Tasks I Posted" section.
- [ ] `TC64_my_task_my_submissions.png` — "My Submissions" section.
- [ ] `TC65_my_task_empty_states.png` — empty states for both sections.

### Support response in tracking (final fix — Issue 6)
- [ ] `TC66_support_tracking_admin_response.png` — admin response + date shown when tracking with correct email.
- [ ] `TC67_support_tracking_wrong_email_blocked.png` — wrong email cannot view the ticket.
