<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$uid = intval($_SESSION['UID']);
$submission_id = intval($_POST['submission_id'] ?? $_GET['id'] ?? 0);

if ($submission_id <= 0) {
    header("Location: my_task.php");
    exit();
}

// Load the submission joined to its task; confirm the current user owns it.
$stmt = $conn->prepare(
    "SELECT ts.submission_id, ts.task_id, ts.submitter_user_id, ts.file_path, ts.message,
            t.task_title
     FROM task_submissions ts
     JOIN task t ON ts.task_id = t.task_id
     WHERE ts.submission_id = ? LIMIT 1"
);
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$submission = $stmt->get_result()->fetch_assoc();

if (!$submission) {
    $_SESSION['feedback'] = "Submission not found.";
    header("Location: my_task.php");
    exit();
}

if (intval($submission['submitter_user_id']) !== $uid) {
    $_SESSION['feedback'] = "You can only edit your own submission.";
    header("Location: my_task.php");
    exit();
}

$task_id = intval($submission['task_id']);

// --- Handle save ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? "");
    $newFile = $submission['file_path'];

    // Optional file replacement.
    if (isset($_FILES['artwork_image']) && $_FILES['artwork_image']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['artwork_image']['size'] > 25000000) {
            $_SESSION['feedback'] = "File size exceeds the limit of 25MB.";
            header("Location: edit_submission.php?id=" . $submission_id);
            exit();
        }
        $ext = strtolower(pathinfo($_FILES['artwork_image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            $_SESSION['feedback'] = "Only JPG, JPEG & PNG files are allowed.";
            header("Location: edit_submission.php?id=" . $submission_id);
            exit();
        }
        $targetDir = 'assets/uploads/task_solution/';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
            $_SESSION['feedback'] = "Submission folder is not available.";
            header("Location: edit_submission.php?id=" . $submission_id);
            exit();
        }
        $newFile = 'solution_' . $task_id . '_' . $uid . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($_FILES['artwork_image']['tmp_name'], $targetDir . $newFile)) {
            $_SESSION['feedback'] = "Failed to upload file.";
            header("Location: edit_submission.php?id=" . $submission_id);
            exit();
        }
        // Remove the old file once the new one is in place.
        if ($submission['file_path'] !== $newFile) {
            $oldPath = $targetDir . $submission['file_path'];
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }
    }

    $update = $conn->prepare("UPDATE task_submissions SET message = ?, file_path = ? WHERE submission_id = ?");
    $update->bind_param("ssi", $message, $newFile, $submission_id);
    $update->execute();

    $_SESSION['feedback'] = "Submission updated successfully.";
    header("Location: submission_detail.php?id=" . $submission_id);
    exit();
}

$fileExt = strtolower(pathinfo($submission['file_path'], PATHINFO_EXTENSION));
$isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);

include("navbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Submission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container-fluid" style="padding-left: 60px; padding-right: 60px;">
    <h1 class="inter-bold-44 mb-2" style="margin-top:60px;">Edit Submission</h1>
    <p class="inter-extralight-24 mb-4">Task: <strong><?php echo htmlspecialchars($submission['task_title']); ?></strong></p>
    <?php if (isset($_SESSION['feedback'])): ?>
        <div class="alert alert-info inter-extralight-24" role="status">
            <?php echo htmlspecialchars($_SESSION['feedback']); ?>
        </div>
        <?php unset($_SESSION['feedback']); ?>
    <?php endif; ?>
    <form action="edit_submission.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="submission_id" value="<?php echo intval($submission_id); ?>">
        <div class="mb-4">
            <label class="inter-bold-32 mb-3">Current File</label>
            <div class="mb-3">
                <?php if ($isImage): ?>
                    <img src="assets/uploads/task_solution/<?php echo htmlspecialchars($submission['file_path']); ?>" alt="Submission"
                        style="max-width:320px; max-height:240px; object-fit:contain; border-radius:12px;">
                <?php else: ?>
                    <a href="assets/uploads/task_solution/<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank"
                        class="btn btn-outline-black inter-medium-25 border_black">Open current file</a>
                <?php endif; ?>
            </div>
            <label for="artwork_image" class="inter-bold-24 mb-2">Replace File (optional)</label>
            <input type="file" class="form-control border_black" id="artwork_image" name="artwork_image" accept="image/*">
        </div>
        <div class="mb-4">
            <label for="message" class="inter-bold-32 mb-3">Message (optional)</label>
            <textarea id="message" name="message" rows="3" class="form-control inter-medium-25 left-placeholder border_black"
                placeholder="Add a note for the task poster"><?php echo htmlspecialchars($submission['message'] ?? ''); ?></textarea>
        </div>
        <div class="d-flex gap-2" style="padding-bottom: 60px;">
            <button type="submit" class="btn btn-outline-black inter-medium-24 active">Save Changes</button>
            <a href="submission_detail.php?id=<?php echo intval($submission_id); ?>" class="btn btn-outline-black inter-medium-25 border_black">Cancel</a>
        </div>
    </form>
</div>
</body>
<footer>
    <?php include("footer.php"); ?>
</footer>
</html>
