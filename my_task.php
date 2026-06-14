<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$uid = intval($_SESSION['UID']);

// Tasks the user posted.
$postedTasks = [];
$postedStmt = $conn->prepare(
    "SELECT task_id, task_title, task_description, task_state, task_image
     FROM task
     WHERE post_user_id = ?
     ORDER BY release_at DESC"
);
$postedStmt->bind_param("i", $uid);
$postedStmt->execute();
$postedResult = $postedStmt->get_result();
while ($row = $postedResult->fetch_assoc()) {
    $postedTasks[] = $row;
}

// Submissions the user made.
$mySubmissions = [];
$subStmt = $conn->prepare(
    "SELECT ts.submission_id, ts.task_id, ts.file_path, ts.status, ts.submitted_at,
            t.task_title
     FROM task_submissions ts
     JOIN task t ON ts.task_id = t.task_id
     WHERE ts.submitter_user_id = ?
     ORDER BY ts.submitted_at DESC"
);
$subStmt->bind_param("i", $uid);
$subStmt->execute();
$subResult = $subStmt->get_result();
while ($row = $subResult->fetch_assoc()) {
    $mySubmissions[] = $row;
}

$taskStateLabels = ['open' => 'Open', 'closed' => 'Closed', 'completed' => 'Completed'];
$submissionStatusLabels = ['accepted' => 'Accepted', 'submitted' => 'Submitted', 'cancelled' => 'Cancelled'];

include("navbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid"
        style="padding-left: 60px; padding-right: 60px; padding-bottom: 60px; margin-top:60px;">
        <div class="row g-3 align-items-stretch" style="margin-bottom: 60px;">
            <div class="col-12 col-sm-3 col-lg-3">
                <a href="upload_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Post Task</a>
            </div>
            <div class="col-12 col-sm-3 col-lg-3">
                <a href="accepted_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Accepted Task</a>
            </div>
            <div class="col-12 col-sm-3 col-lg-3">
                <a href="saved_tasks.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Saved Task</a>
            </div>
            <div class="col-12 col-sm-3 col-lg-3">
                <a href="my_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black active">My Task</a>
            </div>
        </div>

        <!-- Section 1: Tasks I Posted -->
        <h1 class="inter-bold-44 mb-4">Tasks I Posted</h1>
        <?php if (empty($postedTasks)): ?>
            <div class="card_border" style="padding: 40px; margin-bottom:48px;">
                <p class="inter-extralight-24 mb-0">You have not posted any tasks yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($postedTasks as $task):
                $taskImg = !empty($task['task_image']) ? "assets/uploads/task/" . $task['task_image'] : "assets/uploads/artworks/default_artwork.png";
                $stateKey = strtolower($task['task_state'] ?? 'open');
                $statusLabel = $taskStateLabels[$stateKey] ?? ucfirst($stateKey);
            ?>
            <div class="card_border" style="padding: 32px 48px; margin-bottom:32px;">
                <div class="row align-items-center">
                    <div class="col">
                        <p class="mb-2 inter-bold-32"><?php echo htmlspecialchars($task['task_title']); ?></p>
                        <p class="mb-2 inter-extralight-24"><?php echo htmlspecialchars($task['task_description']); ?></p>
                        <p class="mb-3 inter-extralight-15">Status: <?php echo htmlspecialchars($statusLabel); ?></p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="task_detail.php?id=<?php echo intval($task['task_id']); ?>"
                                class="btn btn-outline-black inter-medium-25 border_black">View task</a>
                            <?php if ($stateKey === 'open'): ?>
                                <a href="edit_task.php?id=<?php echo intval($task['task_id']); ?>"
                                    class="btn btn-outline-black inter-medium-25 border_black">Edit</a>
                                <form method="POST" action="close_task.php" onsubmit="return confirm('Close this task? It will be removed from the open task board but submissions are kept.');">
                                    <input type="hidden" name="task_id" value="<?php echo intval($task['task_id']); ?>">
                                    <button type="submit" class="btn btn-outline-black inter-medium-25 border_black">Close</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="delete_task.php" onsubmit="return confirm('Permanently delete this task and ALL its submissions/files? This cannot be undone.');">
                                <input type="hidden" name="task_id" value="<?php echo intval($task['task_id']); ?>">
                                <button type="submit" class="btn btn-outline-black inter-medium-25 border_black">Delete</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-auto">
                        <img src="<?php echo htmlspecialchars($taskImg); ?>" alt="Task Image"
                            style="width:260px; height:190px; border-radius:12px; object-fit:cover;">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Section 2: My Submissions -->
        <h1 class="inter-bold-44 mb-4 mt-5">My Submissions</h1>
        <?php if (empty($mySubmissions)): ?>
            <div class="card_border" style="padding: 40px; margin-bottom:48px;">
                <p class="inter-extralight-24 mb-0">You have not submitted any work yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($mySubmissions as $submission):
                $subImg = "assets/uploads/task_solution/" . $submission['file_path'];
                $subExt = strtolower(pathinfo($submission['file_path'], PATHINFO_EXTENSION));
                $subIsImage = in_array($subExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                $statusKey = strtolower($submission['status']);
                $statusLabel = $submissionStatusLabels[$statusKey] ?? ucfirst($statusKey);
            ?>
            <div class="card_border" style="padding: 32px 48px; margin-bottom:32px;">
                <div class="row align-items-center gx-4">
                    <div class="col">
                        <p class="mb-2 inter-bold-32"><?php echo htmlspecialchars($submission['task_title']); ?></p>
                        <p class="mb-2 inter-extralight-15">Status: <?php echo htmlspecialchars($statusLabel); ?></p>
                        <p class="mb-3 inter-extralight-15">Submitted: <?php echo htmlspecialchars($submission['submitted_at']); ?></p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="submission_detail.php?id=<?php echo intval($submission['submission_id']); ?>"
                                class="btn btn-outline-black inter-medium-25 border_black">View Submission</a>
                            <a href="edit_submission.php?id=<?php echo intval($submission['submission_id']); ?>"
                                class="btn btn-outline-black inter-medium-25 border_black">Edit</a>
                            <form method="POST" action="delete_submission.php" onsubmit="return confirm('Delete your submission? This removes your file but keeps the task; you can submit again later.');">
                                <input type="hidden" name="submission_id" value="<?php echo intval($submission['submission_id']); ?>">
                                <button type="submit" class="btn btn-outline-black inter-medium-25 border_black">Delete</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-auto">
                        <?php if ($subIsImage): ?>
                            <img src="<?php echo htmlspecialchars($subImg); ?>" alt="Submission"
                                style="width:260px; height:190px; border-radius:12px; object-fit:cover;">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <footer>
        <?php include("footer.php"); ?>
    </footer>
</body>
</html>
