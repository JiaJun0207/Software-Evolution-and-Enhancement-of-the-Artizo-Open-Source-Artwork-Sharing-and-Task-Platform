<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id']) && isset($_FILES['artwork_image'])) {
    $task_id = intval($_POST['task_id']);
    $file = $_FILES['artwork_image'];

    // Check for upload errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'solution_' . $task_id . '_' . time() . '.' . $ext;
        $targetDir = 'assets/uploads/task_solution/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetPath = $targetDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Update task_solution column
            $sql = "UPDATE task SET task_solution = ? WHERE task_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $filename, $task_id);
            $stmt->execute();

            // Redirect back to task detail
            header("Location: task_detail.php?id=" . $task_id);
            exit();
        } else {
            echo "Failed to upload file.";
        }
    } else {
        echo "File upload error.";
    }
} else {
    echo "Invalid request.";
}
?>
