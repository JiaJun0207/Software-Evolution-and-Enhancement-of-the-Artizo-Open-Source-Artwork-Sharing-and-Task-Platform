<?php
include("config.php");
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['UID'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please log in to view tasks."
    ]);
    exit();
}

$uid = intval($_SESSION['UID']);
$searchValue = trim($_GET['search'] ?? '');
// Unified category source: same `category` table as explore. Filter on task.category_id.
$selectedCategoryId = intval($_GET['category_id'] ?? 0);

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

$sql = "SELECT t.*, u.user_name, u.profile_image,
               COALESCE(c.category_name, 'Uncategorized') AS display_category_name
        FROM task t
        JOIN user u ON t.post_user_id = u.user_id
        LEFT JOIN category c ON t.category_id = c.category_id";

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

if ($selectedCategoryId > 0) {
    $where[] = "t.category_id = ?";
    $params[] = $selectedCategoryId;
    $types .= "i";
}

$sql .= " WHERE " . implode(" AND ", $where) . " ORDER BY t.release_at DESC";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

ob_start();
if ($result->num_rows === 0): ?>
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
    $categoryName = $row['display_category_name'];
    $isSaved = isset($savedTaskLookup[intval($taskId)]);
?>
    <div class="col">
        <div class="card_border task-card d-flex flex-column justify-content-between"
            style="padding: 24px 32px; height: 330px;">
            <div class="task-card-content">
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
                <p class="task-category-chip mt-3 mb-2"><?php echo htmlspecialchars($categoryName); ?></p>
                <div class="mt-1">
                    <a href="task_detail.php?id=<?php echo urlencode($taskId); ?>" style="text-decoration:none;">
                        <p class="inter-extralight-24 task-description" style="color:#000;">
                            <?php echo htmlspecialchars($row['task_description']); ?>
                        </p>
                    </a>
                </div>
            </div>
            <div class="task-card-actions d-flex justify-content-between align-items-center gap-2">
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
<?php endwhile;

$html = ob_get_clean();

echo json_encode([
    "success" => true,
    "html" => $html,
    "budget_filter_available" => false,
    "budget_filter_message" => "Budget filtering is not available because this schema has no budget field."
]);
exit();
?>
