<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$uid = intval($_SESSION['UID']);
$isAdmin = !empty($_SESSION['ADMIN']);

// Resolve the task id from GET (form view) or POST (save).
$task_id = intval($_POST['task_id'] ?? $_GET['id'] ?? 0);
if ($task_id <= 0) {
    header("Location: my_task.php");
    exit();
}

// Load the task and confirm ownership (or admin).
$stmt = $conn->prepare("SELECT * FROM task WHERE task_id = ? LIMIT 1");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();

if (!$task) {
    $_SESSION['feedback'] = "Task not found.";
    header("Location: my_task.php");
    exit();
}

if (intval($task['post_user_id']) !== $uid && !$isAdmin) {
    $_SESSION['feedback'] = "You are not allowed to edit this task.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

if (($task['task_state'] ?? 'open') !== 'open') {
    $_SESSION['feedback'] = "Only open tasks can be edited.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

// Category options from the shared `category` table.
$categories = [];
$catStmt = $conn->prepare("SELECT category_id, category_name FROM category ORDER BY category_id ASC");
$catStmt->execute();
$catResult = $catStmt->get_result();
while ($catRow = $catResult->fetch_assoc()) {
    $categories[] = $catRow;
}

// --- Handle save ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['task_title'] ?? "");
    $description = trim($_POST['task_description'] ?? "");
    $category_id = intval($_POST['category_id'] ?? 0);

    if ($title === "" || $description === "" || $category_id <= 0) {
        $_SESSION['feedback'] = "All fields are required.";
        header("Location: edit_task.php?id=" . $task_id);
        exit();
    }

    // Validate category.
    $check = $conn->prepare("SELECT category_id FROM category WHERE category_id = ? LIMIT 1");
    $check->bind_param("i", $category_id);
    $check->execute();
    if (!$check->get_result()->fetch_assoc()) {
        $_SESSION['feedback'] = "Invalid category.";
        header("Location: edit_task.php?id=" . $task_id);
        exit();
    }

    // Optional image replacement.
    $newImage = $task['task_image'];
    if (isset($_FILES['task_image']) && $_FILES['task_image']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['task_image']['size'] > 25000000) {
            $_SESSION['feedback'] = "File size exceeds the limit of 25MB.";
            header("Location: edit_task.php?id=" . $task_id);
            exit();
        }
        $ext = strtolower(pathinfo($_FILES['task_image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            $_SESSION['feedback'] = "Only JPG, JPEG & PNG files are allowed.";
            header("Location: edit_task.php?id=" . $task_id);
            exit();
        }
        $uploadDir = "assets/uploads/task/";
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            $_SESSION['feedback'] = "Task upload folder is not available.";
            header("Location: edit_task.php?id=" . $task_id);
            exit();
        }
        $safeBase = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($_FILES['task_image']['name'], PATHINFO_FILENAME));
        $newImage = time() . '_' . $safeBase . '.' . $ext;
        if (!move_uploaded_file($_FILES['task_image']['tmp_name'], $uploadDir . $newImage)) {
            $_SESSION['feedback'] = "Failed to upload image file.";
            header("Location: edit_task.php?id=" . $task_id);
            exit();
        }
        // Remove the previous image file if it was replaced.
        if (!empty($task['task_image']) && $task['task_image'] !== $newImage) {
            $oldPath = $uploadDir . $task['task_image'];
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }
    }

    $update = $conn->prepare("UPDATE task SET task_title = ?, task_description = ?, category_id = ?, task_image = ? WHERE task_id = ?");
    $update->bind_param("ssisi", $title, $description, $category_id, $newImage, $task_id);
    $update->execute();

    $_SESSION['feedback'] = "Task updated successfully.";
    header("Location: task_detail.php?id=" . $task_id);
    exit();
}

include("navbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container-fluid" style="padding-left: 60px; padding-right: 60px;">
    <h1 class="inter-bold-44 mb-4" style="margin-top:60px;">Edit Task</h1>
    <?php if (isset($_SESSION['feedback'])): ?>
        <div class="alert alert-info inter-extralight-24" role="status">
            <?php echo htmlspecialchars($_SESSION['feedback']); ?>
        </div>
        <?php unset($_SESSION['feedback']); ?>
    <?php endif; ?>
    <form action="edit_task.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="task_id" value="<?php echo intval($task_id); ?>">
        <div class="mb-4">
            <label for="task_title" class="inter-bold-32 mb-3">Title</label>
            <input type="text" class="form-control inter-medium-25 left-placeholder border_black" id="task_title" name="task_title"
                value="<?php echo htmlspecialchars($task['task_title']); ?>" required>
        </div>
        <div class="mb-4">
            <label for="task_description" class="inter-bold-32 mb-3">Description</label>
            <textarea class="form-control inter-medium-25 left-placeholder border_black" id="task_description" name="task_description" rows="3" required><?php echo htmlspecialchars($task['task_description']); ?></textarea>
        </div>
        <div class="mb-4">
            <label for="category_id" class="inter-bold-32 mb-3">Category</label>
            <select class="form-control inter-medium-25 border_black" id="category_id" name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>"
                        <?php echo intval($category['category_id']) === intval($task['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label class="inter-bold-32 mb-3">Current Image</label>
            <div class="mb-3">
                <?php if (!empty($task['task_image'])): ?>
                    <img src="assets/uploads/task/<?php echo htmlspecialchars($task['task_image']); ?>" alt="Task image"
                        style="max-width:320px; max-height:240px; object-fit:contain; border-radius:12px;">
                <?php else: ?>
                    <p class="inter-extralight-15">No image.</p>
                <?php endif; ?>
            </div>
            <label for="task_image" class="inter-bold-24 mb-2">Replace Image (optional)</label>
            <input type="file" class="form-control border_black" id="task_image" name="task_image" accept="image/*">
        </div>
        <div class="d-flex gap-2" style="padding-bottom: 60px;">
            <button type="submit" class="btn btn-outline-black inter-medium-24 active">Save Changes</button>
            <a href="task_detail.php?id=<?php echo intval($task_id); ?>" class="btn btn-outline-black inter-medium-25 border_black">Cancel</a>
        </div>
    </form>
</div>
</body>
<footer>
    <?php include("footer.php"); ?>
</footer>
</html>
