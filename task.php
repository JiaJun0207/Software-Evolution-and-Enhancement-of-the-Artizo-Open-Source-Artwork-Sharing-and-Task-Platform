<?php
include("config.php");// Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$uid = intval($_SESSION['UID']);

include("navbar.php"); // Include the navigation bar

$searchValue = $_GET['search'] ?? '';
$selectedTaskCategoryId = intval($_GET['task_category_id'] ?? 0);

$taskCategories = [];
$categoryStmt = $conn->prepare("SELECT task_category_id, category_name FROM task_categories ORDER BY category_name ASC");
if ($categoryStmt) {
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
    while ($categoryRow = $categoryResult->fetch_assoc()) {
        $taskCategories[] = $categoryRow;
    }
}

$taskCategoryColumnExists = false;
$columnCheck = $conn->prepare("SELECT COUNT(*) AS column_count FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'task' AND COLUMN_NAME = 'task_category_id'");
if ($columnCheck) {
    $columnCheck->execute();
    $columnResult = $columnCheck->get_result();
    if ($columnRow = $columnResult->fetch_assoc()) {
        $taskCategoryColumnExists = intval($columnRow["column_count"]) > 0;
    }
}
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
        <div class="row g-3 align-items-stretch">
            <div class="col-12 col-lg-6">
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
                <a href="upload_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Post Task</a>
            </div>
            <div class="col-12 col-sm-4 col-lg-2">
                <a href="accepted_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Accepted Task</a>
            </div>
            <div class="col-12 col-sm-4 col-lg-2">
                <a href="saved_tasks.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Saved Tasks</a>
            </div>
        </div>

        <div class="row" style="margin-top:100px; margin-bottom:130px;">
            <div id="taskCategoryFilters" class="task-filter-bar d-flex justify-content-between align-items-center" style="gap:0; padding:0 20px;">
                <button type="button"
                    class="task-filter-btn inter-medium-32 <?php echo $selectedTaskCategoryId === 0 ? 'active' : ''; ?>"
                    data-category-id="0">
                    ALL
                </button>
                <?php foreach ($taskCategories as $cat): ?>
                    <?php $categoryId = intval($cat['task_category_id']); ?>
                    <button type="button"
                        class="task-filter-btn inter-medium-32 <?php echo $selectedTaskCategoryId === $categoryId ? 'active' : ''; ?>"
                        data-category-id="<?php echo htmlspecialchars($categoryId); ?>">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        $savedTaskLookup = [];
        $savedStmt = $conn->prepare("SELECT task_id FROM saved_tasks WHERE user_id = ?");
        if ($savedStmt) {
            $savedStmt->bind_param("i", $uid);
            $savedStmt->execute();
            $savedResult = $savedStmt->get_result();
            while ($savedRow = $savedResult->fetch_assoc()) {
                $savedTaskLookup[intval($savedRow['task_id'])] = true;
            }
        }

        // Build SQL for category and search
        if ($taskCategoryColumnExists) {
            $sql = "SELECT t.*, u.user_name, u.profile_image,
                           COALESCE(tc.category_name, c.category_name, 'Uncategorized') AS display_category_name
                    FROM task t
                    JOIN user u ON t.post_user_id = u.user_id
                    LEFT JOIN category c ON t.category_id = c.category_id
                    LEFT JOIN task_categories tc ON t.task_category_id = tc.task_category_id";
        } else {
            $sql = "SELECT t.*, u.user_name, u.profile_image,
                           COALESCE(c.category_name, 'Uncategorized') AS display_category_name
                    FROM task t
                    JOIN user u ON t.post_user_id = u.user_id
                    LEFT JOIN category c ON t.category_id = c.category_id";
        }

        $where = ["t.task_status = 'accept'"];
        $params = [];
        $types = "";

        if ($searchValue !== '') {
            $search = "%" . $searchValue . "%";
            $where[] = "(t.task_title LIKE ? OR t.task_description LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }

        if ($taskCategoryColumnExists && $selectedTaskCategoryId > 0) {
            $where[] = "t.task_category_id = ?";
            $params[] = $selectedTaskCategoryId;
            $types .= "i";
        }

        $sql .= " WHERE " . implode(" AND ", $where) . " ORDER BY t.release_at DESC";
        $stmt = $conn->prepare($sql);
        if ($types !== "") {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        ?>

        <div id="taskCards" class="row row-cols-3 g-4">
            <?php if ($result->num_rows === 0): ?>
                <div class="col-12">
                    <div class="card_border" style="padding: 32px;">
                        <p class="inter-extralight-24 mb-0">No tasks found for this filter.</p>
                    </div>
                </div>
            <?php endif; ?>
            <?php while ($row = $result->fetch_assoc()): 
                $profileImg = !empty($row['profile_image']) ? "assets/profile/" . $row['profile_image'] : "assets/profile/user_profile.png";
                $userId = $row['post_user_id'];
                $taskId = $row['task_id'];
                $taskImg = !empty($row['task_image']) ? "assets/uploads/tasks/" . $row['task_image'] : "";
                $categoryName = $row['display_category_name'];
                $isSaved = isset($savedTaskLookup[intval($taskId)]);
            ?>
            <div class="col">
                <div class="card_border d-flex flex-column justify-content-between"
                    style="padding: 24px 32px; height: 330px;">
                    <!-- Top content -->
                    <div>
                        <div class="d-flex align-items-center">
                            <a href="user_profile.php?uid=<?php echo urlencode($userId); ?>">
                                <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image" class="rounded-circle"
                                    style="width:46px; height:46px; object-fit:cover;">
                            </a>
                            <a href="user_profile.php?uid=<?php echo urlencode($userId); ?>" style="text-decoration:none;">
                                <p class="mb-0 inter-medium-32 ms-4" style="color:#000;">
                                    <?php echo htmlspecialchars($row['user_name']); ?>
                                </p>
                            </a>
                        </div>
                        <div class="mt-1">
                            <p class="task-category-chip mt-3 mb-2"><?php echo htmlspecialchars($categoryName); ?></p>
                            <a href="task_detail.php?id=<?php echo urlencode($taskId); ?>" style="text-decoration:none;">
                                <p class="inter-extralight-24" style="color:#000;">
                                    <?php echo htmlspecialchars($row['task_description']); ?>
                                </p>
                            </a>
                        </div>
                    </div>
                    <!-- Bottom actions -->
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <button type="button"
                            class="btn btn-outline-black border_black save-task-btn <?php echo $isSaved ? 'saved' : ''; ?>"
                            data-task-id="<?php echo htmlspecialchars($taskId); ?>"
                            data-saved="<?php echo $isSaved ? '1' : '0'; ?>"
                            aria-pressed="<?php echo $isSaved ? 'true' : 'false'; ?>">
                            <i class="<?php echo $isSaved ? 'fa-solid' : 'fa-regular'; ?> fa-bookmark" aria-hidden="true"></i>
                            <span><?php echo $isSaved ? 'Saved' : 'Save'; ?></span>
                        </button>
                        <a href="task_detail.php?id=<?php echo urlencode($taskId); ?>">
                            <div class="card_border" style="padding: 17px; display:inline-block;">
                                <img src="assets/icons/bag.png" alt="Arrow Right Icon" class="bag-icon-responsive">
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    </div>
    </div>
    <script>
        function attachSaveTaskHandlers() {
            document.querySelectorAll('.save-task-btn').forEach(function (button) {
                if (button.dataset.bound === '1') {
                    return;
                }

                button.dataset.bound = '1';
                button.addEventListener('click', async function () {
                    const taskId = this.dataset.taskId;
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
                            if (response.status === 401) {
                                window.location.href = 'login.php';
                                return;
                            }
                            throw new Error(data.message || 'Unable to update saved task.');
                        }

                        this.dataset.saved = data.saved ? '1' : '0';
                        this.setAttribute('aria-pressed', data.saved ? 'true' : 'false');
                        this.classList.toggle('saved', data.saved);
                        this.innerHTML = data.saved
                            ? '<i class="fa-solid fa-bookmark" aria-hidden="true"></i><span>Saved</span>'
                            : '<i class="fa-regular fa-bookmark" aria-hidden="true"></i><span>Save</span>';
                    } catch (error) {
                        alert(error.message);
                    } finally {
                        this.disabled = false;
                    }
                });
            });
        }

        async function loadFilteredTasks(categoryId) {
            const taskCards = document.getElementById('taskCards');
            const params = new URLSearchParams();
            const search = searchInput.value.trim();

            if (search !== '') {
                params.set('search', search);
            }

            if (categoryId && categoryId !== '0') {
                params.set('task_category_id', categoryId);
            }

            taskCards.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch('task_filter.php?' + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    if (response.status === 401) {
                        window.location.href = 'login.php';
                        return;
                    }
                    throw new Error(data.message || 'Unable to filter tasks.');
                }

                taskCards.innerHTML = data.html;
                attachSaveTaskHandlers();
            } catch (error) {
                alert(error.message);
            } finally {
                taskCards.setAttribute('aria-busy', 'false');
            }
        }

        document.querySelectorAll('.task-filter-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelectorAll('.task-filter-btn').forEach(function (filterButton) {
                    filterButton.classList.remove('active');
                });
                this.classList.add('active');
                loadFilteredTasks(this.dataset.categoryId);
            });
        });

        document.querySelector('form').addEventListener('submit', function (event) {
            event.preventDefault();
            const activeFilter = document.querySelector('.task-filter-btn.active');
            loadFilteredTasks(activeFilter ? activeFilter.dataset.categoryId : '0');
        });

        attachSaveTaskHandlers();
    </script>
</body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>

</html>
