-- ============================================================================
--  Artizo - DEMO / SCREENSHOT EVIDENCE SEED  (TC70-TC88 multi-user task flow)
-- ============================================================================
--  PURPOSE
--    Recreates the multi-user task workflow screenshot/demo evidence used for
--    test cases TC70-TC88 (see TESTING.md). It seeds the demo accounts, one
--    open task posted by userA, and two independent submissions (userB and
--    userC) to the SAME task, proving the task stays open after submissions.
--
--  *** DEMO ONLY - run on a CLEAN / LOCAL demo database ***
--    This file pins exact IDs (admin=1, userA=2, userB=3, userC=4, task_id=2,
--    submission_id=3 and 4) so screenshots stay consistent. Run it ONLY on a
--    freshly imported local database (see usage below). It deletes and
--    recreates the demo rows it owns; it does not touch unrelated data.
--
--  USAGE (after the schema is in place):
--    mysql -u root software_evo_assignment < assets/database/full_database_setup.sql
--    mysql -u root software_evo_assignment < assets/database/demo_evidence_seed.sql
--
--  DEMO LOGINS (passwords are stored ONLY as bcrypt hashes below, never plaintext):
--    admin / Admin@123
--    userA / Test@1234
--    userB / Test@1234
--    userC / Test@1234
--
--  IMAGES
--    The demo images are committed under assets/demo_uploads/ so they survive a
--    clone. The application prepends a fixed folder to stored file names
--    (task -> assets/uploads/task/, submissions -> assets/uploads/task_solution/),
--    so the stored paths below use a relative prefix (../../demo_uploads/...) that
--    resolves back to assets/demo_uploads/ without changing any PHP code.
--
--  IDEMPOTENT
--    Safe to run repeatedly: the demo task is removed first (its acceptances and
--    submissions cascade away via FK ON DELETE CASCADE), demo users are upserted
--    by their fixed IDs, then the task, acceptances, and submissions are
--    re-inserted with the pinned IDs.
-- ============================================================================

START TRANSACTION;

-- 1) Remove any previous copy of the demo task. FK ON DELETE CASCADE on
--    task_acceptances and task_submissions clears the related demo rows too.
DELETE FROM `task`
WHERE `task_id` = 2
   OR `task_title` = 'Logo design for Artizo coffee brand';

-- 2) Demo accounts at fixed IDs. Passwords are bcrypt password_hash() output:
--      admin -> Admin@123
--      userA/userB/userC -> Test@1234
--    ON DUPLICATE KEY UPDATE (on PRIMARY user_id) keeps this idempotent and also
--    repairs an existing seeded admin row to the demo values.
INSERT INTO `user` (`user_id`, `user_name`, `user_description`, `email`, `password`, `profile_image`, `is_admin`) VALUES
  (1, 'admin', 'System administrator', 'admin@artizo.local', '$2y$10$vBY5GhMN0MEJZj5.nj71H.tY3r3InXE9mBpJbgzRDO7k8p2C4CIZq', '', 1),
  (2, 'userA', '', 'userA@test.local', '$2y$10$Dy1NHLufxHuVRy9scgq5T..zUAIEDr9/wXmyfZrbNd75xfH4L8Vay', '', 0),
  (3, 'userB', '', 'userB@test.local', '$2y$10$Dy1NHLufxHuVRy9scgq5T..zUAIEDr9/wXmyfZrbNd75xfH4L8Vay', '', 0),
  (4, 'userC', '', 'userC@test.local', '$2y$10$Dy1NHLufxHuVRy9scgq5T..zUAIEDr9/wXmyfZrbNd75xfH4L8Vay', '', 0)
ON DUPLICATE KEY UPDATE
  `user_name`   = VALUES(`user_name`),
  `user_description` = VALUES(`user_description`),
  `email`       = VALUES(`email`),
  `password`    = VALUES(`password`),
  `profile_image` = VALUES(`profile_image`),
  `is_admin`    = VALUES(`is_admin`);

-- 3) One OPEN task posted by userA (task_id = 2). Category 1 = Graphic Design
--    from the shared `category` table. task_state = 'open' is the source of truth
--    for board visibility; legacy task_status/accepted_user_id are compat-only.
INSERT INTO `task`
  (`task_id`, `task_title`, `task_description`, `task_image`, `task_solution`,
   `task_status`, `task_state`, `post_user_id`, `accepted_user_id`, `category_id`, `release_at`)
VALUES
  (2,
   'Logo design for Artizo coffee brand',
   'Need a clean modern logo for a coffee brand. Vector preferred.',
   '../../demo_uploads/demo_task_logo.jpg',
   '',
   'accept',
   'open',
   2,
   NULL,
   1,
   '2026-06-14 18:50:38');

-- 4) Per-user acceptance state: userB (3) and userC (4) both accepted and then
--    submitted. Unique key (task_id, user_id) keeps this idempotent.
INSERT INTO `task_acceptances` (`task_id`, `user_id`, `status`) VALUES
  (2, 3, 'submitted'),
  (2, 4, 'submitted')
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

-- 5) Two submissions to the SAME open task, with pinned submission IDs 3 and 4.
INSERT INTO `task_submissions`
  (`submission_id`, `task_id`, `submitter_user_id`, `file_path`, `message`, `status`, `submitted_at`)
VALUES
  (3, 2, 3, '../../demo_uploads/demo_solution_userB.jpg',
   'Hi, here is my modern minimalist coffee logo concept. Happy to revise.', 'submitted', '2026-06-14 18:50:54'),
  (4, 2, 4, '../../demo_uploads/demo_solution_userC.jpg',
   'My bold vintage badge-style coffee logo take. Source files included.', 'submitted', '2026-06-14 18:50:54');

COMMIT;

-- ============================================================================
--  Saved / Accepted / My Task evidence:
--    * Accepted Task (userB/userC): driven by the task_acceptances rows above.
--    * My Task -> Tasks I Posted (userA): driven by task.post_user_id = 2.
--    * My Task -> My Submissions (userB/userC): driven by the task_submissions
--      rows above.
--    * Saved Task: optional and user-driven at runtime; no seed row is required
--      for the TC70-TC88 evidence, so none is inserted here.
-- ============================================================================
