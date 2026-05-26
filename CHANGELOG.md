# Changelog

This changelog summarizes the approved CSE6364 Software Evolution and Maintenance enhancements implemented on the `Part-ii-Implementation` branch.

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
