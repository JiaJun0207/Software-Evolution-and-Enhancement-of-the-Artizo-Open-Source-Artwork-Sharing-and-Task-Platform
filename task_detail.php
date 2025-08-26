<?php
include("config.php");

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Fetch user data
$uid = $_SESSION['UID'];
$query = "SELECT `profile_image`, `user_name`, `user_description` FROM `user` WHERE `user_id` = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Determine profile image path
$profileImg = "assets/profile/user_profile.png";
if (!empty($user['profile_image'])) {
    $profileImg = "assets/profile/" . $user['profile_image'];
}

include("navbar.php");

// --- Handle status change to accepted BEFORE fetching task info ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_task']) && isset($_POST['task_id'])) {
    $accept_task_id = intval($_POST['task_id']);
    // Fetch current task info
    $checkSql = "SELECT post_user_id, task_status FROM task WHERE task_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $accept_task_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkTask = $checkResult->fetch_assoc();

    // Only allow if not post_user_id and not already accepted/done/submitted
    $currentStatus = strtolower($checkTask['task_status']);
    if ($uid != $checkTask['post_user_id'] && $currentStatus !== 'accepted' && $currentStatus !== 'done' && $currentStatus !== 'submitted') {
        $updateSql = "UPDATE task SET task_status = 'accepted', accepted_user_id = ? WHERE task_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $uid, $accept_task_id);
        $updateStmt->execute();
        // Refresh page to show updated status
        header("Location: task_detail.php?id=" . $accept_task_id);
        exit();
    }
}

?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>task detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    if (isset($_GET['id'])) {
        $task_id = $_GET['id'];
    } else {
        $task_id = 0; // Default or error value
    }

    // Fetch task and poster info
    $sql = "SELECT t.*, u.user_name, u.profile_image, c.category_name 
            FROM task t
            JOIN user u ON t.post_user_id = u.user_id
            JOIN category c ON t.category_id = c.category_id
            WHERE t.task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();
        // --- Auto-update status to 'submitted' if solution exists and not already submitted ---
        if (!empty($task['task_solution']) && strtolower($task['task_status']) !== 'submitted') {
            $updateStatusSql = "UPDATE task SET task_status = 'submitted' WHERE task_id = ?";
            $updateStatusStmt = $conn->prepare($updateStatusSql);
            $updateStatusStmt->bind_param("i", $task_id);
            $updateStatusStmt->execute();
            // Refresh to show updated status
            header("Location: task_detail.php?id=" . $task_id);
            exit();
        }
        $posterImg = !empty($task['profile_image']) ? "assets/profile/" . $task['profile_image'] : "assets/profile/user_profile.png";
        $posterName = $task['user_name'];
        $categoryName = $task['category_name'];

        // Set category color based on category name
        $categoryColor = "#333"; // default color
        switch (strtolower($categoryName)) {
            case "photography":
                $categoryColor = "#B81149";
                break;
            case "graphic design":
                $categoryColor = "#FD399D";
                break;
            case "illustration":
                $categoryColor = "#033FDE";
                break;
            case "3d art":
                $categoryColor = "#F7822A";
                break;
            case "advertising":
                $categoryColor = "#8DE45B";
                break;
            // add more cases as needed
            default:
                $categoryColor = "#333";
        }

        // Handle comment submission (AJAX)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
            $commentText = trim($_POST['comment_text']);
            if ($commentText !== '') {
                $insertComment = $conn->prepare("INSERT INTO comment (task_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
                $insertComment->bind_param("iis", $task_id, $uid, $commentText);
                $insertComment->execute();
                echo "success";
                exit();
            }
        }
        ?>
        <div class="container-fluid"
            style="padding-left: 60px; padding-right: 60px; padding-bottom: 60px; margin-top:60px;">
            <div class="d-flex align-items-center">
                <div class="d-inline-block">
                    <img src="<?php echo htmlspecialchars($posterImg); ?>" alt="Profile Image" class="rounded-circle"
                        style="width:65px; height:65px; object-fit:cover;">
                </div>
                <p class="mb-0 inter-medium-24 ms-4">
                    <?php echo htmlspecialchars($posterName); ?>
                </p>
            </div>
            <div class="row mb-4">
                <div class="col-7" style="padding-right: 0px ;">
                    <div class="d-flex align-items-center justify-content-between" style="margin-top:60px;">
                        <h1 class="inter-bold-44 mb-0">
                            <?php echo htmlspecialchars($task['task_title']); ?>
                        </h1>
                        <?php
                        $statusLower = strtolower($task['task_status']);
                        $statusBg = ($statusLower === 'accepted' || $statusLower === 'done' || $statusLower === 'submitted') ? 'background:#000; color:#fff;' : '';
                        $isClickable = ($uid != $task['post_user_id'] && $statusLower !== 'accepted' && $statusLower !== 'done' && $statusLower !== 'submitted');
                        ?>
                        <?php if ($isClickable): ?>
                            <form id="accept-task-form" method="POST" style="display:inline;">
                                <input type="hidden" name="accept_task" value="1">
                                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>">
                                <button type="submit" class="card_border inter-medium-25 mb-0"
                                    style="padding: 8px 40px; <?php echo $statusBg; ?> border:none; background:transparent; cursor:pointer;">
                                    <?php echo htmlspecialchars($task['task_status']); ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="card_border inter-medium-25 mb-0" style="padding: 8px 40px; <?php echo $statusBg; ?>">
                                <?php echo htmlspecialchars($task['task_status']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <div class="row mb-5" style="padding-left: 10px;">
                <div class="col-7 d-flex justify-content-center align-items-center"
                    style="background-color:#f0f0f0; max-height:700px; min-height:300px;">
                    <img src="assets/uploads/task/<?php echo htmlspecialchars($task['task_image']); ?>" alt="task_image"
                        style="max-width:100%; max-height:700px; width:auto; height:auto; display:block;">
                </div>
                <div class="col-5" style="padding-left: 20px;">
                    <h5 class="inter-bold-24 mb-4">Category</h5>
                    <div class="category_box inter-medium-25 mb-4"
                        style="color: #fff; background-color: <?php echo $categoryColor; ?>;">
                        <?php echo htmlspecialchars($categoryName); ?>
                    </div>
                    <h5 class="inter-bold-24 mb-4">Description</h5>
                    <p class="inter-extralight-24"><?php echo htmlspecialchars($task['task_description']); ?></p>
                </div>
            </div>
            <?php if (strtolower($task['task_status']) === 'accepted'): ?>
            <form id="submission-form" action="submission_task.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>">
                <div class="mb-5">
                    <label for="artwork_image" class="inter-bold-32 mb-3">Submission</label>
                    <div id="drop-area"
                        class="border border-2 border-dark rounded-3 text-center mb-3 d-flex flex-column align-items-center justify-content-center"
                        style="cursor:pointer; background:#fafafa; padding:60px;">
                        <img src="assets/icons/upload.png" alt="Upload Icon"
                            style="width:48px; height:48px; margin-bottom:12px; display:block;">
                        <span id="drop-text" class="inter-medium-24" style="display:block;">Drag & drop image here or click to
                            select</span>
                    </div>
                    <input type="file" class="form-control d-none" id="artwork_image" name="artwork_image" accept="image/*"
                        required>
                </div>
            </form>
            <?php endif; ?>
        </div>
        <script>
const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('artwork_image');
const dropText = document.getElementById('drop-text');
const uploadForm = document.getElementById('submission-form');

// Click drop area opens file picker
dropArea.addEventListener('click', () => fileInput.click());

// Drag & drop effects
dropArea.addEventListener('dragover', (e) => {
  e.preventDefault();
  dropArea.classList.add('bg-light');
  dropText.textContent = 'Release to upload';
});
dropArea.addEventListener('dragleave', (e) => {
  e.preventDefault();
  dropArea.classList.remove('bg-light');
  dropText.textContent = 'Drag & drop image here or click to select';
});
dropArea.addEventListener('drop', (e) => {
  e.preventDefault();
  dropArea.classList.remove('bg-light');
  if (e.dataTransfer.files.length) {
    fileInput.files = e.dataTransfer.files;
    dropText.textContent = e.dataTransfer.files[0].name;
  }
});

// When file selected, update text
fileInput.addEventListener('change', () => {
  if (fileInput.files.length) {
    dropText.textContent = fileInput.files[0].name;
    dropArea.setAttribute("tabindex", "0");
    dropArea.focus();
  } else {
    dropText.textContent = 'Choose a file or drag and drop here';
  }
});

// Listen for Enter key to auto-submit
dropArea.addEventListener('keydown', (e) => {
  if (e.key === "Enter" && fileInput.files.length) {
    uploadForm.submit();
  }
});
</script>
        <?php
    } else {
        echo "Task not found.";
        exit();
    }
    ?>

</body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>

</html>