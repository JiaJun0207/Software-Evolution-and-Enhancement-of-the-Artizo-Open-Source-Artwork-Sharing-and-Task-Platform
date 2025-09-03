<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type']) && isset($_POST['id'])) {
    $type = $_POST['type'];
    $id = intval($_POST['id']);
    if ($type === 'task') {
        $sql = "DELETE FROM task WHERE task_id = ?";
        $redirect = "admin_task.php";
    } elseif ($type === 'artwork') {
        $sql = "DELETE FROM artwork WHERE artwork_id = ?";
        $redirect = "admin_index.php";
    } elseif ($type === 'user') {
        $sql = "DELETE FROM user WHERE user_id = ?";
        $redirect = "admin_user_preview.php";
    } else {
        exit("Invalid type.");
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: $redirect");
    exit();
} else {
    echo "Invalid request.";
}
?>