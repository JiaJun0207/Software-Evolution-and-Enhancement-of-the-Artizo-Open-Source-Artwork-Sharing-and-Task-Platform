# User Guide

This guide explains how regular Artizo users interact with the new features added for the CSE6364 Software Evolution and Maintenance assignment.

## Save Task

The Save Task feature lets a logged-in user keep a personal list of tasks they may want to revisit later.

How to save a task:

1. Log in to Artizo.
2. Open the task board from `task.php`.
3. Find a task card.
4. Click the `Save` button.
5. The button changes to `Saved` without reloading the page.

How to view saved tasks:

1. Open `saved_tasks.php`, or use the `Saved Tasks` link from the task board/profile area.
2. Review the saved task list.
3. Open a task detail page from the saved task card if needed.

How to unsave a task:

1. Click the `Saved` button again.
2. The task is removed from the saved list or changes back to the unsaved state.

## Account Verification

New accounts require OTP verification.

How to create an account:

1. Open `signup.php`.
2. Enter a username, email, and a password with at least 8 characters including letters and numbers.
3. Submit the form.
4. Enter the OTP sent to your email.
5. After verification, log in from `login.php`.

Users can log in with either their email address or exact-case username.

## Support Tickets

The Support page lets logged-in users submit tickets. Anyone with the ticket code and matching email can track a ticket.

How to submit a ticket:

1. Open `support.php`.
2. Enter email, phone number, subject, and message.
3. Submit the form.
4. Save the displayed ticket code for tracking.

How to track a ticket:

1. Open `support.php`.
2. Enter the ticket code and the email used for the ticket.
3. Review the ticket status and details.

## Task Filtering

Task filtering helps users find task posts by category.

How to create a categorized task:

1. Log in.
2. Open `upload_task.php`.
3. Enter the task title and description.
4. Select a category from the category dropdown.
5. Upload the task image.
6. Submit the form.
7. Confirm the image preview and success notification appear.

How to filter tasks:

1. Open `task.php`.
2. Use the category filter buttons near the top of the task board.
3. The task list updates without a full page reload.
4. Use the search field together with category filters to narrow the task list further.

## Artwork Like

The Artwork Like feature lets users show interest in artwork posts.

How to like artwork:

1. Log in.
2. Open `explore.php` or a user profile page.
3. Click the `Like` button on an artwork card.
4. The button changes to `Unlike`, and the count updates immediately.

How to unlike artwork:

1. Click `Unlike` on a previously liked artwork.
2. The button returns to `Like`, and the count decreases.

The liked state also appears on `artwork_detail.php`, so users can see whether they already liked the artwork when viewing the full detail page.

## AJAX Comments

Artwork comments now update without a full page reload.

How to submit a comment:

1. Log in.
2. Open an artwork detail page.
3. Type a comment in the comment field.
4. Press Enter or click `Submit Comment`.
5. The comment appears immediately on the page.

Near-real-time refresh:

- The artwork detail page checks for newer comments every 5 seconds.
- If another user comments on the same artwork, the new comment appears automatically.
- Existing comments are preserved, and duplicate comments are not added to the page.
