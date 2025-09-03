<?php
include("config.php");// Include the database connection file

session_start(); // Start the session

if (!isset($_SESSION['UID'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Fetch user data
$uid = $_SESSION['UID'];
$query = "SELECT `profile_image`, `user_name`, `user_description`, `user_id` FROM `user` WHERE `user_id` = ?";
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

include("admin_navbar.php"); // Include the navigation bar
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid" style="padding-left: 60px; padding-right: 60px;">
        <h1 class="inter-bold-44 mb-4" style="margin-top:60px;">User List</h1>
        <div class="row mb-4">
            <div class="col">
                <?php
                // Search for task
                $sql = "SELECT t.*, u.user_name, u.profile_image FROM task t JOIN user u ON t.post_user_id = u.user_id";
                $where = [];
                if (isset($_GET['search']) && $_GET['search'] !== '') {
                    $search = $conn->real_escape_string($_GET['search']);
                    $where[] = "(t.task_title LIKE '%$search%' OR t.task_description LIKE '%$search%')";
                }
                if ($where) {
                    $sql .= " WHERE " . implode(' AND ', $where);
                }
                $result = $conn->query($sql);
                ?>
                <form action="" method="get">
                    <div id="searchBarWrapper"
                        style="position:relative; background:#fff; border-radius:14px; transition:all 0.3s;">
                        <input type="text" class="form-control inter-medium-25 left-placeholder border_black"
                            id="searchInput" name="search"
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                            style="padding-right:40px; background:transparent; color:#000; border:none; transition:all 0.3s;">
                        <span id="searchIcon"
                            style="position:absolute; right:12px; top:50%; transform:translateY(-50%); pointer-events:none;">
                            <img src="assets/Icons/search-black.svg" alt="Search Icon" id="searchImg">
                        </span>
                    </div>
                </form>

                <script>
                    const searchInput = document.getElementById('searchInput');
                    const searchBarWrapper = document.getElementById('searchBarWrapper');
                    const searchImg = document.getElementById('searchImg');

                    function updateSearchStyle() {
                        if (searchInput.value.trim() !== "") {
                            // Typing (has value)
                            searchBarWrapper.style.background = "#fff";
                            searchInput.style.color = "#000";
                            searchImg.src = "assets/Icons/search-black.svg";
                        } else if (document.activeElement === searchInput) {
                            // Focused but empty
                            searchBarWrapper.style.background = "#000";
                            searchInput.style.color = "#fff";
                            searchImg.src = "assets/Icons/search-white.svg";
                        } else {
                            // Normal (not focused, empty)
                            searchBarWrapper.style.background = "#fff";
                            searchInput.style.color = "#000";
                            searchImg.src = "assets/Icons/search-black.svg";
                        }
                    }

                    searchInput.addEventListener('focus', updateSearchStyle);
                    searchInput.addEventListener('blur', updateSearchStyle);
                    searchInput.addEventListener('input', updateSearchStyle);

                    // Run once on page load
                    updateSearchStyle();
                </script>

            </div>
        </div>

        <div class="d-flex mb-3 px-3">
            <div class="flex-fill d-flex align-items-center justify-content-center">
                <p class="mb-0 inter-bold-24">User</p>
            </div>
            <div class="flex-fill d-flex align-items-center justify-content-center">
                <p class="mb-0 inter-bold-24">Email</p>
            </div>
            <div class="flex-fill d-flex align-items-center justify-content-center">
                
            </div>
            <div class="flex-fill d-flex align-items-center justify-content-end">
                <!-- alignment usage -->
            </div>
        </div>

        <?php
        // Search for user
        $sql = "SELECT * FROM user";
        $where = [];
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $search = $conn->real_escape_string($_GET['search']);
            $where[] = "(user_name LIKE '%$search%' OR user_description LIKE '%$search%' OR email LIKE '%$search%')";
        }
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $result = $conn->query($sql);
        ?>

        <?php while ($row = $result->fetch_assoc()): 
            $profileImg = !empty($row['profile_image']) ? "assets/profile/" . $row['profile_image'] : "assets/profile/user_profile.png";
            $userName = isset($row['user_name']) ? $row['user_name'] : '';
            $userEmail = isset($row['email']) ? $row['email'] : '';
            $userId = isset($row['user_id']) ? $row['user_id'] : '';
        ?>
        <div id="content_card" class="card_border mb-3" style="padding:30px;">
            <div class="d-flex align-items-center">
                <div class="flex-fill d-flex align-items-center">
                    <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile Image" class="rounded-circle"
                        style="width:100px; height:100px; object-fit:cover;">
                    <p class="card-text mb-0 inter-medium-24 ms-3">
                        <?php echo htmlspecialchars($userName); ?>
                    </p>
                </div>
                <div class="flex-fill d-flex align-items-center justify-content-center">
                    <p class="card-text mb-0 inter-medium-24 ms-3">
                        <?php echo htmlspecialchars($userEmail); ?>
                    </p>
                </div>
                <div class="flex-fill d-flex align-items-center justify-content-end gap-2">
                    <div class="d-flex gap-2">
                        <a href="admin_edit_user.php?id=<?php echo $userId; ?>" style="text-decoration:none;">
                            <button type="button" class="btn border_black d-flex justify-content-center align-items-center"
                                style="width:53px; height:53px; padding:0;">
                                <img src="assets/icons/edit.png" alt="Edit Icon" style="width: 23px; height: 23px;">
                            </button>
                        </a>
                        <form method="post" action="delete_form.php" style="display:inline;">
                            <input type="hidden" name="type" value="user">
                            <input type="hidden" name="id" value="<?php echo $userId; ?>">
                            <button type="submit" class="btn border_black d-flex justify-content-center align-items-center"
                                style="width:53px; height:53px; padding:0;">
                                <img src="assets/icons/delete.png" alt="Delete Icon" style="width: 23px; height: 23px;">
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</body>

</html>