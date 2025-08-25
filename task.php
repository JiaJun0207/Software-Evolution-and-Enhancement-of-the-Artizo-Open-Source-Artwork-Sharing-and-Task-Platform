<?php
include("config.php");// Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
    header("Location: login.php"); // Redirect to login if not logged in
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
        <div class="row">
            <div class="col-8">
                <?php
                $sql = "SELECT * FROM task";
                if (isset($_GET['search'])) {
                    $search = $_GET['search'];
                    $sql .= " WHERE `task_title` LIKE '%$search%'
                    OR `task_description` LIKE '%$search%'";
                } else {
                    $_GET['search'] = '';
                }
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
            <div class="col-2">
                <a href="upload_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Post Task</a>
            </div>
            <div class="col-2">
                <a href="accepted_task.php"
                    class="btn form-control btn-outline-black flex-fill inter-medium-25 border_black">Accepted Task</a>
            </div>
        </div>

        <?php
        // Get selected category from GET
        $selectedCategory = $_GET['category'] ?? 'ALL';

        // Define categories
        $categories = [
            'ALL',
            'Graphic Design',
            'Illustration',
            'Photography',
            '3D Art',
            'Advertising'
        ];
        ?>

        <div class="row" style="margin-top:100px; margin-bottom:130px;">
            <div class="d-flex justify-content-between align-items-center" style="gap:0; padding:0 20px;">
                <?php foreach ($categories as $cat): ?>
                    <?php
                    $isActive = ($selectedCategory === $cat) || ($cat === 'ALL' && $selectedCategory === 'ALL');
                    $catParam = $cat === 'ALL' ? '' : 'category=' . urlencode($cat);
                    $href = $cat === 'ALL' ? 'task.php' : 'task.php?' . $catParam;
                    ?>
                    <a href="<?php echo $href; ?>" style="text-decoration:none; text-align:center;">
                        <p class="inter-medium-32"
                            style="margin:0; padding-bottom:8px; color:#000; display:inline-block; border-bottom:<?php echo $isActive ? '3px solid #000' : 'none'; ?>;">
                            <?php echo $cat; ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        // Build SQL for category and search
        $sql = "SELECT t.*, u.user_name, u.profile_image, c.category_name 
                FROM task t 
                JOIN user u ON t.post_user_id = u.user_id 
                JOIN category c ON t.category_id = c.category_id";
        $where = ["t.task_status = 'pending'"];
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $search = $conn->real_escape_string($_GET['search']);
            $where[] = "(t.task_title LIKE '%$search%' OR t.task_description LIKE '%$search%')";
        }
        if (isset($_GET['category']) && $_GET['category'] !== '' && $_GET['category'] !== 'ALL') {
            $category = $conn->real_escape_string($_GET['category']);
            $where[] = "c.category_name = '$category'";
        }
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $result = $conn->query($sql);
        ?>

        <div class="row row-cols-3 g-4">
            <?php while ($row = $result->fetch_assoc()): 
                $profileImg = !empty($row['profile_image']) ? "assets/profile/" . $row['profile_image'] : "assets/profile/user_profile.png";
                $userId = $row['post_user_id'];
                $taskId = $row['task_id'];
                $taskImg = !empty($row['task_image']) ? "assets/uploads/tasks/" . $row['task_image'] : "";
                $categoryName = $row['category_name'];
            ?>
            <div class="col">
                <div class="card_border d-flex flex-column justify-content-between"
                    style="padding: 24px 32px; height: 300px;">
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
                            <a href="task_detail.php?id=<?php echo urlencode($taskId); ?>" style="text-decoration:none;">
                                <p class="inter-extralight-24" style="color:#000;">
                                    <?php echo htmlspecialchars($row['task_description']); ?>
                                </p>
                            </a>
                        </div>
                    </div>
                    <!-- Bottom right icon -->
                    <div class="text-end">
                        <a href="task_detail.php?id=<?php echo urlencode($taskId); ?>">
                            <div class="card_border" style="padding: 17px; display:inline-block;">
                                <img src="assets/icons/bag.png" alt="Arrow Right Icon" style="width:49px; height:49px;">
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
</body>
<footer>
    <?php include("footer.php"); // Include the footer ?>
</footer>

</html>