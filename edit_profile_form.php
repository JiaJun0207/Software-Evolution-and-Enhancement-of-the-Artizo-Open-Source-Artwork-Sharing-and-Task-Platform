<?php
session_start(); // to start a session

if ($_SERVER["REQUEST_METHOD"] !== "POST") { //prevent users to direct access this backend page
  header("Location: edit_profile.php");
  exit();
}

include("config.php");

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['UID'];

// Update description if posted
if (isset($_POST['user_description'])) {
    $desc = $_POST['user_description'];
    $updateDesc = $conn->prepare("UPDATE user SET user_description = ? WHERE user_id = ?");
    $updateDesc->bind_param("si", $desc, $uid);
    $updateDesc->execute();
    header("Location: user_profile.php?desc=success");
    exit();
}

// Get current profile image filename
$getImg = $conn->prepare("SELECT profile_image FROM user WHERE user_id = ?");
$getImg->bind_param("i", $uid);
$getImg->execute();
$getImgResult = $getImg->get_result();
$currentImg = $getImgResult->fetch_assoc();
$oldImgFile = !empty($currentImg['profile_image']) ? $currentImg['profile_image'] : null;

// Check if file was uploaded
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_image']['tmp_name'];
    $fileName = basename($_FILES['profile_image']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExt, $allowedExt)) {
        $newFileName = 'user_' . $uid . '_' . time() . '.' . $fileExt;
        $destPath = 'assets/profile/' . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Delete old profile image if not default and file exists
            if ($oldImgFile && $oldImgFile !== 'user_profile.png') {
                $oldImgPath = 'assets/profile/' . $oldImgFile;
                if (file_exists($oldImgPath)) {
                    unlink($oldImgPath);
                }
            }
            // Update database
            $update = $conn->prepare("UPDATE user SET profile_image = ? WHERE user_id = ?");
            $update->bind_param("si", $newFileName, $uid);
            $update->execute();

            header("Location: edit_profile.php?success=1");
            exit();
        } else {
            header("Location: edit_profile.php?error=upload");
            exit();
        }
    } else {
        header("Location: edit_profile.php?error=type");
        exit();
    }
} else {
    header("Location: edit_profile.php?error=nofile");
    exit();
}
?>
