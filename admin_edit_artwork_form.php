<?php
include("config.php");
session_start();
include("admin_auth.php"); // Restrict to authenticated admin sessions

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artwork_id'])) {
    $artwork_id = intval($_POST['artwork_id']);

    // Update artwork title if provided
    if (isset($_POST['artwork_title'])) {
        $title = $_POST['artwork_title'];
        $sql = "UPDATE artwork SET artwork_title = ? WHERE artwork_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $title, $artwork_id);
        $stmt->execute();
    }

    // Update artwork description if provided
    if (isset($_POST['artwork_description'])) {
        $desc = $_POST['artwork_description'];
        $sql = "UPDATE artwork SET artwork_description = ? WHERE artwork_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $desc, $artwork_id);
        $stmt->execute();
    }

    // Update artwork image if uploaded
    if (isset($_FILES['artwork_image']) && $_FILES['artwork_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['artwork_image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'artwork_' . $artwork_id . '_' . time() . '.' . $ext;
        $targetDir = 'assets/uploads/artworks/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetPath = $targetDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $sql = "UPDATE artwork SET artwork_image = ? WHERE artwork_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $filename, $artwork_id);
            $stmt->execute();
        }
    }

    // Change category if requested
    if (isset($_POST['artwork_category'])) {
        $cat = intval($_POST['artwork_category']);
        $sql = "UPDATE artwork SET category_id = ? WHERE artwork_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cat, $artwork_id);
        $stmt->execute();
    }

    header("Location: admin_edit_artwork.php?id=" . $artwork_id);
    exit();
} else {
    echo "Invalid request.";
}
?>
