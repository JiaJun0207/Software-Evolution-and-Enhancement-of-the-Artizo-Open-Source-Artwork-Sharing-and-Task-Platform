PASS# CSE6364 Final Testing Plan

Project: Software Evolution and Enhancement of the Artizo Open-Source Artwork Sharing and Task Platform

This document lists manual test cases for the approved proposal improvements and regression areas. Actual Result and Status must be completed only after manual testing is performed.

## Functional Test Cases


| Test ID | Feature                             | Steps                                                                                                              | Expected Result                                                                                     | Actual Result | Status |
| ------- | ----------------------------------- | ------------------------------------------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------- | ------------- | ------ |
| F-001   | 1920x1080 responsive layout         | Open the homepage, explore page, task board, artwork detail page, and profile page at 1920x1080.                   | Main containers remain readable, cards do not stretch too wide, and spacing remains consistent.     |               |        |
| F-002   | 1920x1080 responsive layout         | Inspect artwork gallery cards and task cards at 1920x1080.                                                         | Cards keep controlled widths and maintain visual alignment.                                         |               |        |
| F-003   | Text contrast improvement           | Review category labels, card subtitles, placeholder text, artwork captions, task descriptions, buttons, and links. | Text is readable against its background and weak contrast areas are improved.                       |               |        |
| F-004   | Text contrast improvement           | Hover/focus interactive buttons and links where applicable.                                                        | Hover/focus states remain visible and readable.                                                     |               |        |
| F-005   | Save Task feature                   | Log in, open`task.php`, and click `Save` on an available task card.                                                | Button changes to`Saved` without a full page reload.                                                |               |        |
| F-006   | Save Task feature                   | Refresh`task.php` after saving a task.                                                                             | Previously saved task still shows the saved state.                                                  |               |        |
| F-007   | Save Task feature                   | Open`saved_tasks.php` after saving a task.                                                                         | Saved task appears in the saved tasks list.                                                         |               |        |
| F-008   | Save Task feature                   | Click`Saved` or unsave from the saved tasks view.                                                                  | Task is unsaved and removed or updated without breaking the page.                                   |               |        |
| F-009   | Task categorization and filtering   | Open`upload_task.php`.                                                                                             | Task category dropdown is visible and populated from`task_categories`.                              |               |        |
| F-010   | Task categorization and filtering   | Create a task with a selected category.                                                                            | New task is saved with the selected category ID.                                                    |               |        |
| F-011   | Task categorization and filtering   | Open`task.php` and inspect task cards.                                                                             | Task card displays a category label.                                                                |               |        |
| F-012   | Task categorization and filtering   | Click a category filter on the task board.                                                                         | Task list updates using AJAX/fetch without a full page reload.                                      |               |        |
| F-013   | Task categorization and filtering   | Use search text together with a category filter.                                                                   | Task results match both the search and selected category.                                           |               |        |
| F-014   | Artwork Like feature                | Log in, open`explore.php`, and click `Like` on an artwork card.                                                    | Button changes to liked state and like count increases without page reload.                         |               |        |
| F-015   | Artwork Like feature                | Refresh`explore.php` after liking an artwork.                                                                      | Liked artwork still shows active liked state.                                                       |               |        |
| F-016   | Artwork Like feature                | Open the liked artwork in`artwork_detail.php`.                                                                     | Detail page shows active liked state and correct like count.                                        |               |        |
| F-017   | Artwork Like feature                | Click`Unlike` on the artwork detail page.                                                                          | Button returns to inactive state and like count decreases.                                          |               |        |
| F-018   | AJAX comment submission and refresh | Open`artwork_detail.php?id=<existing artwork id>`, enter a comment, and submit it.                                 | New comment appears immediately without a full page reload.                                         |               |        |
| F-019   | AJAX comment submission and refresh | Refresh the artwork detail page after submitting a comment.                                                        | Submitted comment remains visible from the database.                                                |               |        |
| F-020   | AJAX comment submission and refresh | Open the same artwork detail page in two browser sessions and submit a comment in one session.                     | Other session receives the new comment within approximately 5 seconds.                              |               |        |
| F-021   | AJAX comment submission and refresh | Let polling run after a local comment submission.                                                                  | The same comment is not duplicated in the DOM.                                                      |               |        |
| F-022   | Header/footer regression            | Open any main page at 1920x1080 and mobile width.                                                                  | Header menu bar and footer appear smaller and remain usable.                                        |               |        |
| F-023   | Footer Support link                 | Click`Support` in the footer.                                                                                      | `support.php` opens and shows the ticket tracking form.                                             |               |        |
| F-024   | Support ticket submission           | Log in, then submit email, phone, subject, and message on`support.php`.                                            | Ticket is stored and a tracking code is shown.                                                      |               |        |
| F-025   | Support ticket tracking             | Track a ticket using its ticket code and matching email while logged out.                                          | Ticket status and details are shown.                                                                |               |        |
| F-026   | Support ticket ownership            | Track another user's ticket code with the wrong email.                                                             | Ticket is not shown.                                                                                |               |        |
| F-027   | Registration password rule          | Register with fewer than 8 characters, letters only, and numbers only.                                             | Each invalid password is rejected.                                                                  |               |        |
| F-028   | Registration OTP                    | Register with a valid password and verify with OTP.                                                                | Account is created only after correct OTP.                                                          |               |        |
| F-029   | Email login                         | Log in with a registered email address.                                                                            | Login succeeds.                                                                                     |               |        |
| F-030   | Case-sensitive username login       | Log in with exact-case username, then wrong-case username.                                                         | Exact case succeeds and wrong case fails.                                                           |               |        |
| F-031   | Forgot password wording             | Open`login.php` and `forgot_password.php`.                                                                         | Link reads`Forget Password`, forgot page has Back button, and submit feedback says `Sent to email`. |               |        |
| F-032   | Upload Task image preview           | Select an image through file picker and drag-and-drop.                                                             | Preview appears for both flows.                                                                     |               |        |
| F-033   | Upload Task success                 | Upload a valid task.                                                                                               | Task is inserted and success notification appears.                                                  |               |        |
| F-034   | Accepted/Saved navigation           | Open`accepted_task.php` and `saved_tasks.php`.                                                                     | Accepted page shows`Post Task`/`Saved Task`; Saved page shows `Post Task`/`Accepted Task`.          |               |        |

## Regression Test Cases


| Test ID | Feature            | Steps                                                                          | Expected Result                                                           | Actual Result | Status |
| ------- | ------------------ | ------------------------------------------------------------------------------ | ------------------------------------------------------------------------- | ------------- | ------ |
| R-001   | Login              | Log in with a valid existing user account.                                     | User is authenticated and redirected to the expected page.                |               |        |
| R-002   | Login              | Attempt login with invalid credentials.                                        | Login is rejected without exposing sensitive information.                 |               |        |
| R-003   | Registration       | Register a new user with valid details.                                        | New account is created and can log in.                                    |               |        |
| R-004   | Registration       | Try registration with missing required fields.                                 | Form validation prevents incomplete registration.                         |               |        |
| R-005   | Artwork upload     | Log in and upload a valid artwork image with title, description, and category. | Artwork is saved and appears in gallery/profile views.                    |               |        |
| R-006   | Artwork upload     | Attempt artwork upload with an invalid file type.                              | Upload is rejected safely.                                                |               |        |
| R-007   | Gallery display    | Open`explore.php` with no filters.                                             | Artwork gallery loads existing artworks correctly.                        |               |        |
| R-008   | Gallery display    | Use gallery search and existing category links.                                | Gallery filters still display matching artwork records.                   |               |        |
| R-009   | Task creation      | Create a new task with title, description, image, and category.                | Task is created successfully and appears on the task board.               |               |        |
| R-010   | Task creation      | Attempt task creation with missing required fields.                            | Form validation prevents incomplete task creation.                        |               |        |
| R-011   | Task board display | Open`task.php`.                                                                | Task board loads available tasks and existing navigation actions.         |               |        |
| R-012   | Task board display | Open a task detail page from a task card.                                      | Task detail page loads the selected task.                                 |               |        |
| R-013   | Comment display    | Open an artwork detail page with existing comments.                            | Existing comments are displayed in the comments section.                  |               |        |
| R-014   | Comment display    | Open artwork detail after AJAX polling has run.                                | Existing stored comments remain preserved and visible.                    |               |        |
| R-015   | User profile       | Open`user_profile.php` while logged in.                                        | Profile information, profile actions, and artwork list display correctly. |               |        |
| R-016   | User profile       | Navigate from an artwork/comment/task user link to a profile.                  | User profile page opens without broken links.                             |               |        |

## Browser Testing Checklist


| Test ID | Feature               | Steps                                                                                       | Expected Result                                       | Actual Result | Status |
| ------- | --------------------- | ------------------------------------------------------------------------------------------- | ----------------------------------------------------- | ------------- | ------ |
| B-001   | Chrome                | Run the functional test cases in Google Chrome.                                             | All approved features work and layout remains stable. |               |        |
| B-002   | Firefox               | Run the functional test cases in Mozilla Firefox.                                           | All approved features work and layout remains stable. |               |        |
| B-003   | Edge                  | Run the functional test cases in Microsoft Edge.                                            | All approved features work and layout remains stable. |               |        |
| B-004   | Browser compatibility | Compare button states, AJAX updates, and polling behavior across Chrome, Firefox, and Edge. | Behavior is consistent across supported browsers.     |               |        |

## Responsive Testing Checklist


| Test ID | Feature                     | Steps                                                                              | Expected Result                                                          | Actual Result | Status |
| ------- | --------------------------- | ---------------------------------------------------------------------------------- | ------------------------------------------------------------------------ | ------------- | ------ |
| V-001   | 1366x768 responsive layout  | Test homepage, explore page, task board, artwork detail, and profile at 1366x768.  | Layout remains readable with no major overlap or horizontal overflow.    |               | PASS   |
| V-002   | 1440x900 responsive layout  | Test homepage, explore page, task board, artwork detail, and profile at 1440x900.  | Layout remains readable with stable spacing and card sizing.             |               | PASS   |
| V-003   | 1600x900 responsive layout  | Test homepage, explore page, task board, artwork detail, and profile at 1600x900.  | Containers and cards remain visually controlled.                         |               | PASS   |
| V-004   | 1920x1080 responsive layout | Test homepage, explore page, task board, artwork detail, and profile at 1920x1080. | Main containers do not stretch excessively and content remains readable. |               | PASS   |

## Database Validation Checklist


| Test ID | Feature                                  | Steps                                                                          | Expected Result                                                                                                               | Actual Result | Status |
| ------- | ---------------------------------------- | ------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------- | ------------- | ------ |
| D-001   | `saved_tasks` table                      | Run`SHOW TABLES LIKE 'saved_tasks';`.                                          | `saved_tasks` table exists.                                                                                                   |               | PASS   |
| D-002   | `saved_tasks` table                      | Run`DESCRIBE saved_tasks;`.                                                    | Table includes`saved_task_id`, `user_id`, `task_id`, timestamps, and unique user/task constraint.                             |               | PASS   |
| D-003   | `task_categories` table                  | Run`SHOW TABLES LIKE 'task_categories';`.                                      | `task_categories` table exists.                                                                                               |               | PASS   |
| D-004   | `task_categories` table                  | PASSRun`SELECT * FROM task_categories ORDER BY task_category_id;`.             | Seed categories include Illustration, Graphic Design, Animation, Digital Painting, UI/UX Design, Photography, and Other.      |               | PASS   |
| D-005   | `artwork_likes` table                    | Run`SHOW TABLES LIKE 'artwork_likes';`.                                        | `artwork_likes` table exists.                                                                                                 |               | PASS   |
| D-006   | `artwork_likes` table                    | Run`DESCRIBE artwork_likes;`.                                                  | Table includes`artwork_like_id`, `user_id`, `artwork_id`, timestamp, and unique user/artwork constraint.                      |               | PASS   |
| D-007   | `task.category_id` / `tasks.category_id` | Run`DESCRIBE task;` in this project schema.                                    | Existing singular`task` table includes `category_id`. If a local schema uses `tasks`, confirm equivalent `tasks.category_id`. |               | PASS   |
| D-008   | `task.task_category_id`                  | Run`DESCRIBE task;`.                                                           | `task_category_id` exists if the task categorization migration has been applied.                                              |               | PASS   |
| D-009   | Foreign keys                             | Run an information schema check for`saved_tasks`, `artwork_likes`, and `task`. | Foreign keys exist where referenced base tables are present.                                                                  |               | PASS   |
| D-010   | `pending_user_otps` table                | Run`SHOW TABLES LIKE 'pending_user_otps';`.                                    | Pending OTP table exists.                                                                                                     |               | PASS   |
| D-011   | `support_tickets` table                  | Run`SHOW TABLES LIKE 'support_tickets';`.                                      | Support tickets table exists.                                                                                                 |               | PASS   |

## Notes

- Do not mark a test as passed until it has been manually executed.
- Use the database selected in phpMyAdmin. The project defaults to `web_assignment` and can fall back to `software_evo_assignment`.
- The project schema uses the singular table name `task`; `tasks.category_id` in the proposal maps to `task.category_id` in this repository.

## Final Manual Regression Testing Table


| Test Case ID | Feature                 | Test Steps                                                             | Expected Result                                               | Actual Result                                                                                                                                   | Status | Evidence Screenshot Name                       |
| ------------ | ----------------------- | ---------------------------------------------------------------------- | ------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- | ------ | ---------------------------------------------- |
| TC01         | Password validation     | Register with a password less than 8 characters.                       | Registration is rejected with the password rule message.      | Weak password under 8 characters was rejected.                                                                                                  | PASS   | TC01_weak_password_less_than_8_rejected.png    |
| TC02         | Password validation     | Register with a letters-only password.                                 | Registration is rejected with the password rule message.      | Letters-only password was rejected.                                                                                                             | PASS   | TC02_letters_only_password_rejected.png        |
| TC03         | Password validation     | Register with a numbers-only password.                                 | Registration is rejected with the password rule message.      | Numbers-only password was rejected.                                                                                                             | PASS   | TC03_numbers_only_password_rejected.png        |
| TC04         | Password validation     | Register with a valid password containing letters and numbers.         | Registration proceeds to OTP verification.                    | Valid registration submits successfully and now shows a clear OTP email message before verification.                                            | FIXED  | TC04_valid_password_registration_submitted.png |
| TC05         | OTP verification        | Complete valid registration details.                                   | OTP verification page appears after valid registration.       | OTP verification page appeared.                                                                                                                 | PASS   | TC05_otp_page_appears.png                      |
| TC06         | OTP verification        | Enter an incorrect OTP on the OTP verification page.                   | Wrong OTP is rejected and the final account is not created.   | Wrong OTP was rejected.                                                                                                                         | PASS   | TC06_wrong_otp_rejected.png                    |
| TC07         | OTP verification        | Enter the correct OTP on the OTP verification page.                    | Final user account is created successfully.                   | Correct OTP created the final user account.                                                                                                     | PASS   | TC07_correct_otp_user_created.png              |
| TC08         | Login                   | Log in using the exact-case registered username.                       | Login succeeds.                                               | Exact-case username login worked, showed`Login successful using username.` on the login page, then redirected to the main page after 2 seconds. | PASS   | TC08_exact_case_username_login_success.png     |
| TC09         | Login                   | Log in using the wrong-case version of the registered username.        | Login fails.                                                  | Wrong-case username login failed.                                                                                                               | PASS   | TC09_wrong_case_username_login_failed.png      |
| TC10         | Login                   | Log in using the registered email address.                             | Login succeeds.                                               | Email login worked, showed`Login successful using email.` on the login page, then redirected to the main page after 2 seconds.                  | PASS   | TC10_email_login_success.png                   |
| TC11         | Login wording           | Open the login page.                                                   | Login page displays`Forget Password`.                         | Login page displayed`Forget Password`.                                                                                                          | PASS   | TC11_login_forget_password_wording.png         |
| TC12         | Forgot Password         | Submit an email on the Forgot Password page.                           | Message`Sent to email` is shown.                              | Forgot Password submit showed`Sent to email`.                                                                                                   | PASS   | TC12_forgot_password_sent_to_email.png         |
| TC13         | Forgot Password         | Click the Back button on the Forgot Password page.                     | User returns to the login page.                               | Back button returned to the login page.                                                                                                         | PASS   | TC13_forgot_password_back_to_login.png         |
| TC14         | Support ticket          | Submit a support ticket with valid email, phone, subject, and message. | Ticket is submitted successfully and a tracking code appears. | Support ticket submitted successfully.                                                                                                          | PASS   | TC14_support_ticket_submitted.png              |
| TC15         | Support ticket tracking | Track a support ticket using the correct ticket code and email.        | Ticket details are shown.                                     | Correct ticket code and email displayed ticket details.                                                                                         | PASS   | TC15_support_ticket_tracking_success.png       |
| TC16         | Upload Task             | Select a task image using the file picker.                             | Image preview appears.                                        | File picker image preview worked.                                                                                                               | PASS   | TC16_upload_task_file_picker_preview.png       |
| TC17         | Upload Task             | Drag and drop a task image into the upload area.                       | Image preview appears.                                        | Drag-and-drop preview worked using the same preview component as TC16.                                                                          | PASS   | TC17_upload_task_drag_drop_preview.png         |
| TC18         | Upload Task             | Submit a valid task with title, description, category, and image.      | Task data is inserted into the database.                      | Upload Task inserted into the database.                                                                                                         | PASS   | TC18_upload_task_database_insert.png           |
| TC19         | Upload Task             | Complete a successful task upload.                                     | Success notification appears.                                 | Upload Task success notification appeared.                                                                                                      | PASS   | TC19_upload_task_success_notification.png      |
| TC20         | Accepted Task page      | Open the Accepted Task page.                                           | Page shows only`Post Task` and `Saved Task` buttons.          | Accepted Task page buttons were correct.                                                                                                        | PASS   | TC20_accepted_task_buttons.png                 |
| TC21         | Saved Task page         | Open the Saved Task page.                                              | Page shows only`Post Task` and `Accepted Task` buttons.       | Saved Task page buttons were correct.                                                                                                           | PASS   | TC21_saved_task_buttons.png                    |
| TC22         | Saved Task page         | Check the Saved Task page buttons.                                     | `Task Board` button is not shown.                             | `Task Board` button was not shown, and the footer white bar/layout issue was fixed.                                                             | FIXED  | TC22_saved_task_no_task_board_button.png       |
| TC23         | Footer link             | Click the Support link in the footer.                                  | Support page opens.                                           | Footer Support link opened the support page.                                                                                                    | PASS   | TC23_footer_support_link.png                   |
| TC24         | Header/footer layout    | View the header and footer at 1920x1080 resolution.                    | Header and footer appear smaller.                             | Header and footer appeared smaller at 1920x1080.                                                                                                | PASS   | TC24_header_footer_1920x1080.png               |
| TC25         | Responsive layout       | View the header and footer at mobile width.                            | Header and footer remain usable.                              | Header and footer remained usable on mobile width.                                                                                              | PASS   | TC25_header_footer_mobile_width.png            |

## Suggested Evidence Screenshot Filenames


| Test Case ID | Suggested Screenshot Filename                    |
| ------------ | ------------------------------------------------ |
| TC01         | `TC01_weak_password_less_than_8_rejected.png`    |
| TC02         | `TC02_letters_only_password_rejected.png`        |
| TC03         | `TC03_numbers_only_password_rejected.png`        |
| TC04         | `TC04_valid_password_registration_submitted.png` |
| TC05         | `TC05_otp_page_appears.png`                      |
| TC06         | `TC06_wrong_otp_rejected.png`                    |
| TC07         | `TC07_correct_otp_user_created.png`              |
| TC08         | `TC08_exact_case_username_login_success.png`     |
| TC09         | `TC09_wrong_case_username_login_failed.png`      |
| TC10         | `TC10_email_login_success.png`                   |
| TC11         | `TC11_login_forget_password_wording.png`         |
| TC12         | `TC12_forgot_password_sent_to_email.png`         |
| TC13         | `TC13_forgot_password_back_to_login.png`         |
| TC14         | `TC14_support_ticket_submitted.png`              |
| TC15         | `TC15_support_ticket_tracking_success.png`       |
| TC16         | `TC16_upload_task_file_picker_preview.png`       |
| TC17         | `TC17_upload_task_drag_drop_preview.png`         |
| TC18         | `TC18_upload_task_database_insert.png`           |
| TC19         | `TC19_upload_task_success_notification.png`      |
| TC20         | `TC20_accepted_task_buttons.png`                 |
| TC21         | `TC21_saved_task_buttons.png`                    |
| TC22         | `TC22_saved_task_no_task_board_button.png`       |
| TC23         | `TC23_footer_support_link.png`                   |
| TC24         | `TC24_header_footer_1920x1080.png`               |
| TC25         | `TC25_header_footer_mobile_width.png`            |
