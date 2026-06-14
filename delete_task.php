<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$uid = intval($_SESSION['UID']);
$isAdmin = !empty($_SESSION['ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['task_id'])) {
    header("Location: task.php");
    exit();
}

$task_id = intval($_POST['task_id']);

// Confirm ownership (or admin) before deleting.
$stmt = $conn->prepare("SELECT post_user_id FROM task WHERE task_id = ? LIMIT 1");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();

if (!$task) {
    $_SESSION['feedback'] = "Task not found.";
    header("Location: task.php");
    exit();
}

if (intval($task['post_user_id']) !== $uid && !$isAdmin) {
    $_SESSION['feedback'] = "You are not allowed to delete this task.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

// Remove submission files from disk first to avoid orphaned files. The DB rows
// (task_submissions, task_acceptances) are removed by ON DELETE CASCADE.
$fileStmt = $conn->prepare("SELECT file_path FROM task_submissions WHERE task_id = ?");
$fileStmt->bind_param("i", $task_id);
$fileStmt->execute();
$fileResult = $fileStmt->get_result();
while ($fileRow = $fileResult->fetch_assoc()) {
    $path = "assets/uploads/task_solution/" . $fileRow['file_path'];
    if (is_file($path)) {
        @unlink($path);
    }
}

// Also remove the task image file if present.
$imgStmt = $conn->prepare("SELECT task_image FROM task WHERE task_id = ?");
$imgStmt->bind_param("i", $task_id);
$imgStmt->execute();
if ($imgRow = $imgStmt->get_result()->fetch_assoc()) {
    if (!empty($imgRow['task_image'])) {
        $imgPath = "assets/uploads/task/" . $imgRow['task_image'];
        if (is_file($imgPath)) {
            @unlink($imgPath);
        }
    }
}

$del = $conn->prepare("DELETE FROM task WHERE task_id = ?");
$del->bind_param("i", $task_id);
$del->execute();

$_SESSION['feedback'] = "Task deleted.";
header("Location: my_task.php");
exit();
?>
