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
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($type === 'artwork') {
        $sql = "DELETE FROM artwork WHERE artwork_id = ?";
        $redirect = "admin_index.php";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($type === 'user') {
        $sql = "DELETE FROM user WHERE user_id = ?";
        $redirect = "admin_user_preview.php";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($type === 'comment') {
        // Need artwork_id to redirect back to the edit page
        $sql = "SELECT artwork_id FROM comment WHERE comment_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($artwork_id);
        $stmt->fetch();
        $stmt->close();

        $sql = "DELETE FROM comment WHERE comment_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $redirect = "admin_edit_artwork.php?id=" . intval($artwork_id);
    } else {
        exit("Invalid type.");
    }
    header("Location: $redirect");
    exit();
} else {
    echo "Invalid request.";
}
?>