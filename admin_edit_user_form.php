<?php
include("config.php");
session_start();
include("admin_auth.php"); // Restrict to authenticated admin sessions

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Update profile image if uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
        $targetDir = 'assets/profile/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetPath = $targetDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $sql = "UPDATE user SET profile_image = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $filename, $user_id);
            $stmt->execute();
        }
    }

    // Update description if provided
    if (isset($_POST['user_description'])) {
        $desc = $_POST['user_description'];
        $sql = "UPDATE user SET user_description = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $desc, $user_id);
        $stmt->execute();
    }

    header("Location: admin_edit_user.php?id=" . $user_id);
    exit();
} else {
    echo "Invalid request.";
}
?>
