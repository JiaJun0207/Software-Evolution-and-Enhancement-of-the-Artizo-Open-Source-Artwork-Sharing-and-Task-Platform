<?php
include("config.php");// Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// --- Handle cancelling an accepted task (before any HTML output) ---
// Only the accepter can cancel, and only while still 'accepted'. The original
// task is never deleted; it just becomes available again.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_task']) && isset($_POST['task_id'])) {
    $cancelUid = intval($_SESSION['UID']);
    $cancelTaskId = intval($_POST['task_id']);
    $checkStmt = $conn->prepare("SELECT accepted_user_id, task_status FROM task WHERE task_id = ?");
    $checkStmt->bind_param("i", $cancelTaskId);
    $checkStmt->execute();
    $cancelTask = $checkStmt->get_result()->fetch_assoc();

    if ($cancelTask && intval($cancelTask['accepted_user_id']) === $cancelUid && strtolower($cancelTask['task_status']) === 'accepted') {
        $updateStmt = $conn->prepare("UPDATE task SET task_status = 'accept', accepted_user_id = NULL WHERE task_id = ?");
        $updateStmt->bind_param("i", $cancelTaskId);
        $updateStmt->execute();
        $_SESSION['feedback'] = "Accepted task cancelled. It is now available again.";
    }
    header("Location: accepted_task.php");
    exit();
}

include("navbar.php"); // Include the navigation bar
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid"
        style="padding-left: 60px; padding-right: 60px; padding-bottom: 60px; margin-top:60px;">
        <?php if (isset($_SESSION['feedback'])): ?>
            <div class="alert alert-info inter-extralight-24" role="status">
                <?php echo htmlspecialchars($_SESSION['feedback']); ?>
            </div>
            <?php unset($_SESSION['feedback']); ?>
        <?php endif; ?>
        <div class="row g-3 align-items-stretch" style="margin-bottom: 60px;">
            <div class="col-12 col-lg-6">
                <?php
                $uid = $_SESSION['UID'];

                // Fetch accepted tasks for this user
                $sql = "SELECT t.*, u.user_name, u.profile_image 
                        FROM task t
                        JOIN user u ON t.post_user_id = u.user_id
                        WHERE t.accepted_user_id = ?";

                $params = [$uid];
                $types = "i";

                if (isset($_GET['search']) && $_GET['search'] !== '') {
                    $search = "%" . $_GET['search'] . "%";
                    $sql .= " AND (t.task_title LIKE ? OR t.task_description LIKE ?)";
                    $params[] = $search;
                    $params[] = $search;
                    $types .= "ss";
                }

                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                ?>

                <form action="" method="get">
                    <div id="searchBarWrapper" style="position:relative; background:#000; border-radius:14px;">
                        <input type="text" class="form-control inter-medium-25 left-placeholder border_black"
                            id="searchInput" name="search"
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                            style="padding-right:40px; background:#000; color:#fff;">
                        <span id="searchIcon"
                            style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                            <img src="assets/Icons/search-white.svg" alt="Search Icon" id="searchImg">
                        </span>
                    </div>
                </form>
                <script>
                    const searchInput = document.getElementById('searchInput');
                    const searchBarWrapper = document.getElementById('searchBarWrapper');
                    const searchImg = document.getElementById('searchImg');

                    searchInput.addEventListener('focus', function () {
                        searchBarWrapper.style.background = "#fff";
                        searchInput.style.background = "#fff";
                        searchInput.style.color = "#000";
                        searchImg.src = "assets/Icons/search-black.svg";
                    });
                    searchInput.addEventListener('blur', function () {
                        searchBarWrapper.style.background = "#000";
                        searchInput.style.background = "#000";
                        searchInput.style.color = "#fff";
                        searchImg.src = "assets/Icons/search-white.svg";
                    });
                </script>

            </div>
            <div class="col-12 col-sm-4 col-lg-2">
                <a href="upload_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Post Task</a>
            </div>
            <div class="col-12 col-sm-4 col-lg-2">
                <a href="saved_tasks.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Saved Task</a>
            </div>
            <div class="col-12 col-sm-4 col-lg-2">
                <a href="my_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">My Task</a>
            </div>
        </div>

        <h1 class="inter-bold-44 mb-4">Accepted Tasks</h1>

        <?php if ($result->num_rows === 0): ?>
            <div class="card_border" style="padding: 40px; margin-bottom:48px;">
                <p class="inter-extralight-24 mb-0">No accepted tasks yet.</p>
            </div>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()):
            $profileImg = !empty($row['profile_image']) ? "assets/profile/" . $row['profile_image'] : "assets/profile/user_profile.png";
            $userName = $row['user_name'];
            $taskTitle = $row['task_title'];
            $taskDesc = $row['task_description'];
            $taskImg = !empty($row['task_image']) ? "assets/uploads/task/" . $row['task_image'] : "assets/uploads/artworks/default_artwork.png";
        ?>
        <div class="card_border" style="padding: 68px 100px; margin-bottom:48px;">
            <div class="row align-items-center mb-4">
                <div class="col d-flex align-items-center">
                    <a href="user_profile.php?id=<?php echo $row['post_user_id']; ?>">
                        <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image" class="rounded-circle"
                            style="width:113px; height:113px; object-fit:cover;">
                    </a>
                    <a href="user_profile.php?id=<?php echo $row['post_user_id']; ?>" style="text-decoration:none;">
                        <p class="mb-0 inter-bold-32 ms-4" style="color:#000;"><?php echo htmlspecialchars($userName); ?></p>
                    </a>
                </div>
                <div class="col-auto d-flex gap-2">
                    <a href="task_detail.php?id=<?php echo $row['task_id']; ?>" class="btn form-control btn-outline-black inter-medium-25 border_black"
                        style="width:200px; height:53px;">
                        View task
                    </a>
                    <?php if (strtolower($row['task_status']) === 'accepted'): ?>
                    <form method="POST" onsubmit="return confirm('Cancel this accepted task? It will become available to others again.');">
                        <input type="hidden" name="cancel_task" value="1">
                        <input type="hidden" name="task_id" value="<?php echo $row['task_id']; ?>">
                        <button type="submit" class="btn btn-outline-black inter-medium-25 border_black"
                            style="height:53px;">Cancel</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p class="mb-0 inter-bold-32"><?php echo htmlspecialchars($taskTitle); ?></p>
                    <p class="mb-0 inter-extralight-24"><?php echo htmlspecialchars($taskDesc); ?></p>
                </div>
                <div class="col-auto">
                    <img src="<?php echo htmlspecialchars($taskImg); ?>" alt="Task Image"
                        style="width:310px; height:231px; border-radius:12px; object-fit:cover;">
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <footer>
        <?php include("footer.php"); // Include the footer ?>
    </footer>
</body>
</html>
