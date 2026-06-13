<?php
include("config.php");
session_start();
include("admin_auth.php"); // Restrict to authenticated admin sessions

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = intval($_POST['task_id']);

    // Update task title if provided
    if (isset($_POST['task_title'])) {
        $title = $_POST['task_title'];
        $sql = "UPDATE task SET task_title = ? WHERE task_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $title, $task_id);
        $stmt->execute();
    }

    // Update task description if provided
    if (isset($_POST['task_description'])) {
        $desc = $_POST['task_description'];
        $sql = "UPDATE task SET task_description = ? WHERE task_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $desc, $task_id);
        $stmt->execute();
    }

    // Update task image if uploaded
    if (isset($_FILES['task_image']) && $_FILES['task_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['task_image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'task_' . $task_id . '_' . time() . '.' . $ext;
        $targetDir = 'assets/uploads/task/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetPath = $targetDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $sql = "UPDATE task SET task_image = ? WHERE task_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $filename, $task_id);
            $stmt->execute();
        }
    }

    // Update task solution if provided
    if (isset($_POST['task_solution'])) {
        $solution = $_POST['task_solution'];
        $sql = "UPDATE task SET task_solution = ? WHERE task_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $solution, $task_id);
        $stmt->execute();
    }

    // Update task status if provided
    if (isset($_POST['task_status'])) {
        $status = $_POST['task_status'];
        $sql = "UPDATE task SET task_status = ? WHERE task_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $task_id);
        $stmt->execute();
    }

    // Change category if requested
    if (isset($_POST['category_id'])) {
        $cat = intval($_POST['category_id']);
        $sql = "UPDATE task SET category_id = ? WHERE task_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cat, $task_id);
        $stmt->execute();
    }

    header("Location: admin_edit_task.php?id=" . $task_id);
    exit();
} else {
    echo "Invalid request.";
}
?>
