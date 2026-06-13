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

// Only the user who accepted this task may submit work for it.
$taskStmt = $conn->prepare("SELECT accepted_user_id, task_status FROM task WHERE task_id = ? LIMIT 1");
$taskStmt->bind_param("i", $task_id);
$taskStmt->execute();
$task = $taskStmt->get_result()->fetch_assoc();

if (!$task) {
    $_SESSION['feedback'] = "Task not found.";
    header("Location: task.php");
    exit();
}

if (intval($task['accepted_user_id']) !== $uid) {
    $_SESSION['feedback'] = "You can only submit work for a task you accepted.";
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

// Save a submission record so the task poster (and admin) can review it.
$insertStmt = $conn->prepare(
    "INSERT INTO task_submissions (task_id, submitter_user_id, file_path, message, status)
     VALUES (?, ?, ?, ?, 'submitted')"
);
$insertStmt->bind_param("iiss", $task_id, $uid, $filename, $message);

if (!$insertStmt->execute()) {
    $_SESSION['feedback'] = "Unable to save submission.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

// Keep the legacy task_solution column and status in sync for compatibility.
$updateStmt = $conn->prepare("UPDATE task SET task_solution = ?, task_status = 'submitted' WHERE task_id = ?");
$updateStmt->bind_param("si", $filename, $task_id);
$updateStmt->execute();

$_SESSION['feedback'] = "Submission uploaded successfully.";
header("Location: task_detail.php?id=" . $task_id);
exit();
?>
