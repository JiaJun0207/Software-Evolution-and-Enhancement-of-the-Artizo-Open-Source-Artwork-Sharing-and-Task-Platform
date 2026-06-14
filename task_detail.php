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

// --- Handle accepting a task (per-user) BEFORE fetching task info ---
// Many users can accept the same open task. State is stored per user in
// task_acceptances; the task itself is never globally flipped.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_task']) && isset($_POST['task_id'])) {
    $accept_task_id = intval($_POST['task_id']);
    $checkStmt = $conn->prepare("SELECT post_user_id, task_state FROM task WHERE task_id = ?");
    $checkStmt->bind_param("i", $accept_task_id);
    $checkStmt->execute();
    $checkTask = $checkStmt->get_result()->fetch_assoc();

    if ($checkTask && $uid != $checkTask['post_user_id'] && $checkTask['task_state'] === 'open') {
        // Upsert this user's acceptance back to 'accepted' (also revives a
        // previously cancelled row). Does not touch other users.
        $upsert = $conn->prepare(
            "INSERT INTO task_acceptances (task_id, user_id, status)
             VALUES (?, ?, 'accepted')
             ON DUPLICATE KEY UPDATE status = 'accepted'"
        );
        $upsert->bind_param("ii", $accept_task_id, $uid);
        $upsert->execute();
        header("Location: task_detail.php?id=" . $accept_task_id);
        exit();
    }
}

// --- Handle cancelling an accepted task (per-user) BEFORE fetching task info ---
// Only marks the current user's acceptance as cancelled. The task row is never
// deleted and other users' acceptances/submissions are untouched.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_task']) && isset($_POST['task_id'])) {
    $cancel_task_id = intval($_POST['task_id']);
    $checkStmt = $conn->prepare("SELECT status FROM task_acceptances WHERE task_id = ? AND user_id = ? LIMIT 1");
    $checkStmt->bind_param("ii", $cancel_task_id, $uid);
    $checkStmt->execute();
    $cancelAcc = $checkStmt->get_result()->fetch_assoc();

    if ($cancelAcc && $cancelAcc['status'] === 'accepted') {
        $updateStmt = $conn->prepare("UPDATE task_acceptances SET status = 'cancelled' WHERE task_id = ? AND user_id = ?");
        $updateStmt->bind_param("ii", $cancel_task_id, $uid);
        $updateStmt->execute();
        $_SESSION['feedback'] = "You cancelled this task. You can accept it again anytime.";
        header("Location: task_detail.php?id=" . $cancel_task_id);
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
        $task_id = intval($_GET['id']);
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

        $isPoster = ($uid == $task['post_user_id']);
        $isAdmin = !empty($_SESSION['ADMIN']);
        $taskState = $task['task_state'] ?? 'open';

        // This user's per-user acceptance status (accepted | submitted | cancelled | null).
        $myAcceptance = null;
        $accStmt = $conn->prepare("SELECT status FROM task_acceptances WHERE task_id = ? AND user_id = ? LIMIT 1");
        $accStmt->bind_param("ii", $task_id, $uid);
        $accStmt->execute();
        if ($accRow = $accStmt->get_result()->fetch_assoc()) {
            $myAcceptance = $accRow['status'];
        }

        // User-friendly status label shown to the current viewer.
        if ($taskState === 'closed') {
            $statusLabel = 'Closed';
        } elseif ($taskState === 'completed') {
            $statusLabel = 'Completed';
        } elseif ($isPoster) {
            $statusLabel = 'Open';
        } elseif ($myAcceptance === 'submitted') {
            $statusLabel = 'Submitted by you';
        } elseif ($myAcceptance === 'accepted') {
            $statusLabel = 'Accepted by you';
        } else {
            $statusLabel = 'Open';
        }

        // The accept button is shown to a non-poster on an open task who has not
        // currently accepted/submitted it.
        $canAccept = (!$isPoster && $taskState === 'open' && ($myAcceptance === null || $myAcceptance === 'cancelled'));
        // The submission form is shown to a user holding an 'accepted' acceptance.
        $canSubmit = (!$isPoster && $taskState === 'open' && $myAcceptance === 'accepted');

        // Load submissions so the task poster (and admin) can review them.
        $submissions = [];
        if ($isPoster || $isAdmin) {
            $subStmt = $conn->prepare(
                "SELECT ts.*, u.user_name, u.email
                 FROM task_submissions ts
                 JOIN `user` u ON ts.submitter_user_id = u.user_id
                 WHERE ts.task_id = ?
                 ORDER BY ts.submitted_at DESC"
            );
            $subStmt->bind_param("i", $task_id);
            $subStmt->execute();
            $subResult = $subStmt->get_result();
            while ($subRow = $subResult->fetch_assoc()) {
                $submissions[] = $subRow;
            }
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

        ?>
        <div class="container-fluid px-3 px-md-5 pb-4 pb-md-5" style="margin-top:60px;">
            <?php if (isset($_SESSION['feedback'])): ?>
                <div class="alert alert-info inter-extralight-24" role="status">
                    <?php echo htmlspecialchars($_SESSION['feedback']); ?>
                </div>
                <?php unset($_SESSION['feedback']); ?>
            <?php endif; ?>
            <div class="d-flex align-items-center flex-column flex-sm-row">
                <div class="d-inline-block mb-3 mb-sm-0">
                    <img src="<?php echo htmlspecialchars($posterImg); ?>" alt="Profile Image" class="rounded-circle"
                        style="width:65px; height:65px; object-fit:cover;">
                </div>
                <p class="mb-0 inter-medium-24 ms-0 ms-sm-4 mt-2 mt-sm-0 text-center text-sm-start">
                    <?php echo htmlspecialchars($posterName); ?>
                </p>
            </div>
            <div class="row mb-4">
                <div class="col-12 col-lg-7" style="padding-right: 0px;">
                    <div class="d-flex align-items-center justify-content-between flex-column flex-md-row" style="margin-top:60px;">
                        <h1 class="inter-bold-44 mb-3 mb-md-0 text-center text-md-start">
                            <?php echo htmlspecialchars($task['task_title']); ?>
                        </h1>
                        <?php
                        $statusBg = ($statusLabel !== 'Open') ? 'background:#000; color:#fff;' : '';
                        ?>
                        <?php if ($canAccept): ?>
                            <form id="accept-task-form" method="POST" style="display:inline;">
                                <input type="hidden" name="accept_task" value="1">
                                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>">
                                <button type="submit" class="card_border inter-medium-25 mb-0"
                                    style="padding: 8px 40px; border:none; background:transparent; cursor:pointer;">
                                    Accept
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="card_border inter-medium-25 mb-0" style="padding: 8px 40px; <?php echo $statusBg; ?>">
                                <?php echo htmlspecialchars($statusLabel); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <div class="row mb-5 gx-0 gy-4 gy-lg-0" style="padding-left: 0;">
                <div class="col-12 col-lg-7 d-flex justify-content-center align-items-center"
                    style="background-color:#f0f0f0; max-height:700px; min-height:300px;">
                    <img src="<?php echo !empty($task['task_image']) ? 'assets/uploads/task/' . htmlspecialchars($task['task_image']) : 'assets/uploads/artworks/default_artwork.png'; ?>" alt="task_image"
                        style="max-width:100%; max-height:700px; width:auto; height:auto; display:block;">
                </div>
                <div class="col-12 col-lg-5 mt-4 mt-lg-0" style="padding-left: 20px;">
                    <h5 class="inter-bold-24 mb-4">Category</h5>
                    <div class="category_box inter-medium-25 mb-4"
                        style="color: #fff; background-color: <?php echo $categoryColor; ?>;">
                        <?php echo htmlspecialchars($categoryName); ?>
                    </div>
                    <h5 class="inter-bold-24 mb-4">Description</h5>
                    <p class="inter-extralight-24"><?php echo htmlspecialchars($task['task_description']); ?></p>
                </div>
            </div>

            <?php if ($isPoster): ?>
            <div class="d-flex flex-wrap gap-2 mb-5">
                <?php if ($taskState === 'open'): ?>
                    <a href="edit_task.php?id=<?php echo intval($task_id); ?>"
                        class="btn btn-outline-black inter-medium-25 border_black">Edit Task</a>
                    <form method="POST" action="close_task.php" onsubmit="return confirm('Close this task? It will be removed from the open task board but submissions are kept.');">
                        <input type="hidden" name="task_id" value="<?php echo intval($task_id); ?>">
                        <button type="submit" class="btn btn-outline-black inter-medium-25 border_black">Close Task</button>
                    </form>
                <?php endif; ?>
                <form method="POST" action="delete_task.php" onsubmit="return confirm('Permanently delete this task and ALL its submissions/files? This cannot be undone.');">
                    <input type="hidden" name="task_id" value="<?php echo intval($task_id); ?>">
                    <button type="submit" class="btn btn-outline-black inter-medium-25 border_black">Delete Task</button>
                </form>
            </div>
            <?php endif; ?>

            <?php if ($canSubmit): ?>
            <form id="submission-form" action="submission_task.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>">
                <div class="mb-4">
                    <label for="artwork_image" class="inter-bold-32 mb-3">Submission</label>
                    <div id="drop-area"
                        class="border border-2 border-dark rounded-3 text-center mb-3 d-flex flex-column align-items-center justify-content-center"
                        style="cursor:pointer; background:#fafafa; padding:60px;">
                        <img src="assets/icons/upload.png" alt="Upload Icon"
                            style="width:48px; height:48px; margin-bottom:12px; display:block;">
                        <span id="drop-text" class="inter-medium-24" style="display:block;">Drag & drop image here or click to
                            select</span>
                    </div>
                    <div id="submission-preview-wrap" class="task-image-preview-wrap d-none mb-3">
                        <img id="submission-preview" src="" alt="Submission preview">
                    </div>
                    <input type="file" class="form-control d-none" id="artwork_image" name="artwork_image" accept="image/*"
                        required>
                </div>
                <div class="mb-4">
                    <label for="submission_message" class="inter-bold-32 mb-3">Message (optional)</label>
                    <textarea id="submission_message" name="message" rows="3"
                        class="form-control inter-medium-25 left-placeholder border_black"
                        placeholder="Add a note for the task poster"></textarea>
                </div>
                <div class="d-flex gap-2 mb-5">
                    <button type="submit" class="btn btn-outline-black inter-medium-24 active">
                        <img src="assets/icons/post.png" alt="post Icon" style="width:20px; height:20px; margin-right:8px; vertical-align:middle;">
                        Submit Work
                    </button>
                </div>
            </form>
            <form method="POST" class="mb-5" onsubmit="return confirm('Cancel your acceptance of this task? You can accept it again later.');">
                <input type="hidden" name="cancel_task" value="1">
                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>">
                <button type="submit" class="btn btn-outline-black inter-medium-25 border_black">Cancel Accepted Task</button>
            </form>
            <?php endif; ?>

            <?php if ($isPoster || $isAdmin): ?>
            <div class="mb-5">
                <h5 class="inter-bold-32 mb-3">Submissions</h5>
                <?php if (empty($submissions)): ?>
                    <p class="inter-extralight-24">No submissions yet.</p>
                <?php else: ?>
                    <?php foreach ($submissions as $submission):
                        $subFile = $submission['file_path'];
                        $subExt = strtolower(pathinfo($subFile, PATHINFO_EXTENSION));
                        $subIsImage = in_array($subExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                    ?>
                        <?php
                            $subStatusLabel = ucfirst($submission['status']);
                            $msgPreview = trim((string)$submission['message']);
                            if (mb_strlen($msgPreview) > 120) {
                                $msgPreview = mb_substr($msgPreview, 0, 120) . '…';
                            }
                        ?>
                        <div class="card_border mb-3" style="padding: 20px 24px;">
                            <div class="row gx-4 gy-3 align-items-center">
                                <div class="col-12 col-md-3">
                                    <?php if ($subIsImage): ?>
                                        <div style="background:#f0f0f0; border-radius:12px; padding:6px; text-align:center;">
                                            <img src="assets/uploads/task_solution/<?php echo htmlspecialchars($subFile); ?>"
                                                alt="Submission" style="width:100%; max-height:150px; object-fit:contain; border-radius:8px;">
                                        </div>
                                    <?php else: ?>
                                        <a href="assets/uploads/task_solution/<?php echo htmlspecialchars($subFile); ?>" target="_blank"
                                            class="btn btn-outline-black inter-medium-25 border_black">Open file</a>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <p class="inter-bold-24 mb-1"><?php echo htmlspecialchars($submission['user_name']); ?></p>
                                    <p class="inter-extralight-15 mb-1">Status: <?php echo htmlspecialchars($subStatusLabel); ?></p>
                                    <p class="inter-extralight-15 mb-2">Submitted: <?php echo htmlspecialchars($submission['submitted_at']); ?></p>
                                    <?php if ($msgPreview !== ''): ?>
                                        <p class="inter-extralight-15 mb-0"><?php echo htmlspecialchars($msgPreview); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-3 text-md-end">
                                    <a href="submission_detail.php?id=<?php echo intval($submission['submission_id']); ?>"
                                        class="btn btn-outline-black inter-medium-25 border_black">View Submission</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php if ($canSubmit): ?>
        <script>
const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('artwork_image');
const dropText = document.getElementById('drop-text');
const previewWrap = document.getElementById('submission-preview-wrap');
const previewImg = document.getElementById('submission-preview');

function updateSubmissionPreview(file) {
  if (!file || !file.type.startsWith('image/')) {
    previewWrap.classList.add('d-none');
    previewImg.src = '';
    return;
  }
  const reader = new FileReader();
  reader.onload = (event) => {
    previewImg.src = event.target.result;
    previewWrap.classList.remove('d-none');
  };
  reader.readAsDataURL(file);
}

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
    try {
      fileInput.files = e.dataTransfer.files;
    } catch (error) {
      dropText.textContent = 'Use the file picker to select this image';
      updateSubmissionPreview(e.dataTransfer.files[0]);
      return;
    }
    dropText.textContent = e.dataTransfer.files[0].name;
    updateSubmissionPreview(e.dataTransfer.files[0]);
  }
});

// When file selected, update text + preview
fileInput.addEventListener('change', () => {
  if (fileInput.files.length) {
    dropText.textContent = fileInput.files[0].name;
    updateSubmissionPreview(fileInput.files[0]);
  } else {
    dropText.textContent = 'Choose a file or drag and drop here';
    updateSubmissionPreview(null);
  }
});
</script>
        <?php endif; ?>
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
