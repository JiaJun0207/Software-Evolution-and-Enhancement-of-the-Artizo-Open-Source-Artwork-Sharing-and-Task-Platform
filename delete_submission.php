<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$uid = intval($_SESSION['UID']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submission_id'])) {
    header("Location: my_task.php");
    exit();
}

$submission_id = intval($_POST['submission_id']);

// Load the submission and confirm the current user owns it.
$stmt = $conn->prepare("SELECT submission_id, task_id, submitter_user_id, file_path FROM task_submissions WHERE submission_id = ? LIMIT 1");
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$submission = $stmt->get_result()->fetch_assoc();

if (!$submission) {
    $_SESSION['feedback'] = "Submission not found.";
    header("Location: my_task.php");
    exit();
}

if (intval($submission['submitter_user_id']) !== $uid) {
    $_SESSION['feedback'] = "You can only delete your own submission.";
    header("Location: my_task.php");
    exit();
}

$task_id = intval($submission['task_id']);

// Remove the uploaded file from disk.
$filePath = "assets/uploads/task_solution/" . $submission['file_path'];
if (is_file($filePath)) {
    @unlink($filePath);
}

// Delete the submission row (the original task is NOT touched).
$del = $conn->prepare("DELETE FROM task_submissions WHERE submission_id = ?");
$del->bind_param("i", $submission_id);
$del->execute();

// Revert the user's acceptance back to 'accepted' so they can submit again.
$revert = $conn->prepare("UPDATE task_acceptances SET status = 'accepted' WHERE task_id = ? AND user_id = ?");
$revert->bind_param("ii", $task_id, $uid);
$revert->execute();

$_SESSION['feedback'] = "Your submission was deleted. You can submit again for this task.";
header("Location: task_detail.php?id=" . $task_id);
exit();
?>
