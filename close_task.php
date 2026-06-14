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

// Confirm ownership (or admin) before closing.
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
    $_SESSION['feedback'] = "You are not allowed to close this task.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

// Soft removal: hide from the open board, keep all submissions/files intact.
$update = $conn->prepare("UPDATE task SET task_state = 'closed' WHERE task_id = ?");
$update->bind_param("i", $task_id);
$update->execute();

$_SESSION['feedback'] = "Task closed. It no longer appears on the open task board.";
header("Location: task_detail.php?id=" . $task_id);
exit();
?>
