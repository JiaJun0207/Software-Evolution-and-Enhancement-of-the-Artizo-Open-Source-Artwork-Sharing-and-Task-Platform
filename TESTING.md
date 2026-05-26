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
| F-009 | Task categorization and filtering | Open `upload_task.php`. | Task category dropdown is visible and populated from `task_categories`. |  |  |
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
| V-001 | 1366x768 responsive layout | Test homepage, explore page, task board, artwork detail, and profile at 1366x768. | Layout remains readable with no major overlap or horizontal overflow. |  |  |
| V-002 | 1440x900 responsive layout | Test homepage, explore page, task board, artwork detail, and profile at 1440x900. | Layout remains readable with stable spacing and card sizing. |  |  |
| V-003 | 1600x900 responsive layout | Test homepage, explore page, task board, artwork detail, and profile at 1600x900. | Containers and cards remain visually controlled. |  |  |
| V-004 | 1920x1080 responsive layout | Test homepage, explore page, task board, artwork detail, and profile at 1920x1080. | Main containers do not stretch excessively and content remains readable. |  |  |

## Database Validation Checklist

| Test ID | Feature | Steps | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| D-001 | `saved_tasks` table | Run `SHOW TABLES LIKE 'saved_tasks';`. | `saved_tasks` table exists. |  |  |
| D-002 | `saved_tasks` table | Run `DESCRIBE saved_tasks;`. | Table includes `saved_task_id`, `user_id`, `task_id`, timestamps, and unique user/task constraint. |  |  |
| D-003 | `task_categories` table | Run `SHOW TABLES LIKE 'task_categories';`. | `task_categories` table exists. |  |  |
| D-004 | `task_categories` table | Run `SELECT * FROM task_categories ORDER BY task_category_id;`. | Seed categories include Illustration, Graphic Design, Animation, Digital Painting, UI/UX Design, Photography, and Other. |  |  |
| D-005 | `artwork_likes` table | Run `SHOW TABLES LIKE 'artwork_likes';`. | `artwork_likes` table exists. |  |  |
| D-006 | `artwork_likes` table | Run `DESCRIBE artwork_likes;`. | Table includes `artwork_like_id`, `user_id`, `artwork_id`, timestamp, and unique user/artwork constraint. |  |  |
| D-007 | `task.category_id` / `tasks.category_id` | Run `DESCRIBE task;` in this project schema. | Existing singular `task` table includes `category_id`. If a local schema uses `tasks`, confirm equivalent `tasks.category_id`. |  |  |
| D-008 | `task.task_category_id` | Run `DESCRIBE task;`. | `task_category_id` exists if the task categorization migration has been applied. |  |  |
| D-009 | Foreign keys | Run an information schema check for `saved_tasks`, `artwork_likes`, and `task`. | Foreign keys exist where referenced base tables are present. |  |  |

## Notes

- Do not mark a test as passed until it has been manually executed.
- Use the database selected in phpMyAdmin. The project defaults to `web_assignment` and can fall back to `software_evo_assignment`.
- The project schema uses the singular table name `task`; `tasks.category_id` in the proposal maps to `task.category_id` in this repository.
