<?php
include("config.php");

session_start();

if (!isset($_SESSION['UID'])) {
    header("Location: login.php");
    exit();
}

include("navbar.php");
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid"
        style="padding-left: 60px; padding-right: 60px; padding-bottom: 60px; margin-top:60px;">
        <div class="row g-3 align-items-stretch" style="margin-bottom: 60px;">
            <div class="col-12 col-lg-6">
                <?php
                $uid = intval($_SESSION['UID']);
                $searchValue = $_GET['search'] ?? '';

                $sql = "SELECT st.saved_task_id, st.created_at AS saved_at,
                               t.*, u.user_name, u.profile_image, c.category_name
                        FROM saved_tasks st
                        JOIN task t ON st.task_id = t.task_id
                        JOIN user u ON t.post_user_id = u.user_id
                        LEFT JOIN category c ON t.category_id = c.category_id
                        WHERE st.user_id = ?";

                $params = [$uid];
                $types = "i";

                if ($searchValue !== '') {
                    $search = "%" . $searchValue . "%";
                    $sql .= " AND (t.task_title LIKE ? OR t.task_description LIKE ?)";
                    $params[] = $search;
                    $params[] = $search;
                    $types .= "ss";
                }

                $sql .= " ORDER BY st.created_at DESC";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                ?>

                <form action="" method="get">
                    <div id="searchBarWrapper" style="position:relative; background:#000; border-radius:14px;">
                        <input type="text" class="form-control inter-medium-25 left-placeholder border_black"
                            id="searchInput" name="search"
                            value="<?php echo htmlspecialchars($searchValue); ?>"
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
                <a href="task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Task Board</a>
            </div>
            <div class="col-12 col-sm-4 col-lg-2">
                <a href="upload_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Post Task</a>
            </div>
            <div class="col-12 col-sm-4 col-lg-2">
                <a href="accepted_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Accepted Task</a>
            </div>
        </div>

        <h1 class="inter-bold-44 mb-4">Saved Tasks</h1>

        <?php if ($result->num_rows === 0): ?>
            <div class="card_border" style="padding: 40px; margin-bottom:48px;">
                <p class="inter-extralight-24 mb-0">No saved tasks found.</p>
            </div>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()):
            $profileImg = !empty($row['profile_image']) ? "assets/profile/" . $row['profile_image'] : "assets/profile/user_profile.png";
            $userName = $row['user_name'];
            $taskTitle = $row['task_title'];
            $taskDesc = $row['task_description'];
            $categoryName = $row['category_name'] ?? 'Uncategorized';
            $taskId = $row['task_id'];
            $taskImg = !empty($row['task_image']) ? "assets/uploads/task/" . $row['task_image'] : "assets/uploads/task/default_task.jpeg";
        ?>
        <div class="card_border saved-task-card" data-task-card="<?php echo htmlspecialchars($taskId); ?>"
            style="padding: 48px 64px; margin-bottom:48px;">
            <div class="row align-items-center mb-4">
                <div class="col d-flex align-items-center">
                    <a href="user_profile.php?uid=<?php echo urlencode($row['post_user_id']); ?>">
                        <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image" class="rounded-circle"
                            style="width:86px; height:86px; object-fit:cover;">
                    </a>
                    <div class="ms-4">
                        <a href="user_profile.php?uid=<?php echo urlencode($row['post_user_id']); ?>" style="text-decoration:none;">
                            <p class="mb-0 inter-bold-32" style="color:#000;"><?php echo htmlspecialchars($userName); ?></p>
                        </a>
                        <p class="mb-0 inter-extralight-15"><?php echo htmlspecialchars($categoryName); ?></p>
                    </div>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="button"
                        class="btn btn-outline-black inter-medium-25 border_black save-task-btn saved"
                        data-task-id="<?php echo htmlspecialchars($taskId); ?>"
                        data-saved="1"
                        aria-pressed="true">
                        <i class="fa-solid fa-bookmark" aria-hidden="true"></i>
                        <span>Saved</span>
                    </button>
                    <a href="task_detail.php?id=<?php echo urlencode($taskId); ?>" class="btn form-control btn-outline-black inter-medium-25 border_black"
                        style="width:200px; height:53px;">
                        View task
                    </a>
                </div>
            </div>
            <div class="row align-items-center">
                <div class="col">
                    <p class="mb-0 inter-bold-32"><?php echo htmlspecialchars($taskTitle); ?></p>
                    <p class="mb-0 inter-extralight-24"><?php echo htmlspecialchars($taskDesc); ?></p>
                </div>
                <div class="col-auto">
                    <img src="<?php echo htmlspecialchars($taskImg); ?>" alt="Task Image"
                        style="width:260px; height:190px; border-radius:12px; object-fit:cover;">
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <script>
        document.querySelectorAll('.save-task-btn').forEach(function (button) {
            button.addEventListener('click', async function () {
                const taskId = this.dataset.taskId;
                const card = this.closest('[data-task-card]');
                this.disabled = true;

                try {
                    const response = await fetch('save_task_toggle.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ task_id: taskId })
                    });

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to update saved task.');
                    }

                    if (!data.saved && card) {
                        card.remove();
                    }
                } catch (error) {
                    alert(error.message);
                } finally {
                    this.disabled = false;
                }
            });
        });
    </script>

    <footer>
        <?php include("footer.php"); ?>
    </footer>
</body>
</html>
