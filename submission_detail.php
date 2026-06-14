<?php
include("config.php");
session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

$uid = intval($_SESSION['UID']);
$isAdmin = !empty($_SESSION['ADMIN']);
$submissionId = intval($_GET['id'] ?? 0);

// Fetch the submission joined to its task and submitter.
$submission = null;
if ($submissionId > 0) {
    $stmt = $conn->prepare(
        "SELECT ts.submission_id, ts.task_id, ts.submitter_user_id, ts.file_path, ts.message,
                ts.status, ts.submitted_at,
                u.user_name AS submitter_name, u.email AS submitter_email,
                t.task_title, t.post_user_id
         FROM task_submissions ts
         JOIN `user` u ON ts.submitter_user_id = u.user_id
         JOIN task t ON ts.task_id = t.task_id
         WHERE ts.submission_id = ?
         LIMIT 1"
    );
    $stmt->bind_param("i", $submissionId);
    $stmt->execute();
    $submission = $stmt->get_result()->fetch_assoc();
}

if (!$submission) {
    include("navbar.php");
    echo '<div class="container-fluid px-3 px-md-5" style="margin-top:60px; padding-bottom:60px;"><p class="inter-extralight-24">Submission not found.</p></div>';
    include("footer.php");
    exit();
}

// Access control: only the task poster, the submitter, or an admin may view.
$canView = ($isAdmin)
    || ($uid === intval($submission['post_user_id']))
    || ($uid === intval($submission['submitter_user_id']));

if (!$canView) {
    include("navbar.php");
    echo '<div class="container-fluid px-3 px-md-5" style="margin-top:60px; padding-bottom:60px;"><p class="inter-extralight-24">You are not allowed to view this submission.</p></div>';
    include("footer.php");
    exit();
}

$fileExt = strtolower(pathinfo($submission['file_path'], PATHINFO_EXTENSION));
$isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
$filePath = "assets/uploads/task_solution/" . $submission['file_path'];

include("navbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid px-3 px-md-5" style="margin-top:60px; padding-bottom:60px;">
        <a href="task_detail.php?id=<?php echo intval($submission['task_id']); ?>" class="inter-extralight-24">&larr; Back to task</a>

        <h1 class="inter-bold-44 mb-2 mt-3">Submission Detail</h1>
        <p class="inter-extralight-24 mb-4">Task: <strong><?php echo htmlspecialchars($submission['task_title']); ?></strong></p>

        <div class="row gx-4 gy-4">
            <div class="col-12 col-lg-8">
                <?php if ($isImage): ?>
                    <div class="d-flex justify-content-center align-items-center"
                        style="background-color:#f0f0f0; border-radius:12px; min-height:300px; padding:16px;">
                        <img src="<?php echo htmlspecialchars($filePath); ?>" alt="Submission"
                            style="max-width:100%; max-height:700px; width:auto; height:auto; display:block;">
                    </div>
                <?php else: ?>
                    <div class="card_border" style="padding: 40px;">
                        <p class="inter-extralight-24 mb-3">This submission is not an image file.</p>
                        <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank"
                            class="btn btn-outline-black inter-medium-25 border_black">Open / Download file</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card_border" style="padding: 30px;">
                    <h5 class="inter-bold-24 mb-3">Submitted by</h5>
                    <p class="inter-extralight-24 mb-1"><?php echo htmlspecialchars($submission['submitter_name']); ?></p>
                    <p class="inter-extralight-15 mb-3"><?php echo htmlspecialchars($submission['submitter_email']); ?></p>
                    <h5 class="inter-bold-24 mb-2">Status</h5>
                    <p class="inter-extralight-24 mb-3"><?php echo htmlspecialchars(ucfirst($submission['status'])); ?></p>
                    <h5 class="inter-bold-24 mb-2">Submitted at</h5>
                    <p class="inter-extralight-24 mb-3"><?php echo htmlspecialchars($submission['submitted_at']); ?></p>
                    <h5 class="inter-bold-24 mb-2">Message</h5>
                    <p class="inter-extralight-24 mb-0">
                        <?php echo !empty($submission['message']) ? nl2br(htmlspecialchars($submission['message'])) : 'No message provided.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
<footer>
    <?php include("footer.php"); ?>
</footer>

</html>
