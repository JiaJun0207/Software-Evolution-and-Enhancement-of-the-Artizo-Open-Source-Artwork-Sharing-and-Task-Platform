<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$uid = intval($_SESSION['UID']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['task_id']) || !isset($_FILES['artwork_image'])) {
    $_SESSION['feedback'] = "Invalid submission request.";
    header("Location: task.php");
    exit();
}

$task_id = intval($_POST['task_id']);
$message = trim($_POST['message'] ?? "");

// The task must exist and still be open.
$taskStmt = $conn->prepare("SELECT post_user_id, task_state FROM task WHERE task_id = ? LIMIT 1");
$taskStmt->bind_param("i", $task_id);
$taskStmt->execute();
$task = $taskStmt->get_result()->fetch_assoc();

if (!$task) {
    $_SESSION['feedback'] = "Task not found.";
    header("Location: task.php");
    exit();
}

if ($task['task_state'] !== 'open') {
    $_SESSION['feedback'] = "This task is no longer open for submissions.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

// The user must currently hold an 'accepted' acceptance for this task.
$accStmt = $conn->prepare("SELECT status FROM task_acceptances WHERE task_id = ? AND user_id = ? LIMIT 1");
$accStmt->bind_param("ii", $task_id, $uid);
$accStmt->execute();
$acceptance = $accStmt->get_result()->fetch_assoc();

if (!$acceptance || $acceptance['status'] !== 'accepted') {
    $_SESSION['feedback'] = "You must accept this task before submitting.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

// Block a duplicate submission (one active submission per user per task).
$dupStmt = $conn->prepare("SELECT submission_id FROM task_submissions WHERE task_id = ? AND submitter_user_id = ? LIMIT 1");
$dupStmt->bind_param("ii", $task_id, $uid);
$dupStmt->execute();
if ($dupStmt->get_result()->fetch_assoc()) {
    $_SESSION['feedback'] = "You have already submitted to this task. Edit your existing submission instead.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

$file = $_FILES['artwork_image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['feedback'] = "File upload error. Please try again.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

if ($file['size'] > 25000000) {
    $_SESSION['feedback'] = "File size exceeds the limit of 25MB.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
    $_SESSION['feedback'] = "Only JPG, JPEG & PNG files are allowed.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

$targetDir = 'assets/uploads/task_solution/';
if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
    $_SESSION['feedback'] = "Submission folder is not available.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

$filename = 'solution_' . $task_id . '_' . $uid . '_' . time() . '.' . $ext;
$targetPath = $targetDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    $_SESSION['feedback'] = "Failed to upload file.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

// Save the submission record (per user).
$insertStmt = $conn->prepare(
    "INSERT INTO task_submissions (task_id, submitter_user_id, file_path, message, status)
     VALUES (?, ?, ?, ?, 'submitted')"
);
$insertStmt->bind_param("iiss", $task_id, $uid, $filename, $message);

if (!$insertStmt->execute()) {
    @unlink($targetPath);
    $_SESSION['feedback'] = "Unable to save submission.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

// Mark this user's acceptance as submitted (does NOT affect other users or the task board).
$updateAcc = $conn->prepare("UPDATE task_acceptances SET status = 'submitted' WHERE task_id = ? AND user_id = ?");
$updateAcc->bind_param("ii", $task_id, $uid);
$updateAcc->execute();

$_SESSION['feedback'] = "Submission uploaded successfully.";
header("Location: task_detail.php?id=" . $task_id);
exit();
?>
